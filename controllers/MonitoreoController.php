<?php
/**
 * Controlador de Monitoreo - SGPP-UNS
 * Supervisión del estado del sistema, salud de base de datos, métricas y trazabilidad de seguridad
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Archivo.php';

class MonitoreoController {

    /**
     * Muestra el panel de salud arquitectónica y bitácora de auditoría
     */
    public function index(): void {
        requerirAutenticacion();

        $dbHealth = Database::checkHealth();
        $totalUsuarios = Usuario::contarTotal();
        $totalProyectos = (int)(Proyecto::resumenGlobal()['total_proyectos'] ?? 0);
        $statsArchivos = Archivo::estadisticasAlmacenamiento();

        $filtroResultado = $_GET['resultado'] ?? null;
        $eventosAuditoria = Auditoria::obtenerUltimos(60, $filtroResultado);

        $pageTitle = 'Monitoreo del Sistema y Arquitectura';
        require __DIR__ . '/../views/monitoreo/index.php';
    }
}
