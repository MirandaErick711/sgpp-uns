<?php
/**
 * Vista de Detalle de Proyecto y Entregables - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Encabezado del Proyecto -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="font-monospace fw-bold text-muted small"><?= htmlspecialchars($proyecto['codigo_proyecto']) ?></span>
            <?= badgeEstado($proyecto['estado']) ?>
        </div>
        <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($proyecto['titulo']) ?></h4>
    </div>
    
    <div class="d-flex gap-2">
        <a href="proyectos.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
        <?php if ($esPropietario): ?>
            <button type="button" class="btn btn-uns-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                <i class="bi bi-plus-lg me-1"></i> Subir Entregable
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Información General del Proyecto -->
<div class="uns-card mb-4">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-info-circle text-danger"></i> Información General
        </h5>
    </div>
    <div class="uns-card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="text-muted small fw-semibold d-block mb-1">Descripción del Proyecto</label>
                <p class="text-dark small mb-3" style="line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($proyecto['descripcion'])) ?>
                </p>

                <div class="row g-2 small border-top pt-2">
                    <div class="col-sm-6">
                        <span class="text-muted">Línea de Investigación:</span>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($proyecto['linea_investigacion']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted">Periodo Académico:</span>
                        <div class="fw-semibold text-dark">
                            <?= date('d/m/Y', strtotime($proyecto['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($proyecto['fecha_fin_prevista'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 border-start-md ps-md-3">
                <label class="text-muted small fw-semibold d-block mb-1">Estudiante Responsable</label>
                <div class="fw-semibold text-dark small">
                    <?= htmlspecialchars($proyecto['estudiante_nombres'] . ' ' . $proyecto['estudiante_apellidos']) ?>
                </div>
                <div class="text-muted small"><?= htmlspecialchars($proyecto['estudiante_email']) ?></div>
                <div class="small mt-1"><span class="badge bg-light text-secondary border">Cód: <?= htmlspecialchars($proyecto['codigo_universitario'] ?? '-') ?></span></div>
                <div class="text-muted small mt-2">Escuela: <?= htmlspecialchars($proyecto['estudiante_escuela']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Entregables -->
<div class="uns-card">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-files text-danger"></i> Entregables y Documentos
        </h5>
        <?php if ($esPropietario): ?>
            <button type="button" class="btn btn-sm btn-uns-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Entregable
            </button>
        <?php endif; ?>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregables)): ?>
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-file-earmark-arrow-up fs-2 d-block mb-1 opacity-50"></i>
                <p class="mb-2">No se han registrado entregables para este proyecto.</p>
                <?php if ($esPropietario): ?>
                    <button type="button" class="btn btn-uns-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                        <i class="bi bi-plus-lg me-1"></i> Cargar primer entregable
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="accordion accordion-flush" id="accordionEntregables">
                <?php foreach ($entregables as $idx => $e): 
                    $archivos = Archivo::obtenerPorEntregable((int)$e['id']);
                    $obsList = Observacion::obtenerPorEntregable((int)$e['id']);
                ?>
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="heading<?= $e['id'] ?>">
                        <button class="accordion-button <?= $idx === 0 ? '' : 'collapsed' ?> py-2.5 px-3 bg-white" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse<?= $e['id'] ?>" 
                                aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $e['id'] ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 me-2 gap-2">
                                <div>
                                    <span class="badge bg-light text-secondary border me-1">#<?= (int)$e['numero_entregable'] ?></span>
                                    <span class="fw-semibold text-dark small"><?= htmlspecialchars($e['titulo']) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted small d-none d-sm-inline">
                                        <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                                    </span>
                                    <?= badgeEstado($e['estado']) ?>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $e['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" 
                         aria-labelledby="heading<?= $e['id'] ?>" data-bs-parent="#accordionEntregables">
                        <div class="accordion-body px-3 py-3 bg-light bg-opacity-25">
                            
                            <?php if (!empty($e['descripcion'])): ?>
                                <p class="small text-secondary mb-3"><?= nl2br(htmlspecialchars($e['descripcion'])) ?></p>
                            <?php endif; ?>

                            <!-- Archivos cargados -->
                            <div class="mb-3">
                                <span class="small fw-semibold text-secondary d-block mb-1">Archivos Adjuntos:</span>
                                <?php if (empty($archivos)): ?>
                                    <div class="small text-muted py-1">No hay archivos adjuntos en este entregable.</div>
                                <?php else: ?>
                                    <div class="list-group mb-2">
                                        <?php foreach ($archivos as $a): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-1.5 px-3 bg-white">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                <div class="text-truncate small">
                                                    <span class="fw-medium text-dark"><?= htmlspecialchars($a['nombre_original']) ?></span>
                                                    <span class="text-muted ms-1">(<?= Archivo::formatearTamano((int)$a['tamano_bytes']) ?>)</span>
                                                </div>
                                            </div>
                                            <a href="descargar.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-secondary py-0.5 px-2 small">
                                                <i class="bi bi-download me-1"></i> Descargar
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($esPropietario): ?>
                                    <button class="btn btn-sm btn-outline-secondary mt-1" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#adjuntarForm<?= $e['id'] ?>">
                                        <i class="bi bi-plus-lg me-1"></i> Adjuntar otro archivo
                                    </button>
                                    <div class="collapse mt-2" id="adjuntarForm<?= $e['id'] ?>">
                                        <form action="entregable.php" method="POST" enctype="multipart/form-data" class="card card-body p-3 bg-white border">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="accion" value="adjuntar_archivo">
                                            <input type="hidden" name="entregable_id" value="<?= (int)$e['id'] ?>">
                                            <input type="hidden" name="proyecto_id" value="<?= (int)$proyectoId ?>">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-8">
                                                    <input type="file" name="archivo" class="form-control form-control-sm" required data-max-size="20">
                                                    <div class="form-text small" style="font-size:0.75rem;">
                                                        Formatos: PDF, DOC, DOCX, ZIP (máx. 20 MB).
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-sm btn-uns-primary w-100">
                                                        Subir Documento
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Observaciones del Docente -->
                            <div class="mt-3 pt-2 border-top">
                                <span class="small fw-semibold text-secondary d-block mb-1">Observaciones Docentes:</span>
                                <?php if (empty($obsList)): ?>
                                    <p class="small text-muted mb-0 fst-italic">Sin observaciones registradas.</p>
                                <?php else: ?>
                                    <?php foreach ($obsList as $obs): ?>
                                    <div class="border rounded p-2 mb-2 bg-white small">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="text-dark">Dr. <?= htmlspecialchars($obs['docente_nombres'] . ' ' . $obs['docente_apellidos']) ?></strong>
                                            <span class="text-muted"><?= date('d/m/Y H:i', strtotime($obs['fecha_registro'])) ?></span>
                                        </div>
                                        <div class="mb-1"><?= badgeEstado($obs['tipo_decision']) ?></div>
                                        <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($obs['comentario'])) ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($esDocente): ?>
                                <div class="mt-3 pt-2 border-top text-end">
                                    <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary">
                                        <i class="bi bi-pencil-square me-1"></i> Evaluar Entregable
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Crear Nuevo Entregable -->
<?php if ($esPropietario): ?>
<div class="modal fade" id="modalNuevoEntregable" tabindex="-1" aria-labelledby="modalNuevoEntregableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-white border-bottom">
                <h6 class="modal-title fw-bold text-dark" id="modalNuevoEntregableLabel">
                    Nuevo Entregable
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="entregable.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="accion" value="crear_entregable">
                <input type="hidden" name="proyecto_id" value="<?= (int)$proyectoId ?>">

                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label for="tituloEntregable" class="form-label">
                            Título del Entregable <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="tituloEntregable" name="titulo" 
                               placeholder="ej: Entregable 2: Prototipo y Diseño" required>
                    </div>

                    <div class="mb-3">
                        <label for="descEntregable" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descEntregable" name="descripcion" rows="3" 
                                  placeholder="Detalle breve de los avances..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="archivoEntregable" class="form-label">
                            Documento / Archivo Adjunto <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="archivoEntregable" name="archivo" required data-max-size="20">
                        <div class="form-text small">
                            Formatos: PDF, DOC, DOCX, ZIP (máx. 20 MB).
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-uns-primary btn-sm">Guardar Entregable</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
