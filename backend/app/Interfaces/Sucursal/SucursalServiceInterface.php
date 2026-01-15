<?php

namespace App\Interfaces\Sucursal;

interface SucursalServiceInterface
{
    public function listarSucursales();
    public function crearSucursal(array $datos, $usuarioLogueado);
    public function editarSucursal($id, array $datos, $usuarioLogueado);
    public function eliminarSucursal($id, $usuarioLogueado);
}