

document.addEventListener('DOMContentLoaded', function() {
    
    
    
    // Validation du formulaire d'inscription
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                showAlert('Les mots de passe ne correspondent pas', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Le mot de passe doit contenir au moins 6 caractères', 'error');
                return false;
            }
        });
    }
    // Ajouter une question
    const addQuestionBtn = document.getElementById('add-question-btn');
    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            const questionType = document.getElementById('question-type').value;
            addQuestionForm(questionType);
        });
    }
    
    // Supprimer une question
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-question-btn')) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
                e.target.closest('.question-item').remove();
            }
        }
    });
    
    // Ajouter une option à une question QCM
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-option-btn')) {
            const questionItem = e.target.closest('.question-item');
            const optionsContainer = questionItem.querySelector('.options-container');
            addOption(optionsContainer);
        }
    });
    
    // Supprimer une option
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-option-btn')) {
            e.target.closest('.option-input').remove();
        }
    });

    // Auto-fermeture des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
    // GESTION DU COPIER-COLLER DU LIEN DE QUIZ
    const copyLinkBtns = document.querySelectorAll('.copy-link-btn');
    copyLinkBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const link = this.getAttribute('data-link');
            copyToClipboard(link);
            showAlert('Lien copié dans le presse-papier !', 'success');
        });
    });
    
  
    // CONFIRMATION DE SUPPRESSION
   const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
                return false;
            }
        });
    });
    // ANIMATIONS AU SCROLL
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    const animatedElements = document.querySelectorAll('.card, .quiz-card, .section');
    animatedElements.forEach(function(el) {
        observer.observe(el);
    });
});
// FONCTIONS UTILITAIRES
/**
 * Affiche une alerte
 * @param {string} message
 * @param {string} type
 */

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(function() {
            alertDiv.style.opacity = '0';
            setTimeout(function() {
                alertDiv.remove();
            }, 300);
        }, 5000);
    }
}
/**
 * Copie du texte dans le presse-papier
 * @param {string} text Le texte à copier
 */
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
}

/**
 * Ajoute un formulaire de question
 * @param {string} type 
 */
function addQuestionForm(type) {
    const questionsContainer = document.getElementById('questions-container');
    const questionIndex = questionsContainer.children.length;
    
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-item';
    questionDiv.innerHTML = `
        <div class="question-header">
            <h4>Question ${questionIndex + 1}</h4>
            <button type="button" class="btn btn-sm btn-danger delete-question-btn">Supprimer</button>
        </div>
        <div class="form-group">
            <label>Question</label>
            <input type="text" name="questions[${questionIndex}][question]" required class="form-control">
        </div>
        <input type="hidden" name="questions[${questionIndex}][type]" value="${type}">
        ${type === 'qcm' ? `
            <div class="form-group">
                <label>Options de réponse</label>
                <div class="options-container">
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][]" placeholder="Option 1" required>
                    </div>
                    <div class="option-input">
                        <input type="text" name="questions[${questionIndex}][options][]" placeholder="Option 2" required>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary add-option-btn">Ajouter une option</button>
            </div>
            <div class="form-group">
                <label>Bonne réponse (numéro de l'option, commence à 0)</label>
                <input type="number" name="questions[${questionIndex}][correct_answer]" min="0" required>
            </div>
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="questions[${questionIndex}][points]" value="1" min="1" required>
            </div>
        ` : ''}
    `;
    
    questionsContainer.appendChild(questionDiv);
}

/**
 * Ajoute une option à une question QCM
 * @param {HTMLElement} container 
 */
function addOption(container) {
    const optionCount = container.children.length;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-input';
    optionDiv.innerHTML = `
        <input type="text" placeholder="Option ${optionCount + 1}" required>
        <button type="button" class="btn btn-sm btn-danger delete-option-btn">X</button>
    `;
    container.appendChild(optionDiv);
}

/**
 * Formate une date
 * @param {string} dateString 
 * @returns {string} 
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('fr-FR', options);
}

/**
 * Calcule le pourcentage
 * @param {number} value 
 * @param {number} total 
 * @returns {string} 
 */
function calculatePercentage(value, total) {
    if (total === 0) return '0%';
    return ((value / total) * 100).toFixed(2) + '%';
}
