<?php ob_start(); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="bi bi-plus-circle"></i> Enter Item
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
        <form id="inventoryForm" method="POST" action="/?action=inventory&subaction=store">
            <!-- Item Identification & Categorization -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Name*</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label fw-bold">Code*</label>
                    <input type="text" class="form-control" id="code" name="code" required>
                    <small class="text-muted">Prev. Code : CP PLUS HEAD/ PEOPLE COUNTING</small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <div class="input-group">
                        <select class="form-select" id="category" name="category">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
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
                                <option value="<?= htmlspecialchars($subCategory) ?>"><?= htmlspecialchars($subCategory) ?></option>
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
                            <option value="<?= htmlspecialchars($option) ?>" <?= ($option === 'No') ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
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
                            <option value="<?= htmlspecialchars($level) ?>" <?= ($level === 'Normal') ? 'selected' : '' ?>><?= htmlspecialchars($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Qty</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="0" step="0.01">
                </div>
                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <div class="input-group">
                        <select class="form-select" id="unit" name="unit">
                            <?php if (!empty($units)):
                                foreach ($units as $u): $code = $u['code']; ?>
                                    <option value="<?= htmlspecialchars($code) ?>" <?= ($code==='no.s')?'selected':'' ?>><?= htmlspecialchars($code) ?></option>
                                <?php endforeach; else: ?>
                                    <option value="no.s" selected>no.s</option>
                            <?php endif; ?>
                        </select>
                        
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="store" class="form-label">Store</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="store" name="store" list="storesListCreate">
                        <button type="button" class="btn btn-warning" onclick="openStoreModal()" title="Add/Edit Stores">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    <datalist id="storesListCreate"></datalist>
                </div>
                <div class="col-md-6">
                    <label for="active" class="form-label">Status</label>
                    <select class="form-select" id="active" name="active">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            
            <!-- Item Type & Manufacturing/Purchase Options -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Item Type</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary active" data-item-type="products">
                            <i class="bi bi-square-fill"></i> Products
                        </button>
                        <button type="button" class="btn btn-primary" data-item-type="materials">
                            <i class="bi bi-square"></i> Materials
                        </button>
                        <button type="button" class="btn btn-primary" data-item-type="spares">
                            <i class="bi bi-square"></i> Spares
                        </button>
                        <button type="button" class="btn btn-primary" data-item-type="assemblies">
                            <i class="bi bi-square"></i> Assemblies
                        </button>
                    </div>
                    <input type="hidden" id="item_type" name="item_type" value="products">
                </div>
                <div class="col-md-4">
                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="internal_manufacturing" name="internal_manufacturing" checked>
                            <label class="form-check-label" for="internal_manufacturing">
                                Internal Manufacturing
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="purchase" name="purchase">
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
                        <input type="number" class="form-control" id="std_cost" name="std_cost" min="0" step="0.01">
                        <span class="input-group-text">/ no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="purch_cost" class="form-label">Purch. Cost</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="purch_cost" name="purch_cost" min="0" step="0.01">
                        <span class="input-group-text">/ no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="std_sale_price" class="form-label">Std Sale Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="std_sale_price" name="std_sale_price" min="0" step="0.01">
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
                        <input type="number" class="form-control" id="rate" name="rate" readonly>
                        <span class="input-group-text">/ no.s</span>
                    </div>
                    <small class="text-muted">Auto-filled from Std. Cost</small>
                </div>
                <div class="col-md-4">
                    <label for="value" class="form-label">Total Value (Auto)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control" id="value" name="value" readonly>
                    </div>
                    <small class="text-muted">Qty × Rate</small>
                </div>
            </div>
            
            <!-- Tax & Description -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="hsn_sac" class="form-label">HSN/SAC</label>
                    <div class="input-group">
                        <select class="form-select" id="hsn_sac" name="hsn_sac">
                            <option value="">Select HSN/SAC</option>
                            <?php if (!empty($hsnList)):
                                foreach ($hsnList as $h): $code = $h['code']; $rate = isset($h['rate']) ? (float)$h['rate'] : null; ?>
                                    <option value="<?= htmlspecialchars($code) ?>" <?= $rate !== null ? 'data-rate="'.htmlspecialchars($rate).'"' : '' ?>><?= htmlspecialchars($code) ?><?= ($rate!==null && $rate!==0.0)?' ('.htmlspecialchars($rate).'%)':'' ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                        <a class="btn btn-warning" href="/?action=inventory&subaction=hsn" target="_blank" title="Manage HSN/SAC"><i class="bi bi-wrench"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="gst" class="form-label">GST</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="gst" name="gst" min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" 
                              placeholder="(will be suggested in documents like invoices, orders, etc.)"></textarea>
                </div>
                <div class="col-md-6">
                    <label for="internal_notes" class="form-label">Internal Notes</label>
                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="4"></textarea>
                </div>
            </div>
            
            <!-- Stock & Lead Time -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="min_stock" class="form-label">Min Stock</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="min_stock" name="min_stock" min="0" step="0.01">
                        <span class="input-group-text">no.s</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="lead_time" class="form-label">Lead Time</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="lead_time" name="lead_time" min="0">
                        <span class="input-group-text">days</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="tags" class="form-label">Tags</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="tags" name="tags">
                    </div>
                </div>
            </div>
            
            <!-- Footer Save Button -->
            <div class="row mt-4">
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

// Quick add Unit and refresh select
async function quickAddUnit(){
    const code = prompt('Enter unit code (e.g., pcs, no.s, kg)');
    if (!code || !code.trim()) return;
    try {
        const fd = new FormData();
        fd.append('code', code.trim());
        const r = await fetch('/?action=inventory&subaction=createUnit', { method:'POST', body: fd });
        const raw = await r.text(); let data = null; try { data = JSON.parse(raw); } catch(_){ }
        if (!r.ok || !data || !data.id) { showMessage('Failed to create unit','danger'); return; }
        // refresh units list
        const res = await fetch('/?action=inventory&subaction=listUnits');
        const units = await res.json();
        const sel = document.getElementById('unit');
        const prev = code.trim();
        sel.innerHTML = '';
        (units||[]).forEach(u=>{
            const o = document.createElement('option');
            o.value = u.code; o.textContent = u.code; if (u.code===prev) o.selected = true; sel.appendChild(o);
        });
        showMessage('Unit added','success');
    } catch (e) { showMessage('Error adding unit','danger'); }
}

.btn-primary.active {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #ffca2c;
    border-color: #ffc107;
    color: #000;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    color: #6c757d;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.btn-dark {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}
</style>

<script>
// Handle item type button selection
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

// Auto-calculate rate and value when std_cost or quantity changes
document.getElementById('std_cost').addEventListener('input', calculateRateAndValue);
document.getElementById('quantity').addEventListener('input', calculateRateAndValue);

function calculateRateAndValue() {
    const stdCost = parseFloat(document.getElementById('std_cost').value) || 0;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    // Update rate (same as std_cost)
    document.getElementById('rate').value = stdCost.toFixed(2);
    
    // Calculate total value
    const totalValue = quantity * stdCost;
    document.getElementById('value').value = totalValue.toFixed(2);
    
    console.log('Calculated - Rate:', stdCost, 'Value:', totalValue);
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateRateAndValue();
    // Initialize dependent sub-categories on first load
    setupDependentSubCategories();
    // Auto-fill GST when HSN selected
    const hsnSel = document.getElementById('hsn_sac');
    const gstInput = document.getElementById('gst');
    if (hsnSel && gstInput) {
        hsnSel.addEventListener('change', function(){
            const opt = this.options[this.selectedIndex];
            const r = opt ? parseFloat(opt.getAttribute('data-rate')) : NaN;
            if (!isNaN(r)) { gstInput.value = r; }
        });
    }
});

// After saving a store from the modal, set the store name in this form (if provided)
document.addEventListener('inventory:store:saved', function(e){
    const name = (e.detail && e.detail.name) ? e.detail.name : '';
    if (name) { const input = document.getElementById('store'); if (input) input.value = name; }
    loadStoresListCreate();
});

// Load stores names into datalist for quick selection
async function loadStoresListCreate(){
  try {
    const res = await fetch('/?action=inventory&subaction=listStoresWithUsers');
    const data = await res.json();
    const dl = document.getElementById('storesListCreate');
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
  } catch(e) { /* silent */ }
}
loadStoresListCreate();

// Function to add new category
async function addNewCategory() {
    const name = prompt('Enter new category name:');
    if (!name || !name.trim()) return;
    try {
        const fd = new FormData();
        fd.append('name', name.trim());
        const res = await fetch('/?action=inventory&subaction=createCategory', { method: 'POST', body: fd });
        if (!res.ok) throw new Error('Failed');
        const data = await res.json();
        if (data && data.id) {
            const select = document.getElementById('category');
            // if option not present, add
            let opt = Array.from(select.options).find(o => o.value === data.name);
            if (!opt) {
                opt = document.createElement('option');
                opt.value = data.name; opt.textContent = data.name;
                select.appendChild(opt);
            }
            select.value = data.name;
            // Load sub-categories for new category (will be empty initially)
            if (typeof setupDependentSubCategories === 'function') {
                const evt = new Event('change'); select.dispatchEvent(evt);
            }
            showMessage('Category created', 'success');
        } else { showMessage('Unable to create category', 'danger'); }
    } catch(e) { showMessage('Error creating category', 'danger'); }
}

// Function to add new subcategory
async function setupDependentSubCategories(){
    const catSelect = document.getElementById('category');
    const subSelect = document.getElementById('sub_category');
    if (!catSelect || !subSelect) return;
    const loadSubs = async (category) => {
        const res = await fetch('/?action=inventory&subaction=listSubCategories&category=' + encodeURIComponent(category));
        const subs = await res.json();
        subSelect.innerHTML = '<option value="">Select Sub-Category</option>';
        subs.forEach(sc => { const o=document.createElement('option'); o.value=sc; o.textContent=sc; subSelect.appendChild(o); });
    };
    // On change fetch
    catSelect.addEventListener('change', ()=> loadSubs(catSelect.value));
    // Initial populate if category is pre-selected
    if (catSelect.value) { loadSubs(catSelect.value); }
}

// Function to add new subcategory
async function addNewSubCategory() {
    const catSelect = document.getElementById('category');
    const categoryName = catSelect.value;
    if (!categoryName) { showMessage('Please select a category first', 'warning'); return; }
    const name = prompt('Enter new sub-category name:');
    if (!name || !name.trim()) return;
    try {
        // Resolve category_id by listing categories
        const resCats = await fetch('/?action=inventory&subaction=listCategories');
        const cats = await resCats.json();
        const match = (cats||[]).find(c => String(c.name) === String(categoryName));
        if (!match) { showMessage('Category not found', 'danger'); return; }
        const fd = new FormData();
        fd.append('category_id', match.id);
        fd.append('name', name.trim());
        const res = await fetch('/?action=inventory&subaction=createSubCategory', { method: 'POST', body: fd });
        if (!res.ok) throw new Error('Failed');
        const data = await res.json();
        if (data && data.id) {
            // Refresh sub-categories list for selected category
            const subSelect = document.getElementById('sub_category');
            subSelect.innerHTML = '<option value="">Select Sub-Category</option>';
            const resSubs = await fetch('/?action=inventory&subaction=listSubCategories&category=' + encodeURIComponent(categoryName));
            const subs = await resSubs.json();
            subs.forEach(sc => { const o=document.createElement('option'); o.value=sc; o.textContent=sc; subSelect.appendChild(o); });
            subSelect.value = name.trim();
            showMessage('Sub-category created', 'success');
        } else { showMessage('Unable to create sub-category', 'danger'); }
    } catch(e) { showMessage('Error creating sub-category', 'danger'); }
}

// Function to show messages
function showMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    messageDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    messageDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 3000);
}

// ================= Categories Manager Modal =================
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
          <div class="text-muted small mb-2">${subCount} sub-categor${subCount===1?'y':'ies'}</div>
          <ul class="list-unstyled ms-2 mb-0" data-subs-of="${c.id}"></ul>
        `;
        const ul = item.querySelector('ul');
        (c.subs||[]).forEach(s=>{
          const li = document.createElement('li');
          li.className = 'd-flex align-items-center justify-content-between py-1 border-bottom';
          li.innerHTML = `
            <span>${s.name}</span>
            <span class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary" data-act="rename-sub" data-id="${s.id}" title="Rename Sub"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" data-act="delete-sub" data-id="${s.id}" title="Delete Sub"><i class="bi bi-trash"></i></button>
            </span>
          `;
          ul.appendChild(li);
        });
        listEl.appendChild(item);
      });
    } catch(e){ listEl.innerHTML = `<div class="text-danger">Failed to load categories<br><small>${(e&&e.message)||e}</small></div>`; console.error('listCategoriesWithSubs failed', e); }
  };
  modalEl.addEventListener('shown.bs.modal', refresh);
  addCatBtn.addEventListener('click', async ()=>{
    const name = prompt('Enter category name:'); if(!name||!name.trim()) return;
    const fd = new FormData(); fd.append('name', name.trim());
    const r = await fetch('/?action=inventory&subaction=createCategory', { method:'POST', body: fd });
    const raw = await r.text(); let data = null; try { data = JSON.parse(raw); } catch(_){}
    if (r.ok && data && data.id) { toast('Category created','success'); await refresh(); }
    else { toast(`Failed to create category${data&&data.error?`: ${data.error}`:''}`,'danger'); console.error('createCategory failed', raw); }
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

<?php
// Include reusable Stores modal (needs $users/$stores set by controller)
include __DIR__ . '/partials/stores_modal.php';
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>