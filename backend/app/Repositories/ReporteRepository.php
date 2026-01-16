<?php

namespace App\Repositories;

use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Constants\Estados;       // Para usuarios (1=Activo)
use App\Constants\EstadosTarea;  // Para tareas (4=Finalizada)
use PDO;

class ReporteRepository implements ReporteRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    // 1. KPIs Generales (Contadores para el Dashboard)
    public function obtenerTotales()
    {
        $activo = Estados::ACTIVO;

        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usuarios WHERE usuario_estado = :activo) as total_usuarios,
                    (SELECT COUNT(*) FROM proyectos WHERE fecha_eliminacion IS NULL) as total_proyectos,
                    (SELECT COUNT(*) FROM tareas WHERE fecha_eliminacion IS NULL) as total_tareas";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':activo', $activo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Gráfica de Tareas por Estado (Pie Chart)
    public function obtenerTareasPorEstado($usuarioId = null)
    {
        $sql = "SELECT e.estado_nombre as name, COUNT(t.tarea_id) as value
                FROM tarea_estados e
                LEFT JOIN tareas t ON e.estado_id = t.estado_id AND t.fecha_eliminacion IS NULL";

        // Filtro opcional por usuario (para "Mis Tareas")
        if ($usuarioId) {
            $sql .= " AND t.usuario_asignado = :usuarioId";
        }

        $sql .= " GROUP BY e.estado_id, e.estado_nombre";

        $stmt = $this->conn->prepare($sql);
        
        if ($usuarioId) {
            $stmt->bindParam(':usuarioId', $usuarioId);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Tabla de Rendimiento de Proyectos
    public function obtenerRendimientoProyectos()
    {
        $finalizada = EstadosTarea::COMPLETADA;

        $sql = "SELECT 
                    p.proyecto_nombre,
                    COUNT(t.tarea_id) as total_tareas,
                    SUM(CASE WHEN t.estado_id = :finalizada THEN 1 ELSE 0 END) as completadas
                FROM proyectos p
                LEFT JOIN tareas t ON p.proyecto_id = t.proyecto_id AND t.fecha_eliminacion IS NULL
                WHERE p.fecha_eliminacion IS NULL
                GROUP BY p.proyecto_id, p.proyecto_nombre
                HAVING total_tareas > 0 
                ORDER BY completadas DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':finalizada', $finalizada);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculamos el % en PHP para mayor precisión y facilidad
        foreach ($resultados as &$fila) {
            $total = (int) $fila['total_tareas'];
            $hechas = (int) $fila['completadas'];
            $fila['porcentaje'] = ($total > 0) ? round(($hechas / $total) * 100, 2) : 0;
        }

        return $resultados;
    }

    // 4. Carga de Trabajo (Quién tiene más tareas pendientes)
    public function obtenerCargaTrabajoUsuarios()
    {
        $activo = Estados::ACTIVO;
        $finalizada = EstadosTarea::COMPLETADA;

        $sql = "SELECT u.usuario_nombre, COUNT(t.tarea_id) as pendientes
                FROM usuarios u
                LEFT JOIN tareas t ON u.usuario_id = t.usuario_asignado 
                     AND t.estado_id != :finalizada 
                     AND t.fecha_eliminacion IS NULL
                WHERE u.usuario_estado = :activo
                GROUP BY u.usuario_id
                ORDER BY pendientes DESC
                LIMIT 10"; // Top 10 usuarios más cargados

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':activo', $activo);
        $stmt->bindParam(':finalizada', $finalizada);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Alerta de Tareas Vencidas
    public function tareasVencidas()
    {
        $finalizada = EstadosTarea::COMPLETADA;

        $sql = "SELECT t.tarea_titulo, t.fecha_limite, u.usuario_nombre, p.proyecto_nombre
                FROM tareas t
                LEFT JOIN usuarios u ON t.usuario_asignado = u.usuario_id
                INNER JOIN proyectos p ON t.proyecto_id = p.proyecto_id
                WHERE t.fecha_eliminacion IS NULL
                  AND t.estado_id != :finalizada 
                  AND t.fecha_limite < CURDATE() -- Solo las que ya pasaron la fecha de hoy
                ORDER BY t.fecha_limite ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':finalizada', $finalizada);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}