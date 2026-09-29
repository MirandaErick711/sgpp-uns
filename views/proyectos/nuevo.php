<?php
/**
 * Vista de Registro de Nuevo Proyecto - SGPP-UNS (Simplificado y Limpio)
 */
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
            </a>
            <span class="text-muted small">EPISI &bull; UNS</span>
        </div>

        <div class="uns-card">
            <div class="uns-card-header">
                <h5 class="uns-card-title">
                    <i class="bi bi-plus-circle text-danger"></i> Registrar Proyecto Académico
                </h5>
            </div>

            <div class="uns-card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 px-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="nuevo_proyecto.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">
                            Título del Proyecto <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="titulo" name="titulo" 
                               placeholder="ej: Sistema Web de Gestión de Laboratorios para la EPISI" 
                               required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción / Alcance <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                  placeholder="Detalle los objetivos del proyecto y la problemática que resuelve..." 
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
                                Fecha Prevista de Término <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_fin_prevista" name="fecha_fin_prevista" 
                                   required value="<?= htmlspecialchars($_POST['fecha_fin_prevista'] ?? date('Y-m-d', strtotime('+3 months'))) ?>">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm px-3">Cancelar</a>
                        <button type="submit" class="btn btn-uns-primary btn-sm px-3">
                            Guardar Proyecto
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
