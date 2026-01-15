<?php

namespace App\Controllers;

use App\Interfaces\Sucursal\SucursalServiceInterface;
use App\Utils\ApiResponse;
use App\Validators\SucursalValidator;

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

            ApiResponse::exito("Listado de sucursales.", $data);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Eliminamos $usuarioLogueado
    public function crear($datos)
    {
        try {
            SucursalValidator::validar($datos);
            
            $id = $this->sucursalService->crearSucursal($datos);
            
            ApiResponse::exito("Sucursal creada correctamente.", ['id' => $id]);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Eliminamos $usuarioLogueado
    public function editar($id, $datos)
    {
        try {
            if (empty($datos)) throw new \Exception("No se enviaron datos.");

            $this->sucursalService->editarSucursal($id, $datos);
            
            ApiResponse::exito("Sucursal actualizada.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Eliminamos $usuarioLogueado
    public function eliminar($id)
    {
        try {
            $this->sucursalService->eliminarSucursal($id);
            
            ApiResponse::exito("Sucursal desactivada correctamente.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}