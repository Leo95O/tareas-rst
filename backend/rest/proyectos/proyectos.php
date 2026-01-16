<?php

use App\Controllers\ProyectoController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/proyectos', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app),
    function () use ($app, $container) {

    // Listar (con filtros opcionales por query string)
    $app->get('/', function () use ($app, $container) {
        $filtros = $app->request->get(); // Extraemos query params
        $controller = $container->get(ProyectoController::class);
        $controller->listar($filtros);
    });

    $app->get('/:id', function ($id) use ($app, $container) {
        $controller = $container->get(ProyectoController::class);
        $controller->obtenerPorId($id);
    });

    // Rutas de Escritura (Admin/PM)
    $rolesPermitidos = [Roles::ADMIN, Roles::PROJECT_MANAGER];

    $app->group('/', RolMiddleware::verificar($app, $rolesPermitidos), function () use ($app, $container) {
        
        // Crear
        $app->post('/', function () use ($app, $container) {
            // EXTRACCIÓN: Aquí sacamos los datos de Slim
            $datos = json_decode($app->request->getBody(), true);
            $creadorId = $app->usuario->usuario_id; // Y el usuario del token

            $controller = $container->get(ProyectoController::class);
            // INYECCIÓN: Se los pasamos al controlador
            $controller->crear($datos, $creadorId);
        });

        // Editar
        $app->put('/:id', function ($id) use ($app, $container){
            $datos = json_decode($app->request->getBody(), true);
            
            $controller = $container->get(ProyectoController::class);
            $controller->editar($id, $datos);
        });

        // Eliminar
        $app->delete('/:id', function ($id) use ($app, $container) {
            $controller = $container->get(ProyectoController::class);
            $controller->eliminar($id);
        });

    });

});