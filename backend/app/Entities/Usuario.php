<?php

namespace App\Entities;

class Usuario
{
    public $usuario_id;
    public $usuario_nombre; 
    public $usuario_correo;
    public $usuario_password; // Normalmente esto no se debería serializar, pero lo mantenemos por tu estructura
    public $usuario_token;
    
    public $rol_id;         // La clave foránea (útil para lógica rápida)
    
    /** @var Rol|null */
    public $rol;            // ¡LA FORMA CORRECTA! Objeto completo, no un string suelto.
    
    public $usuario_estado;
    public $fecha_creacion;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            // Asignación dinámica básica
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'rol') {
                    $this->$key = $value;
                }
            }
        }
    }

    // Método helper para inyectar el objeto Rol
    public function setRol(Rol $rol)
    {
        $this->rol = $rol;
        // Aseguramos consistencia
        if ($rol->rol_id) {
            $this->rol_id = $rol->rol_id;
        }
    }

    public function estaActivo()
    {
        return $this->usuario_estado == 1;
    }

    public function esAdmin()
    {
        // Validación robusta: mira el ID o el Objeto
        return $this->rol_id == 1 || ($this->rol && $this->rol->rol_id == 1);
    }

    public function toArray()
    {
        // Aquí decidimos cómo se ve el JSON para el Frontend.
        // Podemos "aplanar" el nombre del rol aquí para facilitar la vida al frontend
        // sin ensuciar la estructura interna de la clase.
        return [
            'usuario_id'      => $this->usuario_id,
            'usuario_nombre'  => $this->usuario_nombre,
            'usuario_correo'  => $this->usuario_correo,
            'rol_id'          => $this->rol_id,
            'rol_nombre'      => $this->rol ? $this->rol->rol_nombre : null, // Extraemos del objeto hijo
            'usuario_estado'  => $this->usuario_estado,
            'fecha_creacion'  => $this->fecha_creacion
        ];
    }
}