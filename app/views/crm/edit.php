<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/?action=crm">Leads & Prospects</a></li>
            <li class="breadcrumb-item active">Edit Lead</li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <a href="/?action=crm" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
        <button type="submit" form="leadForm" class="btn btn-success"><i class="bi bi-check"></i> Update Lead</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Lead: <?= htmlspecialchars($leadData['business_name']) ?></h5>
    </div>
    <div class="card-body">
        <?php $leadData = $leadData ?? []; $ownerUsers = $ownerUsers ?? []; include __DIR__ . '/partials/edit_form.php'; ?>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
