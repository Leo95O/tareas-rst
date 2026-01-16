<?php

namespace App\Validators;

use App\Exceptions\ValidationException;

class UsuarioValidator
{
    public static function validarRegistro($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se recibieron datos para el registro.");
        }

        if (empty($datos['usuario_nombre'])) {
            throw new ValidationException("El nombre del usuario es obligatorio.");
        }

        if (empty($datos['usuario_correo'])) {
            throw new ValidationException("El correo electrónico es obligatorio.");
        }

        if (!filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El formato del correo electrónico no es válido.");
        }

        if (empty($datos['usuario_password'])) {
            throw new ValidationException("La contraseña es obligatoria.");
        }

        if (strlen($datos['usuario_password']) < 6) {
            throw new ValidationException("La contraseña debe tener al menos 6 caracteres.");
        }
    }

    public static function validarLogin($datos)
    {
        if (empty($datos['usuario_correo'])) {
            throw new ValidationException("El correo electrónico es requerido para iniciar sesión.");
        }

        if (empty($datos['usuario_password'])) {
            throw new ValidationException("La contraseña es requerida para iniciar sesión.");
        }
    }

    public static function validarCreacionAdmin($datos)
    {
        self::validarRegistro($datos);

        if (empty($datos['rol_id'])) {
            throw new ValidationException("El rol es obligatorio para la creación administrativa.");
        }
    }

    public static function validarEdicionAdmin($datos)
    {
        if (empty($datos)) {
            throw new ValidationException("No se enviaron datos para realizar la edición.");
        }

        if (isset($datos['usuario_correo']) && !filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El formato del nuevo correo electrónico no es válido.");
        }
        
        if (isset($datos['usuario_password']) && !empty($datos['usuario_password']) && strlen($datos['usuario_password']) < 6) {
            throw new ValidationException("La nueva contraseña debe tener al menos 6 caracteres.");
        }
    }
}