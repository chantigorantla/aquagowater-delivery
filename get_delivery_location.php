<?php
// get_delivery_location.php - Customer fetches delivery person's live location
header('Content-Type: application/json');

// Error handling
error_reporting(0);
ini_set('display_errors', 0);

require_once 'db.php';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_POST;
}

// Also support GET parameters
if (empty($data)) {
    $data = $_GET;
}

// Validate required fields
$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

try {
    // Get order details - simpler query without address lat/lng
    $orderStmt = $conn->prepare("
        SELECT o.id, o.user_id, o.partner_id, o.status, o.address_id,
               u.name as partner_name, u.phone as partner_phone,
               u.lat as partner_default_lat, u.lng as partner_default_lng
        FROM orders o
        LEFT JOIN users u ON o.partner_id = u.id
        WHERE o.id = ?
    ");

    if (!$orderStmt) {
        echo json_encode(['success' => false, 'message' => 'Database query error']);
        exit;
    }

    $orderStmt->bind_param("i", $order_id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    if (!$order = $orderResult->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    $orderStmt->close();

    // Check if order is trackable
    $trackableStatuses = ['out_for_delivery', 'confirmed', 'processing'];
    if (!in_array($order['status'], $trackableStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Order is not currently being delivered',
            'status' => $order['status']
        ]);
        exit;
    }

    // Get customer address location
    $customer_lat = null;
    $customer_lng = null;
    $customer_address = '';

    if (!empty($order['address_id'])) {
        $addrStmt = $conn->prepare("SELECT lat, lng, address_line, city FROM addresses WHERE id = ?");
        if ($addrStmt) {
            $addrStmt->bind_param("i", $order['address_id']);
            $addrStmt->execute();
            $addrResult = $addrStmt->get_result();
            if ($addr = $addrResult->fetch_assoc()) {
                $customer_lat = isset($addr['lat']) ? (float)$addr['lat'] : null;
                $customer_lng = isset($addr['lng']) ? (float)$addr['lng'] : null;
                $customer_address = trim(($addr['address_line'] ?? '') . ', ' . ($addr['city'] ?? ''));
            }
            $addrStmt->close();
        }
    }

    // Get partner's live location from tracking table (if exists)
    $partner_lat = null;
    $partner_lng = null;
    $last_updated = null;
    $is_live = false;

    // Check if delivery_tracking table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'delivery_tracking'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $trackStmt = $conn->prepare("SELECT lat, lng, updated_at FROM delivery_tracking WHERE order_id = ?");
        if ($trackStmt) {
            $trackStmt->bind_param("i", $order_id);
            $trackStmt->execute();
            $trackResult = $trackStmt->get_result();

            if ($tracking = $trackResult->fetch_assoc()) {
                $partner_lat = (float)$tracking['lat'];
                $partner_lng = (float)$tracking['lng'];
                $last_updated = $tracking['updated_at'];
                $is_live = true;
            }
            $trackStmt->close();
        }
    }

    // If no tracking data, use partner's default location
    if ($partner_lat === null) {
        $partner_lat = isset($order['partner_default_lat']) ? (float)$order['partner_default_lat'] : null;
        $partner_lng = isset($order['partner_default_lng']) ? (float)$order['partner_default_lng'] : null;
    }

    // Calculate distance if both locations available
    $distance_km = null;
    $eta_mins = null;

    if ($partner_lat !== null && $partner_lat != 0 && $customer_lat !== null && $customer_lat != 0) {
        // Haversine formula for distance calculation
        $earth_radius = 6371; // km

        $lat1 = deg2rad($partner_lat);
        $lat2 = deg2rad($customer_lat);
        $delta_lat = deg2rad($customer_lat - $partner_lat);
        $delta_lng = deg2rad($customer_lng - $partner_lng);

        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
            cos($lat1) * cos($lat2) *
            sin($delta_lng / 2) * sin($delta_lng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance_km = round($earth_radius * $c, 2);

        // Estimate ETA (assuming 20 km/h average speed for delivery)
        $eta_mins = round(($distance_km / 20) * 60);
        if ($eta_mins < 1) $eta_mins = 1;
    }

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'status' => $order['status'],
        'partner_name' => $order['partner_name'] ?? 'Delivery Partner',
        'partner_phone' => $order['partner_phone'] ?? '',
        'partner_lat' => $partner_lat,
        'partner_lng' => $partner_lng,
        'customer_lat' => $customer_lat,
        'customer_lng' => $customer_lng,
        'customer_address' => $customer_address,
        'distance_km' => $distance_km,
        'eta_mins' => $eta_mins,
        'last_updated' => $last_updated,
        'is_live' => $is_live
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

$conn->close();
