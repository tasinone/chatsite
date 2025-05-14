-- Set the database character set and collation
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create the messages table if it doesn't exist
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    attachments TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add any initial data if needed
-- INSERT INTO messages (message) VALUES ('Welcome to the chatroom!');