<?php ob_start(); ?>

<!-- Inventory Header -->
<div class="row mb-3">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-box-seam"></i> Inventory
        </h1>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end align-items-center">
            <div class="me-3">
                <span class="badge bg-primary">Total Items: <?= $totalItems ?></span>
            </div>
            <div>
                <span class="badge bg-info">Valuation: Standard Cost</span>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-warning">
                <i class="bi bi-box-arrow-right"></i> Out / Issue
            </button>
            <button class="btn btn-warning">
                <i class="bi bi-box-arrow-in-left"></i> In / Receive
            </button>
            <a href="/?action=inventory&subaction=create" class="btn btn-warning">
                <i class="bi bi-plus"></i>  Add Item
            </a>
            <button class="btn btn-primary" onclick="showImportModal()">
                <i class="bi bi-check"></i>  Import Items
            </button>
            <a href="/?action=inventory&subaction=settings" class="btn btn-secondary" title="Inventory Settings">
                <i class="bi bi-gear"></i>
            </a>
            <button class="btn btn-secondary">
                <i class="bi bi-graph-up"></i>
            </button>
            <button class="btn btn-secondary">
                <i class="bi bi-question-circle"></i>
            </button>
        </div>
    </div>
</div>

<!-- Category Tabs -->
<div class="row mb-3">
    <div class="col-12">
        <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= empty($_GET['item_type']) ? 'active' : '' ?>" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" onclick="filterByItemType('')">
                    All
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($_GET['item_type'] ?? '') === 'products' ? 'active' : '' ?>" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab" onclick="filterByItemType('products')">
                    Products
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($_GET['item_type'] ?? '') === 'materials' ? 'active' : '' ?>" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab" onclick="filterByItemType('materials')">
                    Materials
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($_GET['item_type'] ?? '') === 'spares' ? 'active' : '' ?>" id="spares-tab" data-bs-toggle="tab" data-bs-target="#spares" type="button" role="tab" onclick="filterByItemType('spares')">
                    Spares
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($_GET['item_type'] ?? '') === 'assemblies' ? 'active' : '' ?>" id="assemblies-tab" data-bs-toggle="tab" data-bs-target="#assemblies" type="button" role="tab" onclick="filterByItemType('assemblies')">
                    Assemblies
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <select class="form-select" id="categoryFilter" style="width: auto;">
                <option value="">All Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= ($_GET['category'] ?? '') === $category ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select class="form-select" id="subCategoryFilter" style="width: auto;">
                <option value="">All Sub Category</option>
                <?php foreach ($subCategories as $subCategory): ?>
                    <option value="<?= htmlspecialchars($subCategory) ?>" <?= ($_GET['sub_category'] ?? '') === $subCategory ? 'selected' : '' ?>>
                        <?= htmlspecialchars($subCategory) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select class="form-select" id="stockFilter" style="width: auto;">
                <option value="">All Stock</option>
                <option value="in_stock" <?= ($_GET['stock'] ?? '') === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                <option value="out_of_stock" <?= ($_GET['stock'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                <option value="low_stock" <?= ($_GET['stock'] ?? '') === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
            </select>
            
            <select class="form-select" id="importanceFilter" style="width: auto;">
                <option value="">All Importance Levels</option>
                <?php foreach ($importanceLevels as $level): ?>
                    <option value="<?= htmlspecialchars($level) ?>" <?= ($_GET['importance'] ?? '') === $level ? 'selected' : '' ?>>
                        <?= htmlspecialchars($level) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select class="form-select" id="statusFilter" style="width: auto;">
                <option value="">All Items</option>
                <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
            </select>
            
            <div class="input-group" style="width: 200px;">
                <input type="text" class="form-control" id="tagSearch" placeholder="Search by Tag" 
                       value="<?= htmlspecialchars($_GET['tag_search'] ?? '') ?>">
                <button class="btn btn-success" type="button" onclick="applyFilters()">
                    <i class="bi bi-check"></i>
                </button>
            </div>
            
            <button class="btn btn-outline-secondary" type="button" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> Clear Filters
            </button>
        </div>
    </div>
</div>

<!-- Inventory Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No inventory items found. 
                <a href="/?action=inventory&subaction=create">Add your first item</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="inventoryTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>Importance</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr data-item-id="<?= (int)$item['id'] ?>">
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <?php if (!empty($item['tags'])): ?>
                                        <br><small class="text-muted">Tags: <?= htmlspecialchars($item['tags']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($item['code']) ?></span>
                            </td>
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
                            <td>
                                <small>
                                    <?= htmlspecialchars($item['category']) ?>
                                    <?php if (!empty($item['sub_category'])): ?>
                                        <br>/ <?= htmlspecialchars($item['sub_category']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format($item['quantity']) ?> no.s
                                </span>
                            </td>
                            <td>₹<?= number_format($item['rate'], 2) ?></td>
                            <td>₹<?= number_format($item['value'], 2) ?></td>
                            <td>
                                <?php if (($item['active'] ?? 1) == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-warning" 
                                            title="View/Edit Item"
                                            onclick="showItemDetails(<?= $item['id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="/?action=inventory&subaction=delete&id=<?= $item['id'] ?>" 
                                       class="btn btn-sm btn-secondary" 
                                       title="Delete Item"
                                       onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">Items per page: 10</span>
                </div>
                <div>
                    <span class="text-muted">1 - <?= count($items) ?> of <?= count($items) ?></span>
                    <div class="btn-group ms-2">
                        <button class="btn btn-sm btn-outline-secondary" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" disabled>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom Action Buttons -->
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary">
                <i class="bi bi-book"></i> Training Materials
            </button>
            <button class="btn btn-outline-success">
                <i class="bi bi-arrow-up-circle"></i> Upgrade Stock Quota
            </button>
            <button class="btn btn-outline-info">
                <i class="bi bi-play-circle"></i> Watch Training
            </button>
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Items Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Items</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning" onclick="downloadTemplate()">
                        <i class="bi bi-download text-white"></i> Download Template
                    </button>
                    <button type="button" class="btn btn-info btn-sm" title="Information">
                        <i class="bi bi-info text-white"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-primary w-100 mb-3" onclick="document.getElementById('excelFile').click()">
                    <i class="bi bi-upload"></i> Import Items from Excel/CSV
                </button>
                <input type="file" id="excelFile" accept=".xlsx,.xls,.csv" style="display: none;" onchange="handleFileUpload(this)">
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i> Download the template, fill it with your data, and upload it. All items will be imported with "No" batch by default.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    color: #8B4513;
    background-color: transparent;
    border-bottom-color: #8B4513;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #D2691E;
}

#products-tab.active { border-bottom-color: #28a745; }
#materials-tab.active { border-bottom-color: #fd7e14; }
#spares-tab.active { border-bottom-color: #007bff; }
#assemblies-tab.active { border-bottom-color: #17a2b8; }

.form-select {
    min-width: 120px;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}
</style>

<script>
function showItemDetails(itemId) {
    // Show loading in modal
    document.getElementById('modalBody').innerHTML = '<div class="text-center"><i class="bi bi-arrow-clockwise spin"></i> Loading...</div>';
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
    
    // Load item details via AJAX
    fetch('/?action=inventory&subaction=show&id=' + itemId)
        .then(response => response.text())
        .then(html => {
            // Extract the modal content from the response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const modalContent = doc.querySelector('#itemModal .modal-body');
            
            if (modalContent) {
                document.getElementById('modalBody').innerHTML = modalContent.innerHTML;
                document.getElementById('itemModalLabel').textContent = doc.querySelector('#itemModal .modal-title').textContent;
            } else {
                document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Error loading item details</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalBody').innerHTML = '<div class="alert alert-danger">Error loading item details</div>';
        });
}

// Function to toggle item status
function toggleItemStatus(itemId, newStatus) {
    if (confirm('Are you sure you want to mark this item as ' + newStatus + '?')) {
        // Send AJAX request to update status
        fetch('/?action=inventory&subaction=toggleStatus&id=' + itemId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'status=' + newStatus
        })
        .then(response => response.text())
        .then(data => {
            // Close modal and reload page to show updated status
            var modal = bootstrap.Modal.getInstance(document.getElementById('itemModal'));
            modal.hide();
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating item status');
        });
    }
}

// Function to apply filters
function applyFilters() {
    const category = document.getElementById('categoryFilter').value;
    const subCategory = document.getElementById('subCategoryFilter').value;
    const stock = document.getElementById('stockFilter').value;
    const importance = document.getElementById('importanceFilter').value;
    const status = document.getElementById('statusFilter').value;
    const tagSearch = document.getElementById('tagSearch').value;
    
    // Build filter URL
    let filterUrl = '/?action=inventory';
    const params = new URLSearchParams();
    
    if (category) params.append('category', category);
    if (subCategory) params.append('sub_category', subCategory);
    if (stock) params.append('stock', stock);
    if (importance) params.append('importance', importance);
    if (status) params.append('status', status);
    if (tagSearch) params.append('tag_search', tagSearch);
    
    if (params.toString()) {
        filterUrl += '&' + params.toString();
    }
    
    // Redirect to filtered view
    window.location.href = filterUrl;
}

// Add enter key support for tag search
document.getElementById('tagSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// Function to clear all filters
function clearFilters() {
    window.location.href = '/?action=inventory';
}

// Function to filter by item type
function filterByItemType(itemType) {
    // Update active tab
    document.querySelectorAll('#categoryTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Build filter URL
    let filterUrl = '/?action=inventory';
    if (itemType) {
        filterUrl += '&item_type=' + itemType;
    }
    
    // Redirect to filtered view
    window.location.href = filterUrl;
}

// Function to show import modal
function showImportModal() {
    var modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

// Function to download Excel template
function downloadTemplate() {
    // Create a link to download the template
    const link = document.createElement('a');
    link.href = '/?action=inventory&subaction=downloadTemplate';
    link.download = 'inventory_template.xlsx';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Function to handle file upload
function handleFileUpload(input) {
    const file = input.files[0];
    if (file) {
        // Show loading state
        const importBtn = input.parentElement.querySelector('.btn');
        const originalText = importBtn.innerHTML;
        importBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Processing...';
        importBtn.disabled = true;
        
        // Create FormData and upload
        const formData = new FormData();
        formData.append('excel_file', file);
        
        console.log('Uploading file:', file.name, 'Size:', file.size, 'Type:', file.type);
        
        fetch('/?action=inventory&subaction=importExcel', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Import successful! ' + data.message);
                location.reload();
            } else {
                alert('Import failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error during import: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            importBtn.innerHTML = originalText;
            importBtn.disabled = false;
            input.value = '';
        });
    }
}
</script>

<script>
// Row click to open edit/details modal (ignore clicks on controls)
document.addEventListener('DOMContentLoaded', function() {
  const tbody = document.querySelector('#inventoryTable tbody');
  if (!tbody) return;
  tbody.addEventListener('click', function(e) {
    if (e.target.closest('a, button, input, select, label, .btn-group')) return;
    const tr = e.target.closest('tr[data-item-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-item-id');
    if (id) { showItemDetails(id); }
  });
});
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?> 