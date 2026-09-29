<?php
/**
 * Cierre de Sesión Seguro - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/models/Auditoria.php';

if (estaAutenticado()) {
    $uid   = (int)($_SESSION['usuario_id'] ?? 0);
    $login = $_SESSION['usuario_login'] ?? 'usuario';
    $rol   = $_SESSION['usuario_rol'] ?? 'usuario';

    Auditoria::registrar(
        $uid,
        $login,
        $rol,
        'Cierre de sesión',
        'El usuario finalizó su sesión correctamente.',
        'Correcto'
    );
}

// Vaciar sesión
$_SESSION = [];

// Invalidar cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Iniciar nueva sesión sólo para el mensaje flash
session_start();
$_SESSION['flash_info'] = 'Ha cerrado su sesión de forma segura.';
header("Location: login.php");
exit;
