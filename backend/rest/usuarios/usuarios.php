<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Constants\Roles;

/** * @var \Slim\Slim $app 
 * Esta variable viene "heredada" desde public/index.php gracias al require_once.
 * No necesitamos hacer $app = \Slim\Slim::getInstance();
 */

// Recuperamos el contenedor directamente de la instancia que ya nos pasaron.
$container = $app->di;

// GRUPO PRINCIPAL: /usuarios
$app->group('/usuarios', function () use ($app, $container) {

    // =============================================================
    // 1. ZONA PÚBLICA (Sin Token)
    // =============================================================
    
    // POST /usuarios/login
    $app->post('/login', function () use ($app, $container) {
        // Decodificar JSON del body
        $datos = json_decode($app->request->getBody(), true);
        
        /** @var UsuarioController $controller */
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // =============================================================
    // 2. ZONA PROTEGIDA (Requiere Token + Usuario Activo)
    // =============================================================
    
    // Grupo intermedio para aplicar seguridad en cascada
    $app->group('/', 
        AuthMiddleware::verificar($app),       // 1. ¿Token válido?
        ActiveUserMiddleware::verificar($app), // 2. ¿Usuario no baneado?
        function () use ($app, $container) {

        // --- RUTAS ADMIN (Solo Rol Admin) ---
        $app->group('/admin', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container) {
            
            // Listar usuarios (filtros opcionales)
            $app->get('/listar', function () use ($app, $container) {
                $rolId = $app->request->get('rol_id');
                $controller = $container->get(UsuarioController::class);
                $controller->listarTodo($rolId);
            });

            // Crear administrador
            $app->post('/crear', function () use ($app, $container) {
                $datos = json_decode($app->request->getBody(), true);
                $controller = $container->get(UsuarioController::class);
                $controller->crearAdmin($datos);
            });

            // Editar administrador
            $app->put('/editar/:id', function ($id) use ($app, $container){
                $datos = json_decode($app->request->getBody(), true);
                $controller = $container->get(UsuarioController::class);
                $controller->editarAdmin($id, $datos);
            });

            // Eliminar administrador
            $app->delete('/:id', function ($id) use ($app, $container) {
                // El middleware AuthMiddleware ya inyectó el usuario en la app
                $usuarioLogueado = $app->usuario; 
                
                $controller = $container->get(UsuarioController::class);
                $controller->eliminarAdmin($id, $usuarioLogueado);
            });
        });

        // Aquí irían otras rutas protegidas de usuario normal...

    }); // Fin del grupo protegido

}); // Fin del grupo /usuarios