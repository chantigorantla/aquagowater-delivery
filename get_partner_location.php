<?php
// get_partner_location.php - Get partner's saved location
header('Content-Type: application/json');
require_once 'db.php';

$partner_id = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;

if ($partner_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Partner ID required'));
    exit;
}

$sql = "SELECT lat, lng, service_radius_km FROM users WHERE id = ? AND role = 'partner'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $hasLocation = ($row['lat'] !== null && $row['lng'] !== null && $row['lat'] != 0);

    echo json_encode(array(
        'status' => 'ok',
        'has_location' => $hasLocation,
        'lat' => $hasLocation ? (float)$row['lat'] : null,
        'lng' => $hasLocation ? (float)$row['lng'] : null,
        'service_radius_km' => (int)($row['service_radius_km'] ?? 10)
    ));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Partner not found'));
}

$stmt->close();
$conn->close();
