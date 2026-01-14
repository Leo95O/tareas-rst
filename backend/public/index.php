<?php

// Carga de configuración inicial y autoloader de dependencias
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../vendor/autoload.php'; 

// Carga de variables de entorno desde archivo .env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Construcción del contenedor de inyección de dependencias
$container = require __DIR__ . '/../config/container.php';

// Inicialización de la instancia de Slim Framework
$app = new \Slim\Slim();

// Asignación del contenedor a la instancia de la aplicación para su uso en rutas
$app->di = $container;

// Configuración de entorno (Debug activado para desarrollo)
$app->config('debug', true);

// Configuración e implementación del Middleware de Autenticación
$usuarioRepo = $container->get(\App\Interfaces\Usuario\UsuarioRepositoryInterface::class);
$app->add(new \App\Middleware\AuthMiddleware($usuarioRepo));

// Configuración del Middleware para gestión de CORS
$app->add(new \App\Middleware\CorsMiddleware());

// Ruta genérica OPTIONS para manejar solicitudes preflight del navegador
$app->options('/:resource+', function ($resource) use ($app) {
    $app->response->status(200);
    $app->response->headers->set('Access-Control-Allow-Origin', '*');
    $app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
});

// Importación de definiciones de rutas de la API
require_once __DIR__ . '/../rest/datamaster/datamaster.php';
require_once __DIR__ . '/../rest/proyectos/proyectos.php';
require_once __DIR__ . '/../rest/reportes/reportes.php';
require_once __DIR__ . '/../rest/tareas/tareas.php';
require_once __DIR__ . '/../rest/usuarios/usuarios.php';

// Ejecución de la aplicación
$app->run();