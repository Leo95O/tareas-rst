<?php

namespace App\Controllers;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Utils\ApiResponse;
use App\Validators\TareaValidator;
use \Slim\Slim;

class TareaController
{
    private $tareaService;

    public function __construct(TareaServiceInterface $service)
    {
        $this->tareaService = $service;
    }

    // GET /tareas
    public function listar()
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $tareas = $this->tareaService->listarTareas($usuarioLogueado);

            $data = array_map(function ($t) {
                return $t->toArray();
            }, $tareas);

            ApiResponse::exito("Tareas recuperadas correctamente.", $data);

        } catch (\Exception $e) {
            ApiResponse::error("Error al listar tareas: " . $e->getMessage());
        }
    }

    // POST /tareas
    public function crear()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            TareaValidator::validarCreacion($datos);

            $nuevoId = $this->tareaService->crearTarea($datos, $usuarioLogueado);

            ApiResponse::exito("Tarea creada exitosamente.", ['id' => $nuevoId]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // PUT /tareas/:id
    public function editar($id)
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            TareaValidator::validarEdicion($datos);

            $this->tareaService->editarTarea($id, $datos, $usuarioLogueado);

            ApiResponse::exito("Tarea actualizada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // DELETE /tareas/:id
    public function eliminar($id)
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $this->tareaService->eliminarTarea($id, $usuarioLogueado);

            ApiResponse::exito("Tarea eliminada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta("No se pudo eliminar la tarea: " . $e->getMessage());
        }
    }

    // GET /tareas/bolsa
    public function listarBolsa()
    {
        try {
            $tareas = $this->tareaService->listarBolsa();

            $data = array_map(function ($t) {
                return $t->toArray();
            }, $tareas);

            ApiResponse::exito("Tareas disponibles recuperadas correctamente.", $data);

        } catch (\Exception $e) {
            ApiResponse::error("Error al listar tareas disponibles: " . $e->getMessage());
        }
    }

    // PUT /tareas/:id/asignarme
    public function asignarme($id)
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $this->tareaService->asignarTarea($id, $usuarioLogueado);

            ApiResponse::exito("¡Tarea asignada correctamente! Ahora puedes verla en 'Mis Tareas'.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}