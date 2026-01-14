<?php

namespace App\Interfaces\LoginGuard;

interface LoginGuardServiceInterface
{
    public function verificarSiPuedeEntrar($correo);
    public function procesarIntentoFallido($correo, $estadoActual);
    public function limpiarHistorial($correo);
}