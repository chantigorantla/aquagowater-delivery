<?php

/**
 * SMTP Email Test - Port 465 (SSL)
 * Run this: http://172.23.50.148/waterapp/email_debug.php
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<h2>SMTP Email Test (Port 465 SSL)</h2>";
echo "<pre>";

$host = 'smtp.gmail.com';
$port = 465; // SSL port instead of 587
$username = 'chantigorantla848@gmail.com';
$password = 'okrb atpa lztx fpvz';
$to = 'chantigorantla848@gmail.com';

echo "Testing SMTP connection to $host:$port (SSL)\n";
echo "Username: $username\n\n";

// Connect with SSL
echo "Step 1: Connecting with SSL...\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$socket = @stream_socket_client(
    "ssl://$host:$port",
    $errno,
    $errstr,
    30,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    echo "FAILED! Error: $errstr ($errno)\n\n";
    echo "Both port 587 and 465 are blocked.\n";
    echo "Your network/firewall is blocking SMTP connections.\n\n";
    echo "ALTERNATIVE SOLUTION:\n";
    echo "Use a web-based email API like SendGrid or Mailgun.\n";
    die("</pre>");
}
echo "SUCCESS! Connected via SSL.\n\n";

// Read greeting
echo "Step 2: Reading greeting...\n";
$response = fgets($socket, 515);
echo "Response: $response";

// EHLO
echo "\nStep 3: EHLO...\n";
fputs($socket, "EHLO localhost\r\n");
while ($line = fgets($socket, 515)) {
    if (substr($line, 3, 1) == " ") break;
}
echo "SUCCESS!\n\n";

// AUTH
echo "Step 4: Authenticating...\n";
fputs($socket, "AUTH LOGIN\r\n");
fgets($socket, 515);

fputs($socket, base64_encode($username) . "\r\n");
fgets($socket, 515);

fputs($socket, base64_encode($password) . "\r\n");
$response = fgets($socket, 515);
echo "Auth Response: $response";

if (substr($response, 0, 3) != '235') {
    echo "FAILED! Check your App Password.\n";
    fclose($socket);
    die("</pre>");
}
echo "SUCCESS!\n\n";

// Send email
echo "Step 5: Sending test email...\n";
fputs($socket, "MAIL FROM: <$username>\r\n");
fgets($socket, 515);

fputs($socket, "RCPT TO: <$to>\r\n");
fgets($socket, 515);

fputs($socket, "DATA\r\n");
fgets($socket, 515);

$emailContent = "To: $to\r\n";
$emailContent .= "From: AquaGo <$username>\r\n";
$emailContent .= "Subject: SMTP Test (Port 465) - " . date('H:i:s') . "\r\n";
$emailContent .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$emailContent .= "<h1>Port 465 SSL Works!</h1><p>Your email is working now.</p>\r\n.\r\n";

fputs($socket, $emailContent);
$response = fgets($socket, 515);
echo "Response: $response";

fputs($socket, "QUIT\r\n");
fclose($socket);

if (substr($response, 0, 3) == '250') {
    echo "\n<b style='color:green;'>SUCCESS! Email sent! Check your inbox.</b>\n";
} else {
    echo "\nFailed to send.\n";
}

echo "</pre>";
