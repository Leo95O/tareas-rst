<?php

namespace App\Controllers;

use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Utils\ApiResponse;
use App\Exceptions\ValidationException;
use Exception;

class ProyectoController
{
    private $proyectoService;

    public function __construct(ProyectoServiceInterface $service)
    {
        $this->proyectoService = $service;
    }

    public function listar($filtros = [])
    {
        try {
            $lista = $this->proyectoService->listarProyectos($filtros);

            $data = array_map(function ($p) {
                return $p->toArray();
            }, $lista);

            echo ApiResponse::exito("Listado de proyectos.", $data);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ProyectoController::listar: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error al obtener el listado de proyectos.");
        }
    }

    public function obtenerPorId($id)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID del proyecto es obligatorio.");
            }

            $proyecto = $this->proyectoService->obtenerProyectoPorId($id);
            echo ApiResponse::exito("Proyecto recuperado.", $proyecto->toArray());
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ProyectoController::obtenerPorId: " . $e->getMessage());
            echo ApiResponse::error("No se pudo recuperar el proyecto solicitado.");
        }
    }

    public function crear($datos, $creadorId)
    {
        try {
            $id = $this->proyectoService->crearProyecto($datos, $creadorId);
            
            echo ApiResponse::exito("Proyecto creado correctamente.", ['id' => $id]);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ProyectoController::crear: " . $e->getMessage());
            echo ApiResponse::error("Error interno al intentar crear el proyecto.");
        }
    }

    public function editar($id, $datos)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID es necesario para editar el proyecto.");
            }

            $this->proyectoService->editarProyecto($id, $datos);
            
            echo ApiResponse::exito("Proyecto actualizado correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ProyectoController::editar: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error inesperado al actualizar el proyecto.");
        }
    }

    public function eliminar($id)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("ID no proporcionado para eliminación.");
            }

            $this->proyectoService->eliminarProyecto($id);
            
            echo ApiResponse::exito("Proyecto eliminado correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ProyectoController::eliminar: " . $e->getMessage());
            echo ApiResponse::error("No se pudo completar la eliminación del proyecto.");
        }
    }
}