<?php

namespace App\Repositories;

use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Entities\Usuario;
use App\Entities\Rol;
use App\Entities\EstadoUsuario;
use App\Constants\Estados;
use PDO;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Convierte una fila de base de datos en un objeto Usuario hidratado.
     */
    private function hidratarUsuario($fila)
    {
        $usuario = new Usuario($fila);

        // Hidratación de ROL
        if (!empty($fila['rol_nombre'])) {
            $rol = new Rol([
                'rol_id'     => $fila['rol_id'],
                'rol_nombre' => $fila['rol_nombre']
            ]);
            $usuario->setRol($rol);
        }

        // Hidratación de ESTADO
        if (!empty($fila['ue_estado_nombre'])) {
            $estado = new EstadoUsuario([
                'estado_id'          => $fila['ue_estado_id'],
                'estado_nombre'      => $fila['ue_estado_nombre'],
                'estado_descripcion' => isset($fila['ue_estado_descripcion']) ? $fila['ue_estado_descripcion'] : null
            ]);
            $usuario->setEstado($estado);
        }

        return $usuario;
    }

    public function obtenerPorCorreo($correo)
    {
        $sql = "SELECT u.*, 
                       r.rol_nombre, 
                       ue.estado_id as ue_estado_id, 
                       ue.estado_nombre as ue_estado_nombre, 
                       ue.estado_descripcion as ue_estado_descripcion
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.rol_id
                LEFT JOIN usuario_estados ue ON u.usuario_estado = ue.estado_id
                WHERE u.usuario_correo = :correo LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hidratarUsuario($data) : null;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT u.*, 
                       r.rol_nombre, 
                       ue.estado_id as ue_estado_id, 
                       ue.estado_nombre as ue_estado_nombre, 
                       ue.estado_descripcion as ue_estado_descripcion
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.rol_id
                LEFT JOIN usuario_estados ue ON u.usuario_estado = ue.estado_id
                WHERE u.usuario_id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hidratarUsuario($data) : null;
    }

    public function obtenerParaEditar($id)
    {
        return $this->obtenerPorId($id);
    }

    public function listar($filtroRol = null)
    {
        $sql = "SELECT u.*, 
                       r.rol_nombre, 
                       ue.estado_id as ue_estado_id, 
                       ue.estado_nombre as ue_estado_nombre, 
                       ue.estado_descripcion as ue_estado_descripcion
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.rol_id
                INNER JOIN usuario_estados ue ON u.usuario_estado = ue.estado_id";
        
        if ($filtroRol) {
            $sql .= " WHERE u.rol_id = :rol";
        }

        $sql .= " ORDER BY u.usuario_id DESC";

        $stmt = $this->conn->prepare($sql);

        if ($filtroRol) {
            $stmt->bindParam(':rol', $filtroRol);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($resultados as $fila) {
            $usuarios[] = $this->hidratarUsuario($fila);
        }

        return $usuarios;
    }

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

        $stmt->execute();
        return $this->conn->lastInsertId();
    }

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

    public function eliminar($id)
    {
        $sql = "UPDATE usuarios SET usuario_estado = :estado WHERE usuario_id = :id";
        
        $estadoInactivo = Estados::INACTIVO;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estadoInactivo);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function actualizarToken($usuarioId, $token)
    {
        $sql = "UPDATE usuarios SET usuario_token = :token WHERE usuario_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $usuarioId);
        return $stmt->execute();
    }
}