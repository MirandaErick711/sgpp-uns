<?php
/**
 * Módulo de Monitoreo del Sistema y Auditoría de Seguridad - SGPP-UNS
 * Visualización del estado de la arquitectura y supervisión del Driver DR-01
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Auditoria.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Archivo.php';

// Verificación de salud de la base de datos y aplicación
$dbHealth = Database::checkHealth();
$totalUsuarios = Usuario::contarTotal();
$totalProyectos = (int)(Proyecto::resumenGlobal()['total_proyectos'] ?? 0);
$statsArchivos = Archivo::estadisticasAlmacenamiento();

$filtroResultado = $_GET['resultado'] ?? null;
$eventosAuditoria = Auditoria::obtenerUltimos(60, $filtroResultado);

$pageTitle = 'Monitoreo del Sistema y Arquitectura';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="bi bi-activity text-danger me-1"></i> Panel de Monitoreo y Supervisión
            </h3>
            <span class="health-indicator ok">
                <span class="pulse-dot"></span> Operativo
            </span>
        </div>
        <p class="text-muted small mb-0">
            Supervisión arquitectónica en tiempo real, integridad de base de datos, almacenamiento y trazabilidad de seguridad.
        </p>
    </div>

    <!-- Botón de prueba en vivo para el Driver DR-01 -->
    <div class="d-flex gap-2">
        <a href="monitoreo.php" class="btn btn-light btn-sm border" title="Actualizar datos">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </a>
    </div>
</div>

<!-- Tarjetas de Diagnóstico y Salud del Sistema -->
<div class="row g-3 mb-4">
    <!-- Estado Aplicación -->
    <div class="col-xl-3 col-sm-6">
        <div class="uns-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase">Aplicación Web</span>
                <i class="bi bi-globe2 text-success fs-4"></i>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="fw-bold text-success mb-0">Operativa</h4>
                <span class="badge bg-success-subtle text-success border">HTTP 200</span>
            </div>
            <div class="small text-muted mt-2">
                <i class="bi bi-cpu me-1"></i> PHP <?= phpversion() ?> en Apache XAMPP
            </div>
        </div>
    </div>

    <!-- Estado Base de Datos -->
    <div class="col-xl-3 col-sm-6">
        <div class="uns-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase">Base de Datos MySQL</span>
                <i class="bi bi-database-check text-primary fs-4"></i>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="fw-bold <?= $dbHealth['ok'] ? 'text-primary' : 'text-danger' ?> mb-0">
                    <?= $dbHealth['status'] ?>
                </h4>
                <span class="badge bg-primary-subtle text-primary border">PDO Activo</span>
            </div>
            <div class="small text-muted mt-2 text-truncate">
                <i class="bi bi-hdd-stack me-1"></i> <?= htmlspecialchars($dbHealth['version']) ?>
            </div>
        </div>
    </div>

    <!-- Almacenamiento Físico de Archivos -->
    <div class="col-xl-3 col-sm-6">
        <div class="uns-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase">Archivos Almacenados</span>
                <i class="bi bi-folder-check text-warning fs-4"></i>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <h4 class="fw-bold text-dark mb-0"><?= $statsArchivos['total_archivos'] ?> docs</h4>
                <span class="badge bg-light text-secondary border"><?= $statsArchivos['espacio_legible'] ?></span>
            </div>
            <div class="small text-muted mt-2">
                <i class="bi bi-folder2 me-1"></i> Desacoplado en <code>uploads/</code>
            </div>
        </div>
    </div>

    <!-- Usuarios y Proyectos Activos -->
    <div class="col-xl-3 col-sm-6">
        <div class="uns-card p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-bold text-uppercase">Carga del Ecosistema</span>
                <i class="bi bi-people-fill text-danger fs-4"></i>
            </div>
            <div class="d-flex align-items-baseline gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0"><?= $totalUsuarios ?></h5>
                    <span class="text-muted" style="font-size:0.75rem;">Usuarios</span>
                </div>
                <div class="border-start ps-3">
                    <h5 class="fw-bold text-dark mb-0"><?= $totalProyectos ?></h5>
                    <span class="text-muted" style="font-size:0.75rem;">Proyectos</span>
                </div>
            </div>
            <div class="small text-muted mt-2">
                <i class="bi bi-shield-lock me-1"></i> 4 roles configurados
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Historial de Acciones y Auditoría -->
<div class="uns-card">
    <div class="uns-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-text text-danger"></i> Bitácora de Acciones Recientes del Sistema
        </h5>
        
        <div class="btn-group btn-group-sm">
            <a href="monitoreo.php" class="btn <?= empty($filtroResultado) ? 'btn-uns-primary' : 'btn-outline-secondary' ?>">Todas</a>
            <a href="monitoreo.php?resultado=Correcto" class="btn <?= $filtroResultado === 'Correcto' ? 'btn-success' : 'btn-outline-success' ?>">Correctas</a>
            <a href="monitoreo.php?resultado=Rechazado" class="btn <?= $filtroResultado === 'Rechazado' ? 'btn-danger' : 'btn-outline-danger' ?>">Rechazadas</a>
        </div>
    </div>

    <div class="uns-card-body p-0">
        <div class="table-responsive">
            <table class="table uns-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 160px;">Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acción Realizada</th>
                        <th>Detalle Técnico</th>
                        <th>IP Origen</th>
                        <th class="text-center">Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventosAuditoria)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No hay registros de auditoría disponibles con el filtro seleccionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($eventosAuditoria as $ev): ?>
                        <tr class="<?= $ev['resultado'] === 'Rechazado' ? 'table-danger table-opacity-10' : '' ?>">
                            <td class="small text-nowrap">
                                <i class="bi bi-clock me-1 text-muted"></i>
                                <?= date('d/m/Y H:i:s', strtotime($ev['fecha_hora'])) ?>
                            </td>
                            <td>
                                <strong class="small text-dark"><?= htmlspecialchars($ev['nombre_usuario']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border text-uppercase" style="font-size:0.7rem;">
                                    <?= htmlspecialchars($ev['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold small <?= $ev['resultado'] === 'Rechazado' ? 'text-danger' : 'text-dark' ?>">
                                    <?php if ($ev['resultado'] === 'Rechazado'): ?>
                                        <i class="bi bi-shield-x me-1"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($ev['accion']) ?>
                                </span>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="small text-secondary text-truncate" title="<?= htmlspecialchars($ev['detalle'] ?? '') ?>">
                                    <?= htmlspecialchars($ev['detalle'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="small text-muted font-monospace">
                                <?= htmlspecialchars($ev['ip_origen'] ?? '127.0.0.1') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ev['resultado'] === 'Correcto'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Correcto
                                    </span>
                                <?php elseif ($ev['resultado'] === 'Rechazado'): ?>
                                    <span class="badge bg-danger text-white px-2 py-1 shadow-sm">
                                        <i class="bi bi-x-octagon-fill me-1"></i> Rechazado
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-2 py-1">
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
include __DIR__ . '/includes/footer.php';
?>
