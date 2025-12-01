<?php
require_once 'config.php';

$message = "Email verification failed.";
$status_class = "error";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $pdo->beginTransaction();

        // Find user with this token and check expiry
        $stmt = $pdo->prepare("SELECT id, username, email, token_expiry FROM users WHERE verification_token = :token AND email_verified = 0");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if token has expired
            if (strtotime($user['token_expiry']) > time()) {
                // Update user to mark email as verified
                $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);

                $message = "Email verified successfully! You can now log in.";
                $status_class = "success";
            } else {
                $message = "Email verification link has expired. Please request a new one from your dashboard.";
            }
        } else {
            $message = "Invalid or already used verification link.";
        }

        $pdo->commit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Database error during verification: " . $e->getMessage();
        error_log("Email verification error: " . $e->getMessage()); // Log error for debugging
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #1c1e21;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.5;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        a {
            color: #1877f2;
            text-decoration: none;
            margin-top: 1rem;
            display: inline-block;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Verification Status</h1>
        <p class="<?php echo $status_class; ?>"><?php echo $message; ?></p>
        <a href="../login.php">Go to Login Page</a>
    </div>
</body>
</html>
