-- Créer la base de données
CREATE DATABASE IF NOT EXISTS citypulse;

-- Utiliser la base de données
USE citypulse;

-- Créer la table des offres d'emploi
CREATE TABLE IF NOT EXISTS offres (
    id VARCHAR(10) PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    entreprise VARCHAR(100) NOT NULL,
    emplacement VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    date DATE NOT NULL,
    type VARCHAR(50) NOT NULL
);

-- Insérer des offres d'exemple
INSERT INTO offres (id, titre, entreprise, emplacement, description, date, type) VALUES
('OFR123', 'Développeur Web', 'TechCorp', 'Paris', 'Développement d’applications web modernes', '2025-05-01', 'CDI'),
('OFR124', 'Data Analyst', 'DataX', 'Lyon', 'Analyse de données pour des projets innovants', '2025-05-02', 'CDD');

-- Créer la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    adresse VARCHAR(255)
);

-- Insérer des utilisateurs d'exemple (password = 'password123' hashed)
INSERT INTO users (nom, prenom, email, password, adresse) VALUES
('Dupont', 'Jean', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '12 rue de Paris, Lyon'),
('Martin', 'Claire', 'claire.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '45 avenue Victor Hugo, Marseille'),
('Nguyen', 'Linh', 'linh.nguyen@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '88 boulevard Haussmann, Paris');

-- Créer la table des entretiens
CREATE TABLE IF NOT EXISTS entretiens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_offre VARCHAR(10),
    id_user INT,
    competences JSON,
    presentation TEXT,
    motivation TEXT,
    pourquoi_lui TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (id_offre) REFERENCES offres(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);

-- Exemple d'entretien
INSERT INTO entretiens (id_offre, id_user, competences, presentation, motivation, pourquoi_lui, status)
VALUES (
    'OFR123',
    1,
    JSON_OBJECT('langages', 'Python, JavaScript', 'softskills', 'communication, adaptabilité'),
    'Je suis passionné par le développement web depuis plus de 5 ans.',
    'Je souhaite contribuer à des projets innovants et stimulants.',
    'Je suis rigoureux, motivé et j’ai une bonne capacité d’adaptation.',
    'pending'
);
