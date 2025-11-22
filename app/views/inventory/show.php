<?php ob_start(); ?>

<!-- Item Details Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">
                    <?= htmlspecialchars($item['name']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Category Info -->
                <p class="text-muted mb-3">
                    (<?= htmlspecialchars($item['category']) ?> / <?= htmlspecialchars($item['sub_category'] ?? 'N/A') ?>)
                </p>
                
                <!-- Basic Details Section -->
                <div class="card border mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Basic Details</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="toggleItemStatus(<?= $item['id'] ?>, '<?= $item['active'] ? 'inactive' : 'active' ?>')">
                                <i class="bi bi-circle-slash"></i> 
                                Mark <?= $item['active'] ? 'Inactive' : 'Active' ?>
                            </button>
                            <a href="/?action=inventory&subaction=edit&id=<?= $item['id'] ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="badge bg-success me-2"><?= htmlspecialchars($item['code']) ?></div>
                        </div>
                        
                        <!-- Info Boxes -->
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Stock</small>
                                    <strong><?= number_format($item['quantity']) ?> <?= htmlspecialchars($item['unit'] ?? 'no.s') ?></strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Lead Time</small>
                                    <strong><?= $item['lead_time'] ?> days</strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Std Cost</small>
                                    <strong>₹<?= number_format($item['std_cost'], 2) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block">Std Price</small>
                                    <strong>₹<?= number_format($item['std_sale_price'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2">Item Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Importance:</td>
                                <td>
                                    <?php
                                    $importanceClass = 'bg-secondary';
                                    switch ($item['importance']) {
                                        case 'Low': $importanceClass = 'bg-success'; break;
                                        case 'Normal': $importanceClass = 'bg-primary'; break;
                                        case 'High': $importanceClass = 'bg-warning'; break;
                                        case 'Critical': $importanceClass = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $importanceClass ?>"><?= htmlspecialchars($item['importance']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Store Location:</td>
                                <td><?= htmlspecialchars($item['store'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tags:</td>
                                <td><?= htmlspecialchars($item['tags'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2">Financial Details</h6>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">Total Value:</td>
                                <td><strong>₹<?= number_format($item['value'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Purchase Cost:</td>
                                <td>₹<?= number_format($item['purch_cost'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">HSN/SAC:</td>
                                <td><?= htmlspecialchars($item['hsn_sac'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($item['description'])): ?>
                <div class="mt-3">
                    <h6 class="fw-bold mb-2">Description</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Back to Inventory Button -->
<div class="row mb-3">
    <div class="col-12">
        <a href="/?action=inventory" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>
</div>

<script>
// Auto-show modal when page loads
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
});

// Function to toggle item status
function toggleItemStatus(itemId, newStatus) {
    if (confirm('Are you sure you want to mark this item as ' + newStatus + '?')) {
        // Send AJAX request to update status
        fetch('/?action=inventory&subaction=toggleStatus&id=' + itemId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-encoded',
            },
            body: 'status=' + newStatus
        })
        .then(response => response.text())
        .then(data => {
            // Reload the page to show updated status
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating item status');
        });
    }
}
</script>

<style>
.modal-lg {
    max-width: 800px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-sm td {
    padding: 0.5rem;
    border: none;
}

.border.rounded {
    border: 1px solid #dee2e6 !important;
}

.text-muted {
    color: #6c757d !important;
}

.fw-bold {
    font-weight: 600 !important;
}
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
