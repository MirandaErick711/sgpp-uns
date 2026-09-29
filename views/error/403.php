<?php
/**
 * Pantalla de Acceso No Autorizado - SGPP-UNS (Limpia y Sobria)
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
    <link rel="icon" type="image/svg+xml" href="assets/img/logo_uns.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/uns-theme.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-sm" style="border-radius: 8px; overflow: hidden; border-top: 4px solid #8B1527;">
                <div class="card-body p-4 text-center">
                    
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 64px; height: 64px; background-color: #FEF2F2; color: #DC2626; font-size: 2rem;">
                            <i class="bi bi-shield-x"></i>
                        </div>
                    </div>

                    <span class="badge bg-danger-subtle text-danger border px-2.5 py-1 text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        Control de Seguridad &bull; Driver DR-01
                    </span>

                    <h4 class="fw-bold text-danger mb-1">Acceso No Autorizado</h4>
                    <p class="text-muted small mb-3">La capa de aplicación PHP ha restringido esta solicitud.</p>

                    <div class="border rounded p-3 text-start bg-light mb-3 small">
                        <strong class="text-danger d-block mb-1">Principio de Aislamiento Estricto (DR-01):</strong>
                        <p class="text-secondary mb-1">
                            El sistema verificó en el servidor que el usuario autenticado 
                            <strong>(<?= htmlspecialchars($_SESSION['usuario_login'] ?? 'usuario') ?>)</strong> 
                            no es el propietario del recurso solicitado.
                        </p>
                        <p class="text-muted mb-0" style="font-size: 0.78rem;">
                            Este evento fue registrado en la bitácora de auditoría con resultado <code>Rechazado</code>.
                        </p>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="index.php" class="btn btn-uns-primary btn-sm px-3">
                            <i class="bi bi-arrow-left me-1"></i> Ir a mi panel principal
                        </a>
                        <a href="monitoreo.php" class="btn btn-outline-secondary btn-sm px-3">
                            Ver Monitoreo
                        </a>
                    </div>

                </div>
                <div class="card-footer bg-white text-center py-2 text-muted" style="font-size: 0.75rem;">
                    Universidad Nacional del Santa &bull; EPISI
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
