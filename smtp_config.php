<?php

/**
 * SMTP Email Configuration for AquaGo
 * Using Port 465 with SSL (tested and working)
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'chantigorantla848@gmail.com');
define('SMTP_PASSWORD', 'vzwn ersm anvc frpk');
define('SMTP_FROM_NAME', 'AquaGo');
define('SMTP_FROM_EMAIL', 'chantigorantla848@gmail.com');

/**
 * Send email using SMTP with SSL (Port 465)
 */
function sendSmtpEmail($to, $subject, $htmlBody)
{
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client(
        "ssl://" . SMTP_HOST . ":" . SMTP_PORT,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        error_log("SMTP Connection failed: $errstr ($errno)");
        return false;
    }

    try {
        // Read greeting
        fgets($socket, 515);

        // EHLO
        fputs($socket, "EHLO localhost\r\n");
        while ($line = fgets($socket, 515)) {
            if (substr($line, 3, 1) == " ") break;
        }

        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 515);

        fputs($socket, base64_encode(SMTP_USERNAME) . "\r\n");
        fgets($socket, 515);

        fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
        $response = fgets($socket, 515);

        if (substr($response, 0, 3) != '235') {
            fclose($socket);
            return false;
        }

        // MAIL FROM
        fputs($socket, "MAIL FROM: <" . SMTP_FROM_EMAIL . ">\r\n");
        fgets($socket, 515);

        // RCPT TO
        fputs($socket, "RCPT TO: <" . $to . ">\r\n");
        fgets($socket, 515);

        // DATA
        fputs($socket, "DATA\r\n");
        fgets($socket, 515);

        // Email content
        $emailContent = "To: " . $to . "\r\n";
        $emailContent .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $emailContent .= "Subject: " . $subject . "\r\n";
        $emailContent .= "MIME-Version: 1.0\r\n";
        $emailContent .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailContent .= "\r\n";
        $emailContent .= $htmlBody . "\r\n";
        $emailContent .= ".\r\n";

        fputs($socket, $emailContent);
        $response = fgets($socket, 515);

        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return (substr($response, 0, 3) == '250');
    } catch (Exception $e) {
        if (isset($socket)) fclose($socket);
        return false;
    }
}
