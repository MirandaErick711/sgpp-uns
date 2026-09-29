<?php
/**
 * Consulta de Observaciones Docentes - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

require_once __DIR__ . '/models/Observacion.php';

$usuarioId = (int)$_SESSION['usuario_id'];
$rol = obtenerRolActual();

if ($rol === 'estudiante') {
    $observaciones = Observacion::obtenerPorEstudiante($usuarioId);
} else {
    // Si es docente u otro, redirigir a entregables
    header("Location: index.php");
    exit;
}

$pageTitle = 'Observaciones de Mis Proyectos';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="bi bi-chat-left-dots-fill text-danger me-2"></i>Observaciones y Retroalimentación
        </h3>
        <p class="text-muted small mb-0">
            Historial de dictámenes, recomendaciones y observaciones emitidas por los docentes evaluadores sobre sus entregables.
        </p>
    </div>
</div>

<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-text text-danger"></i> Dictámenes Docentes Recibidos
        </h5>
        <span class="badge bg-secondary-subtle text-secondary"><?= count($observaciones) ?> registro(s)</span>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($observaciones)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-chat-square-heart fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">No cuenta con observaciones pendientes en sus proyectos.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Proyecto</th>
                            <th>Entregable</th>
                            <th>Docente Evaluador</th>
                            <th>Dictamen</th>
                            <th>Observación / Retroalimentación</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($observaciones as $obs): ?>
                        <tr>
                            <td class="text-nowrap small text-muted">
                                <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y H:i', strtotime($obs['fecha_registro'])) ?>
                            </td>
                            <td>
                                <strong class="small d-block text-dark"><?= htmlspecialchars($obs['codigo_proyecto']) ?></strong>
                                <span class="small text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                    <?= htmlspecialchars($obs['proyecto_titulo']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark"><?= htmlspecialchars($obs['entregable_titulo']) ?></span>
                            </td>
                            <td>
                                <span class="small text-secondary">
                                    <i class="bi bi-person-check-fill text-danger me-1"></i>
                                    Dr. <?= htmlspecialchars($obs['docente_nombres'] . ' ' . $obs['docente_apellidos']) ?>
                                </span>
                            </td>
                            <td>
                                <?= badgeEstado($obs['tipo_decision']) ?>
                            </td>
                            <td style="max-width: 300px;">
                                <div class="small p-2 bg-light rounded border text-secondary">
                                    <?= nl2br(htmlspecialchars($obs['comentario'])) ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$obs['proyecto_id'] ?>" class="btn btn-sm btn-uns-outline">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Ir al Proyecto
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
include __DIR__ . '/includes/footer.php';
?>
