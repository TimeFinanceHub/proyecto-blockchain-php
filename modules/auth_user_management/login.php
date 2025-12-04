<?php
session_start();
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

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Email and password are required.';
    } else {
        try {
            $sql = "SELECT id, password, email_verified, phone_verified FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['email_verified'] == 0 || $user['phone_verified'] == 0) {
                     $response['message'] = 'Espera a que el dueño del sistema apruebe tu email y teléfono.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $response['status'] = 'success';
                    $response['message'] = 'Login successful. Redirecting to dashboard...';
                }
            } else {
                $response['message'] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    echo json_encode($response);
}
