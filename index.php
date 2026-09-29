<?php
/**
 * Punto de Entrada Principal y Enrutador de Dashboards - SGPP-UNS
 * Universidad Nacional del Santa
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

$rol = obtenerRolActual();

switch ($rol) {
    case 'estudiante':
        require __DIR__ . '/views/estudiante/dashboard.php';
        break;

    case 'docente':
        require __DIR__ . '/views/docente/dashboard.php';
        break;

    case 'coordinador':
        require __DIR__ . '/views/coordinador/dashboard.php';
        break;

    case 'autoridad':
        require __DIR__ . '/views/autoridad/dashboard.php';
        break;

    default:
        // Si no tiene rol válido
        setFlash('error', 'Rol de usuario desconocido. Por favor ingrese nuevamente.');
        header("Location: logout.php");
        exit;
}
