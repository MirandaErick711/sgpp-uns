<?php
/**
 * Vista de Bandeja General de Entregables - SGPP-UNS
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="bi bi-inbox-fill text-danger me-2"></i>Bandeja de Entregables Académicos
        </h3>
        <p class="text-muted small mb-0">
            Revisión, calificación y registro de observaciones de los productos entregados por los estudiantes.
        </p>
    </div>
</div>

<!-- Filtros de Estado -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="entregables.php" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="small fw-bold text-secondary me-2"><i class="bi bi-funnel-fill text-danger"></i> Filtrar por estado:</span>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="entregables.php" class="btn <?= empty($filtroEstado) ? 'btn-uns-primary' : 'btn-outline-secondary' ?>">Todos</a>
                    <a href="entregables.php?estado=En revisión" class="btn <?= $filtroEstado === 'En revisión' ? 'btn-primary' : 'btn-outline-primary' ?>">En revisión</a>
                    <a href="entregables.php?estado=Pendiente" class="btn <?= $filtroEstado === 'Pendiente' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">Pendiente</a>
                    <a href="entregables.php?estado=Observado" class="btn <?= $filtroEstado === 'Observado' ? 'btn-warning' : 'btn-outline-warning' ?>" style="background-color: <?= $filtroEstado === 'Observado' ? '#fd7e14' : 'transparent' ?>; color: <?= $filtroEstado === 'Observado' ? '#fff' : '#fd7e14' ?>; border-color: #fd7e14;">Observado</a>
                    <a href="entregables.php?estado=Aprobado" class="btn <?= $filtroEstado === 'Aprobado' ? 'btn-success' : 'btn-outline-success' ?>">Aprobado</a>
                    <a href="entregables.php?estado=Rechazado" class="btn <?= $filtroEstado === 'Rechazado' ? 'btn-danger' : 'btn-outline-danger' ?>">Rechazado</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-collection-fill text-danger"></i> Listado de Entregables (<?= count($entregables) ?> registros)
        </h5>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregables)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">No se encontraron entregables con el criterio seleccionado.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Estudiante</th>
                            <th>Entregable</th>
                            <th>Archivos</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregables as $e): ?>
                        <tr>
                            <td>
                                <strong class="small d-block text-dark"><?= htmlspecialchars($e['codigo_proyecto']) ?></strong>
                                <span class="small text-muted text-truncate d-inline-block" style="max-width: 220px;">
                                    <?= htmlspecialchars($e['proyecto_titulo']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold small text-dark">
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
                            <td>
                                <?php if ((int)$e['total_archivos'] > 0): ?>
                                    <span class="badge bg-light text-danger border">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> <?= (int)$e['total_archivos'] ?> doc(s)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Sin archivos</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-nowrap">
                                <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y H:i', strtotime($e['fecha_entrega'])) ?>
                            </td>
                            <td>
                                <?= badgeEstado($e['estado']) ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="proyecto.php?id=<?= (int)$e['proyecto_id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Ver proyecto completo">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <?php if (esRol('docente')): ?>
                                    <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary">
                                        <i class="bi bi-pencil-square me-1"></i> Revisar
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
