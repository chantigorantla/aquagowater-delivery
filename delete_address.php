<?php

/**
 * Delete address
 * Expects: user_id, address_id
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

try {
    $deleteQuery = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $deleteQuery->bind_param("ii", $addressId, $userId);
    $deleteQuery->execute();

    if ($deleteQuery->affected_rows > 0) {
        echo json_encode([
            "status" => "ok",
            "message" => "Address deleted"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "error" => "Address not found"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to delete address"
    ]);
}
