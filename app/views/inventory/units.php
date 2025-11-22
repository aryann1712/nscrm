<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-sliders"></i> Units</h4>
  <div class="d-flex gap-2">
    <button class="btn btn-warning" id="btnAddUnit" title="Add Unit"><i class="bi bi-plus-lg"></i></button>
    <a href="/?action=inventory&subaction=settings" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Return to Inventory</a>
  </div>
</div>

<div id="unitsList">
  <?php if (empty($units)): ?>
    <div class="alert alert-info">No units yet. Click + to add your first unit.</div>
  <?php else: ?>
    <?php foreach ($units as $u): ?>
      <div class="border rounded p-2 mb-2 unit-row" data-id="<?= (int)$u['id'] ?>">
        <div class="row g-2 align-items-center">
          <div class="col-md-3">
            <input class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($u['code']) ?>" data-field="code" placeholder="Code (e.g., pcs, kg)">
          </div>
          <div class="col-md-4">
            <input class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($u['label']) ?>" data-field="label" placeholder="Label (optional)">
          </div>
          <div class="col-md-3">
            <input class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($u['precision_format']) ?>" data-field="precision_format" placeholder="Precision (e.g., N, N/N, N.NN)">
          </div>
          <div class="col-md-1 form-check text-center">
            <input class="form-check-input" type="checkbox" data-field="active" <?= ($u['active']? 'checked' : '') ?> title="Active">
          </div>
          <div class="col-md-1 text-end">
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-secondary" data-act="save" title="Save"><i class="bi bi-check"></i></button>
              <button class="btn btn-sm btn-outline-danger" data-act="delete" title="Delete"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<style>
.unit-row { background-color: #fff; }
</style>

<script>
(function(){
  const list = document.getElementById('unitsList');

  function toast(msg, type='info'){
    const div = document.createElement('div');
    div.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    div.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(div);
    const t = new bootstrap.Toast(div, { delay: 1500 }); t.show();
    div.addEventListener('hidden.bs.toast', ()=>div.remove());
  }

  async function reload(){
    const res = await fetch('/?action=inventory&subaction=listUnits');
    const data = await res.json();
    list.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0){
      list.innerHTML = '<div class="alert alert-info">No units yet. Click + to add your first unit.</div>';
      return;
    }
    data.forEach(u=>list.appendChild(rowEl(u)));
  }

  function rowEl(u){
    const wrap = document.createElement('div');
    wrap.className = 'border rounded p-2 mb-2 unit-row';
    wrap.dataset.id = u.id;
    wrap.innerHTML = `
      <div class="row g-2 align-items-center">
        <div class="col-md-3"><input class="form-control form-control-sm" type="text" value="${escapeHtml(u.code||'')}" data-field="code" placeholder="Code (e.g., pcs, kg)"></div>
        <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="${escapeHtml(u.label||'')}" data-field="label" placeholder="Label (optional)"></div>
        <div class="col-md-3"><input class="form-control form-control-sm" type="text" value="${escapeHtml(u.precision_format||'')}" data-field="precision_format" placeholder="Precision (e.g., N, N/N, N.NN)"></div>
        <div class="col-md-1 form-check text-center"><input class="form-check-input" type="checkbox" data-field="active" ${u.active? 'checked' : ''}></div>
        <div class="col-md-1 text-end"><div class="btn-group">
          <button class="btn btn-sm btn-outline-secondary" data-act="save" title="Save"><i class="bi bi-check"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-act="delete" title="Delete"><i class="bi bi-trash"></i></button>
        </div></div>
      </div>`;
    return wrap;
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[m])); }

  document.getElementById('btnAddUnit').addEventListener('click', async ()=>{
    const code = prompt('Enter unit code (e.g., pcs, kg, no.s)');
    if (!code || !code.trim()) return;
    const fd = new FormData(); fd.append('code', code.trim());
    const r = await fetch('/?action=inventory&subaction=createUnit', { method:'POST', body: fd });
    if (r.ok) { toast('Unit added','success'); reload(); } else { toast('Add failed','danger'); }
  });

  list.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]'); if (!btn) return;
    const row = btn.closest('.unit-row'); const id = row.dataset.id;
    if (btn.dataset.act === 'save'){
      const code = row.querySelector('[data-field="code"]').value.trim();
      const label = row.querySelector('[data-field="label"]').value.trim();
      const prec = row.querySelector('[data-field="precision_format"]').value.trim();
      const active = row.querySelector('[data-field="active"]').checked ? 1 : 0;
      const fd = new FormData(); fd.append('id', id); fd.append('code', code); fd.append('label', label); fd.append('precision_format', prec); fd.append('active', active);
      const r = await fetch('/?action=inventory&subaction=updateUnit', { method:'POST', body: fd });
      if (r.ok) toast('Saved','success'); else toast('Save failed','danger');
    } else if (btn.dataset.act === 'delete'){
      if (!confirm('Delete this unit?')) return;
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch('/?action=inventory&subaction=deleteUnit', { method:'POST', body: fd });
      if (r.ok) { row.remove(); toast('Deleted','success'); } else toast('Delete failed','danger');
    }
  });
})();
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
