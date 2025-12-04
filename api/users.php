<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    http_response_code(401);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'An error occurred.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $search_query = $_GET['q'] ?? '';

        if (empty($search_query)) {
            $response['message'] = 'Search query cannot be empty.';
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        // Sanitize search query
        $sanitized_query = '%' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '%';

        $sql = "SELECT id, username FROM users WHERE username LIKE :search_query AND id != :current_user_id LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search_query' => $sanitized_query, 'current_user_id' => $current_user_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = ['status' => 'success', 'users' => $users];

    } else {
        $response['message'] = 'Method not allowed.';
        http_response_code(405);
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
