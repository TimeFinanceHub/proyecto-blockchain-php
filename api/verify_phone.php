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
    $code = $data['code'] ?? '';

    if (empty($code)) {
        $response['message'] = 'Verification code is required.';
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Fetch user's current phone verification code and expiry
        $stmt = $pdo->prepare("SELECT phone_verification_code, phone_code_expiry FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['phone_verification_code'] === $code) {
                if (strtotime($user['phone_code_expiry']) > time()) {
                    // Code is correct and not expired, verify phone
                    $stmt = $pdo->prepare("UPDATE users SET phone_verified = 1, phone_verification_code = NULL, phone_code_expiry = NULL WHERE id = :user_id");
                    $stmt->execute(['user_id' => $user_id]);

                    $response['status'] = 'success';
                    $response['message'] = 'Phone number verified successfully!';
                } else {
                    $response['message'] = 'Verification code has expired.';
                }
            } else {
                $response['message'] = 'Invalid verification code.';
            }
        } else {
            $response['message'] = 'User not found.'; // Should not happen if user_id from session is valid
        }

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Phone verification error: " . $e->getMessage());
        http_response_code(500);
    }
} else {
    $response['message'] = 'Method not allowed.';
    http_response_code(405);
}

echo json_encode($response);
