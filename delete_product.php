<?php
// delete_product.php - Delete a product
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;

if ($product_id <= 0 || $partner_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Product ID and Partner ID required'));
    exit;
}

// Verify product belongs to partner
$checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND partner_id = ?");
$checkStmt->bind_param("ii", $product_id, $partner_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Product not found or unauthorized'));
    exit;
}
$checkStmt->close();

// Delete product
$sql = "DELETE FROM products WHERE id = ? AND partner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $partner_id);

if ($stmt->execute()) {
    echo json_encode(array(
        'status' => 'ok',
        'message' => 'Product deleted successfully'
    ));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Failed to delete product'));
}

$stmt->close();
$conn->close();
