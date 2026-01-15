<?php

namespace App\Interfaces\Sucursal;

use App\Entities\Sucursal;

interface SucursalRepositoryInterface
{
    public function listar();
    public function obtenerPorId($id);
    public function crear(Sucursal $sucursal);
    public function actualizar(Sucursal $sucursal);
    public function eliminar($id);
}