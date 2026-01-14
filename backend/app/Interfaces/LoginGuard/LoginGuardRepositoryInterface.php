<?php

namespace App\Interfaces\LoginGuard;

interface LoginGuardRepositoryInterface
{
    public function obtenerEstado($correo);
    
    // Debe coincidir con los parámetros del repositorio
    public function registrarFallo($correo, $intentos, $nivel, $bloqueadoHasta = null);
    
    public function limpiar($correo);
}