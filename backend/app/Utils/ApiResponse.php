<?php

namespace App\Utils;

class ApiResponse
{
    // Envía una respuesta JSON estandarizada y detiene la ejecución
    public static function enviar($tipo, $mensajes = [], $data = [], $httpCode = 200)
    {
        // Fuerza el formato de arreglo para los mensajes
        if (!is_array($mensajes)) {
            $mensajes = [$mensajes];
        }

        // Convierte data null en arreglo vacío
        if ($data === null) {
            $data = [];
        }

        // Estructura base de la respuesta
        $response = [
            'tipo' => (int) $tipo,
            'mensajes' => $mensajes,
            'data' => $data
        ];

        // Limpia el buffer de salida si existe
        if (ob_get_length()) {
            ob_clean();
        }

        // Envía encabezados y respuesta JSON
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Envía una respuesta de éxito estándar
    public static function exito($mensajes, $data = [])
    {
        self::enviar(1, $mensajes, $data, 200);
    }

    // Envía una respuesta de advertencia
    public static function alerta($mensajes, $data = [])
    {
        self::enviar(2, $mensajes, $data, 400);
    }

    // Envía una respuesta de error
    public static function error($mensajes, $data = [], $httpCode = 500)
    {
        self::enviar(3, $mensajes, $data, $httpCode);
    }
}
