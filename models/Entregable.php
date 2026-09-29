<?php
/**
 * Modelo de Entregables - SGPP-UNS
 * Gestiona el ciclo de vida de los entregables y la revisión docente
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auditoria.php';

class Entregable {
    
    /**
     * Obtiene un entregable por su ID con datos del proyecto y estudiante
     */
    public static function obtenerPorId(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT e.*, 
                           p.titulo AS proyecto_titulo, 
                           p.codigo_proyecto, 
                           p.estudiante_id,
                           u.nombres AS estudiante_nombres, 
                           u.apellidos AS estudiante_apellidos,
                           u.codigo_universitario,
                           u.email AS estudiante_email,
                           d.nombres AS docente_nombres,
                           d.apellidos AS docente_apellidos
                    FROM entregables e
                    INNER JOIN proyectos p ON e.proyecto_id = p.id
                    INNER JOIN usuarios u ON p.estudiante_id = u.id
                    LEFT JOIN usuarios d ON e.docente_revisor_id = d.id
                    WHERE e.id = :id LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (Exception $e) {
            error_log("Error obteniendo entregable: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los entregables asociados a un proyecto
     */
    public static function obtenerPorProyecto(int $proyecto_id): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT e.*, 
                           COUNT(a.id) AS total_archivos,
                           (SELECT o.comentario FROM observaciones o WHERE o.entregable_id = e.id ORDER BY o.id DESC LIMIT 1) AS ultima_observacion,
                           (SELECT o.tipo_decision FROM observaciones o WHERE o.entregable_id = e.id ORDER BY o.id DESC LIMIT 1) AS ultima_decision,
                           (SELECT o.fecha_registro FROM observaciones o WHERE o.entregable_id = e.id ORDER BY o.id DESC LIMIT 1) AS fecha_observacion
                    FROM entregables e
                    LEFT JOIN archivos a ON e.id = a.entregable_id
                    WHERE e.proyecto_id = :pid
                    GROUP BY e.id
                    ORDER BY e.numero_entregable ASC, e.id ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':pid' => $proyecto_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error listando entregables de proyecto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene lista de entregables para la bandeja del docente
     */
    public static function obtenerParaDocente(?string $filtro_estado = null): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT e.*, 
                           p.titulo AS proyecto_titulo, 
                           p.codigo_proyecto,
                           u.nombres AS estudiante_nombres, 
                           u.apellidos AS estudiante_apellidos,
                           u.codigo_universitario,
                           COUNT(a.id) AS total_archivos
                    FROM entregables e
                    INNER JOIN proyectos p ON e.proyecto_id = p.id
                    INNER JOIN usuarios u ON p.estudiante_id = u.id
                    LEFT JOIN archivos a ON e.id = a.entregable_id
                    WHERE 1=1 ";
            
            $params = [];
            if (!empty($filtro_estado)) {
                $sql .= " AND e.estado = :estado";
                $params[':estado'] = $filtro_estado;
            }

            $sql .= " GROUP BY e.id ORDER BY FIELD(e.estado, 'En revisión', 'Pendiente', 'Observado', 'Aprobado', 'Rechazado'), e.fecha_entrega DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error cargando entregables para docente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen de estados de entregables para el docente
     */
    public static function resumenDocente(): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT 
                        COUNT(id) AS total_entregables,
                        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN estado = 'En revisión' THEN 1 ELSE 0 END) AS en_revision,
                        SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) AS aprobados,
                        SUM(CASE WHEN estado = 'Observado' THEN 1 ELSE 0 END) AS observados,
                        SUM(CASE WHEN estado = 'Rechazado' THEN 1 ELSE 0 END) AS rechazados
                    FROM entregables";
            return $pdo->query($sql)->fetch() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Registra un nuevo entregable para un proyecto usando TRANSACCIÓN
     */
    public static function crear(int $proyecto_id, string $titulo, ?string $descripcion, int $usuario_id, string $usuarioLogin): array {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            // Calcular número correlativo del entregable
            $stmtNum = $pdo->prepare("SELECT COALESCE(MAX(numero_entregable), 0) + 1 FROM entregables WHERE proyecto_id = :pid");
            $stmtNum->execute([':pid' => $proyecto_id]);
            $numEntregable = (int)$stmtNum->fetchColumn();

            $sql = "INSERT INTO entregables (proyecto_id, titulo, descripcion, numero_entregable, fecha_entrega, estado, created_at)
                    VALUES (:pid, :titulo, :desc, :num, NOW(), 'En revisión', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pid'    => $proyecto_id,
                ':titulo' => trim($titulo),
                ':desc'   => trim($descripcion ?? ''),
                ':num'    => $numEntregable
            ]);
            $entregableId = (int)$pdo->lastInsertId();

            // Actualizar estado del proyecto a 'En revisión'
            $pdo->prepare("UPDATE proyectos SET estado = 'En revisión' WHERE id = :pid")->execute([':pid' => $proyecto_id]);

            // Registrar en historial de estados
            $sqlHist = "INSERT INTO historial_estados (entregable_id, estado_anterior, estado_nuevo, usuario_id, motivo, fecha_cambio)
                        VALUES (:eid, 'Pendiente', 'En revisión', :uid, 'Creación de entregable y envío a revisión', NOW())";
            $pdo->prepare($sqlHist)->execute([':eid' => $entregableId, ':uid' => $usuario_id]);

            // Registrar en auditoría
            Auditoria::registrar(
                $usuario_id,
                $usuarioLogin,
                'estudiante',
                'Creación de entregable',
                "Registro del '{$titulo}' (N° {$numEntregable}) en el proyecto ID #{$proyecto_id}",
                'Correcto'
            );

            $pdo->commit();
            return ['ok' => true, 'id' => $entregableId];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error creando entregable: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Procesa la revisión de un entregable por parte del docente con TRANSACCIÓN
     * (Aprobar, Observar, Rechazar + Registro de Observación + Historial de Estados)
     */
    public static function evaluarPorDocente(
        int $entregable_id, 
        int $docente_id, 
        string $nuevo_estado, 
        string $comentario, 
        string $docenteLogin
    ): array {
        $validos = ['Aprobado', 'Observado', 'Rechazado'];
        if (!in_array($nuevo_estado, $validos)) {
            return ['ok' => false, 'error' => 'Estado de evaluación no válido.'];
        }

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            // Obtener estado actual
            $stmtAct = $pdo->prepare("SELECT estado, proyecto_id, titulo FROM entregables WHERE id = :id FOR UPDATE");
            $stmtAct->execute([':id' => $entregable_id]);
            $entregable = $stmtAct->fetch();

            if (!$entregable) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Entregable no encontrado.'];
            }

            $estadoAnterior = $entregable['estado'];
            $proyectoId = (int)$entregable['proyecto_id'];

            // 1. Actualizar entregable
            $stmtUpd = $pdo->prepare("UPDATE entregables 
                                      SET estado = :estado, docente_revisor_id = :docente_id, fecha_revision = NOW() 
                                      WHERE id = :id");
            $stmtUpd->execute([
                ':estado'     => $nuevo_estado,
                ':docente_id' => $docente_id,
                ':id'         => $entregable_id
            ]);

            // 2. Insertar observación docente
            $stmtObs = $pdo->prepare("INSERT INTO observaciones (entregable_id, docente_id, comentario, tipo_decision, fecha_registro)
                                      VALUES (:eid, :did, :com, :tipo, NOW())");
            $stmtObs->execute([
                ':eid'  => $entregable_id,
                ':did'  => $docente_id,
                ':com'  => trim($comentario),
                ':tipo' => $nuevo_estado
            ]);

            // 3. Registrar en historial de estados
            $stmtHist = $pdo->prepare("INSERT INTO historial_estados (entregable_id, estado_anterior, estado_nuevo, usuario_id, motivo, fecha_cambio)
                                       VALUES (:eid, :anterior, :nuevo, :uid, :motivo, NOW())");
            $stmtHist->execute([
                ':eid'      => $entregable_id,
                ':anterior' => $estadoAnterior,
                ':nuevo'    => $nuevo_estado,
                ':uid'      => $docente_id,
                ':motivo'   => "Evaluación docente: {$nuevo_estado}"
            ]);

            // 4. Sincronizar estado global del proyecto
            // Si el entregable es observado, el proyecto pasa a Observado. Si es aprobado, verificar si todos están aprobados.
            if ($nuevo_estado === 'Observado') {
                $pdo->prepare("UPDATE proyectos SET estado = 'Observado' WHERE id = :pid")->execute([':pid' => $proyectoId]);
            } elseif ($nuevo_estado === 'Rechazado') {
                $pdo->prepare("UPDATE proyectos SET estado = 'Rechazado' WHERE id = :pid")->execute([':pid' => $proyectoId]);
            } elseif ($nuevo_estado === 'Aprobado') {
                // Verificar si todos los entregables están aprobados
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM entregables WHERE proyecto_id = :pid AND estado != 'Aprobado'");
                $stmtCheck->execute([':pid' => $proyectoId]);
                $noAprobados = (int)$stmtCheck->fetchColumn();
                if ($noAprobados === 0) {
                    $pdo->prepare("UPDATE proyectos SET estado = 'Aprobado' WHERE id = :pid")->execute([':pid' => $proyectoId]);
                } else {
                    $pdo->prepare("UPDATE proyectos SET estado = 'En revisión' WHERE id = :pid")->execute([':pid' => $proyectoId]);
                }
            }

            // 5. Registrar en auditoría
            Auditoria::registrar(
                $docente_id,
                $docenteLogin,
                'docente',
                "Evaluación de entregable ({$nuevo_estado})",
                "El docente evaluó el entregable '{$entregable['titulo']}' cambiando de '{$estadoAnterior}' a '{$nuevo_estado}'. Comentario registrado.",
                'Correcto'
            );

            $pdo->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al evaluar entregable: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
