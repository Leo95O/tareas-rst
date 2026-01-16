<?php

namespace App\Repositories;

use App\Interfaces\Sucursal\SucursalRepositoryInterface;
use App\Entities\Sucursal;
use App\Entities\EstadoSucursal;
use App\Constants\Estados;
use PDO;

class SucursalRepository implements SucursalRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    private function hidratar($fila)
    {
        $sucursal = new Sucursal($fila);

        if (!empty($fila['se_estado_nombre'])) {
            $estado = new EstadoSucursal([
                'estado_id'     => $fila['se_estado_id'],
                'estado_nombre' => $fila['se_estado_nombre']
            ]);
            $sucursal->setEstado($estado);
        }

        return $sucursal;
    }

    public function listar()
    {
        $sql = "SELECT s.*, 
                       se.estado_id as se_estado_id, 
                       se.estado_nombre as se_estado_nombre
                FROM sucursales s
                INNER JOIN sucursal_estados se ON s.sucursal_estado = se.estado_id
                ORDER BY s.sucursal_id DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sucursales = [];
        foreach ($resultados as $fila) {
            $sucursales[] = $this->hidratar($fila);
        }
        
        return $sucursales;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT s.*, 
                       se.estado_id as se_estado_id, 
                       se.estado_nombre as se_estado_nombre
                FROM sucursales s
                INNER JOIN sucursal_estados se ON s.sucursal_estado = se.estado_id
                WHERE s.sucursal_id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? $this->hidratar($data) : null;
    }

    public function crear(Sucursal $sucursal)
    {
        $sql = "INSERT INTO sucursales (sucursal_nombre, sucursal_direccion, sucursal_estado) 
                VALUES (:nombre, :direccion, :estado)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $sucursal->sucursal_nombre);
        $stmt->bindParam(':direccion', $sucursal->sucursal_direccion);
        $stmt->bindParam(':estado', $sucursal->sucursal_estado);

        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function actualizar(Sucursal $sucursal)
    {
        $sql = "UPDATE sucursales SET 
                sucursal_nombre = :nombre, 
                sucursal_direccion = :direccion,
                sucursal_estado = :estado 
                WHERE sucursal_id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $sucursal->sucursal_nombre);
        $stmt->bindParam(':direccion', $sucursal->sucursal_direccion);
        $stmt->bindParam(':estado', $sucursal->sucursal_estado);
        $stmt->bindParam(':id', $sucursal->sucursal_id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $sql = "UPDATE sucursales SET sucursal_estado = :estado WHERE sucursal_id = :id";
        
        $estadoInactivo = Estados::INACTIVO;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estadoInactivo);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}