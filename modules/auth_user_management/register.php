<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON input.';
        http_response_code(400); // Bad Request
        echo json_encode($response);
        exit();
    }

    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $response['message'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
    } else {
        try {
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() > 0) {
                $response['message'] = 'Email already registered.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (username, email, phone, password) VALUES (:username, :email, :phone, :password)";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashed_password
                ]);

                $response['status'] = 'success';
                $response['message'] = 'Registration successful. Your account is awaiting administrator approval.';
            }
        } catch (PDOException $e) {
            // In a real app, log this error instead of echoing
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    echo json_encode($response);
}
