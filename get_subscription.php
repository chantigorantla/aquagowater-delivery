<?php

/**
 * get_subscription.php - Get user's subscription details
 */

header('Content-Type: application/json');
require_once 'db.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

try {
    // Check if subscriptions table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'subscriptions'");

    if ($tableCheck->num_rows == 0) {
        // Create subscriptions table
        $conn->query("
            CREATE TABLE IF NOT EXISTS `subscriptions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `plan_name` varchar(100) NOT NULL,
                `price` decimal(10,2) NOT NULL,
                `frequency` enum('daily','weekly','monthly') DEFAULT 'weekly',
                `quantity` int(11) DEFAULT 2,
                `product_id` int(11) DEFAULT NULL,
                `next_delivery` date DEFAULT NULL,
                `status` enum('active','paused','cancelled') DEFAULT 'active',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `fk_sub_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // Get user's active subscription
    $stmt = $conn->prepare("SELECT id, plan_name, price, frequency, quantity, next_delivery, status 
                            FROM subscriptions 
                            WHERE user_id = ? AND status != 'cancelled'
                            ORDER BY created_at DESC 
                            LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'ok',
            'has_subscription' => true,
            'subscription' => [
                'id' => (int)$row['id'],
                'plan_name' => $row['plan_name'],
                'price' => (float)$row['price'],
                'frequency' => $row['frequency'],
                'quantity' => (int)$row['quantity'],
                'next_delivery' => $row['next_delivery'],
                'subscription_status' => $row['status']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'ok',
            'has_subscription' => false,
            'message' => 'No active subscription'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
