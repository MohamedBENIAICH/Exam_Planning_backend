# FST Digital Backend

## Une API backend robuste pour la gestion des examens universitaires.

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

## Dépannage

-   **Le Backend ne démarre pas ?**
    -   Vérifiez que votre serveur web (Apache/Nginx) et MySQL sont démarrés.
    -   Assurez-vous que les identifiants de la base de données dans `.env` sont corrects et que la base de données existe.
    -   Exécutez `php artisan migrate` pour vous assurer que toutes les tables sont créées.
    -   Consultez les logs Laravel (`storage/logs/laravel.log`) pour des messages d'erreur détaillés.
-   **Problèmes de dépendances ?**
    -   Exécutez `composer install` à nouveau pour vous assurer que toutes les dépendances sont correctement installées.

## Contributeurs et Contribution
*   [MohamedBENIAICH](https://github.com/MohamedBENIAICH)
*   [DiarraIbra](https://github.com/DiarraIbra)

N'hésitez pas à contribuer à ce projet en ouvrant des issues ou des pull requests. Veuillez suivre les conventions de codage Laravel

