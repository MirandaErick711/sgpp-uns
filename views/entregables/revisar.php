<?php
/**
 * Vista de Evaluación Docente de Entregable - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="entregables.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver a Entregables
            </a>
            <span class="text-muted small">
                Proyecto: <?= htmlspecialchars($entregable['codigo_proyecto']) ?>
            </span>
        </div>

        <!-- Ficha del Entregable -->
        <div class="uns-card mb-4">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-file-earmark-text text-danger"></i> 
                    <?= htmlspecialchars($entregable['titulo']) ?>
                </h5>
                <?= badgeEstado($entregable['estado']) ?>
            </div>

            <div class="uns-card-body">
                <div class="row g-3 mb-3 small">
                    <div class="col-md-6">
                        <span class="text-muted d-block">Proyecto:</span>
                        <strong class="text-dark"><?= htmlspecialchars($entregable['proyecto_titulo']) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Estudiante:</span>
                        <strong class="text-dark">
                            <?= htmlspecialchars($entregable['estudiante_nombres'] . ' ' . $entregable['estudiante_apellidos']) ?>
                        </strong>
                        <span class="text-muted">(Cód: <?= htmlspecialchars($entregable['codigo_universitario'] ?? '-') ?>)</span>
                    </div>
                    <?php if (!empty($entregable['descripcion'])): ?>
                    <div class="col-12">
                        <span class="text-muted d-block">Descripción:</span>
                        <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($entregable['descripcion'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Archivos adjuntos para descargar -->
                <div class="border-top pt-3">
                    <span class="small fw-semibold text-secondary d-block mb-2">Documentos Adjuntos:</span>
                    <?php if (empty($archivos)): ?>
                        <div class="small text-muted">El estudiante no adjuntó archivos.</div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($archivos as $a): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-white">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    <div class="text-truncate small">
                                        <span class="fw-medium text-dark"><?= htmlspecialchars($a['nombre_original']) ?></span>
                                        <span class="text-muted ms-1">(<?= Archivo::formatearTamano((int)$a['tamano_bytes']) ?>)</span>
                                    </div>
                                </div>
                                <a href="descargar.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-3">
                                    <i class="bi bi-download me-1"></i> Descargar
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Formulario de Dictamen -->
        <div class="uns-card mb-4">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-pencil-square text-danger"></i> Emitir Dictamen y Observaciones
                </h5>
            </div>

            <div class="uns-card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="revisar.php?id=<?= $entregableId ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label d-block fw-semibold">
                            Dictamen de Evaluación: <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="decision" id="decAprobar" value="Aprobado" required>
                                <label class="form-check-label text-success fw-semibold" for="decAprobar">
                                    Aprobar
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="decision" id="decObservar" value="Observado">
                                <label class="form-check-label text-warning-emphasis fw-semibold" for="decObservar">
                                    Observar
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="decision" id="decRechazar" value="Rechazado">
                                <label class="form-check-label text-danger fw-semibold" for="decRechazar">
                                    Rechazar
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comentario" class="form-label fw-semibold">
                            Observación / Retroalimentación Académica: <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                  placeholder="Detalle las recomendaciones, correcciones o fundamentos del dictamen..." 
                                  required><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="entregables.php" class="btn btn-outline-secondary btn-sm px-3">Cancelar</a>
                        <button type="submit" class="btn btn-uns-primary btn-sm px-3">
                            Guardar Dictamen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial Previo de Observaciones -->
        <?php if (!empty($historialObs)): ?>
        <div class="uns-card">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-clock-history text-secondary"></i> Historial de Evaluaciones
                </h5>
            </div>
            <div class="uns-card-body p-3">
                <?php foreach ($historialObs as $obs): ?>
                <div class="border rounded p-3 mb-2 bg-light small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark">
                            Dr. <?= htmlspecialchars($obs['docente_nombres'] . ' ' . $obs['docente_apellidos']) ?>
                        </strong>
                        <span class="text-muted"><?= date('d/m/Y H:i', strtotime($obs['fecha_registro'])) ?></span>
                    </div>
                    <div class="mb-2"><?= badgeEstado($obs['tipo_decision']) ?></div>
                    <p class="text-secondary mb-0 bg-white p-2 rounded border">
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
include __DIR__ . '/../../includes/footer.php';
?>
