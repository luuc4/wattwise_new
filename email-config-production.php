<?php
/**
 * WattWise Email Configuration for Production
 * 
 * This file contains the production SMTP configuration for Gmail.
 * Copy this to your production server and include it in submit-beta.php
 */

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'wattwisevorarlberg@gmail.com');
define('SMTP_PASSWORD', 'xave ohaw rmqp pwpk'); // App password
define('SMTP_FROM_NAME', 'WattWise Beta System');
define('SMTP_FROM_EMAIL', 'wattwisevorarlberg@gmail.com');

// Email settings
define('NOTIFICATION_EMAIL', 'wattwisevorarlberg@gmail.com');
define('USE_SMTP', true); // Set to false to use PHP mail() function

/**
 * Production SMTP mail function using Gmail
 * This function should work in production environments with internet access
 */
function sendProductionEmail($to, $subject, $message, $from_email = null, $from_name = null) {
    if (!USE_SMTP) {
        // Fallback to PHP mail() function
        $from_email = $from_email ?: SMTP_FROM_EMAIL;
        $from_name = $from_name ?: SMTP_FROM_NAME;
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: $from_name <$from_email>",
            "Reply-To: $from_email",
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    // Use SMTP
    $smtp_server = SMTP_HOST;
    $smtp_port = SMTP_PORT;
    $smtp_username = SMTP_USERNAME;
    $smtp_password = SMTP_PASSWORD;
    $from_email = $from_email ?: SMTP_FROM_EMAIL;
    $from_name = $from_name ?: SMTP_FROM_NAME;
    
    // Create socket connection
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        error_log("SMTP connection failed: $errstr ($errno)");
        return false;
    }
    
    // Helper function to send SMTP command
    function smtp_command($socket, $command, $expected_code = 250) {
        fwrite($socket, $command . "\r\n");
        $response = fgets($socket, 1024);
        $response_code = substr($response, 0, 3);
        return $response_code == $expected_code;
    }
    
    try {
        // Read initial server response
        $response = fgets($socket, 1024);
        
        // Send EHLO
        if (!smtp_command($socket, "EHLO " . $_SERVER['SERVER_NAME'] ?? 'localhost')) {
            throw new Exception("EHLO failed");
        }
        
        // Start TLS
        if (!smtp_command($socket, "STARTTLS", 220)) {
            throw new Exception("STARTTLS failed");
        }
        
        // Enable crypto
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception("TLS encryption failed");
        }
        
        // Send EHLO again after TLS
        if (!smtp_command($socket, "EHLO " . $_SERVER['SERVER_NAME'] ?? 'localhost')) {
            throw new Exception("EHLO after TLS failed");
        }
        
        // Authenticate
        if (!smtp_command($socket, "AUTH LOGIN", 334)) {
            throw new Exception("AUTH LOGIN failed");
        }
        
        if (!smtp_command($socket, base64_encode($smtp_username), 334)) {
            throw new Exception("Username authentication failed");
        }
        
        if (!smtp_command($socket, base64_encode($smtp_password), 235)) {
            throw new Exception("Password authentication failed");
        }
        
        // Send email
        if (!smtp_command($socket, "MAIL FROM: <$from_email>")) {
            throw new Exception("MAIL FROM failed");
        }
        
        if (!smtp_command($socket, "RCPT TO: <$to>")) {
            throw new Exception("RCPT TO failed");
        }
        
        if (!smtp_command($socket, "DATA", 354)) {
            throw new Exception("DATA command failed");
        }
        
        // Email headers and content
        $email_content = "From: $from_name <$from_email>\r\n";
        $email_content .= "To: $to\r\n";
        $email_content .= "Subject: $subject\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Date: " . date('r') . "\r\n";
        $email_content .= "\r\n";
        $email_content .= $message . "\r\n.\r\n";
        
        fwrite($socket, $email_content);
        $response = fgets($socket, 1024);
        $response_code = substr($response, 0, 3);
        
        if ($response_code != 250) {
            throw new Exception("Email send failed: $response");
        }
        
        // Quit
        smtp_command($socket, "QUIT", 221);
        fclose($socket);
        
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . $e->getMessage());
        fclose($socket);
        return false;
    }
}

/**
 * Instructions for Production Deployment:
 * 
 * 1. Upload this file to your web server
 * 2. Make sure your server has outbound internet access on port 587
 * 3. Ensure PHP has the openssl extension enabled for TLS
 * 4. Test the configuration with a simple test email
 * 5. Monitor your error logs for any SMTP issues
 * 
 * Gmail App Password Setup:
 * 1. Go to your Google Account settings
 * 2. Select Security → 2-Step Verification
 * 3. At the bottom, select App passwords
 * 4. Generate a new app password for "Mail"
 * 5. Use this 16-character password in SMTP_PASSWORD
 * 
 * Troubleshooting:
 * - Check that your server can reach smtp.gmail.com:587
 * - Verify the app password is correct
 * - Ensure 2-factor authentication is enabled on the Gmail account
 * - Monitor PHP error logs for detailed error messages
 */
?>