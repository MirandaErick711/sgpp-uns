<?php
/**
 * Vista de Reportes de Avance Académico - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-print-none mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Reporte Consolidado de Proyectos</h4>
        <p class="text-muted small mb-0">Informe académico para comités de evaluación y decanatura.</p>
    </div>
    <div>
        <button type="button" class="btn btn-uns-primary btn-sm" onclick="window.print();">
            <i class="bi bi-printer me-1"></i> Imprimir Reporte
        </button>
    </div>
</div>

<!-- Filtros (ocultos al imprimir) -->
<div class="card border-0 shadow-sm mb-3 d-print-none">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="reportes.php" class="row g-2 align-items-end">
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
            <div class="col-md-4">
                <label class="form-label small">Estudiante:</label>
                <select name="estudiante" class="form-select form-select-sm">
                    <option value="">-- Todos los alumnos --</option>
                    <?php foreach ($estudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Fecha desde:</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroFecha ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">Filtrar</button>
                <a href="reportes.php" class="btn btn-outline-secondary btn-sm" title="Limpiar"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Contenido del Reporte -->
<div class="uns-card p-4 bg-white">
    <!-- Encabezado Institucional UNS para impresión -->
    <div class="text-center pb-3 mb-3 border-bottom">
        <h5 class="fw-bold mb-0 text-dark">UNIVERSIDAD NACIONAL DEL SANTA</h5>
        <div class="small text-secondary mb-1">FACULTAD DE INGENIERÍA &bull; ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMAS E INFORMÁTICA</div>
        <div class="small text-muted">Sistema de Gestión de Proyectos y Productos Académicos (SGPP-UNS)</div>
        <div class="text-muted mt-1" style="font-size: 0.75rem;">Emitido el <?= date('d/m/Y H:i') ?> &bull; Usuario: <?= htmlspecialchars($_SESSION['usuario_login'] ?? '') ?></div>
    </div>

    <!-- Resumen Cuantitativo -->
    <div class="row g-2 mb-3 text-center">
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Total</div>
                <strong class="text-dark"><?= (int)($resumen['total_proyectos'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Aprobados</div>
                <strong class="text-success"><?= (int)($resumen['aprobados'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">En Revisión</div>
                <strong class="text-primary"><?= (int)($resumen['en_revision'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Observados</div>
                <strong class="text-warning-emphasis"><?= (int)($resumen['observados'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Pendientes</div>
                <strong class="text-secondary"><?= (int)($resumen['pendientes'] ?? 0) ?></strong>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 100px;">Código</th>
                    <th>Título del Proyecto</th>
                    <th>Estudiante</th>
                    <th>Línea de Inv.</th>
                    <th>Fechas</th>
                    <th>Entregables</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proyectos)): ?>
                    <tr><td colspan="7" class="text-center py-3 text-muted">No hay registros con los filtros indicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($proyectos as $p): ?>
                    <tr>
                        <td class="text-center font-monospace fw-semibold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                        <td><strong><?= htmlspecialchars($p['titulo']) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?>
                            <small class="text-muted d-block">(Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?>)</small>
                        </td>
                        <td><?= htmlspecialchars($p['linea_investigacion']) ?></td>
                        <td class="text-center small">
                            <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($p['fecha_fin_prevista'])) ?>
                        </td>
                        <td class="text-center"><?= (int)$p['total_entregables'] ?></td>
                        <td class="text-center"><?= badgeEstado($p['estado']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3 pt-3 border-top text-center text-muted small d-print-block" style="font-size: 0.75rem;">
        Documento oficial generado para fines de evaluación y seguimiento académico en la Universidad Nacional del Santa.
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
