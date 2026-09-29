<?php
/**
 * Vista de Bandeja General de Entregables - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bandeja de Entregables</h4>
        <p class="text-muted small mb-0">Revisión y seguimiento de productos académicos entregados por los estudiantes.</p>
    </div>
</div>

<!-- Filtros de Estado Sencillos -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="entregables.php" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="small text-muted me-1">Filtrar por estado:</span>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm">
                    <a href="entregables.php" class="btn <?= empty($filtroEstado) ? 'btn-uns-primary' : 'btn-outline-secondary' ?>">Todos</a>
                    <a href="entregables.php?estado=En revisión" class="btn <?= $filtroEstado === 'En revisión' ? 'btn-primary' : 'btn-outline-secondary' ?>">En revisión</a>
                    <a href="entregables.php?estado=Pendiente" class="btn <?= $filtroEstado === 'Pendiente' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Pendiente</a>
                    <a href="entregables.php?estado=Observado" class="btn <?= $filtroEstado === 'Observado' ? 'btn-warning' : 'btn-outline-secondary' ?>">Observado</a>
                    <a href="entregables.php?estado=Aprobado" class="btn <?= $filtroEstado === 'Aprobado' ? 'btn-success' : 'btn-outline-secondary' ?>">Aprobado</a>
                    <a href="entregables.php?estado=Rechazado" class="btn <?= $filtroEstado === 'Rechazado' ? 'btn-danger' : 'btn-outline-secondary' ?>">Rechazado</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-clipboard-check text-danger"></i> Entregables Registrados
        </h5>
        <span class="badge bg-light text-secondary border"><?= count($entregables) ?> registro(s)</span>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregables)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                <p class="mb-0">No se encontraron entregables con el filtro seleccionado.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Estudiante</th>
                            <th>Entregable</th>
                            <th class="text-center">Archivos</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregables as $e): ?>
                        <tr>
                            <td>
                                <strong class="small d-block text-dark"><?= htmlspecialchars($e['codigo_proyecto']) ?></strong>
                                <span class="small text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                    <?= htmlspecialchars($e['proyecto_titulo']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <?= htmlspecialchars($e['estudiante_apellidos'] . ', ' . $e['estudiante_nombres']) ?>
                                </div>
                                <small class="text-muted">Cód: <?= htmlspecialchars($e['codigo_universitario'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">
                                    <span class="badge bg-light text-secondary border me-1">#<?= (int)$e['numero_entregable'] ?></span>
                                    <?= htmlspecialchars($e['titulo']) ?>
                                </span>
                            </td>
                            <td class="text-center small">
                                <?php if ((int)$e['total_archivos'] > 0): ?>
                                    <span class="badge bg-light text-dark border">
                                        <?= (int)$e['total_archivos'] ?> doc(s)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-nowrap">
                                <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                            </td>
                            <td>
                                <?= badgeEstado($e['estado']) ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="proyecto.php?id=<?= (int)$e['proyecto_id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Ver proyecto">
                                    Ver
                                </a>
                                <?php if (esRol('docente')): ?>
                                    <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary py-1 px-2">
                                        Revisar
                                    </a>
                                <?php endif; ?>
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
