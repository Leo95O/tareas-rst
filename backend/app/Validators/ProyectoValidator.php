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
    }

    public static function validarEdicion($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos para editar.");
        }
    }
}
