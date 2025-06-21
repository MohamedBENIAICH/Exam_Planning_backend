# FST Digital Backend

## Une API backend robuste pour la gestion des examens et concours universitaires.

Ce projet est une API backend développée avec Laravel, conçue pour gérer la planification, l'organisation et le suivi des examens au sein d'une institution universitaire. Il fournit les services nécessaires pour le frontend React, gérant les données des étudiants, professeurs, salles de classe, examens, concours, et bien plus encore.

## Technologies Utilisées

-   **Backend :** Laravel (PHP)
-   **Base de données :** MySQL
-   **Gestionnaire de Dépendances :** Composer
-   **Documentation API :** L5-Swagger (OpenAPI)

## Environnements de Développement Recommandés

-   **Backend :** Tout IDE PHP (par exemple, PhpStorm, VS Code avec extensions PHP)

## Prérequis

Assurez-vous de disposer d'une connexion Internet et d'avoir installé les éléments suivants sur votre système :

-   **PHP :** JDK 8.1 ou une version ultérieure.
-   **Composer :** Gestionnaire de dépendances pour PHP.
-   **MySQL :** Serveur MySQL et un outil de gestion (par exemple, phpMyAdmin, MySQL Workbench).
-   **Serveur Web :** Apache ou Nginx (souvent inclus dans XAMPP/WAMP/MAMP pour le développement local).
-   **Git :** Pour cloner le dépôt.

## Instructions d'Installation

### 1. Cloner le Dépôt

Ouvrez un terminal ou une invite de commande, naviguez jusqu'au répertoire où vous souhaitez cloner le projet, et exécutez la commande suivante :

```bash
git clone https://github.com/MohamedBENIAICH/Exam_Planning_backend.git
cd Exam_Planning_backend
```

### 2. Configuration du Backend (Laravel)

1.  **Installer les Dépendances :**
    Dans le répertoire `Exam_Planning_backend`, exécutez la commande suivante pour installer toutes les dépendances PHP nécessaires :

    ```bash
    composer install
    ```

2.  **Configurer le Fichier d'Environnement :**
    Copiez le fichier d'exemple `.env.example` pour créer votre fichier de configuration `.env` :

    ```bash
    cp .env.example .env
    ```

    Ouvrez le fichier `.env` et configurez les informations de votre base de données. Voici un exemple de configuration pour MySQL :

    ```properties
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=exam_planning_db
    DB_USERNAME=root
    DB_PASSWORD=
    ------------------------------
    N'oubliez pas aussi de configurer la partie de gmail smtp comme suit:
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=465
    MAIL_USERNAME=your_email
    MAIL_PASSWORD=your_app_password
    MAIL_ENCRYPTION=ssl
    MAIL_FROM_ADDRESS="your_email"
    MAIL_FROM_NAME="FST Gestion Examen"
    ```

    Assurez-vous de créer une base de données nommée `exam_planning_db` (ou le nom que vous avez choisi) dans votre serveur MySQL.

3.  **Générer la Clé d'Application :**
    Exécutez cette commande pour générer une clé d'application unique, essentielle pour la sécurité de Laravel :

    ```bash
    php artisan key:generate
    ```

4.  **Exécuter les Migrations de la Base de Données :**
    Ceci créera toutes les tables nécessaires dans votre base de données selon les définitions des migrations :

    ```bash
    php artisan migrate
    ```

5.  **Exécuter les Seeders de la Base de Données (facultatif) :**
    Si vous souhaitez peupler votre base de données avec des données de test (par exemple, des salles de classe, des utilisateurs initiaux), exécutez les seeders :
    ```bash
    php artisan db:seed
    ```

## Démarrage du Serveur

Pour démarrer le serveur de développement Laravel :

```bash
php artisan serve
```

Le serveur sera accessible à l'adresse : `http://localhost:8000`

## Documentation de l'API

La documentation de l'API est générée automatiquement via Swagger/OpenAPI. Pour y accéder :

1.  Assurez-vous que le serveur backend est en cours d'exécution.
2.  Visitez l'URL suivante dans votre navigateur : `http://localhost:8000/api/documentation`

## Structure du Projet

```
Exam_Planning_backend/
├── app/
│   ├── Http/
│   │   └── Controllers/  # Contrôleurs de l'application
│   ├── Models/           # Modèles Eloquent (représentent les tables de la BDD)
│   └── Services/         # Classes de services pour la logique métier
├── bootstrap/            # Fichiers de démarrage du framework
├── config/               # Fichiers de configuration de l'application
├── database/
│   ├── factories/        # Factories pour la génération de données de test
│   ├── migrations/       # Fichiers de migration de la base de données
│   └── seeders/          # Classes pour peupler la base de données
├── public/               # Point d'entrée public de l'application
├── resources/
    ├── views             #Contient nos vues blades plus précisement les vues pour envoyer les convocations aux étudiants, aux professeurs et superviseurs. Elle inclut aussi la vue pour avoir le rapport total d'un examen ou d'un concours.
├── routes/               # Définition des routes (api.php pour l'API)
├── storage/              # Stockage des fichiers générés par l'application
├── tests/                # Tests unitaires et fonctionnels
├── .env.example          # Exemple de fichier d'environnement
├── composer.json         # Dépendances Composer
└── README.md             # Ce fichier
```

## Points d'API Principaux

Le backend expose une API REST sur `http://localhost:8000`. Les points d'API sont documentés via Swagger/OpenAPI, mais voici quelques exemples de ressources gérées :

-   `/api/exams` : Gestion des examens (CRUD)
-   `/api/students` : Gestion des étudiants (CRUD)
-   `/api/professors` : Gestion des professeurs (CRUD)
-   `/api/classrooms` : Gestion des salles de classe (CRUD)
-   `/api/concours` : Gestion des concours (CRUD)
-   `/api/auth` : Authentification des utilisateurs

## Gestion des Présences et Absences

Le système intègre une gestion complète des présences et absences des étudiants lors des examens :

### Fonctionnalités principales

-   **Enregistrement de la présence ou de l'absence** :
    -   Un endpoint permet d'enregistrer la présence ou l'absence d'un étudiant à un examen donné.
    -   Si une présence existe déjà pour l'étudiant et l'examen, elle est mise à jour.
-   **Consultation des présences/absences** :
    -   Un endpoint permet de récupérer la liste des présences/absences pour un examen donné (utile pour l'affichage web ou la génération de rapports PDF).
-   **Affichage dans les rapports** :
    -   Les rapports PDF d'examen affichent pour chaque étudiant le statut "Présent" ou "Absent".

### Modèle de données

-   Table `attendances` :
    -   `student_id` (clé étrangère)
    -   `exam_id` (clé étrangère)
    -   `status` (`present` ou `absent`)
    -   `attended_at` (horodatage de la saisie)
    -   `notes` (optionnel)

### Endpoints API

-   `POST /api/attendances` : Enregistrer ou mettre à jour la présence/absence d'un étudiant à un examen
    -   Paramètres : `student_id`, `exam_id`, `status` (`present` ou `absent`)
-   `GET /api/attendances?exam_id={id}` : Récupérer la liste des présences/absences pour un examen

### Exemple d'utilisation

-   Lors de l'appel, l'application mobile ou web envoie l'ID de l'étudiant, l'ID de l'examen et le statut (`present` ou `absent`).
-   Le backend enregistre ou met à jour l'information dans la table `attendances`.
-   Lors de la génération du rapport PDF, le statut de chaque étudiant est affiché.

### Exemple de rendu dans le PDF d'examen

| N°  | CNE     | Nom    | Prénom | Email           | Niveau | Statut  |
| --- | ------- | ------ | ------ | --------------- | ------ | ------- |
| 1   | 2320382 | ABAHRI | Hatim  | hatim@email.com | L3     | Présent |
| 2   | 2320383 | BENALI | Sara   | sara@email.com  | L3     | Absent  |

Cette gestion permet un suivi précis et automatisé des absences lors des examens.

## Nouvelles Fonctionnalités - Annulation et Notifications Automatiques

### Fonctionnalité d'Annulation

Le système a été amélioré pour remplacer la suppression définitive par une annulation intelligente :

-   **Annulation d'Examens** : `POST /api/exams/{id}/cancel`
-   **Annulation de Concours** : `POST /api/concours/{id}/cancel`

Lorsqu'un examen ou concours est annulé :

-   Le statut passe à `cancelled` au lieu d'être supprimé
-   Des notifications automatiques sont envoyées à tous les acteurs concernés
-   L'historique est conservé pour la traçabilité

### Notifications Automatiques

#### Notifications d'Annulation

-   **Étudiants/Candidats** : Informés de l'annulation avec détails
-   **Superviseurs** : Notifiés de l'annulation de leur mission
-   **Professeurs** : Informés de l'annulation de l'examen/concours

#### Notifications de Mise à Jour

-   **Professeurs et Superviseurs** : Informés des modifications apportées
-   **Nouvelles Convocations** : Envoi automatique de convocations mises à jour

### Nouvelles Routes API

```
POST /api/exams/{id}/send-updated-convocations
POST /api/concours/{id}/send-updated-convocations
```

### Statut des Événements

Les examens et concours ont maintenant un champ `status` avec les valeurs :

-   `active` : Événement actif (par défaut)
-   `cancelled` : Événement annulé
-   `completed` : Événement terminé

### Services de Notification

-   `ExamNotificationService` : Gestion des notifications pour les examens
-   `ConcoursNotificationService` : Gestion des notifications pour les concours

### Classes Mail

Nouvelles classes pour les notifications automatiques :

-   `ExamCancellationNotification`
-   `ConcoursCancellationNotification`
-   `ExamUpdateNotification`
-   `ConcoursUpdateNotification`

### Vues d'Email

Nouvelles vues d'email organisées par type et destinataire :

```
resources/views/emails/
├── exam/
│   ├── cancellation-student.blade.php
│   ├── cancellation-supervisor.blade.php
│   ├── cancellation-professeur.blade.php
│   ├── update-supervisor.blade.php
│   └── update-professeur.blade.php
└── concours/
    ├── cancellation-candidat.blade.php
    ├── cancellation-supervisor.blade.php
    ├── cancellation-professeur.blade.php
    ├── update-supervisor.blade.php
    └── update-professeur.blade.php
```

### Migrations

Nouvelles migrations appliquées :

-   `2025_06_15_134942_add_status_to_exams_table.php`
-   `2025_06_15_135002_add_status_to_concours_table.php`

## Dépannage

-   **Le Backend ne démarre pas ?**
    -   Vérifiez que votre serveur web (Apache/Nginx) et MySQL sont démarrés.
    -   Assurez-vous que les identifiants de la base de données dans `.env` sont corrects et que la base de données existe.
    -   Exécutez `php artisan migrate` pour vous assurer que toutes les tables sont créées.
    -   Consultez les logs Laravel (`storage/logs/laravel.log`) pour des messages d'erreur détaillés.
-   **Problèmes de dépendances ?**
    -   Exécutez `composer install` à nouveau pour vous assurer que toutes les dépendances sont correctement installées.



# Annexe : Implémentation des QR Codes pour les Concours

(Le contenu suivant provient de QR_CODE_CONCOURS_IMPLEMENTATION.md)

# Implémentation des QR Codes pour les Concours

## Vue d'ensemble

Cette implémentation ajoute la génération de QR codes dans les PDFs de convocation pour les concours, similaire à la fonctionnalité existante pour les examens. **De plus, elle inclut l'envoi automatique des convocations mises à jour avec QR codes lors de la modification d'un concours, avec une vue spécifique pour distinguer les mises à jour des créations initiales.**

## Format du QR Code

Le QR code contient les informations suivantes au format JSON :

```json
{
    "nom": "ABAHRI",
    "prenom": "Hatim",
    "CNE": "2320382",
    "CIN": "570161"
}
```

## Modifications apportées

### 1. Service ConcoursNotificationService

**Fichier :** `app/Services/ConcoursNotificationService.php`

-   Ajout de l'injection de dépendance du `QRCodeService`
-   Modification de la méthode `generateConvocationPDF()` pour générer un QR code pour chaque candidat
-   **Nouveau :** Ajout de la méthode `generateUpdatedConvocationPDF()` spécifique pour les mises à jour
-   Le QR code est généré avec les données du candidat (nom, prénom, CNE, CIN)
-   Gestion d'erreur si la génération du QR code échoue

### 2. Contrôleur ConcoursController

**Fichier :** `app/Http/Controllers/ConcoursController.php`

-   **Nouveau :** Ajout de l'envoi automatique des convocations mises à jour avec QR codes lors de la modification d'un concours
-   La méthode `update()` appelle maintenant automatiquement `sendUpdatedConvocations()` après la mise à jour
-   Gestion d'erreur et logging pour le suivi des envois

### 3. Templates PDF de convocation

**Fichier :** `resources/views/pdfs/concours-convocation.blade.php`

-   Vue originale pour les créations de concours
-   Ajout du QR code dans la section des informations du candidat
-   Le QR code est affiché avec une taille de 100x100 pixels
-   Ajout d'un texte explicatif sous le QR code

**Fichier :** `resources/views/pdfs/concours-convocation-updated.blade.php` (NOUVEAU)

-   **Vue spécifique pour les mises à jour** avec design distinctif
-   Couleurs orange (#ff6b35) pour indiquer une mise à jour
-   Bannière d'avertissement "CONVOCATION MISE À JOUR"
-   Messages spécifiques pour les modifications
-   QR code avec bordure orange pour indiquer qu'il a été mis à jour
-   Section "IMPORTANT - MODIFICATIONS APPORTÉES"

### 4. Classes Mail

**Fichier :** `app/Mail/ConcoursConvocation.php`

-   Classe Mail originale pour les créations de concours
-   Ajout d'une mention du QR code dans le message de l'email
-   Transmission du chemin du QR code au template d'email

**Fichier :** `app/Mail/ConcoursConvocationUpdated.php` (NOUVEAU)

-   **Classe Mail spécifique pour les mises à jour**
-   Sujet : "Convocation mise à jour - Concours"
-   Messages adaptés au contexte de mise à jour
-   Nom de fichier attaché : "convocation_concours_mise_a_jour.pdf"

### 5. Templates Email

**Fichier :** `resources/views/emails/concours-convocation.blade.php`

-   Template email original pour les créations
-   Ajout d'une mention du QR code dans les consignes importantes

**Fichier :** `resources/views/emails/concours-convocation-updated.blade.php` (NOUVEAU)

-   **Template email spécifique pour les mises à jour**
-   Design distinctif avec couleurs orange
-   Bannière d'avertissement "CONVOCATION MISE À JOUR"
-   Section spéciale "QR Code mis à jour"
-   Messages spécifiques pour les modifications

### 6. Interface utilisateur

**Fichiers :** `src/components/Dashboard/UpcomingConcours.tsx` et `src/pages/ConcourScheduling.tsx`

-   **Nouveau :** Messages de confirmation mis à jour pour informer l'utilisateur que les convocations mises à jour avec QR codes ont été envoyées automatiquement

## Fonctionnalités

### Génération automatique

-   Le QR code est généré automatiquement lors de la création d'un concours
-   Le QR code est également généré lors de l'envoi de convocations mises à jour

### **Envoi automatique lors de la modification**

-   **Nouveau :** Lors de la modification d'un concours, les convocations mises à jour avec QR codes sont automatiquement envoyées aux candidats
-   **Nouveau :** Utilisation d'une vue et d'un email spécifiques pour les mises à jour
-   Les notifications de surveillance et de mise à jour sont également envoyées aux superviseurs et professeurs
-   Gestion complète des erreurs avec logging

### **Distinction visuelle entre création et mise à jour**

-   **Création :** Vue bleue avec design standard
-   **Mise à jour :** Vue orange avec design distinctif et messages d'avertissement
-   QR codes avec bordures différentes (bleue vs orange)
-   Messages et consignes adaptés au contexte

### Stockage

-   Les QR codes sont stockés dans `storage/app/public/qrcodes/`
-   Chaque QR code a un nom unique généré avec `uniqid()`

### Gestion d'erreur

-   Si la génération du QR code échoue, le PDF est généré sans QR code
-   Les erreurs sont loggées pour le débogage

## Utilisation

La fonctionnalité est automatiquement activée lors de :

1. La création d'un nouveau concours (vue et email standards)
2. **La modification d'un concours existant** (vue et email spécifiques pour les mises à jour)
3. L'envoi de convocations mises à jour
4. L'envoi de notifications de surveillance

## Test

Des tests ont été effectués avec succès :

-   **Test de génération de QR code :** Candidat de test : Nom Prenom (CNE: 2356278, CIN: 97392Y6) - PDF généré avec succès (34,521 bytes) incluant le QR code
-   **Test d'envoi automatique lors de la modification :** Envoi de convocations mises à jour avec QR codes réussi
-   **Test de la vue de mise à jour :** Vue spécifique testée avec succès (38,186 bytes) avec design distinctif

## Compatibilité

Cette implémentation est compatible avec la logique existante et n'affecte pas les fonctionnalités actuelles des examens ou des concours.

## Avantages de l'envoi automatique avec vue spécifique

1. **Cohérence des données :** Les candidats reçoivent toujours les informations les plus récentes
2. **QR codes à jour :** Les QR codes sont régénérés avec les nouvelles informations
3. **Transparence :** L'utilisateur est informé que les convocations ont été mises à jour automatiquement
4. **Fiabilité :** Gestion d'erreur robuste pour éviter les interruptions du processus de modification
5. **Distinction claire :** Les candidats peuvent facilement identifier les convocations mises à jour grâce au design distinctif
6. **Messages adaptés :** Les consignes et messages sont spécifiquement adaptés au contexte de mise à jour

## Contributeurs et Contribution

-   [MohamedBENIAICH](https://github.com/MohamedBENIAICH)
-   [DiarraIbra](https://github.com/DiarraIbra)

N'hésitez pas à contribuer à ce projet en ouvrant des issues ou des pull requests. Veuillez suivre les conventions de codage Laravel
