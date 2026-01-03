<?php

/**
 * Get Partner Profile
 * Expects: user_id
 * Returns: Partner profile including UPI ID, shop name, phone
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Also accept GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
} else if (isset($data['user_id'])) {
    $userId = (int)$data['user_id'];
} else {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

// Get user profile
$stmt = $conn->prepare("SELECT id, name, shop_name, email, phone, upi_id, role, is_online, lat, lng, service_radius_km FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => "error", "error" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();

echo json_encode([
    "status" => "ok",
    "profile" => [
        "id" => (int)$user['id'],
        "name" => $user['name'],
        "shop_name" => $user['shop_name'] ?: "",
        "email" => $user['email'],
        "phone" => $user['phone'] ?: "",
        "upi_id" => $user['upi_id'] ?: "",
        "role" => $user['role'],
        "is_online" => (bool)$user['is_online'],
        "lat" => $user['lat'] ? (float)$user['lat'] : null,
        "lng" => $user['lng'] ? (float)$user['lng'] : null,
        "service_radius_km" => (int)$user['service_radius_km']
    ]
]);
