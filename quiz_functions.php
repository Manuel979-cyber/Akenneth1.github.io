<?php


require_once 'config.php';

function generatePinCode() {
    $pdo = getDbConnection();
    do {
        $pin = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $sql = "SELECT COUNT(*) FROM quiz WHERE pin_code = :pin";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pin' => $pin]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);
    return $pin;
}




function createQuiz($quizData) {
    $pdo = getDbConnection();
    
    try {
        $pinCode = generatePinCode();
        $linkCode = generateQuizLinkCode();
        
        $sql = "INSERT INTO quiz (titre, description, owner_id, owner_role, pin_code, link_code) 
                VALUES (:titre, :description, :owner_id, :owner_role, :pin_code, :link_code)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titre' => cleanInput($quizData['titre']),
            ':description' => isset($quizData['description']) ? cleanInput($quizData['description']) : '',
            ':owner_id' => $quizData['owner_id'],
            ':owner_role' => $quizData['owner_role'],
            ':pin_code' => $pinCode,
            ':link_code' => $linkCode
        ]);
        
        return [
            'success' => true, 
            'message' => 'Quiz créé avec succès', 
            'quiz_id' => $pdo->lastInsertId(),
            'pin_code' => $pinCode,
            'link_code' => $linkCode
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la création du quiz'];
    }
}function addQuestionToQuiz($quizId, $questionData) {
    $pdo = getDbConnection();
    
    try {
        $pdo->beginTransaction();
        $correctAnswers = isset($questionData['correct_answers']) ? $questionData['correct_answers'] : [];
        if (!is_array($correctAnswers)) {
            $correctAnswers = [$correctAnswers];
        }
        
        $sql = "INSERT INTO questions (quiz_id, question, type, correct_answers, points, order_num, time_limit) 
                VALUES (:quiz_id, :question, :type, :correct_answers, :points, :order_num, :time_limit)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':quiz_id' => $quizId,
            ':question' => cleanInput($questionData['question']),
            ':type' => $questionData['type'],
            ':correct_answers' => json_encode($correctAnswers),
            ':points' => isset($questionData['points']) ? intval($questionData['points']) : 1,
            ':order_num' => isset($questionData['order']) ? $questionData['order'] : 0,
            ':time_limit' => isset($questionData['time_limit']) ? intval($questionData['time_limit']) : 30
        ]);
        
        $questionId = $pdo->lastInsertId();
        if (isset($questionData['options'])) {
            $optionSql = "INSERT INTO question_options (question_id, option_text, option_index) 
                         VALUES (:question_id, :option_text, :option_index)";
            $optionStmt = $pdo->prepare($optionSql);
            
            foreach ($questionData['options'] as $index => $option) {
                $optionStmt->execute([
                    ':question_id' => $questionId,
                    ':option_text' => cleanInput($option),
                    ':option_index' => $index
                ]);
            }
        }
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Question ajoutée avec succès'];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
    }
}


function getQuizById($quizId) {
    $pdo = getDbConnection();
    
    $sql = "SELECT * FROM quiz WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $quizId]);
    $quiz = $stmt->fetch();
    
    if (!$quiz) return null;

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
    
    $quiz['questions'] = $questions;
    return $quiz;
}


function getQuizByPin($pin) {
    $pdo = getDbConnection();
    $sql = "SELECT * FROM quiz WHERE pin_code = :pin";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pin' => $pin]);
    $quiz = $stmt->fetch();
    
    if (!$quiz) return null;
    return getQuizById($quiz['id']);
}


function getQuizByOwner($ownerId) {
    $pdo = getDbConnection();
    $sql = "SELECT * FROM quiz WHERE owner_id = :owner_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner_id' => $ownerId]);
    return $stmt->fetchAll();
}


function updateQuizStatus($quizId, $status) {
    $pdo = getDbConnection();
    try {
        $sql = "UPDATE quiz SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':status' => $status, ':id' => $quizId]);
        return ['success' => true, 'message' => 'Statut mis à jour'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur'];
    }
}


function getQuizPlayers($quizId) {
    $pdo = getDbConnection();
    $sql = "SELECT * FROM quiz_players WHERE quiz_id = :quiz_id AND is_active = 1 ORDER BY joined_at";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':quiz_id' => $quizId]);
    return $stmt->fetchAll();
}


function countQuizPlayers($quizId) {
    $pdo = getDbConnection();
    $sql = "SELECT COUNT(*) FROM quiz_players WHERE quiz_id = :quiz_id AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':quiz_id' => $quizId]);
    return $stmt->fetchColumn();
}


function getQuizLeaderboard($quizId) {
    $pdo = getDbConnection();
    $sql = "SELECT player_name, earned_points, score, time_taken, submitted_at 
            FROM responses 
            WHERE quiz_id = :quiz_id 
            ORDER BY earned_points DESC, time_taken ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':quiz_id' => $quizId]);
    return $stmt->fetchAll();
}


function countQuizResponses($quizId) {
    $pdo = getDbConnection();
    $sql = "SELECT COUNT(*) FROM responses WHERE quiz_id = :quiz_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':quiz_id' => $quizId]);
    return $stmt->fetchColumn();
}


function getAllQuiz() {
    $pdo = getDbConnection();
    $sql = "SELECT * FROM quiz ORDER BY created_at DESC";
    return $pdo->query($sql)->fetchAll();
}


function toggleQuizStatus($quizId, $active) {
    $pdo = getDbConnection();
    try {
        $sql = "UPDATE quiz SET active = :active WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':active' => $active ? 1 : 0, ':id' => $quizId]);
        return ['success' => true, 'message' => 'Quiz ' . ($active ? 'activé' : 'désactivé')];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur'];
    }
}


function countQuizByStatus() {
    $pdo = getDbConnection();
    $sql = "SELECT status, COUNT(*) as count FROM quiz GROUP BY status";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    $counts = [
        'en_cours_ecriture' => 0,
        'lance' => 0,
        'termine' => 0
    ];
    
    foreach ($results as $row) {
        $counts[$row['status']] = $row['count'];
    }
    
    return $counts;
}
?>
