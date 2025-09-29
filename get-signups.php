<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $signups = [];
    $csv_file = __DIR__ . '/signups/beta-signups.csv';
    
    if (file_exists($csv_file) && is_readable($csv_file)) {
        $fp = fopen($csv_file, 'r');
        
        if ($fp) {
            // Skip header row
            $header = fgetcsv($fp);
            
            while (($row = fgetcsv($fp)) !== false) {
                if (count($row) >= 6) {
                    $signups[] = [
                        'timestamp' => $row[0],
                        'firstName' => $row[1],
                        'lastName' => $row[2],
                        'email' => $row[3],
                        'phone' => $row[4],
                        'newsletter' => $row[5] === 'Yes'
                    ];
                }
            }
            
            fclose($fp);
        }
    }
    
    // Sort by timestamp (newest first)
    usort($signups, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    echo json_encode([
        'success' => true,
        'signups' => $signups,
        'count' => count($signups)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error reading signups: ' . $e->getMessage(),
        'signups' => []
    ]);
}
?>