<?php

session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre un Quiz - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .join-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .join-container {
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .join-logo {
            font-size: 72px;
            margin-bottom: 20px;
        }
        
        .join-title {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 40px;
            font-weight: bold;
        }
        
        .join-form input {
            width: 100%;
            padding: 20px;
            font-size: 24px;
            border: 3px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .join-form input:focus {
            outline: none;
            border-color: #667eea;
            transform: scale(1.02);
        }
        
        .join-form input[name="pin"] {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 10px;
        }
        
        .join-btn {
            width: 100%;
            padding: 20px;
            font-size: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .join-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .error-message {
            background: #ff4757;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .info-text {
            color: #666;
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
</head>
<body class="join-page">
    <div class="join-container">
        <div class="join-logo"></div>
        <h1 class="join-title">Quizzeo</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                switch($_GET['error']) {
                    case 'pin':
                        echo ' Code PIN invalide';
                        break;
                    case 'name':
                        echo ' Veuillez entrer votre nom';
                        break;
                    case 'inactive':
                        echo ' Ce quiz n\'est pas actif';
                        break;
                    default:
                        echo ' Erreur inconnue';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="lobby.php" class="join-form">
            <input 
                type="text" 
                name="pin" 
                placeholder="CODE PIN" 
                maxlength="6" 
                pattern="[0-9]{6}"
                required
                autofocus
                inputmode="numeric"
            >
            
            <input 
                type="text" 
                name="player_name" 
                placeholder="Votre nom" 
                maxlength="50"
                required
            >
            
            <button type="submit" class="join-btn">
                 REJOINDRE
            </button>
        </form>
        
        <p class="info-text">
            Entrez le code PIN à 6 chiffres fourni par votre professeur/formateur
        </p>
    </div>
    
    <script>
        
        document.querySelector('input[name="pin"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
