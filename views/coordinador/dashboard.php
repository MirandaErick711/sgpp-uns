<?php
/**
 * Dashboard del Coordinador Académico - SGPP-UNS
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('coordinador');

require_once __DIR__ . '/../../models/Proyecto.php';
require_once __DIR__ . '/../../models/Usuario.php';

$resumen = Proyecto::resumenGlobal();

$filtroEstado = $_GET['estado'] ?? null;
$filtroEstudiante = isset($_GET['estudiante']) && $_GET['estudiante'] !== '' ? (int)$_GET['estudiante'] : null;
$filtroFecha = $_GET['fecha'] ?? null;

$proyectos = Proyecto::obtenerTodos($filtroEstado, $filtroEstudiante, $filtroFecha);
$estudiantes = Usuario::obtenerPorRol('estudiante');

$pageTitle = 'Coordinación de Proyectos';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Banner de Bienvenida -->
<div class="card border-0 mb-4 text-white shadow-sm" 
     style="background: linear-gradient(135deg, var(--uns-red-dark) 0%, var(--uns-red) 75%, #198754 100%); border-radius: 12px;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-success fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                <i class="bi bi-briefcase-fill me-1"></i> Coordinación de Proyectos e Investigación &bull; EPISI
            </span>
            <h3 class="fw-bold mb-1">Mag. <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Coordinador') ?></h3>
            <p class="mb-0 opacity-75 small">
                Supervisión del avance académico, reportes de estado y monitoreo del flujo de entregables de los estudiantes santeños.
            </p>
        </div>
        <div class="d-flex flex-shrink-0 gap-2">
            <a href="reportes.php" class="btn btn-uns-gold px-3 py-2 text-nowrap shadow-sm">
                <i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Ver Reporte General
            </a>
            <a href="monitoreo.php" class="btn btn-light px-3 py-2 text-nowrap shadow-sm text-dark">
                <i class="bi bi-activity text-danger me-1"></i> Monitoreo
            </a>
        </div>
    </div>
</div>

<!-- Tarjetas de Métricas Estadísticas Generales -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Total Proyectos</h6>
                <p class="kpi-value"><?= (int)($resumen['total_proyectos'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-folder2"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-gold">
            <div class="kpi-info">
                <h6>Pendientes</h6>
                <p class="kpi-value"><?= (int)($resumen['pendientes'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-clock"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($resumen['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= (int)($resumen['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-check-all"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observados</h6>
                <p class="kpi-value"><?= (int)($resumen['observados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="kpi-card kpi-danger">
            <div class="kpi-info">
                <h6>Rechazados</h6>
                <p class="kpi-value"><?= (int)($resumen['rechazados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-x-circle"></i></div>
        </div>
    </div>
</div>

<!-- Filtros de Reporte de Avance -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos los estados --</option>
                    <option value="Pendiente" <?= $filtroEstado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En revisión" <?= $filtroEstado === 'En revisión' ? 'selected' : '' ?>>En revisión</option>
                    <option value="Observado" <?= $filtroEstado === 'Observado' ? 'selected' : '' ?>>Observado</option>
                    <option value="Aprobado" <?= $filtroEstado === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Rechazado" <?= $filtroEstado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-bold">Estudiante:</label>
                <select name="estudiante" class="form-select form-select-sm">
                    <option value="">-- Todos los estudiantes --</option>
                    <?php foreach ($estudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres'] . ' (' . ($est['codigo_universitario'] ?? $est['usuario']) . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha desde:</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroFecha ?? '') ?>">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Reporte de Avance General -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-table text-danger"></i> Reporte de Avance de Proyectos
        </h5>
        <a href="reportes.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Vista Imprimible
        </a>
    </div>

    <div class="uns-card-body p-0">
        <div class="table-responsive">
            <table class="table uns-table mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Proyecto</th>
                        <th>Estudiante</th>
                        <th>Entregables</th>
                        <th>Estado</th>
                        <th>Última Actualización</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proyectos)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron proyectos con los filtros seleccionados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($p['codigo_proyecto']) ?></span>
                            </td>
                            <td>
                                <strong class="small d-block text-dark"><?= htmlspecialchars($p['titulo']) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($p['linea_investigacion']) ?></small>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?></div>
                                <small class="text-muted">Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?= (int)$p['total_entregables'] ?> entregable(s)
                                </span>
                            </td>
                            <td><?= badgeEstado($p['estado']) ?></td>
                            <td class="small text-muted text-nowrap">
                                <i class="bi bi-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($p['updated_at'] ?? $p['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-uns-outline">
                                    <i class="bi bi-eye"></i> Detalle
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
