<?php

namespace App\Services;

use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Interfaces\LoginGuard\LoginGuardRepositoryInterface;
use Exception;
use DateTime;

class LoginGuardService implements LoginGuardServiceInterface
{
    private $repository;

    public function __construct(LoginGuardRepositoryInterface $repo)
    {
        $this->repository = $repo;
    }

    // Verifica si el usuario tiene permitido intentar iniciar sesión
    public function verificarSiPuedeEntrar($correo)
    {
        $estado = $this->repository->obtenerEstado($correo);

        if ($estado) {
            // Comprueba si existe un bloqueo permanente
            if ($estado['nivel_bloqueo'] >= 3) {
                throw new Exception("Tu cuenta ha sido bloqueada permanentemente. Contacta al soporte.");
            }

            // Comprueba si existe un bloqueo temporal activo
            if (!empty($estado['bloqueado_hasta'])) {
                $ahora = new DateTime();
                $hasta = new DateTime($estado['bloqueado_hasta']);

                if ($ahora < $hasta) {
                    $diff = $hasta->diff($ahora);
                    throw new Exception("Cuenta bloqueada temporalmente. Espera {$diff->i} min y {$diff->s} seg.");
                }
            }
        }

        return $estado;
    }

    // Gestiona la lógica de acumulación de intentos fallidos y escalado de bloqueos
    public function procesarIntentoFallido($correo, $estadoActual)
    {
        $ahora = new DateTime();
        $intentos = 0;
        $nivel = 0;
        $ultimoIntento = null;

        if ($estadoActual) {
            $intentos = (int) $estadoActual['intentos_fallidos'];
            $nivel = (int) $estadoActual['nivel_bloqueo'];
            $ultimoIntento = $estadoActual['ultimo_intento'] ? new DateTime($estadoActual['ultimo_intento']) : null;
        }

        // Si pasaron más de 2 minutos desde el último error, se reinicia el contador de intentos
        if ($ultimoIntento) {
            $diffMinutos = ($ahora->getTimestamp() - $ultimoIntento->getTimestamp()) / 60;
            if ($diffMinutos > 2) {
                $intentos = 0;
            }
        }

        $intentos++;
        $bloqueadoHasta = null;

        // Cada 3 intentos consecutivos aumenta la severidad del bloqueo
        if ($intentos >= 3) {
            $nivel++;
            $intentos = 0;

            if ($nivel === 1) {
                $ahora->modify('+5 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel === 2) {
                $ahora->modify('+10 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel >= 3) {
                $bloqueadoHasta = null; // null indica bloqueo permanente en la lógica de negocio
            }
        }

        $this->repository->registrarFallo($correo, $intentos, $nivel, $bloqueadoHasta);
    }

    // Elimina el historial de bloqueos tras un inicio de sesión exitoso
    public function limpiarHistorial($correo)
    {
        $this->repository->limpiar($correo);
    }
}