<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

// 1. SEGURIDAD GLOBAL DEL MÓDULO (/usuarios)
// AuthMiddleware protege todo. Solo deja pasar lo que esté en su Lista Blanca (Login).
$app->group('/usuarios', AuthMiddleware::verificar($app), function () use ($app, $container) {

    // --- RUTAS PÚBLICAS (Whitelisted en AuthMiddleware) ---
    
    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // --- RUTAS DE ADMINISTRACIÓN ---

    $app->group('/admin', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container) {
        


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

            $usuarioLogueado = $app->usuario; 
            
            $controller = $container->get(UsuarioController::class);
            $controller->eliminarAdmin($id, $usuarioLogueado);
        });
    });

});