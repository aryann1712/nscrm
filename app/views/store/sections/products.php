<div class="card">
  <div class="card-header bg-warning-subtle">Products & Market</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">What do you sell?</h6>
      <small class="text-muted">Select segments of your business and where you sell.</small>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Business Area <span class="text-danger">*</span></label>
        <select class="form-select" id="prod_area">
          <option>Electronics » Robotics & Automation</option>
          <option>Electronics » Components</option>
          <option>Software » SaaS</option>
          <option>Industrial » Machinery</option>
          <option>Custom</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Describe your products / services</label>
        <textarea class="form-control" id="prod_desc" rows="3" placeholder="Description of your products / services"></textarea>
      </div>

      <div class="col-12">
        <h6 class="mt-2">Who do you sell to?</h6>
        <div class="vstack gap-2">
          <select class="form-select" id="prod_seg1">
            <option selected>1. Select Industry / Segment</option>
            <option>Automotive</option>
            <option>Education</option>
            <option>Manufacturing</option>
            <option>Healthcare</option>
          </select>
          <select class="form-select" id="prod_seg2">
            <option selected>2. Select Industry / Segment</option>
            <option>Automotive</option>
            <option>Education</option>
            <option>Manufacturing</option>
            <option>Healthcare</option>
          </select>
          <select class="form-select" id="prod_seg3">
            <option selected>3. Select Industry / Segment</option>
            <option>Automotive</option>
            <option>Education</option>
            <option>Manufacturing</option>
            <option>Healthcare</option>
          </select>
          <select class="form-select" id="prod_seg4">
            <option selected>4. Select Industry / Segment</option>
            <option>Automotive</option>
            <option>Education</option>
            <option>Manufacturing</option>
            <option>Healthcare</option>
          </select>
          <select class="form-select" id="prod_seg5">
            <option selected>5. Select Industry / Segment</option>
            <option>Automotive</option>
            <option>Education</option>
            <option>Manufacturing</option>
            <option>Healthcare</option>
          </select>
        </div>
      </div>

      <div class="col-12 d-flex justify-content-end">
        <a href="/?action=store&step=basic" class="btn btn-outline-secondary me-2">Back</a>
        <button class="btn btn-success" id="prod_save">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
(async function loadProducts(){
  try{
    const keys=['prod_area','prod_desc','prod_seg1','prod_seg2','prod_seg3','prod_seg4','prod_seg5'];
    const r=await fetch('/?action=salesConfig&subaction=getStoreSettings&keys='+encodeURIComponent(keys.join(',')));
    const d=await r.json();
    keys.forEach(k=>{ const el=document.getElementById(k); if(el && d && d[k]) el.value=d[k]; });
  }catch(e){ console.warn('products load failed', e); }
})();

document.getElementById('prod_save')?.addEventListener('click', async ()=>{
  const payload={
    prod_area: document.getElementById('prod_area')?.value||'',
    prod_desc: document.getElementById('prod_desc')?.value||'',
    prod_seg1: document.getElementById('prod_seg1')?.value||'',
    prod_seg2: document.getElementById('prod_seg2')?.value||'',
    prod_seg3: document.getElementById('prod_seg3')?.value||'',
    prod_seg4: document.getElementById('prod_seg4')?.value||'',
    prod_seg5: document.getElementById('prod_seg5')?.value||''
  };
  try{
    const r=await fetch('/?action=salesConfig&subaction=saveStoreSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const d=await r.json(); if(!r.ok||d.error) throw new Error(d.error||'Save failed');
    toast('Products & Market saved','success');
  }catch(err){ toast('Products & Market save error: '+(err?.message||'Failed'),'danger'); }
});
</script>
