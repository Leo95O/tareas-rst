<?php

namespace App\Validators;

use App\Exceptions\ValidationException;

class TareaValidator
{
    public static function validarCreacion($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se recibieron datos para crear la tarea.");
        }

        if (empty($datos['titulo'])) {
            throw new ValidationException("El título de la tarea es obligatorio.");
        }

        if (strlen($datos['titulo']) < 3) {
            throw new ValidationException("El título de la tarea debe tener al menos 3 caracteres.");
        }

        if (empty($datos['proyecto_id'])) {
            throw new ValidationException("La tarea debe estar vinculada a un proyecto.");
        }

        if (empty($datos['prioridad_id'])) {
            throw new ValidationException("Debes asignar una prioridad a la tarea.");
        }

        if (empty($datos['fecha_limite'])) {
            throw new ValidationException("La fecha límite es obligatoria.");
        }

        self::validarFechaFormato($datos['fecha_limite']);
    }

    public static function validarEdicion($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No hay datos para actualizar en la tarea.");
        }

        if (isset($datos['titulo']) && strlen($datos['titulo']) < 3) {
            throw new ValidationException("El nuevo título es demasiado corto.");
        }

        if (isset($datos['fecha_limite'])) {
            self::validarFechaFormato($datos['fecha_limite']);
        }
    }

    private static function validarFechaFormato($fecha)
    {
        $timestamp = strtotime($fecha);
        if (!$timestamp) {
            throw new ValidationException("El formato de la fecha límite no es válido.");
        }

        if ($timestamp < strtotime('today')) {
            throw new ValidationException("La fecha límite no puede ser anterior a hoy.");
        }
    }
}