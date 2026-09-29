<?php
/**
 * Vista de Consulta de Observaciones Docentes - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Observaciones y Retroalimentación</h4>
        <p class="text-muted small mb-0">Dictámenes y comentarios de los docentes evaluadores sobre sus entregables.</p>
    </div>
</div>

<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-chat-square-text text-danger"></i> Evaluaciones Recibidas
        </h5>
        <span class="badge bg-light text-secondary border"><?= count($observaciones) ?> registro(s)</span>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($observaciones)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-chat-square-check fs-2 d-block mb-1 opacity-50"></i>
                <p class="mb-0">No cuenta con observaciones registradas en sus proyectos.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Fecha</th>
                            <th>Proyecto</th>
                            <th>Entregable</th>
                            <th>Docente</th>
                            <th>Dictamen</th>
                            <th>Observación</th>
                            <th class="text-end" style="width: 100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($observaciones as $obs): ?>
                        <tr>
                            <td class="text-nowrap small text-muted">
                                <?= date('d/m/Y', strtotime($obs['fecha_registro'])) ?>
                            </td>
                            <td>
                                <strong class="small d-block text-dark"><?= htmlspecialchars($obs['codigo_proyecto']) ?></strong>
                                <span class="small text-muted text-truncate d-inline-block" style="max-width: 180px;">
                                    <?= htmlspecialchars($obs['proyecto_titulo']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark"><?= htmlspecialchars($obs['entregable_titulo']) ?></span>
                            </td>
                            <td class="small text-secondary">
                                Dr. <?= htmlspecialchars($obs['docente_apellidos']) ?>
                            </td>
                            <td>
                                <?= badgeEstado($obs['tipo_decision']) ?>
                            </td>
                            <td style="max-width: 280px;">
                                <div class="small text-secondary">
                                    <?= nl2br(htmlspecialchars($obs['comentario'])) ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$obs['proyecto_id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
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
