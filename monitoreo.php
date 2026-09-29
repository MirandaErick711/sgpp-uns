<?php
/**
 * Punto de entrada: Monitoreo y Auditoría del Sistema - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/MonitoreoController.php';

$controller = new MonitoreoController();
$controller->index();
