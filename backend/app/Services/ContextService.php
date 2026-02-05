<?php
namespace App\Services;

use App\Interfaces\Context\ContextServiceInterface;
use App\Interfaces\Context\ContextRepositoryInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface; // Para re-hidratar al usuario
use App\Utils\Auth;
use App\Exceptions\ValidationException;

class ContextService implements ContextServiceInterface
{
    private $contextRepo;
    private $usuarioRepo;

    public function __construct(
        ContextRepositoryInterface $contextRepo,
        UsuarioRepositoryInterface $usuarioRepo
    ) {
        $this->contextRepo = $contextRepo;
        $this->usuarioRepo = $usuarioRepo;
    }

    public function cambiarContexto($usuarioId, $sucursalId)
    {
        // 1. Validar que el usuario tenga permiso real en esa sucursal
        if (!$this->contextRepo->verificarAcceso($usuarioId, $sucursalId)) {
            throw new ValidationException("No tienes acceso autorizado a esta sucursal.");
        }

        // 2. Obtener datos frescos del usuario (para el token)
        $usuario = $this->usuarioRepo->obtenerPorId($usuarioId);
        if (!$usuario || !$usuario->estaActivo()) {
            throw new ValidationException("Usuario no válido o inactivo.");
        }

        // 3. Obtener datos de la sucursal (para el token)
        $sucursalData = $this->contextRepo->obtenerSucursalData($sucursalId);
        if (!$sucursalData) {
            throw new ValidationException("La sucursal solicitada no existe.");
        }

        // 4. Generar NUEVO Token con el contexto inyectado
        // Aquí usamos la mejora que hicimos en Auth::generarToken
        $nuevoToken = Auth::generarToken($usuario, $sucursalData);

        return [
            'token' => $nuevoToken,
            'sucursal' => $sucursalData
        ];
    }
}