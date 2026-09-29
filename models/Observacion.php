<?php
/**
 * Modelo de Observaciones - SGPP-UNS
 * Gestiona observaciones docentes y retroalimentación académica
 */

require_once __DIR__ . '/../config/database.php';

class Observacion {
    
    /**
     * Obtiene todas las observaciones realizadas a los proyectos de un estudiante
     */
    public static function obtenerPorEstudiante(int $estudiante_id): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT o.*, 
                           e.titulo AS entregable_titulo, 
                           e.numero_entregable,
                           p.id AS proyecto_id,
                           p.codigo_proyecto,
                           p.titulo AS proyecto_titulo,
                           d.nombres AS docente_nombres,
                           d.apellidos AS docente_apellidos
                    FROM observaciones o
                    INNER JOIN entregables e ON o.entregable_id = e.id
                    INNER JOIN proyectos p ON e.proyecto_id = p.id
                    INNER JOIN usuarios d ON o.docente_id = d.id
                    WHERE p.estudiante_id = :eid
                    ORDER BY o.fecha_registro DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':eid' => $estudiante_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error obteniendo observaciones de estudiante: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el historial de observaciones de un entregable específico
     */
    public static function obtenerPorEntregable(int $entregable_id): array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT o.*, 
                           d.nombres AS docente_nombres, 
                           d.apellidos AS docente_apellidos,
                           d.email AS docente_email
                    FROM observaciones o
                    INNER JOIN usuarios d ON o.docente_id = d.id
                    WHERE o.entregable_id = :eid
                    ORDER BY o.fecha_registro DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':eid' => $entregable_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
