<?php
// examples/06_notifications.php

require_once __DIR__ . '/../vendor/autoload.php';

use Svn\OVOID\OVOClient;

echo "=== NOTIFIKASI OVO ===\n\n";

// Load token
$tokenFile = __DIR__ . '/../storage/token.txt';
if (!file_exists($tokenFile)) {
    echo "❌ Token tidak ditemukan. Jalankan 01_login.php terlebih dahulu.\n";
    exit(1);
}

$authToken = trim(file_get_contents($tokenFile));
$ovo = new OVOClient($authToken);

try {
    $notifications = $ovo->getAllNotifications();
    
    echo "Total notifikasi: {$notifications['total']}\n\n";
    
    foreach ($notifications['notifications'] as $notif) {
        $readStatus = $notif['is_read'] ? '[✓]' : '[ ]';
        echo "{$readStatus} {$notif['title']}\n";
        echo "   {$notif['message']}\n";
        echo "   Waktu: " . date('d/m/Y H:i:s', strtotime($notif['created_at'])) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
