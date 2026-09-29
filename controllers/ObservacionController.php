<?php
/**
 * Controlador de Observaciones - SGPP-UNS
 * Gestiona la consulta de observaciones y dictámenes docentes
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Observacion.php';

class ObservacionController {

    /**
     * Lista las observaciones recibidas para el estudiante en sesión
     */
    public function index(): void {
        requerirAutenticacion();

        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol = obtenerRolActual();

        if ($rol === 'estudiante') {
            $observaciones = Observacion::obtenerPorEstudiante($usuarioId);
        } else {
            // Si es docente u otro rol, redirigir a inicio
            header("Location: index.php");
            exit;
        }

        $pageTitle = 'Observaciones de Mis Proyectos';
        require __DIR__ . '/../views/observaciones/index.php';
    }
}
