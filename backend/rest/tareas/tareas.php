<?php

use App\Controllers\TareaController;
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
$auth   = AuthMiddleware::verificar($app);
$active = ActiveUserMiddleware::verificar($app);

// Para borrar, solo Admin y PM
$rolBorrar = RolMiddleware::verificar($app, [Roles::ADMIN, Roles::PROJECT_MANAGER]);

// =============================================================================
// RUTAS DE TAREAS
// =============================================================================

$app->group('/tareas', function () use ($app, $container, $getJson, $auth, $active, $rolBorrar) {

    // 1. LISTAR (GET /)
    // Auth + Active
    $app->get('/', $auth, $active, function () use ($app, $container) {
        $filtros = $app->request->get(); // Query params: ?proyecto_id=1
        
        // REGLA DE NEGOCIO: Si soy Usuario normal (Rol 3), forzamos ver solo MIS tareas
        // Nota: ActiveUserMiddleware ya convirtió $app->usuario en la Entidad completa de la BD
        if ($app->usuario->rol_id == Roles::USER) {
            $filtros['usuario_asignado'] = $app->usuario->usuario_id;
        }

        $container->get(TareaController::class)->listar($filtros);
    });

    // 2. CREAR (POST /)
    // Auth + Active
    $app->post('/', $auth, $active, function () use ($app, $container, $getJson) {
        $datos = $getJson();
        $creadorId = $app->usuario->usuario_id;

        // REGLA DE NEGOCIO: El Usuario normal se auto-asigna la tarea obligatoriamente
        if ($app->usuario->rol_id == Roles::USER) {
            $datos['usuario_asignado'] = $creadorId;
        }

        $container->get(TareaController::class)->crear($datos, $creadorId);
    });

    // 3. EDITAR (PUT /:id)
    // Auth + Active
    $app->put('/:id', $auth, $active, function ($id) use ($container, $getJson) {
        // Aquí podrías validar si un USER intenta cambiar algo prohibido
        // Por ahora, pasamos los datos al controlador
        $container->get(TareaController::class)->editar($id, $getJson());
    });

    // 4. ELIMINAR (DELETE /:id)
    // Auth + Active + ROL (Solo Admin/PM)
    $app->delete('/:id', $auth, $active, $rolBorrar, function ($id) use ($container) {
        $container->get(TareaController::class)->eliminar($id);
    });

    // 5. ASIGNAR (POST /:id/asignar)
    // Auth + Active (Podrías agregar $rolBorrar si solo jefes asignan)
    $app->post('/:id/asignar', $auth, $active, function ($id) use ($container, $getJson) {
        $datos = $getJson();
        
        // Validamos que venga el ID
        if (empty($datos['usuario_id'])) {
             echo ApiResponse::alerta(["Debes enviar el 'usuario_id' para asignar."]);
             return;
        }
        
        $usuarioAsignado = $datos['usuario_id'];
        $container->get(TareaController::class)->asignar($id, $usuarioAsignado);
    });

});