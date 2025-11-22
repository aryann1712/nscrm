<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="/?action=inventory">Inventory</a></li>
      <li class="breadcrumb-item active">Categories & Sub-Categories</li>
    </ol>
  </nav>
  <div class="d-flex gap-2">
    <button class="btn btn-primary btn-sm" id="btnAddCategory"><i class="bi bi-plus-lg"></i> Add Category</button>
    <a href="/?action=inventory" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="card">
  <div class="card-header">Manage Categories</div>
  <div class="card-body">
    <div id="catList" class="list-group"></div>
    <div id="emptyMsg" class="text-muted" style="display:none;">No categories yet. Click "Add Category" to create one.</div>
  </div>
</div>

<template id="tplCategory">
  <div class="list-group-item mb-2">
    <div class="d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold cat-name"></span>
        <button class="btn btn-sm btn-outline-secondary btnRenameCat" title="Rename"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger btnDeleteCat" title="Delete"><i class="bi bi-trash"></i></button>
      </div>
      <div>
        <button class="btn btn-sm btn-outline-primary btnAddSub"><i class="bi bi-plus"></i> Sub-Category</button>
      </div>
    </div>
    <div class="mt-2 ms-4">
      <ul class="list-unstyled mb-0 subs"></ul>
    </div>
  </div>
</template>

<template id="tplSub">
  <li class="d-flex align-items-center gap-2 py-1">
    <span class="sub-name"></span>
    <button class="btn btn-sm btn-outline-secondary btnRenameSub" title="Rename"><i class="bi bi-pencil"></i></button>
    <button class="btn btn-sm btn-outline-danger btnDeleteSub" title="Delete"><i class="bi bi-trash"></i></button>
  </li>
</template>

<style>
.list-group-item .btn { padding: 0.15rem 0.4rem; }
</style>

<script>
async function apiGet(url){ const r = await fetch(url); if(!r.ok) throw new Error('Failed'); return r.json(); }
async function apiPost(url, data){ const fd = new FormData(); Object.entries(data).forEach(([k,v])=>fd.append(k,v)); const r = await fetch(url,{method:'POST', body:fd}); if(!r.ok) throw new Error('Failed'); return r.json(); }

async function loadAll(){
  try {
    const list = await apiGet('/?action=inventory&subaction=listCategoriesWithSubs');
    const box = document.getElementById('catList'); box.innerHTML = '';
    document.getElementById('emptyMsg').style.display = (list&&list.length)?'none':'';
    const tplC = document.getElementById('tplCategory'); const tplS = document.getElementById('tplSub');
    (list||[]).forEach(cat => {
      const node = tplC.content.cloneNode(true);
      const root = node.querySelector('.list-group-item');
      root.dataset.id = cat.id; root.dataset.name = cat.name;
      root.querySelector('.cat-name').textContent = cat.name;
      // subs
      const ul = root.querySelector('.subs');
      (cat.subs||[]).forEach(s => {
        const sn = tplS.content.cloneNode(true);
        const el = sn.querySelector('li');
        el.dataset.id = s.id; el.dataset.name = s.name; el.dataset.categoryId = cat.id;
        el.querySelector('.sub-name').textContent = s.name;
        ul.appendChild(sn);
      });
      box.appendChild(node);
    });
  } catch(e){
    if (typeof toast !== 'undefined') { try { toast('Failed to load categories','danger'); } catch(_){} }
    console.error('Failed to load categories', e);
  }
}

// Add category
document.getElementById('btnAddCategory').addEventListener('click', async ()=>{
  const name = prompt('Enter category name:'); if(!name||!name.trim()) return;
  try { await apiPost('/?action=inventory&subaction=createCategory', { name: name.trim() }); await loadAll(); }
  catch(e){ if (typeof toast !== 'undefined') { try { toast('Unable to create category','danger'); } catch(_){} } console.error(e); }
});

// Delegated actions
document.getElementById('catList').addEventListener('click', async (e)=>{
  const btnRenameCat = e.target.closest('.btnRenameCat');
  const btnDeleteCat = e.target.closest('.btnDeleteCat');
  const btnAddSub = e.target.closest('.btnAddSub');
  const btnRenameSub = e.target.closest('.btnRenameSub');
  const btnDeleteSub = e.target.closest('.btnDeleteSub');

  // helpers
  const findCatRoot = (el)=> el.closest('.list-group-item');
  const findSubRoot = (el)=> el.closest('li');

  if (btnRenameCat){
    const root = findCatRoot(btnRenameCat); const id = root.dataset.id; const current = root.dataset.name;
    const name = prompt('Rename category:', current||''); if(!name||!name.trim()) return;
    try { await apiPost('/?action=inventory&subaction=updateCategory', { id, name: name.trim() }); await loadAll(); }
    catch(e){ if (typeof toast !== 'undefined') { try { toast('Rename failed','danger'); } catch(_){} } console.error(e); }
    return;
  }
  if (btnDeleteCat){
    const root = findCatRoot(btnDeleteCat); const id = root.dataset.id; const nm=root.dataset.name;
    if (!confirm(`Delete category "${nm}" and its sub-categories?`)) return;
    try { await apiPost('/?action=inventory&subaction=deleteCategoryApi', { id }); await loadAll(); }
    catch(e){ if (typeof toast !== 'undefined') { try { toast('Delete failed','danger'); } catch(_){} } console.error(e); }
    return;
  }
  if (btnAddSub){
    const root = findCatRoot(btnAddSub); const category_id = root.dataset.id; const cname = root.dataset.name;
    const name = prompt(`Add sub-category under "${cname}":`); if(!name||!name.trim()) return;
    try { await apiPost('/?action=inventory&subaction=createSubCategory', { category_id, name: name.trim() }); await loadAll(); }
    catch(e){ if (typeof toast !== 'undefined') { try { toast('Add sub-category failed','danger'); } catch(_){} } console.error(e); }
    return;
  }
  if (btnRenameSub){
    const el = findSubRoot(btnRenameSub); const id = el.dataset.id; const current = el.dataset.name;
    const name = prompt('Rename sub-category:', current||''); if(!name||!name.trim()) return;
    try { await apiPost('/?action=inventory&subaction=updateSubCategory', { id, name: name.trim() }); await loadAll(); }
    catch(e){ if (typeof toast !== 'undefined') { try { toast('Rename failed','danger'); } catch(_){} } console.error(e); }
    return;
  }
  if (btnDeleteSub){
    const el = findSubRoot(btnDeleteSub); const id = el.dataset.id; const nm = el.dataset.name;
    if (!confirm(`Delete sub-category "${nm}"?`)) return;
    try { await apiPost('/?action=inventory&subaction=deleteSubCategoryApi', { id }); await loadAll(); }
    catch(e){ if (typeof toast !== 'undefined') { try { toast('Delete failed','danger'); } catch(_){} } console.error(e); }
    return;
  }
});

// initial load
document.addEventListener('DOMContentLoaded', loadAll);
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
