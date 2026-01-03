<?php

/**
 * Add item to cart - Simplified
 * Expects: user_id, product_id, qty
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['product_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID and Product ID required"]);
    exit;
}

$userId = (int)$data['user_id'];
$productId = (int)$data['product_id'];
$qty = isset($data['qty']) ? (int)$data['qty'] : 1;

try {
    // Check if item already in cart
    $checkQuery = $conn->prepare("SELECT id, qty FROM cart_items WHERE user_id = ? AND product_id = ?");
    $checkQuery->bind_param("ii", $userId, $productId);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        // Update quantity
        $existing = $result->fetch_assoc();
        $newQty = $existing['qty'] + $qty;

        $updateQuery = $conn->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
        $updateQuery->bind_param("ii", $newQty, $existing['id']);
        $updateQuery->execute();
    } else {
        // Insert new item
        $insertQuery = $conn->prepare("INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)");
        $insertQuery->bind_param("iii", $userId, $productId, $qty);
        $insertQuery->execute();
    }

    // Get updated cart count
    $countQuery = $conn->prepare("SELECT SUM(qty) as total FROM cart_items WHERE user_id = ?");
    $countQuery->bind_param("i", $userId);
    $countQuery->execute();
    $countResult = $countQuery->get_result();
    $count = $countResult->fetch_assoc();

    echo json_encode([
        "status" => "ok",
        "message" => "Added to cart",
        "cart_count" => (int)($count['total'] ?? 0)
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to add to cart"
    ]);
}
