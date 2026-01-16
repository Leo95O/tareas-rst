<?php

namespace App\Controllers;

use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Utils\ApiResponse;
use App\Utils\Crypto;

class ReporteController
{
    private $reporteRepository;

    public function __construct(ReporteRepositoryInterface $repo)
    {
        $this->reporteRepository = $repo;
    }

    /**
     * Carga los datos del Dashboard.
     * @param int|null $usuarioId Si viene null, carga datos globales (Admin). Si trae ID, filtra.
     */
    public function dashboard($usuarioId = null)
    {
        try {
            // 1. Datos comunes (Gráfica de pastel de estados)
            // El repositorio ya sabe filtrar si le pasamos el ID
            $porEstado = $this->reporteRepository->obtenerTareasPorEstado($usuarioId);

            $data = [
                'grafico_estados' => $porEstado,
                // Inicializamos vacíos por seguridad
                'resumen'         => null,
                'tabla_proyectos' => [],
                'lista_vencidas'  => [],
                'carga_trabajo'   => []
            ];

            // 2. Datos Globales (Solo para Admin/PM -> $usuarioId es null)
            if ($usuarioId === null) {
                // KPIs Numéricos (Total Usuarios, Proyectos, Tareas)
                $data['resumen'] = $this->reporteRepository->obtenerTotales();
                
                // Tabla de Proyectos y su avance
                $data['tabla_proyectos'] = $this->reporteRepository->obtenerRendimientoProyectos();
                
                // Tareas Vencidas (Globales)
                $vencidas = $this->reporteRepository->tareasVencidas();

                // Lógica de desencriptación (Correctamente ubicada en el Controller)
                if ($vencidas) {
                    foreach ($vencidas as &$v) {
                        if (!empty($v['usuario_nombre'])) {
                            $v['usuario_nombre'] = Crypto::desencriptar($v['usuario_nombre']);
                        }
                    }
                }
                $data['lista_vencidas'] = $vencidas;
            }

            ApiResponse::exito("Datos del dashboard cargados.", $data);

        } catch (\Exception $e) {
            ApiResponse::error("Error cargando dashboard: " . $e->getMessage());
        }
    }

    /**
     * Reporte específico de carga de trabajo (Para vista de Admin Stats)
     */
    public function adminStats()
    {
        try {
            $carga = $this->reporteRepository->obtenerCargaTrabajoUsuarios();
            
            // Desencriptar nombres de usuarios en la lista
            foreach ($carga as &$c) {
                if (!empty($c['usuario_nombre'])) {
                    $c['usuario_nombre'] = Crypto::desencriptar($c['usuario_nombre']);
                }
            }

            ApiResponse::exito("Estadísticas de carga de trabajo.", ['carga_usuarios' => $carga]);

        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }
}