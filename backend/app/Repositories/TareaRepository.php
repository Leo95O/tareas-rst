<?php

namespace App\Repositories;

use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Entities\Tarea;
use App\Entities\EstadoTarea;
use App\Entities\PrioridadTarea;
use App\Entities\CategoriaTarea;
use PDO;

class TareaRepository implements TareaRepositoryInterface
{
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    private function hidratar($fila)
    {
        $tarea = new Tarea($fila);

        // 1. Estado
        if (!empty($fila['te_estado_nombre'])) {
            $estado = new EstadoTarea([
                'estado_id'     => $fila['te_estado_id'],
                'estado_nombre' => $fila['te_estado_nombre']
            ]);
            $tarea->setEstado($estado);
        }

        // 2. Prioridad
        if (!empty($fila['tp_prioridad_nombre'])) {
            $prioridad = new PrioridadTarea([
                'prioridad_id'     => $fila['tp_prioridad_id'],
                'prioridad_nombre' => $fila['tp_prioridad_nombre'],
                'prioridad_valor'  => $fila['tp_prioridad_valor']
            ]);
            $tarea->setPrioridad($prioridad);
        }

        // 3. Categoría
        if (!empty($fila['tc_categoria_nombre'])) {
            $categoria = new CategoriaTarea([
                'categoria_id'     => $fila['tc_categoria_id'],
                'categoria_nombre' => $fila['tc_categoria_nombre']
            ]);
            $tarea->setCategoria($categoria);
        }

        return $tarea;
    }

    public function listar($filtros = [])
    {
        // Mega JOIN para traer datos ricos
        $sql = "SELECT t.*, 
                       te.estado_id as te_estado_id, te.estado_nombre as te_estado_nombre,
                       tp.prioridad_id as tp_prioridad_id, tp.prioridad_nombre as tp_prioridad_nombre, tp.prioridad_valor as tp_prioridad_valor,
                       tc.categoria_id as tc_categoria_id, tc.categoria_nombre as tc_categoria_nombre
                FROM tareas t
                INNER JOIN tarea_estados te ON t.estado_id = te.estado_id
                INNER JOIN tarea_prioridades tp ON t.prioridad_id = tp.prioridad_id
                LEFT JOIN tarea_categorias tc ON t.categoria_id = tc.categoria_id
                WHERE t.fecha_eliminacion IS NULL";

        // Filtros dinámicos
        if (isset($filtros['proyecto_id'])) {
            $sql .= " AND t.proyecto_id = :proyecto_id";
        }
        if (isset($filtros['usuario_asignado'])) {
            $sql .= " AND t.usuario_asignado = :usuario_asignado";
        }

        $sql .= " ORDER BY t.tarea_id DESC";

        $stmt = $this->conn->prepare($sql);

        if (isset($filtros['proyecto_id'])) {
            $stmt->bindParam(':proyecto_id', $filtros['proyecto_id']);
        }
        if (isset($filtros['usuario_asignado'])) {
            $stmt->bindParam(':usuario_asignado', $filtros['usuario_asignado']);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tareas = [];
        foreach ($resultados as $fila) {
            $tareas[] = $this->hidratar($fila);
        }
        return $tareas;
    }

    public function obtenerPorId($id)
    {
        // Misma consulta base pero filtrada por ID
        $sql = "SELECT t.*, 
                       te.estado_id as te_estado_id, te.estado_nombre as te_estado_nombre,
                       tp.prioridad_id as tp_prioridad_id, tp.prioridad_nombre as tp_prioridad_nombre, tp.prioridad_valor as tp_prioridad_valor,
                       tc.categoria_id as tc_categoria_id, tc.categoria_nombre as tc_categoria_nombre
                FROM tareas t
                INNER JOIN tarea_estados te ON t.estado_id = te.estado_id
                INNER JOIN tarea_prioridades tp ON t.prioridad_id = tp.prioridad_id
                LEFT JOIN tarea_categorias tc ON t.categoria_id = tc.categoria_id
                WHERE t.tarea_id = :id AND t.fecha_eliminacion IS NULL LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hidratar($data) : null;
    }

    public function crear(Tarea $tarea)
    {
        $sql = "INSERT INTO tareas (tarea_titulo, tarea_descripcion, fecha_limite, prioridad_id, estado_id, proyecto_id, categoria_id, usuario_asignado, usuario_creador) 
                VALUES (:titulo, :desc, :limite, :prioridad, :estado, :proyecto, :categoria, :asignado, :creador)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':titulo', $tarea->tarea_titulo);
        $stmt->bindParam(':desc', $tarea->tarea_descripcion);
        $stmt->bindParam(':limite', $tarea->fecha_limite);
        $stmt->bindParam(':prioridad', $tarea->prioridad_id);
        $stmt->bindParam(':estado', $tarea->estado_id);
        $stmt->bindParam(':proyecto', $tarea->proyecto_id);
        $stmt->bindParam(':categoria', $tarea->categoria_id);
        $stmt->bindParam(':asignado', $tarea->usuario_asignado_id);
        $stmt->bindParam(':creador', $tarea->usuario_creador_id);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizar(Tarea $tarea)
    {
        $sql = "UPDATE tareas SET 
                tarea_titulo = :titulo, 
                tarea_descripcion = :desc,
                fecha_limite = :limite,
                prioridad_id = :prioridad,
                estado_id = :estado,
                categoria_id = :categoria,
                usuario_asignado = :asignado
                WHERE tarea_id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':titulo', $tarea->tarea_titulo);
        $stmt->bindParam(':desc', $tarea->tarea_descripcion);
        $stmt->bindParam(':limite', $tarea->fecha_limite);
        $stmt->bindParam(':prioridad', $tarea->prioridad_id);
        $stmt->bindParam(':estado', $tarea->estado_id);
        $stmt->bindParam(':categoria', $tarea->categoria_id);
        $stmt->bindParam(':asignado', $tarea->usuario_asignado_id);
        $stmt->bindParam(':id', $tarea->tarea_id);

        return $stmt->execute();
    }

    public function eliminar($id)
    {
        // Soft Delete
        $sql = "UPDATE tareas SET fecha_eliminacion = NOW() WHERE tarea_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}