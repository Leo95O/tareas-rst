<?php

namespace App\Controllers;

use App\Interfaces\Sucursal\SucursalServiceInterface;
use App\Utils\ApiResponse;
use App\Validators\SucursalValidator;
use App\Exceptions\ValidationException;
use Exception;

class SucursalController
{
    private $sucursalService;

    public function __construct(SucursalServiceInterface $service)
    {
        $this->sucursalService = $service;
    }

    public function listar()
    {
        try {
            $lista = $this->sucursalService->listarSucursales();
            
            $data = array_map(function ($s) {
                return $s->toArray();
            }, $lista);

            echo ApiResponse::exito("Listado de sucursales.", $data);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en SucursalController::listar: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error al obtener el listado de sucursales.");
        }
    }

    public function crear($datos)
    {
        try {
            SucursalValidator::validar($datos);
            
            $id = $this->sucursalService->crearSucursal($datos);
            
            echo ApiResponse::exito("Sucursal creada correctamente.", ['id' => $id]);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en SucursalController::crear: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error interno al intentar crear la sucursal.");
        }
    }

    public function editar($id, $datos)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID de la sucursal es requerido.");
            }

            if (empty($datos)) {
                throw new ValidationException("No se enviaron datos para actualizar.");
            }

            $this->sucursalService->editarSucursal($id, $datos);
            
            echo ApiResponse::exito("Sucursal actualizada correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en SucursalController::editar: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error inesperado al actualizar la sucursal.");
        }
    }

    public function eliminar($id)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID es necesario para eliminar la sucursal.");
            }

            $this->sucursalService->eliminarSucursal($id);
            
            echo ApiResponse::exito("Sucursal desactivada correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en SucursalController::eliminar: " . $e->getMessage());
            echo ApiResponse::error("No se pudo completar la eliminación de la sucursal.");
        }
    }
}