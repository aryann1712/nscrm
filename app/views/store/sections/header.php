<div class="card">
  <div class="card-header bg-warning-subtle">Header</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">Company Banner</h6>
      <small class="text-muted">Set up banner image, video about your products/services.</small>
    </div>

    <!-- Banner Upload -->
    <div class="row g-3 align-items-start">
      <div class="col-12">
        <div class="border rounded p-2" style="min-height:140px; background:#f8f9fa;">
          <div id="storeHeaderPreview" class="text-center text-muted" style="min-height:100px;">
            <div class="py-4">No banner uploaded</div>
          </div>
        </div>
        <div class="d-flex gap-2 mt-2">
          <input type="file" id="storeHeaderFile" accept="image/png,image/jpeg,image/webp" class="form-control" style="max-width:380px;" />
          <button class="btn btn-primary" id="btnUploadStoreHeader"><i class="bi bi-upload"></i> Upload</button>
          <button class="btn btn-outline-danger" id="btnRemoveStoreHeader"><i class="bi bi-x"></i> Remove</button>
        </div>
        <div class="small text-muted mt-1">PNG/JPG/WEBP up to 8MB. Recommended: 1200x300 or similar wide ratio.</div>
      </div>
    </div>

    <hr>

    <!-- Video -->
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Video Title</label>
        <input type="text" id="hdr_video_title" class="form-control" placeholder="Demo Video">
      </div>
      <div class="col-md-6">
        <label class="form-label">YouTube Link</label>
        <input type="url" id="hdr_video_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
        <div class="form-text">If your URL is https://www.youtube.com/watch?v=ABC123, please enter ABC123 (coming soon).</div>
      </div>
    </div>

    <hr>

    <!-- Header Links -->
    <div class="mb-2"><strong>Header Links</strong></div>
    <div class="row g-2 align-items-center">
      <div class="col-md-3"><input id="hdr_link1_title" class="form-control" placeholder="Link 1 Title"></div>
      <div class="col-md-9"><input id="hdr_link1_url" class="form-control" placeholder="Link 1 URL"></div>
      <div class="col-md-3"><input id="hdr_link2_title" class="form-control" placeholder="Link 2 Title"></div>
      <div class="col-md-9"><input id="hdr_link2_url" class="form-control" placeholder="Link 2 URL"></div>
      <div class="col-md-3"><input id="hdr_link3_title" class="form-control" placeholder="Link 3 Title"></div>
      <div class="col-md-9"><input id="hdr_link3_url" class="form-control" placeholder="Link 3 URL"></div>
      <div class="col-md-3"><input id="hdr_link4_title" class="form-control" placeholder="Link 4 Title"></div>
      <div class="col-md-9"><input id="hdr_link4_url" class="form-control" placeholder="Link 4 URL"></div>
      <div class="col-md-3"><input id="hdr_link5_title" class="form-control" placeholder="Link 5 Title"></div>
      <div class="col-md-9"><input id="hdr_link5_url" class="form-control" placeholder="Link 5 URL"></div>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <a href="/?action=store&step=purchases" class="btn btn-outline-secondary me-2">Back</a>
      <button class="btn btn-success" id="hdr_save">Save</button>
    </div>
  </div>
</div>

<script>
(async function initStoreHeader(){
  try {
    const resp = await fetch('/?action=salesConfig&subaction=getStoreHeader');
    const data = await resp.json();
    const prev = document.getElementById('storeHeaderPreview');
    if (data && data.exists && data.url) {
      prev.innerHTML = `<img src="${data.url}" alt="Store Header" class="img-fluid rounded border"/>`;
    } else {
      prev.innerHTML = '<div class="py-4 text-muted">No banner uploaded</div>';
    }
  } catch(e) { console.warn('Header fetch failed', e); }
})();

// Upload
const upBtn = document.getElementById('btnUploadStoreHeader');
upBtn?.addEventListener('click', async ()=>{
  const f = document.getElementById('storeHeaderFile');
  if (!f || !f.files || !f.files[0]) { alert('Please select an image'); return; }
  const fd = new FormData();
  fd.append('image', f.files[0]);
  try {
    upBtn.disabled = true; upBtn.textContent = 'Uploading...';
    const resp = await fetch('/?action=salesConfig&subaction=uploadStoreHeader', { method: 'POST', body: fd });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Upload failed');
    document.getElementById('storeHeaderPreview').innerHTML = `<img src="${data.url}" alt="Store Header" class="img-fluid rounded border"/>`;
  } catch(err){
    alert('Upload error: ' + (err?.message || 'Failed'));
  } finally { upBtn.disabled = false; upBtn.textContent = 'Upload'; }
});

// Remove
const rmBtn = document.getElementById('btnRemoveStoreHeader');
rmBtn?.addEventListener('click', async ()=>{
  if (!confirm('Remove current banner?')) return;
  try {
    rmBtn.disabled = true; rmBtn.textContent = 'Removing...';
    const resp = await fetch('/?action=salesConfig&subaction=removeStoreHeader', { method: 'POST' });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed');
    document.getElementById('storeHeaderPreview').innerHTML = '<div class="py-4 text-muted">No banner uploaded</div>';
  } catch(err){
    alert('Remove error: ' + (err?.message || 'Failed'));
  } finally { rmBtn.disabled = false; rmBtn.textContent = 'Remove'; }
});

// Load header fields
(async function loadHeaderFields(){
  try{
    const keys=['hdr_video_title','hdr_video_link','hdr_link1_title','hdr_link1_url','hdr_link2_title','hdr_link2_url','hdr_link3_title','hdr_link3_url','hdr_link4_title','hdr_link4_url','hdr_link5_title','hdr_link5_url'];
    const r=await fetch('/?action=salesConfig&subaction=getStoreSettings&keys='+encodeURIComponent(keys.join(',')));
    const d=await r.json();
    keys.forEach(k=>{ const el=document.getElementById(k); if(el && d && d[k]) el.value=d[k]; });
  }catch(e){ console.warn('header fields load failed', e); }
})();

// Save header fields
document.getElementById('hdr_save')?.addEventListener('click', async ()=>{
  const payload={};
  ['hdr_video_title','hdr_video_link','hdr_link1_title','hdr_link1_url','hdr_link2_title','hdr_link2_url','hdr_link3_title','hdr_link3_url','hdr_link4_title','hdr_link4_url','hdr_link5_title','hdr_link5_url'].forEach(k=>payload[k]=document.getElementById(k)?.value||'');
  try{
    const r=await fetch('/?action=salesConfig&subaction=saveStoreSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const d=await r.json(); if(!r.ok||d.error) throw new Error(d.error||'Save failed');
    alert('Header saved');
  }catch(err){ alert('Save error: '+(err?.message||'Failed')); }
});
</script>
