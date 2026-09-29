<?php
/**
 * Punto de entrada: Inicio de Sesión - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/AuthController.php';

$controller = new AuthController();
$controller->login();
