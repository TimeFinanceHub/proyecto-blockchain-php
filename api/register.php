<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

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
                
                // Generate verification token (valid for 1 hour)
                $verification_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Generate simulated phone verification code (e.g., 6 digits)
                $phone_verification_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                // In a real scenario, this code would be sent via SMS gateway.
                // For this basic version, we'll include it in the response for simulation.

                $sql = "INSERT INTO users (username, email, phone, password, verification_token, token_expiry, phone_verified) VALUES (:username, :email, :phone, :password, :token, :token_expiry, 0)";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashed_password,
                    'token' => $verification_token,
                    'token_expiry' => $token_expiry
                ]);

                // --- Attempt to send verification email (using PHP's built-in mail function) ---
                $verification_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/proyecto_blockchain_php/api/verify_email.php?token=" . $verification_token;
                
                $subject = "Verify your email for Proyecto Blockchain PHP";
                $message = "Hi " . $username . ",\n\nPlease click on the following link to verify your email address:\n" . $verification_link . "\n\nThis link will expire in 1 hour.";
                $headers = "From: no-reply@yourdomain.com\r\n";
                $headers .= "Reply-To: no-reply@yourdomain.com\r\n";
                $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";

                // The mail() function returns true on successful attempt, but not necessarily successful delivery.
                // It's unreliable for production.
                $mail_sent = mail($email, $subject, $message, $headers);

                if ($mail_sent) {
                    $email_message = 'Registration successful. A verification email has been sent to your address. Please check your inbox (and spam folder).';
                } else {
                    $email_message = 'Registration successful, but we could not send the verification email. Please contact support.';
                }

                $response['status'] = 'success';
                $response['message'] = $email_message;
                $response['simulated_phone_code'] = $phone_verification_code; // For basic simulation feedback
                $response['simulated_email_link'] = $verification_link; // For basic simulation feedback
            }
        } catch (PDOException $e) {
            // In a real app, log this error instead of echoing
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    echo json_encode($response);
}
