# 🎓 GestAbsences - Système de Gestion des Absences

Dashboard Administrateur / Agent de scolarité pour la gestion des absences dans un établissement d'enseignement.

![Dashboard Preview](docs/dashboard-preview.png)

## 🚀 Fonctionnalités

### ✅ Dashboard V1 - Vue Générale

- **📊 Cartes KPI** : Statistiques en temps réel
  - Nombre total d'étudiants
  - Nombre total d'absences
  - Justifications en attente
  - Taux d'absentéisme global

- **📈 Graphique** : Histogramme des absences par classe (Chart.js)

- **📋 Tableau** : 10 dernières absences avec statut

- **⚡ Actions rapides** :
  - Saisir une absence
  - Valider une justification
  - Générer un rapport
  - Gérer les utilisateurs

### 🔐 Authentification
- Connexion sécurisée
- Gestion des rôles (Admin, Agent, Enseignant)
- Session persistante

### 📝 Gestion des Absences
- Saisie des absences
- Filtrage par classe, date, statut
- Export des données

### ✅ Gestion des Justifications
- Soumission de justifications
- Validation / Refus par l'administration
- Pièces jointes

### 📄 Rapports
- Rapport global
- Rapport par classe
- Statistiques mensuelles
- Export PDF / Excel

---

## 🛠 Technologies Utilisées

| Technologie | Version | Usage |
|-------------|---------|-------|
| **PHP** | 8.1+ | Langage backend |
| **Symfony** | 6.3 | Framework PHP |
| **Doctrine ORM** | 2.15+ | Mapping objet-relationnel |
| **Twig** | 3.x | Moteur de templates |
| **Bootstrap** | 5.3 | Framework CSS |
| **Chart.js** | 4.4 | Graphiques |
| **MySQL** | 8.0 | Base de données |

---

## 📦 Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 8.0 ou MariaDB
- Symfony CLI (recommandé)

### Étapes d'installation

```bash
# 1. Cloner le projet (ou copier les fichiers)
cd c:\Users\pc\Desktop\web

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données dans .env
# DATABASE_URL="mysql://root:password@127.0.0.1:3306/gestion_absences"

# 4. Créer la base de données
php bin/console doctrine:database:create

# 5. Exécuter les migrations
php bin/console doctrine:schema:create

# 6. Charger les données de démonstration
php bin/console doctrine:fixtures:load --no-interaction

# 7. Lancer le serveur de développement
symfony server:start
# ou
php -S localhost:8000 -t public/
```

### Accès à l'application

- **URL** : http://localhost:8000
- **Login Admin** : `admin@gestabsences.com` / `admin123`
- **Login Agent** : `agent@gestabsences.com` / `agent123`

---

## 📁 Structure du Projet

```
web/
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml
│   │   ├── framework.yaml
│   │   ├── security.yaml
│   │   └── twig.yaml
│   ├── bundles.php
│   └── routes.yaml
├── public/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── DashboardController.php
│   │   └── SecurityController.php
│   ├── DataFixtures/
│   │   └── AppFixtures.php
│   ├── Entity/
│   │   ├── Absence.php
│   │   ├── Classe.php
│   │   ├── Etudiant.php
│   │   ├── Justification.php
│   │   ├── Matiere.php
│   │   └── User.php
│   ├── Repository/
│   │   ├── AbsenceRepository.php
│   │   ├── ClasseRepository.php
│   │   ├── EtudiantRepository.php
│   │   ├── JustificationRepository.php
│   │   ├── MatiereRepository.php
│   │   └── UserRepository.php
│   └── Kernel.php
├── templates/
│   ├── admin/
│   │   ├── absences.html.twig
│   │   ├── dashboard.html.twig
│   │   ├── justifications.html.twig
│   │   └── rapport.html.twig
│   ├── security/
│   │   └── login.html.twig
│   └── base.html.twig
├── .env
├── composer.json
└── README.md
```

---

## 🎨 Captures d'écran

### Page de connexion
- Interface moderne et épurée
- Gradient de fond élégant
- Identifiants de démo affichés

### Dashboard principal
- 4 cartes KPI colorées
- Graphique des absences par classe
- Tableau des dernières absences
- Actions rapides accessibles

### Gestion des justifications
- Liste des justifications en attente
- Boutons de validation/refus
- Historique des traitements

---

## 🔧 Personnalisation

### Modifier les couleurs
Éditez les variables CSS dans `templates/base.html.twig` :

```css
:root {
    --primary-color: #4f46e5;
    --secondary-color: #6366f1;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
}
```

### Ajouter des matières/classes
Modifiez le fichier `src/DataFixtures/AppFixtures.php`

---

## 📊 Diagramme UML - Cas d'Utilisation

```
┌─────────────────────────────────────────────────────────┐
│                    GestAbsences                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌─────────────┐                                       │
│  │    Admin    │──── S'authentifier                    │
│  │   / Agent   │──── Saisir les absences               │
│  └─────────────┘──── Valider une justification         │
│         │       ──── Générer un rapport                │
│         │       ──── Consulter les absences            │
│         │       ──── Gérer les utilisateurs            │
│         │                                              │
│         └────────── Tableau de bord (Dashboard)        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 📄 Licence

Ce projet est sous licence MIT.

---

## 👨‍💻 Auteur

Projet réalisé pour la gestion des absences dans un établissement d'enseignement supérieur.

---

## 🔮 Évolutions Futures (V2)

- [ ] Notifications par email
- [ ] Application mobile
- [ ] Export automatisé des rapports
- [ ] Intégration avec le calendrier
- [ ] Statistiques avancées avec IA
- [ ] Multi-établissements
