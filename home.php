<?php
require "db.php";
header("Content-Type: application/json");

// 1️⃣ Get Authorization header
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Token missing"
    ]);
    exit;
}

// 2️⃣ Extract token
$auth = $headers['Authorization']; // Bearer token
$token = str_replace("Bearer ", "", $auth);

// 3️⃣ Validate token
$stmt = $conn->prepare("SELECT id, name FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid token"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// 4️⃣ SUCCESS RESPONSE (IMPORTANT PART)
echo json_encode([
    "status" => "success",
    "message" => "Home data fetched",
    "user" => [
        "id" => $user['id'],
        "name" => $user['name']
    ],
    "offers" => [
        "20% OFF on first order",
        "Free delivery today"
    ]
]);
