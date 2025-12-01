<?php
require_once 'config.php';
require_once '../core/Blockchain.php';

header('Content-Type: application/json');

try {
    $blockchain = new Blockchain($pdo);
    echo json_encode(['status' => 'success', 'chain' => $blockchain->chain]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
