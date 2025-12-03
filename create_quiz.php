<?php
require_once '../includes/config.php';
require_once '../includes/user_functions.php';
require_once '../includes/quiz_functions.php';
 
if (!isLoggedIn() || !hasRole(ROLE_ENTREPRISE)) redirect('../login.php');
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    $quizData = [
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'owner_id' => $_SESSION['user_id'],
        'owner_role' => ROLE_ENTREPRISE
    ];
   
    $result = createQuiz($quizData);
    if ($result['success']) {
        $quizId = $result['quiz_id'];
        if (isset($_POST['questions'])) {
            foreach ($_POST['questions'] as $q) {
                $correctAnswers = [];
                if ($q['type'] === 'qcm_multiple') {
                    
                    $correctAnswers = isset($q['correct_answers']) ? array_map('intval', $q['correct_answers']) : [];
                } else {
                    
                    if (isset($q['correct_answer'])) {
                        $correctAnswers = [intval($q['correct_answer'])];
                    }
                }
               
                $questionData = [
                    'question' => $q['question'],
                    'type' => $q['type'],
                    'options' => isset($q['options']) ? $q['options'] : [],
                    'correct_answers' => $correctAnswers,
                    'points' => intval($q['points']),
                    'time_limit' => isset($q['time_limit']) ? intval($q['time_limit']) : 30,
                    'order' => 0
                ];
                addQuestionToQuiz($quizId, $questionData);
            }
        }
        if (isset($_POST['launch'])) {
            updateQuizStatus($quizId, QUIZ_STATUS_ACTIVE);
        }
        redirect('dashboard.php');
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Questionnaire - Quizzeo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .question-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .type-btn {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .type-btn.active {
            background: #667eea;
            color: white;
        }
        .question-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .checkbox-option:hover {
            border-color: #667eea;
        }
        .checkbox-option input[type="checkbox"],
        .checkbox-option input[type="radio"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
        .checkbox-option input[type="text"] {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 16px;
        }
        .checkbox-option input[type="text"]:focus {
            outline: none;
        }
        .correct-label {
            font-size: 12px;
            color: #27ae60;
            font-weight: bold;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Créer un questionnaire</h1>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" class="section">
            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="titre" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <h3>Questions</h3>
            <div class="question-type-selector">
                <button type="button" class="type-btn active" onclick="setQuestionType('qcm')">QCM Simple</button>
                <button type="button" class="type-btn" onclick="setQuestionType('qcm_multiple')">QCM Multiple</button>
            </div>
            <div id="questions-container"></div>
            <button type="button" class="btn btn-secondary" onclick="addQuestion()">+ Ajouter une question</button>
            <div style="margin-top: 30px;">
                <button type="submit" name="create_quiz" class="btn btn-primary">Sauvegarder</button>
                <button type="submit" name="create_quiz" value="1" onclick="document.querySelector('input[name=launch]').value='1'" class="btn btn-success">Créer et lancer</button>
                <input type="hidden" name="launch" value="0">
            </div>
        </form>
    </div>
    <script>
        let questionCount = 0;
        let currentType = 'qcm';
        let optionCounts = {};
       
        function setQuestionType(type) {
            currentType = type;
            document.querySelectorAll('.type-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
       
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const qIndex = questionCount++;
            const isMultiple = currentType === 'qcm_multiple';
            optionCounts[qIndex] = 4;
           
            const html = `
                <div class="question-item" id="q${qIndex}" data-type="${currentType}">
                    <div class="question-header">
                        <h4>Question ${qIndex + 1} ${isMultiple ? '(Réponses multiples)' : '(Une seule réponse)'}</h4>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${qIndex})">Supprimer</button>
                    </div>
                    <input type="hidden" name="questions[${qIndex}][type]" value="${currentType}">
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="questions[${qIndex}][question]" required>
                    </div>
                    <div class="form-group">
                        <label>Options de réponse (Cochez la ou les bonnes réponses)</label>
                        <div id="options${qIndex}">
                            ${generateOptions(qIndex, isMultiple, 4)}
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addOption(${qIndex})">+ Ajouter une option</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="removeOption(${qIndex})">- Supprimer une option</button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Points</label>
                            <input type="number" name="questions[${qIndex}][points]" value="10" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Temps (secondes)</label>
                            <input type="number" name="questions[${qIndex}][time_limit]" value="30" min="5" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
       
        function generateOptions(qIndex, isMultiple, count) {
            let html = '';
            for (let i = 0; i < count; i++) {
                html += `
                    <div class="checkbox-option" id="q${qIndex}_option_${i}">
                        ${isMultiple ?
                            `<input type="checkbox" name="questions[${qIndex}][correct_answers][]" value="${i}" id="q${qIndex}_correct_${i}">
                             <label for="q${qIndex}_correct_${i}" class="correct-label">✓ Correcte</label>` :
                            `<input type="radio" name="questions[${qIndex}][correct_answer]" value="${i}" id="q${qIndex}_correct_${i}" ${i === 0 ? 'required' : ''}>
                             <label for="q${qIndex}_correct_${i}" class="correct-label">✓ Correcte</label>`
                        }
                        <input type="text" name="questions[${qIndex}][options][]" placeholder="Option ${i+1}" required>
                    </div>
                `;
            }
            return html;
        }
       
        function addOption(qIndex) {
            const container = document.getElementById('options' + qIndex);
            const questionDiv = document.getElementById('q' + qIndex);
            const isMultiple = questionDiv.dataset.type === 'qcm_multiple';
            const currentCount = optionCounts[qIndex];
           
            if (currentCount >= 10) {
                alert('Maximum 10 options par question');
                return;
            }
           
            const newIndex = currentCount;
            const html = `
                <div class="checkbox-option" id="q${qIndex}_option_${newIndex}">
                    ${isMultiple ?
                        `<input type="checkbox" name="questions[${qIndex}][correct_answers][]" value="${newIndex}" id="q${qIndex}_correct_${newIndex}">
                         <label for="q${qIndex}_correct_${newIndex}" class="correct-label">✓ Correcte</label>` :
                        `<input type="radio" name="questions[${qIndex}][correct_answer]" value="${newIndex}" id="q${qIndex}_correct_${newIndex}">
                         <label for="q${qIndex}_correct_${newIndex}" class="correct-label">✓ Correcte</label>`
                    }
                    <input type="text" name="questions[${qIndex}][options][]" placeholder="Option ${newIndex+1}" required>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            optionCounts[qIndex]++;
        }
       
        function removeOption(qIndex) {
            const currentCount = optionCounts[qIndex];
           
            if (currentCount <= 2) {
                alert('Minimum 2 options par question');
                return;
            }
           
            const lastOption = document.getElementById(`q${qIndex}_option_${currentCount - 1}`);
            if (lastOption) {
                lastOption.remove();
                optionCounts[qIndex]--;
            }
        }
       
        function removeQuestion(qIndex) {
            document.getElementById('q' + qIndex).remove();
            delete optionCounts[qIndex];
        }
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>