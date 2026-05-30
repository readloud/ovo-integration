<?php
// src/SignatureGenerator.php

namespace Svn\OVOID;

class SignatureGenerator
{
    private $deviceId;
    
    public function __construct(?string $deviceId = null)
    {
        $this->deviceId = $deviceId ?? $this->generateDeviceId();
    }
    
    public function generate(
        string $method,
        string $path,
        string $timestamp,
        ?array $body = null,
        string $accessToken = ''
    ): string {
        $bodyString = $body ? json_encode($body) : '';
        
        $stringToSign = implode(':', [
            strtoupper($method),
            $path,
            $timestamp,
            $bodyString,
            $accessToken
        ]);
        
        $secretKey = $this->generateSecretKey($timestamp);
        
        return hash_hmac('sha256', $stringToSign, $secretKey);
    }
    
    public function generateForMultipleTransfers(
        string $method,
        string $path,
        string $timestamp,
        ?array $body = null,
        string $accessToken = '',
        int $transferCount = 1
    ): string {
        $bodyString = $body ? json_encode($body) : '';
        
        $stringToSign = implode(':', [
            strtoupper($method),
            $path,
            $timestamp,
            $bodyString,
            $accessToken,
            (string) $transferCount
        ]);
        
        $secretKey = $this->generateSecretKey($timestamp, $transferCount);
        
        return hash_hmac('sha256', $stringToSign, $secretKey);
    }
    
    private function generateSecretKey(string $timestamp, int $transferCount = 1): string
    {
        $secretBase = $this->deviceId . $timestamp . str_repeat('0', $transferCount);
        return hash('sha256', $secretBase);
    }
    
    private function generateDeviceId(): string
    {
        return sprintf(
            'android-%s-%s',
            uniqid(),
            bin2hex(random_bytes(8))
        );
    }
    
    public function getDeviceId(): string
    {
        return $this->deviceId;
    }
}
