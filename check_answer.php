<?php

session_start();
require_once 'includes/config.php';
 
if (!isset($_SESSION['quiz_id']) || !isset($_SESSION['player_name'])) {
    redirect('join.php');
}
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('play.php');
}
 
$pdo = getDbConnection();
$questionId = $_POST['question_id'];
 

$sql = "SELECT * FROM questions WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $questionId]);
$question = $stmt->fetch();
 

$sql = "SELECT * FROM question_options WHERE question_id = :question_id ORDER BY option_index";
$stmt = $pdo->prepare($sql);
$stmt->execute([':question_id' => $questionId]);
$options = $stmt->fetchAll();
 

if (empty($options)) {
    $options = [];
}
 

$userAnswers = [];
if ($question['type'] === 'qcm_multiple') {
    $userAnswers = isset($_POST['answers']) ? $_POST['answers'] : [];
} else {
    $userAnswers = [isset($_POST['answer']) ? $_POST['answer'] : null];
}
 

$correctAnswers = json_decode($question['correct_answers'], true);
if (!is_array($correctAnswers)) {
    $correctAnswers = [$correctAnswers];
}

$isCorrect = false;
if ($question['type'] === 'qcm_multiple') {
    sort($userAnswers);
    sort($correctAnswers);
    $isCorrect = ($userAnswers == $correctAnswers);
} else {
    $isCorrect = in_array($userAnswers[0], $correctAnswers);
}

$_SESSION['answers'][] = [
    'question_id' => $questionId,
    'user_answers' => $userAnswers,
    'is_correct' => $isCorrect,
    'points' => $isCorrect ? $question['points'] : 0
];
 
if ($isCorrect) {
    $_SESSION['score'] += $question['points'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .correction-page {
            min-height: 100vh;
            background: <?php echo $isCorrect ? 'linear-gradient(135deg, #27ae60, #2ecc71)' : 'linear-gradient(135deg, #e74c3c, #c0392b)'; ?>;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
       
        .correction-container {
            max-width: 800px;
            width: 100%;
            text-align: center;
            color: white;
        }
       
        .result-icon {
            font-size: 120px;
            margin-bottom: 30px;
            animation: bounceIn 0.6s;
        }
       
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
       
        .result-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }
       
        .result-message {
            font-size: 24px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
       
        .correct-answer-box {
            background: rgba(255,255,255,0.2);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
        }
       
        .correct-answer-title {
            font-size: 20px;
            margin-bottom: 15px;
        }
       
        .correct-answer-text {
            font-size: 28px;
            font-weight: bold;
        }
       
        .points-earned {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 40px;
        }
       
        .next-btn {
            display: inline-block;
            padding: 20px 60px;
            font-size: 24px;
            background: white;
            color: <?php echo $isCorrect ? '#27ae60' : '#e74c3c'; ?>;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
       
        .next-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="correction-page">
    <div class="correction-container">
        <div class="result-icon">
            <?php echo $isCorrect ? '✅' : '❌'; ?>
        </div>
       
        <h1 class="result-title">
            <?php echo $isCorrect ? 'CORRECT !' : 'INCORRECT'; ?>
        </h1>
       
        <p class="result-message">
            <?php
            if ($isCorrect) {
                echo 'Bravo ! Excellente réponse !';
            } else {
                echo 'Dommage ! Ce n\'était pas la bonne réponse.';
            }
            ?>
        </p>
       
        <?php if (!$isCorrect): ?>
        <div class="correct-answer-box">
            <div class="correct-answer-title">La bonne réponse était :</div>
            <div class="correct-answer-text">
                <?php
                if (!empty($options)) {
                    foreach ($options as $option) {
                        if (in_array($option['option_index'], $correctAnswers)) {
                            echo  htmlspecialchars($option['option_text']) . '<br>';
                        }
                    }
                } else {
                   
                    echo 'Réponse(s) correcte(s) : ' . implode(', ', array_map(function($i) { return 'Option ' . ($i + 1); }, $correctAnswers));
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
       
        <div class="points-earned">
            <?php echo $isCorrect ? '+' . $question['points'] : '0'; ?> points
        </div>
       
        <form method="POST" action="next_question.php">
            <button type="submit" class="next-btn">
                 QUESTION SUIVANTE
            </button>
        </form>
    </div>
   
    <script>
        // Auto-redirect après 3 secondes
        setTimeout(function() {
            document.querySelector('form').submit();
        }, 3000);
    </script>
</body>
</html>
 