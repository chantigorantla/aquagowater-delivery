<?php

/**
 * Update cart item quantity
 * Expects: user_id, cart_item_id or product_id, qty
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID required"]);
    exit;
}

$userId = (int)$data['user_id'];
$cartItemId = isset($data['cart_item_id']) ? (int)$data['cart_item_id'] : null;
$productId = isset($data['product_id']) ? (int)$data['product_id'] : null;
$qty = isset($data['qty']) ? (int)$data['qty'] : 1;

if (!$cartItemId && !$productId) {
    echo json_encode(["status" => "error", "error" => "cart_item_id or product_id required"]);
    exit;
}

try {
    if ($qty <= 0) {
        // Remove item if qty is 0 or less
        if ($cartItemId) {
            $deleteQuery = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
            $deleteQuery->bind_param("ii", $cartItemId, $userId);
        } else {
            $deleteQuery = $conn->prepare("DELETE FROM cart_items WHERE product_id = ? AND user_id = ?");
            $deleteQuery->bind_param("ii", $productId, $userId);
        }
        $deleteQuery->execute();

        echo json_encode([
            "status" => "ok",
            "message" => "Item removed from cart"
        ]);
    } else {
        // Update quantity
        if ($cartItemId) {
            $updateQuery = $conn->prepare("UPDATE cart_items SET qty = ? WHERE id = ? AND user_id = ?");
            $updateQuery->bind_param("iii", $qty, $cartItemId, $userId);
        } else {
            $updateQuery = $conn->prepare("UPDATE cart_items SET qty = ? WHERE product_id = ? AND user_id = ?");
            $updateQuery->bind_param("iii", $qty, $productId, $userId);
        }
        $updateQuery->execute();

        echo json_encode([
            "status" => "ok",
            "message" => "Cart updated",
            "qty" => $qty
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to update cart"
    ]);
}
