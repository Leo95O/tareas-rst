<?php

use App\Controllers\TareaController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constans\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/tareas', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app),
    function () use ($app, $container) {

    // 1. LISTAR (Con lógica de filtros según Rol)
    $app->get('/', function () use ($app, $container) {
        $filtros = $app->request->get(); // Query params: ?proyecto_id=1
        
        // Regla de Negocio: Si soy Usuario normal, ¿solo veo lo mío?
        // Esto depende de tu regla. Si es "Sí", forzamos el filtro aquí:
        if ($app->usuario->rol_id === Roles::USER) {
            $filtros['usuario_asignado'] = $app->usuario->usuario_id;
        }

        $controller = $container->get(TareaController::class);
        $controller->listar($filtros);
    });

    // 2. CREAR
    $app->post('/', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $creadorId = $app->usuario->usuario_id;

        // Regla de Negocio: El Usuario normal se auto-asigna
        if ($app->usuario->rol_id === Roles::USER) {
            $datos['usuario_asignado'] = $creadorId;
        }

        $controller = $container->get(TareaController::class);
        $controller->crear($datos, $creadorId);
    });

    // 3. EDITAR
    $app->put('/:id', function ($id) use ($app, $container){
        $datos = json_decode($app->request->getBody(), true);
        
        // Aquí podrías validar si un USER intenta cambiar algo prohibido
        // antes de llamar al controlador, o dejar que el servicio valide lo básico.
        
        $controller = $container->get(TareaController::class);
        $controller->editar($id, $datos);
    });

    // 4. ELIMINAR (Solo Admin/PM)
    $rolesPermitidos = [Roles::ADMIN, Roles::PROJECT_MANAGER];
    $app->delete('/:id', RolMiddleware::verificar($app, $rolesPermitidos), function ($id) use ($app, $container) {
        $controller = $container->get(TareaController::class);
        $controller->eliminar($id);
    });

    // 5. ASIGNAR (Ruta específica opcional)
    $app->post('/:id/asignar', function ($id) use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $usuarioAsignado = $datos['usuario_id']; // El ID a quien se le asigna

        $controller = $container->get(TareaController::class);
        $controller->asignar($id, $usuarioAsignado);
    });

});