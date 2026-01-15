<?php

namespace App\Services;

use App\Interfaces\Sucursal\SucursalServiceInterface;
use App\Interfaces\Sucursal\SucursalRepositoryInterface;
use App\Entities\Sucursal;
use App\Constants\Roles;
use Exception;

class SucursalService implements SucursalServiceInterface
{
    private $sucursalRepository;

    public function __construct(SucursalRepositoryInterface $sucursalRepository)
    {
        $this->sucursalRepository = $sucursalRepository;
    }

    private function verificarPermisoAdmin($usuario)
    {
        if ($usuario->rol_id !== Roles::ADMIN) {
            throw new Exception("Acceso denegado. Solo administradores.");
        }
    }

    public function listarSucursales()
    {
        // Podríamos filtrar aquí si quisiéramos, pero el repo ya lo hace
        return $this->sucursalRepository->listar();
    }

    public function crearSucursal(array $datos, $usuarioLogueado)
    {
        $this->verificarPermisoAdmin($usuarioLogueado);

        $sucursal = new Sucursal();
        $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        // El estado se maneja en el repositorio (por defecto 1)

        return $this->sucursalRepository->crear($sucursal);
    }

    public function editarSucursal($id, array $datos, $usuarioLogueado)
    {
        $this->verificarPermisoAdmin($usuarioLogueado);

        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        if (!$sucursal) {
            throw new Exception("La sucursal no existe.");
        }

        // Actualizamos solo lo que viene
        if (!empty($datos['sucursal_nombre'])) {
            $sucursal->sucursal_nombre = $datos['sucursal_nombre'];
        }
        if (!empty($datos['sucursal_direccion'])) {
            $sucursal->sucursal_direccion = $datos['sucursal_direccion'];
        }
        
        // Permitir reactivar o desactivar manualmente si se envía el estado
        if (isset($datos['sucursal_estado'])) {
            $sucursal->sucursal_estado = $datos['sucursal_estado'];
        }

        return $this->sucursalRepository->actualizar($sucursal);
    }

    public function eliminarSucursal($id, $usuarioLogueado)
    {
        $this->verificarPermisoAdmin($usuarioLogueado);

        $sucursal = $this->sucursalRepository->obtenerPorId($id);
        if (!$sucursal) {
            throw new Exception("La sucursal no existe.");
        }

        return $this->sucursalRepository->eliminar($id);
    }
}