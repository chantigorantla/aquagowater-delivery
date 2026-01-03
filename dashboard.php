<?php
// dashboard.php - Get partner dashboard KPIs
header('Content-Type: application/json');
require_once 'db.php';

// Get partner_id from request
$partner_id = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;

// Validate partner_id
if ($partner_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Partner ID required'));
    exit;
}

// Date calculations
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('-7 days'));
$month_start = date('Y-m-01');

// ========================================
// KPI 1: Today's Orders
// ========================================
$orders_today = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND DATE(created_at) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $orders_today = (int)$row['count'];
}
$stmt->close();

// ========================================
// KPI 2: Today's Earnings (delivered orders)
// ========================================
$earnings_today = 0;
$sql = "SELECT SUM(total) as total FROM orders WHERE partner_id = ? AND DATE(created_at) = ? AND status = 'delivered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $earnings_today = (int)($row['total'] ?? 0);
}
$stmt->close();

// ========================================
// KPI 3: Pending Orders (needs action)
// ========================================
$pending_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $pending_orders = (int)$row['count'];
}
$stmt->close();

// ========================================
// KPI 4: This Week's Earnings
// ========================================
$earnings_week = 0;
$sql = "SELECT SUM(total) as total FROM orders WHERE partner_id = ? AND created_at >= ? AND status = 'delivered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $earnings_week = (int)($row['total'] ?? 0);
}
$stmt->close();

// ========================================
// KPI 5: This Month's Earnings
// ========================================
$earnings_month = 0;
$sql = "SELECT SUM(total) as total FROM orders WHERE partner_id = ? AND created_at >= ? AND status = 'delivered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $month_start);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $earnings_month = (int)($row['total'] ?? 0);
}
$stmt->close();

// ========================================
// Additional useful stats
// ========================================

// Active orders (confirmed, processing, out_for_delivery)
$active_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status IN ('confirmed', 'processing', 'out_for_delivery')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $active_orders = (int)$row['count'];
}
$stmt->close();

// Completed today
$completed_today = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'delivered' AND DATE(created_at) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $completed_today = (int)$row['count'];
}
$stmt->close();

// Orders this week
$orders_week = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND created_at >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $orders_week = (int)$row['count'];
}
$stmt->close();

// Orders this month
$orders_month = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND created_at >= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $month_start);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $orders_month = (int)$row['count'];
}
$stmt->close();

// Order Status Breakdown - Confirmed
$confirmed_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'confirmed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $confirmed_orders = (int)$row['count'];
}
$stmt->close();

// Order Status Breakdown - Processing (Dispatched)
$processing_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'processing'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $processing_orders = (int)$row['count'];
}
$stmt->close();

// Order Status Breakdown - Out for Delivery
$out_for_delivery_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'out_for_delivery'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $out_for_delivery_orders = (int)$row['count'];
}
$stmt->close();

// Order Status Breakdown - Delivered (Today)
$delivered_orders = 0;
$sql = "SELECT COUNT(*) as count FROM orders WHERE partner_id = ? AND status = 'delivered' AND DATE(created_at) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $partner_id, $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $delivered_orders = (int)$row['count'];
}
$stmt->close();

echo json_encode(array(
    'success' => true,
    // Primary KPIs
    'orders_today' => $orders_today,
    'earnings_today' => $earnings_today,
    'pending_orders' => $pending_orders,
    'earnings_week' => $earnings_week,
    'earnings_month' => $earnings_month,
    // Additional stats
    'active_orders' => $active_orders,
    'completed_today' => $completed_today,
    'orders_week' => $orders_week,
    'orders_month' => $orders_month,
    // Order Status Breakdown (for PartnerDashboardActivity)
    'confirmed_orders' => $confirmed_orders,
    'processing_orders' => $processing_orders,
    'out_for_delivery_orders' => $out_for_delivery_orders,
    'delivered_orders' => $delivered_orders
));

$conn->close();
