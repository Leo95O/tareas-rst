<?php
// backend/debug_login.php

// 1. Cargar el entorno
require __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 2. Cargar el contenedor (Tu cerebro de dependencias)
$container = require __DIR__ . '/config/container.php';

echo "<h1>Prueba de Diagnóstico de Login</h1>";
echo "<pre>";

// --- DATOS DE PRUEBA (CAMBIA ESTO POR TUS DATOS REALES) ---
$correoPrueba = 'admin@test.com'; // Pon aquí el correo que estás intentando usar
$passPrueba   = '123456';         // Pon aquí la contraseña que intentas usar
// -----------------------------------------------------------

try {
    echo "[INFO] Iniciando prueba para: $correoPrueba\n";

    // 3. Probar Conexión a BD
    echo "[PASO 1] Probando conexión a Base de Datos... ";
    $pdo = $container->get(PDO::class);
    if ($pdo) {
        echo "OK (Conectado)\n";
    } else {
        die("FALLO: No se obtuvo instancia de PDO\n");
    }

    // 4. Probar Repositorio
    echo "[PASO 2] Buscando usuario en el Repositorio... ";
    $repo = $container->get(\App\Interfaces\Usuario\UsuarioRepositoryInterface::class);
    $usuario = $repo->obtenerPorCorreo($correoPrueba);

    if ($usuario) {
        echo "OK (Usuario encontrado: ID " . $usuario->usuario_id . ")\n";
        echo "       Hash en BD: " . $usuario->usuario_password . "\n";
    } else {
        die("FALLO: El usuario no existe en la base de datos.\n");
    }

    // 5. Probar Verificación de Password (Manual)
    echo "[PASO 3] Verificando contraseña manualmente... ";
    if (password_verify($passPrueba, $usuario->usuario_password)) {
        echo "OK (Contraseña Correcta)\n";
    } else {
        echo "FALLO: La contraseña no coincide con el hash.\n";
        echo "       Prueba generando un nuevo hash para '$passPrueba': " . password_hash($passPrueba, PASSWORD_BCRYPT) . "\n";
        die();
    }

    // 6. Probar Servicio Completo (Incluye LoginGuard)
    echo "[PASO 4] Probando el Servicio UsuarioService::loginUsuario... ";
    $servicio = $container->get(\App\Interfaces\Usuario\UsuarioServiceInterface::class);
    
    try {
        $resultado = $servicio->loginUsuario($correoPrueba, $passPrueba);
        echo "ÉXITO TOTAL: El servicio devolvió al usuario correctamente.\n";
        print_r($resultado->toArray());
    } catch (Exception $e) {
        echo "FALLO EN SERVICIO: " . $e->getMessage() . "\n";
    }

} catch (Exception $ex) {
    echo "ERROR CRÍTICO: " . $ex->getMessage() . "\n";
    echo $ex->getTraceAsString();
}

echo "</pre>";     