<?php
// Suppress PHP errors from being displayed (they would break JSON output)
error_reporting(0);
ini_set('display_errors', 0);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "waterapp";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "error" => "Database connection failed"]);
    exit;
}
