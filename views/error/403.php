<?php
/**
 * Pantalla de Acceso No Autorizado - SGPP-UNS
 * Demostración del Driver Arquitectónico DR-01
 */
if (!defined('ACCESO_PERMITIDO') && !isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso No Autorizado (DR-01) - SGPP-UNS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/uns-theme.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; border-top: 6px solid #8B1527;">
                <div class="card-body p-4 p-md-5 text-center">
                    
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 90px; height: 90px; background-color: #FFF5F5; color: #E53E3E; font-size: 3rem;">
                            <i class="bi bi-shield-x"></i>
                        </div>
                    </div>

                    <span class="badge bg-danger px-3 py-2 text-uppercase mb-3" style="letter-spacing: 1px;">
                        <i class="bi bi-lock-fill me-1"></i> Control de Seguridad - Driver DR-01
                    </span>

                    <h2 class="fw-bold text-danger mb-2">Acceso No Autorizado</h2>
                    <h5 class="text-secondary fw-semibold mb-4">La capa de aplicación PHP ha bloqueado esta solicitud</h5>

                    <div class="dr01-alert-box text-start">
                        <h6 class="fw-bold text-danger mb-2">
                            <i class="bi bi-exclamation-octagon-fill me-2"></i>Principio de Aislamiento Estricto (DR-01):
                        </h6>
                        <p class="mb-2 text-muted" style="font-size: 0.92rem;">
                            <em>"Evitar que un estudiante pueda consultar o modificar proyectos que no le pertenecen."</em>
                        </p>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                            El sistema validó en el servidor que el usuario autenticado 
                            <strong>(<?= htmlspecialchars($_SESSION['usuario_login'] ?? 'usuario') ?>)</strong> 
                            no es el propietario del recurso solicitado. Ninguna información sensible ha sido devuelta ni expuesta.
                        </p>
                    </div>

                    <div class="alert alert-light border text-start d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-activity text-danger fs-3"></i>
                        <div style="font-size: 0.85rem;" class="text-secondary">
                            <strong>Registro en Auditoría y Monitoreo:</strong><br>
                            Este evento ha sido registrado en la base de datos con acción 
                            <code>Acceso no autorizado (DR-01)</code> y resultado <code>Rechazado</code>, registrando fecha, IP y usuario.
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="index.php" class="btn btn-uns-primary px-4 py-2">
                            <i class="bi bi-arrow-left me-2"></i>Regresar a mis Proyectos Autorizados
                        </a>
                        <a href="monitoreo.php" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-shield-check me-2"></i>Ver Historial de Monitoreo
                        </a>
                    </div>

                </div>
                <div class="card-footer bg-light text-center py-3 text-muted" style="font-size: 0.8rem;">
                    Universidad Nacional del Santa &bull; Escuela Profesional de Ingeniería de Sistemas e Informática
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
