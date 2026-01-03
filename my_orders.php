<?php

/**
 * Get user's orders - Simplified without token
 * Expects: user_id
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

$userId = (int)$data['user_id'];

try {
    // Get orders (using correct column names)
    $orderQuery = $conn->prepare(
        "SELECT id, total, status, created_at 
         FROM orders 
         WHERE user_id = ? 
         ORDER BY created_at DESC
         LIMIT 10"
    );
    $orderQuery->bind_param("i", $userId);
    $orderQuery->execute();
    $orderResult = $orderQuery->get_result();

    $orders = [];
    while ($order = $orderResult->fetch_assoc()) {
        $orderId = (int)$order['id'];

        // Get order items
        $itemQuery = $conn->prepare(
            "SELECT oi.product_id, oi.qty, oi.price, oi.total_price, p.name as product_name
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?"
        );
        $itemQuery->bind_param("i", $orderId);
        $itemQuery->execute();
        $itemResult = $itemQuery->get_result();

        $items = [];
        while ($item = $itemResult->fetch_assoc()) {
            $items[] = [
                "product_id" => (int)$item['product_id'],
                "product_name" => $item['product_name'] ?? "Water Can",
                "qty" => (int)$item['qty'],
                "price" => (float)$item['price'],
                "total_price" => (float)$item['total_price']
            ];
        }

        $orders[] = [
            "id" => $orderId,
            "total_amount" => (float)$order['total'],
            "status" => $order['status'],
            "created_at" => $order['created_at'],
            "items" => $items
        ];
    }

    echo json_encode([
        "status" => "ok",
        "orders" => $orders
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to load orders"
    ]);
}
