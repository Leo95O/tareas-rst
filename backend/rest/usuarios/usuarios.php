<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Middleware\ActiveUserMiddleware; // <--- 1. IMPORTAR
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

// 2. APLICAR MIDDLEWARE EN CADENA
// Orden de ejecución (de afuera hacia adentro): Auth -> ActiveUser -> Ruta
$app->group('/usuarios', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app), // <--- ¡AQUÍ ESTÁ LA PROTECCIÓN!
    function () use ($app, $container) {

    // --- RUTAS PÚBLICAS ---
    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // --- RUTAS ADMIN ---
    $app->group('/admin', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container) {
        
        /** @var \Slim\Slim $app */

        $app->get('/listar', function () use ($app, $container) {
            $rolId = $app->request->get('rol_id');
            $controller = $container->get(UsuarioController::class);
            $controller->listarTodo($rolId);
        });

        $app->post('/crear', function () use ($app, $container) {
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->crearAdmin($datos);
        });

        $app->put('/editar/:id', function ($id) use ($app, $container){
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->editarAdmin($id, $datos);
        });

        $app->delete('/:id', function ($id) use ($app, $container) {
            /** @var \Slim\Slim $app */
            /** @var \App\Entities\Usuario $usuarioLogueado */
            $usuarioLogueado = $app->usuario; 
            
            $controller = $container->get(UsuarioController::class);
            $controller->eliminarAdmin($id, $usuarioLogueado);
        });
    });

});