<?php

session_start();
require_once 'includes/config.php';
 

if (!isset($_GET['code'])) {
    redirect('index.html');
}
 
$linkCode = cleanInput($_GET['code']);
 

$pdo = getDbConnection();
$sql = "SELECT * FROM quiz WHERE link_code = :link_code AND status = 'lance' AND active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':link_code' => $linkCode]);
$quiz = $stmt->fetch();
 
if (!$quiz) {
    redirect('index.html?error=invalid_link');
}
 

if (isset($_POST['player_name'])) {
    $playerName = cleanInput($_POST['player_name']);
   
    if (empty($playerName)) {
        $error = "Veuillez entrer un pseudo";
    } else {
        
        try {
            $sql = "INSERT INTO quiz_players (quiz_id, player_name) VALUES (:quiz_id, :player_name)
                    ON DUPLICATE KEY UPDATE joined_at = NOW(), is_active = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':quiz_id' => $quiz['id'],
                ':player_name' => $playerName
            ]);
        } catch (PDOException $e) {
           
        }
       
        // Sauvegarder dans la session
        $_SESSION['quiz_id'] = $quiz['id'];
        $_SESSION['player_name'] = $playerName;
        $_SESSION['pin_code'] = $quiz['pin_code'];
       
        redirect('lobby.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejoindre - <?php echo htmlspecialchars($quiz['titre']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .join-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
       
        .join-container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
       
        .quiz-title {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
            font-weight: bold;
        }
       
        .quiz-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
       
        .join-form {
            margin-top: 30px;
        }
       
        .form-group {
            margin-bottom: 20px;
        }
       
        .form-group label {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }
       
        .form-group input {
            width: 100%;
            padding: 15px;
            font-size: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
        }
       
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
       
        .btn-join {
            width: 100%;
            padding: 18px;
            font-size: 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
       
        .btn-join:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
       
        .error {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="join-page">
    <div class="join-container">
        <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
        <?php if (!empty($quiz['description'])): ?>
            <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
        <?php endif; ?>
       
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
       
        <form method="POST" class="join-form">
            <div class="form-group">
                <label>Entrez votre pseudo</label>
                <input type="text" name="player_name" placeholder="Votre pseudo" required autofocus maxlength="50">
            </div>
           
            <button type="submit" class="btn-join">
                🎮 REJOINDRE LE QUIZ
            </button>
        </form>
    </div>
</body>
</html>
 
 