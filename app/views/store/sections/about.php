<div class="card">
  <div class="card-header bg-warning-subtle">About Company</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">Company Image</h6>
      <small class="text-muted">Set up team image and detailed background of your company.</small>
    </div>

    <div class="row g-3 align-items-start">
      <div class="col-12">
        <div class="border rounded p-2" style="min-height:180px; background:#f8f9fa;">
          <div id="aboutImagePreview" class="text-center text-muted" style="min-height:140px;">
            <div class="py-5">No image uploaded</div>
          </div>
        </div>
        <div class="d-flex gap-2 mt-2">
          <input type="file" id="aboutImageFile" accept="image/png,image/jpeg,image/webp" class="form-control" style="max-width:380px;" />
          <button class="btn btn-primary" id="btnUploadAboutImage"><i class="bi bi-upload"></i> Upload</button>
          <button class="btn btn-outline-danger" id="btnRemoveAboutImage"><i class="bi bi-x"></i> Remove</button>
        </div>
        <div class="small text-muted mt-1">PNG/JPG/WEBP up to 8MB. Recommended landscape image.</div>
      </div>
    </div>

    <hr>

    <div class="mb-2"><strong>Description</strong></div>
    <textarea id="about_desc" class="form-control" rows="6" placeholder="Company Description (About your Company, Origin, Mission, Vision, and more)"></textarea>

    <div class="d-flex justify-content-end mt-3">
      <a href="/?action=store&step=catalog" class="btn btn-outline-secondary me-2">Back</a>
      <button class="btn btn-success" id="about_save">Save</button>
    </div>
  </div>
</div>

<script>
(async function initAboutImage(){
  try {
    const resp = await fetch('/?action=salesConfig&subaction=getAboutImage');
    const data = await resp.json();
    const prev = document.getElementById('aboutImagePreview');
    if (data && data.exists && data.url) {
      prev.innerHTML = `<img src="${data.url}" alt="About Image" class="img-fluid rounded border"/>`;
    } else {
      prev.innerHTML = '<div class="py-5 text-muted">No image uploaded</div>';
    }
  } catch(e) { console.warn('About image fetch failed', e); }
})();

// Upload
const upBtnA = document.getElementById('btnUploadAboutImage');
upBtnA?.addEventListener('click', async ()=>{
  const f = document.getElementById('aboutImageFile');
  if (!f || !f.files || !f.files[0]) { alert('Please select an image'); return; }
  const fd = new FormData();
  fd.append('image', f.files[0]);
  try {
    upBtnA.disabled = true; upBtnA.textContent = 'Uploading...';
    const resp = await fetch('/?action=salesConfig&subaction=uploadAboutImage', { method: 'POST', body: fd });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Upload failed');
    document.getElementById('aboutImagePreview').innerHTML = `<img src="${data.url}" alt="About Image" class="img-fluid rounded border"/>`;
  } catch(err){
    alert('Upload error: ' + (err?.message || 'Failed'));
  } finally { upBtnA.disabled = false; upBtnA.textContent = 'Upload'; }
});

// Remove
const rmBtnA = document.getElementById('btnRemoveAboutImage');
rmBtnA?.addEventListener('click', async ()=>{
  if (!confirm('Remove current image?')) return;
  try {
    rmBtnA.disabled = true; rmBtnA.textContent = 'Removing...';
    const resp = await fetch('/?action=salesConfig&subaction=removeAboutImage', { method: 'POST' });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed');
    document.getElementById('aboutImagePreview').innerHTML = '<div class="py-5 text-muted">No image uploaded</div>';
  } catch(err){
    alert('Remove error: ' + (err?.message || 'Failed'));
  } finally { rmBtnA.disabled = false; rmBtnA.textContent = 'Remove'; }
});

// Load/save about description
(async function loadAbout(){
  try{
    const r=await fetch('/?action=salesConfig&subaction=getStoreSettings&keys=' + encodeURIComponent('about_desc'));
    const d=await r.json();
    if (d && d.about_desc) document.getElementById('about_desc').value = d.about_desc;
  }catch(e){ console.warn('about load failed', e); }
})();

document.getElementById('about_save')?.addEventListener('click', async ()=>{
  const payload={ about_desc: document.getElementById('about_desc')?.value||'' };
  try{
    const r=await fetch('/?action=salesConfig&subaction=saveStoreSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const d=await r.json(); if(!r.ok||d.error) throw new Error(d.error||'Save failed');
    alert('About saved');
  }catch(err){ alert('Save error: '+(err?.message||'Failed')); }
});
</script>
