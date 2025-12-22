<?php
// db.php - simple DB connection for XAMPP local

header('Content-Type: application/json; charset=utf-8');

$DB_HOST = '127.0.0.1';   // localhost
$DB_NAME = 'waterapp';    // mana database peru
$DB_USER = 'root';        // default XAMPP user
$DB_PASS = '';            // XAMPP lo password usually empty

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'DB connection failed',
        'details' => $e->getMessage()
    ]);
    exit;
}
