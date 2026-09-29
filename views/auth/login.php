<?php
/**
 * Vista de Inicio de Sesión - SGPP-UNS
 * Universidad Nacional del Santa
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SGPP-UNS</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo_uns.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/uns-theme.css">
</head>
<body class="login-page-body">

<div class="login-card-container">
    <div class="login-header">
        <img src="assets/img/logo_uns.png" alt="Escudo Universidad Nacional del Santa" class="img-fluid">
        <h4 style="font-family: 'Cinzel', serif;">SGPP-UNS</h4>
        <p class="text-uppercase fw-semibold" style="font-size: 0.76rem; letter-spacing: 1px; color: var(--uns-gold-dark);">
            Universidad Nacional del Santa
        </p>
        <span class="badge bg-light text-secondary border">Facultad de Ingeniería &bull; EPISI</span>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <?php $msg = getFlash('info'); if ($msg): ?>
            <div class="alert alert-info py-2 px-3 mb-3 small">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php $msgErr = getFlash('error'); if ($msgErr): ?>
            <div class="alert alert-warning py-2 px-3 mb-3 small">
                <?= htmlspecialchars($msgErr) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label for="inputUsuario" class="form-label">
                    <i class="bi bi-person-fill text-danger me-1"></i> Usuario o correo institucional
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="inputUsuario" name="usuario" 
                           placeholder="ej: estudiante1 o docente1" required autofocus 
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="inputPassword" class="form-label">
                    <i class="bi bi-key-fill text-danger me-1"></i> Contraseña
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="inputPassword" name="password" 
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-uns-primary w-100 py-2 fs-6 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
