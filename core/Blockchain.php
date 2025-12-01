<?php
require_once 'Block.php';

class Blockchain {
    public $chain;
    public $difficulty;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->difficulty = 4; // Basic difficulty
        $this->chain = $this->getChainFromDB();
        if (empty($this->chain)) {
            $this->chain[] = $this->createGenesisBlock();
            $this->saveBlockToDB($this->chain[0]);
        }
    }

    private function getChainFromDB() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM blockchain ORDER BY block_index ASC");
            $chain = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $block = new Block($row['block_index'], $row['timestamp'], json_decode($row['data'], true), $row['previous_hash']);
                $block->hash = $row['hash'];
                $block->nonce = $row['nonce'];
                $chain[] = $block;
            }
            return $chain;
        } catch (PDOException $e) {
            // Handle DB error
            return [];
        }
    }

    private function saveBlockToDB($block) {
        try {
            $sql = "INSERT INTO blockchain (block_index, timestamp, data, previous_hash, hash, nonce) 
                    VALUES (:index, :timestamp, :data, :previous_hash, :hash, :nonce)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'index' => $block->index,
                'timestamp' => $block->timestamp,
                'data' => json_encode($block->data),
                'previous_hash' => $block->previousHash,
                'hash' => $block->hash,
                'nonce' => $block->nonce
            ]);
        } catch (PDOException $e) {
            // Handle DB error
        }
    }

    private function createGenesisBlock() {
        return new Block(0, time(), "Genesis Block", "0");
    }

    public function getLatestBlock() {
        return $this->chain[count($this->chain) - 1];
    }

    public function addBlock($newBlock) {
        $newBlock->previousHash = $this->getLatestBlock()->hash;
        $newBlock->mineBlock($this->difficulty);
        $this->chain[] = $newBlock;
        $this->saveBlockToDB($newBlock);
    }

    public function isChainValid() {
        for ($i = 1; $i < count($this->chain); $i++) {
            $currentBlock = $this->chain[$i];
            $previousBlock = $this->chain[$i - 1];

            if ($currentBlock->hash !== $currentBlock->calculateHash()) {
                return false;
            }

            if ($currentBlock->previousHash !== $previousBlock->hash) {
                return false;
            }
        }
        return true;
    }
}
