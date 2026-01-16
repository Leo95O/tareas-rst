<?php

// 1. Carga de dependencias
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../vendor/autoload.php'; 

// 2. Carga de variables de entorno
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 3. Contenedor de Inyección de Dependencias
$container = require __DIR__ . '/../config/container.php';

// 4. Inicialización de Slim
$app = new \Slim\Slim();

// 5. Inyectar contenedor en la App (Vital para los Controllers)
$app->di = $container;

// 6. Configuración (Dinámica según entorno)
// Usamos filter_var para convertir el string "true"/"false" del .env a booleano real
$debugMode = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);
$app->config('debug', $debugMode);

// -----------------------------------------------------------------------------
// MIDDLEWARES GLOBALES
// -----------------------------------------------------------------------------

// A. CORS (Cross-Origin Resource Sharing)
// Este SÍ debe ser global para permitir que el navegador pregunte desde otro dominio.
$app->add(new \App\Middleware\CorsMiddleware());

// NOTA IMPORTANTE:
// NO agregamos AuthMiddleware aquí. 
// La seguridad se aplica GRUPO POR GRUPO en los archivos de rutas.
// Esto permite que el Login sea público.

// -----------------------------------------------------------------------------
// MANEJO DE PREFLIGHT (OPTIONS)
// -----------------------------------------------------------------------------
// Necesario para que Slim 2 no bloquee las peticiones complejas (con headers custom)
$app->options('/:resource+', function ($resource) use ($app) {
    $app->response->status(200);
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

// -----------------------------------------------------------------------------
// RUTAS (MÓDULOS)
// -----------------------------------------------------------------------------
// El orden no altera el producto, pero mantenemos alfabético o lógico.

require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/usuarios/usuarios.php';
require_once __DIR__ . '/../rest/sucursales/sucursales.php'; // ¡Faltaba este!
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';

// -----------------------------------------------------------------------------
// EJECUCIÓN
// -----------------------------------------------------------------------------
$app->run();