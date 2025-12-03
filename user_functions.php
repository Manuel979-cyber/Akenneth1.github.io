<?php

 
require_once 'config.php';
 

function createUser($userData) {
    $pdo = getDbConnection();
    if (empty($userData['nom']) || empty($userData['prenom']) || empty($userData['email']) || empty($userData['password']) || empty($userData['role'])) {
        return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
    }
    if (emailExists($userData['email'])) {
        return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :password, :role)");
        $stmt->execute([
            ':nom' => cleanInput($userData['nom']),
            ':prenom' => cleanInput($userData['prenom']),
            ':email' => cleanInput($userData['email']),
            ':password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            ':role' => cleanInput($userData['role'])
        ]);
        return ['success' => true, 'message' => 'Compte créé avec succès', 'user_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }
}
 

function emailExists($email) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn() > 0;
}
 

function authenticateUser($email, $password) {
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if (!$user) return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        if (!$user['active']) return ['success' => false, 'message' => 'Votre compte a été désactivé. Contactez l\'administrateur.'];
        if (password_verify($password, $user['password'])) {
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id")->execute([':id' => $user['id']]);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            return ['success' => true, 'message' => 'Connexion réussie', 'user' => $user];
        }
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la connexion'];
    }
}
 

function logoutUser() {
    session_unset();
    session_destroy();
}
 
// Récupère tous les utilisateurs
function getAllUsers() {
    return getDbConnection()->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
}
 
// Récupère un utilisateur par ID
function getUserById($userId) {
    $stmt = getDbConnection()->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch();
}
 
// Récupère l'utilisateur connecté
function getCurrentUser() {
    return isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
}
 
// Compte les utilisateurs par rôle
function countUsersByRole() {
    $results = getDbConnection()->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
    $counts = [ROLE_ADMIN => 0, ROLE_ECOLE => 0, ROLE_ENTREPRISE => 0, ROLE_UTILISATEUR => 0];
    foreach ($results as $row) $counts[$row['role']] = $row['count'];
    return $counts;
}
 
// Récupère les utilisateurs connectés récemment
function getConnectedUsers() {
    return getDbConnection()->query("SELECT * FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY last_login DESC")->fetchAll();
}
 
// Met à jour un utilisateur
function updateUser($userId, $newData) {
    $pdo = getDbConnection();
    try {
        $updates = [];
        $params = [':id' => $userId];
        if (isset($newData['nom'])) {
            $updates[] = "nom = :nom";
            $params[':nom'] = cleanInput($newData['nom']);
        }
        if (isset($newData['prenom'])) {
            $updates[] = "prenom = :prenom";
            $params[':prenom'] = cleanInput($newData['prenom']);
        }
        if (isset($newData['email'])) {
            $existingUser = getUserByEmail($newData['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
            $updates[] = "email = :email";
            $params[':email'] = cleanInput($newData['email']);
        }
        if (isset($newData['password']) && !empty($newData['password'])) {
            $updates[] = "password = :password";
            $params[':password'] = password_hash($newData['password'], PASSWORD_DEFAULT);
        }
        if (empty($updates)) return ['success' => false, 'message' => 'Aucune modification à effectuer'];
        $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id")->execute($params);
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            if (isset($newData['nom'])) $_SESSION['user_nom'] = $newData['nom'];
            if (isset($newData['prenom'])) $_SESSION['user_prenom'] = $newData['prenom'];
            if (isset($newData['email'])) $_SESSION['user_email'] = $newData['email'];
        }
        return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}
 
// Active/désactive un utilisateur
function toggleUserStatus($userId, $active) {
    try {
        getDbConnection()->prepare("UPDATE users SET active = :active WHERE id = :id")->execute([':active' => $active ? 1 : 0, ':id' => $userId]);
        return ['success' => true, 'message' => 'Utilisateur ' . ($active ? 'activé' : 'désactivé') . ' avec succès'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la modification'];
    }
}
 
// Récupère un utilisateur par email
function getUserByEmail($email) {
    $stmt = getDbConnection()->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}
?>