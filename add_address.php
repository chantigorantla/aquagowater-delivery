<?php
// add_address.php - save a new delivery address for logged-in user
require 'db.php';

// ---------- helper: try to read Bearer token from headers ----------
function get_bearer_token_from_header(): ?string {
    $auth = null;

    if (function_exists('getallheaders')) {
        $h = getallheaders();
        if (isset($h['Authorization'])) {
            $auth = $h['Authorization'];
        } elseif (isset($h['authorization'])) {
            $auth = $h['authorization'];
        }
    }

    if (!$auth && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!$auth && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
        return trim($m[1]);
    }
    return null;
}

// ---------- read JSON body ----------
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

// 1st try: header lo nunchi token chadavadam
$token = get_bearer_token_from_header();

// 2nd fallback: body lo "token" field unde danini vadukundam
if (!$token && !empty($input['token'])) {
    $token = trim((string)$input['token']);
}

// still no token -> unauthorized
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized (no token found in header or body)']);
    exit;
}

// ---------- find user by token ----------
$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// ---------- address fields ----------
$label        = trim((string)($input['label'] ?? 'Home'));
$address_line = trim((string)($input['address_line'] ?? ''));
$city         = trim((string)($input['city'] ?? ''));
$pincode      = trim((string)($input['pincode'] ?? ''));
$lat          = $input['lat'] ?? null;
$lng          = $input['lng'] ?? null;

if ($address_line === '') {
    http_response_code(400);
    echo json_encode(['error' => 'address_line is required']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO addresses (user_id, label, address_line, city, pincode, lat, lng, is_default, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())"
    );
    $stmt->execute([
        $user['id'],
        $label,
        $address_line,
        $city,
        $pincode,
        $lat,
        $lng
    ]);

    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $addr = $stmt->fetch();

    http_response_code(201);
    echo json_encode(['status' => 'ok', 'address' => $addr]);

} catch (Throwable $e) {
    error_log('add_address.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
