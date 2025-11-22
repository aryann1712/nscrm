<div class="card">
  <div class="card-header bg-warning-subtle">Basic Info</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-12">
        <label class="form-label">Company Name <span class="text-danger">*</span></label>
        <input type="text" id="basic_company" class="form-control" placeholder="Company Name" value="NS Technology">
      </div>
      <div class="col-md-12">
        <label class="form-label">Tagline</label>
        <input type="text" id="basic_tagline" class="form-control" placeholder="Let's Change The World" value="Let's Change The World">
      </div>
      <div class="col-md-12">
        <label class="form-label">Description</label>
        <textarea id="basic_desc" class="form-control" rows="3" placeholder="Write about your business..."></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Location <span class="text-danger">*</span></label>
        <input type="text" id="basic_city" class="form-control" placeholder="City" value="Noida">
      </div>
      <div class="col-md-6">
        <label class="form-label">State</label>
        <input type="text" id="basic_state" class="form-control" placeholder="State" value="Uttar Pradesh">
      </div>
      <div class="col-md-6">
        <label class="form-label">GSTIN</label>
        <input type="text" id="basic_gstin" class="form-control" placeholder="GSTIN" value="09BTPH2530A2Z4">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="ms-auto">
          <a href="/?action=store&step=products" class="btn btn-outline-primary">Next: Products & Market</a>
          <button class="btn btn-success" id="basic_save">Save</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(async function loadBasic(){
  try{
    const r = await fetch('/?action=salesConfig&subaction=getStoreSettings&keys=' + encodeURIComponent([
      'basic_company','basic_tagline','basic_desc','basic_city','basic_state','basic_gstin'
    ].join(',')));
    const d = await r.json();
    const set=(id,key)=>{ const el=document.getElementById(id); if(el && d && d[key]) el.value = d[key]; };
    set('basic_company','basic_company');
    set('basic_tagline','basic_tagline');
    set('basic_desc','basic_desc');
    set('basic_city','basic_city');
    set('basic_state','basic_state');
    set('basic_gstin','basic_gstin');
  }catch(e){ console.warn('basic load failed', e); }
})();

document.getElementById('basic_save')?.addEventListener('click', async ()=>{
  const payload = {
    basic_company: document.getElementById('basic_company')?.value||'',
    basic_tagline: document.getElementById('basic_tagline')?.value||'',
    basic_desc: document.getElementById('basic_desc')?.value||'',
    basic_city: document.getElementById('basic_city')?.value||'',
    basic_state: document.getElementById('basic_state')?.value||'',
    basic_gstin: document.getElementById('basic_gstin')?.value||''
  };
  try{
    const r = await fetch('/?action=salesConfig&subaction=saveStoreSettings', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const d = await r.json();
    if(!r.ok || d.error) throw new Error(d.error||'Save failed');
    toast('Basic details saved','success');
  }catch(err){ toast('Basic save error: ' + (err?.message||'Failed'),'danger'); }
});
</script>
