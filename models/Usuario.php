<?php
/**
 * Modelo de Usuario - SGPP-UNS
 * Gestiona autenticación, perfiles y roles institucionales
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Auditoria.php';

class Usuario {
    
    /**
     * Autentica un usuario verificando hash de contraseña
     */
    public static function autenticar(string $usuario, string $password): ?array {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT u.*, r.nombre AS rol_nombre, r.descripcion AS rol_descripcion
                    FROM usuarios u
                    INNER JOIN roles r ON u.rol_id = r.id
                    WHERE u.usuario = :usuario AND u.activo = 1
                    LIMIT 1";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario' => trim($usuario)]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Registrar login exitoso en auditoría
                Auditoria::registrar(
                    $user['id'],
                    $user['usuario'],
                    $user['rol_nombre'],
                    'Inicio de sesión',
                    'Inicio de sesión exitoso desde ' . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
                    'Correcto'
                );
                return $user;
            }

            // Registrar intento fallido
            Auditoria::registrar(
                null,
                $usuario,
                'Desconocido',
                'Intento de inicio de sesión fallido',
                'Credenciales incorrectas para el usuario "' . htmlspecialchars($usuario) . '"',
                'Rechazado'
            );

            return null;
        } catch (Exception $e) {
            error_log("Error de autenticación: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un usuario por ID con su rol
     */
    public static function obtenerPorId(int $id): ?array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre, r.descripcion AS rol_descripcion 
                                   FROM usuarios u 
                                   INNER JOIN roles r ON u.rol_id = r.id 
                                   WHERE u.id = :id");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene todos los usuarios por rol
     */
    public static function obtenerPorRol(string $nombreRol): array {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT u.* FROM usuarios u 
                                   INNER JOIN roles r ON u.rol_id = r.id 
                                   WHERE r.nombre = :rol AND u.activo = 1 
                                   ORDER BY u.apellidos ASC, u.nombres ASC");
            $stmt->execute([':rol' => $nombreRol]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Cuenta el total de usuarios registrados
     */
    public static function contarTotal(): int {
        try {
            $pdo = Database::getConnection();
            return (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
