<?php
namespace App\Interfaces;

interface LoginGuardServiceInterface
{
    public function verificarAcceso($usuario);
    public function registrarIntentoFallido($usuario);
    public function limpiarIntentos($usuario);
}