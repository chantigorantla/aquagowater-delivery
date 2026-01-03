<?php
// update_delivery_location.php - Partner updates live GPS location during delivery
header('Content-Type: application/json');
require_once 'db.php';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_POST;
}

// Validate required fields
$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;
$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lng = isset($data['lng']) ? (float)$data['lng'] : null;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

if ($partner_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Partner ID required']);
    exit;
}

if ($lat === null || $lng === null) {
    echo json_encode(['success' => false, 'message' => 'Latitude and longitude required']);
    exit;
}

// Verify order belongs to this partner and is out for delivery
$checkStmt = $conn->prepare("SELECT id, status, partner_id FROM orders WHERE id = ?");
$checkStmt->bind_param("i", $order_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if (!$order = $checkResult->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

if ($order['partner_id'] != $partner_id) {
    echo json_encode(['success' => false, 'message' => 'Order does not belong to this partner']);
    exit;
}

if ($order['status'] !== 'out_for_delivery') {
    echo json_encode(['success' => false, 'message' => 'Order is not out for delivery']);
    exit;
}

$checkStmt->close();

// Check if delivery_tracking table exists, create if not
$tableCheck = $conn->query("SHOW TABLES LIKE 'delivery_tracking'");
if ($tableCheck->num_rows == 0) {
    $createTable = "CREATE TABLE delivery_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        partner_id INT NOT NULL,
        lat DECIMAL(10, 8) NOT NULL,
        lng DECIMAL(11, 8) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_order (order_id),
        INDEX idx_order (order_id),
        INDEX idx_partner (partner_id)
    )";
    $conn->query($createTable);
}

// Insert or update location
$sql = "INSERT INTO delivery_tracking (order_id, partner_id, lat, lng, updated_at) 
        VALUES (?, ?, ?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng), updated_at = NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iidd", $order_id, $partner_id, $lat, $lng);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Location updated',
        'lat' => $lat,
        'lng' => $lng,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update location: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
