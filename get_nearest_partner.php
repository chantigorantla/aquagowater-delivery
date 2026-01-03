<?php
// get_nearest_partner.php - Find nearest online partner for customer
header('Content-Type: application/json');
require_once 'db.php';

// Get customer location
$customer_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 0;
$customer_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 0;

// Validate
if ($customer_lat == 0 || $customer_lng == 0) {
    // If no location provided, return first online partner (fallback)
    $sql = "SELECT id, name, lat, lng, service_radius_km 
            FROM users 
            WHERE role = 'partner' AND is_online = 1 AND lat IS NOT NULL
            LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(array(
            'success' => true,
            'partner' => array(
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'distance' => 0
            ),
            'fallback' => true
        ));
    } else {
        echo json_encode(array('success' => false, 'message' => 'No partners available'));
    }
    $conn->close();
    exit;
}

// Find nearest online partner using Haversine formula
// Distance in km = 6371 * acos(cos(radians(lat1)) * cos(radians(lat2)) * cos(radians(lng2) - radians(lng1)) + sin(radians(lat1)) * sin(radians(lat2)))

$sql = "SELECT id, name, lat, lng, service_radius_km,
        (6371 * acos(
            cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
            sin(radians(?)) * sin(radians(lat))
        )) AS distance
        FROM users
        WHERE role = 'partner' 
          AND is_online = 1 
          AND lat IS NOT NULL 
          AND lng IS NOT NULL
        HAVING distance <= service_radius_km
        ORDER BY distance ASC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddd", $customer_lat, $customer_lng, $customer_lat);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(array(
        'success' => true,
        'partner' => array(
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'lat' => (float)$row['lat'],
            'lng' => (float)$row['lng'],
            'distance' => round((float)$row['distance'], 2),
            'service_radius_km' => (int)$row['service_radius_km']
        )
    ));
} else {
    // No partner within service radius - return nearest anyway with warning
    $sql2 = "SELECT id, name, lat, lng, service_radius_km,
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )) AS distance
            FROM users
            WHERE role = 'partner' 
              AND is_online = 1 
              AND lat IS NOT NULL
            ORDER BY distance ASC
            LIMIT 1";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ddd", $customer_lat, $customer_lng, $customer_lat);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($row2 = $result2->fetch_assoc()) {
        echo json_encode(array(
            'success' => true,
            'partner' => array(
                'id' => (int)$row2['id'],
                'name' => $row2['name'],
                'distance' => round((float)$row2['distance'], 2)
            ),
            'warning' => 'Partner is outside normal service area'
        ));
    } else {
        echo json_encode(array('success' => false, 'message' => 'No online partners available'));
    }
    $stmt2->close();
}

$stmt->close();
$conn->close();
