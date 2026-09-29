<?php
/**
 * Dashboard del Docente Asesor - SGPP-UNS
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

<!-- Banner de Bienvenida -->
<div class="card border-0 mb-4 text-white shadow-sm" 
     style="background: linear-gradient(135deg, var(--uns-red-dark) 0%, var(--uns-red) 75%, #0A58CA 100%); border-radius: 12px;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-danger fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                <i class="bi bi-person-workspace me-1"></i> Docente Asesor / Revisor &bull; EPISI
            </span>
            <h3 class="fw-bold mb-1">Dr. <?= htmlspecialchars($_SESSION['usuario_nombre_completo'] ?? 'Docente') ?></h3>
            <p class="mb-0 opacity-75 small">
                Gestión y evaluación de entregables académicos. Revise productos, emita observaciones y determine la aprobación de proyectos.
            </p>
        </div>
        <div class="d-flex flex-shrink-0 gap-2">
            <a href="entregables.php" class="btn btn-uns-gold px-3 py-2 text-nowrap shadow-sm">
                <i class="bi bi-inbox-fill me-1"></i> Bandeja de Entregables
            </a>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas Docente -->
<div class="row g-3 mb-4 mx-0">
    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-gold">
            <div class="kpi-info">
                <h6>Pendientes</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['pendientes'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Entregables Aprobados</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observados</h6>
                <p class="kpi-value"><?= (int)($resumenDocente['observados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-exclamation-diamond-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Entregables para Revisar -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-inbox-fill text-danger"></i> Entregables Prioritarios para Evaluación
        </h5>
        <a href="entregables.php" class="btn btn-sm btn-outline-secondary">
            Ver Todos los Entregables <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregablesPendientes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check2-all fs-1 d-block mb-2 text-success opacity-50"></i>
                <p class="mb-0">¡Al día! No hay entregables pendientes de revisión en este momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Proyecto</th>
                            <th>Estudiante</th>
                            <th>Entregable</th>
                            <th>Fecha de Entrega</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entregablesPendientes as $e): ?>
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
                                <?php if ((int)$e['total_archivos'] > 0): ?>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-paperclip text-danger"></i> <?= (int)$e['total_archivos'] ?> archivo(s) adjunto(s)
                                    </div>
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
                                <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Revisar
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