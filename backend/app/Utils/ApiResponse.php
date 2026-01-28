<?php

namespace App\Utils;

class ApiResponse
{
    /**
     * NÚCLEO: Convierte a JSON.
     * Privado o público, depende de si quieres permitir tipos personalizados.
     */
    public static function enviar($tipo, $mensajes = [], $data = [])
    {
        if (!is_array($mensajes)) {
            $mensajes = [$mensajes];
        }

        $response = [
            'tipo' => (int) $tipo,
            'mensajes' => $mensajes,
            'data' => $data ?? []
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    // -------------------------------------------------------------------------
    // MÉTODOS DE EMPRESA (SEMÁNTICOS)
    // Estos métodos ENFUERZAN el estándar 1, 2, 3.
    // -------------------------------------------------------------------------

    /**
     * Tipo 1: Éxito
     * Retorna string JSON. NO hace echo. NO hace exit.
     */
    public static function exito($mensajes, $data = [])
    {
        return self::enviar(1, $mensajes, $data);
    }

    /**
     * Tipo 2: Advertencia / Alerta
     * Retorna string JSON.
     */
    public static function alerta($mensajes, $data = [])
    {
        return self::enviar(2, $mensajes, $data);
    }

    /**
     * Tipo 3: Error
     * Retorna string JSON.
     */
    public static function error($mensajes, $data = [])
    {
        return self::enviar(3, $mensajes, $data);
    }
}