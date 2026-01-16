<?php

namespace App\Controllers;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Utils\ApiResponse;
use App\Exceptions\ValidationException;
use Exception;

class TareaController
{
    private $tareaService;

    public function __construct(TareaServiceInterface $service)
    {
        $this->tareaService = $service;
    }

    public function listar($filtros = [])
    {
        try {
            $lista = $this->tareaService->listarTareas($filtros);

            $data = array_map(function ($t) {
                return $t->toArray();
            }, $lista);

            ApiResponse::exito("Listado de tareas.", $data);
        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en TareaController::listar: " . $e->getMessage());
            ApiResponse::error("Ocurrió un error al obtener el listado de tareas.");
        }
    }

    public function crear($datos, $creadorId)
    {
        try {
            $id = $this->tareaService->crearTarea($datos, $creadorId);
            
            ApiResponse::exito("Tarea creada exitosamente.", ['id' => $id]);
        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en TareaController::crear: " . $e->getMessage());
            ApiResponse::error("No se pudo crear la tarea por un error interno.");
        }
    }

    public function editar($id, $datos)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID de la tarea es obligatorio.");
            }

            $this->tareaService->editarTarea($id, $datos);
            
            ApiResponse::exito("Tarea actualizada correctamente.");
        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en TareaController::editar: " . $e->getMessage());
            ApiResponse::error("Error inesperado al intentar actualizar la tarea.");
        }
    }

    public function asignar($id, $usuarioId)
    {
        try {
            if (empty($id) || empty($usuarioId)) {
                throw new ValidationException("Datos de asignación incompletos.");
            }

            $this->tareaService->asignarTarea($id, $usuarioId);
            ApiResponse::exito("Tarea asignada correctamente.");
        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en TareaController::asignar: " . $e->getMessage());
            ApiResponse::error("Ocurrió un error al procesar la asignación.");
        }
    }

    public function eliminar($id)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("ID no válido para eliminación.");
            }

            $this->tareaService->eliminarTarea($id);
            
            ApiResponse::exito("Tarea eliminada correctamente.");
        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en TareaController::eliminar: " . $e->getMessage());
            ApiResponse::error("No se pudo completar la eliminación de la tarea.");
        }
    }
}