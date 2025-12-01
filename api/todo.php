<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request.'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $sql = "SELECT id, task, is_completed FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = ['status' => 'success', 'tasks' => $tasks];
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $task = $data['task'] ?? '';

        if (!empty($task)) {
            $sql = "INSERT INTO tasks (user_id, task) VALUES (:user_id, :task)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'task' => $task]);
            $response = ['status' => 'success', 'message' => 'Task added.', 'task_id' => $pdo->lastInsertId()];
        } else {
            $response['message'] = 'Task cannot be empty.';
        }
    } 
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        $task_id = $data['id'] ?? null;
        $is_completed = $data['is_completed'] ?? null;

        if ($task_id !== null && $is_completed !== null) {
            $sql = "UPDATE tasks SET is_completed = :is_completed WHERE id = :id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['is_completed' => $is_completed, 'id' => $task_id, 'user_id' => $user_id]);
            $response = ['status' => 'success', 'message' => 'Task status updated.'];
        } else {
            $response['message'] = 'Missing task ID or completion status.';
        }
    } 
    elseif ($method === 'DELETE') {
        $task_id = $_GET['id'] ?? null;

        if ($task_id) {
            $sql = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $task_id, 'user_id' => $user_id]);
            $response = ['status' => 'success', 'message' => 'Task deleted.'];
        } else {
            $response['message'] = 'Missing task ID.';
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
