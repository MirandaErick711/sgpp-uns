<?php
/**
 * Modelo de Archivos - SGPP-UNS
 * Gestiona el almacenamiento de archivos físicos en uploads/
 * y sus metadatos en la base de datos MySQL (Decisión arquitectónica)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auditoria.php';

class Archivo {
    
    // Directorio de almacenamiento físico
    public const UPLOADS_DIR = __DIR__ . '/../uploads/';
    
    // Tamaño máximo permitido: 20 Megabytes
    public const MAX_BYTES = 20 * 1024 * 1024;
    
    // Extensiones y tipos MIME permitidos
    public const EXTENSIONES_PERMITIDAS = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip'  => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
        'txt'  => 'text/plain',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg'
    ];

    /**
     * Obtiene los archivos asociados a un entregable
     */
    public static function obtenerPorEntregable(int $entregable_id): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT a.*, u.nombres, u.apellidos, u.usuario 
                                   FROM archivos a 
                                   INNER JOIN usuarios u ON a.usuario_id = u.id 
                                   WHERE a.entregable_id = :eid 
                                   ORDER BY a.fecha_subida DESC");
            $stmt->execute([':eid' => $entregable_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo archivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un archivo por su ID
     */
    public static function obtenerPorId(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT a.*, e.proyecto_id, p.estudiante_id, p.titulo AS proyecto_titulo
                    FROM archivos a
                    INNER JOIN entregables e ON a.entregable_id = e.id
                    INNER JOIN proyectos p ON e.proyecto_id = p.id
                    WHERE a.id = :id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Valida, almacena físicamente el archivo en uploads/ y registra en BD
     */
    public static function subir(int $entregable_id, int $usuario_id, string $usuarioLogin, array $archivo_post): array {
        if (!isset($archivo_post['name']) || $archivo_post['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = match ($archivo_post['error'] ?? UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió solo parcialmente.',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo para subir.',
                default => 'Error al procesar la carga del archivo.'
            };
            return ['ok' => false, 'error' => $errorMsg];
        }

        // 1. Validar tamaño
        if ($archivo_post['size'] > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'El archivo excede el límite máximo de 20 MB.'];
        }

        // 2. Validar extensión
        $nombreOriginal = basename($archivo_post['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!array_key_exists($extension, self::EXTENSIONES_PERMITIDAS)) {
            return ['ok' => false, 'error' => "Formato de archivo no permitido (.{$extension}). Formatos válidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP, TXT, PNG, JPG."];
        }

        // 3. Generar nombre físico seguro y único
        if (!is_dir(self::UPLOADS_DIR)) {
            mkdir(self::UPLOADS_DIR, 0755, true);
        }

        $nombreFisico = sprintf("doc_%d_%s_%s.%s", $entregable_id, date('Ymd_His'), substr(uniqid(), -6), $extension);
        $rutaCompleta = self::UPLOADS_DIR . $nombreFisico;
        $rutaRelativa = 'uploads/' . $nombreFisico;

        // 4. Mover archivo físico al directorio uploads/
        if (!move_uploaded_file($archivo_post['tmp_name'], $rutaCompleta)) {
            return ['ok' => false, 'error' => 'No se pudo guardar el archivo físico en el servidor. Verifique permisos de carpeta.'];
        }

        // 5. Registrar metadatos en la base de datos (con Transacción)
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            $tipoMime = mime_content_type($rutaCompleta) ?: 'application/octet-stream';
            $tamanoBytes = (int)$archivo_post['size'];

            $sql = "INSERT INTO archivos (entregable_id, usuario_id, nombre_original, nombre_archivo, ruta, tipo_mime, tamano_bytes, fecha_subida)
                    VALUES (:eid, :uid, :nom_orig, :nom_fisc, :ruta, :mime, :tam, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':eid'      => $entregable_id,
                ':uid'      => $usuario_id,
                ':nom_orig' => $nombreOriginal,
                ':nom_fisc' => $nombreFisico,
                ':ruta'     => $rutaRelativa,
                ':mime'     => $tipoMime,
                ':tam'      => $tamanoBytes
            ]);
            $archivoId = (int)$pdo->lastInsertId();

            // Auditoría
            Auditoria::registrar(
                $usuario_id,
                $usuarioLogin,
                'estudiante',
                'Subida de archivo',
                "Carga exitosa del archivo '{$nombreOriginal}' (" . self::formatearTamano($tamanoBytes) . ") en entregable ID #{$entregable_id}",
                'Correcto'
            );

            $pdo->commit();
            return ['ok' => true, 'id' => $archivoId, 'nombre' => $nombreOriginal];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta); // Limpieza si falla BD
            }
            error_log("Error registrando archivo en BD: " . $e->getMessage());
            return ['ok' => false, 'error' => 'Error al registrar archivo en la base de datos.'];
        }
    }

    /**
     * Formateador legible de bytes
     */
    public static function formatearTamano(int $bytes): string {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Estadísticas de almacenamiento para el módulo de Monitoreo
     */
    public static function estadisticasAlmacenamiento(): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT COUNT(*) AS total_archivos, COALESCE(SUM(tamano_bytes), 0) AS total_bytes FROM archivos");
            $data = $stmt->fetch();
            $totalBytes = (int)($data['total_bytes'] ?? 0);
            return [
                'total_archivos' => (int)($data['total_archivos'] ?? 0),
                'total_bytes'    => $totalBytes,
                'espacio_legible' => self::formatearTamano($totalBytes)
            ];
        } catch (Exception $e) {
            return ['total_archivos' => 0, 'total_bytes' => 0, 'espacio_legible' => '0 MB'];
        }
    }
}
