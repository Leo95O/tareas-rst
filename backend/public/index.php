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

// -----------------------------------------------------------------------------
// MIDDLEWARES (Arquitectura de Cebolla)
// En Slim 2, el middleware agregado AL FINAL se ejecuta PRIMERO.
// -----------------------------------------------------------------------------

// 2. CORS Middleware (EXTERNO):
// Se ejecuta primero. Maneja OPTIONS y pone cabeceras de acceso a todo lo que salga.
$app->add(new \App\Middleware\CorsMiddleware());

// 1. JSON Middleware (INTERNO):
// Se ejecuta después de CORS. Garantiza que la respuesta tenga Content-Type: application/json
// antes de que toque la aplicación o ocurra un error.
$app->add(new \App\Middleware\JsonMiddleware());


// -----------------------------------------------------------------------------
// MANEJO GLOBAL DE ERRORES
// -----------------------------------------------------------------------------
$app->error(function (\Exception $e) use ($app) {
    // 1. Limpieza de Buffer: Evita que se mezcle HTML de error de PHP con nuestro JSON
    if (ob_get_length()) {
        ob_clean();
    }

    // 2. Logging: Registramos el error real en el servidor (Apache/PHP logs)
    error_log("CRITICAL API ERROR: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());

    // 3. Status HTTP 500 (Error Interno)
    $app->response->status(500);

    // 4. CORRECCIÓN PROFESIONAL: Consistencia
    // Usamos ApiResponse para mantener el MISMO formato JSON que el resto del sistema.
    // Nada de { "error": true ... }, usamos { "tipo": 3 ... }
    $mensajeCliente = $app->config('debug') ? $e->getMessage() : 'Ocurrió un error interno en el servidor. Intente más tarde.';
    
    echo \App\Utils\ApiResponse::enviar(3, $mensajeCliente, []);
});

// -----------------------------------------------------------------------------
// RUTAS
// -----------------------------------------------------------------------------
require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/usuarios/usuarios.php';
require_once __DIR__ . '/../rest/sucursales/sucursales.php';
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';
require_once __DIR__ . '/../rest/auth/auth.php';


$app->run();