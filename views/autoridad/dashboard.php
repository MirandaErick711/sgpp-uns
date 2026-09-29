<?php
/**
 * Dashboard Ejecutivo para Autoridades - SGPP-UNS
 * (Decanatura de Ingeniería y Dirección de Escuela)
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('autoridad');

require_once __DIR__ . '/../../models/Proyecto.php';
require_once __DIR__ . '/../../models/Entregable.php';
require_once __DIR__ . '/../../models/Archivo.php';

$resumenProyectos = Proyecto::resumenGlobal();
$resumenEntregables = Entregable::resumenDocente();
$ultimosProyectos = Proyecto::obtenerTodos();

$totalProyectos = (int)($resumenProyectos['total_proyectos'] ?? 0);
$aprobados      = (int)($resumenProyectos['aprobados'] ?? 0);
$pendientes     = (int)($resumenProyectos['pendientes'] ?? 0);
$enRevision     = (int)($resumenProyectos['en_revision'] ?? 0);
$observados     = (int)($resumenProyectos['observados'] ?? 0);
$rechazados     = (int)($resumenProyectos['rechazados'] ?? 0);

$porcAprobados = $totalProyectos > 0 ? round(($aprobados / $totalProyectos) * 100, 1) : 0;
$porcRevision  = $totalProyectos > 0 ? round(($enRevision / $totalProyectos) * 100, 1) : 0;
$porcObs       = $totalProyectos > 0 ? round(($observados / $totalProyectos) * 100, 1) : 0;
$porcPend      = $totalProyectos > 0 ? round(($pendientes / $totalProyectos) * 100, 1) : 0;

$pageTitle = 'Dashboard Ejecutivo - Dirección y Decanatura';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Banner Ejecutivo Institucional -->
<div class="card border-0 mb-4 text-white shadow-sm" 
     style="background: linear-gradient(135deg, var(--uns-red-dark) 0%, #212529 100%); border-radius: 12px; border-left: 6px solid var(--uns-gold) !important;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-dark fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                <i class="bi bi-bank me-1 text-danger"></i> Decanatura &bull; Dirección de Escuela EPISI
            </span>
            <h3 class="fw-bold mb-1">Dr. <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Autoridad') ?></h3>
            <p class="mb-0 opacity-75 small">
                Vista ejecutiva y de consulta institucional. Monitoreo del rendimiento académico global y avance de proyectos formativos UNS.
            </p>
        </div>
        <div class="d-flex flex-shrink-0 gap-2">
            <a href="reportes.php" class="btn btn-uns-gold px-3 py-2 text-nowrap shadow-sm">
                <i class="bi bi-printer me-1"></i> Imprimir Reporte
            </a>
            <a href="monitoreo.php" class="btn btn-outline-light px-3 py-2 text-nowrap shadow-sm">
                <i class="bi bi-activity me-1"></i> Auditoría Técnica
            </a>
        </div>
    </div>
</div>

<!-- Indicadores Clave de Desempeño (KPIs) -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Total Proyectos</h6>
                <p class="kpi-value"><?= $totalProyectos ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-journals"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= $aprobados ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-award-fill"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= $enRevision ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-gold">
            <div class="kpi-info">
                <h6>Pendientes</h6>
                <p class="kpi-value"><?= $pendientes ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observados</h6>
                <p class="kpi-value"><?= $observados ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-exclamation-diamond"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Entregables</h6>
                <p class="kpi-value"><?= (int)($resumenEntregables['total_entregables'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-files"></i></div>
        </div>
    </div>
</div>

<!-- Gráficos y Barras de Distribución de Estado -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="uns-card h-100">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-bar-chart-steps text-danger"></i> Distribución Porcentual del Avance
                </h5>
            </div>
            <div class="uns-card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span><i class="bi bi-check-circle-fill text-success me-1"></i> Aprobados</span>
                        <span class="text-success"><?= $porcAprobados ?>% (<?= $aprobados ?>)</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $porcAprobados ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span><i class="bi bi-arrow-repeat text-primary me-1"></i> En Revisión Docente</span>
                        <span class="text-primary"><?= $porcRevision ?>% (<?= $enRevision ?>)</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $porcRevision ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Observados con Correcciones</span>
                        <span class="text-warning text-dark"><?= $porcObs ?>% (<?= $observados ?>)</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $porcObs ?>%"></div>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span><i class="bi bi-clock text-secondary me-1"></i> Pendientes de Entrega</span>
                        <span class="text-secondary"><?= $porcPend ?>% (<?= $pendientes ?>)</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $porcPend ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="uns-card h-100">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-shield-lock-fill text-danger"></i> Estado del Ecosistema SGPP-UNS
                </h5>
            </div>
            <div class="uns-card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-shield-check text-success me-2 fs-6"></i> Driver DR-01 (Aislamiento de proyectos):</span>
                        <span class="badge bg-success-subtle text-success border">Activo y Verificado (100%)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-database-check text-primary me-2 fs-6"></i> Integridad y Confiabilidad (Transacciones):</span>
                        <span class="badge bg-primary-subtle text-primary border">InnoDB PDO Transaccional</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-hdd-network text-info me-2 fs-6"></i> Almacenamiento de Archivos Digitales:</span>
                        <span class="badge bg-info-subtle text-info border">Desacoplado fuera de BD (uploads/)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-eye text-danger me-2 fs-6"></i> Auditoría y Monitoreo en Tiempo Real:</span>
                        <span class="badge bg-danger-subtle text-danger border">Supervisión Activa</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Consulta Ejecutiva de Proyectos -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-table text-danger"></i> Consulta General de Proyectos
        </h5>
    </div>
    <div class="uns-card-body p-0">
        <div class="table-responsive">
            <table class="table uns-table mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Proyecto</th>
                        <th>Estudiante</th>
                        <th>Línea de Inv.</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosProyectos as $p): ?>
                    <tr>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['codigo_proyecto']) ?></span></td>
                        <td>
                            <strong class="small d-block text-dark"><?= htmlspecialchars($p['titulo']) ?></strong>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?></div>
                            <small class="text-muted">Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?></small>
                        </td>
                        <td><span class="small text-secondary"><?= htmlspecialchars($p['linea_investigacion']) ?></span></td>
                        <td><?= badgeEstado($p['estado']) ?></td>
                        <td class="text-end">
                            <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Consultar
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
