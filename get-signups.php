<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Since we're now using email instead of CSV, return empty data
// The admin dashboard will rely on email notifications instead
try {
    echo json_encode([
        'success' => true,
        'signups' => [],
        'count' => 0,
        'message' => 'Email-based signup system is active. Check your email for notifications.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'signups' => []
    ]);
}
?>