<?php

if (!isLoggedIn()) {
    redirect('../login.php');
}

$currentUser = getCurrentUser();
?>
<header>
    <div class="container">
        <div class="header-left">
            <img src="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/ecole/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/entreprise/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/utilisateur/') !== false 
                              ? '../assets/images/logo.png' : 'assets/images/logo.png'; ?>" 
                 alt="Logo Quizzeo" class="logo-small">
         
        </div>
        
        <nav>
            <ul>
                <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                                      strpos($_SERVER['PHP_SELF'], '/ecole/') !== false || 
                                      strpos($_SERVER['PHP_SELF'], '/entreprise/') !== false || 
                                      strpos($_SERVER['PHP_SELF'], '/utilisateur/') !== false 
                                      ? 'dashboard.php' : '../dashboard.php'; ?>">Dashboard</a></li>
                
                <?php if (hasRole(ROLE_ECOLE) || hasRole(ROLE_ENTREPRISE)): ?>
                    <li><a href="create_quiz.php">Créer un quiz</a></li>
                <?php endif; ?>
                
                <?php if (hasRole(ROLE_UTILISATEUR)): ?>
                    <li><a href="profil.php">Mon profil</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-info">
            <span>Bonjour, <strong><?php echo htmlspecialchars($currentUser['prenom']); ?></strong></span>
            <span class="badge badge-<?php echo $currentUser['role']; ?>">
                <?php echo ucfirst($currentUser['role']); ?>
            </span>
            <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/ecole/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/entreprise/') !== false || 
                              strpos($_SERVER['PHP_SELF'], '/utilisateur/') !== false 
                              ? '../logout.php' : 'logout.php'; ?>" 
               class="btn btn-sm btn-danger">Déconnexion</a>
        </div>
    </div>
</header>
