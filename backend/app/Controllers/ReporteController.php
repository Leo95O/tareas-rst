<?php

namespace App\Controllers;

use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Utils\ApiResponse;
use App\Utils\Crypto;
use App\Exceptions\ValidationException;
use Exception;

class ReporteController
{
    private $reporteRepository;

    public function __construct(ReporteRepositoryInterface $repo)
    {
        $this->reporteRepository = $repo;
    }

    public function dashboard($usuarioId = null)
    {
        try {
            $porEstado = $this->reporteRepository->obtenerTareasPorEstado($usuarioId);

            $data = [
                'grafico_estados' => $porEstado,
                'resumen'         => null,
                'tabla_proyectos' => [],
                'lista_vencidas'  => [],
                'carga_trabajo'   => []
            ];

            if ($usuarioId === null) {
                $data['resumen'] = $this->reporteRepository->obtenerTotales();
                $data['tabla_proyectos'] = $this->reporteRepository->obtenerRendimientoProyectos();
                
                $vencidas = $this->reporteRepository->tareasVencidas();

                if ($vencidas) {
                    foreach ($vencidas as &$v) {
                        if (!empty($v['usuario_nombre'])) {
                            $v['usuario_nombre'] = Crypto::desencriptar($v['usuario_nombre']);
                        }
                    }
                }
                $data['lista_vencidas'] = $vencidas;
            }

            ApiResponse::exito("Datos del dashboard cargados correctamente.", $data);

        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ReporteController::dashboard: " . $e->getMessage());
            ApiResponse::error("Ocurrió un error interno al generar los datos del dashboard.");
        }
    }

    public function adminStats()
    {
        try {
            $carga = $this->reporteRepository->obtenerCargaTrabajoUsuarios();
            
            foreach ($carga as &$c) {
                if (!empty($c['usuario_nombre'])) {
                    $c['usuario_nombre'] = Crypto::desencriptar($c['usuario_nombre']);
                }
            }

            ApiResponse::exito("Estadísticas de carga de trabajo.", ['carga_usuarios' => $carga]);

        } catch (ValidationException $e) {
            ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en ReporteController::adminStats: " . $e->getMessage());
            ApiResponse::error("Error al procesar las estadísticas administrativas.");
        }
    }
}