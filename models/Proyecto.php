<?php
/**
 * Modelo de Proyecto - SGPP-UNS
 * Implementa la lógica de proyectos y las validaciones del Driver DR-01
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auditoria.php';

class Proyecto {
    
    /**
     * DR-01: Verifica si un proyecto específico pertenece a un estudiante determinado.
     * Validación estricta a nivel de aplicación (servidor PHP).
     */
    public static function perteneceAEstudiante(int $proyecto_id, int $estudiante_id): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM proyectos WHERE id = :pid AND estudiante_id = :eid");
            $stmt->execute([':pid' => $proyecto_id, ':eid' => $estudiante_id]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (Exception $e) {
            error_log("Error verificando pertenencia de proyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DR-01: Obtiene un proyecto por ID validando permisos de acceso según el usuario y su rol.
     * Si un estudiante intenta consultar un proyecto ajeno, la consulta se BLOQUEA de inmediato,
     * se registra el intento no autorizado en auditoría y se deniega el acceso (100% de rechazo).
     */
    public static function obtenerPorIdConSeguridad(int $proyecto_id, int $usuario_id, string $rol, ?string &$error_dr01 = null): ?array {
        try {
            $pdo = Database::getConnection();

            // Consulta datos del proyecto
            $sql = "SELECT p.*, 
                           u.nombres AS estudiante_nombres, 
                           u.apellidos AS estudiante_apellidos,
                           u.codigo_universitario,
                           u.email AS estudiante_email,
                           u.escuela AS estudiante_escuela
                    FROM proyectos p
                    INNER JOIN usuarios u ON p.estudiante_id = u.id
                    WHERE p.id = :id LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $proyecto_id]);
            $proyecto = $stmt->fetch();

            if (!$proyecto) {
                $error_dr01 = 'El proyecto solicitado no existe en el sistema.';
                return null;
            }

            // APLICACIÓN DEL DRIVER ARQUITECTÓNICO DR-01:
            // "Evitar que un estudiante pueda consultar o modificar proyectos que no le pertenecen"
            if ($rol === 'estudiante') {
                if ((int)$proyecto['estudiante_id'] !== $usuario_id) {
                    $error_dr01 = 'ACCESO_NO_AUTORIZADO_DR01';
                    
                    // Registro automático en el historial de auditoría
                    $usuario = $_SESSION['usuario_nombre_completo'] ?? ('Usuario #' . $usuario_id);
                    $usuarioLogin = $_SESSION['usuario_login'] ?? 'estudiante';
                    
                    Auditoria::registrar(
                        $usuario_id,
                        $usuarioLogin,
                        $rol,
                        'Acceso no autorizado (DR-01)',
                        "Intento denegado: El estudiante intentó acceder al proyecto ID #{$proyecto_id} ('" . htmlspecialchars($proyecto['titulo']) . "') que pertenece a otro alumno (ID #{$proyecto['estudiante_id']}).",
                        'Rechazado'
                    );

                    return null; // NO se entrega ningún dato sensible del proyecto ajeno
                }
            }

            // Si es el propietario o es docente / coordinador / autoridad con permisos académicos
            return $proyecto;
        } catch (Exception $e) {
            error_log("Error al consultar proyecto con seguridad: " . $e->getMessage());
            $error_dr01 = 'Error interno en el servidor.';
            return null;
        }
    }

    /**
     * Obtiene todos los proyectos pertenecientes a un estudiante
     */
    public static function obtenerPorEstudiante(int $estudiante_id): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT p.*, 
                           COUNT(e.id) AS total_entregables,
                           SUM(CASE WHEN e.estado = 'Aprobado' THEN 1 ELSE 0 END) AS entregables_aprobados,
                           SUM(CASE WHEN e.estado = 'Observado' THEN 1 ELSE 0 END) AS entregables_observados
                    FROM proyectos p
                    LEFT JOIN entregables e ON p.id = e.proyecto_id
                    WHERE p.estudiante_id = :estudiante_id
                    GROUP BY p.id
                    ORDER BY p.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':estudiante_id' => $estudiante_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error listando proyectos de estudiante: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene proyectos con filtros opcionales (para docentes, coordinadores y autoridades)
     */
    public static function obtenerTodos(?string $estado = null, ?int $estudiante_id = null, ?string $fecha = null): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT p.*, 
                           u.nombres AS estudiante_nombres, 
                           u.apellidos AS estudiante_apellidos,
                           u.codigo_universitario,
                           COUNT(e.id) AS total_entregables
                    FROM proyectos p
                    INNER JOIN usuarios u ON p.estudiante_id = u.id
                    LEFT JOIN entregables e ON p.id = e.proyecto_id
                    WHERE 1=1 ";
            
            $params = [];
            if (!empty($estado)) {
                $sql .= " AND p.estado = :estado";
                $params[':estado'] = $estado;
            }
            if (!empty($estudiante_id)) {
                $sql .= " AND p.estudiante_id = :estudiante_id";
                $params[':estudiante_id'] = $estudiante_id;
            }
            if (!empty($fecha)) {
                $sql .= " AND (p.fecha_inicio >= :fecha OR p.created_at >= :fecha)";
                $params[':fecha'] = $fecha;
            }

            $sql .= " GROUP BY p.id ORDER BY p.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error consultando lista de proyectos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra un nuevo proyecto utilizando TRANSACCIONES PDO (Confiabilidad y consistencia)
     */
    public static function crear(array $datos, int $estudiante_id, string $usuarioLogin): array {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            // Generar código institucional correlativo (ej. PRY-2026-005)
            $ano = date('Y');
            $stmtCount = $pdo->query("SELECT COUNT(*) + 1 AS correlativo FROM proyectos");
            $correlativo = (int)$stmtCount->fetchColumn();
            $codigo = sprintf("PRY-%s-%03d", $ano, $correlativo);

            $sql = "INSERT INTO proyectos (codigo_proyecto, titulo, descripcion, estudiante_id, linea_investigacion, fecha_inicio, fecha_fin_prevista, estado, created_at)
                    VALUES (:codigo, :titulo, :descripcion, :estudiante_id, :linea, :fecha_inicio, :fecha_fin, 'Pendiente', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':codigo'        => $codigo,
                ':titulo'        => trim($datos['titulo']),
                ':descripcion'   => trim($datos['descripcion']),
                ':estudiante_id' => $estudiante_id,
                ':linea'         => trim($datos['linea_investigacion'] ?? 'Sistemas de Información y Gestión del Conocimiento'),
                ':fecha_inicio'  => $datos['fecha_inicio'],
                ':fecha_fin'     => $datos['fecha_fin_prevista']
            ]);

            $proyectoId = (int)$pdo->lastInsertId();

            // Auditoría dentro de la misma transacción para consistencia
            Auditoria::registrar(
                $estudiante_id,
                $usuarioLogin,
                'estudiante',
                'Registro de proyecto',
                "Registro exitoso del proyecto '{$datos['titulo']}' con código {$codigo}",
                'Correcto'
            );

            $pdo->commit();
            return ['ok' => true, 'id' => $proyectoId, 'codigo' => $codigo];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Fallo al registrar proyecto: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resumen de métricas para dashboard de estudiante
     */
    public static function resumenEstudiante(int $estudiante_id): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT 
                        COUNT(id) AS total_proyectos,
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN estado = 'En revisión' THEN 1 ELSE 0 END) AS en_revision,
                        SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                        SUM(CASE WHEN estado = 'Observado' THEN 1 ELSE 0 END) AS observados
                    FROM proyectos 
                    WHERE estudiante_id = :eid";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':eid' => $estudiante_id]);
            $res = $stmt->fetch();

            // Contar observaciones recibidas pendientes
            $sqlObs = "SELECT COUNT(o.id) 
                       FROM observaciones o
                       INNER JOIN entregables e ON o.entregable_id = e.id
                       INNER JOIN proyectos p ON e.proyecto_id = p.id
                       WHERE p.estudiante_id = :eid AND o.tipo_decision = 'Observado'";
            $stmtObs = $pdo->prepare($sqlObs);
            $stmtObs->execute([':eid' => $estudiante_id]);
            $res['observaciones_pendientes'] = (int)$stmtObs->fetchColumn();

            return $res ?: [
                'total_proyectos' => 0, 'pendientes' => 0, 'en_revision' => 0, 
                'aprobados' => 0, 'observados' => 0, 'observaciones_pendientes' => 0
            ];
        } catch (Exception $e) {
            return [
                'total_proyectos' => 0, 'pendientes' => 0, 'en_revision' => 0, 
                'aprobados' => 0, 'observados' => 0, 'observaciones_pendientes' => 0
            ];
        }
    }

    /**
     * Resumen global de métricas para coordinadores y autoridades
     */
    public static function resumenGlobal(): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT 
                        COUNT(id) AS total_proyectos,
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN estado = 'En revisión' THEN 1 ELSE 0 END) AS en_revision,
                        SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                        SUM(CASE WHEN estado = 'Observado' THEN 1 ELSE 0 END) AS observados,
                        SUM(CASE WHEN estado = 'Rechazado' THEN 1 ELSE 0 END) AS rechazados
                    FROM proyectos";
            return $pdo->query($sql)->fetch() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
