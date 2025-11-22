<?php
if (!isset($stores)) { $stores = []; }
if (!isset($users)) { $users = []; }
ob_start();
?>
<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Stores</h4>
    <button class="btn btn-sm btn-primary" onclick="openStoreModal()">Add</button>
  </div>

  <?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success py-1"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger py-1"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
  <?php endif; ?>

  <div class="list-group">
    <?php foreach ($stores as $s): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-bold text-uppercase small"><?php echo htmlspecialchars($s['name']); ?></div>
            <div class="text-muted small">
              <?php if (!empty($s['users'])): ?>
                <?php foreach ($s['users'] as $u): ?>
                  <span class="me-3">• <?php echo htmlspecialchars($u['name']); ?>
                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" onclick="openRightsModal(<?php echo (int)$u['id']; ?>, '<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>')">Set Rights</button>
                  </span>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="text-muted">No users</span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-secondary" onclick="openStoreModal('<?php echo (int)$s['id']; ?>','<?php echo htmlspecialchars($s['name'], ENT_QUOTES); ?>')" title="Edit"><i class="bi bi-pencil"></i></button>
            <form method="post" action="/?action=inventory&subaction=delete_store" class="d-inline" onsubmit="return confirm('Delete this store?');">
              <input type="hidden" name="store_id" value="<?php echo (int)$s['id']; ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="storeModalTitle">Add Store</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/?action=inventory&subaction=save_store">
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
let storeAssignments = {};
<?php foreach ($stores as $s): ?>
  storeAssignments[<?php echo (int)$s['id']; ?>] = (<?php echo json_encode(array_column($s['users'], 'id')); ?>);
<?php endforeach; ?>

function openStoreModal(id = '', name = '') {
  document.getElementById('store_id').value = id;
  document.getElementById('store_name').value = name;
  // clear checks
  document.querySelectorAll('#storeModal input[type=checkbox]').forEach(el => el.checked = false);
  if (id && storeAssignments[id]) {
    storeAssignments[id].forEach(uid => {
      const el = document.getElementById('user_' + uid);
      if (el) el.checked = true;
    });
  }
  document.getElementById('storeModalTitle').textContent = id ? 'Edit Store' : 'Add Store';
  const m = new bootstrap.Modal(document.getElementById('storeModal'));
  m.show();
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>

<!-- Rights Modal (inline) -->
<div class="modal fade" id="rightsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Rights for <span id="rights_user_name"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/?action=settings&subaction=saveRights">
      <div class="modal-body">
        <input type="hidden" name="user_id" id="rights_user_id">
        <div class="row">
          <div class="col-md-6">
            <h6>CRM Tools</h6>
            <div class="mb-2">
              <label class="form-label">Leads & Prospects</label>
              <select class="form-select form-select-sm" name="rights[crm][leads]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Customers</label>
              <select class="form-select form-select-sm" name="rights[crm][customers]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Quotations</label>
              <select class="form-select form-select-sm" name="rights[crm][quotations]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Invoices</label>
              <select class="form-select form-select-sm" name="rights[crm][invoices]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <h6>ERP Tools</h6>
            <div class="mb-2">
              <label class="form-label">Inventory</label>
              <select class="form-select form-select-sm" name="rights[erp][inventory]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Orders</label>
              <select class="form-select form-select-sm" name="rights[erp][orders]">
                <option value="none">No Access</option>
                <option value="view">View</option>
                <option value="edit">Edit</option>
                <option value="full">Full</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Default Page</label>
              <select class="form-select form-select-sm" name="rights[b2b][default_page]">
                <option value="home">Home</option>
                <option value="leads">Leads</option>
                <option value="customers">Customers</option>
                <option value="quotes">Quotes</option>
                <option value="invoices">Invoices</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
function openRightsModal(userId, userName) {
  document.getElementById('rights_user_id').value = userId;
  document.getElementById('rights_user_name').textContent = userName;
  const m = new bootstrap.Modal(document.getElementById('rightsModal'));
  m.show();
}
</script>
