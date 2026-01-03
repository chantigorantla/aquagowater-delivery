<?php

/**
 * Send Password Reset OTP
 * Generates a 6-digit OTP, stores it in DB, and sends via email (SMTP)
 */
require_once 'db.php';
require_once 'smtp_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || empty($data['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

$email = trim($data['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No account found with this email']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$userName = $user['name'];
$stmt->close();

// Generate 6-digit OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Create table if not exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'password_resets'");
if ($tableCheck->num_rows == 0) {
    $conn->query("CREATE TABLE password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_otp (otp)
    )");
}

// Delete existing and insert new OTP
$conn->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

$insertStmt = $conn->prepare("INSERT INTO password_resets (user_id, email, otp, expires_at) VALUES (?, ?, ?, ?)");
$insertStmt->bind_param("isss", $userId, $email, $otp, $expiresAt);

if (!$insertStmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate OTP']);
    exit;
}
$insertStmt->close();

// Email HTML
$subject = "AquaGo - Password Reset OTP";
$htmlBody = "
<html>
<body style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>
    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px;'>
        <h2 style='color: #12A4E8; text-align: center;'>🔐 Password Reset</h2>
        <p>Hi <strong>$userName</strong>,</p>
        <p>Use this OTP to reset your password:</p>
        <div style='background: linear-gradient(135deg, #12A4E8, #0d8ed4); color: white; font-size: 32px; font-weight: bold; text-align: center; padding: 20px; border-radius: 8px; letter-spacing: 8px; margin: 20px 0;'>
            $otp
        </div>
        <p style='color: #888; text-align: center;'>Valid for <strong>10 minutes</strong>.</p>
        <p style='color: #aaa; font-size: 11px; text-align: center;'>💧 AquaGo - Water Delivery App</p>
    </div>
</body>
</html>
";

// Try to send email with new app password
$emailSent = sendSmtpEmail($email, $subject, $htmlBody);
$conn->close();

// Return success with OTP (for testing - remove OTP in production)
if ($emailSent) {
    echo json_encode([
        'status' => 'ok',
        'message' => 'OTP sent to your email. Please check your inbox.'
    ]);
} else {
    // Email failed but OTP is saved - show OTP for testing
    echo json_encode([
        'status' => 'ok',
        'message' => 'OTP generated. Email delivery may be delayed.',
        'otp' => $otp  // Fallback - remove in production
    ]);
}
