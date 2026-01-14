<?php
namespace App\Interfaces;

interface ReporteRepositoryInterface
{
    public function obtenerTotales($usuarioId, $rolId);
    public function avanceProyectos($usuarioId, $rolId);
}