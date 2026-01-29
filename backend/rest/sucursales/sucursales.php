<?php

use App\Controllers\SucursalController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;
use App\Utils\ApiResponse;

/** @var \Slim\Slim $app */
$container = $app->di;

// =============================================================================
// HELPER: Decodificador JSON Seguro
// =============================================================================
$getJson = function () use ($app) {
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $app->response->status(400);
        echo ApiResponse::alerta(["JSON inválido o mal formado."], []); 
        $app->stop();
    }
    return $data;
};

// =============================================================================
// MIDDLEWARES (Instancia Única)
// =============================================================================
$auth     = AuthMiddleware::verificar($app);
$active   = ActiveUserMiddleware::verificar($app);
$rolAdmin = RolMiddleware::verificar($app, [Roles::ADMIN]);

// =============================================================================
// RUTAS DE SUCURSALES
// =============================================================================

$app->group('/sucursales', function () use ($app, $container, $getJson, $auth, $active, $rolAdmin) {

    // 1. LISTAR (GET /listar)
    // Accesible para cualquier usuario logueado (Admin, PM o User)
    $app->get('/listar', $auth, $active, function () use ($container) {
        $container->get(SucursalController::class)->listar();
    });

    // --- RUTAS ADMINISTRATIVAS (Solo ADMIN) ---
    // Aplicamos $rolAdmin explícitamente en cada una.

    // 2. CREAR (POST /crear)
    $app->post('/crear', $auth, $active, $rolAdmin, function () use ($container, $getJson) {
        $container->get(SucursalController::class)->crear($getJson());
    });

    // 3. EDITAR (PUT /editar/:id)
    $app->put('/editar/:id', $auth, $active, $rolAdmin, function ($id) use ($container, $getJson) {
        $container->get(SucursalController::class)->editar($id, $getJson());
    });

    // 4. ELIMINAR (DELETE /:id)
    $app->delete('/:id', $auth, $active, $rolAdmin, function ($id) use ($container) {
        $container->get(SucursalController::class)->eliminar($id);
    });

});