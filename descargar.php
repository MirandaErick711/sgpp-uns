<?php
/**
 * Punto de entrada: Descarga Segura de Archivos (DR-01) - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ArchivoController.php';

$controller = new ArchivoController();
$controller->descargar((int)($_GET['id'] ?? 0));
