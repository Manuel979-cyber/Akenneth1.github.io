CREATE DATABASE IF NOT EXISTS quizzeo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quizzeo;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'ecole', 'entreprise', 'utilisateur') NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    owner_role ENUM('ecole', 'entreprise') NOT NULL,
    status ENUM('en_cours_ecriture', 'lance', 'termine') DEFAULT 'en_cours_ecriture',
    active BOOLEAN DEFAULT TRUE,
    pin_code VARCHAR(6) NOT NULL UNIQUE COMMENT 'Code PIN à 6 chiffres',
    link_code VARCHAR(8) NOT NULL UNIQUE COMMENT 'Code pour le lien',
    show_answers BOOLEAN DEFAULT TRUE COMMENT 'Afficher correction immédiate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_status (status),
    INDEX idx_pin_code (pin_code),
    INDEX idx_link_code (link_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    type ENUM('qcm', 'qcm_multiple', 'libre') NOT NULL,
    correct_answers TEXT NULL COMMENT 'JSON array des index des bonnes réponses pour QCM multiple',
    points INT DEFAULT 1,
    order_num INT DEFAULT 0,
    time_limit INT DEFAULT 30 COMMENT 'Temps limite en secondes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    option_index INT NOT NULL COMMENT 'Index de l option (0, 1, 2, ...)',
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL COMMENT 'Nom du joueur (pas besoin de compte)',
    score DECIMAL(5,2) NULL COMMENT 'Score en pourcentage',
    earned_points INT NULL,
    total_points INT NULL,
    rank_position INT NULL COMMENT 'Position dans le classement',
    time_taken INT NULL COMMENT 'Temps total en secondes',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    INDEX idx_player (player_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS response_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_value TEXT NOT NULL COMMENT 'JSON array pour réponses multiples ou texte',
    is_correct BOOLEAN NULL COMMENT 'NULL pour questions libres',
    time_taken INT NULL COMMENT 'Temps pris pour cette question en secondes',
    FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_response (response_id),
    INDEX idx_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS quiz_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE,
    INDEX idx_quiz (quiz_id),
    UNIQUE KEY unique_player_quiz (quiz_id, player_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- hashage du Mot de passe
INSERT INTO users (nom, prenom, email, password, role, active) 
VALUES (
    'Admin',
    'Quizzeo',
    'admin@quizzeo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin',
    TRUE
) ON DUPLICATE KEY UPDATE email=email;

-
INSERT INTO users (nom, prenom, email, password, role, active) 
VALUES (
    'Dupont',
    'Marie',
    'ecole@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'ecole',
    TRUE
) ON DUPLICATE KEY UPDATE email=email;


INSERT INTO users (nom, prenom, email, password, role, active) 
VALUES (
    'Martin',
    'Pierre',
    'entreprise@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'entreprise',
    TRUE
) ON DUPLICATE KEY UPDATE email=email;


INSERT INTO users (nom, prenom, email, password, role, active) 
VALUES (
    'Durand',
    'Sophie',
    'utilisateur@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'utilisateur',
    TRUE
) ON DUPLICATE KEY UPDATE email=email;


CREATE OR REPLACE VIEW quiz_statistics AS
SELECT 
    q.id,
    q.titre,
    q.status,
    u.nom AS owner_nom,
    u.prenom AS owner_prenom,
    COUNT(DISTINCT r.id) AS nb_responses,
    COUNT(DISTINCT qu.id) AS nb_questions
FROM quiz q
LEFT JOIN users u ON q.owner_id = u.id
LEFT JOIN responses r ON q.id = r.quiz_id
LEFT JOIN questions qu ON q.id = qu.quiz_id
GROUP BY q.id;


CREATE OR REPLACE VIEW recent_users AS
SELECT 
    id,
    nom,
    prenom,
    email,
    role,
    last_login
FROM users
WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
ORDER BY last_login DESC;

SELECT 'Base de données Quizzeo créée avec succès!' AS Message;
SELECT 'Compte admin créé: admin@quizzeo.com / admin123' AS Info;
