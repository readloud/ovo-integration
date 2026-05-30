<?php
// examples/profile.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;

echo "=== PROFIL OVO ===\n\n";

// Load token
$tokenFile = __DIR__ . '/../storage/token.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token tidak ditemukan. Jalankan 01_login.php terlebih dahulu.\n";
    exit(1);
}

$authToken = trim(file_get_contents($tokenFile));
$ovo = new OVOClient($authToken);

try {
    $profile = $ovo->getProfile();
    
    echo "Nama: {$profile['name']}\n";
    echo "Email: {$profile['email']}\n";
    echo "Nomor HP: {$profile['phone']}\n";
    echo "Status Verifikasi: " . ($profile['is_verified'] ? '✓ Terverifikasi' : '✗ Belum terverifikasi') . "\n";
    echo "Status KYC: {$profile['kyc_status']}\n";
    
    // Get unread notifications
    $unread = $ovo->getUnreadNotificationCount();
    echo "\nNotifikasi belum dibaca: {$unread['total']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
