<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Check for malformed JSON input for POST/PUT requests
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
            $sql = "SELECT key_value FROM settings WHERE key_name = 'project_price'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $price_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($price_data) {
                $response = ['status' => 'success', 'project_price' => $price_data['key_value']];
            } else {
                // Default price if not found in DB
                $response = ['status' => 'success', 'project_price' => '60000.00']; // Default
            }
            break;

        case 'PUT':
            // Authentication and Authorization check (admin only)
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Authentication required.';
                http_response_code(401);
                echo json_encode($response);
                exit();
            }

            $current_user_id = $_SESSION['user_id'];
            $admin_email = 'mostlyphpsoftware@gmail.com';
            $is_admin = false;

            try {
                $stmt_user_email = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
                $stmt_user_email->execute(['user_id' => $current_user_id]);
                $user_email_data = $stmt_user_email->fetch(PDO::FETCH_ASSOC);
                if ($user_email_data && $user_email_data['email'] === $admin_email) {
                    $is_admin = true;
                }
            } catch (PDOException $e) {
                error_log("Database error during admin check in settings API: " . $e->getMessage());
                $response['message'] = 'Internal server error during admin check.';
                http_response_code(500);
                echo json_encode($response);
                exit();
            }

            if (!$is_admin) {
                $response['message'] = 'Access denied. Administrator privileges required.';
                http_response_code(403); // Forbidden
                echo json_encode($response);
                exit();
            }

            // --- Admin only from here ---
            $new_price = $data['project_price'] ?? null;

            if ($new_price === null) {
                $response['message'] = 'Project price is required.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }
            
            // Basic validation for price format
            if (!is_numeric($new_price) || $new_price < 0) {
                $response['message'] = 'Invalid price format. Must be a non-negative number.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            // Insert or Update the price
            $sql = "INSERT INTO settings (key_name, key_value) VALUES ('project_price', :price) ON DUPLICATE KEY UPDATE key_value = :price";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['price' => $new_price]);

            $response = ['status' => 'success', 'message' => 'Project price updated successfully.', 'project_price' => $new_price];
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
