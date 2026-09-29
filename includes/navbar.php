<?php
/**
 * Top Navbar - SGPP-UNS (Limpia y Funcional)
 */
$rol = obtenerRolActual();
$rolTexto = ucfirst($rol ?? 'Usuario');
?>
<header class="top-navbar">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none btn-sm" id="sidebarToggle" type="button" aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="top-navbar-title">
            <?= htmlspecialchars($pageTitle ?? 'SGPP-UNS') ?>
        </span>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Usuario y Rol -->
        <div class="text-end d-none d-sm-block">
            <div class="fw-semibold text-dark small" style="line-height: 1.2;">
                <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? $_SESSION['usuario_login'] ?? 'Usuario') ?>
            </div>
            <span class="text-muted" style="font-size: 0.75rem;">
                <?= htmlspecialchars($rolTexto) ?>
            </span>
        </div>

        <!-- Botón de Cerrar Sesión directo y limpio -->
        <a href="logout.php" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-md-inline">Salir</span>
        </a>
    </div>
</header>
<main class="app-main">
    <div class="content-body">
        <?php include __DIR__ . '/alerts.php'; ?>
