<?php
// my_orders.php - list all orders for logged-in user
require 'db.php';

// ---------- read JSON body ----------
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$token = trim((string)($input['token'] ?? ''));

if ($token === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized (no token)']);
    exit;
}

// ---------- get user ----------
$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

try {
    // Get orders
    $stmt = $pdo->prepare(
        "SELECT id, address_id, subtotal, delivery_fee, discount, total,
                payment_method, payment_status, order_status, created_at
         FROM orders
         WHERE user_id = ?
         ORDER BY created_at DESC"
    );
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();

    // Attach order items to each order
    foreach ($orders as &$o) {
        $stItems = $pdo->prepare(
            "SELECT oi.product_id, p.name, oi.qty, oi.price, oi.total_price
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?"
        );
        $stItems->execute([$o['id']]);
        $o['items'] = $stItems->fetchAll();
    }

    echo json_encode(['status' => 'ok', 'orders' => $orders]);

} catch (Throwable $e) {
    error_log('my_orders.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
