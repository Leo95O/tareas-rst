<?php

use App\Controllers\UsuarioController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/usuarios', function () use ($app, $container) {

    $app->post('/registro', function () use ($app, $container) {

        $datos = json_decode($app->request->getBody(), true);
        
        $controller = $container->get(UsuarioController::class);

        $controller->registrar($datos);
    });


    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // --- Rutas Privadas (Admin/PM) ---


    // GET /usuarios/admin/listar
    $app->get('/admin/listar', function () use ($app, $container) {
        // Obtenemos parámetros de URL (Query Params) si los hubiera
        $rolId = $app->request->get('rol_id');
        $usuarioLogueado = $app->usuario; // Inyectado por AuthMiddleware

        $controller = $container->get(UsuarioController::class);
        $controller->listarTodo($usuarioLogueado, $rolId);
    });

    // POST /usuarios/ (Crear Admin)
    $app->post('/admin/crear', function () use ($app, $container) {

        $datos = json_decode($app->request->getBody(), true);

        $usuarioLogueado = $app->usuario;

        $controller = $container->get(UsuarioController::class);
    
        $controller->crearAdmin($datos, $usuarioLogueado);
    });

    // PUT /usuarios/:id
    $app->put('/admin/editar/:id', function ($id) use ($app, $container){
        $datos = json_decode($app->request->getBody(), true);
        $usuarioLogueado = $app->usuario;

        $controller = $container->get(UsuarioController::class);
        $controller->editarAdmin($id, $datos, $usuarioLogueado);
    });

    // DELETE /usuarios/:id
    $app->delete('/:id', function ($id) use ($app, $container) {
        $usuarioLogueado = $app->usuario;

        $controller = $container->get(UsuarioController::class);
        $controller->eliminarAdmin($id, $usuarioLogueado);
    });

});