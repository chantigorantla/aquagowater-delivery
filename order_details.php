<?php

/**
 * Get Order Details with items and delivery address
 * Expects: order_id (and optionally user_id for validation)
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Get order_id from POST body
$orderId = isset($data['order_id']) ? (int)$data['order_id'] : 0;

// Also support GET for testing
if ($orderId <= 0 && isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
}

if ($orderId <= 0) {
    echo json_encode(["status" => "error", "error" => "Order ID is required"]);
    exit;
}

try {
    // Get order with address details and customer name
    $orderQuery = $conn->prepare(
        "SELECT o.id, o.user_id, o.partner_id, o.address_id, o.product_name, o.quantity, o.total, 
                o.status, o.payment_method, o.payment_status, o.created_at,
                o.delivery_date, o.delivery_slot,
                a.address_line, a.city, a.pincode, a.label as address_label,
                u.name as customer_name
         FROM orders o
         LEFT JOIN addresses a ON a.id = o.address_id
         LEFT JOIN users u ON u.id = o.user_id
         WHERE o.id = ?"
    );

    if (!$orderQuery) {
        // Try simpler query without address join (if address_id column doesn't exist)
        $orderQuery = $conn->prepare(
            "SELECT o.id, o.user_id, o.partner_id, o.product_name, o.quantity, o.total, 
                    o.status, o.payment_method, o.payment_status, o.created_at,
                    a.address_line, a.city, a.pincode, a.label as address_label,
                    u.name as customer_name
             FROM orders o
             LEFT JOIN addresses a ON a.user_id = o.user_id AND a.is_default = 1
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = ?"
        );
    }

    $orderQuery->bind_param("i", $orderId);
    $orderQuery->execute();
    $orderResult = $orderQuery->get_result();

    if ($orderResult->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "Order not found"]);
        exit;
    }

    $order = $orderResult->fetch_assoc();

    // Get order items
    $itemsQuery = $conn->prepare(
        "SELECT oi.id, oi.product_id, oi.qty, oi.price, oi.total_price,
                p.name as product_name
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?"
    );

    $items = [];
    if ($itemsQuery) {
        $itemsQuery->bind_param("i", $orderId);
        $itemsQuery->execute();
        $itemsResult = $itemsQuery->get_result();

        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = [
                "id" => (int)$item['id'],
                "product_id" => (int)$item['product_id'],
                "product_name" => $item['product_name'] ?? "Water Can",
                "qty" => (int)$item['qty'],
                "price" => (float)$item['price'],
                "total_price" => (float)$item['total_price']
            ];
        }
    }

    // If no items found, add a fallback item from order data
    if (empty($items)) {
        $items[] = [
            "id" => 0,
            "product_id" => 0,
            "product_name" => $order['product_name'] ?? "Water Can",
            "qty" => (int)($order['quantity'] ?? 1),
            "price" => (float)($order['total'] ?? 0) / max(1, (int)($order['quantity'] ?? 1)),
            "total_price" => (float)($order['total'] ?? 0)
        ];
    }

    // If no address in join, try to get user's default address
    if (empty($order['address_line'])) {
        $userId = (int)$order['user_id'];
        $addrQuery = $conn->prepare(
            "SELECT address_line, city, pincode, label 
             FROM addresses 
             WHERE user_id = ? 
             ORDER BY is_default DESC, id DESC LIMIT 1"
        );
        if ($addrQuery) {
            $addrQuery->bind_param("i", $userId);
            $addrQuery->execute();
            $addrResult = $addrQuery->get_result();
            if ($addr = $addrResult->fetch_assoc()) {
                $order['address_line'] = $addr['address_line'];
                $order['city'] = $addr['city'];
                $order['pincode'] = $addr['pincode'];
                $order['address_label'] = $addr['label'];
            }
        }
    }

    // Build combined address
    $fullAddress = trim(($order['address_line'] ?? '') . ', ' . ($order['city'] ?? ''));
    if (!empty($order['pincode'])) {
        $fullAddress .= ' - ' . $order['pincode'];
    }
    $fullAddress = trim($fullAddress, ', ');

    // Build response
    echo json_encode([
        "status" => "ok",
        "order" => [
            "id" => (int)$order['id'],
            "order_id" => "#AQ" . $order['id'],
            "user_id" => (int)$order['user_id'],
            "partner_id" => (int)($order['partner_id'] ?? 0),
            "product_name" => $order['product_name'] ?? "",
            "quantity" => (int)($order['quantity'] ?? 0),
            "total" => (float)$order['total'],
            "status" => $order['status'] ?? "pending",
            "payment_method" => $order['payment_method'] ?? "COD",
            "payment_status" => $order['payment_status'] ?? "pending",
            "created_at" => $order['created_at'] ?? "",
            "customer_name" => $order['customer_name'] ?? "Customer",
            "address" => $fullAddress,
            "address_line" => $order['address_line'] ?? "",
            "city" => $order['city'] ?? "",
            "pincode" => $order['pincode'] ?? "",
            "address_label" => $order['address_label'] ?? "",
            "delivery_date" => $order['delivery_date'] ?? "Today",
            "delivery_slot" => $order['delivery_slot'] ?? "Morning (6 AM - 9 AM)",
            "items" => $items
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to load order details: " . $e->getMessage()
    ]);
}
