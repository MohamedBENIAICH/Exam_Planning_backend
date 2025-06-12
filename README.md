# FST Digital Backend

Ce projet est une API backend développée avec Laravel pour l'application FST Digital (Gestion des examens).

## Prérequis

-   PHP >= 8.1
-   Composer
-   MySQL
-   XAMPP

## Installation

1. Clonez le repository :

```bash
git clone https://github.com/MohamedBENIAICH/Exam_Planning_backend.git
```

2. Installez les dépendances PHP avec Composer :

```bash
composer install
```

3. Copiez le fichier d'environnement :

```bash
cp .env.example .env
```

4. Générez la clé d'application :

```bash
php artisan key:generate
```

5. Configurez votre base de données dans le fichier `.env` :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exam_planning_db
DB_USERNAME=root
DB_PASSWORD=
```

6. Exécutez les migrations de la base de données :

```bash
php artisan migrate
```

## Démarrage du serveur

1. Démarrez votre serveur XAMPP (Apache et MySQL)

2. Lancez le serveur de développement Laravel :

```bash
php artisan serve
```

Le serveur sera accessible à l'adresse : `http://localhost:8000`

## Documentation de l'API

La documentation de l'API est disponible via Swagger/OpenAPI. Pour y accéder :

1. Assurez-vous que le serveur est en cours d'exécution
2. Visitez : `http://localhost:8000/api/documentation`

## Structure du Projet

-   `app/Http/Controllers/` - Contrôleurs de l'application
-   `app/Models/` - Modèles Eloquent
-   `database/migrations/` - Migrations de la base de données
-   `routes/api.php` - Routes de l'API
