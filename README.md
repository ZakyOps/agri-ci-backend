# Backend Agri CI

Ce dossier contient l'API Laravel du projet Agri CI.

## Lancement local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

## Documentation API

La specification OpenAPI est incluse dans le depot :

```text
api/openapi.yaml
```

Quand le backend tourne, Swagger est disponible ici :

```text
http://127.0.0.1:8000/docs/api
```

## Tests

```bash
php artisan test
```

## Deploiement

Le backend peut etre deploye sur un hebergeur compatible Docker ou PHP. Pour une demo rapide, le projet inclut un `Dockerfile` et un script d'entree qui lancent automatiquement :

- installation des dependances Composer ;
- creation de la base SQLite si necessaire ;
- migrations et donnees de demonstration ;
- serveur Laravel sur le port fourni par l'hebergeur.

Variables utiles :

```text
APP_NAME="Agri CI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://URL_BACKEND_DEPLOYE
APP_KEY=base64:...
DB_CONNECTION=sqlite
CORS_ALLOWED_ORIGINS=https://NOM_UTILISATEUR.github.io
```

Generer une cle d'application avant le deploiement :

```bash
php artisan key:generate --show
```

## Comptes de démonstration

| Rôle | Email | Mot de passe |
| --- | --- | --- |
| Admin | admin@agrici.ci | password |
| Superviseur | supervisor.abidjan@agrici.ci | password |
| Opérateur | operator.abidjan@agrici.ci | password |
