<?php

namespace App\Validators;

use Exception;

class UsuarioValidator
{
    public static function validarRegistro($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se recibieron datos.");
        }

        if (empty($datos['usuario_nombre'])) {
            throw new Exception("El nombre es obligatorio.");
        }

        if (empty($datos['usuario_correo'])) {
            throw new Exception("El correo es obligatorio.");
        }

        if (!filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo no es válido.");
        }

        if (empty($datos['usuario_password'])) {
            throw new Exception("La contraseña es obligatoria.");
        }

        if (strlen($datos['usuario_password']) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres.");
        }
    }

    public static function validarLogin($datos)
    {
        if (empty($datos['usuario_correo'])) {
            throw new Exception("El correo es requerido.");
        }

        if (empty($datos['usuario_password'])) {
            throw new Exception("La contraseña es requerida.");
        }
    }

    public static function validarCreacionAdmin($datos)
    {
        self::validarRegistro($datos);

        if (empty($datos['rol_id'])) {
            throw new Exception("El rol es obligatorio.");
        }
    }

    public static function validarEdicionAdmin($datos)
    {
        if (empty($datos)) {
            throw new Exception("No se enviaron datos para editar.");
        }

        if (isset($datos['usuario_correo']) && !filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo no es válido.");
        }
    }
}
