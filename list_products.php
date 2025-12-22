<?php
// list_products.php - list all active products
require 'db.php';

// optional search query ?q=water
$q = $_GET['q'] ?? '';
$like = "%$q%";

try {
    $stmt = $pdo->prepare(
        "SELECT id, name, description, unit, price, stock
         FROM products
         WHERE active = 1
           AND (name LIKE ? OR description LIKE ?)
         ORDER BY created_at DESC"
    );
    $stmt->execute([$like, $like]);
    $rows = $stmt->fetchAll();

    echo json_encode(['status' => 'ok', 'products' => $rows]);

} catch (Throwable $e) {
    error_log('list_products.php error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal_server_error']);
}
