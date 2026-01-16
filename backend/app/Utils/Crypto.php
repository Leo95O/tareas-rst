<?php

namespace App\Utils;

use Exception;

class Crypto
{
    private static $method = 'AES-256-CBC';
    private static $secret_key;

    private static function getKey()
    {
        if (!empty(self::$secret_key)) {
            return self::$secret_key;
        }

        $secret = getenv('APP_SECRET_KEY') ?: $_ENV['APP_SECRET_KEY'] ?: '';

        if (empty($secret)) {
            throw new Exception('La variable APP_SECRET_KEY no está definida en el entorno');
        }

        self::$secret_key = hash('sha256', $secret, true);
        return self::$secret_key;
    }

    public static function encriptar($texto)
    {
        if (empty($texto)) {
            return null;
        }

        $ivSize = openssl_cipher_iv_length(self::$method);
        $ivGenerado = random_bytes($ivSize);

        $encryptedRaw = openssl_encrypt(
            $texto, 
            self::$method, 
            self::getKey(), 
            OPENSSL_RAW_DATA, 
            $ivGenerado
        );

        return base64_encode($ivGenerado . $encryptedRaw);
    }

    public static function desencriptar($textoCifrado)
    {
        if (empty($textoCifrado)) {
            return null;
        }

        $data = base64_decode($textoCifrado, true);
        $ivSize = openssl_cipher_iv_length(self::$method);

        if ($data === false || strlen($data) < $ivSize) {
            return $textoCifrado;
        }

        $ivExtraido = substr($data, 0, $ivSize);
        $encryptedTextBinario = substr($data, $ivSize);

        $decrypted = openssl_decrypt(
            $encryptedTextBinario, 
            self::$method, 
            self::getKey(), 
            OPENSSL_RAW_DATA, 
            $ivExtraido
        );

        return $decrypted !== false ? $decrypted : $textoCifrado;
    }
}