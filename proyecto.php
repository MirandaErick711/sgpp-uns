<?php
/**
 * Punto de entrada: Detalle y Expediente del Proyecto - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ProyectoController.php';

$controller = new ProyectoController();
$controller->verDetalle((int)($_GET['id'] ?? 0));
