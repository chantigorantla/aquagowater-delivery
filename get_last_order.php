<?php

/**
 * Get user's last order for Quick Reorder feature
 * Returns the products from the most recent order
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate user_id
if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

$userId = (int)$data['user_id'];

// Get the user's most recent order (using correct column name 'total')
$orderQuery = $conn->prepare("
    SELECT o.id, o.total, o.status, o.created_at 
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 1
");
$orderQuery->bind_param("i", $userId);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();

if ($orderResult->num_rows == 0) {
    echo json_encode([
        "status" => "ok",
        "has_previous_order" => false,
        "message" => "No previous orders found"
    ]);
    exit;
}

$order = $orderResult->fetch_assoc();
$orderId = $order['id'];

// Get order items with product details
$itemsQuery = $conn->prepare("
    SELECT oi.id, oi.product_id, oi.qty, oi.price, oi.total_price,
           p.name as product_name, p.size as product_size
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsQuery->bind_param("i", $orderId);
$itemsQuery->execute();
$itemsResult = $itemsQuery->get_result();

$items = [];
while ($item = $itemsResult->fetch_assoc()) {
    $items[] = [
        "id" => (int)$item['id'],
        "product_id" => (int)$item['product_id'],
        "product_name" => $item['product_name'] ?? "Water Can",
        "product_size" => $item['product_size'] ?? "20L",
        "qty" => (int)$item['qty'],
        "price" => (float)$item['price'],
        "total_price" => (float)$item['total_price']
    ];
}

echo json_encode([
    "status" => "ok",
    "has_previous_order" => true,
    "order" => [
        "id" => (int)$order['id'],
        "total_amount" => (float)$order['total'],  // Map 'total' to 'total_amount' for API consistency
        "status" => $order['status'],
        "created_at" => $order['created_at']
    ],
    "items" => $items
]);
