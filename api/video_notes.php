<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    http_response_code(401);
    exit();
}

require_once 'config.php';
$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request.'];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $stmt = $pdo->prepare("SELECT id, video_url, timestamp_in_video, title, content, created_at, updated_at FROM video_notes WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute(['user_id' => $user_id]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['status' => 'success', 'notes' => $notes];
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $video_url = $data['video_url'] ?? null;
            $timestamp_in_video = $data['timestamp_in_video'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';

            if (empty($title) || empty($content)) {
                $response['message'] = 'Title and content are required.';
                http_response_code(400);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO video_notes (user_id, video_url, timestamp_in_video, title, content) VALUES (:user_id, :video_url, :timestamp_in_video, :title, :content)");
            $stmt->execute([
                'user_id' => $user_id,
                'video_url' => $video_url,
                'timestamp_in_video' => $timestamp_in_video,
                'title' => $title,
                'content' => $content
            ]);
            
            $new_note_id = $pdo->lastInsertId();
            $response = [
                'status' => 'success', 
                'message' => 'Note added successfully.',
                'note' => [
                    'id' => $new_note_id,
                    'video_url' => $video_url,
                    'timestamp_in_video' => $timestamp_in_video,
                    'title' => $title,
                    'content' => $content,
                    'created_at' => date('Y-m-d H:i:s'), // Placeholder for immediate feedback
                    'updated_at' => date('Y-m-d H:i:s')  // Placeholder for immediate feedback
                ]
            ];
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $video_url = $data['video_url'] ?? null;
            $timestamp_in_video = $data['timestamp_in_video'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';

            if (!$id || empty($title) || empty($content)) {
                $response['message'] = 'ID, title, and content are required for update.';
                http_response_code(400);
                break;
            }

            $stmt = $pdo->prepare("UPDATE video_notes SET video_url = :video_url, timestamp_in_video = :timestamp_in_video, title = :title, content = :content WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                'video_url' => $video_url,
                'timestamp_in_video' => $timestamp_in_video,
                'title' => $title,
                'content' => $content,
                'id' => $id,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Note updated successfully.'];
            } else {
                $response['message'] = 'Note not found or you do not have permission to update it.';
                http_response_code(404);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                $response['message'] = 'Note ID is required.';
                http_response_code(400);
                error_log("Video Notes API (DELETE): Note ID not provided.");
                break;
            }

            // --- Admin check for deletion ---
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
                error_log("Database error during admin check in video notes API: " . $e->getMessage());
                $response['message'] = 'Internal server error during admin check.';
                http_response_code(500);
                break;
            }
            // --- End Admin check ---
            error_log("Video Notes API (DELETE): Attempting to delete note ID: " . $id . " by user ID: " . $current_user_id . ", Is Admin: " . ($is_admin ? 'Yes' : 'No'));

            if ($is_admin) {
                // Admin can delete any note
                $stmt = $pdo->prepare("DELETE FROM video_notes WHERE id = :id");
                $stmt->execute(['id' => $id]);
            } else {
                // Regular user can only delete their own notes
                $stmt = $pdo->prepare("DELETE FROM video_notes WHERE id = :id AND user_id = :user_id");
                $stmt->execute(['id' => $id, 'user_id' => $user_id]);
            }

            $deleted_rows = $stmt->rowCount();
            error_log("Video Notes API (DELETE): Query affected " . $deleted_rows . " rows.");

            if ($deleted_rows > 0) {
                $response = ['status' => 'success', 'message' => 'Note deleted successfully.'];
            } else {
                $response['message'] = 'Note not found or you do not have permission to delete it.';
                http_response_code(404);
            }
            break;

        default:
            $response['message'] = 'Method not allowed.';
            http_response_code(405);
            break;
    }

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("Video notes API error: " . $e->getMessage());
    http_response_code(500);
}

echo json_encode($response);
