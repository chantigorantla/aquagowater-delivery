<?php
// get_partners.php - Get list of all online partners with distance
header('Content-Type: application/json');
require_once 'db.php';

// Optional customer location for distance calculation
$customer_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 0;
$customer_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 0;

// Check if location provided
$has_location = ($customer_lat != 0 && $customer_lng != 0);

if ($has_location) {
    // Get partners with distance calculation (Haversine formula)
    $sql = "SELECT id, name, lat, lng, service_radius_km, is_online,
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
                sin(radians(?)) * sin(radians(lat))
            )) AS distance
            FROM users
            WHERE role = 'partner' 
              AND is_online = 1 
              AND lat IS NOT NULL 
              AND lng IS NOT NULL
            ORDER BY distance ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddd", $customer_lat, $customer_lng, $customer_lat);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Get all online partners without distance
    $sql = "SELECT id, name, lat, lng, service_radius_km, is_online
            FROM users
            WHERE role = 'partner' 
              AND is_online = 1
            ORDER BY name ASC";
    $result = $conn->query($sql);
}

$partners = [];
while ($row = $result->fetch_assoc()) {
    $partner = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'is_online' => (bool)$row['is_online']
    ];

    // Include location if available
    if ($row['lat'] && $row['lng']) {
        $partner['lat'] = (float)$row['lat'];
        $partner['lng'] = (float)$row['lng'];
        $partner['service_radius_km'] = (int)$row['service_radius_km'];
    }

    // Include distance if calculated
    if (isset($row['distance'])) {
        $partner['distance'] = round((float)$row['distance'], 2);
        $partner['distance_text'] = round((float)$row['distance'], 1) . ' km away';
    }

    $partners[] = $partner;
}

if ($has_location && isset($stmt)) {
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'partners' => $partners,
    'count' => count($partners)
]);

$conn->close();
