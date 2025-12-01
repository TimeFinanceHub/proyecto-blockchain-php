<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    http_response_code(401);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$admin_email = 'mostlyphpsoftware@gmail.com';

// Fetch current user's email to check for admin privileges
try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $current_user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$current_user || $current_user['email'] !== $admin_email) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied. Administrator privileges required.']);
        http_response_code(403); // Forbidden
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error during authorization: ' . $e->getMessage()]);
    http_response_code(500);
    exit();
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all users or filter by verification status if parameters are provided
        $sql = "SELECT id, username, email, phone, email_verified, phone_verified FROM users ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = ['status' => 'success', 'users' => $users];
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response['message'] = 'Invalid JSON input.';
            http_response_code(400); // Bad Request
            echo json_encode($response);
            exit();
        }
        $user_id = $data['id'] ?? null;
        $email_verified = $data['email_verified'] ?? null;
        $phone_verified = $data['phone_verified'] ?? null;

        if (!$user_id) {
            $response['message'] = 'User ID is required.';
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        $update_fields = [];
        $params = ['id' => $user_id];

        if ($email_verified !== null) {
            $update_fields[] = 'email_verified = :email_verified';
            $params['email_verified'] = (int)$email_verified;
        }
        if ($phone_verified !== null) {
            $update_fields[] = 'phone_verified = :phone_verified';
            $params['phone_verified'] = (int)$phone_verified;
        }

        if (empty($update_fields)) {
            $response['message'] = 'No verification status provided for update.';
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $response = ['status' => 'success', 'message' => 'User verification status updated.'];
        } else {
            $response = ['status' => 'error', 'message' => 'User not found or no changes made.'];
            http_response_code(404);
        }
    } else {
        $response['message'] = 'Method not allowed.';
        http_response_code(405);
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
