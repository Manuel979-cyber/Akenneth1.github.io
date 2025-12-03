<?php

require_once 'includes/config.php';
require_once 'includes/quiz_functions.php';
 
$pdo = getDbConnection();
 
echo "<h1>Création d'un Quiz de Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
 
try {
    
    $quizData = [
        'titre' => 'Quiz de Test',
        'description' => 'Quiz créé automatiquement pour tester',
        'owner_id' => 1, 
        'owner_role' => 'ecole'
    ];
   
    $result = createQuiz($quizData);
   
    if ($result['success']) {
        $quizId = $result['quiz_id'];
        echo "<p class='success'> Quiz créé avec ID: $quizId</p>";
       
        // Récupérer le PIN
        $sql = "SELECT pin_code FROM quiz WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $quizId]);
        $pin = $stmt->fetch()['pin_code'];
        echo "<p class='success'> Code PIN: <strong>$pin</strong></p>";
       
        // Ajouter une question
        $questionData = [
            'question' => 'Quelle est la capitale de la France?',
            'type' => 'qcm',
            'options' => ['Londres', 'Paris', 'Berlin', 'Madrid'],
            'correct_answers' => [1], // Paris est l'index 1
            'points' => 10,
            'time_limit' => 30,
            'order' => 0
        ];
       
        $qResult = addQuestionToQuiz($quizId, $questionData);
       
        if ($qResult['success']) {
            echo "<p class='success'> Question ajoutée</p>";
           
            
            $sql = "SELECT q.id, q.question, qo.option_text, qo.option_index
                    FROM questions q
                    LEFT JOIN question_options qo ON q.id = qo.question_id
                    WHERE q.quiz_id = :quiz_id
                    ORDER BY qo.option_index";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':quiz_id' => $quizId]);
            $rows = $stmt->fetchAll();
           
            echo "<h3>Vérification:</h3>";
            echo "<ul>";
            foreach ($rows as $row) {
                echo "<li>[" . $row['option_index'] . "] " . htmlspecialchars($row['option_text']) . "</li>";
            }
            echo "</ul>";
           
            updateQuizStatus($quizId, 'lance');
            echo "<p class='success'> Quiz lancé</p>";
           
            echo "<hr>";
            echo "<h2> Pour jouer:</h2>";
            echo "<p>1. Va sur: <a href='join.php'>join.php</a></p>";
            echo "<p>2. Entre le code PIN: <strong>$pin</strong></p>";
            echo "<p>3. Entre un pseudo</p>";
            echo "<p>4. Joue!</p>";
           
        } else {
            echo "<p class='error'> Erreur ajout question: " . $qResult['message'] . "</p>";
        }
       
    } else {
        echo "<p class='error'> Erreur création quiz: " . $result['message'] . "</p>";
    }
   
} catch (Exception $e) {
    echo "<p class='error'> Exception: " . $e->getMessage() . "</p>";
}
?>
 