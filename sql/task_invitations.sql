CREATE TABLE IF NOT EXISTS task_invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    inviter_id INT NOT NULL,
    invitee_email VARCHAR(255) NOT NULL,
    message TEXT,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (inviter_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX (token)
);