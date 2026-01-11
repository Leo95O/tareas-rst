<?php

namespace App\Utils;

use Exception;

class Crypto
{
    private static $method = 'AES-256-CBC';
    private static $secret_key;

    // Obtiene la clave de encriptación y asegura que tenga 32 bytes (SHA-256)
    private static function getKey()
    {
        if (!empty(self::$secret_key)) {
            return self::$secret_key;
        }

        // 1. Intentar obtener de variables de entorno del servidor
        $secret = $_ENV['APP_SECRET_KEY'] ?? $_SERVER['APP_SECRET_KEY'] ?? getenv('APP_SECRET_KEY') ?: '';

        // 2. Si no está disponible, leer directamente del archivo .env (Fallback manual)
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

        // Generamos un hash binario de 32 bytes exactos.
        // Esto asegura compatibilidad con AES-256 sin importar el largo de tu contraseña.
        self::$secret_key = hash('sha256', $secret, true);
        return self::$secret_key;
    }

    public static function encriptar($texto)
    {
        if (empty($texto))
            return null;

        // 1. Definir tamaño del vector
        $ivSize = openssl_cipher_iv_length(self::$method);

        // 2. Generar un IV nuevo y aleatorio (Usamos random_bytes por ser más moderno/seguro)
        // Le llamamos $ivGenerado para entender que NACE aquí.
        $ivGenerado = random_bytes($ivSize);

        // 3. Encriptar el texto
        // Usamos OPENSSL_RAW_DATA para obtener binario puro (sin base64 intermedio)
        $encryptedRaw = openssl_encrypt(
            $texto, 
            self::$method, 
            self::getKey(), 
            OPENSSL_RAW_DATA, 
            $ivGenerado
        );

        // 4. Empaquetar: Concatenamos el IV (binario) + Mensaje (binario)
        // Y convertimos todo a Base64 una sola vez para el transporte.
        return base64_encode($ivGenerado . $encryptedRaw);
    }

    public static function desencriptar($textoCifrado)
    {
        if (empty($textoCifrado))
            return null;

        // 1. Decodificar de Base64 a binario (El paquete completo)
        $data = base64_decode($textoCifrado, true);

        // Validación: ¿Es un dato válido y tiene al menos el tamaño del IV?
        $ivSize = openssl_cipher_iv_length(self::$method);
        if ($data === false || strlen($data) < $ivSize) {
            return $textoCifrado; // Retornamos original si no parece estar encriptado
        }

        // 2. Separar los ingredientes
        
        // Extraemos el IV del inicio del paquete
        $ivExtraido = substr($data, 0, $ivSize);

        // Extraemos el mensaje cifrado (lo que queda después del IV)
        $encryptedTextBinario = substr($data, $ivSize);

        // 3. Desencriptar
        // IMPORTANTE: Usamos OPENSSL_RAW_DATA porque $encryptedTextBinario es binario puro
        $decrypted = openssl_decrypt(
            $encryptedTextBinario, 
            self::$method, 
            self::getKey(), 
            OPENSSL_RAW_DATA, 
            $ivExtraido
        );

        // Si falla la desencriptación, devolvemos el texto cifrado original
        return $decrypted !== false ? $decrypted : $textoCifrado;
    }
}