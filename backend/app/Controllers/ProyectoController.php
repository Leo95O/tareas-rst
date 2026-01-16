<?php

namespace App\Controllers;

use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Utils\ApiResponse;

class ProyectoController
{
    private $proyectoService;

    public function __construct(ProyectoServiceInterface $service)
    {
        $this->proyectoService = $service;
    }

    // Recibe filtros opcionales (ej: ['estado_id' => 1])
    public function listar($filtros = [])
    {
        try {
            $lista = $this->proyectoService->listarProyectos($filtros);

            $data = array_map(function ($p) {
                return $p->toArray();
            }, $lista);

            ApiResponse::exito("Listado de proyectos.", $data);
        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $proyecto = $this->proyectoService->obtenerProyectoPorId($id);
            ApiResponse::exito("Proyecto recuperado.", $proyecto->toArray());
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Recibe datos limpios y el ID del creador
    public function crear($datos, $creadorId)
    {
        try {
            $id = $this->proyectoService->crearProyecto($datos, $creadorId);
            
            ApiResponse::exito("Proyecto creado.", ['id' => $id]);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Recibe ID y datos a editar
    public function editar($id, $datos)
    {
        try {
            $this->proyectoService->editarProyecto($id, $datos);
            
            ApiResponse::exito("Proyecto actualizado.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            $this->proyectoService->eliminarProyecto($id);
            
            ApiResponse::exito("Proyecto eliminado (Soft Delete).");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}