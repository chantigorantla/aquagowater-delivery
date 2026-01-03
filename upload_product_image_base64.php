<?php
// upload_product_image_base64.php - Upload product image from base64 data
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

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$base64_data = isset($input['image_data']) ? $input['image_data'] : '';
$partner_id = isset($input['partner_id']) ? (int)$input['partner_id'] : 0;

if (empty($base64_data)) {
    echo json_encode(['success' => false, 'message' => 'No image data provided']);
    exit;
}

// Remove base64 header if present
if (strpos($base64_data, 'data:image') !== false) {
    $base64_data = preg_replace('/^data:image\/\w+;base64,/', '', $base64_data);
}

// Decode base64
$image_data = base64_decode($base64_data);
if ($image_data === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid base64 data']);
    exit;
}

// Check file size (max 5MB)
$max_size = 5 * 1024 * 1024;
if (strlen($image_data) > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB allowed']);
    exit;
}

// Detect image type from binary data
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->buffer($image_data);

$allowed_types = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif'
];

if (!isset($allowed_types[$mime_type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, WebP, GIF']);
    exit;
}

$extension = $allowed_types[$mime_type];

// Generate unique filename
$filename = 'product_' . $partner_id . '_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Save file
if (file_put_contents($filepath, $image_data)) {
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'image_url' => $filepath,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save image']);
}
