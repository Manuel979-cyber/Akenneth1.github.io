<?php

require_once 'includes/config.php';
 
$pdo = getDbConnection();
 
echo "<h1>Débogage des Quiz</h1>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#667eea;color:white;}</style>";
 

$sql = "SELECT * FROM quiz ORDER BY id DESC LIMIT 5";
$stmt = $pdo->query($sql);
$quizzes = $stmt->fetchAll();
 
echo "<h2>Derniers Quiz créés</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Titre</th><th>PIN</th><th>Statut</th><th>Nb Questions</th></tr>";
 
foreach ($quizzes as $quiz) {
    
    $sqlCount = "SELECT COUNT(*) as count FROM questions WHERE quiz_id = :quiz_id";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute([':quiz_id' => $quiz['id']]);
    $count = $stmtCount->fetch()['count'];
   
    echo "<tr>";
    echo "<td>" . $quiz['id'] . "</td>";
    echo "<td>" . htmlspecialchars($quiz['titre']) . "</td>";
    echo "<td><strong>" . $quiz['pin_code'] . "</strong></td>";
    echo "<td>" . $quiz['statut'] . "</td>";
    echo "<td>" . $count . "</td>";
    echo "</tr>";
   
   
    $sqlQ = "SELECT * FROM questions WHERE quiz_id = :quiz_id ORDER BY order_num";
    $stmtQ = $pdo->prepare($sqlQ);
    $stmtQ->execute([':quiz_id' => $quiz['id']]);
    $questions = $stmtQ->fetchAll();
   
    if (!empty($questions)) {
        echo "<tr><td colspan='5'>";
        echo "<strong>Questions:</strong><br>";
        foreach ($questions as $q) {
            echo "- Q" . $q['id'] . ": " . htmlspecialchars($q['question']) . " (Type: " . $q['type'] . ", Réponses correctes: " . $q['correct_answers'] . ")<br>";
           
            
            $sqlO = "SELECT * FROM question_options WHERE question_id = :question_id ORDER BY option_index";
            $stmtO = $pdo->prepare($sqlO);
            $stmtO->execute([':question_id' => $q['id']]);
            $options = $stmtO->fetchAll();
           
            if (!empty($options)) {
                echo "&nbsp;&nbsp;&nbsp;Options:<br>";
                foreach ($options as $opt) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;[" . $opt['option_index'] . "] " . htmlspecialchars($opt['option_text']) . "<br>";
                }
            } else {
                echo "&nbsp;&nbsp;&nbsp;<span style='color:red;'> AUCUNE OPTION!</span><br>";
            }
        }
        echo "</td></tr>";
    }
}
 
echo "</table>";
 
echo "<h2>Test de connexion</h2>";
echo " Connexion à la base de données OK<br>";
echo " Base: " . DB_NAME . "<br>";
echo " Host: " . DB_HOST . "<br>";
?>
 
 