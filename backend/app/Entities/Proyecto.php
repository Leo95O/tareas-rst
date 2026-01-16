<?php

namespace App\Entities;

use App\Constans\EstadosProyecto;

class Proyecto
{
    public $proyecto_id;
    public $proyecto_nombre;
    public $proyecto_descripcion;
    public $sucursal_id;
    public $usuario_creador;
    public $fecha_inicio;
    public $fecha_fin;
    public $fecha_creacion;
    
    // Relación de Estado
    public $proyecto_estado; // ID plano (columna 'estado_id' en BD, pero lo mapeamos aquí)
    
    /** @var EstadoProyecto|null */
    public $estado; // Objeto hidratado

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->proyecto_id = isset($data['proyecto_id']) ? $data['proyecto_id'] : null;
            $this->proyecto_nombre = isset($data['proyecto_nombre']) ? $data['proyecto_nombre'] : null;
            $this->proyecto_descripcion = isset($data['proyecto_descripcion']) ? $data['proyecto_descripcion'] : null;
            $this->sucursal_id = isset($data['sucursal_id']) ? $data['sucursal_id'] : null;
            $this->usuario_creador = isset($data['usuario_creador']) ? $data['usuario_creador'] : null;
            $this->fecha_inicio = isset($data['fecha_inicio']) ? $data['fecha_inicio'] : null;
            $this->fecha_fin = isset($data['fecha_fin']) ? $data['fecha_fin'] : null;
            $this->fecha_creacion = isset($data['fecha_creacion']) ? $data['fecha_creacion'] : null;

            // Mapeo especial: En tu tabla se llama 'estado_id', pero en la clase lo llamamos 'proyecto_estado'
            // para ser consistentes con 'usuario_estado', 'sucursal_estado', etc.
            if (isset($data['estado_id'])) {
                $this->proyecto_estado = $data['estado_id'];
            } elseif (isset($data['proyecto_estado'])) {
                $this->proyecto_estado = $data['proyecto_estado'];
            }
        }
    }

    // Inyección de Dependencia
    public function setEstado(EstadoProyecto $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->proyecto_estado = $estado->estado_id;
        }
    }

    public function estaFinalizado()
    {
        if ($this->estado) {
            return $this->estado->estado_id === EstadosProyecto::FINALIZADO;
        }
        return (int)$this->proyecto_estado === EstadosProyecto::FINALIZADO;
    }

    public function toArray()
    {
        return [
            'id'          => $this->proyecto_id,
            'nombre'      => $this->proyecto_nombre,
            'descripcion' => $this->proyecto_descripcion,
            'sucursal_id' => $this->sucursal_id,
            'creador_id'  => $this->usuario_creador,
            'fecha_inicio'=> $this->fecha_inicio,
            'fecha_fin'   => $this->fecha_fin,
            'fecha_creacion' => $this->fecha_creacion,
            
            // Estado enriquecido
            'estado_id'   => $this->proyecto_estado,
            'estado'      => $this->estado ? [
                'id'     => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'orden'  => $this->estado->estado_orden
            ] : null
        ];
    }
}