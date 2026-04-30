<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'application' => 'Agri CI',
        'message' => 'Backend Laravel prêt. Ouvre /api pour voir les routes de test.',
        'api' => url('/api'),
        'documentation_api' => url('/docs/api'),
    ]);
});

Route::get('/docs/openapi.yaml', function () {
    $path = base_path('api/openapi.yaml');

    abort_unless(file_exists($path), 404, 'Specification OpenAPI introuvable.');

    return response()->file($path, [
        'Content-Type' => 'application/yaml',
    ]);
});

Route::get('/docs/api', function () {
    return response(<<<'HTML'
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentation API - Agri CI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; }
        .topbar { display: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function () {
            SwaggerUIBundle({
                url: '/docs/openapi.yaml',
                dom_id: '#swagger-ui',
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout',
                docExpansion: 'list',
                defaultModelsExpandDepth: 1
            });
        };
    </script>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html']);
});
