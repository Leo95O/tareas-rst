<?php

namespace App\Validators;

use Exception;

class ProyectoValidator
{
    public static function validarCreacion($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos.");
        }

        if (empty($datos['nombre'])) {
            throw new Exception("El nombre del proyecto es obligatorio.");
        }

        if (empty($datos['sucursal_id'])) {
            throw new Exception("La sucursal es obligatoria.");
        }
        self::validarFechas($datos);
    }

    public static function validarEdicion($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos para editar.");
        }
        self::validarFechas($datos);
    }
    private static function validarFechas($datos)
    {
        // Solo valida si el usuario envió ambas fechas explícitamente
        if (!empty($datos['fecha_inicio']) && !empty($datos['fecha_fin'])) {
            $inicio = strtotime($datos['fecha_inicio']);
            $fin = strtotime($datos['fecha_fin']);

            if ($inicio > $fin) {
                throw new Exception("La fecha de inicio no puede ser posterior a la fecha de fin.");
            }
        }
    }
}
