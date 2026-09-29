<?php
/**
 * Punto de entrada: Consulta de Observaciones Estudiantiles - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ObservacionController.php';

$controller = new ObservacionController();
$controller->index();
