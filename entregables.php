<?php
/**
 * Punto de entrada: Bandeja General de Entregables - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/EntregableController.php';

$controller = new EntregableController();
$controller->listar();
