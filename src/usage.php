<?php
// usage.php

require_once 'vendor/autoload.php';
require_once 'Svn/helpers.php';

use Svn\OVOID;

// Inisialisasi dengan device ID
$ovo = new OVOID(null, "android-test-device-12345");

try {
    // Step 1: Request OTP
    echo "Step 1: Request OTP\n";
    $otpResponse = $ovo->OTP("+628123456789");
    $otpRefId = $otpResponse->getOtpRefId();
    echo "OTP Ref ID: {$otpRefId}\n\n";
    
    // Step 2: Validate OTP (input from user)
    echo "Step 2: Enter OTP: ";
    $otpCode = trim(fgets(STDIN));
    $validateResponse = $ovo->OTPValidation("+628123456789", $otpRefId, $otpCode);
    $otpToken = $validateResponse->getOtpToken();
    echo "OTP Token: {$otpToken}\n\n";
    
    // Step 3: Login with security code
    echo "Step 3: Enter 6-digit security code: ";
    $securityCode = trim(fgets(STDIN));
    $loginResponse = $ovo->accountLogin("+628123456789", $otpRefId, $otpToken, $securityCode);
    $accessToken = $loginResponse->getAccessToken();
    echo "Access Token: {$accessToken}\n\n";
    
    // ==================== BALANCE ====================
    echo "\n=== BALANCE ===\n";
    $balance = $ovo->balanceModel();
    echo "Payment Methods: " . implode(', ', $balance->getPaymentMethod()) . "\n";
    echo "OVO Balance: Rp " . number_format($balance->getCardBalance('OVO')['balance'] ?? 0, 0, ',', '.') . "\n";
    echo "OVO Card No: " . $balance->getCardNo('OVO') . "\n";
    
    // ==================== TRANSFER ====================
    echo "\n=== TRANSFER ===\n";
    $isOvo = $ovo->isOVO(10000, "+628123456789");
    if ($isOvo->isOVO()) {
        echo "Destination is OVO user: {$isOvo->getName()}\n";
        
        // Transfer pertama (tanpa signature)
        $transfer = $ovo->transferOvo("+628123456789", 10000, "Test transfer");
        echo "Transfer success: {$transfer->getTrxId()}\n";
    }
    
    // ==================== HISTORY ====================
    echo "\n=== TRANSACTION HISTORY ===\n";
    $history = $ovo->getWalletTransaction(1, 5);
    foreach ($history->getTransactions() as $tx) {
        echo "- {$tx->description}: Rp " . number_format($tx->amount, 0, ',', '.') . "\n";
    }
    
    // ==================== NOTIFICATIONS ====================
    echo "\n=== NOTIFICATIONS ===\n";
    $unread = $ovo->unreadHistory();
    echo "Unread notifications: {$unread->getTotal()}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}