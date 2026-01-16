<?php

namespace App\Repositories;

use App\Interfaces\LoginGuard\LoginGuardRepositoryInterface;
use PDO;

class LoginGuardRepository implements LoginGuardRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function obtenerEstado($usuarioHash)
    {
        $sql = "SELECT * FROM intentos_acceso WHERE usuario_hash = :hash LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hash', $usuarioHash);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarIntento($usuarioHash, $intentos, $nivel, $bloqueadoHasta)
    {
        // Usamos ON DUPLICATE KEY para que la lógica sea atómica y eficiente
        $sql = "INSERT INTO intentos_acceso 
                (usuario_hash, intentos_fallidos, nivel_bloqueo, ultimo_intento, bloqueado_hasta)
                VALUES (:hash, :intentos, :nivel, NOW(), :bloqueado)
                ON DUPLICATE KEY UPDATE 
                    intentos_fallidos = :intentos2,
                    nivel_bloqueo = :nivel2,
                    ultimo_intento = NOW(),
                    bloqueado_hasta = :bloqueado2";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hash', $usuarioHash);
        $stmt->bindParam(':intentos', $intentos);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->bindParam(':bloqueado', $bloqueadoHasta);
        
        // Parámetros para el UPDATE
        $stmt->bindParam(':intentos2', $intentos);
        $stmt->bindParam(':nivel2', $nivel);
        $stmt->bindParam(':bloqueado2', $bloqueadoHasta);

        $stmt->execute();
    }

    public function limpiarCuentas($usuarioHash)
    {
        $sql = "DELETE FROM intentos_acceso WHERE usuario_hash = :hash";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hash', $usuarioHash);
        $stmt->execute();
    }
}