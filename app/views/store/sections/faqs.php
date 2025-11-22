<div class="card">
  <div class="card-header bg-warning-subtle">FAQs</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">Frequently Asked Questions</h6>
      <small class="text-muted">Enter up to five questions and their answers.</small>
    </div>

    <div class="vstack gap-3">
      <!-- Repeat Q/A rows -->
      <?php for ($i=1; $i<=5; $i++): ?>
        <div class="row g-3 align-items-start">
          <div class="col-md-4">
            <label class="form-label">Question <?= $i ?></label>
            <input type="text" id="faq_q<?= $i ?>" class="form-control" placeholder="Question <?= $i ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Answer to Question <?= $i ?></label>
            <textarea id="faq_a<?= $i ?>" class="form-control" rows="2" placeholder="Answer to Question <?= $i ?>"></textarea>
          </div>
        </div>
      <?php endfor; ?>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <a href="/?action=store&step=team" class="btn btn-outline-secondary me-2">Back</a>
      <button class="btn btn-success" id="faq_save">Save</button>
    </div>
  </div>
</div>

<script>
(async function loadFaqs(){
  try{
    const keys=[]; for(let i=1;i<=5;i++){ keys.push('faq_q'+i,'faq_a'+i); }
    const r=await fetch('/?action=salesConfig&subaction=getStoreSettings&keys='+encodeURIComponent(keys.join(',')));
    const d=await r.json();
    keys.forEach(k=>{ const el=document.getElementById(k); if(el && d && d[k]) el.value=d[k]; });
  }catch(e){ console.warn('faqs load failed', e); }
})();

document.getElementById('faq_save')?.addEventListener('click', async ()=>{
  const payload={}; for(let i=1;i<=5;i++){ payload['faq_q'+i]=document.getElementById('faq_q'+i)?.value||''; payload['faq_a'+i]=document.getElementById('faq_a'+i)?.value||''; }
  try{
    const r=await fetch('/?action=salesConfig&subaction=saveStoreSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const d=await r.json(); if(!r.ok||d.error) throw new Error(d.error||'Save failed');
    alert('FAQs saved');
  }catch(err){ alert('Save error: '+(err?.message||'Failed')); }
});
</script>
