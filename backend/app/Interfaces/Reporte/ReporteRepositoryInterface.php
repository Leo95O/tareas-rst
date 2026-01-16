<?php

namespace App\Interfaces\Reporte;

interface ReporteRepositoryInterface
{
    public function obtenerTotales();
    public function obtenerTareasPorEstado($usuarioId = null);
    public function obtenerRendimientoProyectos();
    public function obtenerCargaTrabajoUsuarios();
    public function tareasVencidas();
}