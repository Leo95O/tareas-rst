<?php

namespace App\Utils;

use \Firebase\JWT\JWT;

class Auth
{
    private static $secret_key;
    private static $encrypt = ['HS256'];

    private static function getSecretKey()
    {
        if (!empty(self::$secret_key)) {
            return self::$secret_key;
        }

        // Intentar obtener de variables de entorno
        self::$secret_key = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '';

        // Si no está disponible, leer directamente del archivo .env
        if (empty(self::$secret_key)) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), 'JWT_SECRET=') === 0) {
                        self::$secret_key = trim(substr($line, 11));
                        break;
                    }
                }
            }
        }

        if (empty(self::$secret_key)) {
            throw new \Exception('JWT_SECRET no está configurada');
        }

        return self::$secret_key;
    }

    public static function generarToken($usuario)
    {
        $secretKey = self::getSecretKey();

        $ahora = time();
        $vence = $ahora + (60 * 60 * 24);

        $payload = [
            'creacion' => $ahora,
            'expiracion' => $vence,
            'data' => [
                'id' => $usuario->usuario_id,
                'nombre' => $usuario->usuario_nombre,
                'correo' => $usuario->usuario_correo,
                'rol' => $usuario->rol_id
            ]
        ];

        return JWT::encode($payload, $secretKey);
    }

    public static function verificarToken($token)
    {
        $secretKey = self::getSecretKey();
        return JWT::decode($token, $secretKey, self::$encrypt);
    }
}