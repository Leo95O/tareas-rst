<?php

namespace App\Validators;

use Exception;

class TareaValidator
{
    public static function validarCreacion($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos.");
        }

        if (empty($datos['titulo'])) {
            throw new Exception("El título de la tarea es obligatorio.");
        }

        if (empty($datos['proyecto_id'])) {
            throw new Exception("Debes asignar la tarea a un proyecto.");
        }

        if (empty($datos['fecha_limite'])) {
            throw new Exception("La fecha límite es obligatoria.");
        }
    }

    public static function validarEdicion($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos para editar.");
        }
    }
}
