<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Constants\Roles;

/** * @var \Slim\Slim $app 
 * @var \DI\Container $container
 */
$container = $app->di;

// =============================================================================
// HELPER LOCAL: Parseo de JSON seguro (Solución al DRY)
// =============================================================================
// Esto evita repetir json_decode en cada ruta y maneja errores de sintaxis.
$getJson = function () use ($app) {
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Si el JSON está mal formado, detenemos todo aquí mismo.
        // Usamos ApiResponse (o el formato manual si prefieres no importarlo aquí)
        $app->response->status(400);
        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode(['tipo' => 2, 'mensajes' => ['JSON inválido o mal formado.'], 'data' => []]);
        $app->stop();
    }
    return $data;
};

// =============================================================================
// RUTAS DE USUARIOS
// =============================================================================

$app->group('/usuarios', function () use ($app, $container, $getJson) {

    // -------------------------------------------------------------------------
    // 1. ZONA PÚBLICA
    // -------------------------------------------------------------------------
    
    $app->post('/login', function () use ($container, $getJson) {
        $controller = $container->get(UsuarioController::class);
        $controller->login($getJson()); // ¡Mira qué limpio!
    });

    // -------------------------------------------------------------------------
    // 2. ZONA PROTEGIDA (Auth + Active + Roles)
    // -------------------------------------------------------------------------
    
    // Definimos los middlewares de seguridad básica
    $seguridadBasica = [
        AuthMiddleware::verificar($app),
        ActiveUserMiddleware::verificar($app)
    ];

    // GRUPO GENERAL PROTEGIDO
    // Usamos call_user_func_array para aplicar los middlewares sin anidar otro grupo innecesario
    // (Nota: En Slim 2, para aplicar array de middlewares a un grupo, la sintaxis varía, 
    // mantenemos el anidamiento mínimo necesario pero organizado).
    
    $app->group('/', $seguridadBasica[0], $seguridadBasica[1], function () use ($app, $container, $getJson) {

        // --- SUB-GRUPO ADMIN ---
        $app->group('/admin', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container, $getJson) {
            
            // Listar
            $app->get('/listar', function () use ($app, $container) {
                $rolId = $app->request->get('rol_id');
                $container->get(UsuarioController::class)->listarTodo($rolId);
            });

            // Crear
            $app->post('/crear', function () use ($container, $getJson) {
                $container->get(UsuarioController::class)->crearAdmin($getJson());
            });

            // Editar
            $app->put('/editar/:id', function ($id) use ($container, $getJson) {
                $container->get(UsuarioController::class)->editarAdmin($id, $getJson());
            });

            // Eliminar
            $app->delete('/:id', function ($id) use ($app, $container) {
                /** @var \stdClass $usuarioLogueado (Documentamos la magia para el IDE) */
                $usuarioLogueado = $app->usuario; 
                
                $container->get(UsuarioController::class)->eliminarAdmin($id, $usuarioLogueado);
            });
        });

        // --- SUB-GRUPO USUARIO NORMAL (Futuro) ---
        // $app->group('/perfil', ...);

    });
});