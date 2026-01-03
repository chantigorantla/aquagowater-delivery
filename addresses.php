<?php
require_once 'db.php';
header('Content-Type: application/json');

// Get user_id from request (POST or GET)
$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? intval($data['user_id']) : (isset($_GET['user_id']) ? intval($_GET['user_id']) : 0);

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

// Get all addresses for this user from database
$stmt = $conn->prepare("SELECT id, label, address_line, city, pincode, lat, lng, is_default, created_at FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = [
        'id' => intval($row['id']),
        'label' => $row['label'],
        'address_line' => $row['address_line'],
        'city' => $row['city'],
        'pincode' => $row['pincode'],
        'lat' => floatval($row['lat']),
        'lng' => floatval($row['lng']),
        'is_default' => (bool)$row['is_default'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode([
    'status' => 'ok',
    'addresses' => $addresses
]);

$stmt->close();
$conn->close();
