<?php

use App\Controllers\ProyectoController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;
use App\Utils\ApiResponse;

/** @var \Slim\Slim $app */
$container = $app->di;

// =============================================================================
// HELPER: Decodificador JSON Seguro (DRY)
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
$auth       = AuthMiddleware::verificar($app);
$active     = ActiveUserMiddleware::verificar($app);
// Definimos quién puede escribir (Admin + PM)
$rolAdminPM = RolMiddleware::verificar($app, [Roles::ADMIN, Roles::PROJECT_MANAGER]);

// =============================================================================
// RUTAS DE PROYECTOS
// =============================================================================

$app->group('/proyectos', function () use ($app, $container, $getJson, $auth, $active, $rolAdminPM) {

    // 1. LISTAR (GET /)
    // Auth + Active
    $app->get('/', $auth, $active, function () use ($app, $container) {
        $filtros = $app->request->get(); // Extraemos query params (?sucursal_id=1)
        $container->get(ProyectoController::class)->listar($filtros);
    });

    // 2. OBTENER POR ID (GET /:id)
    // Auth + Active
    $app->get('/:id', $auth, $active, function ($id) use ($container) {
        $container->get(ProyectoController::class)->obtenerPorId($id);
    });

    // 3. CREAR (POST /)
    // Auth + Active + Rol (Admin/PM)
    $app->post('/', $auth, $active, $rolAdminPM, function () use ($app, $container, $getJson) {
        // Obtenemos el ID del usuario logueado (inyectado por ActiveUserMiddleware)
        $creadorId = $app->usuario->usuario_id; 

        $container->get(ProyectoController::class)->crear($getJson(), $creadorId);
    });

    // 4. EDITAR (PUT /:id)
    // Auth + Active + Rol (Admin/PM)
    $app->put('/:id', $auth, $active, $rolAdminPM, function ($id) use ($container, $getJson) {
        $container->get(ProyectoController::class)->editar($id, $getJson());
    });

    // 5. ELIMINAR (DELETE /:id)
    // Auth + Active + Rol (Admin/PM)
    $app->delete('/:id', $auth, $active, $rolAdminPM, function ($id) use ($container) {
        $container->get(ProyectoController::class)->eliminar($id);
    });

});