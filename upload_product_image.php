<?php
// upload_product_image.php - Upload product image
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_code = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file';
    echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error: ' . $error_code]);
    exit;
}

$file = $_FILES['image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, WebP']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB allowed']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Return relative URL (can be used directly with base URL)
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'image_url' => $filepath,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
}
