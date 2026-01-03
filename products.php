<?php

/**
 * Get products - optionally filtered by partner_id
 * If partner_id is provided, returns only that partner's products
 * Otherwise returns all products (for backward compatibility)
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include "db.php";

try {
    // Get optional partner_id filter
    $partner_id = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;

    if ($partner_id > 0) {
        // Get products for specific partner
        $query = $conn->prepare("SELECT id, partner_id, name, size, price, image_url FROM products WHERE partner_id = ? ORDER BY id");
        $query->bind_param("i", $partner_id);
        $query->execute();
        $result = $query->get_result();
    } else {
        // Get all products (backward compatibility)
        $result = $conn->query("SELECT id, partner_id, name, size, price, image_url FROM products ORDER BY id");
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            "id" => (int)$row['id'],
            "partner_id" => (int)($row['partner_id'] ?? 0),
            "name" => $row['name'],
            "size" => $row['size'],
            "price" => (float)$row['price'],
            "image_url" => $row['image_url'] ?? ''
        ];
    }

    echo json_encode([
        "success" => true,
        "status" => "ok",
        "products" => $products,
        "count" => count($products),
        "partner_id" => $partner_id > 0 ? $partner_id : null
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "status" => "error",
        "error" => "Failed to load products"
    ]);
}

$conn->close();
