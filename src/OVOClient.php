<?php
// src/OVOClient.php

namespace Svn\OVOID;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Svn\OVOID\Exceptions\OVOException;

class OVOClient
{
    const BASE_URL = 'https://api.ovo.id';
    const API_VERSION = 'v2.0';
    const APP_VERSION = '2.8.0';
    const PLATFORM = 'android';
    const USER_AGENT = 'ovo.id/2.8.0 (Android; 11; SM-G998B)';
    
    private $httpClient;
    private $authToken;
    private $deviceId;
    private $signatureGenerator;
    private $headers = [];
    
    public function __construct(?string $authToken = null, ?string $deviceId = null)
    {
        $this->authToken = $authToken;
        $this->deviceId = $deviceId ?? $this->generateDeviceId();
        $this->signatureGenerator = new SignatureGenerator($this->deviceId);
        
        $this->httpClient = new Client([
            'base_uri' => self::BASE_URL,
            'timeout' => 30,
            'verify' => false,
            'headers' => $this->buildDefaultHeaders()
        ]);
        
        if ($authToken) {
            $this->setAuthToken($authToken);
        }
    }
    
    private function generateDeviceId(): string
    {
        return sprintf('android-%s-%s', uniqid(), bin2hex(random_bytes(8)));
    }
    
    private function buildDefaultHeaders(): array
    {
        return [
            'User-Agent' => self::USER_AGENT,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'app-version' => self::APP_VERSION,
            'platform' => self::PLATFORM,
            'device-id' => $this->deviceId,
            'device-os' => 'Android 11',
            'device-model' => 'SM-G998B',
            'device-manufacturer' => 'samsung'
        ];
    }
    
    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
        $this->headers['authorization'] = 'Bearer ' . $authToken;
    }
    
    private function generateSignatureHeaders(string $method, string $path, ?array $body = null, int $transferCount = 1): array
    {
        $timestamp = (string) (time() * 1000);
        
        if ($transferCount > 1) {
            $signature = $this->signatureGenerator->generateForMultipleTransfers(
                $method, $path, $timestamp, $body, $this->authToken ?? '', $transferCount
            );
        } else {
            $signature = $this->signatureGenerator->generate(
                $method, $path, $timestamp, $body, $this->authToken ?? ''
            );
        }
        
        return ['timestamp' => $timestamp, 'signature' => $signature];
    }
    
    private function request(string $method, string $endpoint, ?array $data = null, bool $needAuth = true, int $transferCount = 1): array
    {
        $url = self::API_VERSION . $endpoint;
        $headers = $this->headers;
        
        if ($needAuth && $this->authToken) {
            $headers['authorization'] = 'Bearer ' . $this->authToken;
        }
        
        $signatureHeaders = $this->generateSignatureHeaders($method, $endpoint, $data, $transferCount);
        $headers = array_merge($headers, $signatureHeaders);
        
        try {
            $options = ['headers' => $headers, 'http_errors' => false];
            if ($data) $options['json'] = $data;
            
            $response = $this->httpClient->request($method, $url, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            
            if ($response->getStatusCode() === 401) {
                throw new OVOException('Unauthorized - Token expired', 401);
            }
            
            if ($response->getStatusCode() !== 200 && isset($body['errorCode'])) {
                throw new OVOException($body['message'] ?? 'Unknown error', $response->getStatusCode(), $body['errorCode'] ?? null);
            }
            
            return $body;
            
        } catch (GuzzleException $e) {
            throw new OVOException('Network error: ' . $e->getMessage(), 500);
        }
    }
    
    // ==================== AUTHENTICATION ====================
    
    public function requestOTP(string $mobilePhone): array
    {
        $response = $this->request('POST', '/auth/otp', [
            'mobilePhone' => $mobilePhone,
            'deviceId' => $this->deviceId
        ], false);
        
        return ['otp_ref_id' => $response['otpRefId'] ?? null];
    }
    
    public function validateOTP(string $mobilePhone, string $otpRefId, string $otp): array
    {
        $response = $this->request('POST', '/auth/otp/validate', [
            'mobilePhone' => $mobilePhone,
            'otpRefId' => $otpRefId,
            'otp' => $otp
        ], false);
        
        return ['otp_token' => $response['otpToken'] ?? null];
    }
    
    public function accountLogin(string $mobilePhone, string $otpRefId, string $otpToken, string $securityCode): array
    {
        $response = $this->request('POST', '/auth/login', [
            'mobilePhone' => $mobilePhone,
            'otpRefId' => $otpRefId,
            'otpToken' => $otpToken,
            'securityCode' => $securityCode,
            'deviceId' => $this->deviceId
        ], false);
        
        if (isset($response['authToken'])) {
            $this->setAuthToken($response['authToken']);
        }
        
        return [
            'access_token' => $response['authToken'] ?? null,
            'refresh_token' => $response['refreshToken'] ?? null,
            'user_name' => $response['userName'] ?? null
        ];
    }
    
    // ==================== BALANCE ====================
    
    public function getPaymentMethods(): array
    {
        $response = $this->request('GET', '/wallet/paymentMethod');
        return $response['paymentMethods'] ?? [];
    }
    
    public function getCardBalance(string $paymentMethod): array
    {
        $response = $this->request('POST', '/wallet/balance/inquiry', ['paymentMethod' => $paymentMethod]);
        return [
            'balance' => $response['balance'] ?? 0,
            'cash_balance' => $response['cashBalance'] ?? 0,
            'points_balance' => $response['pointsBalance'] ?? 0
        ];
    }
    
    public function getCardNumber(string $paymentMethod): array
    {
        $response = $this->request('POST', '/wallet/card/inquiry', ['paymentMethod' => $paymentMethod]);
        return ['card_number' => $response['cardNo'] ?? null];
    }
    
    public function getFullBalance(): array
    {
        $methods = $this->getPaymentMethods();
        $balances = [];
        foreach ($methods as $method) {
            $balances[] = array_merge(
                ['payment_method' => $method],
                $this->getCardBalance($method),
                $this->getCardNumber($method)
            );
        }
        return $balances;
    }
    
    // ==================== TRANSFER ====================
    
    public function isOVOUser(string $mobilePhone): array
    {
        $response = $this->request('POST', '/user/inquiry', ['mobilePhone' => $mobilePhone]);
        return [
            'is_ovo' => $response['isOVO'] ?? false,
            'name' => $response['name'] ?? null
        ];
    }
    
    public function transferOvo(string $toMobilePhone, int $amount, ?string $message = null, int $transferCount = 1): array
    {
        $userCheck = $this->isOVOUser($toMobilePhone);
        if (!$userCheck['is_ovo']) {
            throw new OVOException('Destination is not an OVO user', 400);
        }
        
        $referenceId = 'REF-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        
        $response = $this->request('POST', '/transfer/ovo', [
            'amount' => $amount,
            'toMobilePhone' => $toMobilePhone,
            'referenceId' => $referenceId,
            'note' => $message ?? 'Transfer via API',
            'paymentMethod' => 'OVO'
        ], true, $transferCount);
        
        return [
            'reference_id' => $referenceId,
            'transaction_id' => $response['transactionId'] ?? null,
            'amount' => $amount,
            'to_name' => $userCheck['name']
        ];
    }
    
    // ==================== TRANSACTION HISTORY ====================
    
    public function getWalletTransaction(int $page, int $limit = 10, string $productType = '001'): array
    {
        $response = $this->request('POST', '/wallet/transaction', [
            'page' => $page,
            'limit' => $limit,
            'productType' => $productType
        ]);
        
        $transactions = [];
        foreach ($response['transactions'] ?? [] as $tx) {
            $transactions[] = [
                'transaction_id' => $tx['transactionId'] ?? null,
                'type' => $tx['type'] ?? null,
                'amount' => $tx['amount'] ?? 0,
                'status' => $tx['status'] ?? null,
                'date' => $tx['transactionDate'] ?? null,
                'description' => $tx['description'] ?? null,
                'counterpart_name' => $tx['counterpartName'] ?? null
            ];
        }
        
        return [
            'current_page' => $page,
            'total' => $response['total'] ?? 0,
            'transactions' => $transactions
        ];
    }
    
    // ==================== PROFILE ====================
    
    public function getProfile(): array
    {
        $response = $this->request('GET', '/profile');
        return [
            'name' => $response['name'] ?? null,
            'email' => $response['email'] ?? null,
            'phone' => $response['mobilePhone'] ?? null,
            'is_verified' => $response['verified'] ?? false,
            'kyc_status' => $response['kycStatus'] ?? null
        ];
    }
    
    // ==================== NOTIFICATIONS ====================
    
    public function getAllNotifications(): array
    {
        $response = $this->request('GET', '/notification/all');
        $notifications = [];
        foreach ($response['notifications'] ?? [] as $notif) {
            $notifications[] = [
                'id' => $notif['id'] ?? null,
                'title' => $notif['title'] ?? null,
                'message' => $notif['message'] ?? null,
                'is_read' => $notif['read'] ?? false,
                'created_at' => $notif['createdAt'] ?? null
            ];
        }
        return ['notifications' => $notifications, 'total' => count($notifications)];
    }
    
    public function getUnreadNotificationCount(): array
    {
        $response = $this->request('GET', '/notification/unread/count');
        return ['total' => $response['total'] ?? 0];
    }
}
