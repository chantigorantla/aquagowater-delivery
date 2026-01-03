<?php

/**
 * Get cart items - Simplified
 * Expects: user_id
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID required"]);
    exit;
}

$userId = (int)$data['user_id'];

try {
    $query = $conn->prepare(
        "SELECT ci.id, ci.product_id, ci.qty, p.name, p.size, p.price
         FROM cart_items ci
         LEFT JOIN products p ON ci.product_id = p.id
         WHERE ci.user_id = ?"
    );
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();

    $items = [];
    $subtotal = 0;

    while ($row = $result->fetch_assoc()) {
        $itemTotal = (float)$row['price'] * (int)$row['qty'];
        $subtotal += $itemTotal;

        $items[] = [
            "id" => (int)$row['id'],
            "product_id" => (int)$row['product_id'],
            "name" => $row['name'] ?? "Water Can",
            "size" => $row['size'] ?? "20L",
            "price" => (float)$row['price'],
            "qty" => (int)$row['qty'],
            "total" => $itemTotal
        ];
    }

    $deliveryFee = count($items) > 0 ? 0 : 0; // Free delivery
    $total = $subtotal + $deliveryFee;

    echo json_encode([
        "status" => "ok",
        "items" => $items,
        "subtotal" => $subtotal,
        "delivery_fee" => $deliveryFee,
        "total" => $total
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to load cart"
    ]);
}
