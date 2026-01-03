<?php

/**
 * Create Order - With partner assignment and notifications
 * Expects: user_id, address_id, payment_method, partner_id, items[]
 */
include "db.php";
include "send_push_notification.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

$userId = (int)$data['user_id'];
$addressId = isset($data['address_id']) ? (int)$data['address_id'] : 1;
$paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : 'COD';
$partnerId = isset($data['partner_id']) ? (int)$data['partner_id'] : null;
$items = isset($data['items']) ? $data['items'] : [];
$deliveryDate = isset($data['delivery_date']) ? $data['delivery_date'] : 'Today';
$deliverySlot = isset($data['delivery_slot']) ? $data['delivery_slot'] : 'Morning (6 AM - 9 AM)';

// If no items provided, use cart items
if (empty($items)) {
    $cartQuery = $conn->prepare("SELECT product_id, qty FROM cart_items WHERE user_id = ?");
    $cartQuery->bind_param("i", $userId);
    $cartQuery->execute();
    $cartResult = $cartQuery->get_result();

    while ($cartItem = $cartResult->fetch_assoc()) {
        $items[] = [
            'product_id' => (int)$cartItem['product_id'],
            'qty' => (int)$cartItem['qty']
        ];
    }
}

// If still no items, create a demo order
if (empty($items)) {
    $items = [
        ['product_id' => 1, 'qty' => 1]
    ];
}

// If no partner_id provided, try to get it from the first product
if (!$partnerId && !empty($items)) {
    $firstProductId = (int)$items[0]['product_id'];
    $partnerQuery = $conn->prepare("SELECT partner_id FROM products WHERE id = ?");
    $partnerQuery->bind_param("i", $firstProductId);
    $partnerQuery->execute();
    $partnerResult = $partnerQuery->get_result();
    if ($row = $partnerResult->fetch_assoc()) {
        $partnerId = (int)$row['partner_id'];
    }
}

try {
    // Calculate total
    $total = 0;
    $orderItems = [];
    $productName = '';
    $totalQty = 0;
    $itemsList = [];

    foreach ($items as $item) {
        $productId = (int)$item['product_id'];
        $qty = (int)$item['qty'];

        // Get product price
        $prodQuery = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $prodQuery->bind_param("i", $productId);
        $prodQuery->execute();
        $prodResult = $prodQuery->get_result();

        if ($prodResult->num_rows > 0) {
            $product = $prodResult->fetch_assoc();
            $price = (float)$product['price'];
            $itemTotal = $price * $qty;
            $total += $itemTotal;
            $totalQty += $qty;

            // Use first product name for order
            if (empty($productName)) {
                $productName = $product['name'];
            }

            $itemsList[] = $qty . "x " . $product['name'];

            $orderItems[] = [
                'product_id' => $productId,
                'qty' => $qty,
                'price' => $price,
                'total_price' => $itemTotal
            ];
        }
    }

    // If no address_id provided, try to get user's default address
    if ($addressId <= 0) {
        $addrQuery = $conn->prepare("SELECT id FROM addresses WHERE user_id = ? ORDER BY is_default DESC LIMIT 1");
        $addrQuery->bind_param("i", $userId);
        $addrQuery->execute();
        $addrResult = $addrQuery->get_result();
        if ($addr = $addrResult->fetch_assoc()) {
            $addressId = (int)$addr['id'];
        }
    }

    // Get customer name for partner notification
    $customerName = "Customer";
    $custQuery = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $custQuery->bind_param("i", $userId);
    $custQuery->execute();
    $custResult = $custQuery->get_result();
    if ($cust = $custResult->fetch_assoc()) {
        $customerName = $cust['name'];
    }

    // Get address for detailed notification
    $addressText = "";
    if ($addressId > 0) {
        $addrDetailQuery = $conn->prepare("SELECT address_line, city FROM addresses WHERE id = ?");
        $addrDetailQuery->bind_param("i", $addressId);
        $addrDetailQuery->execute();
        $addrDetailResult = $addrDetailQuery->get_result();
        if ($addrDetail = $addrDetailResult->fetch_assoc()) {
            $addressText = $addrDetail['address_line'];
            if (!empty($addrDetail['city'])) {
                $addressText .= ", " . $addrDetail['city'];
            }
        }
    }

    // Insert order WITH partner_id, address_id, AND delivery slot
    $status = 'pending';
    $insertOrder = $conn->prepare(
        "INSERT INTO orders (user_id, partner_id, address_id, product_name, quantity, total, status, payment_method, payment_status, delivery_date, delivery_slot, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())"
    );
    $insertOrder->bind_param("iiisidssss", $userId, $partnerId, $addressId, $productName, $totalQty, $total, $status, $paymentMethod, $deliveryDate, $deliverySlot);
    $insertOrder->execute();

    $orderId = $conn->insert_id;

    // Insert order items
    foreach ($orderItems as $oi) {
        $insertItem = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, qty, price, total_price) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $insertItem->bind_param(
            "iiidd",
            $orderId,
            $oi['product_id'],
            $oi['qty'],
            $oi['price'],
            $oi['total_price']
        );
        $insertItem->execute();
    }

    // Clear cart after order
    $clearCart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $clearCart->bind_param("i", $userId);
    $clearCart->execute();

    // Get partner info for UPI payment
    $partnerUpi = null;
    $partnerName = "AquaGo Partner";
    if ($partnerId) {
        $partnerInfoQuery = $conn->prepare("SELECT name, shop_name, upi_id FROM users WHERE id = ? AND role = 'partner'");
        $partnerInfoQuery->bind_param("i", $partnerId);
        $partnerInfoQuery->execute();
        $partnerInfoResult = $partnerInfoQuery->get_result();
        if ($partnerInfo = $partnerInfoResult->fetch_assoc()) {
            $partnerName = !empty($partnerInfo['shop_name']) ? $partnerInfo['shop_name'] : $partnerInfo['name'];
            $partnerUpi = $partnerInfo['upi_id'];
        }
    }

    // =============================================
    // SEND NOTIFICATIONS
    // =============================================

    $itemsListStr = implode(", ", $itemsList);

    // Notify CUSTOMER - Order placed confirmation
    notifyUser(
        $conn,
        $userId,
        "Order Placed! 🎉",                    // Push title (brief)
        "Your order #$orderId is confirmed",   // Push body (brief)
        "Order Placed Successfully",           // In-app title
        "Your order #$orderId has been placed successfully.\n\nItems: $itemsListStr\nTotal: ₹$total\nPayment: $paymentMethod\n\nThe partner will confirm your order shortly.", // In-app body (detailed)
        ['order_id' => $orderId],
        'order_update'
    );

    // Notify PARTNER - New order available
    if ($partnerId) {
        notifyUser(
            $conn,
            $partnerId,
            "New Order! 📦",                       // Push title (brief)
            "Order #$orderId from $customerName", // Push body (brief)
            "New Order Received",                  // In-app title
            "New order #$orderId from $customerName.\n\nItems: $itemsListStr\nTotal: ₹$total\nPayment: $paymentMethod\nDelivery Address: $addressText\n\nPlease accept or reject this order.", // In-app body (detailed)
            ['order_id' => $orderId],
            'new_order'
        );
    }

    echo json_encode([
        "status" => "ok",
        "order_id" => $orderId,
        "partner_id" => $partnerId,
        "partner_name" => $partnerName,
        "partner_upi" => $partnerUpi,
        "total" => $total,
        "message" => "Order placed successfully"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to create order: " . $e->getMessage()
    ]);
}
