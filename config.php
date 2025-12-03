<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '8889');        
define('DB_NAME', 'quizzeo');            
define('DB_USER', 'root');              
define('DB_PASS', 'Bonneannee');                   
define('DB_CHARSET', 'utf8mb4');         


/**
 *  connexion PDO 
 * @return PDO 
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    return $pdo;
}



define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');


define('ROLE_ADMIN', 'admin');
define('ROLE_ECOLE', 'ecole');
define('ROLE_ENTREPRISE', 'entreprise');
define('ROLE_UTILISATEUR', 'utilisateur');


define('QUIZ_STATUS_DRAFT', 'en_cours_ecriture');
define('QUIZ_STATUS_ACTIVE', 'lance');
define('QUIZ_STATUS_CLOSED', 'termine');


define('SESSION_TIMEOUT', 1800); 
define('MAX_LOGIN_ATTEMPTS', 5);


/**
 * Vérifie si un utilisateur est connecté
 * @return bool 
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string
 * @return bool 
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Redirige vers une page
 * @param string $page La page de destination
 */
function redirect($page) {
    header("Location: $page");
    exit();
}

/**
 * Vérifie le timeout de session
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            redirect('login.php?timeout=1');
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Nettoie les données entrées par l'utilisateur
 * @param string 
 * @return string 
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Génère un code de lien unique pour un quiz
 * @return string
 */
function generateQuizLinkCode() {
    return substr(md5(uniqid(rand(), true)), 0, 8);
}

/**
 * Formate une date pour l'affichage
 * @param string 
 * @return string 
 */
function formatDate($date) {
    if (!$date) return 'Jamais';
    return date('d/m/Y H:i', strtotime($date));
}


if (isLoggedIn()) {
    checkSessionTimeout();
}

?>