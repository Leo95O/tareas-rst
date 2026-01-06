<?php

namespace App\Controllers;

use App\Services\TareaService;
use App\Utils\ApiResponse;
use App\Validators\TareaValidator;
use \Slim\Slim;

class TareaController
{
    private $tareaService;

    public function __construct()
    {
        $this->tareaService = new TareaService();
    }

    // GET /tareas
    public function listar()
    {
        try {
            $app = Slim::getInstance();

            // Obtenemos el usuario que el Middleware validó previamente.
            $usuarioLogueado = $app->usuario;

            // Llamamos al servicio pasando al usuario entero
            // El servicio decidirá qué tareas mostrarle según su rol.
            $tareas = $this->tareaService->listarTareas($usuarioLogueado);

            // Convertimos los objetos a Array para la respuesta JSON
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
            $usuarioLogueado = $app->usuario; // Objeto Usuario completo

            TareaValidator::validarCreacion($datos);

            // CAMBIO: Pasamos $usuarioLogueado (Objeto) en vez de solo el ID
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

            // Necesitamos saber quién está editando para validar permisos
            $usuarioLogueado = $app->usuario;

            TareaValidator::validarEdicion($datos);

            $this->tareaService->editarTarea($id, $datos, $usuarioLogueado);

            ApiResponse::exito("Tarea actualizada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // SOFT DELETE
    public function eliminar($id)
    {
        try {
            $app = Slim::getInstance();

            // Obtenemos quién quiere borrar
            $usuarioLogueado = $app->usuario;

            // Llamamos al servicio PASANDO EL USUARIO
            $this->tareaService->eliminarTarea($id, $usuarioLogueado);

            ApiResponse::exito("Tarea eliminada correctamente.");

        } catch (\Exception $e) {
            ApiResponse::alerta("No se pudo eliminar la tarea: " . $e->getMessage());
        }
    }

    // GET /tareas/bolsa - Lista tareas sin asignar (Bolsa de Tareas)
    public function listarBolsa()
    {
        try {
            $tareas = $this->tareaService->listarTareasBolsa();

            $data = array_map(function ($t) {
                return $t->toArray();
            }, $tareas);

            ApiResponse::exito("Tareas disponibles recuperadas correctamente.", $data);

        } catch (\Exception $e) {
            ApiResponse::error("Error al listar tareas disponibles: " . $e->getMessage());
        }
    }

    // PUT /tareas/:id/asignarme - Usuario se auto-asigna una tarea
    public function asignarme($id)
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $this->tareaService->autoAsignarTarea($id, $usuarioLogueado);

            ApiResponse::exito("¡Tarea asignada correctamente! Ahora puedes verla en 'Mis Tareas'.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}