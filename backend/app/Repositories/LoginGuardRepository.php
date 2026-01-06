<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class LoginGuardRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function obtenerEstado($correo)
    {
        $sql = "SELECT * FROM intentos_acceso WHERE usuario_hash = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarFallo($correo, $intentos, $nivel, $bloqueadoHasta = null)
    {
        $sql = "INSERT INTO intentos_acceso (usuario_hash, intentos_fallidos, nivel_bloqueo, ultimo_intento, bloqueado_hasta) 
                VALUES (:correo, :intentos, :nivel, NOW(), :hasta)
                ON DUPLICATE KEY UPDATE 
                    intentos_fallidos = :intentos_u, 
                    nivel_bloqueo = :nivel_u, 
                    ultimo_intento = NOW(), 
                    bloqueado_hasta = :hasta_u";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':intentos', $intentos);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->bindParam(':hasta', $bloqueadoHasta);
        $stmt->bindParam(':intentos_u', $intentos);
        $stmt->bindParam(':nivel_u', $nivel);
        $stmt->bindParam(':hasta_u', $bloqueadoHasta);

        return $stmt->execute();
    }

    public function limpiar($correo)
    {
        $sql = "DELETE FROM intentos_acceso WHERE usuario_hash = :correo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        return $stmt->execute();
    }
}