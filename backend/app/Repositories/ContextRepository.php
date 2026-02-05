<?php
namespace App\Repositories;

use App\Interfaces\Context\ContextRepositoryInterface;
use PDO;

class ContextRepository implements ContextRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function verificarAcceso($usuarioId, $sucursalId)
    {
        // Verificamos si existe la relación en tu tabla nueva
        $sql = "SELECT COUNT(*) FROM usuario_sucursales 
                WHERE usuario_id = :uid AND sucursal_id = :sid";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':uid', $usuarioId);
        $stmt->bindParam(':sid', $sucursalId);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function obtenerSucursalData($sucursalId)
    {
        // Necesitamos el nombre para meterlo al Token
        $sql = "SELECT sucursal_id, sucursal_nombre FROM sucursales WHERE sucursal_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $sucursalId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}