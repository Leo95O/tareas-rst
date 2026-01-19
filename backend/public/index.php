<?php
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../vendor/autoload.php'; 

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = require __DIR__ . '/../config/container.php';

$app = new \Slim\Slim();

$app->di = $container;

$debugMode = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);
$app->config('debug', $debugMode);

$app->add(new \App\Middleware\CorsMiddleware());

$app->error(function (\Exception $e) use ($app) {
    error_log("CRITICAL API ERROR: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());

    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->status(500);

    echo json_encode([
        'error' => true,
        'msg'   => 'Ocurrió un error interno en el servidor. Intente más tarde.',
        'debug_details' => $app->config('debug') ? $e->getMessage() : null 
    ]);
});

require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/usuarios/usuarios.php';
require_once __DIR__ . '/../rest/sucursales/sucursales.php';
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';

$app->run();