<?php
/**
 * Dashboard del Coordinador Académico - SGPP-UNS (Simplificado y Limpio)
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

<!-- Encabezado de Página -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Coordinador') ?></h4>
        <p class="text-muted small mb-0">Supervisión general de avance de proyectos formativos &bull; EPISI</p>
    </div>
    <div class="d-flex gap-2">
        <a href="reportes.php" class="btn btn-uns-primary btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Reportes
        </a>
    </div>
</div>

<!-- Tarjetas KPI Resumen (4 métricas esenciales) -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Total Proyectos</h6>
                <p class="kpi-value"><?= (int)($resumen['total_proyectos'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-folder"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($resumen['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= (int)($resumen['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observados</h6>
                <p class="kpi-value"><?= (int)($resumen['observados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
        </div>
    </div>
</div>

<!-- Filtros Compactos -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="Pendiente" <?= $filtroEstado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En revisión" <?= $filtroEstado === 'En revisión' ? 'selected' : '' ?>>En revisión</option>
                    <option value="Observado" <?= $filtroEstado === 'Observado' ? 'selected' : '' ?>>Observado</option>
                    <option value="Aprobado" <?= $filtroEstado === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Rechazado" <?= $filtroEstado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label small">Estudiante:</label>
                <select name="estudiante" class="form-select form-select-sm">
                    <option value="">-- Todos los estudiantes --</option>
                    <?php foreach ($estudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">
                    Filtrar
                </button>
                <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Avance de Proyectos -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-list-check text-danger"></i> Avance de Proyectos
        </h5>
        <a href="reportes.php" class="btn btn-sm btn-link text-decoration-none text-secondary p-0">
            Vista de reporte <i class="bi bi-arrow-right"></i>
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
                        <th class="text-center">Entregables</th>
                        <th>Estado</th>
                        <th class="text-end" style="width: 90px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proyectos)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted small">No se encontraron proyectos con los filtros seleccionados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td class="font-monospace fw-semibold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($p['titulo']) ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($p['linea_investigacion']) ?></small>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?></div>
                            </td>
                            <td class="text-center small"><?= (int)$p['total_entregables'] ?></td>
                            <td><?= badgeEstado($p['estado']) ?></td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    Ver
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
