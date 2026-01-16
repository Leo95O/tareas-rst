<?php

namespace App\Validators;

use App\Exceptions\ValidationException;

class SucursalValidator
{
    public static function validar($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se recibieron datos para la sucursal.");
        }

        if (empty($datos['sucursal_nombre'])) {
            throw new ValidationException("El nombre de la sucursal es obligatorio.");
        }

        if (strlen($datos['sucursal_nombre']) < 3) {
            throw new ValidationException("El nombre de la sucursal debe tener al menos 3 caracteres.");
        }

        if (empty($datos['sucursal_direccion'])) {
            throw new ValidationException("La dirección de la sucursal es obligatoria.");
        }
    }
}