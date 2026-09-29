<?php
/**
 * Controlador de Entregables - SGPP-UNS
 * Gestiona el ciclo de vida, evaluación docente y validación de seguridad DR-01
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Proyecto.php';
require_once __DIR__ . '/../models/Entregable.php';
require_once __DIR__ . '/../models/Archivo.php';
require_once __DIR__ . '/../models/Observacion.php';
require_once __DIR__ . '/../models/Auditoria.php';

class EntregableController {

    /**
     * Bandeja general de entregables con filtros por estado
     */
    public function listar(): void {
        requerirAutenticacion();

        $filtroEstado = $_GET['estado'] ?? null;
        $entregables = Entregable::obtenerParaDocente($filtroEstado);

        $pageTitle = 'Bandeja de Entregables';
        require __DIR__ . '/../views/entregables/index.php';
    }

    /**
     * Formulario y procesamiento de la evaluación docente (Aprobar, Observar, Rechazar)
     */
    public function revisar(int $entregableId = 0): void {
        requerirRol('docente');

        if ($entregableId <= 0) {
            $entregableId = (int)($_GET['id'] ?? 0);
        }

        if ($entregableId <= 0) {
            setFlash('error', 'Identificador de entregable inválido.');
            header("Location: entregables.php");
            exit;
        }

        $entregable = Entregable::obtenerPorId($entregableId);
        if (!$entregable) {
            setFlash('error', 'El entregable no existe en el sistema.');
            header("Location: entregables.php");
            exit;
        }

        $error = null;

        // Procesar Formulario de Evaluación por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validar_csrf()) {
                $error = 'Token de seguridad inválido o expirado.';
            } else {
                $decision   = trim($_POST['decision'] ?? '');
                $comentario = trim($_POST['comentario'] ?? '');

                if (empty($decision) || !in_array($decision, ['Aprobado', 'Observado', 'Rechazado'])) {
                    $error = 'Debe seleccionar un dictamen de evaluación válido (Aprobar, Observar o Rechazar).';
                } elseif (empty($comentario)) {
                    $error = 'Debe ingresar una justificación o comentario para el dictamen del entregable.';
                } else {
                    $docenteId = (int)$_SESSION['usuario_id'];
                    $docenteLogin = (string)$_SESSION['usuario_login'];

                    $res = Entregable::evaluarPorDocente($entregableId, $docenteId, $decision, $comentario, $docenteLogin);

                    if ($res['ok']) {
                        setFlash('success', "¡Entregable evaluado con éxito! Dictamen '{$decision}' y observaciones registradas.");
                        header("Location: entregables.php");
                        exit;
                    } else {
                        $error = 'Error al registrar evaluación: ' . ($res['error'] ?? 'Intente nuevamente.');
                    }
                }
            }
        }

        $archivos = Archivo::obtenerPorEntregable($entregableId);
        $historialObs = Observacion::obtenerPorEntregable($entregableId);

        $pageTitle = 'Evaluar Entregable #' . $entregable['numero_entregable'];
        require __DIR__ . '/../views/entregables/revisar.php';
    }

    /**
     * Procesa la subida de un entregable o archivo adjunto validando el Driver DR-01
     */
    public function procesarAccion(): void {
        requerirAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php");
            exit;
        }

        if (!validar_csrf()) {
            setFlash('error', 'Token de seguridad inválido o expirado.');
            header("Location: index.php");
            exit;
        }

        $accion     = $_POST['accion'] ?? '';
        $proyectoId = (int)($_POST['proyecto_id'] ?? 0);
        $usuarioId  = (int)$_SESSION['usuario_id'];
        $rolUsuario = (string)$_SESSION['usuario_rol'];
        $userLogin  = (string)$_SESSION['usuario_login'];

        // ====================================================================
        // VALIDACIÓN ESTRICTA DEL DRIVER ARQUITECTÓNICO DR-01
        // Ningún estudiante puede agregar entregables ni subir archivos a proyectos ajenos
        // ====================================================================
        if ($rolUsuario === 'estudiante') {
            if (!Proyecto::perteneceAEstudiante($proyectoId, $usuarioId)) {
                Auditoria::registrar(
                    $usuarioId,
                    $userLogin,
                    $rolUsuario,
                    'Acceso no autorizado',
                    "Intento bloqueado: El estudiante intentó registrar un entregable/archivo en el proyecto ajeno ID #{$proyectoId}.",
                    'Rechazado'
                );
                http_response_code(403);
                require __DIR__ . '/../views/error/403.php';
                exit;
            }
        }

        // Acción: Crear un nuevo entregable y subir su archivo
        if ($accion === 'crear_entregable') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($titulo)) {
                setFlash('error', 'Debe indicar un título para el entregable.');
                header("Location: proyecto.php?id=" . $proyectoId);
                exit;
            }

            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                setFlash('error', 'Debe adjuntar un archivo válido para el entregable.');
                header("Location: proyecto.php?id=" . $proyectoId);
                exit;
            }

            // 1. Crear el entregable con transacción
            $resEntregable = Entregable::crear($proyectoId, $titulo, $descripcion, $usuarioId, $userLogin);
            if (!$resEntregable['ok']) {
                setFlash('error', 'Error al registrar entregable: ' . ($resEntregable['error'] ?? ''));
                header("Location: proyecto.php?id=" . $proyectoId);
                exit;
            }

            $entregableId = (int)$resEntregable['id'];

            // 2. Subir el archivo físico a uploads/ y guardar metadatos
            $resArchivo = Archivo::subir($entregableId, $usuarioId, $userLogin, $_FILES['archivo']);
            if (!$resArchivo['ok']) {
                setFlash('warning', "El entregable fue creado pero ocurrió un problema con el archivo: " . $resArchivo['error']);
            } else {
                setFlash('success', "¡Entregable '{$titulo}' y documento '{$resArchivo['nombre']}' registrados con éxito!");
            }

            header("Location: proyecto.php?id=" . $proyectoId);
            exit;
        }

        // Acción: Adjuntar archivo adicional a entregable existente
        if ($accion === 'adjuntar_archivo') {
            $entregableId = (int)($_POST['entregable_id'] ?? 0);
            $entregable = Entregable::obtenerPorId($entregableId);

            if (!$entregable || (int)$entregable['proyecto_id'] !== $proyectoId) {
                setFlash('error', 'Entregable no válido para este proyecto.');
                header("Location: proyecto.php?id=" . $proyectoId);
                exit;
            }

            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                setFlash('error', 'Debe seleccionar un archivo válido para adjuntar.');
                header("Location: proyecto.php?id=" . $proyectoId);
                exit;
            }

            $resArchivo = Archivo::subir($entregableId, $usuarioId, $userLogin, $_FILES['archivo']);
            if ($resArchivo['ok']) {
                setFlash('success', "Documento adicional '{$resArchivo['nombre']}' subido exitosamente.");
            } else {
                setFlash('error', "Error al subir documento: " . $resArchivo['error']);
            }

            header("Location: proyecto.php?id=" . $proyectoId);
            exit;
        }

        header("Location: proyecto.php?id=" . $proyectoId);
        exit;
    }
}
