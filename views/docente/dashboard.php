<?php
/**
 * Dashboard del Docente Asesor - SGPP-UNS (Simplificado y Limpio)
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('docente');

require_once __DIR__ . '/../../models/Entregable.php';
require_once __DIR__ . '/../../models/Proyecto.php';

$resumenDocente = Entregable::resumenDocente();
$entregablesPendientes = Entregable::obtenerParaDocente('En revisión');

$pageTitle = 'Dashboard del Docente Asesor';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Encabezado de Página -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Docente') ?></h4>
        <p class="text-muted small mb-0">Evaluación y revisión de entregables académicos &bull; EPISI</p>
    </div>
    <div>
        <a href="entregables.php" class="btn btn-uns-primary btn-sm">
            <i class="bi bi-inbox me-1"></i> Bandeja de Entregables
        </a>
    </div>
</div>

<!-- Tarjetas KPI Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="kpi-card kpi-gold">
            <div class="kpi-info">
                <h6>Pendientes</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['pendientes'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-clock"></i></div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aprobados</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        </div>
    </div>
</div>

<!-- Tabla de Entregables Prioritarios -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-inbox text-danger"></i> Entregables por Evaluar
        </h5>
        <a href="entregables.php" class="btn btn-sm btn-link text-decoration-none text-secondary p-0">
            Ver todos los entregables <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregablesPendientes)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-check-circle fs-2 d-block mb-1 text-success opacity-50"></i>
                <p class="mb-0">No hay entregables pendientes de revisión en este momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Estudiante</th>
                            <th>Entregable</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end" style="width: 120px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregablesPendientes as $e): ?>
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
                            </td>
                            <td>
                                <span class="small fw-semibold text-dark">#<?= (int)$e['numero_entregable'] ?> <?= htmlspecialchars($e['titulo']) ?></span>
                            </td>
                            <td class="small text-muted text-nowrap">
                                <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                            </td>
                            <td><?= badgeEstado($e['estado']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary py-1 px-2">
                                    Revisar
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