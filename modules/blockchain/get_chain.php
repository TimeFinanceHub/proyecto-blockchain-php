<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Blockchain.php';

header('Content-Type: application/json');

try {
    $blockchain = new Blockchain($pdo);
    echo json_encode(['status' => 'success', 'chain' => $blockchain->chain]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
