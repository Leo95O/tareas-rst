<?php

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Proyecto;
use PDO;

class ProyectoRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Listar todos los proyectos activos (no eliminados)
     */
    public function listar()
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

    /**
     * Obtener un proyecto por ID
     */
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

    /**
     * Crear un nuevo proyecto
     */
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

    /**
     * Actualizar un proyecto existente
     */
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
     * Soft Delete: Llenar fecha_eliminacion
     */
    public function eliminar($id)
    {
        $sql = "UPDATE proyectos SET fecha_eliminacion = NOW() WHERE proyecto_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}