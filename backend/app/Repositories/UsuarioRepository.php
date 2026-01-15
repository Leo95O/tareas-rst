<?php

namespace App\Repositories;

use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Entities\Usuario;
use App\Entities\Rol;
use App\Entities\EstadoUsuario; // Importamos la Entidad, no la Constante
use PDO;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Convierte una fila de base de datos en un Grafo de Objetos completo.
     * (Usuario -> tiene Rol, Usuario -> tiene Estado)
     */
    private function hidratarUsuario($fila)
    {
        // 1. Crear el objeto Usuario base
        $usuario = new Usuario($fila);

        // 2. Hidratar ROL (si la consulta trajo datos de rol)
        if (!empty($fila['rol_nombre'])) {
            $rol = new Rol([
                'rol_id'     => $fila['rol_id'],
                'rol_nombre' => $fila['rol_nombre']
            ]);
            $usuario->setRol($rol);
        }

        // 3. Hidratar ESTADO (si la consulta trajo datos de estado)
        // Usamos los alias definidos en el SQL (ue_*) para evitar colisiones
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
        // JOIN DOBLE: Traemos datos de Roles y Estados en una sola consulta
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
        // Para editar, a veces basta con los datos planos, pero por consistencia usamos la hidratación completa
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

    // --- Métodos de Escritura (INSERT/UPDATE/DELETE) ---
    // Estos no requieren hidratación compleja, solo ejecutan SQL

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
        
        // Aquí insertamos el ID del estado (entero)
        $stmt->bindParam(':estado', $usuario->usuario_estado); 

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
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
        // Soft Delete: Pasamos a estado INACTIVO (ID 2 según tu DB)
        $sql = "UPDATE usuarios SET usuario_estado = 2 WHERE usuario_id = :id";
        
        $stmt = $this->conn->prepare($sql);
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