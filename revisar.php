<?php
/**
 * Evaluación Docente de Entregable - SGPP-UNS
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirRol('docente');

require_once __DIR__ . '/models/Entregable.php';
require_once __DIR__ . '/models/Archivo.php';
require_once __DIR__ . '/models/Observacion.php';

$entregableId = (int)($_GET['id'] ?? 0);
if ($entregableId <= 0) {
    setFlash('error', 'Identificador de entregable inválido.');
    header("Location: entregables.php");
    exit;
}

$entregable = Entregable::obtenerPorId($entregableId);
if (!$entregable) {
    setFlash('error', 'El entregable no existe en el sistema.');
    header("Location: entregables.php");
    exit;
}

$error = null;

// Procesar Formulario de Evaluación por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_csrf()) {
        $error = 'Token de seguridad inválido o expirado.';
    } else {
        $decision   = trim($_POST['decision'] ?? '');
        $comentario = trim($_POST['comentario'] ?? '');

        if (empty($decision) || !in_array($decision, ['Aprobado', 'Observado', 'Rechazado'])) {
            $error = 'Debe seleccionar un dictamen de evaluación válido (Aprobar, Observar o Rechazar).';
        } elseif (empty($comentario)) {
            $error = 'Debe ingresar una justificación o comentario para el dictamen del entregable.';
        } else {
            $docenteId = (int)$_SESSION['usuario_id'];
            $docenteLogin = (string)$_SESSION['usuario_login'];

            $res = Entregable::evaluarPorDocente($entregableId, $docenteId, $decision, $comentario, $docenteLogin);

            if ($res['ok']) {
                setFlash('success', "¡Entregable evaluado con éxito! Dictamen '{$decision}' y observaciones registradas.");
                header("Location: entregables.php");
                exit;
            } else {
                $error = 'Error al registrar evaluación: ' . ($res['error'] ?? 'Intente nuevamente.');
            }
        }
    }
}

$archivos = Archivo::obtenerPorEntregable($entregableId);
$historialObs = Observacion::obtenerPorEntregable($entregableId);

$pageTitle = 'Evaluar Entregable #' . $entregable['numero_entregable'];
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="entregables.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a Entregables
            </a>
            <span class="badge bg-secondary-subtle text-secondary border">
                Proyecto: <?= htmlspecialchars($entregable['codigo_proyecto']) ?>
            </span>
        </div>

        <!-- Ficha Resumen del Entregable y Proyecto -->
        <div class="uns-card mb-4">
            <div class="uns-card-header bg-white">
                <h5 class="uns-card-title">
                    <i class="bi bi-clipboard-check text-danger"></i> 
                    <?= htmlspecialchars($entregable['titulo']) ?>
                </h5>
                <?= badgeEstado($entregable['estado']) ?>
            </div>

            <div class="uns-card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <strong class="d-block small text-muted">Proyecto Asociado:</strong>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($entregable['proyecto_titulo']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong class="d-block small text-muted">Estudiante Responsable:</strong>
                        <span class="text-dark">
                            <i class="bi bi-person me-1 text-danger"></i>
                            <?= htmlspecialchars($entregable['estudiante_nombres'] . ' ' . $entregable['estudiante_apellidos']) ?>
                            (Cód: <?= htmlspecialchars($entregable['codigo_universitario'] ?? '-') ?>)
                        </span>
                    </div>
                    <div class="col-12">
                        <strong class="d-block small text-muted">Descripción del Entregable:</strong>
                        <p class="small text-secondary mb-0">
                            <?= nl2br(htmlspecialchars($entregable['descripcion'] ?: 'Sin descripción detallada proporcionada por el estudiante.')) ?>
                        </p>
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-25">

                <!-- Documentos físicos para descargar -->
                <h6 class="fw-bold small text-uppercase text-secondary mb-2">
                    <i class="bi bi-folder-symlink me-1"></i> Documentos Adjuntos para Descargar y Revisar:
                </h6>
                <?php if (empty($archivos)): ?>
                    <div class="alert alert-warning small py-2">
                        El estudiante no adjuntó ningún archivo a este entregable.
                    </div>
                <?php else: ?>
                    <div class="list-group mb-2">
                        <?php foreach ($archivos as $a): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                <div>
                                    <span class="fw-bold small text-dark"><?= htmlspecialchars($a['nombre_original']) ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Tamaño: <?= Archivo::formatearTamano((int)$a['tamano_bytes']) ?> &bull; Fecha: <?= date('d/m/Y H:i', strtotime($a['fecha_subida'])) ?>
                                    </div>
                                </div>
                            </div>
                            <a href="descargar.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-danger px-3">
                                <i class="bi bi-download me-1"></i> Descargar Documento
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario de Evaluación Docente -->
        <div class="uns-card mb-4" style="border-top: 4px solid var(--uns-red) !important;">
            <div class="uns-card-header bg-white">
                <h5 class="uns-card-title">
                    <i class="bi bi-pencil-square text-danger"></i> Emitir Dictamen y Registrar Observaciones
                </h5>
            </div>

            <div class="uns-card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="revisar.php?id=<?= $entregableId ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-4">
                        <label class="form-label d-block fw-bold">
                            Seleccione el Dictamen de Evaluación: <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="card h-100 p-3 border cursor-pointer text-center option-card" style="cursor: pointer;">
                                    <input type="radio" name="decision" value="Aprobado" class="form-check-input mx-auto mb-2" required>
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Aprobar</strong>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">Cumple con los requisitos y estándares exigidos.</small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="card h-100 p-3 border cursor-pointer text-center option-card" style="cursor: pointer;">
                                    <input type="radio" name="decision" value="Observado" class="form-check-input mx-auto mb-2">
                                    <strong class="text-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> Observar</strong>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">Requiere correcciones o aclaraciones del estudiante.</small>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="card h-100 p-3 border cursor-pointer text-center option-card" style="cursor: pointer;">
                                    <input type="radio" name="decision" value="Rechazado" class="form-check-input mx-auto mb-2">
                                    <strong class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Rechazar</strong>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">No cumple con el alcance académico mínimo.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="comentario" class="form-label fw-bold">
                            Observación / Retroalimentación Académica: <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                  placeholder="Escriba aquí los comentarios detallados, recomendaciones metodológicas o fundamentos del dictamen..." 
                                  required><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
                        <div class="form-text small">
                            Esta observación quedará registrada en el expediente del proyecto y será visible de inmediato para el estudiante.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="entregables.php" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-uns-primary px-4 shadow-sm">
                            <i class="bi bi-send-check-fill me-1"></i> Guardar Dictamen y Notificar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial Previo de Observaciones -->
        <?php if (!empty($historialObs)): ?>
        <div class="uns-card">
            <div class="uns-card-header bg-white">
                <h5 class="uns-card-title">
                    <i class="bi bi-clock-history text-secondary"></i> Historial de Evaluaciones de este Entregable
                </h5>
            </div>
            <div class="uns-card-body p-3">
                <?php foreach ($historialObs as $obs): ?>
                <div class="border rounded p-3 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="small text-dark">
                            Dr. <?= htmlspecialchars($obs['docente_nombres'] . ' ' . $obs['docente_apellidos']) ?>
                        </strong>
                        <span class="small text-muted"><?= date('d/m/Y H:i', strtotime($obs['fecha_registro'])) ?></span>
                    </div>
                    <div class="mb-2"><?= badgeEstado($obs['tipo_decision']) ?></div>
                    <p class="small text-secondary mb-0 bg-white p-2 rounded border">
                        <?= nl2br(htmlspecialchars($obs['comentario'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
