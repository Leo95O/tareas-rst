<?php

use App\Controllers\SucursalController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$container = $app->di;

// GRUPO PRINCIPAL /sucursales
// 1. AuthMiddleware: Valida token JWT.
// 2. ActiveUserMiddleware: Valida que el usuario no esté baneado en BD.
$app->group('/sucursales', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app),
    function () use ($app, $container) {

    // --- RUTAS DE LECTURA (Cualquier usuario logueado) ---
    
    $app->get('/listar', function () use ($app, $container) {
        /** @var \Slim\Slim $app */
        $controller = $container->get(SucursalController::class);
        $controller->listar();
    });

    // --- RUTAS ADMINISTRATIVAS (Solo ADMIN) ---
    // Agrupamos las rutas de escritura y aplicamos el RolMiddleware
    
    $app->group('/', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container) {
        
        /** @var \Slim\Slim $app */

        // Crear
        $app->post('/crear', function () use ($app, $container) {
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(SucursalController::class);
            $controller->crear($datos);
        });

        // Editar
        $app->put('/editar/:id', function ($id) use ($app, $container){
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(SucursalController::class);
            $controller->editar($id, $datos);
        });

        // Eliminar (Soft Delete)
        $app->delete('/:id', function ($id) use ($app, $container) {
            // Nota: Ya no necesitamos pasar el usuarioLogueado al controller,
            // porque la seguridad ya la validó el Middleware.
            $controller = $container->get(SucursalController::class);
            $controller->eliminar($id);
        });

    });

});