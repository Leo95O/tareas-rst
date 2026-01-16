<?php

namespace App\Repositories;

use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Entities\Proyecto;
use App\Entities\EstadoProyecto;
use App\Constans\EstadosProyecto;
use PDO;

class ProyectoRepository implements ProyectoRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    private function hidratar($fila)
    {
        // Nota: El constructor de Proyecto espera 'estado_id' o 'proyecto_estado'.
        // Tu BD devuelve 'estado_id' en la tabla proyectos.
        $proyecto = new Proyecto($fila);

        // Hidratar Estado si el JOIN trajo datos
        if (!empty($fila['pe_estado_nombre'])) {
            $estado = new EstadoProyecto([
                'estado_id'     => $fila['pe_estado_id'],
                'estado_nombre' => $fila['pe_estado_nombre'],
                'estado_orden'  => isset($fila['pe_estado_orden']) ? $fila['pe_estado_orden'] : 0
            ]);
            $proyecto->setEstado($estado);
        }

        return $proyecto;
    }

    public function listar($filtros = [])
    {
        // JOIN con proyecto_estados (alias pe)
        // Seleccionamos p.* (proyectos) y pe.* (estados) con alias
        $sql = "SELECT p.*, 
                       pe.estado_id as pe_estado_id, 
                       pe.estado_nombre as pe_estado_nombre,
                       pe.estado_orden as pe_estado_orden
                FROM proyectos p
                INNER JOIN proyecto_estados pe ON p.estado_id = pe.estado_id
                WHERE p.fecha_eliminacion IS NULL"; // Soft delete check
        
        // (Aquí podrías agregar lógica para filtrar por sucursal si viene en $filtros)

        $sql .= " ORDER BY p.proyecto_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $proyectos = [];
        foreach ($resultados as $fila) {
            $proyectos[] = $this->hidratar($fila);
        }
        
        return $proyectos;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, 
                       pe.estado_id as pe_estado_id, 
                       pe.estado_nombre as pe_estado_nombre,
                       pe.estado_orden as pe_estado_orden
                FROM proyectos p
                INNER JOIN proyecto_estados pe ON p.estado_id = pe.estado_id
                WHERE p.proyecto_id = :id AND p.fecha_eliminacion IS NULL LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->hidratar($data) : null;
    }

    public function crear(Proyecto $proyecto)
    {
        $sql = "INSERT INTO proyectos (proyecto_nombre, proyecto_descripcion, sucursal_id, estado_id, usuario_creador, fecha_inicio, fecha_fin, fecha_creacion) 
                VALUES (:nombre, :descripcion, :sucursal, :estado, :creador, :inicio, :fin, NOW())";
        
        $estadoInicial = $proyecto->proyecto_estado ?: EstadosProyecto::PENDIENTE;

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':descripcion', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':sucursal', $proyecto->sucursal_id);
        $stmt->bindParam(':estado', $estadoInicial);
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
                proyecto_descripcion = :descripcion,
                fecha_inicio = :inicio,
                fecha_fin = :fin,
                estado_id = :estado 
                WHERE proyecto_id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $proyecto->proyecto_nombre);
        $stmt->bindParam(':descripcion', $proyecto->proyecto_descripcion);
        $stmt->bindParam(':inicio', $proyecto->fecha_inicio);
        $stmt->bindParam(':fin', $proyecto->fecha_fin);
        $stmt->bindParam(':estado', $proyecto->proyecto_estado);
        $stmt->bindParam(':id', $proyecto->proyecto_id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        // Soft Delete (según tu DB hay campo 'fecha_eliminacion')
        $sql = "UPDATE proyectos SET fecha_eliminacion = NOW() WHERE proyecto_id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}