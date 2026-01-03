<?php

/**
 * add_address.php - Add a new delivery address for customer
 * Supports both token and user_id authentication
 */

header('Content-Type: application/json');
require_once 'db.php';

// Read JSON body
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body']);
    exit;
}

// Get user_id (direct or from token)
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

// If no user_id, try token
if ($user_id <= 0 && !empty($data['token'])) {
    $token = trim($data['token']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_id = (int)$row['id'];
    }
}

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID or valid token required']);
    exit;
}

// Address fields
$label = isset($data['label']) ? trim($data['label']) : 'Home';
$address_line = isset($data['address_line']) ? trim($data['address_line']) : '';
$city = isset($data['city']) ? trim($data['city']) : '';
$pincode = isset($data['pincode']) ? trim($data['pincode']) : '';
$lat = isset($data['lat']) ? (float)$data['lat'] : 0;
$lng = isset($data['lng']) ? (float)$data['lng'] : 0;
$is_default = isset($data['is_default']) ? ($data['is_default'] ? 1 : 0) : 0;

// Validation
if (empty($address_line)) {
    echo json_encode(['status' => 'error', 'message' => 'Address line is required']);
    exit;
}

try {
    // Check if addresses table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'addresses'");
    if ($tableCheck->num_rows == 0) {
        // Create addresses table
        $conn->query("
            CREATE TABLE IF NOT EXISTS `addresses` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `label` varchar(50) DEFAULT 'Home',
                `address_line` text NOT NULL,
                `city` varchar(100) DEFAULT NULL,
                `pincode` varchar(10) DEFAULT NULL,
                `lat` double DEFAULT NULL,
                `lng` double DEFAULT NULL,
                `is_default` tinyint(1) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `fk_addr_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // If this is set as default, unset other defaults
    if ($is_default) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Insert address (without updated_at since it may not exist in older tables)
    $stmt = $conn->prepare(
        "INSERT INTO addresses (user_id, label, address_line, city, pincode, lat, lng, is_default, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("issssddi", $user_id, $label, $address_line, $city, $pincode, $lat, $lng, $is_default);

    if ($stmt->execute()) {
        $address_id = $conn->insert_id;

        echo json_encode([
            'status' => 'ok',
            'message' => 'Address saved successfully',
            'address' => [
                'id' => $address_id,
                'label' => $label,
                'address_line' => $address_line,
                'city' => $city,
                'pincode' => $pincode,
                'lat' => $lat,
                'lng' => $lng,
                'is_default' => (bool)$is_default
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Failed to save address: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}

$conn->close();
