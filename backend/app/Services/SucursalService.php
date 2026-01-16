<?php

namespace App\Services;

use App\Interfaces\Sucursal\SucursalServiceInterface;
use App\Interfaces\Sucursal\SucursalRepositoryInterface;
use App\Entities\Sucursal;
use App\Constants\Estados;
use Exception;

class SucursalService implements SucursalServiceInterface
{
    private $sucursalRepository;

    public function __construct(SucursalRepositoryInterface $sucursalRepository)
    {
        $this->sucursalRepository = $sucursalRepository;
    }

    // Se eliminó verificarPermisoAdmin (Responsabilidad del Middleware)

    public function listarSucursales()
    {
        return $this->sucursalRepository->listar();
    }

    // Eliminado parámetro $usuarioLogueado
    public function crearSucursal(array $datos)
    {
        $sucursal = new Sucursal();
        $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        
        // Asignamos estado por defecto (Activo) usando constante
        $sucursal->sucursal_estado = Estados::ACTIVO;

        return $this->sucursalRepository->crear($sucursal);
    }

    // Eliminado parámetro $usuarioLogueado
    public function editarSucursal($id, array $datos)
    {
        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        
        if (!$sucursal) {
            throw new Exception("La sucursal no existe.");
        }

        if (!empty($datos['sucursal_nombre'])) {
            $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        }
        if (!empty($datos['sucursal_direccion'])) {
            $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        }
        
        // Actualización de estado (usando constantes para validación si se quisiera)
        if (isset($datos['sucursal_estado'])) {
            $sucursal->sucursal_estado = $datos['sucursal_estado'];
        }

        return $this->sucursalRepository->actualizar($sucursal);
    }

    // Eliminado parámetro $usuarioLogueado
    public function eliminarSucursal($id)
    {
        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        
        if (!$sucursal) {
            throw new Exception("La sucursal no existe.");
        }

        return $this->sucursalRepository->eliminar($id);
    }
}