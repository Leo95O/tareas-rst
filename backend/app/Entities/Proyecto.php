<?php

namespace App\Entities;

use App\Constants\EstadosProyecto; // Sincronizado con el nombre de tu clase constante

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
    public $proyecto_estado; 
    
    /** @var EstadoProyecto|null */
    public $estado; 

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                // Mapeo especial para la columna 'estado_id' de la BD al nombre consistente en la entidad
                if ($key === 'estado_id') {
                    $this->proyecto_estado = $value !== null ? (int)$value : null;
                    continue;
                }

                if (property_exists($this, $key) && $key !== 'estado') {
                    // Casteo automático de IDs a enteros para comparaciones seguras
                    if (in_array($key, ['proyecto_id', 'sucursal_id', 'usuario_creador', 'proyecto_estado'])) {
                        $this->$key = $value !== null ? (int)$value : null;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    public function setEstado(EstadoProyecto $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->proyecto_estado = (int)$estado->estado_id;
        }
    }

    public function estaFinalizado()
    {
        $idEstado = $this->estado ? $this->estado->estado_id : $this->proyecto_estado;
        // Sincronizado con la constante real: 3 (Finalizado) según tu dump de base de datos
        return (int)$idEstado === EstadosProyecto::FINALIZADO;
    }

    public function toArray()
    {
        return [
            'id'             => $this->proyecto_id,
            'nombre'         => $this->proyecto_nombre,
            'descripcion'    => $this->proyecto_descripcion,
            'sucursal_id'    => $this->sucursal_id,
            'creador_id'     => $this->usuario_creador,
            'fecha_inicio'   => $this->fecha_inicio,
            'fecha_fin'      => $this->fecha_fin,
            'fecha_creacion' => $this->fecha_creacion,
            'estado_id'      => $this->proyecto_estado,
            'estado'         => $this->estado ? [
                'id'     => (int)$this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'orden'  => (int)$this->estado->estado_orden
            ] : null
        ];
    }
}