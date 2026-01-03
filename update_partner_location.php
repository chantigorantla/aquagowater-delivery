<?php
// update_partner_location.php - Update partner lat/lng location
header('Content-Type: application/json');
require_once 'db.php';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Also support POST form data
if (empty($data)) {
    $data = $_POST;
}

// Validate
$partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;
$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lng = isset($data['lng']) ? (float)$data['lng'] : null;
$address = isset($data['address']) ? trim($data['address']) : null;
$service_radius = isset($data['service_radius_km']) ? (int)$data['service_radius_km'] : 10;

if ($partner_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Partner ID required'));
    exit;
}

if ($lat === null || $lng === null) {
    echo json_encode(array('success' => false, 'message' => 'Latitude and longitude required'));
    exit;
}

// Verify user is a partner
$checkStmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
$checkStmt->bind_param("i", $partner_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if (!$row = $checkResult->fetch_assoc()) {
    echo json_encode(array('success' => false, 'message' => 'Partner not found'));
    exit;
}

if ($row['role'] !== 'partner') {
    echo json_encode(array('success' => false, 'message' => 'User is not a partner'));
    exit;
}

$checkStmt->close();

// Update location
$sql = "UPDATE users SET lat = ?, lng = ?, service_radius_km = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ddii", $lat, $lng, $service_radius, $partner_id);

if ($stmt->execute()) {
    echo json_encode(array(
        'status' => 'ok',
        'message' => 'Location updated successfully',
        'lat' => $lat,
        'lng' => $lng,
        'service_radius_km' => $service_radius
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Failed to update location: ' . $stmt->error
    ));
}

$stmt->close();
$conn->close();
