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
$leaderboard = getQuizLeaderboard($quizId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <title>Classement - Quizzeo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .leaderboard-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 20px;
        }
        
        .leaderboard-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .leaderboard-title {
            font-size: 56px;
            margin-bottom: 10px;
        }
        
        .leaderboard-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .podium {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .podium-place {
            background: white;
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.6s;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .podium-place.first {
            order: 2;
            width: 250px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
        }
        
        .podium-place.second {
            order: 1;
            width: 200px;
            background: linear-gradient(135deg, #C0C0C0, #808080);
            color: white;
        }
        
        .podium-place.third {
            order: 3;
            width: 200px;
            background: linear-gradient(135deg, #CD7F32, #8B4513);
            color: white;
        }
        
        .podium-medal {
            font-size: 72px;
            margin-bottom: 10px;
        }
        
        .podium-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .podium-score {
            font-size: 36px;
            font-weight: bold;
        }
        
        .other-players {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .player-row {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }
        
        .player-row:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .player-rank {
            font-size: 28px;
            font-weight: bold;
            width: 60px;
            text-align: center;
            color: #667eea;
        }
        
        .player-name-col {
            flex: 1;
            font-size: 20px;
            font-weight: 500;
        }
        
        .player-score-col {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 15px 30px;
            background: white;
            color: #f5576c;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="leaderboard-page">
    <a href="dashboard.php" class="back-btn">← Retour</a>
    
    <div class="leaderboard-header">
        <h1 class="leaderboard-title">🏆 CLASSEMENT</h1>
        <div style="font-size: 24px;"><?php echo htmlspecialchars($quiz['titre']); ?></div>
    </div>
    
    <div class="leaderboard-container">
        <?php if (count($leaderboard) >= 3): ?>
        <div class="podium">
            
            <?php if (isset($leaderboard[1])): ?>
            <div class="podium-place second">
                <div class="podium-medal">🥈</div>
                <div class="podium-name"><?php echo htmlspecialchars($leaderboard[1]['player_name']); ?></div>
                <div class="podium-score"><?php echo $leaderboard[1]['earned_points']; ?> pts</div>
            </div>
            <?php endif; ?>
            
            
            <?php if (isset($leaderboard[0])): ?>
            <div class="podium-place first">
                <div class="podium-medal">🥇</div>
                <div class="podium-name"><?php echo htmlspecialchars($leaderboard[0]['player_name']); ?></div>
                <div class="podium-score"><?php echo $leaderboard[0]['earned_points']; ?> pts</div>
            </div>
            <?php endif; ?>
            
            
            <?php if (isset($leaderboard[2])): ?>
            <div class="podium-place third">
                <div class="podium-medal">🥉</div>
                <div class="podium-name"><?php echo htmlspecialchars($leaderboard[2]['player_name']); ?></div>
                <div class="podium-score"><?php echo $leaderboard[2]['earned_points']; ?> pts</div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (count($leaderboard) > 3): ?>
        <div class="other-players">
            <h3 style="margin-bottom: 20px; text-align: center;">Autres joueurs</h3>
            <?php for ($i = 3; $i < count($leaderboard); $i++): ?>
                <div class="player-row">
                    <div class="player-rank">#<?php echo $i + 1; ?></div>
                    <div class="player-name-col"><?php echo htmlspecialchars($leaderboard[$i]['player_name']); ?></div>
                    <div class="player-score-col"><?php echo $leaderboard[$i]['earned_points']; ?> pts</div>
                </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($leaderboard)): ?>
        <div class="other-players">
            <p style="text-align: center; font-size: 20px; color: #999;">
                Aucun joueur n'a encore terminé le quiz
            </p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
