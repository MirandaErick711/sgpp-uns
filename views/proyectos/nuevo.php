<?php
/**
 * Vista de Registro de Nuevo Proyecto - SGPP-UNS
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
            </a>
            <span class="badge bg-danger-subtle text-danger px-3 py-2 border">
                <i class="bi bi-shield-check me-1"></i> Validación y Transaccionalidad PDO
            </span>
        </div>

        <div class="uns-card shadow-sm">
            <div class="uns-card-header bg-white">
                <h5 class="uns-card-title">
                    <i class="bi bi-plus-square-fill text-danger"></i> Registro de Proyecto Académico
                </h5>
                <span class="text-muted small">EPISI &bull; UNS</span>
            </div>

            <div class="uns-card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="nuevo_proyecto.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            Título del Proyecto o Producto Académico <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="Ej: Sistema Web de Gestión de Tutorías Académicas para la EPISI" 
                               required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                        <div class="form-text">Debe ser claro, conciso y representar el alcance del trabajo.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción / Resumen del Proyecto <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                  placeholder="Detalle los objetivos del proyecto, la problemática que resuelve y las tecnologías planificadas..." 
                                  required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="linea_investigacion" class="form-label">
                            Línea de Investigación <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="linea_investigacion" name="linea_investigacion" required>
                            <option value="Sistemas de Información y Gestión del Conocimiento" <?= (($_POST['linea_investigacion'] ?? '') === 'Sistemas de Información y Gestión del Conocimiento') ? 'selected' : '' ?>>
                                Sistemas de Información y Gestión del Conocimiento
                            </option>
                            <option value="Ingeniería de Software y Tecnologías Emergentes" <?= (($_POST['linea_investigacion'] ?? '') === 'Ingeniería de Software y Tecnologías Emergentes') ? 'selected' : '' ?>>
                                Ingeniería de Software y Tecnologías Emergentes
                            </option>
                            <option value="Automatización, Redes y Telemática" <?= (($_POST['linea_investigacion'] ?? '') === 'Automatización, Redes y Telemática') ? 'selected' : '' ?>>
                                Automatización, Redes y Telemática
                            </option>
                            <option value="Ciencia de Datos e Inteligencia Artificial" <?= (($_POST['linea_investigacion'] ?? '') === 'Ciencia de Datos e Inteligencia Artificial') ? 'selected' : '' ?>>
                                Ciencia de Datos e Inteligencia Artificial
                            </option>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="fecha_inicio" class="form-label">
                                Fecha de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                   required value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_fin_prevista" class="form-label">
                                Fecha Prevista de Finalización <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_fin_prevista" name="fecha_fin_prevista" 
                                   required value="<?= htmlspecialchars($_POST['fecha_fin_prevista'] ?? date('Y-m-d', strtotime('+3 months'))) ?>">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="index.php" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-uns-primary px-4 shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> Guardar Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
