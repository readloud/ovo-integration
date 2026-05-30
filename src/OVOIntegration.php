<?php
// jubelio-integration/OVOIntegration.php

namespace Jubelio\Integrations;

use Stelin\OVOID\OVOClient;
use Stelin\OVOID\Exceptions\OVOException;

class OVOIntegration
{
    /**
     * @var OVOClient
     */
    private $ovoClient;
    
    /**
     * @var array
     */
    private $config;
    
    /**
     * OVOIntegration constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeClient();
    }
    
    /**
     * Initialize OVO client with stored token
     */
    private function initializeClient(): void
    {
        $authToken = $this->getStoredToken();
        $deviceId = $this->config['device_id'] ?? null;
        
        $this->ovoClient = new OVOClient($authToken, $deviceId);
        
        if ($authToken) {
            $this->ovoClient->setAuthToken($authToken);
        }
    }
    
    /**
     * Get stored token from database/file
     *
     * @return string|null
     */
    private function getStoredToken(): ?string
    {
        // Implement your token storage logic here
        // Could be database, file, cache, etc.
        
        if (file_exists('storage/ovo_token.txt')) {
            return trim(file_get_contents('storage/ovo_token.txt'));
        }
        
        return null;
    }
    
    /**
     * Save token
     *
     * @param string $token
     */
    private function saveToken(string $token): void
    {
        file_put_contents('storage/ovo_token.txt', $token);
    }
    
    /**
     * Authenticate OVO user
     *
     * @param string $phone
     * @param string $otpCode
     * @param string $securityCode
     * @return array
     */
    public function authenticate(string $phone, string $otpCode, string $securityCode): array
    {
        try {
            // Step 1: Request OTP
            $otpResult = $this->ovoClient->requestOTP($phone);
            $otpRefId = $otpResult['otp_ref_id'];
            
            // Step 2: Validate OTP
            $validateResult = $this->ovoClient->validateOTP($phone, $otpRefId, $otpCode);
            $otpToken = $validateResult['otp_token'];
            
            // Step 3: Login
            $loginResult = $this->ovoClient->accountLogin($phone, $otpRefId, $otpToken, $securityCode);
            $accessToken = $loginResult['access_token'];
            
            // Save token
            if ($accessToken) {
                $this->saveToken($accessToken);
            }
            
            return [
                'success' => true,
                'access_token' => $accessToken,
                'user' => [
                    'name' => $loginResult['user_name'],
                    'email' => $loginResult['user_email']
                ]
            ];
            
        } catch (OVOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getErrorCode()
            ];
        }
    }
    
    /**
     * Get OVO balance
     *
     * @return array
     */
    public function getBalance(): array
    {
        try {
            $balance = $this->ovoClient->getFullBalance();
            
            return [
                'success' => true,
                'balance' => $balance
            ];
        } catch (OVOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Transfer to OVO user
     *
     * @param string $toPhone
     * @param int $amount
     * @param string|null $message
     * @return array
     */
    public function transferToOVO(string $toPhone, int $amount, ?string $message = null): array
    {
        try {
            // Check if user exists
            $userCheck = $this->ovoClient->isOVOUser($toPhone);
            
            if (!$userCheck['is_ovo']) {
                return [
                    'success' => false,
                    'error' => 'Destination is not an OVO user'
                ];
            }
            
            // Perform transfer
            $transfer = $this->ovoClient->transferOvo($toPhone, $amount, $message);
            
            // Log transaction
            $this->logTransaction($transfer);
            
            return [
                'success' => true,
                'transaction' => $transfer
            ];
            
        } catch (OVOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transaction history
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getTransactionHistory(int $page = 1, int $limit = 10): array
    {
        try {
            $transactions = $this->ovoClient->getWalletTransaction($page, $limit);
            
            return [
                'success' => true,
                'transactions' => $transactions
            ];
        } catch (OVOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Log transaction to database
     *
     * @param array $transaction
     */
    private function logTransaction(array $transaction): void
    {
        // Implement your logging logic here
        // Could save to database for reporting
        
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'ovo_transfer',
            'data' => $transaction
        ];
        
        file_put_contents(
            'logs/ovo_transactions.log',
            json_encode($log) . PHP_EOL,
            FILE_APPEND
        );
    }
}