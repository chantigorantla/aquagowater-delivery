<?php
// add_product.php - Add a new product for partner
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

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Support both user_id and partner_id
$partner_id = isset($input['partner_id']) ? (int)$input['partner_id'] : 0;
if ($partner_id <= 0) {
    $partner_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
}

$name = isset($input['name']) ? trim($input['name']) : '';
$size = isset($input['size']) ? trim($input['size']) : '';
$price = isset($input['price']) ? (float)$input['price'] : 0;
$stock = isset($input['stock']) ? (int)$input['stock'] : 0;
$description = isset($input['description']) ? trim($input['description']) : '';
$image_url = isset($input['image_url']) ? trim($input['image_url']) : '';

// Validation
if ($partner_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Partner ID required']);
    exit;
}

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Product name is required']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
    exit;
}

// Insert product - table has: id, partner_id, name, size, price, description, image_url, stock
// Note: price is INT, stock is INT
$sql = "INSERT INTO products (partner_id, name, size, price, description, image_url, stock) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // If description column doesn't exist, try simpler insert
    $sql = "INSERT INTO products (partner_id, name, size, price, image_url, stock) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("issisi", $partner_id, $name, $size, $price, $image_url, $stock);
} else {
    $stmt->bind_param("issiisi", $partner_id, $name, $size, $price, $description, $image_url, $stock);
}

if ($stmt->execute()) {
    $product_id = $conn->insert_id;

    echo json_encode([
        'success' => true,
        'status' => 'ok',
        'message' => 'Product added successfully',
        'product' => [
            'id' => $product_id,
            'partner_id' => $partner_id,
            'name' => $name,
            'size' => $size,
            'price' => $price,
            'image_url' => $image_url
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
