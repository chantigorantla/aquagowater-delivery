<?php

/**
 * Cancel Order
 * Cancels an order by updating its status to 'cancelled'
 * Restores items to cart if needed
 * Expects: order_id, reason (optional)
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['order_id'])) {
    echo json_encode(["status" => "error", "error" => "Order ID is required"]);
    exit;
}

$orderId = (int)$data['order_id'];
$reason = isset($data['reason']) ? $data['reason'] : 'Cancelled by user';

try {
    // Get order details first
    $orderQuery = $conn->prepare("SELECT id, user_id, status FROM orders WHERE id = ?");
    $orderQuery->bind_param("i", $orderId);
    $orderQuery->execute();
    $orderResult = $orderQuery->get_result();

    if ($orderResult->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "Order not found"]);
        exit;
    }

    $order = $orderResult->fetch_assoc();

    // Check if order can be cancelled (not already delivered or cancelled)
    if ($order['status'] == 'delivered') {
        echo json_encode(["status" => "error", "error" => "Cannot cancel a delivered order"]);
        exit;
    }

    if ($order['status'] == 'cancelled') {
        echo json_encode(["status" => "ok", "message" => "Order already cancelled"]);
        exit;
    }

    // Update order status to cancelled
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $updateStmt->bind_param("i", $orderId);
    $updateStmt->execute();

    // Optionally restore items to cart
    // Get order items and add back to cart
    $itemsQuery = $conn->prepare("SELECT product_id, qty FROM order_items WHERE order_id = ?");
    $itemsQuery->bind_param("i", $orderId);
    $itemsQuery->execute();
    $itemsResult = $itemsQuery->get_result();

    $userId = $order['user_id'];

    while ($item = $itemsResult->fetch_assoc()) {
        // Check if item already in cart
        $checkCart = $conn->prepare("SELECT id, qty FROM cart_items WHERE user_id = ? AND product_id = ?");
        $checkCart->bind_param("ii", $userId, $item['product_id']);
        $checkCart->execute();
        $cartResult = $checkCart->get_result();

        if ($cartResult->num_rows > 0) {
            // Update quantity
            $cartItem = $cartResult->fetch_assoc();
            $newQty = $cartItem['qty'] + $item['qty'];
            $updateCart = $conn->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
            $updateCart->bind_param("ii", $newQty, $cartItem['id']);
            $updateCart->execute();
        } else {
            // Insert new cart item
            $insertCart = $conn->prepare("INSERT INTO cart_items (user_id, product_id, qty) VALUES (?, ?, ?)");
            $insertCart->bind_param("iii", $userId, $item['product_id'], $item['qty']);
            $insertCart->execute();
        }
    }

    echo json_encode([
        "status" => "ok",
        "message" => "Order cancelled successfully",
        "order_id" => $orderId,
        "reason" => $reason
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to cancel order: " . $e->getMessage()
    ]);
}
