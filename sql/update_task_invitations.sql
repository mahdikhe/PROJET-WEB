-- Drop the existing table if it exists
DROP TABLE IF EXISTS task_invitations;

-- Recreate the table without inviter_id constraint
CREATE TABLE task_invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    invitee_email VARCHAR(255) NOT NULL,
    message TEXT,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    INDEX (token)
);