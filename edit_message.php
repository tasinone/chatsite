<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id']) && isset($input['message'])) {
    $id = $input['id'];
    $message = trim($input['message']);
    if (!empty($message)) {
        try {
            $stmt = $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?");
            $result = $stmt->execute([$message, $id]);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Message updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update message']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}
?>