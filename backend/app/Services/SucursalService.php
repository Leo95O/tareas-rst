<?php

namespace App\Services;

use App\Interfaces\Sucursal\SucursalServiceInterface;
use App\Interfaces\Sucursal\SucursalRepositoryInterface;
use App\Entities\Sucursal;
use App\Constants\Estados;
use App\Exceptions\ValidationException;

class SucursalService implements SucursalServiceInterface
{
    private $sucursalRepository;

    public function __construct(SucursalRepositoryInterface $sucursalRepository)
    {
        $this->sucursalRepository = $sucursalRepository;
    }

    public function listarSucursales()
    {
        return $this->sucursalRepository->listar();
    }

    public function crearSucursal(array $datos)
    {
        $sucursal = new Sucursal();
        $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        $sucursal->sucursal_estado = Estados::ACTIVO;

        return $this->sucursalRepository->crear($sucursal);
    }

    public function editarSucursal($id, array $datos)
    {
        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        
        if (!$sucursal) {
            throw new ValidationException("La sucursal solicitada no existe.");
        }

        if (!empty($datos['sucursal_nombre'])) {
            $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        }

        if (!empty($datos['sucursal_direccion'])) {
            $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        }
        
        if (isset($datos['sucursal_estado'])) {
            $sucursal->sucursal_estado = (int) $datos['sucursal_estado'];
        }

        return $this->sucursalRepository->actualizar($sucursal);
    }

    public function eliminarSucursal($id)
    {
        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        
        if (!$sucursal) {
            throw new ValidationException("No se puede eliminar: La sucursal no existe.");
        }

        return $this->sucursalRepository->eliminar($id);
    }
}