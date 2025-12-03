<?php
require_once '../includes/config.php';
require_once '../includes/user_functions.php';
require_once '../includes/quiz_functions.php';

if (!isLoggedIn() || !hasRole(ROLE_UTILISATEUR)) redirect('../login.php');

$mesReponses = getUserResponses($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Quizzeo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Mes Quiz Complétés</h1>
        <?php if (empty($mesReponses)): ?>
            <div class="alert alert-info">
                Vous n'avez pas encore répondu à de quiz. 
                Utilisez un lien fourni par une école ou entreprise.
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Date</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mesReponses as $response): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($response['quiz_titre']); ?></td>
                            <td><?php echo formatDate($response['submitted_at']); ?></td>
                            <td>
                                <?php if ($response['score'] !== null): ?>
                                    <strong><?php echo round($response['score'], 2); ?>%</strong>
                                    (<?php echo $response['earned_points']; ?>/<?php echo $response['total_points']; ?> points)
                                <?php else: ?>
                                    Réponse libre
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>
