<?php
// examples/transfer.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;
use Svn\OVOID\Exceptions\OVOException;

echo "=== TRANSFER OVO ===\n\n";

// Load token
$tokenFile = __DIR__ . '/../storage/token.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token tidak ditemukan. Jalankan 01_login.php terlebih dahulu.\n";
    exit(1);
}

$authToken = trim(file_get_contents($tokenFile));
$ovo = new OVOClient($authToken);

try {
    // Input transfer details
    echo "Masukkan nomor tujuan (contoh: +628123456789): ";
    $toPhone = trim(fgets(STDIN));
    
    echo "Masukkan jumlah transfer (minimal Rp 10.000): Rp ";
    $amount = (int) trim(fgets(STDIN));
    
    echo "Masukkan pesan (optional): ";
    $message = trim(fgets(STDIN));
    
    // Check if user exists
    echo "\n✓ Mengecek akun tujuan...\n";
    $userCheck = $ovo->isOVOUser($toPhone);
    
    if (!$userCheck['is_ovo']) {
        echo "❌ Nomor bukan pengguna OVO!\n";
        exit(1);
    }
    
    echo "✓ Akun ditemukan: {$userCheck['name']}\n\n";
    
    // Confirm transfer
    echo "Konfirmasi transfer:\n";
    echo "  Ke: {$userCheck['name']} ({$toPhone})\n";
    echo "  Jumlah: Rp " . number_format($amount, 0, ',', '.') . "\n";
    echo "  Pesan: " . ($message ?: '-') . "\n";
    echo "\nLanjutkan? (y/n): ";
    $confirm = trim(fgets(STDIN));
    
    if (strtolower($confirm) !== 'y') {
        echo "Transfer dibatalkan.\n";
        exit(0);
    }
    
    // Execute transfer
    echo "\n✓ Memproses transfer...\n";
    $transfer = $ovo->transferOvo($toPhone, $amount, $message);
    
    echo "\n✅ TRANSFER BERHASIL!\n";
    echo "Reference ID: {$transfer['reference_id']}\n";
    echo "Transaction ID: {$transfer['transaction_id']}\n";
    echo "Jumlah: Rp " . number_format($transfer['amount'], 0, ',', '.') . "\n";
    echo "Kepada: {$transfer['to_name']}\n";
    
} catch (OVOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    if ($e->getErrorCode()) {
        echo "Error Code: " . $e->getErrorCode() . "\n";
    }
}
