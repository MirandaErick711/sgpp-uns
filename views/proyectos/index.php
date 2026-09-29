<?php
/**
 * Vista de Listado de Proyectos - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($pageTitle) ?></h4>
        <p class="text-muted small mb-0">
            <?= ($rol === 'estudiante') 
                ? 'Lista de proyectos registrados bajo su autoría.' 
                : 'Consulta y supervisión de proyectos académicos formativos EPISI.' ?>
        </p>
    </div>

    <?php if ($rol === 'estudiante'): ?>
        <div>
            <a href="nuevo_proyecto.php" class="btn btn-uns-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Registrar Proyecto
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Filtros de búsqueda para Docentes / Coordinadores / Autoridades -->
<?php if ($rol !== 'estudiante'): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="proyectos.php" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos los estados --</option>
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
                    <?php foreach ($listaEstudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">
                    Filtrar
                </button>
                <a href="proyectos.php" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Tabla de Proyectos -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-folder text-danger"></i> Catálogo de Proyectos
        </h5>
        <span class="badge bg-light text-secondary border"><?= count($proyectos) ?> registro(s)</span>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($proyectos)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-folder-x fs-2 d-block mb-1 opacity-50"></i>
                <p class="mb-0">No se encontraron proyectos registrados con los criterios seleccionados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Proyecto</th>
                            <?php if ($rol !== 'estudiante'): ?>
                                <th>Estudiante</th>
                            <?php endif; ?>
                            <th>Línea de Inv.</th>
                            <th class="text-center">Entregables</th>
                            <th>Estado</th>
                            <th class="text-end" style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td class="font-monospace fw-semibold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($p['titulo']) ?></strong>
                            </td>
                            <?php if ($rol !== 'estudiante'): ?>
                                <td>
                                    <div class="small fw-semibold text-dark">
                                        <?= htmlspecialchars(($p['estudiante_apellidos'] ?? '') . ', ' . ($p['estudiante_nombres'] ?? '')) ?>
                                    </div>
                                    <small class="text-muted">Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?></small>
                                </td>
                            <?php endif; ?>
                            <td class="text-muted small"><?= htmlspecialchars($p['linea_investigacion']) ?></td>
                            <td class="text-center small"><?= (int)($p['total_entregables'] ?? 0) ?></td>
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
