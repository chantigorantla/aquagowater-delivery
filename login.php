<?php
// login.php - login using phone number only
require 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$phoneRaw = trim((string)($input['phone'] ?? ''));
$phone    = preg_replace('/\D+/', '', $phoneRaw);

if ($phone === '') {
    http_response_code(400);
    echo json_encode(['error' => 'phone required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'user not found']);
        exit;
    }

    if (empty($user['token'])) {
        $newToken = bin2hex(random_bytes(16));
        $pdo->prepare("UPDATE users SET token = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$newToken, $user['id']]);
        $user['token'] = $newToken;
    }

    echo json_encode([
        'status' => 'ok',
        'user'   => $user
    ]);

} catch (Throwable $e) {
    error_log('login.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
