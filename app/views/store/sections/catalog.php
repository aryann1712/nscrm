<div class="card">
  <div class="card-header bg-warning-subtle">Catalog</div>
  <div class="card-body">
    <div class="mb-2 text-muted">Configure images and details of your products/services.</div>

    <div class="mb-4">
      <h6 class="mb-2">Products & Services</h6>
      <div class="d-flex flex-wrap gap-3">
        <button class="btn btn-outline-secondary p-4" id="addProdBtn" style="border:2px dashed #ced4da; min-width:180px;">
          <div class="fs-2">+</div>
          <div> Add Item</div>
        </button>
        <div id="prodList" class="d-flex flex-wrap gap-3"></div>
      </div>
    </div>

    <div class="mb-4">
      <h6 class="mb-2">Information</h6>
      <div class="d-flex flex-wrap gap-3">
        <button class="btn btn-outline-secondary p-4" id="addInfoBtn" style="border:2px dashed #ced4da; min-width:180px;">
          <div class="fs-2">+</div>
          <div> Add Item</div>
        </button>
        <div id="infoList" class="d-flex flex-wrap gap-3"></div>
      </div>
    </div>

    <div class="mb-2">
      <h6 class="mb-2">Used Machinery</h6>
      <div class="d-flex flex-wrap gap-3">
        <button class="btn btn-outline-secondary p-4" id="addMachBtn" style="border:2px dashed #ced4da; min-width:180px;">
          <div class="fs-2">+</div>
          <div> Add Item</div>
        </button>
        <div id="machList" class="d-flex flex-wrap gap-3"></div>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <a href="/?action=store&step=offer" class="btn btn-outline-secondary me-2">Back</a>
      <button class="btn btn-success">Save</button>
    </div>
  </div>
</div>

<!-- Simple Add Item Modal (UI only) -->
<div class="modal fade" id="catalogItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Catalog Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Title</label>
          <input type="text" id="ciTitle" class="form-control" placeholder="Item title">
        </div>
        <div class="mb-2">
          <label class="form-label">Short Description</label>
          <textarea id="ciDesc" class="form-control" rows="2" placeholder="Short description"></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label">Image (optional)</label>
          <input type="file" id="ciImage" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="ciSaveBtn">Add</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  let targetList = null;
  const modalEl = document.getElementById('catalogItemModal');
  const bsModal = new bootstrap.Modal(modalEl);
  const titleEl = document.getElementById('ciTitle');
  const descEl = document.getElementById('ciDesc');
  const imgEl = document.getElementById('ciImage');

  function openModal(list){
    targetList = list;
    titleEl.value = '';
    descEl.value = '';
    imgEl.value = '';
    bsModal.show();
  }

  document.getElementById('addProdBtn')?.addEventListener('click', ()=>openModal(document.getElementById('prodList')));
  document.getElementById('addInfoBtn')?.addEventListener('click', ()=>openModal(document.getElementById('infoList')));
  document.getElementById('addMachBtn')?.addEventListener('click', ()=>openModal(document.getElementById('machList')));

  document.getElementById('ciSaveBtn')?.addEventListener('click', ()=>{
    if (!targetList) return;
    const title = titleEl.value.trim() || 'Untitled';
    const desc = descEl.value.trim();
    const card = document.createElement('div');
    card.className = 'card';
    card.style.width = '220px';
    card.innerHTML = `
      <div class="card-body">
        <div class="small text-muted">Catalog Item</div>
        <div class="fw-semibold">${title}</div>
        <div class="text-muted small">${desc}</div>
      </div>`;
    targetList.appendChild(card);
    bsModal.hide();
  });
})();
</script>
