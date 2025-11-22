<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Settings</h4>
  <a href="/?action=inventory" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Return to Inventory</a>
</div>

<div class="row gy-3">
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-primary text-white py-2">Stores & Rights</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Configure stores and control the accessibility by giving rights.</p>
        <div>
          <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#storesManagerModal">Manage</button>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-primary text-white py-2">Categories</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Add and update categories as well as sub-categories for inventory items.</p>
        <a class="btn btn-outline-primary btn-sm" href="/?action=inventory&subaction=taxonomy">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-primary text-white py-2">Units</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Configure units and precision for inventory items.</p>
        <a class="btn btn-outline-primary btn-sm" href="/?action=inventory&subaction=units">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-primary text-white py-2">Valuation</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Select the valuation mode.</p>
        <button class="btn btn-outline-secondary btn-sm" disabled>Open</button>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-secondary text-white py-2">HSN / SAC</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Add HSN/SAC code for the inventory items.</p>
        <a class="btn btn-outline-primary btn-sm" href="/?action=inventory&subaction=hsn">Manage</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-secondary text-white py-2">BOM Print Configuration</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Configure print settings for BOM details.</p>
        <button class="btn btn-outline-secondary btn-sm" disabled>Open</button>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-secondary text-white py-2">Stage Flow</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Configure the stage flow for the inventory items.</p>
        <button class="btn btn-outline-secondary btn-sm" disabled>Open</button>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header bg-secondary text-white py-2">Production Stages</div>
      <div class="card-body small">
        <p class="mb-3 text-muted">Configure and manage the stages involved in the production process.</p>
        <button class="btn btn-outline-secondary btn-sm" disabled>Open</button>
      </div>
    </div>
  </div>
</div>

<style>
.card-header { font-weight: 600; }
</style>

<?php
// Include the reusable Stores modal so clicking Open shows it inline
include __DIR__ . '/partials/stores_modal.php';
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

<!-- Stores Manager Modal -->
<div class="modal fade" id="storesManagerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Stores</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-warning" onclick="openStoreModal()" title="Add Store"><i class="bi bi-plus"></i></button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body" style="max-height: 60vh; overflow:auto;">
        <div id="storesMgrList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<!-- Inline Rights Modal for quick set from Stores list -->
<div class="modal fade" id="rightsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Rights for <span id="rights_user_name"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/?action=settings&subaction=saveRights">
      <div class="modal-body">
        <input type="hidden" name="user_id" id="rights_user_id">
        <div class="row">
          <div class="col-md-6">
            <h6>CRM Tools</h6>
            <div class="mb-2">
              <label class="form-label">Leads & Prospects</label>
              <select class="form-select form-select-sm" name="rights[crm][leads]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Customers</label>
              <select class="form-select form-select-sm" name="rights[crm][customers]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Quotations</label>
              <select class="form-select form-select-sm" name="rights[crm][quotations]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Invoices</label>
              <select class="form-select form-select-sm" name="rights[crm][invoices]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <h6>ERP Tools</h6>
            <div class="mb-2">
              <label class="form-label">Inventory</label>
              <select class="form-select form-select-sm" name="rights[erp][inventory]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Orders</label>
              <select class="form-select form-select-sm" name="rights[erp][orders]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Default Page</label>
              <select class="form-select form-select-sm" name="rights[b2b][default_page]">
                <option value="home">Home</option>
                <option value="leads">Leads</option>
                <option value="customers">Customers</option>
                <option value="quotes">Quotes</option>
                <option value="invoices">Invoices</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
// Stores Manager modal logic
document.addEventListener('DOMContentLoaded', ()=>{
  const modalEl = document.getElementById('storesManagerModal');
  if (!modalEl) return;
  const listEl = modalEl.querySelector('#storesMgrList');
  const toast = (msg, type = 'info') => {
    const div = document.createElement('div');
    div.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    div.setAttribute('role','alert'); div.style.zIndex = 2000;
    div.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(div);
    const t = new bootstrap.Toast(div, { delay: 2000 }); t.show();
    div.addEventListener('hidden.bs.toast', ()=> div.remove());
  };
  const refresh = async ()=>{
    try {
      listEl.innerHTML = '<div class="text-muted py-3">Loading...</div>';
      const res = await fetch('/?action=inventory&subaction=listStoresWithUsers');
      const raw = await res.text(); let data = [];
      try { data = JSON.parse(raw); } catch(e){ throw new Error(`HTTP ${res.status} ${res.statusText}: ${raw.slice(0,200)}`); }
      if (!res.ok) { const msg = (data && data.error) ? data.error : `HTTP ${res.status}`; throw new Error(msg); }
      listEl.innerHTML = '';
      (data||[]).forEach(s => {
        const item = document.createElement('div');
        item.className = 'border rounded p-2 mb-2';
        const userCount = (s.users||[]).length;
        item.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">${s.name}</div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary" data-act="edit" data-id="${s.id}" data-name="${s.name}" title="Edit"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" data-act="delete" data-id="${s.id}" title="Delete"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="text-muted small mb-2">${userCount} user${userCount===1?'':'s'}</div>
          <ul class="list-unstyled ms-2 mb-0" data-users-of="${s.id}"></ul>`;
        const ul = item.querySelector('ul');
        (s.users||[]).forEach(u => {
          const li = document.createElement('li');
          li.className = 'd-flex align-items-center justify-content-between py-1 border-bottom';
          li.innerHTML = `<span>${u.name}</span>
            <span><button class="btn btn-sm btn-link p-0" data-act="rights" data-userid="${u.id}" data-username="${u.name}">Set Rights</button></span>`;
          ul.appendChild(li);
        });
        listEl.appendChild(item);
      });
    } catch(e){ listEl.innerHTML = `<div class="text-danger">Failed to load stores<br><small>${(e&&e.message)||e}</small></div>`; console.error('listStoresWithUsers failed', e); }
  };
  modalEl.addEventListener('shown.bs.modal', refresh);
  listEl.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]'); if (!btn) return;
    const act = btn.dataset.act;
    if (act === 'edit') {
      openStoreModal(btn.dataset.id, btn.dataset.name);
    } else if (act === 'delete') {
      if (!confirm('Delete this store?')) return;
      const fd = new FormData(); fd.append('store_id', btn.dataset.id);
      const r = await fetch('/?action=inventory&subaction=delete_store', { method:'POST', body: fd });
      if (r.ok) { toast('Store deleted','success'); refresh(); }
      else { const t = await r.text(); toast('Delete failed','danger'); console.error('delete_store failed', t); }
    } else if (act === 'rights') {
      const uid = btn.dataset.userid; const uname = btn.dataset.username;
      openRightsModal(uid, uname);
    }
  });
  document.addEventListener('inventory:store:saved', ()=> refresh());
});

function openRightsModal(userId, userName) {
  document.getElementById('rights_user_id').value = userId;
  document.getElementById('rights_user_name').textContent = userName;
  const m = new bootstrap.Modal(document.getElementById('rightsModal'));
  m.show();
}
</script>
