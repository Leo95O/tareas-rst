<?php

namespace App\Interfaces\LoginGuard;

interface LoginGuardRepositoryInterface
{
    public function obtenerEstado($correo);
    
    public function registrarFallo($correo, $intentos, $nivel, $bloqueadoHasta = null);
    
    public function limpiar($correo);
}