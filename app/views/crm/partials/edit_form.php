<?php
// $leadData (array) and $ownerUsers (array) expected
$ld = $leadData ?? [];
?>
<form id="leadEditForm" method="POST" action="/?action=crm&subaction=update">
  <input type="hidden" name="id" value="<?= (int)($ld['id'] ?? 0) ?>">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Business Name *</label>
      <input type="text" class="form-control" name="business_name" value="<?= htmlspecialchars($ld['business_name'] ?? '') ?>" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Contact Person *</label>
      <input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($ld['contact_person'] ?? '') ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Contact Email</label>
      <input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($ld['contact_email'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Contact Phone</label>
      <input type="tel" class="form-control" name="contact_phone" value="<?= htmlspecialchars($ld['contact_phone'] ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Source</label>
      <?php $src = (string)($ld['source'] ?? ''); ?>
      <div class="input-group">
        <select class="form-select" name="source" id="sourceSelectEdit">
          <option value="">Select Source</option>
        </select>
        <button type="button" class="btn btn-outline-success" onclick="if(window.openAddSourceModal){openAddSourceModal();}">+</button>
      </div>
      <small class="text-muted">Configured in Sales Configuration › Sources</small>
    </div>
    <div class="col-md-4">
      <label class="form-label">Stage</label>
      <?php $stg = (string)($ld['stage'] ?? ''); ?>
      <select class="form-select" name="stage">
        <option value="">Select Stage</option>
        <?php foreach (["Raw"=>"Raw (Unqualified)","New"=>"New","Discussion"=>"Discussion","Demo"=>"Demo","Proposal"=>"Proposal","Decided"=>"Decided","Inactive"=>"Inactive"] as $k=>$lbl): ?>
          <option value="<?= $k ?>" <?= $stg===$k?'selected':'' ?>><?= $lbl ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Assigned To</label>
      <?php $ass = (string)($ld['assigned_to'] ?? ''); ?>
      <select class="form-select" name="assigned_to" id="assigned_to_edit">
        <option value="">Select Assignee</option>
        <?php if (!empty($ownerUsers)):
          foreach ($ownerUsers as $u): $uid=(int)$u['id']; $uname=trim($u['name']??''); $isOwner=(int)($u['is_owner']??0)===1; ?>
          <option value="<?= htmlspecialchars($uname) ?>" data-id="<?= $uid ?>" <?= ($ass===$uname)?'selected':'' ?>><?= htmlspecialchars($uname . ($isOwner?" (Owner)":"")) ?></option>
        <?php endforeach; endif; ?>
        <option value="Unassigned" <?= ($ass==='Unassigned')?'selected':'' ?>>Unassigned</option>
      </select>
      <input type="hidden" name="assigned_to_user_id" id="assigned_to_user_id_edit" value="">
    </div>

    <div class="col-md-6">
      <label class="form-label">City</label>
      <div class="input-group">
        <input type="text" class="form-control" name="city" id="cityInputEdit" value="<?= htmlspecialchars($ld['city'] ?? '') ?>">
        <button type="button" class="btn btn-outline-success" onclick="if(window.openAddCityModal){openAddCityModal();}">+</button>
      </div>
      <div id="citySuggestionsEdit" class="mt-1"></div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Product</label>
      <div class="input-group">
        <input type="text" class="form-control" name="product" id="productInputEdit" value="<?= htmlspecialchars($ld['product'] ?? '') ?>">
        <button type="button" class="btn btn-outline-success" onclick="addProductEdit()">+</button>
      </div>
      <div id="productSuggestionsEdit" class="mt-1"></div>
    </div>

    <div class="col-md-12">
      <label class="form-label">Tags</label>
      <div class="input-group">
        <input type="text" class="form-control" name="tags" id="tagsInputEdit" value="<?= htmlspecialchars($ld['tags'] ?? '') ?>">
      </div>
      <div id="tagsSuggestionsEdit" class="mt-1"></div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Potential Value (₹)</label>
      <input type="number" class="form-control" name="potential_value" min="0" step="0.01" value="<?= htmlspecialchars((string)($ld['potential_value'] ?? '0')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Star Lead</label>
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="is_starred" value="1" <?= ((int)($ld['is_starred'] ?? 0))? 'checked':'' ?>>
        <label class="form-check-label">Mark as Star Lead</label>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Last Contact Date</label>
      <input type="date" class="form-control" name="last_contact" value="<?= htmlspecialchars((string)($ld['last_contact'] ?? '')) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Next Follow-up Date</label>
      <input type="date" class="form-control" name="next_followup" value="<?= htmlspecialchars((string)($ld['next_followup'] ?? '')) ?>">
    </div>

    <div class="col-12">
      <label class="form-label">Requirements</label>
      <textarea class="form-control" name="requirements" rows="3" placeholder="Describe the requirements..."><?= htmlspecialchars($ld['requirements'] ?? '') ?></textarea>
    </div>

    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."><?= htmlspecialchars($ld['notes'] ?? '') ?></textarea>
    </div>
  </div>
</form>
<script>
(function(){
  const sel = document.getElementById('assigned_to_edit');
  const hid = document.getElementById('assigned_to_user_id_edit');
  if (sel && hid) {
    const sync = ()=>{ const opt = sel.options[sel.selectedIndex]; hid.value = opt ? (opt.getAttribute('data-id')||'') : ''; };
    sel.addEventListener('change', sync); sync();
  }
})();

// Populate Source/City/Product suggestions from Sales Configuration
(function(){
  const currentSrc = <?= json_encode((string)($ld['source'] ?? '')) ?>;
  fetch('/?action=salesConfig&subaction=listSources').then(r=>r.json()).then(data=>{
    const sel = document.getElementById('sourceSelectEdit'); if (!sel) return;
    sel.innerHTML = '<option value="">Select Source</option>' + (Array.isArray(data)?data.map(s=>`<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join(''): '');
    if (currentSrc) sel.value = currentSrc;
  }).catch(()=>{});

  // Products
  fetch('/?action=salesConfig&subaction=listLeadProducts').then(r=>r.json()).then(data=>{
    const box = document.getElementById('productSuggestionsEdit'); const input = document.getElementById('productInputEdit');
    if (!box || !Array.isArray(data)) return; box.innerHTML = '';
    data.forEach(p=>{ const b=document.createElement('span'); b.className='badge bg-light text-dark border me-1 mb-1'; b.textContent=p.name; b.style.cursor='pointer'; b.onclick=()=>{ if(input) input.value=p.name; }; box.appendChild(b); });
  }).catch(()=>{});

  // Cities
  fetch('/?action=salesConfig&subaction=listCities').then(r=>r.json()).then(data=>{
    const box = document.getElementById('citySuggestionsEdit'); const input = document.getElementById('cityInputEdit');
    if (!box || !Array.isArray(data)) return; box.innerHTML = '';
    data.forEach(c=>{ const b=document.createElement('span'); b.className='badge bg-light text-dark border me-1 mb-1'; b.textContent=c.name; b.style.cursor='pointer'; b.onclick=()=>{ if(input) input.value=c.name; }; box.appendChild(b); });
  }).catch(()=>{});
})();

// City quick add is handled via Sales Config mini dialog: openAddCityModal()
function addProductEdit(){ const inp=document.getElementById('productInputEdit'); const v=inp.value.trim(); if(!v) return; const box=document.getElementById('productSuggestionsEdit'); const b=document.createElement('span'); b.className='badge bg-info me-1 mb-1'; b.textContent=v; b.style.cursor='pointer'; b.onclick=()=>{ inp.value=v; }; box.appendChild(b); inp.value=''; }
</script>
