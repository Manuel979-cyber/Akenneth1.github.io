<?php

require_once '../includes/config.php';
require_once '../includes/user_functions.php';
require_once '../includes/quiz_functions.php';

if (!isLoggedIn() || !hasRole(ROLE_ECOLE)) {
    redirect('../login.php');
}

$quizId = $_GET['id'] ?? null;
if (!$quizId) {
    redirect('dashboard.php');
}

$quiz = getQuizById($quizId);
$players = getQuizPlayers($quizId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="3">
    <title>Joueurs Connectés - Quizzeo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .live-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .live-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .live-title {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .player-count {
            font-size: 32px;
            margin: 20px 0;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .player-card {
            background: white;
            padding: 30px 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .player-avatar {
            font-size: 64px;
            margin-bottom: 15px;
        }
        
        .player-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .player-time {
            font-size: 14px;
            color: #999;
            margin-top: 5px;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 15px 30px;
            background: white;
            color: #667eea;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="live-page">
    <a href="dashboard.php" class="back-btn">← Retour</a>
    
    <div class="live-header">
        <h1 class="live-title">👥 Joueurs Connectés</h1>
        <div style="font-size: 24px;"><?php echo htmlspecialchars($quiz['titre']); ?></div>
        <div class="player-count">
            <?php echo count($players); ?> joueur<?php echo count($players) > 1 ? 's' : ''; ?>
        </div>
    </div>
    
    <div class="players-grid">
        <?php 
        $avatars = ['😀', '😎', '🤓', '😊', '🥳', '🤩', '😇', '🙂', '😃', '😄', '😁', '😆', '🤗', '🥰', '😍', '🤪', '😜', '🥸', '🤠', '👻'];
        foreach ($players as $index => $player): 
            $avatar = $avatars[$index % count($avatars)];
            $timeAgo = time() - strtotime($player['joined_at']);
            $timeText = $timeAgo < 60 ? 'À l\'instant' : 'Il y a ' . floor($timeAgo / 60) . ' min';
        ?>
            <div class="player-card">
                <div class="player-avatar"><?php echo $avatar; ?></div>
                <div class="player-name"><?php echo htmlspecialchars($player['player_name']); ?></div>
                <div class="player-time"><?php echo $timeText; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
