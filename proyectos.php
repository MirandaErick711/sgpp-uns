<?php
/**
 * Punto de entrada: Catálogo y Listado de Proyectos - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/controllers/ProyectoController.php';

$controller = new ProyectoController();
$controller->listar();
