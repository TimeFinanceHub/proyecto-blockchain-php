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

    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'public'; // Default to public
            $post_id = $_GET['id'] ?? null;
            $user_posts_only = isset($_GET['user_posts_only']) && $_GET['user_posts_only'] == 'true';


            $sql_params = [];

            if ($post_id) {
                // Fetch a single post
                $sql = "SELECT p.id, p.user_id, u.username, p.title, p.content, p.is_public, p.created_at, p.updated_at FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = :id";
                $sql_params['id'] = $post_id;
            } elseif ($type === 'public') {
                // Fetch all public posts
                $sql = "SELECT p.id, p.user_id, u.username, p.title, p.content, p.is_public, p.created_at, p.updated_at FROM posts p JOIN users u ON p.user_id = u.id WHERE p.is_public = 1 ORDER BY p.created_at DESC";
            } elseif ($type === 'private' || $user_posts_only) {
                // Fetch private posts or all posts for a specific user (authentication required)
                if (!isset($_SESSION['user_id'])) {
                    $response['message'] = 'Authentication required to view private posts.';
                    http_response_code(401);
                    echo json_encode($response);
                    exit();
                }
                $current_user_id = $_SESSION['user_id'];
                $sql = "SELECT p.id, p.user_id, u.username, p.title, p.content, p.is_public, p.created_at, p.updated_at FROM posts p JOIN users u ON p.user_id = u.id WHERE p.user_id = :user_id ORDER BY p.created_at DESC";
                $sql_params['user_id'] = $current_user_id;
            } else {
                $response['message'] = 'Invalid GET request type.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($sql_params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // If fetching a single post, return just the object
            if ($post_id) {
                if ($posts) {
                    $response = ['status' => 'success', 'post' => $posts[0]];
                } else {
                    $response = ['status' => 'error', 'message' => 'Post not found.'];
                    http_response_code(404);
                }
            } else {
                $response = ['status' => 'success', 'posts' => $posts];
            }
            break;

        case 'POST':
            // Authentication check
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Authentication required to create posts.';
                http_response_code(401);
                echo json_encode($response);
                exit();
            }
            $current_user_id = $_SESSION['user_id'];

            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $is_public = (int)($data['is_public'] ?? 1); // Default to public

            if (empty($title) || empty($content)) {
                $response['message'] = 'Title and content are required.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $sql = "INSERT INTO posts (user_id, title, content, is_public) VALUES (:user_id, :title, :content, :is_public)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $current_user_id,
                'title' => $title,
                'content' => $content,
                'is_public' => $is_public
            ]);

            $new_post_id = $pdo->lastInsertId();
            $response = [
                'status' => 'success',
                'message' => 'Post created successfully.',
                'post' => [
                    'id' => $new_post_id,
                    'user_id' => $current_user_id,
                    'title' => $title,
                    'content' => $content,
                    'is_public' => $is_public,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ];
            break;

        case 'PUT':
            // Authentication check
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Authentication required to update posts.';
                http_response_code(401);
                echo json_encode($response);
                exit();
            }
            $current_user_id = $_SESSION['user_id'];

            $post_id = $data['id'] ?? null;
            $title = $data['title'] ?? null;
            $content = $data['content'] ?? null;
            $is_public = isset($data['is_public']) ? (int)$data['is_public'] : null;

            if (!$post_id) {
                $response['message'] = 'Post ID is required for update.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            $update_fields = [];
            $sql_params = ['id' => $post_id];

            if ($title !== null) {
                $update_fields[] = 'title = :title';
                $sql_params['title'] = $title;
            }
            if ($content !== null) {
                $update_fields[] = 'content = :content';
                $sql_params['content'] = $content;
            }
            if ($is_public !== null) {
                $update_fields[] = 'is_public = :is_public';
                $sql_params['is_public'] = $is_public;
            }

            if (empty($update_fields)) {
                $response['message'] = 'No fields provided for update.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            // Check ownership for non-admin users (assuming no admin bypass for now)
            $check_owner_sql = "SELECT user_id FROM posts WHERE id = :id";
            $stmt_owner = $pdo->prepare($check_owner_sql);
            $stmt_owner->execute(['id' => $post_id]);
            $post_owner = $stmt_owner->fetchColumn();

            if ($post_owner !== false && $post_owner != $current_user_id) {
                $response['message'] = 'Permission denied. You can only update your own posts.';
                http_response_code(403); // Forbidden
                echo json_encode($response);
                exit();
            }


            $sql = "UPDATE posts SET " . implode(', ', $update_fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($sql_params);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Post updated successfully.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Post not found or no changes made.'];
                http_response_code(404);
            }
            break;

        case 'DELETE':
            // Authentication check
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Authentication required to delete posts.';
                http_response_code(401);
                echo json_encode($response);
                exit();
            }
            $current_user_id = $_SESSION['user_id'];

            $post_id = $_GET['id'] ?? null;

            if (!$post_id) {
                $response['message'] = 'Post ID is required for deletion.';
                http_response_code(400);
                echo json_encode($response);
                exit();
            }

            // Check ownership for non-admin users (assuming no admin bypass for now)
            $check_owner_sql = "SELECT user_id FROM posts WHERE id = :id";
            $stmt_owner = $pdo->prepare($check_owner_sql);
            $stmt_owner->execute(['id' => $post_id]);
            $post_owner = $stmt_owner->fetchColumn();

            if ($post_owner !== false && $post_owner != $current_user_id) {
                $response['message'] = 'Permission denied. You can only delete your own posts.';
                http_response_code(403); // Forbidden
                echo json_encode($response);
                exit();
            }

            $sql = "DELETE FROM posts WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $post_id]);

            if ($stmt->rowCount() > 0) {
                $response = ['status' => 'success', 'message' => 'Post deleted successfully.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Post not found or no changes made.'];
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
