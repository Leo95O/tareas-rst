<?php

namespace App\Controllers;

use App\Repositories\ReporteRepository;
use App\Utils\ApiResponse;
use App\Utils\Crypto;

class ReporteController
{
    private $reporteRepository;

    public function __construct()
    {
        $this->reporteRepository = new ReporteRepository();
    }

    public function dashboardGeneral()
    {
        try {
            // Obtenemos todas las estadísticas
            $totales = $this->reporteRepository->obtenerTotales();
            $porEstado = $this->reporteRepository->tareasPorEstado();
            $avance = $this->reporteRepository->avanceProyectos();
            $vencidas = $this->reporteRepository->tareasVencidas();

            // Desencriptar nombres en la lista de vencidas
            foreach ($vencidas as &$v) {
                if (!empty($v['usuario_nombre'])) {
                    $v['usuario_nombre'] = Crypto::desencriptar($v['usuario_nombre']);
                }
            }

            // Armamos un JSON grande con todo
            $data = [
                'resumen' => $totales,
                'grafico_estados' => $porEstado,
                'tabla_proyectos' => $avance,
                'lista_vencidas' => $vencidas
            ];

            ApiResponse::exito("Datos del dashboard cargados.", $data);

        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }
}