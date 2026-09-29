<?php
/**
 * Modelo de Auditoría e Historial de Acciones - SGPP-UNS
 * Permite la trazabilidad de seguridad y supervisión del driver DR-01
 */

require_once __DIR__ . '/../config/database.php';

class Auditoria {
    
    /**
     * Registra un evento en la tabla historial_acciones
     */
    public static function registrar(
        ?int $usuario_id,
        string $nombre_usuario,
        string $rol,
        string $accion,
        string $detalle,
        string $resultado = 'Correcto'
    ): bool {
        try {
            $pdo = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            $sql = "INSERT INTO historial_acciones (usuario_id, nombre_usuario, rol, accion, detalle, resultado, ip_origen, fecha_hora)
                    VALUES (:usuario_id, :nombre_usuario, :rol, :accion, :detalle, :resultado, :ip_origen, NOW())";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':usuario_id'      => $usuario_id,
                ':nombre_usuario'  => $nombre_usuario,
                ':rol'             => $rol,
                ':accion'          => $accion,
                ':detalle'         => $detalle,
                ':resultado'       => $resultado,
                ':ip_origen'       => $ip
            ]);
        } catch (Exception $e) {
            error_log("Fallo al registrar auditoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los últimos registros de auditoría para el módulo de Monitoreo
     */
    public static function obtenerUltimos(int $limite = 50, ?string $filtroResultado = null): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT h.*, u.nombres, u.apellidos
                    FROM historial_acciones h
                    LEFT JOIN usuarios u ON h.usuario_id = u.id ";
            
            if ($filtroResultado !== null) {
                $sql .= "WHERE h.resultado = :resultado ";
            }
            
            $sql .= "ORDER BY h.id DESC LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            if ($filtroResultado !== null) {
                $stmt->bindValue(':resultado', $filtroResultado, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo auditoría: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta total de eventos registrados
     */
    public static function contarTotal(): int {
        try {
            $pdo = Database::getConnection();
            return (int) $pdo->query("SELECT COUNT(*) FROM historial_acciones")->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
