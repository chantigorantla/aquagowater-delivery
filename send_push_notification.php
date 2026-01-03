<?php

/**
 * Send push notification via Firebase Cloud Messaging V1 API
 * Uses Service Account for authentication
 * 
 * SETUP REQUIRED:
 * 1. Go to Firebase Console -> Project Settings -> Service Accounts
 * 2. Click "Generate new private key" 
 * 3. Save the JSON file as "firebase-service-account.json" in the same folder as this file
 */

// Path to your Firebase service account JSON file
define('FIREBASE_CREDENTIALS_PATH', __DIR__ . '/firebase-service-account.json');
define('FIREBASE_PROJECT_ID', 'aquago-7ea00'); // Your Firebase project ID from the console

/**
 * Get OAuth2 access token from service account
 */
function getAccessToken()
{
    if (!file_exists(FIREBASE_CREDENTIALS_PATH)) {
        // Silently return null if file doesn't exist - notifications will be skipped
        return null;
    }

    $credentialsContent = file_get_contents(FIREBASE_CREDENTIALS_PATH);
    if ($credentialsContent === false) {
        return null;
    }

    $credentials = json_decode($credentialsContent, true);

    if (!$credentials || !isset($credentials['private_key']) || !isset($credentials['client_email'])) {
        return null;
    }

    // Create JWT
    $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

    $now = time();
    $claims = [
        'iss' => $credentials['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];
    $payload = base64url_encode(json_encode($claims));

    // Sign with private key
    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    if (!$privateKey) {
        return null;
    }

    $signature = '';
    openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $signature = base64url_encode($signature);

    $jwt = "$header.$payload.$signature";

    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return null;
    }

    $data = json_decode($response, true);
    return isset($data['access_token']) ? $data['access_token'] : null;
}

/**
 * Base64 URL encode (for JWT)
 */
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Send push notification using FCM V1 API
 * 
 * @param string $fcmToken - User's FCM token
 * @param string $title - Notification title
 * @param string $body - Notification body
 * @param array $data - Additional data payload
 * @return bool - Success or failure
 */
function sendPushNotification($fcmToken, $title, $body, $data = [])
{
    if (empty($fcmToken)) {
        return false;
    }

    $accessToken = getAccessToken();
    if (!$accessToken) {
        // No access token - push notifications not configured, but don't fail
        return false;
    }

    $url = 'https://fcm.googleapis.com/v1/projects/' . FIREBASE_PROJECT_ID . '/messages:send';

    // Ensure all data values are strings
    $stringData = [];
    foreach ($data as $key => $value) {
        $stringData[$key] = is_string($value) ? $value : strval($value);
    }

    $message = [
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
            'data' => $stringData,
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY'
                ]
            ]
        ]
    ];

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode == 200;
}

/**
 * Create in-app notification and send push notification
 * This function is safe to call even if FCM is not configured
 * 
 * @param mysqli $conn - Database connection
 * @param int $userId - User to notify
 * @param string $pushTitle - Brief title for push notification
 * @param string $pushBody - Brief body for push notification
 * @param string $inAppTitle - Title for in-app notification
 * @param string $inAppBody - Detailed body for in-app notification
 * @param array $meta - Additional metadata (e.g., order_id)
 * @param string $type - Notification type (new_order, order_update, etc.)
 */
function notifyUser($conn, $userId, $pushTitle, $pushBody, $inAppTitle, $inAppBody, $meta = [], $type = 'general')
{
    // 1. Create in-app notification (always works)
    // Include type in meta so it can be retrieved by get_notifications.php
    $metaWithType = array_merge($meta, ['type' => $type]);
    $metaJson = json_encode($metaWithType);

    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, title, body, meta, read_flag, created_at) 
         VALUES (?, ?, ?, ?, 0, NOW())"
    );
    if ($stmt) {
        $stmt->bind_param("isss", $userId, $inAppTitle, $inAppBody, $metaJson);
        $stmt->execute();
        $stmt->close();
    }

    // 2. Try to send push notification (may fail silently if FCM not configured)
    // Check if fcm_token column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        $tokenQuery = $conn->prepare("SELECT fcm_token FROM users WHERE id = ?");
        if ($tokenQuery) {
            $tokenQuery->bind_param("i", $userId);
            $tokenQuery->execute();
            $result = $tokenQuery->get_result();

            if ($row = $result->fetch_assoc()) {
                $fcmToken = $row['fcm_token'];
                if (!empty($fcmToken)) {
                    $data = array_merge($meta, ['type' => $type]);
                    sendPushNotification($fcmToken, $pushTitle, $pushBody, $data);
                }
            }
            $tokenQuery->close();
        }
    }
}
