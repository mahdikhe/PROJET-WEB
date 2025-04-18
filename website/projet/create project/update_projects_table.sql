-- Ajout des colonnes pour les projets payants
ALTER TABLE projects 
ADD COLUMN is_paid TINYINT(1) DEFAULT 0 COMMENT 'Indique si le projet est payant (1) ou gratuit (0)',
ADD COLUMN ticket_price DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Prix du ticket si le projet est payant';

-- Mise à jour des projets existants comme gratuits par défaut
UPDATE projects SET is_paid = 0, ticket_price = 0.00;

-- Création d'une table pour les achats de tickets
CREATE TABLE IF NOT EXISTS project_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID de l''utilisateur qui a acheté le ticket',
    project_id INT NOT NULL COMMENT 'ID du projet concerné',
    amount DECIMAL(10,2) NOT NULL COMMENT 'Montant payé',
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Date d''achat',
    payment_status VARCHAR(50) DEFAULT 'pending' COMMENT 'État du paiement (pending, completed, cancelled)',
    payment_reference VARCHAR(100) COMMENT 'Référence de paiement externe',
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création d'une table pour le panier
CREATE TABLE IF NOT EXISTS shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID de l''utilisateur propriétaire du panier',
    project_id INT NOT NULL COMMENT 'ID du projet dans le panier',
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Date d''ajout au panier',
    UNIQUE KEY (user_id, project_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 