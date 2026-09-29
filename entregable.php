<?php
/**
 * Controlador de Entregables y Carga de Archivos - SGPP-UNS
 * Aplicación de validaciones de propiedad DR-01
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Entregable.php';
require_once __DIR__ . '/models/Archivo.php';
require_once __DIR__ . '/models/Auditoria.php';

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
        include __DIR__ . '/views/error/403.php';
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

    // 1. Crear el entregable
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
