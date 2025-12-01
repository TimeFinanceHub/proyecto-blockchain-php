<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $phone = $data['phone'] ?? ''; // Assuming phone number might be passed or retrieved from user session

    // In a real app, you'd validate the phone number format here.

    try {
        // Generate a 6-digit code
        $verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $code_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Code valid for 10 minutes

        // Store code and expiry in the database for the user
        $stmt = $pdo->prepare("UPDATE users SET phone_verification_code = :code, phone_code_expiry = :expiry WHERE id = :user_id");
        $stmt->execute(['code' => $verification_code, 'expiry' => $code_expiry, 'user_id' => $user_id]);

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Verification code generated.';
            $response['simulated_code'] = $verification_code; // *** THIS IS FOR SIMULATION ONLY ***
                                                               // In production, this would be sent via SMS gateway.
        } else {
            $response['message'] = 'Failed to generate code for user.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Phone verification code generation error: " . $e->getMessage());
        http_response_code(500);
    }
} else {
    $response['message'] = 'Method not allowed.';
    http_response_code(405);
}

echo json_encode($response);
