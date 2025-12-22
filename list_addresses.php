<?php
// list_addresses.php - list all addresses for logged-in user
require 'db.php';

function get_bearer_token(): ?string {
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        $auth = $h['Authorization'] ?? ($h['authorization'] ?? null);
    } else {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
    }
    if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $m)) return trim($m[1]);
    return null;
}

$token = get_bearer_token();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, label, address_line, city, pincode, lat, lng, is_default
         FROM addresses
         WHERE user_id = ?
         ORDER BY created_at DESC"
    );
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll();

    echo json_encode(['status' => 'ok', 'addresses' => $rows]);

} catch (Throwable $e) {
    error_log('list_addresses.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
