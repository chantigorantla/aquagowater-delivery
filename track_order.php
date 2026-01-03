<?php

/**
 * Track Order - Get order status and details
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

$userId = (int)$data['user_id'];
$orderId = isset($data['order_id']) ? (int)$data['order_id'] : null;

// If order_id provided, get specific order, otherwise get latest active order
if ($orderId) {
    $orderQuery = $conn->prepare("
        SELECT o.id, o.total, o.status, o.created_at, o.address_id,
               a.address_line, a.city, a.pincode
        FROM orders o
        LEFT JOIN addresses a ON a.id = o.address_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $orderQuery->bind_param("ii", $orderId, $userId);
} else {
    // Get the most recent non-delivered order
    $orderQuery = $conn->prepare("
        SELECT o.id, o.total, o.status, o.created_at, o.address_id,
               a.address_line, a.city, a.pincode
        FROM orders o
        LEFT JOIN addresses a ON a.id = o.address_id
        WHERE o.user_id = ? AND o.status != 'delivered' AND o.status != 'cancelled'
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $orderQuery->bind_param("i", $userId);
}

$orderQuery->execute();
$orderResult = $orderQuery->get_result();

if ($orderResult->num_rows == 0) {
    echo json_encode([
        "status" => "ok",
        "has_active_order" => false,
        "message" => "No active orders to track"
    ]);
    exit;
}

$order = $orderResult->fetch_assoc();

// Define tracking steps based on status
$trackingSteps = [
    ["step" => "Order Placed", "completed" => true, "time" => $order['created_at']],
    ["step" => "Order Confirmed", "completed" => in_array($order['status'], ['confirmed', 'processing', 'dispatched', 'out_for_delivery', 'delivered']), "time" => null],
    ["step" => "Dispatched", "completed" => in_array($order['status'], ['dispatched', 'out_for_delivery', 'delivered']), "time" => null],
    ["step" => "Out for Delivery", "completed" => in_array($order['status'], ['out_for_delivery', 'delivered']), "time" => null],
    ["step" => "Delivered", "completed" => $order['status'] == 'delivered', "time" => null]
];

// Get order items
$itemsQuery = $conn->prepare("
    SELECT oi.qty, oi.price, p.name as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsQuery->bind_param("i", $order['id']);
$itemsQuery->execute();
$itemsResult = $itemsQuery->get_result();

$items = [];
while ($item = $itemsResult->fetch_assoc()) {
    $items[] = [
        "product_name" => $item['product_name'] ?? "Water Can",
        "qty" => (int)$item['qty'],
        "price" => (float)$item['price']
    ];
}

// Build address text from joined address or fallback
$addressText = "Address not available";
if (!empty($order['address_line'])) {
    $addressText = $order['address_line'];
    if (!empty($order['city'])) $addressText .= ", " . $order['city'];
    if (!empty($order['pincode'])) $addressText .= " - " . $order['pincode'];
} else {
    // Fallback: get user's default address
    $addressQuery = $conn->prepare("
        SELECT address_line, city, pincode 
        FROM addresses 
        WHERE user_id = ? 
        ORDER BY is_default DESC LIMIT 1
    ");
    $addressQuery->bind_param("i", $userId);
    $addressQuery->execute();
    $addressResult = $addressQuery->get_result();
    if ($addressResult->num_rows > 0) {
        $addr = $addressResult->fetch_assoc();
        $addressText = $addr['address_line'] . ", " . $addr['city'] . " - " . $addr['pincode'];
    }
}

echo json_encode([
    "status" => "ok",
    "has_active_order" => true,
    "order" => [
        "id" => (int)$order['id'],
        "total_amount" => (float)$order['total'],
        "status" => $order['status'],
        "created_at" => $order['created_at'],
        "delivery_address" => $addressText
    ],
    "items" => $items,
    "tracking" => $trackingSteps
]);
