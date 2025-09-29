<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

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
$firstName = filter_var($input['firstName'], FILTER_SANITIZE_STRING);
$lastName = filter_var($input['lastName'], FILTER_SANITIZE_STRING);
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$phone = isset($input['phone']) ? filter_var($input['phone'], FILTER_SANITIZE_STRING) : '';
$newsletter = isset($input['newsletter']) && $input['newsletter'] === 'on';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Email configuration - Replace with your Gmail SMTP settings
$to_email = 'your-email@gmail.com'; // Replace with your email
$subject = 'Neue Beta-Anmeldung für WattWise';

// Email content
$message = "Neue Beta-Anmeldung für WattWise\n\n";
$message .= "Vorname: $firstName\n";
$message .= "Nachname: $lastName\n";
$message .= "E-Mail: $email\n";
$message .= "Telefon: " . ($phone ?: 'Nicht angegeben') . "\n";
$message .= "Newsletter: " . ($newsletter ? 'Ja' : 'Nein') . "\n";
$message .= "Anmeldedatum: " . date('Y-m-d H:i:s') . "\n";
$message .= "IP-Adresse: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Email headers
$headers = "From: WattWise Beta <noreply@wattwise.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Store to file as backup (create signups directory if it doesn't exist)
$backup_dir = __DIR__ . '/signups';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$backup_file = $backup_dir . '/beta-signups.csv';
$csv_data = [
    date('Y-m-d H:i:s'),
    $firstName,
    $lastName,
    $email,
    $phone,
    $newsletter ? 'Yes' : 'No',
    $_SERVER['REMOTE_ADDR']
];

// Write to CSV
$fp = fopen($backup_file, 'a');
if ($fp) {
    // Write header if file is empty
    if (filesize($backup_file) === 0) {
        fputcsv($fp, ['Timestamp', 'First Name', 'Last Name', 'Email', 'Phone', 'Newsletter', 'IP Address']);
    }
    fputcsv($fp, $csv_data);
    fclose($fp);
}

// Send email
$email_sent = mail($to_email, $subject, $message, $headers);

if ($email_sent) {
    // Log successful signup
    error_log("Beta signup: $email - " . date('Y-m-d H:i:s'));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Beta-Anmeldung erfolgreich! Du wirst benachrichtigt, sobald die Beta verfügbar ist.'
    ]);
} else {
    // Log error but still save to file
    error_log("Failed to send email for beta signup: $email");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Anmeldung gespeichert! Du wirst benachrichtigt, sobald die Beta verfügbar ist.'
    ]);
}
?>