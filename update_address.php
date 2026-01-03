<?php

/**
 * Update existing address
 * Expects: user_id, address_id, fields to update
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['address_id'])) {
    echo json_encode(["status" => "error", "error" => "user_id and address_id required"]);
    exit;
}

$userId = (int)$data['user_id'];
$addressId = (int)$data['address_id'];
$label = isset($data['label']) ? trim($data['label']) : null;
$addressLine = isset($data['address_line']) ? trim($data['address_line']) : null;
$city = isset($data['city']) ? trim($data['city']) : null;
$pincode = isset($data['pincode']) ? trim($data['pincode']) : null;
$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lng = isset($data['lng']) ? (float)$data['lng'] : null;
$isDefault = isset($data['is_default']) ? (bool)$data['is_default'] : null;

try {
    // Build update query
    $updates = [];
    $params = [];
    $types = "";

    if ($label) {
        $updates[] = "label = ?";
        $params[] = $label;
        $types .= "s";
    }
    if ($addressLine) {
        $updates[] = "address_line = ?";
        $params[] = $addressLine;
        $types .= "s";
    }
    if ($city) {
        $updates[] = "city = ?";
        $params[] = $city;
        $types .= "s";
    }
    if ($pincode) {
        $updates[] = "pincode = ?";
        $params[] = $pincode;
        $types .= "s";
    }
    if ($lat !== null) {
        $updates[] = "lat = ?";
        $params[] = $lat;
        $types .= "d";
    }
    if ($lng !== null) {
        $updates[] = "lng = ?";
        $params[] = $lng;
        $types .= "d";
    }
    if ($isDefault !== null) {
        $updates[] = "is_default = ?";
        $params[] = $isDefault ? 1 : 0;
        $types .= "i";
    }

    if (empty($updates)) {
        echo json_encode(["status" => "error", "error" => "No fields to update"]);
        exit;
    }

    // If setting as default, unset other defaults
    if ($isDefault) {
        $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $userId");
    }

    $params[] = $addressId;
    $params[] = $userId;
    $types .= "ii";

    $sql = "UPDATE addresses SET " . implode(", ", $updates) . " WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo json_encode([
        "status" => "ok",
        "message" => "Address updated"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to update address"
    ]);
}
