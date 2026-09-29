<?php
/**
 * Controlador de Reportes - SGPP-UNS
 * Gestiona los reportes consolidados y estadísticas para comités y autoridades
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Usuario.php';

class ReporteController {

    /**
     * Reporte consolidado de proyectos con filtros e impresión oficial
     */
    public function index(): void {
        requerirAutenticacion();

        // Permitido para coordinador, autoridad y docente
        $rol = obtenerRolActual();
        if (!in_array($rol, ['coordinador', 'autoridad', 'docente'])) {
            http_response_code(403);
            require __DIR__ . '/../views/error/403.php';
            exit;
        }

        $filtroEstado = $_GET['estado'] ?? null;
        $filtroEstudiante = isset($_GET['estudiante']) && $_GET['estudiante'] !== '' ? (int)$_GET['estudiante'] : null;
        $filtroFecha = $_GET['fecha'] ?? null;

        $proyectos = Proyecto::obtenerTodos($filtroEstado, $filtroEstudiante, $filtroFecha);
        $estudiantes = Usuario::obtenerPorRol('estudiante');
        $resumen = Proyecto::resumenGlobal();

        $pageTitle = 'Reporte de Avance de Proyectos';
        require __DIR__ . '/../views/reportes/index.php';
    }
}
