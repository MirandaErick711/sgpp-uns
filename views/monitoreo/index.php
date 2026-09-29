<?php
/**
 * Vista del Panel de Monitoreo y Auditoría - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">Monitoreo del Sistema</h4>
            <span class="health-indicator ok">
                <span class="pulse-dot"></span> Operativo
            </span>
        </div>
        <p class="text-muted small mb-0">Supervisión del estado del sistema, integridad de base de datos y trazabilidad de seguridad.</p>
    </div>
    <div>
        <a href="monitoreo.php" class="btn btn-outline-secondary btn-sm" title="Actualizar datos">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
        </a>
    </div>
</div>

<!-- Tarjetas de Diagnóstico del Sistema -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Aplicación Web</h6>
                <p class="kpi-value text-success" style="font-size: 1.35rem;">Operativa</p>
            </div>
            <div class="kpi-icon"><i class="bi bi-globe"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>Base de Datos</h6>
                <p class="kpi-value text-primary" style="font-size: 1.35rem;"><?= $dbHealth['status'] ?></p>
            </div>
            <div class="kpi-icon"><i class="bi bi-database"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card kpi-gold">
            <div class="kpi-info">
                <h6>Almacenamiento</h6>
                <p class="kpi-value" style="font-size: 1.35rem;"><?= $statsArchivos['total_archivos'] ?> docs</p>
            </div>
            <div class="kpi-icon"><i class="bi bi-folder"></i></div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Ecosistema</h6>
                <p class="kpi-value" style="font-size: 1.35rem;"><?= $totalProyectos ?> proyectos</p>
            </div>
            <div class="kpi-icon"><i class="bi bi-people"></i></div>
        </div>
    </div>
</div>

<!-- Tabla de Historial de Acciones y Auditoría -->
<div class="uns-card">
    <div class="uns-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-text text-danger"></i> Bitácora de Acciones Recientes
        </h5>
        
        <div class="btn-group btn-group-sm">
            <a href="monitoreo.php" class="btn <?= empty($filtroResultado) ? 'btn-uns-primary' : 'btn-outline-secondary' ?>">Todas</a>
            <a href="monitoreo.php?resultado=Correcto" class="btn <?= $filtroResultado === 'Correcto' ? 'btn-success' : 'btn-outline-secondary' ?>">Correctas</a>
            <a href="monitoreo.php?resultado=Rechazado" class="btn <?= $filtroResultado === 'Rechazado' ? 'btn-danger' : 'btn-outline-secondary' ?>">Rechazadas</a>
        </div>
    </div>

    <div class="uns-card-body p-0">
        <div class="table-responsive">
            <table class="table uns-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 140px;">Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acción</th>
                        <th>Detalle Técnico</th>
                        <th>IP</th>
                        <th class="text-center" style="width: 100px;">Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventosAuditoria)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted small">No hay eventos de auditoría disponibles con el filtro seleccionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($eventosAuditoria as $ev): ?>
                        <tr class="<?= $ev['resultado'] === 'Rechazado' ? 'table-danger table-opacity-10' : '' ?>">
                            <td class="small text-nowrap text-muted">
                                <?= date('d/m/Y H:i', strtotime($ev['fecha_hora'])) ?>
                            </td>
                            <td>
                                <strong class="small text-dark"><?= htmlspecialchars($ev['nombre_usuario']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">
                                    <?= htmlspecialchars($ev['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold <?= $ev['resultado'] === 'Rechazado' ? 'text-danger' : 'text-dark' ?>">
                                    <?= htmlspecialchars($ev['accion']) ?>
                                </span>
                            </td>
                            <td style="max-width: 300px;">
                                <div class="small text-secondary text-truncate" title="<?= htmlspecialchars($ev['detalle'] ?? '') ?>">
                                    <?= htmlspecialchars($ev['detalle'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="small text-muted font-monospace">
                                <?= htmlspecialchars($ev['ip_origen'] ?? '127.0.0.1') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ev['resultado'] === 'Correcto'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size:0.75rem;">
                                        Correcto
                                    </span>
                                <?php elseif ($ev['resultado'] === 'Rechazado'): ?>
                                    <span class="badge bg-danger text-white px-2 py-0.5" style="font-size:0.75rem;">
                                        Rechazado
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-2 py-0.5" style="font-size:0.75rem;">
                                        <?= htmlspecialchars($ev['resultado']) ?>
                                    </span>
                                <?php endif; ?>
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
