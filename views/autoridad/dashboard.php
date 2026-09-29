<?php
/**
 * Dashboard Ejecutivo para Autoridades - SGPP-UNS (Simplificado y Limpio)
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('autoridad');

require_once __DIR__ . '/../../models/Proyecto.php';
require_once __DIR__ . '/../../models/Entregable.php';

$resumenProyectos = Proyecto::resumenGlobal();
$ultimosProyectos = Proyecto::obtenerTodos();

$totalProyectos = (int)($resumenProyectos['total_proyectos'] ?? 0);
$aprobados      = (int)($resumenProyectos['aprobados'] ?? 0);
$enRevision     = (int)($resumenProyectos['en_revision'] ?? 0);
$observados     = (int)($resumenProyectos['observados'] ?? 0);
$pendientes     = (int)($resumenProyectos['pendientes'] ?? 0);

$porcAprobados = $totalProyectos > 0 ? round(($aprobados / $totalProyectos) * 100, 1) : 0;
$porcRevision  = $totalProyectos > 0 ? round(($enRevision / $totalProyectos) * 100, 1) : 0;
$porcObs       = $totalProyectos > 0 ? round(($observados / $totalProyectos) * 100, 1) : 0;

$pageTitle = 'Dashboard Ejecutivo - Dirección y Decanatura';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Encabezado de Página -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Autoridad') ?></h4>
        <p class="text-muted small mb-0">Decanatura de Ingeniería &bull; Dirección de Escuela EPISI</p>
    </div>
    <div class="d-flex gap-2">
        <a href="reportes.php" class="btn btn-uns-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Imprimir Reporte
        </a>
    </div>
</div>

<!-- Tarjetas KPI Resumen (4 métricas ejecutivas) -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Total Proyectos</h6>
                <p class="kpi-value"><?= $totalProyectos ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-folder"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= $aprobados ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= $enRevision ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observados</h6>
                <p class="kpi-value"><?= $observados ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
        </div>
    </div>
</div>

<!-- Distribución del Avance -->
<div class="uns-card mb-4">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-bar-chart text-danger"></i> Distribución de Estado de Proyectos
        </h5>
        <span class="small text-muted"><?= $totalProyectos ?> proyectos registrados</span>
    </div>
    <div class="uns-card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i> Aprobados</span>
                    <span><?= $porcAprobados ?>% (<?= $aprobados ?>)</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: <?= $porcAprobados ?>%"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-primary"><i class="bi bi-hourglass me-1"></i> En Revisión</span>
                    <span><?= $porcRevision ?>% (<?= $enRevision ?>)</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: <?= $porcRevision ?>%"></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold text-warning"><i class="bi bi-exclamation-circle me-1"></i> Observados</span>
                    <span><?= $porcObs ?>% (<?= $observados ?>)</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: <?= $porcObs ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Proyectos Recientes -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-text text-danger"></i> Proyectos Académicos UNS
        </h5>
        <a href="reportes.php" class="btn btn-sm btn-link text-decoration-none text-secondary p-0">
            Ver informe consolidado <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="uns-card-body p-0">
        <div class="table-responsive">
            <table class="table uns-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 120px;">Código</th>
                        <th>Proyecto</th>
                        <th>Estudiante</th>
                        <th>Línea de Inv.</th>
                        <th>Estado</th>
                        <th class="text-end" style="width: 90px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosProyectos as $p): ?>
                    <tr>
                        <td class="font-monospace fw-semibold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                        <td>
                            <strong class="text-dark d-block"><?= htmlspecialchars($p['titulo']) ?></strong>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?></div>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($p['linea_investigacion']) ?></td>
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
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
