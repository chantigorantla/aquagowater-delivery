<?php
// update_product.php - Update existing product
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db.php';

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;
$name = isset($data['name']) ? trim($data['name']) : '';
$size = isset($data['size']) ? trim($data['size']) : '';
$price = isset($data['price']) ? (int)$data['price'] : 0;
$stock = isset($data['stock']) ? (int)$data['stock'] : 0;
$description = isset($data['description']) ? trim($data['description']) : '';
$image_url = isset($data['image_url']) ? trim($data['image_url']) : null;

// Validation
if ($product_id <= 0 || $partner_id <= 0) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Product ID and Partner ID required']);
    exit;
}

if (empty($name) || $price <= 0) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Name and price are required']);
    exit;
}

// Verify product belongs to partner
$checkStmt = $conn->prepare("SELECT id, image_url FROM products WHERE id = ? AND partner_id = ?");
$checkStmt->bind_param("ii", $product_id, $partner_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Product not found or unauthorized']);
    exit;
}

$existing = $checkResult->fetch_assoc();
$checkStmt->close();

// If no new image provided, keep existing
if ($image_url === null || $image_url === '') {
    $image_url = $existing['image_url'];
}

// Update product with all fields
$sql = "UPDATE products SET name = ?, size = ?, price = ?, stock = ?, description = ?, image_url = ? WHERE id = ? AND partner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiissii", $name, $size, $price, $stock, $description, $image_url, $product_id, $partner_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'status' => 'ok',
        'message' => 'Product updated successfully',
        'product' => [
            'id' => $product_id,
            'name' => $name,
            'size' => $size,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'image_url' => $image_url
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Failed to update product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
