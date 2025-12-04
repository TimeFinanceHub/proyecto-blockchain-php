<?php
session_start(); // Start the session
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Block.php';
require_once __DIR__ . '/core/Blockchain.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

// Authentication check
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required to mine a block.';
    http_response_code(401); // Unauthorized
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON input.';
        http_response_code(400); // Bad Request
        echo json_encode($response);
        exit();
    }

    if (!isset($data['data'])) {
        $response['message'] = 'Data for the new block is required.';
    } else {
        // Sanitize input data to prevent XSS
        $block_data = htmlspecialchars($data['data'], ENT_QUOTES, 'UTF-8');

        try {
            $blockchain = new Blockchain($pdo);
            $latestBlock = $blockchain->getLatestBlock();
            
            $newBlock = new Block(
                $latestBlock->index + 1,
                time(),
                $block_data // Use sanitized data
            );
            
            $blockchain->addBlock($newBlock);

            $response['status'] = 'success';
            $response['message'] = 'New block mined and added successfully!';
            $response['new_block'] = $newBlock;

        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    }
    echo json_encode($response);
}
