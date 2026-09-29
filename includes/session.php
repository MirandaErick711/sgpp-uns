<?php
/**
 * Gestión de Sesiones y Control de Autorización - SGPP-UNS
 * Universidad Nacional del Santa
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escapa cadenas para prevenir ataques Cross-Site Scripting (XSS)
 */
function e(?string $cadena): string {
    return htmlspecialchars($cadena ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Comprueba si el usuario tiene una sesión activa
 */
function estaAutenticado(): bool {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Obtiene el rol del usuario autenticado
 */
function obtenerRolActual(): ?string {
    return $_SESSION['usuario_rol'] ?? null;
}

/**
 * Verifica si el usuario actual tiene un rol específico
 */
function esRol(string $rol): bool {
    return (obtenerRolActual() === $rol);
}

/**
 * Exige autenticación; si no está autenticado, redirige a login.php
 */
function requerirAutenticacion(string $rutaLogin = 'login.php'): void {
    if (!estaAutenticado()) {
        $_SESSION['flash_error'] = 'Debe iniciar sesión para acceder al sistema institucional.';
        header("Location: {$rutaLogin}");
        exit;
    }
}

/**
 * Exige uno de los roles permitidos; si no cuenta con el rol, deniega acceso
 */
function requerirRol(string|array $rolesPermitidos): void {
    requerirAutenticacion();
    
    $roles = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];
    $rolActual = obtenerRolActual();

    if (!in_array($rolActual, $roles)) {
        http_response_code(403);
        include __DIR__ . '/../views/error/403.php';
        exit;
    }
}

/**
 * Establece un mensaje flash en la sesión
 */
function setFlash(string $tipo, string $mensaje): void {
    $_SESSION["flash_{$tipo}"] = $mensaje;
}

/**
 * Obtiene y elimina un mensaje flash de la sesión
 */
function getFlash(string $tipo): ?string {
    $clave = "flash_{$tipo}";
    if (isset($_SESSION[$clave])) {
        $msg = $_SESSION[$clave];
        unset($_SESSION[$clave]);
        return $msg;
    }
    return null;
}

/**
 * Genera o recupera un token CSRF
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida el token CSRF recibido por POST
 */
function validar_csrf(): bool {
    $tokenPost = $_POST['csrf_token'] ?? '';
    return !empty($tokenPost) && hash_equals($_SESSION['csrf_token'] ?? '', $tokenPost);
}

/**
 * Helper para badge de estado visual institucional
 */
function badgeEstado(string $estado): string {
    $slug = match ($estado) {
        'Pendiente'   => 'pendiente',
        'En revisión' => 'en-revision',
        'Observado'   => 'observado',
        'Aprobado'    => 'aprobado',
        'Rechazado'   => 'rechazado',
        default       => 'pendiente'
    };

    $icon = match ($estado) {
        'Pendiente'   => 'bi-hourglass-split',
        'En revisión' => 'bi-arrow-repeat',
        'Observado'   => 'bi-exclamation-triangle-fill',
        'Aprobado'    => 'bi-check-circle-fill',
        'Rechazado'   => 'bi-x-circle-fill',
        default       => 'bi-circle'
    };

    return sprintf(
        '<span class="badge-estado %s"><i class="bi %s"></i> %s</span>',
        $slug,
        $icon,
        htmlspecialchars($estado)
    );
}
