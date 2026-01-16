<?php

namespace App\Repositories;

use App\Interfaces\DataMaster\DataMasterRepositoryInterface;
use PDO;

class DataMasterRepository implements DataMasterRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function obtenerRoles()
    {
        $sql = "SELECT rol_id as id, rol_nombre as nombre FROM roles ORDER BY rol_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NUEVO: Implementación para Usuarios ---
    public function obtenerEstadosUsuario()
    {
        // Tabla creada en la refactorización de Usuarios
        $sql = "SELECT estado_id as id, estado_nombre as nombre FROM usuario_estados ORDER BY estado_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NUEVO: Implementación para Sucursales ---
    public function obtenerEstadosSucursal()
    {
        // Tabla creada en la refactorización de Sucursales
        $sql = "SELECT estado_id as id, estado_nombre as nombre FROM sucursal_estados ORDER BY estado_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadosProyecto()
    {
        // Asumiendo que esta tabla ya existe o existirá (lo verificaremos en el módulo Proyectos)
        $sql = "SELECT estado_id as id, estado_nombre as nombre FROM proyecto_estados ORDER BY estado_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadosTarea()
    {
        // Asumiendo que esta tabla ya existe o existirá (lo verificaremos en el módulo Tareas)
        $sql = "SELECT estado_id as id, estado_nombre as nombre FROM tarea_estados ORDER BY estado_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerPrioridades()
    {
        // Asumiendo que la tabla se llama 'tarea_prioridades' o 'prioridades'
        // Ajusta el nombre de la tabla si es necesario según tu BD
        $sql = "SELECT prioridad_id as id, prioridad_nombre as nombre, prioridad_valor as valor FROM tarea_prioridades ORDER BY prioridad_valor DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}