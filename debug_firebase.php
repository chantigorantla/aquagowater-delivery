<?php

/**
 * Deeper debug for Firebase credentials issue
 */
header('Content-Type: application/json');

$output = [];

// 1. Check PHP extensions
$output['openssl_enabled'] = extension_loaded('openssl');
$output['curl_enabled'] = extension_loaded('curl');

// 2. Check the credentials file content structure
$credPath = __DIR__ . '/firebase-service-account.json';
if (file_exists($credPath)) {
    $content = file_get_contents($credPath);
    $credentials = json_decode($content, true);

    if ($credentials) {
        $output['json_parse_success'] = true;
        $output['has_private_key'] = isset($credentials['private_key']);
        $output['has_client_email'] = isset($credentials['client_email']);
        $output['project_id'] = $credentials['project_id'] ?? 'missing';
        $output['client_email'] = $credentials['client_email'] ?? 'missing';

        // Try to load the private key
        if (isset($credentials['private_key'])) {
            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            $output['private_key_valid'] = ($privateKey !== false);
            if ($privateKey === false) {
                $output['openssl_error'] = openssl_error_string();
            }
        }
    } else {
        $output['json_parse_success'] = false;
        $output['json_error'] = json_last_error_msg();
    }
} else {
    $output['file_exists'] = false;
}

// 3. Try to get token with detailed error reporting
function getAccessTokenDebug()
{
    $credPath = __DIR__ . '/firebase-service-account.json';

    if (!file_exists($credPath)) {
        return ['error' => 'File not found'];
    }

    $credentialsContent = file_get_contents($credPath);
    if ($credentialsContent === false) {
        return ['error' => 'Cannot read file'];
    }

    $credentials = json_decode($credentialsContent, true);
    if (!$credentials) {
        return ['error' => 'JSON parse failed: ' . json_last_error_msg()];
    }

    if (!isset($credentials['private_key']) || !isset($credentials['client_email'])) {
        return ['error' => 'Missing required fields'];
    }

    // Create JWT header
    $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $header = rtrim(strtr($header, '+/', '-_'), '=');

    // Create JWT claims
    $now = time();
    $claims = [
        'iss' => $credentials['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];
    $payload = base64_encode(json_encode($claims));
    $payload = rtrim(strtr($payload, '+/', '-_'), '=');

    // Sign with private key
    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    if (!$privateKey) {
        return ['error' => 'Cannot load private key: ' . openssl_error_string()];
    }

    $signResult = openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256);
    if (!$signResult) {
        return ['error' => 'Cannot sign JWT: ' . openssl_error_string()];
    }

    $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
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
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!$response) {
        return ['error' => 'cURL failed: ' . $curlError];
    }

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        return [
            'success' => true,
            'token_preview' => substr($data['access_token'], 0, 50) . '...'
        ];
    } else {
        return [
            'error' => 'Token request failed',
            'http_code' => $httpCode,
            'response' => $data,
            'raw_response' => $response
        ];
    }
}

$output['token_debug'] = getAccessTokenDebug();

echo json_encode($output, JSON_PRETTY_PRINT);
