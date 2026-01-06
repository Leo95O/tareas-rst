<?php

use App\Controllers\TareaController;

$app = \Slim\Slim::getInstance();
// Agrupamos bajo /tareas
$app->group('/tareas', function () use ($app) {

    // GET /tareas (Listar)
    $app->get('/', function () {
        (new TareaController())->listar();
    });

    // POST /tareas (Crear)
    $app->post('/', function () {
        (new TareaController())->crear();
    });

    // PUT /tareas/:id (Editar)
    $app->put('/:id', function ($id) {
        (new TareaController())->editar($id);
    });

    // DELETE /tareas/:id (Eliminar)
    $app->delete('/:id', function ($id) {
        (new TareaController())->eliminar($id);
    });

    // GET /tareas/bolsa - Lista tareas disponibles (sin asignar)
    $app->get('/bolsa', function () {
        (new TareaController())->listarBolsa();
    });

    // PUT /tareas/:id/asignarme - Auto-asignarse una tarea
    $app->put('/:id/asignarme', function ($id) {
        (new TareaController())->asignarme($id);
    });

});