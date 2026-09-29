<?php
/**
 * Detalle del Proyecto y Entregables - SGPP-UNS
 * IMPLEMENTACIÓN DEL DRIVER ARQUITECTÓNICO DR-01
 */

define('APP_INIT', true);
require_once __DIR__ . '/includes/session.php';
requerirAutenticacion();

require_once __DIR__ . '/models/Proyecto.php';
require_once __DIR__ . '/models/Entregable.php';
require_once __DIR__ . '/models/Archivo.php';
require_once __DIR__ . '/models/Observacion.php';

$proyectoId = (int)($_GET['id'] ?? 0);
if ($proyectoId <= 0) {
    setFlash('error', 'Identificador de proyecto no válido.');
    header("Location: index.php");
    exit;
}

$error_dr01 = null;
$usuarioId  = (int)$_SESSION['usuario_id'];
$rolUsuario = (string)$_SESSION['usuario_rol'];

// ====================================================================
// COMPROBACIÓN CRÍTICA DEL DRIVER ARQUITECTÓNICO DR-01
// Validación en la capa de aplicación PHP (servidor) antes de procesar
// ====================================================================
$proyecto = Proyecto::obtenerPorIdConSeguridad($proyectoId, $usuarioId, $rolUsuario, $error_dr01);

if (!$proyecto) {
    if ($error_dr01 === 'ACCESO_NO_AUTORIZADO_DR01') {
        // Enviar respuesta 403 Forbidden y renderizar pantalla de rechazo de DR-01
        http_response_code(403);
        include __DIR__ . '/views/error/403.php';
        exit;
    } else {
        setFlash('error', $error_dr01 ?? 'El proyecto solicitado no existe.');
        header("Location: index.php");
        exit;
    }
}

// Proyecto válido y autorizado para el usuario actual:
$entregables = Entregable::obtenerPorProyecto($proyectoId);
$esPropietario = ($rolUsuario === 'estudiante' && (int)$proyecto['estudiante_id'] === $usuarioId);
$esDocente = ($rolUsuario === 'docente');

$pageTitle = 'Proyecto: ' . $proyecto['codigo_proyecto'];
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- Encabezado del Proyecto -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="proyectos.php" class="text-decoration-none">Proyectos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($proyecto['codigo_proyecto']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($proyecto['titulo']) ?></h3>
            <?= badgeEstado($proyecto['estado']) ?>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <a href="proyectos.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a Lista
        </a>
        <?php if ($esPropietario): ?>
            <button type="button" class="btn btn-uns-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> + Subir Entregable
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Ficha de Datos del Proyecto -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="uns-card h-100">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-card-text text-danger"></i> Descripción y Alcance
                </h5>
                <span class="badge bg-light text-secondary border"><?= htmlspecialchars($proyecto['codigo_proyecto']) ?></span>
            </div>
            <div class="uns-card-body">
                <p class="text-secondary" style="font-size: 0.95rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($proyecto['descripcion'])) ?>
                </p>

                <hr class="my-3 text-muted opacity-25">

                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <strong class="d-block text-muted mb-1"><i class="bi bi-bookmark-fill text-danger me-1"></i> Línea de Investigación:</strong>
                        <span class="text-dark fw-semibold"><?= htmlspecialchars($proyecto['linea_investigacion']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <strong class="d-block text-muted mb-1"><i class="bi bi-calendar-range me-1 text-danger"></i> Periodo Académico:</strong>
                        <span class="text-dark">
                            <?= date('d/m/Y', strtotime($proyecto['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($proyecto['fecha_fin_prevista'])) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="uns-card h-100">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-person-badge text-danger"></i> Autor / Estudiante
                </h5>
            </div>
            <div class="uns-card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="user-avatar-circle" style="width: 48px; height: 48px; font-size: 1.2rem;">
                        <?= strtoupper(substr($proyecto['estudiante_nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($proyecto['estudiante_nombres'] . ' ' . $proyecto['estudiante_apellidos']) ?></h6>
                        <small class="text-muted d-block"><?= htmlspecialchars($proyecto['estudiante_email']) ?></small>
                        <small class="badge bg-secondary-subtle text-secondary mt-1">Cód: <?= htmlspecialchars($proyecto['codigo_universitario'] ?? 'S/C') ?></small>
                    </div>
                </div>

                <div class="border-top pt-3 small text-muted">
                    <div><strong>Escuela:</strong> <?= htmlspecialchars($proyecto['estudiante_escuela']) ?></div>
                    <div><strong>Facultad:</strong> Facultad de Ingeniería</div>
                    <div><strong>Registrado:</strong> <?= date('d/m/Y H:i', strtotime($proyecto['created_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Entregables y Documentos -->
<div class="uns-card mb-4">
    <div class="uns-card-header">
        <h5 class="uns-card-title">
            <i class="bi bi-folder2-open text-danger"></i> Entregables y Documentos Asociados
        </h5>
        <?php if ($esPropietario): ?>
            <button type="button" class="btn btn-sm btn-uns-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Entregable
            </button>
        <?php endif; ?>
    </div>

    <div class="uns-card-body p-0">
        <?php if (empty($entregables)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-arrow-up fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-2">No se han registrado entregables para este proyecto.</p>
                <?php if ($esPropietario): ?>
                    <button type="button" class="btn btn-uns-gold btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoEntregable">
                        <i class="bi bi-plus-circle me-1"></i> Cargar primer entregable y archivo
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
                        <button class="accordion-button <?= $idx === 0 ? '' : 'collapsed' ?> py-3 px-4" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse<?= $e['id'] ?>" 
                                aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $e['id'] ?>">
                            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 me-3 gap-2">
                                <div>
                                    <span class="badge bg-light text-dark border me-2">Entregable #<?= (int)$e['numero_entregable'] ?></span>
                                    <strong class="text-dark"><?= htmlspecialchars($e['titulo']) ?></strong>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="small text-muted d-none d-sm-inline">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y H:i', strtotime($e['fecha_entrega'])) ?>
                                    </span>
                                    <?= badgeEstado($e['estado']) ?>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $e['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" 
                         aria-labelledby="heading<?= $e['id'] ?>" data-bs-parent="#accordionEntregables">
                        <div class="accordion-body px-4 py-3 bg-light bg-opacity-25">
                            
                            <?php if (!empty($e['descripcion'])): ?>
                                <p class="small text-muted mb-3"><?= nl2br(htmlspecialchars($e['descripcion'])) ?></p>
                            <?php endif; ?>

                            <!-- Archivos cargados -->
                            <div class="mb-3">
                                <h6 class="fw-bold small text-uppercase text-secondary mb-2">
                                    <i class="bi bi-paperclip me-1"></i> Archivos Digitales Adjuntos (Almacenamiento Físico):
                                </h6>
                                <?php if (empty($archivos)): ?>
                                    <div class="alert alert-light border small text-muted py-2 mb-2">
                                        No hay archivos adjuntos en este entregable.
                                    </div>
                                <?php else: ?>
                                    <div class="list-group mb-2 shadow-sm">
                                        <?php foreach ($archivos as $a): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <i class="bi bi-file-earmark-check-fill text-danger fs-5"></i>
                                                <div class="text-truncate">
                                                    <span class="fw-semibold text-dark small"><?= htmlspecialchars($a['nombre_original']) ?></span>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <?= Archivo::formatearTamano((int)$a['tamano_bytes']) ?> &bull; Subido el <?= date('d/m/Y H:i', strtotime($a['fecha_subida'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="descargar.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-danger px-3">
                                                <i class="bi bi-download me-1"></i> Descargar
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($esPropietario): ?>
                                    <!-- Botón para adjuntar archivo adicional al entregable -->
                                    <button class="btn btn-sm btn-outline-secondary mt-1" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#adjuntarForm<?= $e['id'] ?>">
                                        <i class="bi bi-paperclip me-1"></i> + Adjuntar otro archivo
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
                                                        Formatos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP (máx. 20 MB).
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="submit" class="btn btn-sm btn-uns-primary w-100">
                                                        <i class="bi bi-upload me-1"></i> Subir Documento
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Observaciones del Docente -->
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="fw-bold small text-uppercase text-secondary mb-2">
                                    <i class="bi bi-chat-square-quote-fill me-1"></i> Retroalimentación y Observaciones Docentes:
                                </h6>
                                <?php if (empty($obsList)): ?>
                                    <p class="small text-muted mb-0 fst-italic">
                                        No se han emitido observaciones para este entregable.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($obsList as $obs): ?>
                                    <div class="card mb-2 border shadow-none" style="border-left: 4px solid <?= $obs['tipo_decision'] === 'Aprobado' ? '#198754' : ($obs['tipo_decision'] === 'Observado' ? '#fd7e14' : '#dc3545') ?> !important;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="small text-dark">
                                                    <i class="bi bi-person-check-fill text-danger me-1"></i> Docente Evaluador: <?= htmlspecialchars($obs['docente_nombres'] . ' ' . $obs['docente_apellidos']) ?>
                                                </strong>
                                                <span class="small text-muted"><?= date('d/m/Y H:i', strtotime($obs['fecha_registro'])) ?></span>
                                            </div>
                                            <div class="mb-2">
                                                <?= badgeEstado($obs['tipo_decision']) ?>
                                            </div>
                                            <p class="small text-dark mb-0 bg-white p-2 rounded border">
                                                <?= nl2br(htmlspecialchars($obs['comentario'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Si el usuario actual es Docente, botón para evaluar este entregable -->
                            <?php if ($esDocente): ?>
                                <div class="mt-3 pt-3 border-top text-end">
                                    <a href="revisar.php?id=<?= (int)$e['id'] ?>" class="btn btn-sm btn-uns-primary">
                                        <i class="bi bi-pencil-square me-1"></i> Evaluar / Registrar Observación
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

<!-- Modal para Crear Nuevo Entregable (Estudiante Propietario) -->
<?php if ($esPropietario): ?>
<div class="modal fade" id="modalNuevoEntregable" tabindex="-1" aria-labelledby="modalNuevoEntregableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title fw-bold text-danger" id="modalNuevoEntregableLabel">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Nuevo Entregable y Carga de Archivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="entregable.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="accion" value="crear_entregable">
                <input type="hidden" name="proyecto_id" value="<?= (int)$proyectoId ?>">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="tituloEntregable" class="form-label">
                            Título del Entregable <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="tituloEntregable" name="titulo" 
                               placeholder="Ej: Entregable 2: Prototipo Funcional y Documento de Diseño" required>
                    </div>

                    <div class="mb-3">
                        <label for="descEntregable" class="form-label">Descripción / Contenido del Entregable</label>
                        <textarea class="form-control" id="descEntregable" name="descripcion" rows="3" 
                                  placeholder="Detalle los avances incluidos en este entregable..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="archivoEntregable" class="form-label">
                            Documento / Archivo Adjunto <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="archivoEntregable" name="archivo" required data-max-size="20">
                        <div class="form-text small">
                            Formatos permitidos: <strong>PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP</strong> (Máximo: 20 MB).
                            El archivo se almacenará físicamente en el servidor fuera de la base de datos.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-uns-primary btn-sm px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Guardar y Enviar a Revisión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include __DIR__ . '/includes/footer.php';
?>
