<?php

use App\Controllers\ProyectoController;

$app = \Slim\Slim::getInstance();

$app->group('/proyectos', function () use ($app) {

    // Todos pueden entrar aquí
    $app->get('/', function () {
        (new ProyectoController())->listar();
    });

    $app->get('/:id', function ($id) {
        (new ProyectoController())->obtenerPorId($id);
    });

    // Estas rutas ejecutarán la lógica del Service y
    // lanzarán error si el usuario es Rol 3.
    $app->post('/', function () {
        (new ProyectoController())->crear();
    });

    $app->put('/:id', function ($id) {
        (new ProyectoController())->editar($id);
    });

    $app->delete('/:id', function ($id) {
        (new ProyectoController())->eliminar($id);
    });

});