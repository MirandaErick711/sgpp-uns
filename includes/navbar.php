<?php
/**
 * Top Navbar - SGPP-UNS
 */
$rol = obtenerRolActual();
$rolBadgeClass = match($rol) {
    'estudiante'  => 'badge bg-warning text-dark',
    'docente'     => 'badge bg-primary',
    'coordinador' => 'badge bg-success',
    'autoridad'   => 'badge bg-dark',
    default       => 'badge bg-secondary'
};
?>
<header class="top-navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary d-lg-none btn-sm" id="sidebarToggle" type="button" aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>
        <h2 class="top-navbar-title">
            <i class="bi bi-mortarboard-fill text-danger d-none d-sm-inline"></i>
            <span><?= htmlspecialchars($pageTitle ?? 'Portal Académico') ?></span>
        </h2>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Semestre y Estado del Sistema -->
        <span class="d-none d-md-inline-block text-secondary small fw-medium">
            <i class="bi bi-calendar3 me-1 text-danger"></i> Semestre 2026-II &bull; EPISI
        </span>

        <span class="<?= $rolBadgeClass ?> text-uppercase py-2 px-3 fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
            <?= htmlspecialchars($rol ?? 'Invitado') ?>
        </span>

        <!-- Dropdown Usuario -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle text-secondary fs-6"></i>
                <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($_SESSION['usuario_login'] ?? 'usuario') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="border-radius: 8px;">
                <li class="px-3 py-2 border-bottom">
                    <p class="mb-0 fw-bold small"><?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Usuario') ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?></p>
                </li>
                <li><a class="dropdown-item py-2" href="monitoreo.php"><i class="bi bi-activity me-2 text-danger"></i> Monitoreo del Sistema</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</header>
<main class="app-main">
    <div class="content-body">
        <?php include __DIR__ . '/alerts.php'; ?>
