<?php
/**
 * Punto de entrada: Registro de Nuevo Proyecto Académico - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ProyectoController.php';

$controller = new ProyectoController();
$controller->crear();
