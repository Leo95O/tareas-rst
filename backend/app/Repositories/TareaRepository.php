<?php

namespace App\Repositories;

use App\Config\Database;
use App\Entities\Tarea;
use PDO;

class TareaRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function listar($usuarioId, $rolId)
    {
        $sql = "SELECT 
                    t.*, 
                    p.proyecto_nombre as nombre_proyecto,
                    s.sucursal_nombre as nombre_sucursal,
                    e.estado_nombre as nombre_estado,
                    pr.prioridad_nombre as nombre_prioridad,
                    u.usuario_nombre as nombre_asignado
                FROM tareas t
                INNER JOIN proyectos p ON t.proyecto_id = p.proyecto_id
                LEFT JOIN sucursales s ON p.sucursal_id = s.sucursal_id
                INNER JOIN tarea_estados e ON t.estado_id = e.estado_id
                INNER JOIN tarea_prioridades pr ON t.prioridad_id = pr.prioridad_id
                LEFT JOIN usuarios u ON t.usuario_asignado = u.usuario_id
                WHERE t.fecha_eliminacion IS NULL";

        // Rol 3 = Usuario Normal. Solo ve lo que tiene asignado.
        if ($rolId == 3) {
            $sql .= " AND t.usuario_asignado = :usuario_id";
        }

        // Ordenamos por prioridad (Crítica primero) y luego fecha
        $sql .= " ORDER BY pr.prioridad_valor DESC, t.fecha_limite ASC";

        $stmt = $this->conn->prepare($sql);

        // Si agregamos el filtro de usuario, vinculamos el parámetro
        if ($rolId == 3) {
            $stmt->bindParam(':usuario_id', $usuarioId);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //  Convertir los arrays de SQL en Objetos 'Tarea'
        $tareas = [];
        foreach ($resultados as $fila) {
            $tareas[] = new Tarea($fila);
        }

        return $tareas;
    }

    public function crear(Tarea $tarea)
    {
        $sql = "INSERT INTO tareas 
                (tarea_titulo, tarea_descripcion, fecha_limite, prioridad_id, estado_id, proyecto_id, categoria_id, usuario_asignado, usuario_creador)
                VALUES 
                (:titulo, :desc, :limite, :prio, :estado, :proy, :cat, :asignado, :creador)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':titulo', $tarea->tarea_titulo);
        $stmt->bindParam(':desc', $tarea->tarea_descripcion);
        $stmt->bindParam(':limite', $tarea->fecha_limite);
        $stmt->bindParam(':prio', $tarea->prioridad_id);
        $stmt->bindParam(':estado', $tarea->estado_id);
        $stmt->bindParam(':proy', $tarea->proyecto_id);
        $stmt->bindParam(':cat', $tarea->categoria_id);
        $stmt->bindParam(':asignado', $tarea->usuario_asignado);
        $stmt->bindParam(':creador', $tarea->usuario_creador);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar(Tarea $tarea)
    {
        $sql = "UPDATE tareas SET 
                    tarea_titulo        = :titulo,
                    tarea_descripcion   = :desc,
                    fecha_limite        = :limite,
                    prioridad_id        = :prio,
                    estado_id           = :estado,
                    proyecto_id         = :proy,
                    categoria_id        = :cat,
                    usuario_asignado    = :asignado
                WHERE tarea_id = :id";

        $stmt = $this->conn->prepare($sql);

        // Vinculamos los parámetros
        $stmt->bindParam(':titulo', $tarea->tarea_titulo);
        $stmt->bindParam(':desc', $tarea->tarea_descripcion);
        $stmt->bindParam(':limite', $tarea->fecha_limite);
        $stmt->bindParam(':prio', $tarea->prioridad_id);
        $stmt->bindParam(':estado', $tarea->estado_id);
        $stmt->bindParam(':proy', $tarea->proyecto_id);
        $stmt->bindParam(':cat', $tarea->categoria_id);
        $stmt->bindParam(':asignado', $tarea->usuario_asignado);
        $stmt->bindParam(':id', $tarea->tarea_id);

        return $stmt->execute();
    }

    // Soft Delete de una tarea
    public function eliminar($tareaId)
    {
        // En lugar de DELETE FROM, hacemos UPDATE
        $sql = "UPDATE tareas SET fecha_eliminacion = NOW() WHERE tarea_id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $tareaId);

        return $stmt->execute();
    }

    // Buscar tarea por ID para verificar antes de editar/borrar
    public function obtenerPorId($tareaId)
    {
        $sql = "SELECT * FROM tareas WHERE tarea_id = :id AND fecha_eliminacion IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $tareaId);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Tarea($data);
        }
        return null;
    }

    // Listar tareas sin asignar (Bolsa de Tareas)
    public function listarSinAsignar()
    {
        $sql = "SELECT 
                    t.*, 
                    p.proyecto_nombre as nombre_proyecto,
                    e.estado_nombre as nombre_estado,
                    pr.prioridad_nombre as nombre_prioridad
                FROM tareas t
                INNER JOIN proyectos p ON t.proyecto_id = p.proyecto_id
                INNER JOIN tarea_estados e ON t.estado_id = e.estado_id
                INNER JOIN tarea_prioridades pr ON t.prioridad_id = pr.prioridad_id
                WHERE t.usuario_asignado IS NULL 
                  AND t.fecha_eliminacion IS NULL
                ORDER BY pr.prioridad_valor DESC, t.fecha_limite ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tareas = [];
        foreach ($resultados as $fila) {
            $tareas[] = new Tarea($fila);
        }

        return $tareas;
    }

    // Asignar tarea a un usuario
    public function asignarUsuario($tareaId, $usuarioId)
    {
        $sql = "UPDATE tareas SET usuario_asignado = :usuario_id WHERE tarea_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->bindParam(':id', $tareaId);
        return $stmt->execute();
    }
}
