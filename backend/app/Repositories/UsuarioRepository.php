<?php

namespace App\Repositories;

use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Entities\Usuario;
use App\Entities\Rol; // ¡Importante!
use PDO;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    // Método auxiliar privado para hidratar (DRY - Don't Repeat Yourself)
    // Esto es lo que enamora en una entrevista: reutilización de lógica de mapeo.
    private function hidratarUsuario($fila)
    {
        // 1. Crear Usuario con sus datos directos
        $usuario = new Usuario($fila);

        // 2. Si la consulta trajo datos de Rol, creamos el objeto Rol y lo asignamos
        if (isset($fila['rol_nombre'])) {
            $rol = new Rol([
                'rol_id'     => $fila['rol_id'], // Viene de la tabla usuarios o roles (son iguales en el join)
                'rol_nombre' => $fila['rol_nombre']
            ]);
            $usuario->setRol($rol);
        }

        return $usuario;
    }

    public function obtenerPorCorreo($correo)
    {
        // Login suele necesitar el rol para validar permisos, así que hacemos JOIN
        $sql = "SELECT u.*, r.rol_nombre 
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.rol_id
                WHERE u.usuario_correo = :correo LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hidratarUsuario($data) : null;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT u.*, r.rol_nombre 
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.rol_id
                WHERE u.usuario_id = :id AND u.usuario_estado = 1 LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hidratarUsuario($data) : null;
    }

    public function obtenerParaEditar($id)
    {
        // Para editar, a veces no necesitamos el nombre del rol, solo el ID.
        // Pero por consistencia, podemos traerlo todo.
        $sql = "SELECT * FROM usuarios WHERE usuario_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Usuario($data) : null; 
    }

    public function listar($filtroRol = null)
    {
        $sql = "SELECT u.*, r.rol_nombre 
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.rol_id";
        
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

    // ... (Los métodos crear, actualizar, eliminar se mantienen igual 
    // porque solo tocan la tabla usuarios) ...
    public function crearUsuario(Usuario $usuario) { /* ... código existente ... */ return false; }
    public function actualizar(Usuario $usuario) { /* ... código existente ... */ return false; }
    public function eliminar($id) { /* ... código existente ... */ return false; }
    public function actualizarToken($usuarioId, $token) { /* ... código existente ... */ return false; }
}