<?php
// earnings.php - Get partner earnings
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db.php';

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get input - support both JSON POST and GET parameters
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}
if (empty($input)) {
    $input = $_GET;
}

$partner_id = isset($input['partner_id']) ? (int)$input['partner_id'] : 0;
$period = isset($input['period']) ? strtolower($input['period']) : 'all';

// Validate partner_id
if ($partner_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Partner ID required']);
    exit;
}

// Build date filter
$date_filter = "";
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('-7 days'));
$month_start = date('Y-m-01');

switch ($period) {
    case 'today':
        $date_filter = " AND DATE(o.created_at) = '$today'";
        break;
    case 'week':
        $date_filter = " AND o.created_at >= '$week_start'";
        break;
    case 'month':
        $date_filter = " AND o.created_at >= '$month_start'";
        break;
}

// Get summary for the selected period
$summary_sql = "SELECT COALESCE(SUM(o.total), 0) as total, COUNT(o.id) as order_count 
    FROM orders o 
    WHERE o.partner_id = ? AND o.status = 'delivered'" . $date_filter;
$stmt = $conn->prepare($summary_sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
$summary_row = $result->fetch_assoc();
$stmt->close();

$total_earnings = (int)($summary_row['total'] ?? 0);
$order_count = (int)($summary_row['order_count'] ?? 0);

// Get earnings transactions for the selected period
$earnings_sql = "SELECT o.id, o.total, o.created_at, o.product_name, o.quantity,
               u.name as customer_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.partner_id = ? AND o.status = 'delivered'" . $date_filter . "
        ORDER BY o.created_at DESC LIMIT 100";

$stmt = $conn->prepare($earnings_sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();

$earnings = array();
while ($row = $result->fetch_assoc()) {
    $amount = (int)($row['total'] ?? 0);

    $earnings[] = array(
        'id' => (int)$row['id'],
        'order_id' => '#AQ' . $row['id'],
        'amount' => $amount,
        'customer_name' => $row['customer_name'] ?? 'Customer',
        'date' => date('M d, Y', strtotime($row['created_at'])),
        'time' => date('h:i A', strtotime($row['created_at'])),
        'status' => 'delivered'
    );
}
$stmt->close();

// Return response in format expected by Android app
echo json_encode(array(
    'success' => true,
    'period' => $period,
    'summary' => array(
        'total' => $total_earnings,
        'order_count' => $order_count
    ),
    'earnings' => $earnings
));

$conn->close();
