<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-tags"></i> HSN / SAC</h4>
  <div class="d-flex gap-2">
    <button class="btn btn-warning" id="btnAddHsn" title="Add HSN/SAC"><i class="bi bi-plus"></i></button>
    <a href="/?action=inventory&subaction=settings" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Return to Inventory</a>
  </div>
</div>

<div id="hsnList">
  <?php if (empty($hsn)): ?>
    <div class="alert alert-info">No HSN/SAC codes yet. Click + to add your first code.</div>
  <?php else: ?>
    <?php foreach ($hsn as $h): ?>
      <div class="border rounded p-2 mb-2 hsn-row" data-id="<?= (int)$h['id'] ?>">
        <div class="row g-2 align-items-center">
          <div class="col-md-3">
            <input class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($h['code']) ?>" data-field="code" placeholder="HSN/SAC code">
          </div>
          <div class="col-md-2">
            <div class="input-group input-group-sm">
              <input class="form-control" type="number" step="0.01" min="0" value="<?= htmlspecialchars($h['rate']) ?>" data-field="rate" placeholder="%">
              <span class="input-group-text">%</span>
            </div>
          </div>
          <div class="col-md-5">
            <input class="form-control form-control-sm" type="text" value="<?= htmlspecialchars($h['note']) ?>" data-field="note" placeholder="Note (optional)">
          </div>
          <div class="col-md-1 form-check text-center">
            <input class="form-check-input" type="checkbox" data-field="active" <?= ($h['active']? 'checked' : '') ?> title="Active">
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

<!-- Add HSN Modal -->
<div class="modal fade" id="addHsnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add HSN/SAC</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label form-label-sm">HSN/SAC</label>
          <input type="text" id="hsnCode" class="form-control form-control-sm" placeholder="e.g., 8473">
        </div>
        <div class="mb-2">
          <label class="form-label form-label-sm">Percentage</label>
          <div class="input-group input-group-sm">
            <input type="number" id="hsnRate" class="form-control" step="0.01" min="0" placeholder="18">
            <span class="input-group-text">%</span>
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label form-label-sm">Note</label>
          <input type="text" id="hsnNote" class="form-control form-control-sm" placeholder="Note (optional)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="btnSaveHsn"><i class="bi bi-check"></i> Save</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const list = document.getElementById('hsnList');
  const addBtn = document.getElementById('btnAddHsn');
  const addModalEl = document.getElementById('addHsnModal');
  let addModal = null;

  function toast(msg, type='info'){
    const div = document.createElement('div');
    div.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    div.innerHTML = `<div class=\"d-flex\"><div class=\"toast-body\">${msg}</div><button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" data-bs-dismiss=\"toast\"></button></div>`;
    document.body.appendChild(div);
    const t = new bootstrap.Toast(div, { delay: 1800 }); t.show();
    div.addEventListener('hidden.bs.toast', ()=>div.remove());
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[m])); }

  function rowEl(h){
    const wrap = document.createElement('div');
    wrap.className = 'border rounded p-2 mb-2 hsn-row';
    wrap.dataset.id = h.id;
    wrap.innerHTML = `
      <div class=\"row g-2 align-items-center\">
        <div class=\"col-md-3\"><input class=\"form-control form-control-sm\" type=\"text\" value=\"${escapeHtml(h.code||'')}\" data-field=\"code\" placeholder=\"HSN/SAC code\"></div>
        <div class=\"col-md-2\"><div class=\"input-group input-group-sm\"><input class=\"form-control\" type=\"number\" step=\"0.01\" min=\"0\" value=\"${escapeHtml(h.rate||'')}\" data-field=\"rate\" placeholder=\"%\"><span class=\"input-group-text\">%</span></div></div>
        <div class=\"col-md-5\"><input class=\"form-control form-control-sm\" type=\"text\" value=\"${escapeHtml(h.note||'')}\" data-field=\"note\" placeholder=\"Note (optional)\"></div>
        <div class=\"col-md-1 form-check text-center\"><input class=\"form-check-input\" type=\"checkbox\" data-field=\"active\" ${h.active? 'checked' : ''}></div>
        <div class=\"col-md-1 text-end\"><div class=\"btn-group\"><button class=\"btn btn-sm btn-outline-secondary\" data-act=\"save\" title=\"Save\"><i class=\"bi bi-check\"></i></button><button class=\"btn btn-sm btn-outline-danger\" data-act=\"delete\" title=\"Delete\"><i class=\"bi bi-trash\"></i></button></div></div>
      </div>`;
    return wrap;
  }

  async function reload(){
    const res = await fetch('/?action=inventory&subaction=listHsn');
    const data = await res.json();
    list.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0){
      list.innerHTML = '<div class="alert alert-info">No HSN/SAC codes yet. Click + to add your first code.</div>';
      return;
    }
    data.forEach(h=>list.appendChild(rowEl(h)));
  }

  if (addBtn) {
    addBtn.addEventListener('click', ()=>{
      if (addModalEl && window.bootstrap && !addModal) {
        try { addModal = new bootstrap.Modal(addModalEl); } catch (e) { console.error('Bootstrap Modal init failed', e); }
      }
      if (!addModalEl) { console.error('Add HSN modal element not found'); return; }
      addModalEl.querySelector('#hsnCode').value = '';
      addModalEl.querySelector('#hsnRate').value = '';
      addModalEl.querySelector('#hsnNote').value = '';
      if (addModal && typeof addModal.show === 'function') {
        addModal.show();
      } else {
        // Fallback: prompt if Bootstrap JS not available
        const code = prompt('HSN/SAC code'); if (!code) return;
        const rate = prompt('Percentage (e.g., 18)') || '';
        const note = prompt('Note (optional)') || '';
        const fd = new FormData(); fd.append('code', code.trim()); if (rate!=='') fd.append('rate', rate.trim()); if (note!=='') fd.append('note', note.trim());
        fetch('/?action=inventory&subaction=createHsn', { method:'POST', body: fd })
          .then(r=>{ if(!r.ok) throw new Error('Add failed'); return r.json(); })
          .then(()=> { toast('HSN added','success'); reload(); })
          .catch(()=> toast('Add failed','danger'));
      }
    });
  }

  document.getElementById('btnSaveHsn').addEventListener('click', async ()=>{
    const code = addModalEl.querySelector('#hsnCode').value.trim();
    const rate = addModalEl.querySelector('#hsnRate').value;
    const note = addModalEl.querySelector('#hsnNote').value.trim();
    if (!code) { toast('HSN/SAC required','danger'); return; }
    const fd = new FormData(); fd.append('code', code); if (rate!=='') fd.append('rate', rate); if (note!=='') fd.append('note', note);
    const r = await fetch('/?action=inventory&subaction=createHsn', { method:'POST', body: fd });
    if (r.ok) { toast('HSN added','success'); addModal.hide(); reload(); } else { toast('Add failed','danger'); }
  });

  list.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-act]'); if (!btn) return;
    const row = btn.closest('.hsn-row'); const id = row.dataset.id;
    if (btn.dataset.act === 'save'){
      const code = row.querySelector('[data-field="code"]').value.trim();
      const rate = row.querySelector('[data-field="rate"]').value;
      const note = row.querySelector('[data-field="note"]').value.trim();
      const active = row.querySelector('[data-field="active"]').checked ? 1 : 0;
      const fd = new FormData(); fd.append('id', id); fd.append('code', code); if (rate!=='') fd.append('rate', rate); fd.append('note', note); fd.append('active', active);
      const r = await fetch('/?action=inventory&subaction=updateHsn', { method:'POST', body: fd });
      if (r.ok) toast('Saved','success'); else toast('Save failed','danger');
    } else if (btn.dataset.act === 'delete'){
      if (!confirm('Delete this HSN/SAC?')) return;
      const fd = new FormData(); fd.append('id', id);
      const r = await fetch('/?action=inventory&subaction=deleteHsn', { method:'POST', body: fd });
      if (r.ok) { row.remove(); toast('Deleted','success'); } else toast('Delete failed','danger');
    }
  });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
