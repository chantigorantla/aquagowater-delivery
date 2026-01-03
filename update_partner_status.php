<?php
// update_partner_status.php - Update partner online/offline status
header('Content-Type: application/json');
require_once 'db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Also support form POST
if (empty($data)) {
    $data = $_POST;
}

// Support GET for fetching status
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $partner_id = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;

    if ($partner_id <= 0) {
        echo json_encode(array('success' => false, 'message' => 'Partner ID required'));
        exit;
    }

    $sql = "SELECT is_online FROM users WHERE id = ? AND role = 'partner'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(array(
            'success' => true,
            'is_online' => (bool)$row['is_online']
        ));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Partner not found'));
    }

    $stmt->close();
    $conn->close();
    exit;
}

// POST - Update status
$partner_id = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;
$is_online = isset($data['is_online']) ? (bool)$data['is_online'] : true;

// Validate
if ($partner_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Partner ID required'));
    exit;
}

// Update status
$online_val = $is_online ? 1 : 0;
$sql = "UPDATE users SET is_online = ? WHERE id = ? AND role = 'partner'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $online_val, $partner_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(array(
            'success' => true,
            'message' => $is_online ? 'You are now Online' : 'You are now Offline',
            'is_online' => $is_online
        ));
    } else {
        echo json_encode(array(
            'success' => true,
            'message' => 'Status unchanged',
            'is_online' => $is_online
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Failed to update status: ' . $stmt->error
    ));
}

$stmt->close();
$conn->close();
