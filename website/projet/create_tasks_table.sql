-- Create tasks table for Jira-like functionality
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('To Do', 'In Progress', 'Review', 'Done') DEFAULT 'To Do',
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    assigned_to VARCHAR(255),
    created_by VARCHAR(255),
    estimated_hours DECIMAL(5,2),
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Create task_comments table for discussions on tasks
CREATE TABLE IF NOT EXISTS task_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Create task_attachments table for files attached to tasks
CREATE TABLE IF NOT EXISTS task_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100),
    file_size INT,
    uploaded_by VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Insert sample tasks
INSERT INTO tasks (project_id, title, description, status, priority, assigned_to, created_by, estimated_hours, due_date)
VALUES
(1, 'Design project mockups', 'Create initial design mockups for the urban garden layout', 'To Do', 'High', 'Sarah Designer', 'Project Manager', 8.5, DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)),
(1, 'Community survey', 'Conduct survey with local residents about garden preferences', 'In Progress', 'Medium', 'John Researcher', 'Project Manager', 12, DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)),
(1, 'Secure permits', 'Obtain necessary permits from city planning department', 'Done', 'Critical', 'Legal Team', 'Project Manager', 5, DATE_ADD(CURRENT_DATE, INTERVAL -7 DAY)),
(2, 'Traffic analysis', 'Analyze current traffic patterns at main intersections', 'In Progress', 'High', 'Traffic Engineer', 'Project Lead', 20, DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY)),
(2, 'AI model development', 'Develop initial AI model for traffic prediction', 'To Do', 'Critical', 'AI Team', 'Tech Lead', 40, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)),
(3, 'Historical documentation', 'Document current state of building with photos and measurements', 'Review', 'Medium', 'Documentation Team', 'Project Historian', 15, DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY));

-- Insert sample comments
INSERT INTO task_comments (task_id, user_name, comment)
VALUES
(1, 'Sarah Designer', 'Starting work on the mockups today. Will focus on the community area first.'),
(1, 'Project Manager', 'Great! Remember to include space for the educational garden section.'),
(2, 'John Researcher', 'Survey has been distributed to 100 local residents. Expecting responses within a week.'),
(3, 'Legal Team', 'All permits have been approved. We can proceed with the next phase.');

-- Create view for tasks with project information
CREATE OR REPLACE VIEW task_details AS
SELECT t.*, p.projectName as project_name, p.projectLocation as project_location
FROM tasks t
JOIN projects p ON t.project_id = p.id; 