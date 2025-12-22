<?php
// add_product.php - add a water can product (for admin/testing)
require 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$name  = trim((string)($input['name'] ?? ''));
$desc  = trim((string)($input['description'] ?? ''));
$unit  = trim((string)($input['unit'] ?? ''));
$price = (float)($input['price'] ?? 0);
$stock = (int)($input['stock'] ?? 0);

if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'name is required']);
    exit;
}
if ($price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'price must be > 0']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO products (name, description, unit, price, stock, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute([$name, $desc, $unit, $price, $stock]);

    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    http_response_code(201);
    echo json_encode(['status' => 'ok', 'product' => $product]);

} catch (Throwable $e) {
    error_log('add_product.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
