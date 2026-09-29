<?php
/**
 * Lista General o Personal de Proyectos - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Usuario.php';

$rol = obtenerRolActual();
$usuarioId = (int)$_SESSION['usuario_id'];

$filtroEstado = $_GET['estado'] ?? null;
$filtroEstudiante = isset($_GET['estudiante']) && $_GET['estudiante'] !== '' ? (int)$_GET['estudiante'] : null;
$filtroFecha = $_GET['fecha'] ?? null;

if ($rol === 'estudiante') {
    // Si es estudiante, SIEMPRE y ÚNICAMENTE ve sus propios proyectos (Aislamiento DR-01)
    $proyectos = Proyecto::obtenerPorEstudiante($usuarioId);
    $listaEstudiantes = [];
} else {
    // Docente, coordinador o autoridad pueden consultar proyectos de todos los estudiantes
    $proyectos = Proyecto::obtenerTodos($filtroEstado, $filtroEstudiante, $filtroFecha);
    $listaEstudiantes = Usuario::obtenerPorRol('estudiante');
}

$pageTitle = ($rol === 'estudiante') ? 'Mis Proyectos Académicos' : 'Proyectos Académicos UNS';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="bi bi-folder-fill text-danger me-2"></i><?= htmlspecialchars($pageTitle) ?>
        </h3>
        <p class="text-muted small mb-0">
            <?= ($rol === 'estudiante') 
                ? 'Lista de proyectos registrados bajo su autoría en el semestre académico.' 
                : 'Gestión y consulta general de proyectos de investigación formativa de la EPISI.' ?>
        </p>
    </div>

    <?php if ($rol === 'estudiante'): ?>
        <a href="nuevo_proyecto.php" class="btn btn-uns-primary btn-sm shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> + Registrar Proyecto
        </a>
    <?php endif; ?>
</div>

<!-- Filtros de búsqueda para Docentes / Coordinadores / Autoridades -->
<?php if ($rol !== 'estudiante'): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="proyectos.php" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filtrar por Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos los estados --</option>
                    <option value="Pendiente" <?= $filtroEstado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En revisión" <?= $filtroEstado === 'En revisión' ? 'selected' : '' ?>>En revisión</option>
                    <option value="Observado" <?= $filtroEstado === 'Observado' ? 'selected' : '' ?>>Observado</option>
                    <option value="Aprobado" <?= $filtroEstado === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Rechazado" <?= $filtroEstado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-bold">Filtrar por Estudiante:</label>
                <select name="estudiante" class="form-select form-select-sm">
                    <option value="">-- Todos los estudiantes --</option>
                    <?php foreach ($listaEstudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres'] . ' (' . ($est['codigo_universitario'] ?? $est['usuario']) . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
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
            <i class="bi bi-card-list text-danger"></i> Registros de Proyectos (<?= count($proyectos) ?> encontrados)
        </h5>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($proyectos)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0">No se encontraron proyectos con los criterios seleccionados.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table uns-table mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Proyecto</th>
                            <?php if ($rol !== 'estudiante'): ?>
                                <th>Estudiante</th>
                            <?php endif; ?>
                            <th>Línea de Inv.</th>
                            <th>Fechas</th>
                            <th>Entregables</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proyectos as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold">
                                    <?= htmlspecialchars($p['codigo_proyecto']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($p['titulo']) ?></strong>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">
                                    <?= htmlspecialchars($p['descripcion']) ?>
                                </small>
                            </td>
                            <?php if ($rol !== 'estudiante'): ?>
                                <td>
                                    <div class="fw-semibold small text-dark">
                                        <?= htmlspecialchars(($p['estudiante_apellidos'] ?? '') . ', ' . ($p['estudiante_nombres'] ?? '')) ?>
                                    </div>
                                    <small class="text-muted">Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?></small>
                                </td>
                            <?php endif; ?>
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
                                    <i class="bi bi-files me-1"></i> <?= (int)($p['total_entregables'] ?? 0) ?>
                                </span>
                            </td>
                            <td>
                                <?= badgeEstado($p['estado']) ?>
                            </td>
                            <td class="text-end">
                                <a href="proyecto.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-uns-outline">
                                    <i class="bi bi-eye-fill me-1"></i> Ver Detalle
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
