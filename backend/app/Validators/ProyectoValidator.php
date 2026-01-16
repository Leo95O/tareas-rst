<?php

namespace App\Validators;

use App\Exceptions\ValidationException;

class ProyectoValidator
{
    public static function validarCreacion($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se enviaron datos para crear el proyecto.");
        }

        if (empty($datos['nombre'])) {
            throw new ValidationException("El nombre del proyecto es obligatorio.");
        }

        if (strlen($datos['nombre']) < 5) {
            throw new ValidationException("El nombre del proyecto debe tener al menos 5 caracteres.");
        }

        if (empty($datos['sucursal_id'])) {
            throw new ValidationException("La sucursal es obligatoria para asignar el proyecto.");
        }

        self::validarFechas($datos);
    }

    public static function validarEdicion($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se enviaron datos para editar el proyecto.");
        }

        if (isset($datos['nombre']) && strlen($datos['nombre']) < 5) {
            throw new ValidationException("El nombre del proyecto es demasiado corto.");
        }

        self::validarFechas($datos);
    }

    private static function validarFechas($datos)
    {
        if (!empty($datos['fecha_inicio']) && !empty($datos['fecha_fin'])) {
            $inicio = strtotime($datos['fecha_inicio']);
            $fin = strtotime($datos['fecha_fin']);

            if (!$inicio || !$fin) {
                throw new ValidationException("El formato de las fechas no es válido.");
            }

            if ($inicio > $fin) {
                throw new ValidationException("La fecha de inicio no puede ser posterior a la fecha de finalización.");
            }
        }
    }
}