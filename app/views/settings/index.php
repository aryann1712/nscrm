<?php ob_start(); ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['warning_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['warning_message']); ?>
            <?php endif; ?>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Company Employees Panel -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Company Employees (<?= $totalUsers ?>/<?= $maxUsers ?>)</h3>
                                <div>
                                    <a href="/?action=settings&subaction=create" class="btn btn-warning btn-sm">
                                        <i class="bi bi-plus"></i> Add Employee
                                    </a>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleUsersPanel()">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="usersPanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $currentIsOwner = (int)($_SESSION['user']['is_owner'] ?? 0) === 1; ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr <?= ((int)($user['is_owner'] ?? 0) === 1) ? 'class="table-warning"' : '' ?>>
                                            <td>
                                                <?= htmlspecialchars($user['name']) ?>
                                                <?php if ((int)($user['is_owner'] ?? 0) === 1): ?>
                                                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-key-fill me-1"></i>Admin</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['phone']) ?></td>
                                            <td>
                                                <?php if (isset($user['email_verified']) && $user['email_verified']): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                                        <span class="badge bg-warning">Pending</span>
                                                        <button class="btn btn-sm btn-outline-warning" onclick="resendVerification('<?= htmlspecialchars($user['email']) ?>')">
                                                            <i class="bi bi-arrow-clockwise"></i> Resend
                                                        </button>
                                                        <form class="d-flex align-items-center gap-1" method="get" action="/?action=settings&subaction=verifyEmail" onsubmit="return this.pin.value.trim().length===6;">
                                                            <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                                                            <input type="text" name="pin" inputmode="numeric" maxlength="6" class="form-control form-control-sm" placeholder="OTP" style="width:90px">
                                                            <button type="submit" class="btn btn-sm btn-primary">Verify</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-warning btn-sm"
                                                        onclick="openUserModal(<?= (int)$user['id'] ?>,'<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>','<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>','<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES) ?>',<?= (int)($user['is_owner'] ?? 0) ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($currentIsOwner && (int)($user['is_owner'] ?? 0) !== 1): ?>
                                                <a href="/?action=settings&subaction=delete&id=<?= (int)$user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- System Changes Panel -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">System Changes</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <button class="btn btn-warning btn-lg w-100" onclick="resetData()">
                                        Reset Data
                                    </button>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button class="btn btn-warning btn-lg w-100" onclick="changeAdmin()">
                                        Change Admin
                                    </button>
                                </div>
                                <?php if ($currentIsOwner): ?>
                                <div class="col-md-4 mb-2">
                                    <button class="btn btn-warning btn-lg w-100" onclick="changeCompany()">
                                        Change Company
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Left Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-secondary btn-lg w-100 mb-2" onclick="showTrainingMaterials()">
                                <i class="bi bi-question-circle me-2"></i>
                                Training Materials
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-secondary btn-lg w-100 mb-2" onclick="upgradeUsersQuota()">
                                <i class="bi bi-graph-up me-2"></i>
                                Upgrade Users Quota
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Other Settings Panel -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Other Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action" onclick="showBiziverseInvoices()">
                                    Biziverse Invoices
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" onclick="showAccountAddress()">
                                    Account Address
                                </a>
                                <a href="/?action=settings&subaction=salesConfiguration" class="list-group-item list-group-item-action">
                                    Sales Configuration
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Plan Panel -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Subscription Plan</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">Trial Plan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const NS_CURRENT_COMPANY_NAME = <?php echo json_encode($_SESSION['user']['company_name'] ?? ($_SESSION['user']['name'] ?? '')); ?>;
function toggleUsersPanel() {
    const panel = document.getElementById('usersPanel');
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        icon.className = 'bi bi-chevron-up';
    } else {
        panel.style.display = 'none';
        icon.className = 'bi bi-chevron-down';
    }
}

function resetData() {
    if (confirm('Are you sure you want to reset all data? This action cannot be undone.')) {
        alert('Data reset functionality will be implemented soon.');
    }
}

function changeAdmin() {
    alert('Change Admin functionality will be implemented soon.');
}

function changeCompany() {
    const input = document.getElementById('company_name_input');
    if (input && typeof NS_CURRENT_COMPANY_NAME === 'string') {
        input.value = NS_CURRENT_COMPANY_NAME;
    }
    const m = new bootstrap.Modal(document.getElementById('companyModal'));
    m.show();
}

function showTrainingMaterials() {
    alert('Training Materials\n\nAccess to training resources and documentation will be available soon.');
}

function upgradeUsersQuota() {
    alert('Upgrade Users Quota\n\nContact support to increase your user limit beyond 10 users.');
}

function showBiziverseInvoices() {
    alert('Biziverse Invoices\n\nInvoice management and billing settings will be available soon.');
}

function showAccountAddress() {
    alert('Account Address\n\nCompany address and contact information settings will be available soon.');
}



function resendVerification(email) {
    if (confirm('Resend verification code to ' + email + '?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/?action=settings&subaction=resendVerification';
        
        const emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email';
        emailInput.value = email;
        
        form.appendChild(emailInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// User modal logic
let currentUser = { id: 0, name: '', email: '', phone: '', is_owner: 0 };
function openUserModal(id, name, email, phone, isOwner) {
    currentUser = { id, name, email, phone, is_owner: isOwner };
    document.getElementById('um_user_name').textContent = name;
    document.getElementById('um_name_input').value = name;
    document.getElementById('um_email_input').value = email;
    document.getElementById('um_id_inputs_name').value = id;
    document.getElementById('um_id_inputs_email').value = id;
    document.getElementById('um_phone_hidden_name').value = phone || '';
    document.getElementById('um_phone_hidden_email').value = phone || '';
    const delBtn = document.getElementById('um_delete_btn');
    if (delBtn) delBtn.disabled = (isOwner === 1);
    // default show none
    document.getElementById('um_name_form').classList.add('d-none');
    document.getElementById('um_email_form').classList.add('d-none');
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}

function toggleNameForm() {
    document.getElementById('um_name_form').classList.remove('d-none');
    document.getElementById('um_email_form').classList.add('d-none');
}
function toggleEmailForm() {
    document.getElementById('um_email_form').classList.remove('d-none');
    document.getElementById('um_name_form').classList.add('d-none');
}
function beforeSubmitName() {
    document.getElementById('um_name_hidden_email').value = document.getElementById('um_email_input').value;
    return true;
}
function beforeSubmitEmail() {
    document.getElementById('um_email_hidden_name').value = document.getElementById('um_name_input').value;
    return true;
}
function umSetPOC() {
    alert('Set as POC: feature not implemented yet.');
}
function umTransfer() {
    alert('Transfer Leads & Prospects: feature not implemented yet.');
}

// Rights modal
function openRightsModal(userId, userName) {
    document.getElementById('rights_user_id').value = userId;
    document.getElementById('rights_user_name').textContent = userName;
    const m = new bootstrap.Modal(document.getElementById('rightsModal'));
    m.show();
}
function submitCompanyForm(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('company_name_input');
    const errorEl = document.getElementById('company_name_error');
    if (!input) return false;
    const name = (input.value || '').trim();
    if (!name) {
        if (errorEl) errorEl.textContent = 'Company name is required';
        return false;
    }
    if (errorEl) errorEl.textContent = '';
    fetch('/?action=salesConfig&subaction=saveStoreSettings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ basic_company: name })
    }).then(r => r.json()).then(d => {
        if (d && d.success) {
            window.location.reload();
        } else {
            if (errorEl) errorEl.textContent = (d && d.error) ? d.error : 'Failed to save. Please try again.';
        }
    }).catch(() => {
        if (errorEl) errorEl.textContent = 'Network error. Please try again.';
    });
    return false;
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>

<!-- User Update Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 fw-bold" id="um_user_name"></div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openRightsModal(currentUser.id, currentUser.name)">Set Rights</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="umSetPOC()">Set as POC</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleNameForm()">Change Name</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleEmailForm()">Change Email</button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="umTransfer()">Transfer Leads & Prospects</button>
        </div>
        <form id="um_name_form" class="d-none" method="post" action="/?action=settings&subaction=update" onsubmit="return beforeSubmitName()">
            <input type="hidden" name="id" id="um_id_inputs_name">
            <input type="hidden" name="phone" id="um_phone_hidden_name">
            <div class="input-group mb-2">
                <span class="input-group-text">Name</span>
                <input type="text" class="form-control" name="name" id="um_name_input" required>
            </div>
            <input type="hidden" name="email" id="um_name_hidden_email">
            <button type="submit" class="btn btn-primary btn-sm">Save Name</button>
        </form>
        <form id="um_email_form" class="d-none" method="post" action="/?action=settings&subaction=update" onsubmit="return beforeSubmitEmail()">
            <input type="hidden" name="id" id="um_id_inputs_email">
            <input type="hidden" name="phone" id="um_phone_hidden_email">
            <div class="input-group mb-2">
                <span class="input-group-text">Email</span>
                <input type="email" class="form-control" name="email" id="um_email_input" required>
            </div>
            <input type="hidden" name="name" id="um_email_hidden_name">
            <button type="submit" class="btn btn-primary btn-sm">Save Email</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Keep delete form id in sync when modal opens
// delete disabled
</script>

<!-- Rights Modal -->
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

<!-- Company Name Change Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Company Name</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form onsubmit="return submitCompanyForm(event)">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" id="company_name_input" required>
            <div class="text-danger small mt-1" id="company_name_error"></div>
          </div>
          <p class="text-muted mb-0">Only the admin can change the company name. This will update how your company name appears on quotations, orders, invoices and other places.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
