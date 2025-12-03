<?php

require_once 'includes/config.php';
 
$pdo = getDbConnection();
 
echo "<h1>Vérification des Comptes Admin</h1>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#667eea;color:white;}</style>";
 

$sql = "SELECT id, nom, prenom, email, role, active, created_at FROM users WHERE role = 'admin'";
$stmt = $pdo->query($sql);
$admins = $stmt->fetchAll();
 
if (empty($admins)) {
    echo "<p style='color:red;'>❌ AUCUN COMPTE ADMIN TROUVÉ!</p>";
    echo "<p>Exécute cette requête SQL dans phpMyAdmin:</p>";
    echo "<pre>";
    echo "INSERT INTO users (nom, prenom, email, password, role, active) \n";
    echo "VALUES (\n";
    echo "    'Administrateur',\n";
    echo "    'Système',\n";
    echo "    'admin@quizzeo.local',\n";
    echo "    '\$2y\$12\$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5jtJ3sdHWUMLK',\n";
    echo "    'admin',\n";
    echo "    TRUE\n";
    echo ");";
    echo "</pre>";
} else {
    echo "<p style='color:green;'>✅ " . count($admins) . " compte(s) admin trouvé(s)</p>";
   
    echo "<table>";
    echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Actif</th><th>Créé le</th><th>Mot de passe possible</th></tr>";
   
    foreach ($admins as $admin) {
        echo "<tr>";
        echo "<td>" . $admin['id'] . "</td>";
        echo "<td>" . htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($admin['email']) . "</strong></td>";
        echo "<td>" . ($admin['active'] ? '✅ Actif' : '❌ Inactif') . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($admin['created_at'])) . "</td>";
       
        
        if (strpos($admin['email'], 'quizzeo.local') !== false) {
            echo "<td><code>Admin@2024!</code></td>";
        } elseif (strpos($admin['email'], 'test.com') !== false || strpos($admin['email'], 'quizzeo.com') !== false) {
            echo "<td><code>admin123</code></td>";
        } else {
            echo "<td>Inconnu</td>";
        }
       
        echo "</tr>";
    }
   
    echo "</table>";
   
    echo "<hr>";
    echo "<h2>🔧 Tester la Connexion</h2>";
   
    foreach ($admins as $admin) {
        echo "<div style='background:#f8f9fa;padding:15px;margin:10px 0;border-radius:8px;'>";
        echo "<strong>Compte " . $admin['id'] . ":</strong><br>";
        echo "Email: <code>" . htmlspecialchars($admin['email']) . "</code><br>";
       
        
        $passwords = ['admin123', 'Admin@2024!', 'admin', 'password'];
       
        foreach ($passwords as $pwd) {
            
            $sqlHash = "SELECT password FROM users WHERE id = :id";
            $stmtHash = $pdo->prepare($sqlHash);
            $stmtHash->execute([':id' => $admin['id']]);
            $hash = $stmtHash->fetch()['password'];
           
            if (password_verify($pwd, $hash)) {
                echo "✅ Mot de passe: <code style='background:#d4edda;padding:5px;'>" . $pwd . "</code><br>";
                break;
            }
        }
       
        echo "</div>";
    }
}
 
echo "<hr>";
echo "<h2>📝 Pour Mettre à Jour le Mot de Passe</h2>";
echo "<p>Exécute cette requête SQL:</p>";
echo "<pre>";
echo "UPDATE users \n";
echo "SET password = '\$2y\$12\$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5jtJ3sdHWUMLK',\n";
echo "    email = 'admin@quizzeo.local'\n";
echo "WHERE role = 'admin';";
echo "</pre>";
echo "<p>Puis connecte-toi avec:</p>";
echo "<ul>";
echo "<li>Email: <code>admin@quizzeo.local</code></li>";
echo "<li>Mot de passe: <code>Admin@2024!</code></li>";
echo "</ul>";
?>