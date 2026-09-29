<?php
/**
 * Conexión a Base de Datos - SGPP-UNS
 * Universidad Nacional del Santa
 * Implementación con PHP Data Objects (PDO)
 */

class Database {
    private static ?PDO $instance = null;
    
    // Parámetros de conexión predeterminados para XAMPP
    private static string $host = '127.0.0.1';
    private static string $db   = 'sgpp_uns';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $charset = 'utf8mb4';

    /**
     * Retorna una instancia única de PDO (Patrón Singleton)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                // Registro seguro del error y mensaje amigable
                error_log("Error de conexión a la BD SGPP-UNS: " . $e->getMessage());
                die("<div style='font-family:sans-serif;padding:30px;background:#fff5f5;border-left:5px solid #8B1527;margin:30px;'>
                        <h2 style='color:#8B1527;'>Error al conectar con la Base de Datos SGPP-UNS</h2>
                        <p>No se pudo establecer comunicación con MySQL en <code>" . htmlspecialchars(self::$host) . "</code>.</p>
                        <p><strong>Verifique:</strong></p>
                        <ul>
                            <li>Que el servicio MySQL en el panel de XAMPP esté iniciado (verde).</li>
                            <li>Que la base de datos <code>sgpp_uns</code> haya sido importada desde el archivo <code>database.sql</code>.</li>
                        </ul>
                        <p style='color:#666;'>Detalle técnico: " . htmlspecialchars($e->getMessage()) . "</p>
                     </div>");
            }
        }
        return self::$instance;
    }

    /**
     * Verifica el estado actual de la conexión a la base de datos (usado por Monitoreo)
     */
    public static function checkHealth(): array {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT VERSION() AS version, NOW() AS server_time");
            $data = $stmt->fetch();
            return [
                'status' => 'Conectada',
                'ok' => true,
                'version' => $data['version'] ?? 'Desconocida',
                'server_time' => $data['server_time'] ?? date('Y-m-d H:i:s'),
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'status' => 'Error de conexión',
                'ok' => false,
                'version' => 'N/A',
                'server_time' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ];
        }
    }
}
