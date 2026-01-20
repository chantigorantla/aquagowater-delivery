<?php

/**
 * Debug script for push notifications with detailed FCM response
 * Access: http://your-server/waterapp/test_push.php
 */
header('Content-Type: application/json');
include "db.php";

// Path to your Firebase service account JSON file
define('FIREBASE_CREDENTIALS_PATH', __DIR__ . '/firebase-service-account.json');
define('FIREBASE_PROJECT_ID', 'aquago-7ea00');

$output = [];

// 1. Check if firebase-service-account.json exists
$output['firebase_file_exists'] = file_exists(FIREBASE_CREDENTIALS_PATH);
$output['firebase_file_path'] = FIREBASE_CREDENTIALS_PATH;

// Base64 URL encode function
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Get access token
function getAccessTokenDebug()
{
    if (!file_exists(FIREBASE_CREDENTIALS_PATH)) {
        return ['error' => 'Service account file not found'];
    }

    $credentialsContent = file_get_contents(FIREBASE_CREDENTIALS_PATH);
    $credentials = json_decode($credentialsContent, true);

    if (!$credentials || !isset($credentials['private_key'])) {
        return ['error' => 'Invalid credentials file'];
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

    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    if (!$privateKey) {
        return ['error' => 'Failed to load private key'];
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
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        return ['token' => $data['access_token']];
    }
    return ['error' => 'Token request failed', 'response' => $data, 'http_code' => $httpCode];
}

// Send push with detailed response
function sendPushDebug($fcmToken, $title, $body)
{
    $tokenResult = getAccessTokenDebug();
    if (isset($tokenResult['error'])) {
        return $tokenResult;
    }

    $accessToken = $tokenResult['token'];
    $url = 'https://fcm.googleapis.com/v1/projects/' . FIREBASE_PROJECT_ID . '/messages:send';

    $message = [
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
            'data' => [
                'type' => 'test',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'aquago_notifications'
                ]
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'success' => $httpCode == 200,
        'fcm_response' => json_decode($result, true),
        'raw_response' => $result,
        'curl_error' => $curlError
    ];
}

// 2. Get access token
$tokenResult = getAccessTokenDebug();
$output['access_token_result'] = isset($tokenResult['error']) ? $tokenResult : ['obtained' => true];

// 3. Check FCM tokens in database
$tokenQuery = $conn->query("SELECT id, name, role, fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != '' LIMIT 10");
$usersWithTokens = [];
if ($tokenQuery) {
    while ($row = $tokenQuery->fetch_assoc()) {
        $usersWithTokens[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'role' => $row['role'],
            'fcm_token_full' => $row['fcm_token']  // Show full token for debugging
        ];
    }
}
$output['users_with_fcm_tokens'] = $usersWithTokens;

// 4. Test sending notification
if (isset($_GET['test_user_id'])) {
    $testUserId = (int)$_GET['test_user_id'];

    $stmt = $conn->prepare("SELECT name, fcm_token FROM users WHERE id = ?");
    $stmt->bind_param("i", $testUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (!empty($user['fcm_token'])) {
            $pushResult = sendPushDebug(
                $user['fcm_token'],
                "Test Notification 🔔",
                "Hello " . $user['name'] . "! This is a test from AquaGo."
            );
            $output['test_notification'] = [
                'user_id' => $testUserId,
                'user_name' => $user['name'],
                'fcm_token' => $user['fcm_token'],
                'result' => $pushResult
            ];
        } else {
            $output['test_notification'] = ['error' => 'User has no FCM token'];
        }
    } else {
        $output['test_notification'] = ['error' => 'User not found'];
    }
}

$output['instructions'] = 'Add ?test_user_id=YOUR_USER_ID to test';

echo json_encode($output, JSON_PRETTY_PRINT);
$conn->close();
