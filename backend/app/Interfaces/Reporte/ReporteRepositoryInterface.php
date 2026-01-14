<?php

namespace App\Interfaces\Reporte;

interface ReporteRepositoryInterface
{
    public function obtenerTotales();
    public function tareasPorEstado();
    public function tareasVencidas();
    public function avanceProyectos();
}