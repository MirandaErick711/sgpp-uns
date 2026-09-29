<?php
/**
 * Dashboard del Estudiante - SGPP-UNS
 */
require_once __DIR__ . '/../../includes/session.php';
requerirRol('estudiante');

require_once __DIR__ . '/../../models/Proyecto.php';
require_once __DIR__ . '/../../models/Observacion.php';

$estudianteId = (int)$_SESSION['usuario_id'];
$stats = Proyecto::resumenEstudiante($estudianteId);
$misProyectos = Proyecto::obtenerPorEstudiante($estudianteId);

$pageTitle = 'Dashboard del Estudiante';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Banner de Bienvenida Institucional -->
<div class="card border-0 mb-4 text-white shadow-sm" 
     style="background: linear-gradient(135deg, var(--uns-red-dark) 0%, var(--uns-red) 70%, var(--uns-gold-dark) 100%); border-radius: 12px;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-white text-danger fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">
                <i class="bi bi-mortarboard-fill me-1"></i> Pregrado EPISI &bull; Cód: <?= htmlspecialchars($_SESSION['usuario_codigo'] ?? 'S/C') ?>
            </span>
            <h3 class="fw-bold mb-1">¡Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombres'] ?? 'Estudiante') ?>!</h3>
            <p class="mb-0 opacity-75 small">
                Sistema de Gestión de Proyectos y Productos Académicos (SGPP-UNS). Administre sus entregables, cargue documentos y consulte observaciones docentes.
            </p>
        </div>
        <div class="d-flex flex-shrink-0 gap-2">
            <a href="nuevo_proyecto.php" class="btn btn-uns-gold px-3 py-2 text-nowrap shadow-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> + Nuevo Proyecto
            </a>
        </div>
    </div>
</div>

<!-- Tarjetas de Métricas Estadísticas (KPIs) -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-info">
                <h6>Proyectos Registrados</h6>
                <p class="kpi-value"><?= (int)($stats['total_proyectos'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-blue">
            <div class="kpi-info">
                <h6>En Revisión</h6>
                <p class="kpi-value"><?= (int)($stats['en_revision'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-arrow-repeat"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-green">
            <div class="kpi-info">
                <h6>Proyectos Aprobados</h6>
                <p class="kpi-value"><?= (int)($stats['aprobados'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-patch-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card kpi-orange">
            <div class="kpi-info">
                <h6>Observaciones Recibidas</h6>
                <p class="kpi-value"><?= (int)($stats['observaciones_pendientes'] ?? 0) ?></p>
            </div>
            <div class="kpi-icon">
                <i class="bi bi-chat-left-dots"></i>
            </div>
        </div>
    </div>
</div>

<!-- Cuadro Explicativo del Driver Arquitectónico DR-01 -->
<div class="card border-0 mb-4 shadow-sm" style="border-left: 5px solid var(--uns-gold) !important; background: #FFFDF7;">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size:0.68rem;">Driver DR-01</span>
                    <strong class="text-danger-emphasis">Demostración de Aislamiento de Proyectos</strong>
                </div>
                <p class="mb-0 text-muted small">
                    El sistema aplica validación estricta en el servidor PHP: un estudiante solo puede consultar o modificar sus propios proyectos.
                    Si intenta acceder a un ID ajeno (por ejemplo alterando la URL a <code>proyecto.php?id=3</code> que es de <strong>estudiante2</strong>), el sistema deniega el acceso y registra la infracción.
                </p>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="proyecto.php?id=3" class="btn btn-outline-danger btn-sm text-nowrap" target="_blank" title="Probar vulnerabilidad alterando ID en la URL">
                    <i class="bi bi-shield-slash me-1"></i> Probar Intento Ajeno (ID=3)
                </a>
                <a href="monitoreo.php" class="btn btn-outline-secondary btn-sm text-nowrap">
                    <i class="bi bi-activity me-1"></i> Monitoreo
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Proyectos del Estudiante -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-journal-bookmark-fill text-danger"></i> Mis Proyectos Académicos
        </h5>
        <a href="nuevo_proyecto.php" class="btn btn-sm btn-uns-primary">
            <i class="bi bi-plus-lg me-1"></i> Registrar Proyecto
        </a>
    </div>
    <div class="uns-card-body p-0">
        <?php if (empty($misProyectos)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-2">Aún no tiene proyectos registrados en el SGPP-UNS.</p>
                <a href="nuevo_proyecto.php" class="btn btn-uns-gold btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Iniciar mi primer proyecto
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Proyecto</th>
                            <th>Línea de Investigación</th>
                            <th>Fechas</th>
                            <th>Entregables</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misProyectos as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold">
                                    <?= htmlspecialchars($p['codigo_proyecto']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($p['titulo']) ?></strong>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">
                                    <?= htmlspecialchars($p['descripcion']) ?>
                                </small>
                            </td>
                            <td>
                                <span class="small text-secondary"><?= htmlspecialchars($p['linea_investigacion']) ?></span>
                            </td>
                            <td>
                                <div class="small">
                                    <div><i class="bi bi-calendar-event me-1 text-muted"></i> <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></div>
                                    <div class="text-muted"><i class="bi bi-calendar-check me-1"></i> <?= date('d/m/Y', strtotime($p['fecha_fin_prevista'])) ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    <i class="bi bi-files me-1"></i> <?= (int)$p['total_entregables'] ?> entregable(s)
                                </span>
                            </td>
                            <td>
                                <?= badgeEstado($p['estado']) ?>
                            </td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-uns-outline">
                                    <i class="bi bi-eye-fill me-1"></i> Ver Proyecto
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
