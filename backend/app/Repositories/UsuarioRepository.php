<?php

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Usuario;
use PDO;
use Exception;

class UsuarioRepository
{
    private $conn;

    public function __construct()
    {
        // Obtiene la conexión a la base de datos
        $this->conn = Database::getInstance()->getConnection();
    }

    // Obtiene un usuario por correo
    public function obtenerPorCorreo($correo)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario_correo = :correo LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Usuario($data);
        }

        return null;
    }

    // Crea un nuevo usuario
    public function crearUsuario(Usuario $usuario)
    {
        $sql = "INSERT INTO usuarios 
                (usuario_nombre, usuario_correo, usuario_password, rol_id, usuario_estado, fecha_creacion) 
                VALUES 
                (:nombre, :correo, :password, :rol, :estado, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $usuario->usuario_nombre);
        $stmt->bindParam(':correo', $usuario->usuario_correo);
        $stmt->bindParam(':password', $usuario->usuario_password);
        $stmt->bindParam(':rol', $usuario->rol_id);
        $stmt->bindParam(':estado', $usuario->usuario_estado);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Lista usuarios activos para uso general
    public function listarTodos()
    {
        $sql = "SELECT u.*, r.rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.rol_id 
                WHERE u.usuario_estado = 1 
                ORDER BY u.usuario_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualiza el token de sesión
    public function actualizarToken($usuarioId, $token)
    {
        $sql = "UPDATE usuarios SET usuario_token = :token WHERE usuario_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $usuarioId);

        return $stmt->execute();
    }

    // Obtiene un usuario activo por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario_id = :id AND usuario_estado = 1 LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Usuario($data);
        }

        return null;
    }

    // Listado avanzado de usuarios para el admin
    public function listar($rolId = null)
    {
        // Permite ver también usuarios inactivos
        $sql = "SELECT u.*, r.rol_nombre 
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.rol_id";

        if ($rolId) {
            $sql .= " WHERE u.rol_id = :rol";
        }

        $sql .= " ORDER BY u.usuario_id DESC";

        $stmt = $this->conn->prepare($sql);

        if ($rolId) {
            $stmt->bindParam(':rol', $rolId);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($resultados as $fila) {
            $usuarios[] = new Usuario($fila);
        }

        return $usuarios;
    }

    // Obtiene un usuario sin validar estado
    public function obtenerParaEditar($id)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Usuario($data) : null;
    }

    // Actualiza los datos del usuario
    public function actualizar(Usuario $usuario)
    {
        $sql = "UPDATE usuarios SET 
                    usuario_nombre = :nombre, 
                    usuario_correo = :correo, 
                    rol_id = :rol, 
                    usuario_estado = :estado 
                WHERE usuario_id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre', $usuario->usuario_nombre);
        $stmt->bindParam(':correo', $usuario->usuario_correo);
        $stmt->bindParam(':rol', $usuario->rol_id);
        $stmt->bindParam(':estado', $usuario->usuario_estado);
        $stmt->bindParam(':id', $usuario->usuario_id);

        return $stmt->execute();
    }

    // Realiza el eliminado lógico del usuario
    public function eliminar($id)
    {
        $sql = "UPDATE usuarios SET usuario_estado = 0 WHERE usuario_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
