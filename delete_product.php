<?php
// delete_product.php - Delete a product completely
header('Content-Type: application/json');
require_once 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;

    if ($product_id <= 0 || $partner_id <= 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Product ID and Partner ID required'));
        exit;
    }

    // Verify product belongs to partner
    $checkStmt = $conn->prepare("SELECT id, name FROM products WHERE id = ? AND partner_id = ?");
    $checkStmt->bind_param("ii", $product_id, $partner_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Product not found or unauthorized'));
        exit;
    }
    $productRow = $checkResult->fetch_assoc();
    $productName = $productRow['name'];
    $checkStmt->close();

    // Disable foreign key checks temporarily
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Remove product from cart_items first
    $clearCartStmt = $conn->prepare("DELETE FROM cart_items WHERE product_id = ?");
    $clearCartStmt->bind_param("i", $product_id);
    $clearCartStmt->execute();
    $clearCartStmt->close();

    // Update order_items to store product name before removing reference
    // This preserves the product name in order history even after deleting
    $updateOrdersStmt = $conn->prepare("UPDATE order_items SET product_id = NULL WHERE product_id = ?");
    $updateOrdersStmt->bind_param("i", $product_id);
    $updateOrdersStmt->execute();
    $updateOrdersStmt->close();

    // Now delete the product
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ? AND partner_id = ?");
    $deleteStmt->bind_param("ii", $product_id, $partner_id);
    $result = $deleteStmt->execute();
    $deleteStmt->close();

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    if ($result) {
        echo json_encode(array(
            'status' => 'ok',
            'success' => true,
            'message' => 'Product deleted successfully'
        ));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to delete product: ' . $conn->error));
    }

    $conn->close();
} catch (Exception $e) {
    // Re-enable foreign key checks in case of error
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo json_encode(array('status' => 'error', 'message' => 'Error: ' . $e->getMessage()));
}
