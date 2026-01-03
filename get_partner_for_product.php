<?php

/**
 * Get partner info for a product
 * Used for UPI payments to get partner's UPI ID before creating order
 */

header('Content-Type: application/json');
require_once 'db.php';

// Get product_id from query params
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($productId <= 0) {
    echo json_encode([
        'status' => 'error',
        'error' => 'Product ID is required'
    ]);
    exit;
}

try {
    // Get partner_id from product
    $stmt = $conn->prepare("SELECT partner_id FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode([
            'status' => 'error',
            'error' => 'Product not found'
        ]);
        exit;
    }

    $product = $result->fetch_assoc();
    $partnerId = (int)$product['partner_id'];

    if ($partnerId <= 0) {
        echo json_encode([
            'status' => 'ok',
            'partner_id' => null,
            'partner_name' => 'AquaGo Partner',
            'upi_id' => null
        ]);
        exit;
    }

    // Get partner info
    $partnerStmt = $conn->prepare("SELECT id, name, shop_name, upi_id FROM users WHERE id = ? AND role = 'partner'");
    $partnerStmt->bind_param("i", $partnerId);
    $partnerStmt->execute();
    $partnerResult = $partnerStmt->get_result();

    if ($partnerResult->num_rows == 0) {
        echo json_encode([
            'status' => 'ok',
            'partner_id' => $partnerId,
            'partner_name' => 'AquaGo Partner',
            'upi_id' => null
        ]);
        exit;
    }

    $partner = $partnerResult->fetch_assoc();
    $partnerName = !empty($partner['shop_name']) ? $partner['shop_name'] : $partner['name'];

    echo json_encode([
        'status' => 'ok',
        'partner_id' => (int)$partner['id'],
        'partner_name' => $partnerName,
        'upi_id' => $partner['upi_id']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error' => 'Failed to get partner info: ' . $e->getMessage()
    ]);
}

$conn->close();
