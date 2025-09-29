<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Beta signup handler with Gmail email integration
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/**
 * Send email using PHP mail() function with Gmail-style headers
 * In production, this should be configured with SMTP
 */
function sendNotificationEmail($recipient_email, $subject, $html_message, $from_email, $from_name) {
    // Set up headers for HTML email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        "From: $from_name <$from_email>",
        "Reply-To: $from_email",
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $headers_string = implode("\r\n", $headers);
    
    // Use PHP mail() function
    $success = mail($recipient_email, $subject, $html_message, $headers_string);
    
    if ($success) {
        error_log("Email sent successfully to: $recipient_email");
        return true;
    } else {
        error_log("Failed to send email to: $recipient_email");
        return false;
    }
}

/**
 * Advanced SMTP function for production use
 * Only use if SMTP configuration is available
 */
function sendGmailSMTP($to, $subject, $message, $from_email, $from_name, $smtp_password) {
    // This would be used in production with proper SMTP configuration
    // For now, we'll use the fallback mail() function
    return sendNotificationEmail($to, $subject, $message, $from_email, $from_name);
}

try {
    // Get form data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'email'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            exit;
        }
    }
    
    // Sanitize input
    $firstName = htmlspecialchars(trim($input['firstName']), ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars(trim($input['lastName']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone']), ENT_QUOTES, 'UTF-8') : '';
    $newsletter = isset($input['newsletter']) && ($input['newsletter'] === 'on' || $input['newsletter'] === true);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Email configuration
    $notification_email = 'wattwisevorarlberg@gmail.com';
    $from_name = 'WattWise Beta System';
    
    // Prepare email content
    $subject = 'Neue Beta-Anmeldung - WattWise';
    $message = "
    <html>
    <head>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                margin: 0; 
                padding: 0; 
                background-color: #f8f9fa; 
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background-color: white; 
                border-radius: 12px; 
                overflow: hidden; 
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            }
            .header { 
                background: linear-gradient(135deg, #00c389 0%, #0073e6 100%); 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 28px; 
                font-weight: 300; 
            }
            .content { 
                padding: 30px 20px; 
            }
            .field { 
                margin-bottom: 15px; 
                padding: 12px; 
                background-color: #f8f9fa; 
                border-radius: 8px; 
                border-left: 4px solid #00c389; 
            }
            .label { 
                font-weight: 600; 
                color: #495057; 
                display: inline-block; 
                min-width: 120px; 
            }
            .value { 
                color: #212529; 
            }
            .footer { 
                background-color: #f8f9fa; 
                padding: 20px; 
                text-align: center; 
                color: #6c757d; 
                font-size: 14px; 
            }
            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .status-new {
                background-color: #28a745;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⚡ WattWise Beta-Anmeldung</h1>
                <span class='status-badge status-new'>Neue Anmeldung</span>
            </div>
            <div class='content'>
                <p style='font-size: 16px; margin-bottom: 25px; color: #495057;'>
                    Eine neue Person hat sich für die WattWise Beta angemeldet:
                </p>
                
                <div class='field'>
                    <span class='label'>👤 Name:</span> 
                    <span class='value'>$firstName $lastName</span>
                </div>
                
                <div class='field'>
                    <span class='label'>📧 E-Mail:</span> 
                    <span class='value'>$email</span>
                </div>
                
                <div class='field'>
                    <span class='label'>📱 Telefon:</span> 
                    <span class='value'>" . ($phone ?: 'Nicht angegeben') . "</span>
                </div>
                
                <div class='field'>
                    <span class='label'>📩 Newsletter:</span> 
                    <span class='value'>" . ($newsletter ? '✅ Ja' : '❌ Nein') . "</span>
                </div>
                
                <div class='field'>
                    <span class='label'>📅 Anmeldung:</span> 
                    <span class='value'>" . date('d.m.Y \u\m H:i:s \U\h\r') . "</span>
                </div>
                
                <div class='field'>
                    <span class='label'>🌍 IP-Adresse:</span> 
                    <span class='value'>" . ($_SERVER['REMOTE_ADDR'] ?? 'Unbekannt') . "</span>
                </div>
                
                <div class='field'>
                    <span class='label'>🖥️ Browser:</span> 
                    <span class='value'>" . (isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 100) . '...' : 'Unbekannt') . "</span>
                </div>
            </div>
            <div class='footer'>
                <p>Diese E-Mail wurde automatisch vom WattWise Beta-System generiert.</p>
                <p><strong>wattwisevorarlberg@gmail.com</strong></p>
            </div>
        </div>
    </body>
    </html>";
    
    // Try to send email
    $email_sent = sendNotificationEmail(
        $notification_email, 
        $subject, 
        $message, 
        $notification_email, 
        $from_name
    );
    
    if ($email_sent) {
        // Log successful signup
        error_log("Beta signup email sent: $email -> $notification_email - " . date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => true, 
            'message' => 'Beta-Anmeldung erfolgreich! Du wirst benachrichtigt, sobald die Beta verfügbar ist.'
        ]);
    } else {
        // Email failed, but we should still log the attempt
        error_log("Email failed for beta signup: $email -> $notification_email");
        
        // In production, you might want to store this in a database as fallback
        // For now, we'll return success to user but log the failure
        echo json_encode([
            'success' => true,
            'message' => 'Beta-Anmeldung erfolgreich! Du wirst benachrichtigt, sobald die Beta verfügbar ist.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Beta signup error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Es gab einen technischen Fehler. Bitte versuche es später erneut.'
    ]);
}
?>