<?php
session_start();
// In a real scenario, this page would verify payment success before serving the file.
// For this simulation, we'll assume a "successful payment" trigger from the frontend.

// Path to the ZIP file
$file_path = __DIR__ . '/../proyecto_blockchain_php.zip'; // Adjust path as needed

// Check if the file exists
if (!file_exists($file_path)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Project file not found on server.']);
    http_response_code(404);
    exit();
}

// Ensure output buffering is turned off
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Read the file and output it to the browser
readfile($file_path);
exit();
