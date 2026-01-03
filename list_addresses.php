<?php

/**
 * Get user addresses - Simplified
 * Expects: user_id
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID required"]);
    exit;
}

$userId = (int)$data['user_id'];

try {
    $query = $conn->prepare(
        "SELECT id, label, address_line, city, pincode, lat, lng, is_default
         FROM addresses
         WHERE user_id = ?
         ORDER BY is_default DESC, id DESC"
    );
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();

    $addresses = [];
    while ($row = $result->fetch_assoc()) {
        $addresses[] = [
            "id" => (int)$row['id'],
            "label" => $row['label'] ?? "Home",
            "address_line" => $row['address_line'],
            "city" => $row['city'],
            "pincode" => $row['pincode'],
            "lat" => (float)$row['lat'],
            "lng" => (float)$row['lng'],
            "is_default" => (bool)$row['is_default']
        ];
    }

    echo json_encode([
        "status" => "ok",
        "addresses" => $addresses
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to load addresses"
    ]);
}
