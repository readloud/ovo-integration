<?php
// run.php - File ini diletakkan di root project

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║        OVO INTEGRATION TOOL            ║\n";
echo "╠════════════════════════════════════════╣\n";
echo "║  1. Login OVO                          ║\n";
echo "║  2. Cek Saldo                          ║\n";
echo "║  3. Transfer OVO                       ║\n";
echo "║  4. History Transaksi                  ║\n";
echo "║  5. Profil OVO                         ║\n";
echo "║  6. Notifikasi                         ║\n";
echo "║  7. Keluar                             ║\n";
echo "╚════════════════════════════════════════╝\n";
echo "Pilih menu (1-7): ";

$choice = trim(fgets(STDIN));

$scripts = [
    1 => 'examples/login.php',
    2 => 'examples/balance.php',
    3 => 'examples/transfer.php',
    4 => 'examples/transaction_history.php',
    5 => 'examples/profile.php',
    6 => 'examples/notifications.php',
];

if (isset($scripts[$choice])) {
    echo "\n";
    passthru('php ' . $scripts[$choice]);
} elseif ($choice == 7) {
    echo "Terima kasih!\n";
} else {
    echo "Pilihan tidak valid!\n";
}
