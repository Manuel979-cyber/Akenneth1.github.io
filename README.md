📂 Architecture du Projet
Pour faciliter la lecture du code, j'ai organisé mon arborescence de manière logique en séparant les vues, la logique métier et les ressources.

1. La Racine 

-index.html : Landing page vitrine pour les visiteurs.

-dashboard.php : Le tableau de bord principal après connexion.

-ecole/ & entreprise/ : Dossiers contenant les logiques spécifiques à ces deux secteurs (versions adaptées du dashboard).

2. Authentification & Sécurité


login.php / register.php : Gestion des formulaires et hashage des mots de passe.

check_admin.php : Script de vérification inclus en début de fichier pour protéger les pages réservées à l'administration.

logout.php : Destruction propre de la session.

3. La "Game Loop" (Cœur du Jeu)

Le déroulement d'un quiz suit une logique séquentielle précise que nous avons découper en plusieurs fichiers pour la maintenabilité :

-Entrée : join.php (via code PIN) ou join_link.php (via URL).

-Attente : lobby.php (Salle d'attente avant le lancement par l'admin).

Jeu :

-take_quiz.php : Initialise la session de jeu.

-play.php : Affiche la question courante.

-check_answer.php : Vérifie la réponse, calcule le score et met à jour la BDD.

-next_question.php : Gère la pagination et détecte la fin du quiz.

Fin : results.php (Affichage du score final et du classement).

4. Backend & Configuration

/includes : Contient les éléments réutilisables (connexion BDD db.php, header.php, footer.php) pour éviter la répétition de code (principe DRY).

/admin : Back-office pour la modération globale et la gestion des utilisateurs.

/assets : Stockage des feuilles de style CSS, scripts JS et images.

//Installation & Démarrage
Si vous souhaitez tester le projet localement :

-Cloner ou télécharger les fichiers dans votre dossier serveur  htdocs.

Base de données :

Créez une base de données (ex: quizzeo_db).

Importez les fichiers situés dans le dossier sql/ via PHPMyAdmin.

Configuration :

Vérifiez les identifiants de connexion dans includes/database.php  pour qu'ils correspondent à votre configuration locale.

Lancement :

Accédez à localhost/nom_du_dossier/index.html.