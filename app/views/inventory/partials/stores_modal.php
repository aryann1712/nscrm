<?php
if (!isset($stores)) { $stores = []; }
if (!isset($users)) { $users = []; }
?>
<div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="storeModalTitle">Add Store</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="storeForm" method="post" action="/?action=inventory&subaction=save_store">
        <div class="modal-body">
          <input type="hidden" name="store_id" id="store_id">
          <div class="mb-3">
            <label class="form-label">Store</label>
            <input type="text" class="form-control" required name="name" id="store_name">
          </div>
          <div class="mb-2">
            <?php foreach ($users as $u): ?>
              <div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?php echo (int)$u['id']; ?>" id="user_<?php echo (int)$u['id']; ?>">
                <label class="form-check-label" for="user_<?php echo (int)$u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
window.storeAssignments = window.storeAssignments || {};
function openStoreModal(id = '', name = '', assignedUserIds = []) {
  document.getElementById('store_id').value = id || '';
  document.getElementById('store_name').value = name || '';
  document.querySelectorAll('#storeModal input[type=checkbox]').forEach(el => el.checked = false);
  (assignedUserIds || []).forEach(uid => {
    const el = document.getElementById('user_' + uid);
    if (el) el.checked = true;
  });
  document.getElementById('storeModalTitle').textContent = id ? 'Edit Store' : 'Add Store';
  const storeEl = document.getElementById('storeModal');
  // If another modal (like the Manage dialog) is already open, do not hide it.
  const anotherOpen = document.querySelector('.modal.show:not(#storeModal)');
  const opts = anotherOpen ? { backdrop: false } : {};
  const m = new bootstrap.Modal(storeEl, opts);
  // Raise z-index a bit so it appears above the manager modal when stacked
  if (anotherOpen) { storeEl.style.zIndex = 1065; }
  m.show();
}
// AJAX submit so we can use this modal from any page
const storeForm = document.getElementById('storeForm');
if (storeForm) {
  storeForm.addEventListener('submit', async function(ev){
    ev.preventDefault();
    const fd = new FormData(storeForm);
    try {
      const res = await fetch(storeForm.action, { method: 'POST', body: fd, credentials: 'same-origin' });
      // Always redirect back to stores after saving on server; here we swallow redirect and just emit event
      document.dispatchEvent(new CustomEvent('inventory:store:saved', { detail: { name: fd.get('name') } }));
      bootstrap.Modal.getInstance(document.getElementById('storeModal')).hide();
    } catch(e) { alert('Failed to save store'); }
  });
}
</script>
