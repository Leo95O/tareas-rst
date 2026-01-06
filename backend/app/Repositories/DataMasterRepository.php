<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class DataMasterRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listarCategorias()
    {
        $sql = "SELECT categoria_id as id, categoria_nombre as nombre FROM tarea_categorias ORDER BY categoria_nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPrioridades()
    {
        $sql = "SELECT prioridad_id as id, prioridad_nombre as nombre 
                FROM tarea_prioridades ORDER BY prioridad_valor DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEstados()
    {
        $sql = "SELECT estado_id as id, estado_nombre as nombre FROM tarea_estados ORDER BY estado_orden ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarSucursales()
    {
        $sql = "SELECT sucursal_id as id, sucursal_nombre as nombre, sucursal_direccion as direccion 
                FROM sucursales ORDER BY sucursal_nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEstadosProyecto()
    {
        $sql = "SELECT estado_id as id, estado_nombre as nombre 
                FROM proyecto_estados ORDER BY estado_orden ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}