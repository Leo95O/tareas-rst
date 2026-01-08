<?php
require_once __DIR__ . '/../config/config.php';

$app = new \Slim\Slim();

$app->config('debug', true);

$app->add(new \App\Middleware\AuthMiddleware());
$app->add(new \App\Middleware\CorsMiddleware());

// Carga de rutas
require_once __DIR__ . '/../rest/usuarios/usuarios.php';
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';

$app->run();