<?php
// register.php - create account using name, phone, email
require 'db.php'; // db.php lo $pdo ready ga untundi

// Input ni JSON ga expect chestunnam (Postman / mobile app nunchi)
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

// Values tiskovatam
$phoneRaw = trim((string)($input['phone'] ?? ''));
$name     = trim((string)($input['name'] ?? ''));
$email    = trim((string)($input['email'] ?? ''));

// Phone lo digits matrame vundela clean chestam
$phone = preg_replace('/\D+/', '', $phoneRaw);

// Basic validation
if ($phone === '') {
    http_response_code(400);
    echo json_encode(['error' => 'phone is required']);
    exit;
}
if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'name is required']);
    exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid email']);
    exit;
}

try {
    // Already user unna check
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        // Already register ayithe token undakapoina create cheyyi
        if (empty($user['token'])) {
            $newToken = bin2hex(random_bytes(16));
            $pdo->prepare("UPDATE users SET token = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$newToken, $user['id']]);
            $user['token'] = $newToken;
        }

        echo json_encode([
            'status'  => 'ok',
            'message' => 'user already exists',
            'user'    => $user
        ]);
        exit;
    }

    // New user create
    $token = bin2hex(random_bytes(16));
    $ins = $pdo->prepare(
        "INSERT INTO users (name, phone, email, token, created_at, updated_at)
         VALUES (?, ?, ?, ?, NOW(), NOW())"
    );
    $ins->execute([$name, $phone, $email, $token]);
    $id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $newUser = $stmt->fetch();

    http_response_code(201);
    echo json_encode([
        'status'  => 'ok',
        'message' => 'registered',
        'user'    => $newUser
    ]);

} catch (Throwable $e) {
    error_log('register.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
