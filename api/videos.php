<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
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
            $stmt = $pdo->prepare("SELECT id, video_id FROM youtube_videos WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute(['user_id' => $user_id]);
            $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['status' => 'success', 'videos' => $videos];
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $response['message'] = 'Invalid JSON input.';
                http_response_code(400); // Bad Request
                echo json_encode($response);
                exit();
            }

            $url = $data['url'] ?? '';

            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                 $response['message'] = 'Invalid or empty URL provided.';
                 break;
            }
            
            $videoId = '';
            $regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
            if (preg_match($regex, $url, $matches)) {
                $videoId = $matches[1];
            }

            if (empty($videoId)) {
                $response['message'] = 'Could not extract YouTube video ID.';
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO youtube_videos (user_id, video_id) VALUES (:user_id, :video_id)");
            $stmt->execute(['user_id' => $user_id, 'video_id' => $videoId]);
            
            $new_video_id = $pdo->lastInsertId();
            $response = [
                'status' => 'success', 
                'message' => 'Video added successfully.',
                'video' => [
                    'id' => $new_video_id,
                    'video_id' => $videoId
                ]
            ];
            break;

        case 'DELETE':
            $video_id = $_GET['id'] ?? null;
            if (!$video_id) {
                $response['message'] = 'Video ID is required.';
                http_response_code(400);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM youtube_videos WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $video_id, 'user_id' => $user_id]);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Video deleted successfully.'];
            } else {
                $response['message'] = 'Video not found or you do not have permission to delete it.';
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
    http_response_code(500);
}

echo json_encode($response);
