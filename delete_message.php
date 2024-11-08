<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id'])) {
    $id = $input['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $result = $stmt->execute([$id]);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete message']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>