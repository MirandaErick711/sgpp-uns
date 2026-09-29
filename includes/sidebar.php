<?php
/**
 * Sidebar del SGPP-UNS - Navegación adaptable y simplificada por rol
 */
$rol = obtenerRolActual();
$paginaActual = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <img src="assets/img/logo_uns.png" alt="Logo UNS">
        <div class="brand-text">
            <span class="brand-title">SGPP-UNS</span>
            <span class="brand-sub">UNS &bull; EPISI</span>
        </div>
    </div>

    <div class="sidebar-nav">
        <!-- INICIO COMÚN -->
        <a href="index.php" class="sidebar-link <?= ($paginaActual === 'index' || $paginaActual === 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Inicio
        </a>

        <?php if ($rol === 'estudiante'): ?>
            <!-- ESTUDIANTE -->
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-folder"></i> Mis Proyectos
            </a>
            <a href="nuevo_proyecto.php" class="sidebar-link <?= ($paginaActual === 'nuevo_proyecto') ? 'active' : '' ?>">
                <i class="bi bi-plus-circle"></i> Nuevo Proyecto
            </a>
            <a href="observaciones.php" class="sidebar-link <?= ($paginaActual === 'observaciones') ? 'active' : '' ?>">
                <i class="bi bi-chat-square-text"></i> Observaciones
            </a>

        <?php elseif ($rol === 'docente'): ?>
            <!-- DOCENTE -->
            <a href="entregables.php" class="sidebar-link <?= ($paginaActual === 'entregables' || $paginaActual === 'revisar') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i> Entregables
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-folder"></i> Proyectos
            </a>

        <?php elseif ($rol === 'coordinador'): ?>
            <!-- COORDINADOR -->
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-folder"></i> Proyectos
            </a>
            <a href="reportes.php" class="sidebar-link <?= ($paginaActual === 'reportes') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>

        <?php elseif ($rol === 'autoridad'): ?>
            <!-- AUTORIDAD -->
            <a href="reportes.php" class="sidebar-link <?= ($paginaActual === 'reportes') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>
            <a href="proyectos.php" class="sidebar-link <?= ($paginaActual === 'proyectos' || $paginaActual === 'proyecto') ? 'active' : '' ?>">
                <i class="bi bi-folder"></i> Proyectos
            </a>
        <?php endif; ?>

        <!-- MONITOREO DEL SISTEMA -->
        <?php if ($rol !== 'estudiante' && $rol !== 'docente'): ?>
            <a href="monitoreo.php" class="sidebar-link <?= ($paginaActual === 'monitoreo') ? 'active' : '' ?>">
                <i class="bi bi-activity"></i> Monitoreo
            </a>
        <?php endif; ?>

        <!-- SEPARADOR -->
        <div class="sidebar-divider mt-auto"></div>

        <!-- CERRAR SESIÓN -->
        <a href="logout.php" class="sidebar-link text-white-50">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>
</aside>
