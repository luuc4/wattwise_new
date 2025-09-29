<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Simple and reliable beta signup handler - saves to CSV file only
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
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
    
    // Create signups directory if it doesn't exist
    $backup_dir = __DIR__ . '/signups';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    // Save to CSV file (this is our primary tracking method)
    $backup_file = $backup_dir . '/beta-signups.csv';
    $csv_data = [
        date('Y-m-d H:i:s'),
        $firstName,
        $lastName,
        $email,
        $phone,
        $newsletter ? 'Yes' : 'No',
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    
    // Write to CSV file
    $fp = fopen($backup_file, 'a');
    if ($fp) {
        // Write header if file is empty
        if (filesize($backup_file) === 0) {
            fputcsv($fp, ['Timestamp', 'First Name', 'Last Name', 'Email', 'Phone', 'Newsletter', 'IP Address', 'User Agent']);
        }
        fputcsv($fp, $csv_data);
        fclose($fp);
        
        // Log successful signup
        error_log("Beta signup saved: $email - " . date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => true, 
            'message' => 'Beta-Anmeldung erfolgreich! Du wirst benachrichtigt, sobald die Beta verfügbar ist.'
        ]);
    } else {
        // If we can't save to file, something is seriously wrong
        error_log("Failed to open CSV file for beta signup: $email");
        
        echo json_encode([
            'success' => false,
            'message' => 'Es gab einen technischen Fehler. Bitte versuche es später erneut.'
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