<?php
/**
 * Punto de entrada: Reportes Consolidados de Proyectos - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ReporteController.php';

$controller = new ReporteController();
$controller->index();
