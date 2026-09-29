<?php
/**
 * Sidebar del SGPP-UNS - Navegación adaptable por rol
 */
$rol = obtenerRolActual();
$paginaActual = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <img src="assets/img/logo_uns.png" alt="Logo UNS">
        <div class="brand-text">
            <h1 class="brand-title">SGPP-UNS</h1>
            <span class="brand-sub">Univ. Nac. del Santa</span>
        </div>
    </div>

    <div class="sidebar-nav">
        
        <?php if ($rol === 'estudiante'): ?>
            <!-- NAVEGACIÓN ESTUDIANTE -->
            <div class="nav-section-title">Panel de Estudiante</div>
            <a href="index.php" class="sidebar-link <?= ($paginaActual === 'index' || $paginaActual === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-folder-fill"></i> Mis Proyectos
            </a>
            <a href="nuevo_proyecto.php" class="sidebar-link <?= ($paginaActual === 'nuevo_proyecto') ? 'active' : '' ?>">
                <i class="bi bi-plus-circle-fill"></i> + Nuevo Proyecto
            </a>
            <a href="observaciones.php" class="sidebar-link <?= ($paginaActual === 'observaciones') ? 'active' : '' ?>">
                <i class="bi bi-chat-left-dots-fill"></i> Observaciones
            </a>

        <?php elseif ($rol === 'docente'): ?>
            <!-- NAVEGACIÓN DOCENTE -->
            <div class="nav-section-title">Panel de Docente Asesor</div>
            <a href="index.php" class="sidebar-link <?= ($paginaActual === 'index' || $paginaActual === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="entregables.php" class="sidebar-link <?= ($paginaActual === 'entregables' || $paginaActual === 'revisar') ? 'active' : '' ?>">
                <i class="bi bi-inbox-fill"></i> Entregables a Revisar
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-collection-fill"></i> Proyectos Académicos
            </a>

        <?php elseif ($rol === 'coordinador'): ?>
            <!-- NAVEGACIÓN COORDINADOR -->
            <div class="nav-section-title">Coordinación de Proyectos</div>
            <a href="index.php" class="sidebar-link <?= ($paginaActual === 'index' || $paginaActual === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-kanban-fill"></i> Todos los Proyectos
            </a>
            <a href="reportes.php" class="sidebar-link <?= ($paginaActual === 'reportes') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph-fill"></i> Reportes de Avance
            </a>

        <?php elseif ($rol === 'autoridad'): ?>
            <!-- NAVEGACIÓN AUTORIDAD -->
            <div class="nav-section-title">Dirección y Decanatura</div>
            <a href="index.php" class="sidebar-link <?= ($paginaActual === 'index' || $paginaActual === 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard Ejecutivo
            </a>
            <a href="reportes.php" class="sidebar-link <?= ($paginaActual === 'reportes') ? 'active' : '' ?>">
                <i class="bi bi-pie-chart-fill"></i> Reportes Generales
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-journals"></i> Proyectos UNS
            </a>
        <?php endif; ?>

        <!-- SECCIÓN COMÚN: MONITOREO Y ARQUITECTURA -->
        <div class="nav-section-title">Supervisión y Arquitectura</div>
        <a href="monitoreo.php" class="sidebar-link <?= ($paginaActual === 'monitoreo') ? 'active' : '' ?>">
            <i class="bi bi-activity"></i> Monitoreo (DR-01)
        </a>

        <!-- ACCIONES RÁPIDAS -->
        <div class="nav-section-title">Cuenta</div>
        <a href="logout.php" class="sidebar-link text-danger-emphasis">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>

    <!-- PIE DEL SIDEBAR CON USUARIO ACTUAL -->
    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="user-avatar-circle">
                <?= strtoupper(substr($_SESSION['usuario_nombres'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-truncate text-white" style="font-size: 0.85rem;">
                    <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Usuario') ?>
                </div>
                <div class="text-uppercase" style="font-size: 0.7rem; color: var(--uns-gold-light); letter-spacing: 0.5px;">
                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars(obtenerRolActual() ?? 'Rol') ?>
                </div>
            </div>
        </div>
    </div>
</aside>
