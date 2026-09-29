<?php
/**
 * Alertas y Notificaciones Flash - SGPP-UNS
 */
$msgSuccess = getFlash('success');
$msgError   = getFlash('error');
$msgInfo    = getFlash('info');
$msgWarning = getFlash('warning');
?>

<?php if ($msgSuccess): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($msgSuccess) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($msgError): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div><?= htmlspecialchars($msgError) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($msgInfo): ?>
<div class="alert alert-info alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($msgInfo) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($msgWarning): ?>
<div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($msgWarning) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
