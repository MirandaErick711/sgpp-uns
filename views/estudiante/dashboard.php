<?php
/**
 * Dashboard del Estudiante - SGPP-UNS (Simplificado y Limpio)
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('estudiante');

require_once __DIR__ . '/../../models/Proyecto.php';
require_once __DIR__ . '/../../models/Observacion.php';

$estudianteId = (int)$_SESSION['usuario_id'];
$stats = Proyecto::resumenEstudiante($estudianteId);
$misProyectos = Proyecto::obtenerPorEstudiante($estudianteId);

$pageTitle = 'Dashboard del Estudiante';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Encabezado de Página -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombres'] ?? 'Estudiante') ?></h4>
        <p class="text-muted small mb-0">Gestión de proyectos académicos y entregables &bull; Semestre 2026-II</p>
    </div>
    <div>
        <a href="nuevo_proyecto.php" class="btn btn-uns-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proyecto
        </a>
    </div>
</div>

<!-- Tarjetas KPI Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Proyectos</h6>
                <p class="kpi-value"><?= (int)($stats['total_proyectos'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-folder"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($stats['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= (int)($stats['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observaciones</h6>
                <p class="kpi-value"><?= (int)($stats['observaciones_pendientes'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-chat-text"></i></div>
        </div>
    </div>
</div>

<!-- Tabla de Proyectos Recientes -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-text text-danger"></i> Mis Proyectos
        </h5>
        <a href="proyectos.php" class="btn btn-sm btn-link text-decoration-none text-secondary p-0">
            Ver catálogo completo <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($misProyectos)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-folder-x fs-2 d-block mb-1 opacity-50"></i>
                <p class="mb-2">Aún no tiene proyectos registrados.</p>
                <a href="nuevo_proyecto.php" class="btn btn-uns-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Registrar mi primer proyecto
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Proyecto</th>
                            <th>Línea de Inv.</th>
                            <th class="text-center">Entregables</th>
                            <th>Estado</th>
                            <th class="text-end" style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misProyectos as $p): ?>
                        <tr>
                            <td class="font-monospace fw-semibold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($p['titulo']) ?></strong>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($p['linea_investigacion']) ?></td>
                            <td class="text-center small"><?= (int)$p['total_entregables'] ?></td>
                            <td><?= badgeEstado($p['estado']) ?></td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
