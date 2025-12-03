<?php

require_once 'includes/config.php';
require_once 'includes/user_functions.php';
 
if (isLoggedIn()) {
    redirect('dashboard.php');
}
 
$error = '';
if (isset($_GET['timeout'])) {
    $error = 'Votre session a expiré. Veuillez vous reconnecter.';
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
   
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        $result = authenticateUser($email, $password);
        if ($result['success']) {
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo Quizzeo" class="logo">
            <h1>Quizzeo</h1>
            <p class="tagline">Plateforme de quiz en ligne</p>
        </div>
 
        <div class="login-form-container">
            <h2>Connexion</h2>
           
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
 
            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
 
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
 
                <button type="submit" name="login" class="btn btn-primary btn-block">Se connecter</button>
            </form>
 
            <div class="form-footer">
                <p style="color: #666; font-size: 14px;">Contactez l'administrateur pour obtenir un compte</p>
            </div>
        </div>
 
        <div class="info-box">
            <h3>Comptes de test</h3>
            <p><strong>Admin:</strong> admin@quizzeo.com / admin123</p>
            <p><strong>École:</strong> ecole@test.com / admin123</p>
            <p><strong>Entreprise:</strong> entreprise@test.com / admin123</p>
            <p><strong>Utilisateur:</strong> utilisateur@test.com / admin123</p>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
 