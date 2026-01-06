<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class ReporteRepository
{
    private $conn;

    public function __construct()
    {
        // Obtiene la conexión a la base de datos
        $this->conn = Database::getInstance()->getConnection();
    }

    // Obtiene los contadores globales del sistema
    public function obtenerTotales()
    {
        // Ejecuta múltiples conteos en una sola consulta
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM usuarios WHERE usuario_estado = 1) as total_usuarios,
                    (SELECT COUNT(*) FROM proyectos WHERE fecha_eliminacion IS NULL) as total_proyectos,
                    (SELECT COUNT(*) FROM tareas WHERE fecha_eliminacion IS NULL) as total_tareas";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene la cantidad de tareas agrupadas por estado
    public function tareasPorEstado()
    {
        $sql = "SELECT e.estado_nombre, COUNT(t.tarea_id) as cantidad
                FROM tarea_estados e
                LEFT JOIN tareas t ON e.estado_id = t.estado_id AND t.fecha_eliminacion IS NULL
                GROUP BY e.estado_id, e.estado_nombre";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene las tareas vencidas no completadas
    public function tareasVencidas()
    {
        // Excluye tareas completadas y canceladas
        $sql = "SELECT t.tarea_titulo, t.fecha_limite, u.usuario_nombre, p.proyecto_nombre
                FROM tareas t
                LEFT JOIN usuarios u ON t.usuario_asignado = u.usuario_id
                INNER JOIN proyectos p ON t.proyecto_id = p.proyecto_id
                WHERE t.fecha_eliminacion IS NULL
                  AND t.estado_id NOT IN (4, 99) 
                  AND t.fecha_limite < NOW()
                ORDER BY t.fecha_limite ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene el avance porcentual de los proyectos
    public function avanceProyectos()
    {
        $sql = "SELECT 
                    p.proyecto_nombre,
                    COUNT(t.tarea_id) as total_tareas,
                    SUM(CASE WHEN t.estado_id = 4 THEN 1 ELSE 0 END) as completadas
                FROM proyectos p
                LEFT JOIN tareas t ON p.proyecto_id = t.proyecto_id AND t.fecha_eliminacion IS NULL
                WHERE p.fecha_eliminacion IS NULL
                GROUP BY p.proyecto_id, p.proyecto_nombre";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcula el porcentaje de avance 
        foreach ($resultados as &$fila) {
            $total = (int) $fila['total_tareas'];
            $hechas = (int) $fila['completadas'];

            $fila['porcentaje'] = ($total > 0) ? round(($hechas / $total) * 100, 2) : 0;
        }

        return $resultados;
    }
}
