<?php

namespace App\Repositories;

use App\Interfaces\Proyecto\ProyectoRepositoryInterface; // 1. Usar Interfaz
use App\Entities\Proyecto;
use PDO;

class ProyectoRepository implements ProyectoRepositoryInterface // 2. Implementar
{
    private $conn;

    // 3. Inyección de Dependencias
    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Listar proyectos (Cumple con la firma de la interfaz)
     * Por defecto trae todos (lógica de admin), pero recibe params por si quieres filtrar a futuro.
     */
    public function listar($usuarioId, $rolId)
    {
        $sql = "SELECT p.*, 
                       u.usuario_nombre as nombre_creador,
                       s.sucursal_nombre,
                       pe.estado_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.usuario_creador = u.usuario_id
                LEFT JOIN sucursales s ON p.sucursal_id = s.sucursal_id
                LEFT JOIN proyecto_estados pe ON p.estado_id = pe.estado_id
                WHERE p.fecha_eliminacion IS NULL 
                ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $proyectos = [];
        foreach ($resultados as $fila) {
            $proyectos[] = new Proyecto($fila);
        }

        return $proyectos;
    }

    /**
     * Listar solo proyectos donde el usuario tiene tareas asignadas
     */
    public function listarPorUsuario($usuarioId)
    {
        $sql = "SELECT DISTINCT p.*, 
                       u.usuario_nombre as nombre_creador,
                       s.sucursal_nombre,
                       pe.estado_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.usuario_creador = u.usuario_id
                LEFT JOIN sucursales s ON p.sucursal_id = s.sucursal_id
                LEFT JOIN proyecto_estados pe ON p.estado_id = pe.estado_id
                INNER JOIN tareas t ON t.proyecto_id = p.proyecto_id
                WHERE p.fecha_eliminacion IS NULL 
                AND t.usuario_asignado = :usuario_id
                AND t.fecha_eliminacion IS NULL
                ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $proyectos = [];
        foreach ($resultados as $fila) {
            $proyectos[] = new Proyecto($fila);
        }

        return $proyectos;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, 
                       u.usuario_nombre as nombre_creador,
                       s.sucursal_nombre,
                       pe.estado_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.usuario_creador = u.usuario_id
                LEFT JOIN sucursales s ON p.sucursal_id = s.sucursal_id
                LEFT JOIN proyecto_estados pe ON p.estado_id = pe.estado_id
                WHERE p.proyecto_id = :id AND p.fecha_eliminacion IS NULL";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Proyecto($data) : null;
    }

    public function crear(Proyecto $proyecto)
    {
        $sql = "INSERT INTO proyectos 
                (proyecto_nombre, proyecto_descripcion, sucursal_id, estado_id, usuario_creador, fecha_inicio, fecha_fin, fecha_creacion)
                VALUES 
                (:nombre, :desc, :sucursal, :estado, :creador, :inicio, :fin, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':desc', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':sucursal', $proyecto->sucursal_id);
        $stmt->bindParam(':estado', $proyecto->estado_id);
        $stmt->bindParam(':creador', $proyecto->usuario_creador);
        $stmt->bindParam(':inicio', $proyecto->fecha_inicio);
        $stmt->bindParam(':fin', $proyecto->fecha_fin);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar(Proyecto $proyecto)
    {
        $sql = "UPDATE proyectos SET 
                    proyecto_nombre = :nombre, 
                    proyecto_descripcion = :desc,
                    sucursal_id = :sucursal,
                    estado_id = :estado,
                    fecha_inicio = :inicio,
                    fecha_fin = :fin
                WHERE proyecto_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':desc', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':sucursal', $proyecto->sucursal_id);
        $stmt->bindParam(':estado', $proyecto->estado_id);
        $stmt->bindParam(':inicio', $proyecto->fecha_inicio);
        $stmt->bindParam(':fin', $proyecto->fecha_fin);
        $stmt->bindParam(':id', $proyecto->proyecto_id);

        return $stmt->execute();
    }

    /**
     * Soft Delete
     * Actualizamos la firma para aceptar $usuarioId (aunque no lo usemos en la query)
     * para cumplir con la interfaz.
     */
    public function eliminar($id, $usuarioId = null)
    {
        $sql = "UPDATE proyectos SET fecha_eliminacion = NOW() WHERE proyecto_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * NUEVO MÉTODO: Requerido por la Interfaz para validaciones
     */
    public function existeNombre($nombre, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) FROM proyectos WHERE proyecto_nombre = :nombre AND fecha_eliminacion IS NULL";
        
        if ($excluirId) {
            $sql .= " AND proyecto_id != :id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}