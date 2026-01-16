<?php

namespace App\Controllers;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Utils\ApiResponse;

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
        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }

    public function crear($datos, $creadorId)
    {
        try {
            $id = $this->tareaService->crearTarea($datos, $creadorId);
            
            ApiResponse::exito("Tarea creada exitosamente.", ['id' => $id]);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function editar($id, $datos)
    {
        try {
            $this->tareaService->editarTarea($id, $datos);
            
            ApiResponse::exito("Tarea actualizada.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            $this->tareaService->eliminarTarea($id);
            
            ApiResponse::exito("Tarea eliminada.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Método específico para la acción de asignar (si decides usar ruta dedicada)
    public function asignar($id, $usuarioId)
    {
        try {
            $this->tareaService->asignarTarea($id, $usuarioId);
            ApiResponse::exito("Tarea asignada correctamente.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}