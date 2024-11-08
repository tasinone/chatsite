<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

if (isset($_POST['message']) || isset($_FILES['attachment'])) {
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $attachments = [];

    // Handle file uploads
    if (isset($_FILES['attachment'])) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['attachment']['name'] as $key => $name) {
            $tmp_name = $_FILES['attachment']['tmp_name'][$key];
            $error = $_FILES['attachment']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . $name;
                if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                    $attachments[] = $filename;
                }
            }
        }
    }

    if (!empty($message) || !empty($attachments)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (message, attachments) VALUES (?, ?)");
            $result = $stmt->execute([$message, json_encode($attachments)]);
            if ($result) {
                $response = ['status' => 'success', 'message' => 'Message sent successfully'];
            } else {
                $response = ['status' => 'error', 'message' => 'Failed to send message'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Message and attachments cannot both be empty'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'No message or attachment provided'];
}

echo json_encode($response);
?>