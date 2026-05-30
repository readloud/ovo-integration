<?php
// examples/login.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;

echo "=== OVO LOGIN ===\n\n";

// Inisialisasi client
$ovo = new OVOClient(null, "contoh-device-id");

try {
    // Step 1: Request OTP
    echo "Step 1: Meminta OTP\n";
    echo "Masukkan nomor HP (contoh: +628123456789): ";
    $phone = trim(fgets(STDIN));
    
    $otpResult = $ovo->requestOTP($phone);
    $otpRefId = $otpResult['otp_ref_id'];
    echo "✓ OTP Ref ID: {$otpRefId}\n\n";
    
    // Step 2: Validate OTP
    echo "Step 2: Validasi OTP\n";
    echo "Masukkan kode OTP yang diterima: ";
    $otpCode = trim(fgets(STDIN));
    
    $validateResult = $ovo->validateOTP($phone, $otpRefId, $otpCode);
    $otpToken = $validateResult['otp_token'];
    echo "✓ OTP Token: {$otpToken}\n\n";
    
    // Step 3: Login
    echo "Step 3: Login dengan security code\n";
    echo "Masukkan 6 digit security code PIN OVO Anda: ";
    $securityCode = trim(fgets(STDIN));
    
    $loginResult = $ovo->accountLogin($phone, $otpRefId, $otpToken, $securityCode);
    $accessToken = $loginResult['access_token'];
    
    echo "\n✅ LOGIN BERHASIL!\n";
    echo "Access Token: {$accessToken}\n";
    echo "User: {$loginResult['user_name']}\n";
    
    // Simpan token
    file_put_contents(__DIR__ . '/../storage/token.txt', $accessToken);
    echo "\n✓ Token disimpan di storage/token.txt\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
