<?php
// orders.php - Get orders for partner
header('Content-Type: application/json');
require_once 'db.php';

// Support both POST body and GET parameters
$data = json_decode(file_get_contents('php://input'), true);
$partner_id = 0;
$status = 'all';

// Try POST data first, then GET
if (isset($data['partner_id'])) {
    $partner_id = (int)$data['partner_id'];
} elseif (isset($_GET['partner_id'])) {
    $partner_id = (int)$_GET['partner_id'];
}

if (isset($data['status'])) {
    $status = strtolower($data['status']);
} elseif (isset($_GET['status'])) {
    $status = strtolower($_GET['status']);
}

// Validate partner_id
if ($partner_id <= 0) {
    echo json_encode(array('status' => 'error', 'error' => 'Partner ID required'));
    exit;
}

// Build query based on filter - use address_id from orders table
$sql = "SELECT o.id, o.user_id, o.product_name, o.quantity, o.total, o.status, 
               o.payment_method, o.payment_status, o.created_at, o.address_id,
               o.delivery_date, o.delivery_slot,
               u.name as customer_name, u.email as customer_email,
               a.address_line, a.city, a.pincode
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN addresses a ON a.id = o.address_id
        WHERE o.partner_id = ?";

// Apply status filter
if ($status !== 'all') {
    switch ($status) {
        case 'pending':
            $sql .= " AND o.status = 'pending'";
            break;
        case 'active':
        case 'accepted':
        case 'confirmed':
            $sql .= " AND o.status IN ('confirmed', 'processing', 'out_for_delivery')";
            break;
        case 'completed':
        case 'delivered':
            $sql .= " AND o.status = 'delivered'";
            break;
        case 'cancelled':
            $sql .= " AND o.status = 'cancelled'";
            break;
    }
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = array();
while ($row = $result->fetch_assoc()) {
    // Build address string from joined address or fallback
    $address = "";
    if (!empty($row['address_line'])) {
        $address = $row['address_line'];
        if (!empty($row['city'])) $address .= ", " . $row['city'];
        if (!empty($row['pincode'])) $address .= " - " . $row['pincode'];
    } else {
        // Fallback: get user's default address if order has no address_id
        $userId = (int)$row['user_id'];
        $addrQuery = $conn->prepare("SELECT address_line, city, pincode FROM addresses WHERE user_id = ? ORDER BY is_default DESC LIMIT 1");
        $addrQuery->bind_param("i", $userId);
        $addrQuery->execute();
        $addrResult = $addrQuery->get_result();
        if ($addr = $addrResult->fetch_assoc()) {
            $address = $addr['address_line'];
            if (!empty($addr['city'])) $address .= ", " . $addr['city'];
            if (!empty($addr['pincode'])) $address .= " - " . $addr['pincode'];
        } else {
            $address = "Address not available";
        }
    }

    $orders[] = array(
        'id' => (int)$row['id'],
        'customer_name' => $row['customer_name'] ?? 'Unknown',
        'customer_email' => $row['customer_email'] ?? '',
        'product_name' => $row['product_name'] ?? 'Water Can',
        'quantity' => (int)($row['quantity'] ?? 1),
        'total' => (int)($row['total'] ?? 0),
        'status' => $row['status'] ?? 'pending',
        'payment_method' => $row['payment_method'] ?? 'COD',
        'payment_status' => $row['payment_status'] ?? 'pending',
        'address' => $address,
        'delivery_date' => $row['delivery_date'] ?? 'Today',
        'time_slot' => $row['delivery_slot'] ?? '',
        'created_at' => $row['created_at']
    );
}

echo json_encode(array(
    'status' => 'ok',
    'success' => true,
    'orders' => $orders,
    'count' => count($orders)
));

$stmt->close();
$conn->close();
