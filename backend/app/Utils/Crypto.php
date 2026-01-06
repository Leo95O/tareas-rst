<?php

namespace App\Utils;

use Exception;

class Crypto
{
    private static $method = 'AES-256-CBC';
    private static $secret_key;

    // Obtiene la clave de encriptación desde la variable de entorno
    private static function getKey()
    {
        if (!empty(self::$secret_key)) {
            return self::$secret_key;
        }

        // Intentar obtener de variables de entorno
        $secret = $_ENV['APP_SECRET_KEY'] ?? $_SERVER['APP_SECRET_KEY'] ?? getenv('APP_SECRET_KEY') ?: '';

        // Si no está disponible, leer directamente del archivo .env
        if (empty($secret)) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), 'APP_SECRET_KEY=') === 0) {
                        $secret = trim(substr($line, 15));
                        break;
                    }
                }
            }
        }

        if (empty($secret)) {
            throw new Exception('La variable APP_SECRET_KEY no está definida en el .env');
        }

        // Cachear el hash para no tener que calcularlo múltiples veces
        self::$secret_key = hash('sha256', $secret, true);
        return self::$secret_key;
    }

    public static function encriptar($texto)
    {
        if (empty($texto))
            return null;

        // 1. Generar un IV (Vector de Inicialización) aleatorio y seguro
        $ivSize = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivSize);

        // 2. Encriptar el texto usando la llave y el IV
        $encrypted = openssl_encrypt($texto, self::$method, self::getKey(), 0, $iv);

        // 3. Concatenamos el IV + El Texto Encriptado y lo convertimos a Base64
        // Guardamos el IV para poder desencriptar después.
        return base64_encode($iv . $encrypted);
    }

    public static function desencriptar($textoCifrado)
    {
        if (empty($textoCifrado))
            return null;

        // 1. Decodificar de Base64 a binario
        $data = base64_decode($textoCifrado, true);

        // Si falla la decodificación o el dato es muy corto, retornamos el texto original (asumimos que no estaba encriptado)
        $ivSize = openssl_cipher_iv_length(self::$method);
        if ($data === false || strlen($data) < $ivSize) {
            return $textoCifrado;
        }

        // 3. Extraer el IV (la primera parte del string)
        $iv = substr($data, 0, $ivSize);

        // 4. Extraer el mensaje cifrado real (el resto del string)
        $encryptedText = substr($data, $ivSize);

        // 5. Desencriptar
        $decrypted = openssl_decrypt($encryptedText, self::$method, self::getKey(), 0, $iv);

        // Si falla la desencriptación, devolvemos el original
        return $decrypted !== false ? $decrypted : $textoCifrado;
    }
}