<?php
require_once 'db_connect.php';

function makeLinksClickable($text) {
    $urlPattern = '/https?:\/\/\S+/i';
    return preg_replace($urlPattern, '<a href="$0" target="_blank">$0</a>', $text);
}

$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    echo "<div class='message' data-id='{$row['id']}'>";
    echo "<div class='message-content'>";
    $messageText = makeLinksClickable(htmlspecialchars($row['message']));
    $messageText = preg_replace('/\r\n|\r|\n/', '<br>', $messageText); // Replace newlines with <br> without adding extra lines
    echo "<div class='message-text'>" . $messageText . "</div>";
    
    // Display attachments
    $attachments = json_decode($row['attachments'], true);
    if (!empty($attachments)) {
        echo "<div class='attachments'>";
        foreach ($attachments as $attachment) {
            $displayName = (strlen($attachment) > 20) ? substr($attachment, 0, 20) . '...' : $attachment;
            echo "<a href='uploads/" . htmlspecialchars($attachment) . "' target='_blank'>" . htmlspecialchars($displayName) . "</a><br>";
        }
        echo "</div>";
    }
    echo "</div>";
    
    echo "<div class='message-actions'>";
    echo "<button class='edit-button' data-id='{$row['id']}'>Edit</button>";
    echo "<button class='delete-button' data-id='{$row['id']}'>Delete</button>";
    echo "</div></div>";
}
?>