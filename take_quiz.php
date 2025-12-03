<?php
require_once 'includes/config.php';
require_once 'includes/user_functions.php';
require_once 'includes/quiz_functions.php';

if (!isLoggedIn()) redirect('login.php');

$error = '';
$quiz = null;

if (isset($_GET['code'])) {
    $quiz = getQuizByLinkCode($_GET['code']);
    if (!$quiz) {
        $error = 'Quiz non trouvé';
    } elseif (!$quiz['active']) {
        $error = 'Ce quiz a été désactivé';
    } elseif ($quiz['status'] !== QUIZ_STATUS_ACTIVE) {
        $error = 'Ce quiz n\'est pas disponible';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $responseData = [
        'quiz_id' => $_POST['quiz_id'],
        'user_id' => $_SESSION['user_id'],
        'answers' => $_POST['answers']
    ];
    
    $result = submitQuizResponse($responseData);
    if ($result['success']) {
        $message = 'Réponses enregistrées avec succès!';
        if ($result['score'] !== null) {
            $message .= ' Votre score: ' . round($result['score'], 2) . '%';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre au Quiz - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
                <a href="utilisateur/dashboard.php" class="btn btn-primary">Retour au dashboard</a>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php elseif ($quiz): ?>
            <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
            <form method="POST" class="section">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                <?php foreach ($quiz['questions'] as $index => $question): ?>
                    <div class="question-item">
                        <h3>Question <?php echo $index + 1; ?></h3>
                        <p><?php echo htmlspecialchars($question['question']); ?></p>
                        <?php if ($question['type'] === 'qcm'): ?>
                            <?php foreach ($question['options'] as $optIndex => $option): ?>
                                <div class="option-item">
                                    <label>
                                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $optIndex; ?>" required>
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <textarea name="answers[<?php echo $question['id']; ?>]" rows="4" required placeholder="Votre réponse..."></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_quiz" class="btn btn-primary btn-block">Soumettre mes réponses</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info">Veuillez utiliser un lien valide pour accéder à un quiz.</div>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
