<?php
// get_partner_products.php - Get products for a specific partner (for edit/delete)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db.php';

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$partner_id = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;

if ($partner_id <= 0) {
    echo json_encode(['success' => false, 'status' => 'error', 'message' => 'Partner ID required']);
    exit;
}

$sql = "SELECT id, name, size, price, image_url, stock, description FROM products WHERE partner_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'size' => $row['size'],
        'price' => (int)$row['price'],
        'stock' => (int)($row['stock'] ?? 0),
        'description' => $row['description'] ?? '',
        'image_url' => $row['image_url'] ?? ''
    ];
}

echo json_encode([
    'success' => true,
    'status' => 'ok',
    'products' => $products,
    'count' => count($products)
]);

$stmt->close();
$conn->close();
