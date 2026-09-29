<?php
/**
 * Controlador de Archivos - SGPP-UNS
 * Gestiona la descarga segura de archivos y la aplicación del Driver DR-01
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Archivo.php';
require_once __DIR__ . '/../models/Auditoria.php';

class ArchivoController {

    /**
     * Descarga segura de un archivo validando permisos DR-01
     */
    public function descargar(int $archivoId = 0): void {
        requerirAutenticacion();

        if ($archivoId <= 0) {
            $archivoId = (int)($_GET['id'] ?? 0);
        }

        if ($archivoId <= 0) {
            setFlash('error', 'Identificador de archivo inválido.');
            header("Location: index.php");
            exit;
        }

        $archivo = Archivo::obtenerPorId($archivoId);
        if (!$archivo) {
            setFlash('error', 'El documento solicitado no existe en los registros.');
            header("Location: index.php");
            exit;
        }

        $usuarioId  = (int)$_SESSION['usuario_id'];
        $rolUsuario = (string)$_SESSION['usuario_rol'];
        $userLogin  = (string)$_SESSION['usuario_login'];

        // ====================================================================
        // VALIDACIÓN DEL DRIVER ARQUITECTÓNICO DR-01
        // Un estudiante NO puede descargar archivos pertenecientes a proyectos ajenos
        // ====================================================================
        if ($rolUsuario === 'estudiante') {
            if ((int)$archivo['estudiante_id'] !== $usuarioId) {
                Auditoria::registrar(
                    $usuarioId,
                    $userLogin,
                    $rolUsuario,
                    'Acceso no autorizado',
                    "Intento denegado de descarga de archivo ID #{$archivoId} ('{$archivo['nombre_original']}') perteneciente a otro estudiante.",
                    'Rechazado'
                );
                http_response_code(403);
                require __DIR__ . '/../views/error/403.php';
                exit;
            }
        }

        // Ruta física en disco (en la raíz del proyecto bajo uploads/)
        $rutaFisica = dirname(__DIR__) . '/' . $archivo['ruta'];

        if (!file_exists($rutaFisica)) {
            setFlash('error', 'El archivo no se encuentra físicamente en el servidor de almacenamiento.');
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }

        // Registrar descarga exitosa en auditoría
        Auditoria::registrar(
            $usuarioId,
            $userLogin,
            $rolUsuario,
            'Descarga de archivo',
            "Descarga del documento '{$archivo['nombre_original']}' desde uploads/.",
            'Correcto'
        );

        // Enviar cabeceras HTTP para forzar la descarga con el nombre original
        $tipoMime = $archivo['tipo_mime'] ?: 'application/octet-stream';
        $nombreLimpio = str_replace(['"', "'", ';'], '', $archivo['nombre_original']);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $tipoMime);
        header('Content-Disposition: attachment; filename="' . $nombreLimpio . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaFisica));

        // Limpiar buffer de salida para evitar corrupción binaria
        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($rutaFisica);
        exit;
    }
}
