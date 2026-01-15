<?php

namespace App\Interfaces\Sucursal;

interface SucursalServiceInterface
{
    public function listarSucursales();
    public function crearSucursal(array $datos); // Sin $usuario
    public function editarSucursal($id, array $datos); // Sin $usuario
    public function eliminarSucursal($id); // Sin $usuario
}