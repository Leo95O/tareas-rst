<?php

namespace App\Repositories;

use App\Interfaces\Sucursal\SucursalRepositoryInterface;
use App\Entities\Sucursal;
use PDO;

class SucursalRepository implements SucursalRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    // Listar
    public function listar()
    {
        $sql = "SELECT * FROM sucursales ORDER BY sucursal_id DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sucursales = [];
        foreach ($resultados as $fila) {
            $sucursales[] = new Sucursal($fila);
        }
        
        return $sucursales;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM sucursales WHERE sucursal_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? new Sucursal($data) : null;
    }

    public function crear(Sucursal $sucursal)
    {
        // Forzamos el estado activo al crear
        $estado = 1; 

        $sql = "INSERT INTO sucursales (sucursal_nombre, sucursal_direccion, sucursal_estado) 
                VALUES (:nombre, :direccion, :estado)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $sucursal->sucursal_nombre);
        $stmt->bindParam(':direccion', $sucursal->sucursal_direccion);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
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
        $stmt->bindParam(':estado', $sucursal->sucursal_estado); // Permitimos reactivar/desactivar manual
        $stmt->bindParam(':id', $sucursal->sucursal_id);

        return $stmt->execute();
    }

    // ELIMINADO LÓGICO (Soft Delete)
    public function eliminar($id)
    {
        $sql = "UPDATE sucursales SET sucursal_estado = 0 WHERE sucursal_id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}