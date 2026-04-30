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

## Deploiement Render

Le backend peut etre deploye sur Render avec Docker. Le depot inclut :

- `Dockerfile`
- `docker/entrypoint.sh`
- `render.yaml`

Dans Render :

1. Creer un nouveau `Web Service`.
2. Choisir le depot `ZakyOps/agri-ci-backend`.
3. Choisir le runtime `Docker`.
4. Laisser Render utiliser le `Dockerfile`.
5. Ajouter les variables demandees par `render.yaml`.

Variables utiles :

```text
APP_NAME="Agri CI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://URL_BACKEND_DEPLOYE
APP_KEY=base64:...
DB_CONNECTION=sqlite
CORS_ALLOWED_ORIGINS=https://zakyops.github.io
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
