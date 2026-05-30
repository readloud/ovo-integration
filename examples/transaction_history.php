<?php
// examples/transaction_history.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;

echo "=== HISTORY TRANSAKSI OVO ===\n\n";

// Load token
$tokenFile = __DIR__ . '/../storage/token.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token tidak ditemukan. Jalankan 01_login.php terlebih dahulu.\n";
    exit(1);
}

$authToken = trim(file_get_contents($tokenFile));
$ovo = new OVOClient($authToken);

try {
    $page = 1;
    $limit = 10;
    
    while (true) {
        $transactions = $ovo->getWalletTransaction($page, $limit);
        
        echo "Halaman {$page} dari " . ceil($transactions['total'] / $limit) . "\n";
        echo "Total transaksi: {$transactions['total']}\n";
        echo str_repeat('=', 60) . "\n\n";
        
        if (empty($transactions['transactions'])) {
            echo "Tidak ada transaksi.\n";
            break;
        }
        
        foreach ($transactions['transactions'] as $tx) {
            $typeIcon = $tx['type'] === 'DEBIT' ? '⬇️ ' : '⬆️ ';
            $amountColor = $tx['type'] === 'DEBIT' ? '-' : '+';
            
            echo $typeIcon . " {$tx['description']}\n";
            echo "   Jumlah: Rp " . number_format($tx['amount'], 0, ',', '.') . "\n";
            echo "   Status: {$tx['status']}\n";
            echo "   Tanggal: " . date('d/m/Y H:i:s', strtotime($tx['date'])) . "\n";
            
            if ($tx['counterpart_name']) {
                echo "   Lawan transaksi: {$tx['counterpart_name']}\n";
            }
            echo "\n";
        }
        
        echo str_repeat('-', 60) . "\n";
        echo "Tekan Enter untuk halaman berikutnya, atau 'q' untuk keluar: ";
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) === 'q') {
            break;
        }
        
        $page++;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
