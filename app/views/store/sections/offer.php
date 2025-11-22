<div class="card">
  <div class="card-header bg-warning-subtle">Offer</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">Special Offer for Website Visitors</h6>
      <small class="text-muted">Show an exclusive offer to website visitors.</small>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Title</label>
        <input type="text" id="offer_title" class="form-control" placeholder="Offer Title">
      </div>
      <div class="col-12">
        <label class="form-label">Link</label>
        <input type="url" id="offer_link" class="form-control" placeholder="Offer Link (Optional)">
      </div>
      <div class="col-12">
        <label class="form-label">Details</label>
        <textarea id="offer_desc" class="form-control" rows="4" placeholder="Offer Description"></textarea>
      </div>
      <div class="col-12 d-flex justify-content-end">
        <a href="/?action=store&step=header" class="btn btn-outline-secondary me-2">Back</a>
        <button class="btn btn-success" id="offer_save">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
(async function loadOffer(){
  try{
    const keys=['offer_title','offer_link','offer_desc'];
    const r=await fetch('/?action=salesConfig&subaction=getStoreSettings&keys='+encodeURIComponent(keys.join(',')));
    const d=await r.json();
    keys.forEach(k=>{ const el=document.getElementById(k); if(el && d && d[k]) el.value=d[k]; });
  }catch(e){ console.warn('offer load failed', e); }
})();

document.getElementById('offer_save')?.addEventListener('click', async ()=>{
  const payload={
    offer_title: document.getElementById('offer_title')?.value||'',
    offer_link: document.getElementById('offer_link')?.value||'',
    offer_desc: document.getElementById('offer_desc')?.value||''
  };
  try{
    const r=await fetch('/?action=salesConfig&subaction=saveStoreSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const d=await r.json(); if(!r.ok||d.error) throw new Error(d.error||'Save failed');
    alert('Offer saved');
  }catch(err){ alert('Save error: '+(err?.message||'Failed')); }
});
</script>
