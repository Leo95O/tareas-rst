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

    public function crear($datos, $usuarioLogueado)
    {
        try {
            SucursalValidator::validar($datos);
            
            $id = $this->sucursalService->crearSucursal($datos, $usuarioLogueado);
            
            ApiResponse::exito("Sucursal creada correctamente.", ['id' => $id]);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function editar($id, $datos, $usuarioLogueado)
    {
        try {
            // Validamos campos básicos si vienen
            if (empty($datos)) throw new \Exception("No se enviaron datos.");

            $this->sucursalService->editarSucursal($id, $datos, $usuarioLogueado);
            
            ApiResponse::exito("Sucursal actualizada.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminar($id, $usuarioLogueado)
    {
        try {
            $this->sucursalService->eliminarSucursal($id, $usuarioLogueado);
            
            ApiResponse::exito("Sucursal desactivada correctamente.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}