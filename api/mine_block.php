<?php
require_once 'config.php';
require_once '../core/Block.php';
require_once '../core/Blockchain.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

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
        try {
            $blockchain = new Blockchain($pdo);
            $latestBlock = $blockchain->getLatestBlock();
            
            $newBlock = new Block(
                $latestBlock->index + 1,
                time(),
                $data['data']
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
