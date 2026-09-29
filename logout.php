<?php
/**
 * Punto de entrada: Cierre de Sesión Seguro - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();
