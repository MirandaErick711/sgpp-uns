<?php
/**
 * Header común del sistema SGPP-UNS
 */
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}
require_once __DIR__ . '/session.php';
$paginaActual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>SGPP-UNS (Universidad Nacional del Santa)</title>
    <!-- Favicon institucional -->
    <link rel="icon" type="image/svg+xml" href="assets/img/logo_uns.svg">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos Institucionales UNS -->
    <link rel="stylesheet" href="assets/css/uns-theme.css">
</head>
<body>
<div class="app-wrapper">
