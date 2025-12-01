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
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM video_notes WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $id, 'user_id' => $user_id]);

            if ($stmt->rowCount() > 0) {
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
