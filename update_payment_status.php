<?php

/**
 * Update Payment Status
 * Updates order payment status after UPI payment
 * Expects: order_id, payment_status, upi_response
 * 
 * Parses UPI response to extract:
 * - txnId: Transaction ID
 * - ApprovalRefNo: UTR Number (Bank Reference)
 * - responseCode: Response code
 * - Status: Success/Failure
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['order_id'])) {
    echo json_encode(["status" => "error", "error" => "Order ID is required"]);
    exit;
}

$orderId = (int)$data['order_id'];
$paymentStatus = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
$upiResponse = isset($data['upi_response']) ? $data['upi_response'] : '';

// Parse UPI response to extract UTR and transaction details
$txnId = '';
$utrNumber = '';
$responseCode = '';

if (!empty($upiResponse)) {
    // Parse response format: txnId=xxx&responseCode=00&Status=SUCCESS&txnRef=xxx&ApprovalRefNo=xxx
    parse_str($upiResponse, $parsed);

    $txnId = isset($parsed['txnId']) ? $parsed['txnId'] : '';
    $utrNumber = isset($parsed['ApprovalRefNo']) ? $parsed['ApprovalRefNo'] : '';
    $responseCode = isset($parsed['responseCode']) ? $parsed['responseCode'] : '';
}

try {
    // Determine order status based on payment status
    $newStatus = ($paymentStatus == 'paid') ? 'confirmed' : 'pending_payment';

    // Check if UTR column exists
    $checkUtr = $conn->query("SHOW COLUMNS FROM orders LIKE 'utr_number'");
    $hasUtrColumn = $checkUtr->num_rows > 0;

    // Check if payment columns exist
    $checkPayment = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");
    $hasPaymentColumns = $checkPayment->num_rows > 0;

    if ($hasPaymentColumns && $hasUtrColumn) {
        // All columns exist - update everything including UTR
        $stmt = $conn->prepare(
            "UPDATE orders SET status = ?, payment_status = ?, upi_response = ?, 
             txn_id = ?, utr_number = ? WHERE id = ?"
        );
        $stmt->bind_param("sssssi", $newStatus, $paymentStatus, $upiResponse, $txnId, $utrNumber, $orderId);
    } elseif ($hasPaymentColumns) {
        // Payment columns exist but not UTR - update without UTR
        $stmt = $conn->prepare(
            "UPDATE orders SET status = ?, payment_status = ?, upi_response = ? WHERE id = ?"
        );
        $stmt->bind_param("sssi", $newStatus, $paymentStatus, $upiResponse, $orderId);
    } else {
        // Only basic columns exist - just update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
    }

    $stmt->execute();

    $response = [
        "status" => "ok",
        "message" => "Payment status updated successfully",
        "order_status" => $newStatus,
        "payment_status" => $paymentStatus
    ];

    // Include UTR if available
    if (!empty($utrNumber)) {
        $response["utr_number"] = $utrNumber;
    }
    if (!empty($txnId)) {
        $response["txn_id"] = $txnId;
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to update payment status: " . $e->getMessage()
    ]);
}
