<?php
namespace App\Interfaces;

interface LoginGuardRepositoryInterface
{
    public function obtenerEstado($usuario);
    public function registrarFallo($usuario);
    public function limpiar($usuario);
}