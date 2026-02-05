<?php

namespace App\Interfaces\Context;

interface ContextServiceInterface
{
    /**
     * Valida permisos y genera un nuevo token con la sucursal incrustada.
     *
     * @param int $usuarioId ID del usuario autenticado (del token actual)
     * @param int $sucursalId ID de la sucursal a la que se quiere cambiar
     * @return array Retorna ['token' => string, 'sucursal' => array]
     * @throws \App\Exceptions\ValidationException Si no tiene acceso o datos incorrectos
     */
    public function cambiarContexto($usuarioId, $sucursalId);
}