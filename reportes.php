<?php
/**
 * Reportes de Avance Académico - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

// Permitido para coordinador, autoridad y docente
$rol = obtenerRolActual();
if (!in_array($rol, ['coordinador', 'autoridad', 'docente'])) {
    http_response_code(403);
    include __DIR__ . '/views/error/403.php';
    exit;
}

require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Usuario.php';

$filtroEstado = $_GET['estado'] ?? null;
$filtroEstudiante = isset($_GET['estudiante']) && $_GET['estudiante'] !== '' ? (int)$_GET['estudiante'] : null;
$filtroFecha = $_GET['fecha'] ?? null;

$proyectos = Proyecto::obtenerTodos($filtroEstado, $filtroEstudiante, $filtroFecha);
$estudiantes = Usuario::obtenerPorRol('estudiante');
$resumen = Proyecto::resumenGlobal();

$pageTitle = 'Reporte de Avance de Proyectos';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="d-print-none mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">
            <i class="bi bi-file-earmark-bar-graph-fill text-danger me-2"></i>Reporte Consolidado de Proyectos
        </h3>
        <p class="text-muted small mb-0">Informe académico para comités de evaluación, decanatura y acreditación ICACIT.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-uns-primary btn-sm px-3 shadow-sm" onclick="window.print();">
            <i class="bi bi-printer-fill me-1"></i> Imprimir Reporte
        </button>
    </div>
</div>

<!-- Filtros interactivos (ocultos al imprimir) -->
<div class="card border-0 shadow-sm mb-4 d-print-none">
    <div class="card-body p-3 bg-white">
        <form method="GET" action="reportes.php" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Estado:</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="Pendiente" <?= $filtroEstado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En revisión" <?= $filtroEstado === 'En revisión' ? 'selected' : '' ?>>En revisión</option>
                    <option value="Observado" <?= $filtroEstado === 'Observado' ? 'selected' : '' ?>>Observado</option>
                    <option value="Aprobado" <?= $filtroEstado === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Rechazado" <?= $filtroEstado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Estudiante:</label>
                <select name="estudiante" class="form-select form-select-sm">
                    <option value="">-- Todos los alumnos --</option>
                    <?php foreach ($estudiantes as $est): ?>
                        <option value="<?= $est['id'] ?>" <?= $filtroEstudiante === (int)$est['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha desde:</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroFecha ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-uns-primary btn-sm flex-fill">Filtrar</button>
                <a href="reportes.php" class="btn btn-outline-secondary btn-sm" title="Limpiar"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Contenido del Reporte (visible en pantalla y optimizado para imprimir) -->
<div class="uns-card p-4 bg-white">
    <!-- Encabezado Institucional UNS para impresión -->
    <div class="text-center pb-3 mb-3 border-bottom">
        <h4 class="fw-bold mb-0 text-dark" style="font-family: 'Cinzel', serif;">UNIVERSIDAD NACIONAL DEL SANTA</h4>
        <h6 class="text-secondary mb-1">FACULTAD DE INGENIERÍA &bull; ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMAS E INFORMÁTICA</h6>
        <p class="small text-muted mb-0">Sistema de Gestión de Proyectos y Productos Académicos (SGPP-UNS)</p>
        <span class="badge bg-light text-dark border mt-2">Reporte emitido el <?= date('d/m/Y H:i:s') ?> &bull; Usuario: <?= htmlspecialchars($_SESSION['usuario_login'] ?? '') ?></span>
    </div>

    <!-- Resumen Cuantitativo -->
    <div class="row g-2 mb-4 text-center">
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Total</div>
                <strong><?= (int)($resumen['total_proyectos'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Aprobados</div>
                <strong class="text-success"><?= (int)($resumen['aprobados'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">En Revisión</div>
                <strong class="text-primary"><?= (int)($resumen['en_revision'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Observados</div>
                <strong class="text-warning text-dark"><?= (int)($resumen['observados'] ?? 0) ?></strong>
            </div>
        </div>
        <div class="col">
            <div class="border rounded p-2 bg-light">
                <div class="small text-muted">Pendientes</div>
                <strong class="text-secondary"><?= (int)($resumen['pendientes'] ?? 0) ?></strong>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle" style="font-size: 0.85rem;">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 120px;">Código</th>
                    <th>Título del Proyecto</th>
                    <th>Estudiante</th>
                    <th>Línea de Investigación</th>
                    <th>Fechas</th>
                    <th>Entregables</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proyectos)): ?>
                    <tr><td colspan="7" class="text-center py-3">No hay registros que coincidan con la búsqueda.</td></tr>
                <?php else: ?>
                    <?php foreach ($proyectos as $p): ?>
                    <tr>
                        <td class="text-center font-monospace fw-bold"><?= htmlspecialchars($p['codigo_proyecto']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($p['estudiante_apellidos'] . ', ' . $p['estudiante_nombres']) ?><br>
                            <small class="text-muted">Cód: <?= htmlspecialchars($p['codigo_universitario'] ?? '-') ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['linea_investigacion']) ?></td>
                        <td class="text-center small">
                            <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?><br>al <?= date('d/m/Y', strtotime($p['fecha_fin_prevista'])) ?>
                        </td>
                        <td class="text-center"><?= (int)$p['total_entregables'] ?></td>
                        <td class="text-center"><?= badgeEstado($p['estado']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 pt-4 border-top text-center text-muted small d-print-block">
        Documento oficial generado para fines de evaluación y seguimiento académico en la UNS.
    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
