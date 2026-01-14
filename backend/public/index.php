<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php'; 

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');

$dotenv->load();

$container = require __DIR__ . '/../config/container.php';

$app = new \Slim\Slim();

// Configuración de entorno
$app->config('debug', true);
// $app->config('debug', false); // Producción


$usuarioRepo = $container->get(\App\Interfaces\UsuarioRepositoryInterface::class);


$app->add(new \App\Middleware\AuthMiddleware($usuarioRepo));


$app->add(new \App\Middleware\CorsMiddleware());


require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/usuarios/usuarios.php';

$app->run();