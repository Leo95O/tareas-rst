<?php

namespace App\Interfaces\LoginGuard;

interface LoginGuardRepositoryInterface
{

    public function obtenerEstado($usuarioHash);

    public function registrarIntento($usuarioHash, $intentos, $nivel, $bloqueadoHasta);

    public function limpiarCuentas($usuarioHash);
}