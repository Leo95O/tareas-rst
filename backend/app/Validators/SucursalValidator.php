<?php

namespace App\Validators;

use Exception;

class SucursalValidator
{
    public static function validar($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se recibieron datos.");
        }

        if (empty($datos['sucursal_nombre'])) {
            throw new Exception("El nombre de la sucursal es obligatorio.");
        }

        if (empty($datos['sucursal_direccion'])) {
            throw new Exception("La dirección de la sucursal es obligatoria.");
        }
    }
}