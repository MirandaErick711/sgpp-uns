<?php
/**
 * Controlador de Proyectos - SGPP-UNS
 * Gestiona el ciclo de vida de los proyectos y las reglas de seguridad DR-01
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Entregable.php';
require_once __DIR__ . '/../models/Archivo.php';
require_once __DIR__ . '/../models/Observacion.php';

class ProyectoController {

    /**
     * Lista los proyectos (personales para estudiante, generales con filtros para docentes/coordinadores/autoridades)
     */
    public function listar(): void {
        requerirAutenticacion();

        $rol = obtenerRolActual();
        $usuarioId = (int)$_SESSION['usuario_id'];

        $filtroEstado = $_GET['estado'] ?? null;
        $filtroEstudiante = isset($_GET['estudiante']) && $_GET['estudiante'] !== '' ? (int)$_GET['estudiante'] : null;
        $filtroFecha = $_GET['fecha'] ?? null;

        if ($rol === 'estudiante') {
            // Aislamiento DR-01: Estudiante solo consulta sus propios proyectos
            $proyectos = Proyecto::obtenerPorEstudiante($usuarioId);
            $listaEstudiantes = [];
        } else {
            // Docentes, coordinadores y autoridades consultan todos los proyectos
            $proyectos = Proyecto::obtenerTodos($filtroEstado, $filtroEstudiante, $filtroFecha);
            $listaEstudiantes = Usuario::obtenerPorRol('estudiante');
        }

        $pageTitle = ($rol === 'estudiante') ? 'Mis Proyectos Académicos' : 'Proyectos Académicos UNS';
        require __DIR__ . '/../views/proyectos/index.php';
    }

    /**
     * Muestra el detalle del proyecto con estricta validación del Driver DR-01
     */
    public function verDetalle(int $proyectoId = 0): void {
        requerirAutenticacion();

        if ($proyectoId <= 0) {
            $proyectoId = (int)($_GET['id'] ?? 0);
        }

        if ($proyectoId <= 0) {
            setFlash('error', 'Identificador de proyecto no válido.');
            header("Location: index.php");
            exit;
        }

        $error_dr01 = null;
        $usuarioId  = (int)$_SESSION['usuario_id'];
        $rolUsuario = (string)$_SESSION['usuario_rol'];

        // Comprobación crítica del Driver DR-01 en el servidor
        $proyecto = Proyecto::obtenerPorIdConSeguridad($proyectoId, $usuarioId, $rolUsuario, $error_dr01);

        if (!$proyecto) {
            if ($error_dr01 === 'ACCESO_NO_AUTORIZADO' || $error_dr01 === 'ACCESO_NO_AUTORIZADO_DR01') {
                http_response_code(403);
                require __DIR__ . '/../views/error/403.php';
                exit;
            } else {
                setFlash('error', $error_dr01 ?? 'El proyecto solicitado no existe.');
                header("Location: index.php");
                exit;
            }
        }

        $entregables = Entregable::obtenerPorProyecto($proyectoId);
        $esPropietario = ($rolUsuario === 'estudiante' && (int)$proyecto['estudiante_id'] === $usuarioId);
        $esDocente = ($rolUsuario === 'docente');

        $pageTitle = 'Proyecto: ' . $proyecto['codigo_proyecto'];
        require __DIR__ . '/../views/proyectos/detalle.php';
    }

    /**
     * Muestra formulario y procesa la creación de un nuevo proyecto
     */
    public function crear(): void {
        requerirRol('estudiante');

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validar_csrf()) {
                $error = 'Token de seguridad inválido o expirado. Inténtelo de nuevo.';
            } else {
                $titulo       = trim($_POST['titulo'] ?? '');
                $descripcion  = trim($_POST['descripcion'] ?? '');
                $linea        = trim($_POST['linea_investigacion'] ?? '');
                $fechaInicio  = trim($_POST['fecha_inicio'] ?? '');
                $fechaFin     = trim($_POST['fecha_fin_prevista'] ?? '');

                if (empty($titulo) || empty($descripcion) || empty($fechaInicio) || empty($fechaFin)) {
                    $error = 'Por favor complete todos los campos obligatorios (*).';
                } elseif (strtotime($fechaFin) < strtotime($fechaInicio)) {
                    $error = 'La fecha de finalización no puede ser anterior a la fecha de inicio.';
                } else {
                    $estudianteId = (int)$_SESSION['usuario_id'];
                    $usuarioLogin = $_SESSION['usuario_login'];

                    $res = Proyecto::crear([
                        'titulo'              => $titulo,
                        'descripcion'         => $descripcion,
                        'linea_investigacion' => $linea,
                        'fecha_inicio'        => $fechaInicio,
                        'fecha_fin_prevista'  => $fechaFin
                    ], $estudianteId, $usuarioLogin);

                    if ($res['ok']) {
                        setFlash('success', "¡Proyecto {$res['codigo']} registrado exitosamente en la base de datos! Ahora puede registrar su primer entregable.");
                        header("Location: proyecto.php?id=" . $res['id']);
                        exit;
                    } else {
                        $error = 'Error al registrar el proyecto: ' . ($res['error'] ?? 'Intente nuevamente.');
                    }
                }
            }
        }

        $pageTitle = 'Registrar Nuevo Proyecto';
        require __DIR__ . '/../views/proyectos/nuevo.php';
    }
}
