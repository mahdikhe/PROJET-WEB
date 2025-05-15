CREATE DATABASE IF NOT EXISTS project_contribution;
USE project_contribution;

CREATE TABLE IF NOT EXISTS contributors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    city VARCHAR(100),
    phone VARCHAR(20),
    age_group VARCHAR(20),
    preferred_project INT,
    location_availability VARCHAR(50),
    contribution_type VARCHAR(50),
    message TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);