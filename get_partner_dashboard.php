<?php

/**
 * get_partner_dashboard.php - Get partner dashboard KPIs
 */

header('Content-Type: application/json');
require_once 'db.php';

// Accept partner_id from GET, POST, or JSON body
$data = json_decode(file_get_contents("php://input"), true);
$partner_id = 0;

// Try GET parameter first
if (isset($_GET['partner_id'])) {
    $partner_id = intval($_GET['partner_id']);
}
// Then try JSON body
elseif (isset($data['partner_id'])) {
    $partner_id = intval($data['partner_id']);
}
// Then try POST
elseif (isset($_POST['partner_id'])) {
    $partner_id = intval($_POST['partner_id']);
}

if ($partner_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Partner ID required']);
    exit;
}

try {
    // Get today's date range
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');

    // Get week start (Monday) and end (Sunday)
    $week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
    $week_end = date('Y-m-d 23:59:59', strtotime('sunday this week'));

    // Get month start and end
    $month_start = date('Y-m-01 00:00:00');
    $month_end = date('Y-m-t 23:59:59');

    // KPI 1: Today's Orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND created_at BETWEEN ? AND ?");
    $stmt->bind_param("iss", $partner_id, $today_start, $today_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders_today = $result->fetch_assoc()['count'] ?? 0;

    // KPI 2: Today's Earnings
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE partner_id = ? AND status IN ('delivered', 'completed') AND created_at BETWEEN ? AND ?");
    $stmt->bind_param("iss", $partner_id, $today_start, $today_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $earnings_today = $result->fetch_assoc()['total'] ?? 0;

    // KPI 3: Pending Orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status IN ('pending', 'confirmed', 'processing')");
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_orders = $result->fetch_assoc()['count'] ?? 0;

    // KPI 4: This Week's Earnings
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE partner_id = ? AND status IN ('delivered', 'completed') AND created_at BETWEEN ? AND ?");
    $stmt->bind_param("iss", $partner_id, $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $earnings_week = $result->fetch_assoc()['total'] ?? 0;

    // KPI 5: This Month's Earnings
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE partner_id = ? AND status IN ('delivered', 'completed') AND created_at BETWEEN ? AND ?");
    $stmt->bind_param("iss", $partner_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $earnings_month = $result->fetch_assoc()['total'] ?? 0;

    // Total orders (all time)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ?");
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_orders = $result->fetch_assoc()['count'] ?? 0;

    // Order Status Breakdown - Confirmed
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'confirmed'");
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $confirmed_orders = $result->fetch_assoc()['count'] ?? 0;

    // Order Status Breakdown - Processing (Dispatched)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'processing'");
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $processing_orders = $result->fetch_assoc()['count'] ?? 0;

    // Order Status Breakdown - Out for Delivery
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'out_for_delivery'");
    $stmt->bind_param("i", $partner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $out_for_delivery_orders = $result->fetch_assoc()['count'] ?? 0;

    // Order Status Breakdown - Delivered (Today)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status IN ('delivered', 'completed') AND created_at BETWEEN ? AND ?");
    $stmt->bind_param("iss", $partner_id, $today_start, $today_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $delivered_orders = $result->fetch_assoc()['count'] ?? 0;

    echo json_encode([
        'status' => 'ok',
        'orders_today' => (int)$orders_today,
        'earnings_today' => (int)$earnings_today,
        'pending_orders' => (int)$pending_orders,
        'earnings_week' => (int)$earnings_week,
        'earnings_month' => (int)$earnings_month,
        'total_orders' => (int)$total_orders,
        'confirmed_orders' => (int)$confirmed_orders,
        'processing_orders' => (int)$processing_orders,
        'out_for_delivery_orders' => (int)$out_for_delivery_orders,
        'delivered_orders' => (int)$delivered_orders
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
