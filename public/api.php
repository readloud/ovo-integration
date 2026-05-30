<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? null;

require_once __DIR__ . '/../vendor/autoload.php';

// Load config if you have one; otherwise set sensible defaults here.
// Edit these constants to match your project's config/base.php values if needed.
define('APP_VERSION', '1.0.0');
define('OS_NAME', 'php');
define('OS_VERSION', phpversion());
define('CLIENT_ID', 'client-id-placeholder');
define('USER_AGENT', 'ovoid-ui/1.0');

// Attempt to require library files directly from src
$possible = [
  __DIR__ . '/../src/OVOID.php',
  __DIR__ . '/../src/OVOClient.php',
];

$found = false;
foreach ($possible as $p) {
  if (file_exists($p)) {
    require_once $p;
    $found = true;
  }
}

if (!$found) {
  echo json_encode(['error' => 'Library files not found. Adjust public/api.php bootstrap.']);
  exit;
}

// Helper to convert responses (objects) to arrays for json_encode()
function toArray($v) {
  if (is_array($v)) {
    return array_map('toArray', $v);
  }
  if (is_object($v)) {
    // Try toJSON / toArray methods first
    if (method_exists($v, 'toArray')) return $v->toArray();
    if (method_exists($v, 'toJson')) return json_decode($v->toJson(), true);
    // fallback: cast public properties
    $arr = [];
    foreach (get_object_vars($v) as $k => $val) $arr[$k] = toArray($val);
    return $arr;
  }
  return $v;
}

function respond($data) {
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  exit;
}

try {
  // Create client instance. Adjust constructor args if your class requires config or token.
  if (class_exists('OVOID')) {
    $client = new OVOID(null);
  } elseif (class_exists('OVOClient')) {
    $client = new OVOClient(null);
  } else {
    respond(['error' => 'Could not find OVOID or OVOClient class.']);
  }

  switch ($action) {
    case 'login':
      $phone = $_POST['phone'] ?? '';
      $device = $_POST['device_id'] ?? null;
      $res = $client->login2FA($phone, $device);
      respond(['ok' => true, 'action' => 'login', 'result' => toArray($res)]);
      break;

    case 'verify':
      $ref = $_POST['refId'] ?? '';
      $otp = $_POST['otp'] ?? '';
      $phone = $_POST['phone2'] ?? '';
      $device = $_POST['device_id2'] ?? '';
      $res = $client->login2FAVerify($ref, $otp, $phone, $device);
      respond(['ok' => true, 'action' => 'verify', 'result' => toArray($res)]);
      break;

    case 'profile':
      $res = $client->getProfile();
      respond(['ok' => true, 'action' => 'profile', 'result' => toArray($res)]);
      break;

    case 'balance':
      $res = $client->getBalance(null);
      respond(['ok' => true, 'action' => 'balance', 'result' => toArray($res)]);
      break;

    case 'transactions':
      $res = $client->getWalletTransaction(1, 10);
      respond(['ok' => true, 'action' => 'transactions', 'result' => toArray($res)]);
      break;

    case 'notifications':
      $res = $client->getAllNotification();
      respond(['ok' => true, 'action' => 'notifications', 'result' => toArray($res)]);
      break;

    case 'transfer':
      $to = $_POST['to'] ?? '';
      $amount = intval($_POST['amount'] ?? 0);
      $message = $_POST['message'] ?? '';
      $res = $client->transferOvo($to, $amount, $message);
      respond(['ok' => true, 'action' => 'transfer', 'result' => toArray($res)]);
      break;

    default:
      respond(['error' => 'No action specified or unknown action']);
  }

} catch (Throwable $e) {
  respond(['error' => true, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
