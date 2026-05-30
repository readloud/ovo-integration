<?php
// examples/balance.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;

echo "=== CEK SALDO OVO ===\n\n";

// Load token
$tokenFile = __DIR__ . '/../storage/token.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token tidak ditemukan. Jalankan 01_login.php terlebih dahulu.\n";
    exit(1);
}

$authToken = trim(file_get_contents($tokenFile));
$ovo = new OVOClient($authToken);

try {
    // Get all payment methods
    $paymentMethods = $ovo->getPaymentMethods();
    echo "Payment Methods: " . implode(', ', $paymentMethods) . "\n\n";
    
    // Get full balance
    $balances = $ovo->getFullBalance();
    
    foreach ($balances as $balance) {
        echo "=== {$balance['payment_method']} ===\n";
        echo "Saldo: Rp " . number_format($balance['balance'], 0, ',', '.') . "\n";
        echo "Saldo Cash: Rp " . number_format($balance['cash_balance'], 0, ',', '.') . "\n";
        echo "Poin: " . number_format($balance['points_balance'], 0, ',', '.') . "\n";
        echo "No Kartu: {$balance['card_number']}\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
