<?php
/**
 * Punto de entrada: Procesamiento de Acciones de Entregables (Subida de archivos y creación) - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/EntregableController.php';

$controller = new EntregableController();
$controller->procesarAccion();
