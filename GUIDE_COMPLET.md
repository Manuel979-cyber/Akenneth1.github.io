# 🎯 GUIDE COMPLET - QUIZZEO STYLE KAHOOT

## 🚀 INSTALLATION

### 1. Importer la base de données
```
1. Ouvrez phpMyAdmin: http://localhost/phpmyadmin
2. Cliquez sur "Importer"
3. Sélectionnez: sql/database.sql
4. Cliquez sur "Exécuter"
```

### 2. Accéder au site
```
URL: http://localhost/quizzeo-avec-bdd/
```

### 3. Comptes de test
- **Admin:** admin@quizzeo.com / admin123
- **École:** ecole@test.com / admin123
- **Entreprise:** entreprise@test.com / admin123

---

## 🎮 COMMENT ÇA MARCHE (Style Kahoot)

### POUR LE PROFESSEUR/FORMATEUR:

#### 1. Créer un quiz
```
1. Connectez-vous avec un compte École
2. Cliquez sur "Créer un nouveau quiz"
3. Donnez un titre: "Test de Mathématiques"
4. Ajoutez des questions:
   - Cliquez sur "QCM Simple" ou "QCM Multiple"
   - Cliquez sur "+ Ajouter une question"
   - Tapez votre question
   - Ajoutez 4 options
   - Cochez la/les bonne(s) réponse(s)
   - Définissez les points (ex: 10)
   - Définissez le temps (ex: 30 secondes)
5. Cliquez sur "Créer et lancer"
```

#### 2. Partager le quiz
Vous obtenez **3 moyens** de partager:

**A. CODE PIN (6 chiffres)**
```
Exemple: 123456
Les joueurs vont sur le site et entrent ce code
```

**B. LIEN DIRECT**
```
Exemple: http://localhost/quizzeo-avec-bdd/join.php?pin=123456
Envoyez ce lien par email/WhatsApp/Teams
```

**C. QR CODE**
```
Affichez le QR Code au tableau
Les élèves le scannent avec leur téléphone
```

#### 3. Voir les joueurs connectés
```
1. Sur votre dashboard, cliquez sur "👥 Joueurs"
2. Vous voyez tous les joueurs qui ont rejoint
3. La page se rafraîchit automatiquement toutes les 3 secondes
```

#### 4. Voir le classement en direct
```
1. Cliquez sur "🏆 Classement"
2. Vous voyez le podium (1er, 2ème, 3ème)
3. Et tous les autres joueurs
4. La page se rafraîchit automatiquement toutes les 5 secondes
```

---

### POUR LES JOUEURS (Élèves/Participants):

#### 1. Rejoindre le quiz
```
Méthode 1: CODE PIN
1. Allez sur: http://localhost/quizzeo-avec-bdd/
2. Entrez le CODE PIN (6 chiffres)
3. Entrez votre NOM
4. Cliquez sur "REJOINDRE"

Méthode 2: LIEN DIRECT
1. Cliquez sur le lien reçu
2. Entrez votre NOM
3. Cliquez sur "REJOINDRE"

Méthode 3: QR CODE
1. Scannez le QR Code
2. Entrez votre NOM
3. Cliquez sur "REJOINDRE"
```

#### 2. Salle d'attente (Lobby)
```
- Vous voyez votre nom avec un avatar
- Vous voyez tous les autres joueurs
- Attendez que le professeur lance le quiz
- Cliquez sur "COMMENCER" quand c'est prêt
```

#### 3. Jouer au quiz
```
Pour chaque question:
1. Lisez la question
2. Cliquez sur votre/vos réponse(s)
3. Cliquez sur "VALIDER"
4. Vous voyez immédiatement si c'est bon:
   - VERT ✅ = Correct
   - ROUGE ❌ = Incorrect
5. Vous voyez la bonne réponse si vous vous êtes trompé
6. Passez automatiquement à la question suivante
```

#### 4. Voir vos résultats
```
À la fin du quiz:
- Vous voyez votre SCORE (ex: 80%)
- Vous voyez vos POINTS (ex: 80/100)
- Vous voyez votre TEMPS
- Vous voyez votre POSITION (ex: 3ème place)
- Vous voyez le CLASSEMENT COMPLET avec:
  🥇 1ère place
  🥈 2ème place
  🥉 3ème place
  Et tous les autres
```

---

## 🎨 TYPES DE QUESTIONS

### QCM Simple (1 seule bonne réponse)
```
Question: Combien font 2 + 2 ?
○ 3
● 4  ← Bonne réponse
○ 5
○ 6
```

### QCM Multiple (Plusieurs bonnes réponses)
```
Question: Quels sont des nombres pairs ?
☑ 2  ← Bonne réponse
☐ 3
☑ 4  ← Bonne réponse
☐ 5
```

---

## 📊 FONCTIONNALITÉS

### ✅ Ce qui fonctionne:
- Création de quiz avec QCM simple et multiple
- Code PIN à 6 chiffres
- QR Code automatique
- Lien direct
- Salle d'attente avec liste des joueurs
- Jeu avec correction immédiate (vert/rouge)
- Classement en temps réel
- Podium avec médailles
- Temps limite par question
- Points personnalisables
- Dashboard avec statistiques

### 🎯 Différences avec Kahoot:
- ✅ Pas besoin d'application mobile
- ✅ Fonctionne sur navigateur web
- ✅ Hébergé sur votre serveur local
- ✅ Gratuit et sans limite
- ✅ Données privées (pas dans le cloud)

---

## 🔧 PERSONNALISATION

### Changer les couleurs
Éditez `assets/css/style.css`:
```css
:root {
    --primary-color: #667eea;  /* Votre couleur */
}
```

### Ajouter votre logo
Placez votre logo dans:
```
assets/images/logo.png
```

---

## 🐛 DÉPANNAGE

### Problème: "Quiz non trouvé"
- Vérifiez que le quiz est bien en statut "lancé"
- Vérifiez que le code PIN est correct

### Problème: "Ce quiz n'est pas actif"
- L'admin a peut-être désactivé le quiz
- Contactez le professeur

### Problème: Page blanche
- Vérifiez que MySQL est démarré
- Vérifiez que la base de données est importée

---

## 📱 UTILISATION SUR TÉLÉPHONE

Les joueurs peuvent utiliser leur téléphone:
1. Connectez-vous au même réseau WiFi
2. Remplacez `localhost` par l'IP de votre PC
3. Exemple: `http://192.168.1.10/quizzeo-avec-bdd/`

Pour trouver votre IP:
```
Windows: ipconfig
Cherchez "Adresse IPv4"
```

---

## 🎉 EXEMPLE COMPLET

### Scénario: Cours de Mathématiques

**Professeur:**
1. Crée un quiz "Fractions - Niveau 5ème"
2. Ajoute 10 questions QCM
3. Lance le quiz
4. Obtient le code PIN: **456789**
5. Écrit le code au tableau
6. Affiche le QR Code au projecteur

**Élèves:**
1. Sortent leur téléphone
2. Vont sur le site
3. Entrent le code **456789**
4. Entrent leur nom
5. Attendent dans le lobby

**Professeur:**
1. Voit 25 élèves connectés
2. Dit "C'est parti!"
3. Les élèves cliquent sur "COMMENCER"

**Pendant le quiz:**
- Les élèves répondent aux questions
- Ils voient immédiatement si c'est bon (vert/rouge)
- Le professeur voit le classement en direct

**À la fin:**
- Chaque élève voit son score
- Le professeur affiche le classement au projecteur
- Les 3 premiers montent sur le podium! 🏆

---

**Bon quiz! 🎮**
