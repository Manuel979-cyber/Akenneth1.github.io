<?php

session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['quiz_id']) || !isset($_SESSION['player_name'])) {
    redirect('join.php');
}

$pdo = getDbConnection();
$quizId = $_SESSION['quiz_id'];
$playerName = $_SESSION['player_name'];


$sql = "SELECT * FROM quiz WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $quizId]);
$quiz = $stmt->fetch();


$sql = "SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY order_num";
$stmt = $pdo->prepare($sql);
$stmt->execute([':quiz_id' => $quizId]);
$questions = $stmt->fetchAll();


foreach ($questions as &$question) {
    $sql = "SELECT * FROM question_options WHERE question_id = :question_id ORDER BY option_index";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':question_id' => $question['id']]);
    $question['options'] = $stmt->fetchAll();
    
    
    if ($question['correct_answers']) {
        $question['correct_answers_array'] = json_decode($question['correct_answers'], true);
    }
}



if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
    $_SESSION['score'] = 0;
}


if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}

if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
}

$currentQuestionIndex = $_SESSION['current_question'];
$totalQuestions = count($questions);


if ($currentQuestionIndex >= $totalQuestions) {
    redirect('results.php');
}

$currentQuestion = $questions[$currentQuestionIndex];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question <?php echo $currentQuestionIndex + 1; ?> - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .play-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .play-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .play-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .question-counter {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .player-info {
            font-size: 18px;
            color: #666;
        }
        
        .question-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .question-text {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .option-btn {
            padding: 30px;
            font-size: 20px;
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
            position: relative;
        }
        
        .option-btn:hover {
            transform: scale(1.05);
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .option-btn input[type="checkbox"] {
            width: 24px;
            height: 24px;
            margin-right: 15px;
        }
        
        .option-btn.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .option-btn.correct {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
            animation: correctAnswer 0.5s;
        }
        
        .option-btn.incorrect {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
            animation: incorrectAnswer 0.5s;
        }
        
        @keyframes correctAnswer {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes incorrectAnswer {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .submit-btn {
            width: 100%;
            padding: 20px;
            font-size: 24px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.4);
        }
        
        .submit-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .timer {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .progress-bar {
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
        }
    </style>
</head>
<body class="play-page">
    <div class="play-container">
        <div class="play-header">
            <div class="question-counter">
                Question <?php echo $currentQuestionIndex + 1; ?> / <?php echo $totalQuestions; ?>
            </div>
            <div class="player-info">
                 <?php echo htmlspecialchars($playerName); ?>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo ($currentQuestionIndex / $totalQuestions) * 100; ?>%"></div>
        </div>
        
        <div class="question-card">
            <div class="question-text">
                <?php echo htmlspecialchars($currentQuestion['question']); ?>
            </div>
            
            <form method="POST" action="check_answer.php" id="answerForm">
                <input type="hidden" name="question_id" value="<?php echo $currentQuestion['id']; ?>">
                
                <div class="options-grid">
                    <?php foreach ($currentQuestion['options'] as $option): ?>
                        <label class="option-btn">
                            <?php if ($currentQuestion['type'] === 'qcm_multiple'): ?>
                                <input type="checkbox" name="answers[]" value="<?php echo $option['option_index']; ?>">
                            <?php else: ?>
                                <input type="radio" name="answer" value="<?php echo $option['option_index']; ?>" required>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($option['option_text']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="submit-btn" style="margin-top: 30px;">
                    ✓ VALIDER
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Sélection visuelle des options
        document.querySelectorAll('.option-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.querySelector('input');
                if (input.type === 'radio') {
                    document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                } else {
                    this.classList.toggle('selected');
                }
            });
        });
    </script>
</body>
</html>
