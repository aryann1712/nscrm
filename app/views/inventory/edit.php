<?php ob_start(); ?>

<!-- Inventory Header -->
<div class="row mb-3">
    <div class="col-md-6">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil"></i> Edit Inventory Item
        </h1>
    </div>
    <div class="col-md-6 text-end">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-end mb-0">
                <li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="/?action=inventory">Inventory</a></li>
                <li class="breadcrumb-item active">Edit Item</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Edit Form Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="bi bi-pencil"></i> Edit Item: <?= htmlspecialchars($item['name']) ?>
        </h3>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-dark" title="Manage Categories" data-bs-toggle="modal" data-bs-target="#categoriesManagerModal">
                <i class="bi bi-gear text-white"></i>
            </button>
            <button type="submit" form="inventoryForm" class="btn btn-success">
                <i class="bi bi-check text-white"></i> Save
            </button>
            <a href="/?action=inventory" class="btn btn-light">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <form id="inventoryForm" method="POST" action="/?action=inventory&subaction=update&id=<?= $item['id'] ?>">
            <!-- Item Identification & Categorization -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Name*</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($item['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Code*</label>
                    <input type="text" class="form-control" id="code" name="code" 
                           value="<?= htmlspecialchars($item['code']) ?>" required>
                    <small class="text-muted">Prev. Code: <?= htmlspecialchars($item['code']) ?></small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <div class="input-group">
                        <select class="form-select" id="category" name="category">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= $item['category'] === $category ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-warning" onclick="addNewCategory()">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="sub_category" class="form-label">Sub-Category</label>
                    <div class="input-group">
                        <select class="form-select" id="sub_category" name="sub_category">
                            <option value="">Select Sub-Category</option>
                            <?php foreach ($subCategories as $subCategory): ?>
                                <option value="<?= $subCategory ?>" <?= $item['sub_category'] === $subCategory ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subCategory) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-warning" onclick="addNewSubCategory()">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="batch" class="form-label">Batch</label>
                    <select class="form-select" id="batch" name="batch">
                        <?php foreach ($batchOptions as $option): ?>
                            <option value="<?= $option ?>" <?= $item['batch'] === $option ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="importance" class="form-label">Importance</label>
                    <select class="form-select" id="importance" name="importance" required>
                        <option value="">Select Importance</option>
                        <?php foreach ($importanceLevels as $level): ?>
                            <option value="<?= $level ?>" <?= $item['importance'] === $level ? 'selected' : '' ?>>
                                <?= htmlspecialchars($level) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Qty</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" 
                           min="0" step="0.01" value="<?= $item['quantity'] ?>">
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="unit" name="unit" 
                               value="<?= htmlspecialchars($item['unit'] ?? 'no.s') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="store" class="form-label">Store</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="store" name="store" list="storesListEdit"
                               value="<?= htmlspecialchars($item['store'] ?? '') ?>">
                        <button type="button" class="btn btn-warning" onclick="openStoreModal()" title="Add/Edit Stores">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    <datalist id="storesListEdit"></datalist>
                </div>
                <div class="col-md-6">
                    <label for="active" class="form-label">Status</label>
                    <div class="input-group">
                        <select class="form-select" id="active" name="active">
                            <option value="1" <?= ($item['active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($item['active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <button type="button" class="btn btn-warning">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Item Type & Manufacturing/Purchase Options -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Item Type</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary <?= ($item['item_type'] ?? 'products') === 'products' ? 'active' : '' ?>" data-item-type="products">
                            <i class="bi bi-square-fill"></i> Products
                        </button>
                        <button type="button" class="btn btn-primary <?= ($item['item_type'] ?? '') === 'materials' ? 'active' : '' ?>" data-item-type="materials">
                            <i class="bi bi-square"></i> Materials
                        </button>
                        <button type="button" class="btn btn-primary <?= ($item['item_type'] ?? '') === 'spares' ? 'active' : '' ?>" data-item-type="spares">
                            <i class="bi bi-square"></i> Spares
                        </button>
                        <button type="button" class="btn btn-primary <?= ($item['item_type'] ?? '') === 'assemblies' ? 'active' : '' ?>" data-item-type="assemblies">
                            <i class="bi bi-square"></i> Assemblies
                        </button>
                    </div>
                    <input type="hidden" id="item_type" name="item_type" value="<?= htmlspecialchars($item['item_type'] ?? 'products') ?>">
                </div>
                <div class="col-md-4">
                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="internal_manufacturing" name="internal_manufacturing" 
                                   <?= ($item['internal_manufacturing'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="internal_manufacturing">
                                Internal Manufacturing
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="purchase" name="purchase" 
                                   <?= ($item['purchase'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="purchase">
                                Purchase
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Costing & Pricing -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="std_cost" class="form-label">Std. Cost</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="std_cost" name="std_cost" 
                               min="0" step="0.01" value="<?= $item['std_cost'] ?>">
                        <span class="input-group-text">/ no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="purch_cost" class="form-label">Purch. Cost</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="purch_cost" name="purch_cost" 
                               min="0" step="0.01" value="<?= $item['purch_cost'] ?? 0 ?>">
                        <span class="input-group-text">/ no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="std_sale_price" class="form-label">Std Sale Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="std_sale_price" name="std_sale_price" 
                               min="0" step="0.01" value="<?= $item['std_sale_price'] ?>">
                        <span class="input-group-text">/ no.s</span>
                    </div>
                </div>
            </div>
            
            <!-- Auto-calculated Rate and Value -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="rate" class="form-label">Rate (Auto)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="rate" name="rate" 
                               value="<?= $item['rate'] ?? 0 ?>" readonly>
                        <span class="input-group-text">/ no.s</span>
                    </div>
                    <small class="text-muted">Auto-filled from Std. Cost</small>
                </div>
                <div class="col-md-4">
                    <label for="value" class="form-label">Total Value (Auto)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="value" name="value" 
                               value="<?= $item['value'] ?? 0 ?>" readonly>
                    </div>
                    <small class="text-muted">Qty x Rate</small>
                </div>
                <div class="col-md-4">
                    <label for="hsn_sac" class="form-label">HSN/SAC</label>
                    <div class="input-group">
                        <select class="form-select" id="hsn_sac" name="hsn_sac">
                            <option value="">Select HSN/SAC</option>
                            <?php if (!empty($hsnList)):
                                foreach ($hsnList as $h): 
                                    $code = $h['code']; 
                                    $rate = isset($h['rate']) ? (float)$h['rate'] : null; 
                                    $sel = (string)($item['hsn_sac'] ?? '') === (string)$code ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($code) ?>" <?= $sel ?> <?= $rate !== null ? 'data-rate="'.htmlspecialchars($rate).'"' : '' ?>>
                                    <?= htmlspecialchars($code) ?><?= ($rate!==null && $rate!==0.0)?' ('.htmlspecialchars($rate).'%)':'' ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <a class="btn btn-warning" href="/?action=inventory&subaction=hsn" target="_blank" title="Manage HSN/SAC"><i class="bi bi-wrench"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="gst" class="form-label">GST</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="gst" name="gst" 
                               min="0" step="0.01" value="<?= $item['gst'] ?? 0 ?>">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="min_stock" class="form-label">Min Stock</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="min_stock" name="min_stock" 
                               min="0" step="0.01" value="<?= $item['min_stock'] ?? 0 ?>">
                        <span class="input-group-text">no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="lead_time" class="form-label">Lead Time</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="lead_time" name="lead_time" 
                               min="0" value="<?= $item['lead_time'] ?? 0 ?>">
                        <span class="input-group-text">days</span>
                    </div>
                </div>
            </div>
            
            <!-- Description and Notes -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                    <small class="text-muted">(will be suggested in documents like invoices, orders, etc.)</small>
                </div>
                <div class="col-md-6">
                    <label for="internal_notes" class="form-label">Internal Notes</label>
                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="4"><?= htmlspecialchars($item['internal_notes'] ?? '') ?></textarea>
                </div>
            </div>
            
            <!-- Tags -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="tags" class="form-label">Tags</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="tags" name="tags" 
                               value="<?= htmlspecialchars($item['tags'] ?? '') ?>" 
                               placeholder="Separate tags with commas">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Item type selection
document.querySelectorAll('[data-item-type]').forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('[data-item-type]').forEach(btn => {
            btn.classList.remove('active');
            btn.querySelector('i').className = 'bi bi-square';
        });
        
        // Add active class to clicked button
        this.classList.add('active');
        this.querySelector('i').className = 'bi bi-square-fill';
        
        // Update hidden input
        document.getElementById('item_type').value = this.dataset.itemType;
    });
});

// Auto-calculate rate and value
document.getElementById('std_cost').addEventListener('input', calculateValues);
document.getElementById('quantity').addEventListener('input', calculateValues);

function calculateValues() {
    const stdCost = parseFloat(document.getElementById('std_cost').value) || 0;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    document.getElementById('rate').value = stdCost.toFixed(2);
    document.getElementById('value').value = (quantity * stdCost).toFixed(2);
}

// Initialize calculations
calculateValues();

// Dependent Sub-Category logic (Edit form)
(function setupDependentSubCategories(){
  const catSelect = document.getElementById('category');
  const subSelect = document.getElementById('sub_category');
  if (!catSelect || !subSelect) return;
  const currentSub = String(<?= json_encode((string)($item['sub_category'] ?? '')) ?>);
  const loadSubs = async (category, selectVal = '') => {
    subSelect.innerHTML = '<option value="">Select Sub-Category</option>';
    if (!category) { return; }
    try {
      const res = await fetch('/?action=inventory&subaction=listSubCategories&category=' + encodeURIComponent(category));
      if (!res.ok) throw new Error('Failed');
      const subs = await res.json();
      subs.forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc; opt.textContent = sc;
        if (selectVal && String(sc) === String(selectVal)) opt.selected = true;
        subSelect.appendChild(opt);
      });
    } catch(e){ /* ignore */ }
  };
  // On change fetch
  catSelect.addEventListener('change', ()=> loadSubs(catSelect.value, ''));
  // Initial load with existing selection
  loadSubs(catSelect.value, currentSub);
})();

// Auto-fill GST when HSN selected (same as create.php)
document.addEventListener('DOMContentLoaded', function(){
  const hsnSel = document.getElementById('hsn_sac');
  const gstInput = document.getElementById('gst');
  if (hsnSel && gstInput) {
    const applyRate = () => {
      const opt = hsnSel.options[hsnSel.selectedIndex];
      const r = opt ? parseFloat(opt.getAttribute('data-rate')) : NaN;
      if (!isNaN(r)) { gstInput.value = r; }
    };
    hsnSel.addEventListener('change', applyRate);
    // Optionally set on load if GST is empty
    if (!gstInput.value) applyRate();
  }
});
</script>

<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-primary.active {
    background-color: #8B4513;
    border-color: #8B4513;
}

.btn-primary:not(.active) {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-primary:hover:not(.active) {
    background-color: #5a6268;
    border-color: #5a6268;
}

.breadcrumb-item a {
    color: #8B4513;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #6c757d;
}
</style>

<!-- Categories Manager Modal -->
<div class="modal fade" id="categoriesManagerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Categories</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-warning" id="catMgrAddCat" title="Add Category"><i class="bi bi-plus"></i></button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body" style="max-height: 60vh; overflow:auto;">
        <div id="catMgrList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Categories Manager Modal logic (same as create.php)
document.addEventListener('DOMContentLoaded', ()=>{
  const modalEl = document.getElementById('categoriesManagerModal');
  if (!modalEl) return;
  const listEl = modalEl.querySelector('#catMgrList');
  const addCatBtn = modalEl.querySelector('#catMgrAddCat');
  // Toast helper
  const toast = (msg, type = 'info') => {
    const div = document.createElement('div');
    div.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    div.setAttribute('role','alert');
    div.style.zIndex = 2000;
    div.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>`;
    document.body.appendChild(div);
    const t = new bootstrap.Toast(div, { delay: 2000 });
    t.show();
    div.addEventListener('hidden.bs.toast', ()=> div.remove());
  };
  const refresh = async ()=>{
    try {
      listEl.innerHTML = '<div class="text-muted py-3">Loading...</div>';
      const res = await fetch('/?action=inventory&subaction=listCategoriesWithSubs');
      const raw = await res.text();
      let data = [];
      try { data = JSON.parse(raw); } catch(e){ throw new Error(`HTTP ${res.status} ${res.statusText}: ${raw.slice(0,200)}`); }
      if (!res.ok) { const msg = (data && data.error) ? data.error : `HTTP ${res.status}`; throw new Error(msg); }
      listEl.innerHTML = '';
      (data||[]).forEach(c => {
        const item = document.createElement('div');
        item.className = 'border rounded p-2 mb-2';
        const subCount = (c.subs||[]).length;
        item.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">${c.name}</div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary" data-act="rename" data-id="${c.id}" title="Rename"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-primary" data-act="add-sub" data-id="${c.id}" title="Add Sub-Category"><i class="bi bi-plus"></i></button>
              <button class="btn btn-sm btn-outline-danger" data-act="delete" data-id="${c.id}" title="Delete"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="text-muted small">${subCount} sub-categor${subCount===1?'y':'ies'}</div>
        `;
        listEl.appendChild(item);
      });
    } catch(e){ listEl.innerHTML = `<div class="text-danger">Failed to load categories<br><small>${(e&&e.message)||e}</small></div>`; console.error('listCategoriesWithSubs failed', e); }
  };
  modalEl.addEventListener('shown.bs.modal', refresh);
  addCatBtn.addEventListener('click', async ()=>{
    const name = prompt('Enter category name:'); if(!name||!name.trim()) return;
    const fd = new FormData(); fd.append('name', name.trim());
    const r = await fetch('/?action=inventory&subaction=createCategory', { method:'POST', body: fd });
    const raw = await r.text(); let data = null; try { data = JSON.parse(raw); } catch(_){ }
    if (r.ok && data && data.id) { toast('Category created','success'); await refresh(); }
    else { toast('Failed to create category','danger'); console.error('createCategory failed', raw); }
  });
  listEl.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]'); if(!btn) return;
    const act = btn.dataset.act; const id = btn.dataset.id;
    if (act === 'rename'){
      const name = prompt('New category name:'); if(!name||!name.trim()) return;
      const fd = new FormData(); fd.append('id', id); fd.append('name', name.trim());
      const r = await fetch('/?action=inventory&subaction=updateCategory', { method:'POST', body: fd });
      if (r.ok) { toast('Category renamed','success'); refresh(); }
      else { const t = await r.text(); toast('Rename failed','danger'); console.error('updateCategory failed', t); }
    } else if (act === 'delete'){
      if (!confirm('Delete category and its sub-categories?')) return;
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch('/?action=inventory&subaction=deleteCategoryApi', { method:'POST', body: fd });
      if (r.ok) { toast('Category deleted','success'); refresh(); }
      else { const t = await r.text(); toast('Delete failed','danger'); console.error('deleteCategoryApi failed', t); }
    } else if (act === 'add-sub'){
      const name = prompt('Sub-category name:'); if(!name||!name.trim()) return;
      const fd = new FormData(); fd.append('category_id', id); fd.append('name', name.trim());
      const r = await fetch('/?action=inventory&subaction=createSubCategory', { method:'POST', body: fd });
      if (r.ok) { toast('Sub-category created','success'); refresh(); }
      else { const t = await r.text(); toast('Add sub-category failed','danger'); console.error('createSubCategory failed', t); }
    } else if (act === 'rename-sub'){
      const name = prompt('Rename sub-category:'); if(!name||!name.trim()) return;
      const fd = new FormData(); fd.append('id', id); fd.append('name', name.trim());
      const r = await fetch('/?action=inventory&subaction=updateSubCategory', { method:'POST', body: fd });
      if (r.ok) { toast('Sub-category renamed','success'); refresh(); }
      else { const t = await r.text(); toast('Rename sub failed','danger'); console.error('updateSubCategory failed', t); }
    } else if (act === 'delete-sub'){
      if (!confirm('Delete sub-category?')) return;
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch('/?action=inventory&subaction=deleteSubCategoryApi', { method:'POST', body: fd });
      if (r.ok) { toast('Sub-category deleted','success'); refresh(); }
      else { const t = await r.text(); toast('Delete sub failed','danger'); console.error('deleteSubCategoryApi failed', t); }
    }
  });
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
<?php // Reusable Stores modal for add/edit store inline
include __DIR__ . '/partials/stores_modal.php'; ?>

<script>
// Keep store suggestions in Edit via datalist like Create
async function loadStoresListEdit(){
  try {
    const res = await fetch('/?action=inventory&subaction=listStoresWithUsers');
    const data = await res.json();
    const dl = document.getElementById('storesListEdit');
    if (!dl) return;
    const prev = new Set();
    dl.innerHTML = '';
    (data||[]).forEach(s => {
      if (s && s.name && !prev.has(s.name)) {
        prev.add(s.name);
        const opt = document.createElement('option');
        opt.value = s.name; dl.appendChild(opt);
      }
    });
  } catch(e) { /* ignore */ }
}
document.addEventListener('DOMContentLoaded', loadStoresListEdit);
document.addEventListener('inventory:store:saved', function(e){
  const name = (e.detail && e.detail.name) ? e.detail.name : '';
  if (name) { const input = document.getElementById('store'); if (input) input.value = name; }
  loadStoresListEdit();
});
</script>
