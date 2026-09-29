<?php
/**
 * Controlador de Autenticación - SGPP-UNS
 * Gestiona el inicio y cierre de sesión, sesiones seguras y regeneración de ID
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Auditoria.php';

class AuthController {

    /**
     * Muestra la pantalla de login o procesa la autenticación POST
     */
    public function login(): void {
        // Si ya cuenta con una sesión activa, redirigir al panel correspondiente
        if (estaAutenticado()) {
            header("Location: index.php");
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario  = trim($_POST['usuario'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($usuario) || empty($password)) {
                $error = 'Por favor ingrese tanto el usuario como la contraseña.';
            } else {
                $user = Usuario::autenticar($usuario, $password);

                if ($user) {
                    // Regenerar ID de sesión para prevenir Session Fixation
                    session_regenerate_id(true);

                    $_SESSION['usuario_id']              = (int)$user['id'];
                    $_SESSION['usuario_login']           = $user['usuario'];
                    $_SESSION['usuario_rol']             = $user['rol_nombre'];
                    $_SESSION['usuario_nombres']         = $user['nombres'];
                    $_SESSION['usuario_apellidos']       = $user['apellidos'];
                    $_SESSION['usuario_nombre_completo'] = $user['nombres'] . ' ' . $user['apellidos'];
                    $_SESSION['usuario_email']           = $user['email'];
                    $_SESSION['usuario_codigo']          = $user['codigo_universitario'];
                    $_SESSION['usuario_escuela']         = $user['escuela'];

                    setFlash('success', "¡Bienvenido al sistema, {$user['nombres']}! Sesión iniciada como " . ucfirst($user['rol_nombre']) . ".");
                    header("Location: index.php");
                    exit;
                } else {
                    $error = 'Usuario o contraseña incorrectos. Verifique sus credenciales.';
                }
            }
        }

        // Renderizar la vista de Login
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Cierra la sesión de forma segura e invalida cookies
     */
    public function logout(): void {
        if (estaAutenticado()) {
            $uid   = (int)($_SESSION['usuario_id'] ?? 0);
            $login = $_SESSION['usuario_login'] ?? 'usuario';
            $rol   = $_SESSION['usuario_rol'] ?? 'usuario';

            Auditoria::registrar(
                $uid,
                $login,
                $rol,
                'Cierre de sesión',
                'El usuario finalizó su sesión correctamente.',
                'Correcto'
            );
        }

        // Vaciar variables de sesión
        $_SESSION = [];

        // Invalidar cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        // Iniciar nueva sesión solo para mensaje flash
        session_start();
        $_SESSION['flash_info'] = 'Ha cerrado su sesión de forma segura.';
        header("Location: login.php");
        exit;
    }
}
