<?php

namespace App\Controllers;

// 1. Usamos la Interfaz (Asegúrate de que la ruta del namespace sea correcta)
use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Utils\ApiResponse;
use App\Utils\Crypto;

class ReporteController
{
    private $reporteRepository;

    // 2. Inyección de Dependencias: Pedimos el Repositorio mediante su contrato
    public function __construct(ReporteRepositoryInterface $repo)
    {
        $this->reporteRepository = $repo;
    }

    public function dashboardGeneral()
    {
        try {
            // El repositorio ya está instanciado e inyectado
            $totales = $this->reporteRepository->obtenerTotales();
            $porEstado = $this->reporteRepository->tareasPorEstado();
            $avance = $this->reporteRepository->avanceProyectos();
            $vencidas = $this->reporteRepository->tareasVencidas();

            // Desencriptar nombres en la lista de vencidas
            if ($vencidas) {
                foreach ($vencidas as &$v) {
                    if (!empty($v['usuario_nombre'])) {
                        $v['usuario_nombre'] = Crypto::desencriptar($v['usuario_nombre']);
                    }
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