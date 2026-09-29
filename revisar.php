<?php
/**
 * Punto de entrada: Evaluación Docente de Entregables - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/EntregableController.php';

$controller = new EntregableController();
$controller->revisar((int)($_GET['id'] ?? 0));
