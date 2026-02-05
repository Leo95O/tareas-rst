<?php
use App\Controllers\ContextController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Utils\ApiResponse;

$container = $app->di;
$auth   = AuthMiddleware::verificar($app);
$active = ActiveUserMiddleware::verificar($app);

// helper json decoder
$getJson = function () use ($app) {
    return json_decode($app->request->getBody(), true);
};

$app->group('/auth', function () use ($app, $container, $getJson, $auth, $active) {
    
    // POST /auth/context/switch
    $app->post('/context/switch', $auth, $active, function () use ($app, $container, $getJson) {
        // Obtenemos el ID del usuario del token actual
        $usuarioId = $app->usuario->sub; 
        $container->get(ContextController::class)->switchBranch($getJson(), $usuarioId);
    });

});