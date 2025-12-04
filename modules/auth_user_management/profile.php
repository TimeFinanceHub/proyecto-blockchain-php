<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'An error occurred.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response['message'] = 'Invalid JSON input.';
            http_response_code(400); // Bad Request
            echo json_encode($response);
            exit();
        }
    }

    switch ($method) {
        case 'GET':
            $sql = "SELECT username, email, phone, instagram_handle, email_verified, phone_verified FROM users WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_profile) {
                $response = ['status' => 'success', 'profile' => $user_profile];
            } else {
                $response = ['status' => 'error', 'message' => 'User profile not found.'];
                http_response_code(404);
            }
            break;

        case 'PUT':
            $username = $data['username'] ?? null;
            $phone = $data['phone'] ?? null;
            $instagram_handle = $data['instagram_handle'] ?? null;

            if ($username === null && $phone === null && $instagram_handle === null) {
                $response['message'] = 'No data provided for update.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $update_fields = [];
            $params = ['user_id' => $user_id];

            if ($username !== null) {
                $update_fields[] = 'username = :username';
                $params['username'] = $username;
            }
            if ($phone !== null) {
                $update_fields[] = 'phone = :phone';
                $params['phone'] = $phone;
            }
            if ($instagram_handle !== null) {
                $update_fields[] = 'instagram_handle = :instagram_handle';
                $params['instagram_handle'] = $instagram_handle;
            }
            
            // Email is typically not updated via profile settings directly
            // Verification statuses are updated via admin interface

            if (empty($update_fields)) {
                $response['message'] = 'No valid fields provided for update.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Profile updated successfully.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Profile not found or no changes made.'];
                http_response_code(404);
            }
            break;

        default:
            $response['message'] = 'Method not allowed.';
            http_response_code(405);
            break;
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
