<?php
// create_order.php - place an order using products + address
require 'db.php';

// ---------- helper: get token from JSON body ----------
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$token = trim((string)($input['token'] ?? ''));

if ($token === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized (no token in body)']);
    exit;
}

// ---------- get user by token ----------
$stmt = $pdo->prepare("SELECT * FROM users WHERE token = ? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid token']);
    exit;
}

// ---------- read order details ----------
$address_id     = (int)($input['address_id'] ?? 0);
$payment_method = trim((string)($input['payment_method'] ?? 'cash'));
$items          = $input['items'] ?? [];

if ($address_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'address_id is required']);
    exit;
}
if (!is_array($items) || empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'items is required']);
    exit;
}

// ---------- start transaction ----------
try {
    $pdo->beginTransaction();

    // Check address belongs to user
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$address_id, $user['id']]);
    $addr = $stmt->fetch();
    if (!$addr) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'invalid address']);
        exit;
    }

    // Calculate subtotal and validate products/stock
    $subtotal = 0.0;
    foreach ($items as $it) {
        $pid = (int)($it['product_id'] ?? 0);
        $qty = (int)($it['qty'] ?? 0);

        if ($pid <= 0 || $qty <= 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'invalid product_id or qty in items']);
            exit;
        }

        // Lock product row
        $ps = $pdo->prepare("SELECT id, price, stock FROM products WHERE id = ? FOR UPDATE");
        $ps->execute([$pid]);
        $prod = $ps->fetch();

        if (!$prod) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => "product $pid not found"]);
            exit;
        }

        if ($prod['stock'] < $qty) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => "insufficient stock for product $pid"]);
            exit;
        }

        $subtotal += $prod['price'] * $qty;
    }

    // Simple charges
    $delivery_fee = 20.00; // flat 20 rs
    $discount     = 0.00;  // no coupon for now
    $total        = max(0, $subtotal - $discount + $delivery_fee);

    // Insert into orders
    $payment_status = ($payment_method === 'cash') ? 'paid' : 'pending';

    $stmt = $pdo->prepare(
        "INSERT INTO orders
            (user_id, address_id, subtotal, delivery_fee, discount, total,
             payment_method, payment_status, order_status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'placed', NOW(), NOW())"
    );

    $stmt->execute([
        $user['id'],
        $address_id,
        $subtotal,
        $delivery_fee,
        $discount,
        $total,
        $payment_method,
        $payment_status
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order_items + reduce stock
    foreach ($items as $it) {
        $pid = (int)$it['product_id'];
        $qty = (int)$it['qty'];

        $ps = $pdo->prepare("SELECT price, stock FROM products WHERE id = ? FOR UPDATE");
        $ps->execute([$pid]);
        $prod = $ps->fetch();

        $price       = $prod['price'];
        $total_price = $price * $qty;

        $insItem = $pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, qty, price, total_price)
             VALUES (?, ?, ?, ?, ?)"
        );
        $insItem->execute([$order_id, $pid, $qty, $price, $total_price]);

        $updStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $updStock->execute([$qty, $pid]);
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'status'   => 'ok',
        'order_id' => $order_id,
        'total'    => $total
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('create_order.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
	