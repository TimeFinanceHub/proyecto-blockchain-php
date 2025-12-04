<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Check for malformed JSON input for POST/PUT requests
    if ($method === 'POST' || $method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response['message'] = 'Invalid JSON input.';
            http_response_code(400); // Bad Request
            echo json_encode($response);
            exit();
        }
    }

    // Authentication check for all operations
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Authentication required.';
        http_response_code(401); // Unauthorized
        echo json_encode($response);
        exit();
    }
    $current_user_id = $_SESSION['user_id'];

    switch ($method) {
        case 'GET':
            $other_user_id = $_GET['other_user_id'] ?? null;

            if (!$other_user_id) {
                // If no specific other_user_id, return a list of users the current user has conversed with
                // This is a simplified approach, could be improved to show unread counts etc.
                $sql = "SELECT DISTINCT u.id, u.username
                        FROM users u
                        WHERE u.id != :current_user_id
                        AND (u.id IN (SELECT receiver_id FROM messages WHERE sender_id = :current_user_id_a)
                             OR u.id IN (SELECT sender_id FROM messages WHERE receiver_id = :current_user_id_b))";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['current_user_id' => $current_user_id, 'current_user_id_a' => $current_user_id, 'current_user_id_b' => $current_user_id]);
                $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response = ['status' => 'success', 'conversations' => $conversations];

            } else {
                // Fetch messages in a conversation
                $sql = "SELECT m.id, m.sender_id, s.username as sender_username, m.receiver_id, r.username as receiver_username, m.content, m.created_at, m.is_read
                        FROM messages m
                        JOIN users s ON m.sender_id = s.id
                        JOIN users r ON m.receiver_id = r.id
                        WHERE (m.sender_id = :current_user_id AND m.receiver_id = :other_user_id)
                           OR (m.sender_id = :other_user_id_b AND m.receiver_id = :current_user_id_c)
                        ORDER BY m.created_at ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'current_user_id' => $current_user_id,
                    'other_user_id' => $other_user_id,
                    'other_user_id_b' => $other_user_id,
                    'current_user_id_c' => $current_user_id
                ]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Mark received messages as read
                $update_sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = :current_user_id AND sender_id = :other_user_id_d AND is_read = 0";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    'current_user_id' => $current_user_id,
                    'other_user_id_d' => $other_user_id
                ]);

                $response = ['status' => 'success', 'messages' => $messages];
            }
            break;

        case 'POST':
            $receiver_id = $data['receiver_id'] ?? null;
            $content = $data['content'] ?? '';

            if (!$receiver_id || empty($content)) {
                $response['message'] = 'Receiver ID and content are required.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            // Sanitize content
            $sanitized_content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            $sql = "INSERT INTO messages (sender_id, receiver_id, content) VALUES (:sender_id, :receiver_id, :content)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'sender_id' => $current_user_id,
                'receiver_id' => $receiver_id,
                'content' => $sanitized_content
            ]);

            $new_message_id = $pdo->lastInsertId();
            $response = [
                'status' => 'success',
                'message' => 'Message sent successfully.',
                'sent_message' => [
                    'id' => $new_message_id,
                    'sender_id' => $current_user_id,
                    'receiver_id' => $receiver_id,
                    'content' => $sanitized_content,
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_read' => 0
                ]
            ];
            break;

        case 'PUT':
            $message_id = $data['message_id'] ?? null;

            if (!$message_id) {
                $response['message'] = 'Message ID is required.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $sql = "UPDATE messages SET is_read = 1 WHERE id = :id AND receiver_id = :receiver_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $message_id,
                'receiver_id' => $current_user_id
            ]);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Message marked as read.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Message not found or not owned by current user.'];
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
