<?php

session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['quiz_id']) || !isset($_SESSION['player_name'])) {
    redirect('join.php');
}

$pdo = getDbConnection();
$quizId = $_SESSION['quiz_id'];
$playerName = $_SESSION['player_name'];
$score = $_SESSION['score'];
$answers = $_SESSION['answers'];


$sql = "SELECT SUM(points) as total_points FROM questions WHERE quiz_id = :quiz_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':quiz_id' => $quizId]);
$result = $stmt->fetch();
$totalPoints = $result['total_points'];

$percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;


$timeTaken = time() - $_SESSION['start_time'];
$sql = "INSERT INTO responses (quiz_id, player_name, score, earned_points, total_points, time_taken) 
        VALUES (:quiz_id, :player_name, :score, :earned_points, :total_points, :time_taken)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':quiz_id' => $quizId,
    ':player_name' => $playerName,
    ':score' => $percentage,
    ':earned_points' => $score,
    ':total_points' => $totalPoints,
    ':time_taken' => $timeTaken
]);


$sql = "SELECT player_name, earned_points, time_taken, submitted_at 
        FROM responses 
        WHERE quiz_id = :quiz_id 
        ORDER BY earned_points DESC, time_taken ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':quiz_id' => $quizId]);
$leaderboard = $stmt->fetchAll();

// Trouver la position du joueur
$position = 0;
foreach ($leaderboard as $index => $entry) {
    if ($entry['player_name'] === $playerName && 
        $entry['earned_points'] == $score) {
        $position = $index + 1;
        break;
    }
}

// Nettoyer la session
unset($_SESSION['current_question']);
unset($_SESSION['answers']);
unset($_SESSION['start_time']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .results-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .results-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .results-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .results-title {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .score-card {
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .score-icon {
            font-size: 100px;
            margin-bottom: 20px;
        }
        
        .score-percentage {
            font-size: 72px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .score-details {
            font-size: 24px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .position-badge {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 50px;
            font-size: 28px;
            font-weight: bold;
            margin-top: 20px;
        }
        
        .leaderboard-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .leaderboard-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }
        
        .leaderboard-item:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .leaderboard-item.current-player {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
        }
        
        .leaderboard-rank {
            font-size: 32px;
            font-weight: bold;
            width: 60px;
            text-align: center;
        }
        
        .leaderboard-rank.gold { color: #FFD700; }
        .leaderboard-rank.silver { color: #C0C0C0; }
        .leaderboard-rank.bronze { color: #CD7F32; }
        
        .leaderboard-name {
            flex: 1;
            font-size: 20px;
        }
        
        .leaderboard-score {
            font-size: 24px;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .btn-action {
            padding: 15px 40px;
            font-size: 18px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="results-page">
    <div class="results-container">
        <div class="results-header">
            <h1 class="results-title"> Quiz Terminé !</h1>
        </div>
        
        <div class="score-card">
            <div class="score-icon">
                <?php 
                if ($percentage >= 80) echo '';
                elseif ($percentage >= 60) echo '';
                elseif ($percentage >= 40) echo '';
                else echo '';
                ?>
            </div>
            
            <div class="score-percentage">
                <?php echo round($percentage); ?>%
            </div>
            
            <div class="score-details">
                <?php echo $score; ?> / <?php echo $totalPoints; ?> points
            </div>
            
            <div class="score-details">
                ⏱ Temps: <?php echo gmdate("i:s", $timeTaken); ?>
            </div>
            
            <div class="position-badge">
                 <?php echo $position; ?><?php 
                if ($position == 1) echo 'er';
                else echo 'ème';
                ?> place
            </div>
        </div>
        
        <div class="leaderboard-card">
            <h2 class="leaderboard-title"> Classement</h2>
            
            <?php foreach ($leaderboard as $index => $entry): ?>
                <div class="leaderboard-item <?php echo $entry['player_name'] === $playerName && $entry['earned_points'] == $score ? 'current-player' : ''; ?>">
                    <div class="leaderboard-rank <?php 
                        if ($index == 0) echo 'gold';
                        elseif ($index == 1) echo 'silver';
                        elseif ($index == 2) echo 'bronze';
                    ?>">
                        <?php 
                        if ($index == 0) echo '1';
                        elseif ($index == 1) echo '2';
                        elseif ($index == 2) echo '3';
                        else echo '#' . ($index + 1);
                        ?>
                    </div>
                    <div class="leaderboard-name">
                        <?php echo htmlspecialchars($entry['player_name']); ?>
                    </div>
                    <div class="leaderboard-score">
                        <?php echo $entry['earned_points']; ?> pts
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="join.php" class="btn-action btn-primary">
                 Rejouer
            </a>
            <a href="index.html" class="btn-action btn-secondary">
                 Accueil
            </a>
        </div>
    </div>
</body>
</html>
