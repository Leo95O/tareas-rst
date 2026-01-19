<?php

namespace App\Utils;

use \Firebase\JWT\JWT;

class Auth
{
    private static $secret_key;
    private static $encrypt = ['HS256'];
// En Auth.php y Crypto.php, simplificar a:
    private static function getSecretKey() {
       if (!empty(self::$secret_key)) {
        return self::$secret_key;
       }
        

    // Solo usar getenv o $_ENV
    self::$secret_key = getenv('JWT_SECRET') ?: $_ENV['JWT_SECRET'] ?: '';
    
    if (empty(self::$secret_key)) {
        throw new \Exception('Configuración faltante en el entorno.');
    }
    return self::$secret_key;
}


public static function generarToken($usuario)
    {
        $secretKey = self::getSecretKey();

        $ahora = time();
        $vence = $ahora + (60 * 60 * 24); 

        $payload = [

            'iat' => $ahora,               
            'exp' => $vence,               
            'sub' => $usuario->usuario_id, 
 
            
            'data' => [

                'nombre' => $usuario->usuario_nombre,
                'correo' => $usuario->usuario_correo,
                'rol'    => $usuario->rol_id
            ]
        ];

        return JWT::encode($payload, $secretKey);
    }

    // En Auth.php
public static function verificarToken($token) {
    try {
        $secretKey = self::getSecretKey();
        return JWT::decode($token, $secretKey, self::$encrypt);
    } catch (\Firebase\JWT\ExpiredException $e) {
        throw new \Exception("El token ha expirado.");
    } catch (\Exception $e) {
        throw new \Exception("Token inválido o corrupto.");
    }
}
}
