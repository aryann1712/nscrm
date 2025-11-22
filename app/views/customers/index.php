<?php ob_start(); ?>
<?php $isSupplierPage = (($_GET['type'] ?? '') === 'supplier'); ?>

<div class="row mb-3">
  <div class="col-md-6">
    <h1 class="h3 mb-0">
      <i class="bi bi-person-lines-fill"></i> <?= $isSupplierPage ? 'Suppliers' : 'Customers' ?>
    </h1>
  </div>
  <div class="col-md-6 text-end">
    <button class="btn btn-primary" onclick="openCustomerModal()">
      <i class="bi bi-plus-circle"></i> <?= $isSupplierPage ? 'Add Supplier' : 'Add Customer' ?>
    </button>
  </div>
</div>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-sm-3">
        <label class="form-label">Search</label>
        <input type="text" id="searchQ" class="form-control" placeholder="Company, person, phone, email">
      </div>
      <div class="col-sm-2">
        <label class="form-label">Type</label>
        <select id="filterType" class="form-select">
          <option value="">All</option>
          <option value="customer">Customer</option>
          <option value="supplier">Supplier</option>
          <option value="neighbour">Neighbour</option>
          <option value="friend">Friend</option>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="form-label">Executive</label>
        <input type="text" id="filterExecutive" class="form-control" placeholder="Exec name">
      </div>
      <div class="col-sm-2">
        <label class="form-label">City</label>
        <select id="filterCity" class="form-select">
          <option value="">All</option>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="form-label">Status</label>
        <select id="filterActive" class="form-select">
          <option value="">All</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="col-sm-1 text-end">
        <button class="btn btn-success w-100" onclick="loadCustomers()"><i class="bi bi-search"></i></button>
      </div>
    </div>
  </div>
</div>

<!-- List -->
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle" id="customersTable">
        <thead class="table-dark">
          <tr>
            <th>Company</th>
            <th>Contact</th>
            <th>Type</th>
            <th>Executive</th>
            <th>City</th>
            <th>Last Talk</th>
            <th>Next Action</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div id="noCustomers" class="alert alert-info d-none mt-2"><i class="bi bi-info-circle"></i> No customers found.</div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customerModalLabel">Enter Connection</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('customerForm').requestSubmit()"><i class="bi bi-check2"></i> Save</button>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <form id="customerForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="customerId">
          <input type="hidden" name="contact_name" id="contact_name_hidden">
          <input type="hidden" name="type" id="type_hidden" value="customer">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Business <span class="text-danger">*</span></label>
              <input type="text" name="company" id="company" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col-2 col-md-2">
                  <select class="form-select" id="title" name="title">
                    <option value="Mr.">Mr.</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Mrs.">Mrs.</option>
                    <option value="Dr.">Dr.</option>
                  </select>
                </div>
                <div class="col-5 col-md-5">
                  <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name">
                </div>
                <div class="col-5 col-md-5">
                  <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mobile</label>
              <input type="text" name="contact_phone" id="contact_phone" class="form-control" placeholder="e.g., +91 98765 43210">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="contact_email" id="contact_email" class="form-control" placeholder="name@example.com">
            </div>

            <div class="col-12">
              <div class="d-flex gap-2 flex-wrap mt-2" id="typeButtons">
                <button class="btn btn-outline-warning type-btn active" data-type="customer" type="button">Customer</button>
                <button class="btn btn-outline-primary type-btn" data-type="supplier" type="button">Supplier</button>
                <button class="btn btn-outline-success type-btn" data-type="neighbour" type="button">Neighbour</button>
                <button class="btn btn-outline-secondary type-btn" data-type="friend" type="button">Friend</button>
              </div>
            </div>

            <div class="col-12">
              <a class="d-inline-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="collapse" href="#moreDetails" role="button" aria-expanded="false" aria-controls="moreDetails">
                <i class="bi bi-chevron-down"></i> Enter More Details
              </a>
              <div class="collapse mt-3" id="moreDetails">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" id="website" class="form-control" placeholder="https://...">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Industry & Segment</label>
                    <input type="text" name="industry_segment" id="industry_segment" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" id="country" class="form-control" value="India">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" id="state" class="form-control" placeholder="Select/Type">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">City</label>
                    <select name="city" id="city" class="form-select"></select>
                  </div>

                  <div class="col-12">
                    <button type="button" id="btnManageAddresses" class="btn btn-warning"><i class="bi bi-geo-alt"></i> Manage Addresses & GST</button>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Relation</label>
                    <input type="text" name="relation" id="relation" class="form-control" placeholder="e.g., Client, Dealer">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Executive</label>
                    <input type="text" name="executive" id="executive" class="form-control" placeholder="Owner/Executive">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Last Talk</label>
                    <input type="date" name="last_talk" id="last_talk" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Next Action</label>
                    <input type="date" name="next_action" id="next_action" class="form-control">
                  </div>

                  <div class="col-12 d-flex align-items-center">
                    <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                      <label class="form-check-label" for="is_active"> Active</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#customersTable td small { color: #6c757d; }
</style>

<script>
// Load and render customer addresses in details modal
async function loadCustomerAddresses(customerId){
  try {
    const res = await fetch('/?action=customers&subaction=listAddresses&customer_id=' + encodeURIComponent(customerId));
    const rows = await res.json();
    const container = document.getElementById('detailsAddresses');
    if (!container) return;
    container.innerHTML = '';
    if (!Array.isArray(rows) || rows.length === 0){
      container.innerHTML = '<div class="text-muted">No addresses yet</div>';
      return;
    }
    rows.forEach(a=>{
      const div = document.createElement('div');
      div.className = 'border rounded p-2 mb-2';
      const title = a.title ? `<strong>${escapeHtml(a.title)}</strong><br>` : '';
      const addr = [a.line1, a.line2].filter(Boolean).map(escapeHtml).join('<br>');
      const cityState = [a.city, a.state, a.country, a.pincode].filter(Boolean).map(escapeHtml).join(', ');
      const gst = a.gstin ? `<div><small class="text-muted">GST: ${escapeHtml(a.gstin)}</small></div>` : '';
      div.innerHTML = `${title}${addr ? addr + '<br>' : ''}${cityState}${gst}`;
      container.appendChild(div);
    });
  } catch (e) {
    const container = document.getElementById('detailsAddresses');
    if (container) container.innerHTML = '<div class="text-danger">Failed to load addresses</div>';
  }
}
let customerModal;
let addressModal;
let currentCustomerId = null; // set when editing existing customer
let pendingAddresses = []; // addresses captured before customer is saved
// Defaults: filter shows All (no type) on reload; form type defaults based on page
const defaultFilterType = <?= $isSupplierPage ? "'supplier'" : "''" ?>; // if page explicitly requests suppliers, keep it; else All
const defaultFormType = <?= $isSupplierPage ? "'supplier'" : "'customer'" ?>;

function cityOption(value, label) {
  const opt = document.createElement('option');
  opt.value = value ?? '';
  opt.textContent = label ?? '';
  return opt;
}

function populateCities() {
  // Filter dropdown
  fetch('/?action=salesConfig&subaction=listCities')
    .then(r => r.json())
    .then(data => {
      const filterCity = document.getElementById('filterCity');
      const citySel = document.getElementById('city');
      // clear
      filterCity.innerHTML = '';
      filterCity.appendChild(cityOption('', 'All'));
      citySel.innerHTML = '';
      citySel.appendChild(cityOption('', 'Select City'));
      (data || []).forEach(c => {
        filterCity.appendChild(cityOption(c.name, c.name));
        citySel.appendChild(cityOption(c.name, c.name));
      });
    })
    .catch(() => {/* ignore */});
}

function loadCustomers() {
  const params = new URLSearchParams();
  const q = document.getElementById('searchQ').value.trim();
  const type = document.getElementById('filterType').value;
  const executive = document.getElementById('filterExecutive').value.trim();
  const city = document.getElementById('filterCity').value;
  const active = document.getElementById('filterActive').value;
  if (q) params.append('q', q);
  if (type) params.append('type', type);
  if (executive) params.append('executive', executive);
  if (city) params.append('city', city);
  if (active !== '') params.append('active', active);

  fetch('/?action=customers&subaction=list' + (params.toString() ? ('&' + params.toString()) : ''))
    .then(r => r.json())
    .then(rows => renderTable(rows || []))
    .catch(err => {
      console.error(err);
      renderTable([]);
    });
}

let customersCache = [];
function renderTable(rows) {
  customersCache = rows;
  const tbody = document.querySelector('#customersTable tbody');
  tbody.innerHTML = '';
  if (!rows.length) {
    document.getElementById('noCustomers').classList.remove('d-none');
    return;
  }
  document.getElementById('noCustomers').classList.add('d-none');
  rows.forEach((r, i) => {
    const tr = document.createElement('tr');
    tr.style.cursor = 'pointer';
    tr.setAttribute('data-index', i);
    tr.innerHTML = `
      <td>
        <strong>${escapeHtml(r.company || '')}</strong>
        ${r.relation ? `<br><small>${escapeHtml(r.relation)}</small>` : ''}
      </td>
      <td>
        ${r.contact_name ? `<div>${escapeHtml(r.contact_name)}</div>` : ''}
        ${r.contact_phone ? `<small class="me-2"><i class="bi bi-phone"></i> ${escapeHtml(r.contact_phone)}</small>` : ''}
        ${r.contact_email ? `<small><i class="bi bi-envelope"></i> ${escapeHtml(r.contact_email)}</small>` : ''}
      </td>
      <td>${escapeHtml(r.type || '')}</td>
      <td>${escapeHtml(r.executive || '')}</td>
      <td>${escapeHtml(r.city || '')}</td>
      <td>${r.last_talk ? escapeHtml(r.last_talk) : ''}</td>
      <td>${r.next_action ? escapeHtml(r.next_action) : ''}</td>
      <td>${(+r.is_active === 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
      <td>
        <div class="btn-group btn-group-sm" role="group">
          <button class="btn btn-warning" title="Edit" onclick="event.stopPropagation(); openDetailsFromIndex(${i})"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-secondary" title="Delete" onclick="event.stopPropagation(); deleteCustomer(${r.id})"><i class="bi bi-trash"></i></button>
        </div>
      </td>`;
    tr.addEventListener('click', () => openDetailsFromIndex(i));
    tbody.appendChild(tr);
  });
}

function editCustomerFromIndex(i){
  const row = customersCache[i];
  if (!row) return;
  editCustomer(row);
}

function openDetailsFromIndex(i){
  const row = customersCache[i];
  if (!row) return;
  openDetailsModal(row);
}

function openDetailsModal(row){
  // Header
  const titleEl = document.getElementById('detailsTitleName');
  if (titleEl) titleEl.textContent = row.company || '';

  // Contact Information
  document.getElementById('detailsContactName').textContent = row.contact_name || '-';
  document.getElementById('detailsContactPhone').textContent = row.contact_phone || '-';
  document.getElementById('detailsContactEmail').textContent = row.contact_email || '-';

  // Business Opportunity (simple placeholders from relation/industry)
  const opp = document.getElementById('detailsBusinessOpp');
  opp.innerHTML = '';
  if (row.relation) {
    const badge = document.createElement('span');
    badge.className = 'badge text-bg-light me-2 mb-2';
    badge.textContent = row.relation;
    opp.appendChild(badge);
  }
  if (row.industry_segment) {
    const badge2 = document.createElement('span');
    badge2.className = 'badge text-bg-light me-2 mb-2';
    badge2.textContent = `Segment: ${row.industry_segment}`;
    opp.appendChild(badge2);
  }

  // Next Appointment
  document.getElementById('detailsNextAppt').textContent = row.next_action || '—';

  // Interactions placeholder
  const interactions = document.getElementById('detailsInteractions');
  interactions.innerHTML = '';
  const empty = document.createElement('div');
  empty.className = 'text-muted';
  empty.textContent = 'No interactions yet';
  interactions.appendChild(empty);

  // Buttons actions
  const detailsEl = document.getElementById('customerDetailsModal');
  const btnModify = document.getElementById('btnDetailsModify');
  if (btnModify) btnModify.onclick = () => {
    // Prefer to close details, then open edit to avoid stacking issues
    const dm = bootstrap.Modal.getInstance(detailsEl) || new bootstrap.Modal(detailsEl);
    detailsEl.addEventListener('hidden.bs.modal', () => {
      // Small defer to ensure backdrop removed
      setTimeout(() => { editCustomer(row); }, 10);
    }, { once: true });
    dm.hide();
  };
  document.getElementById('btnDetailsDelete').onclick = () => {
    if (!confirm('Delete this customer?')) return;
    deleteCustomer(row.id);
    const md = bootstrap.Modal.getInstance(document.getElementById('customerDetailsModal'));
    if (md) md.hide();
  };
  // Wire action buttons
  const actionsEl = document.getElementById('detailsActions');
  const btnReassign = document.getElementById('btnCustReassign');
  const btnUpdateStatus = document.getElementById('btnCustUpdateStatus');
  const btnQuote = document.getElementById('btnCustQuote');
  const btnPI = document.getElementById('btnCustPI');
  const btnOrder = document.getElementById('btnCustOrder');
  const btnInvoice = document.getElementById('btnCustInvoice');
  const btnHistory = document.getElementById('btnCustHistory');

  if (btnReassign) btnReassign.onclick = () => alert('Reassign coming soon');
  if (btnUpdateStatus) btnUpdateStatus.onclick = () => openCustomerStatus(row);
  const qParams = (obj) => Object.entries(obj)
    .filter(([,v]) => v !== undefined && v !== null && String(v).length > 0)
    .map(([k,v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`)
    .join('&');
  const basePrefill = {
    customer: row.company || '',
    contact_person: row.contact_name || '',
    reference: `Customer #${row.id}`
  };
  if (btnQuote) btnQuote.onclick = () => {
    const params = qParams({ ...basePrefill, type: 'Quotation' });
    window.location.href = `/?action=quotations&subaction=create&${params}`;
  };
  if (btnPI) btnPI.onclick = () => {
    const params = qParams({ ...basePrefill, type: 'Proforma' });
    window.location.href = `/?action=quotations&subaction=create&${params}`;
  };
  if (btnOrder) btnOrder.onclick = () => {
    const params = new URLSearchParams({ customer_id: row.id });
    window.location.href = `/?action=orders&subaction=create&${params.toString()}`;
  };
  if (btnInvoice) btnInvoice.onclick = () => alert('Invoices module is not available yet.');
  if (btnHistory) btnHistory.onclick = () => alert('Business History coming soon');

  // Status badge in header
  const statusBadge = document.getElementById('detailsStatusBadge');
  if (statusBadge) {
    const active = (+row.is_active === 1);
    statusBadge.textContent = active ? 'Active' : 'Inactive';
    statusBadge.className = `badge ${active ? 'text-bg-success' : 'text-bg-secondary'}`;
  }

  // Load Addresses into details
  loadCustomerAddresses(row.id);

  // Show modal
  const md = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
  md.show();
}

function editCustomer(row) {
  resetForm();
  document.getElementById('customerModalLabel').textContent = 'Edit Connection';
  document.getElementById('customerId').value = row.id || '';
  currentCustomerId = row.id || null;
  document.getElementById('company').value = row.company || '';
  document.getElementById('relation').value = row.relation || '';
  // Prefill name parts for visibility
  const fullName = (row.contact_name || '').trim();
  document.getElementById('contact_name_hidden').value = fullName;
  if (fullName){
    const titles = ['Mr.', 'Ms.', 'Mrs.', 'Dr.'];
    let parts = fullName.split(/\s+/);
    let title = titles.includes(parts[0]) ? parts.shift() : '';
    const first = parts.shift() || '';
    const last = parts.join(' ');
    if (title) document.getElementById('title').value = title;
    document.getElementById('first_name').value = first;
    document.getElementById('last_name').value = last;
  }
  document.getElementById('contact_phone').value = row.contact_phone || '';
  document.getElementById('contact_email').value = row.contact_email || '';
  // Type buttons
  setType(row.type || 'customer');
  document.getElementById('executive').value = row.executive || '';
  // Ensure city select is populated before selecting value
  const setCityValue = (val)=>{ const citySel = document.getElementById('city'); if (citySel) { citySel.value = val || ''; } };
  if (document.getElementById('city')?.options?.length > 0) { setCityValue(row.city || ''); }
  else { setTimeout(()=> setCityValue(row.city || ''), 300); }
  document.getElementById('website').value = row.website || '';
  document.getElementById('industry_segment').value = row.industry_segment || '';
  document.getElementById('country').value = row.country || 'India';
  document.getElementById('state').value = row.state || '';
  document.getElementById('last_talk').value = row.last_talk || '';
  document.getElementById('next_action').value = row.next_action || '';
  document.getElementById('is_active').checked = (+row.is_active === 1);
  customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
  customerModal.show();
}

function openCustomerModal(){
  resetForm();
  currentCustomerId = null;
  document.getElementById('customerModalLabel').textContent = 'Enter Connection';
  // default type
  setType(defaultFormType);
  customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
  customerModal.show();
}

function resetForm() {
  document.getElementById('customerForm').reset();
  document.getElementById('customerId').value = '';
}

function deleteCustomer(id) {
  if (!confirm('Delete this customer?')) return;
  fetch('/?action=customers&subaction=delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ id: id })
  }).then(r => r.json())
    .then(resp => {
      if (resp && resp.success) {
        loadCustomers();
      } else {
        alert(resp.error || 'Failed to delete');
      }
    })
    .catch(err => alert('Error: ' + err.message));
}

// Handle form submit
(document.getElementById('customerForm')).addEventListener('submit', function(e){
  e.preventDefault();
  const form = e.target;
  // Compose contact_name from parts if provided
  const title = document.getElementById('title').value.trim();
  const first = document.getElementById('first_name').value.trim();
  const last = document.getElementById('last_name').value.trim();
  const existingContact = document.getElementById('contact_name_hidden').value.trim();
  const composed = (title + ' ' + first + ' ' + last).trim();
  document.getElementById('contact_name_hidden').value = composed || existingContact || '';

  // Ensure type from buttons is submitted
  document.getElementById('type_hidden').value = (document.querySelector('#typeButtons .type-btn.active')?.dataset.type) || 'customer';

  const formData = new URLSearchParams(new FormData(form));
  const selectedType = (document.getElementById('type_hidden').value || 'customer');

  const id = document.getElementById('customerId').value;
  const endpoint = id ? '/?action=customers&subaction=update' : '/?action=customers&subaction=create';

  // Normalize checkbox for backend expected field name (active vs is_active)
  if (!formData.has('is_active')) formData.append('is_active', '0');

  fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  }).then(r => r.json())
    .then(resp => {
      if (resp.error) {
        alert(resp.error);
        return;
      }
      const newId = resp.id || document.getElementById('customerId').value || null;
      // If there are staged addresses, create them now against the customer id
      const createAllAddresses = async (cid) => {
        let failed = 0; let lastDetail = '';
        for (const addr of pendingAddresses) {
          const params = new URLSearchParams({ ...addr, customer_id: cid });
          try {
            const r = await fetch('/?action=customers&subaction=createAddress', {
              method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString()
            });
            const respA = await r.json();
            if (respA && respA.error) { failed++; lastDetail = respA.detail || ''; }
          } catch (e) { failed++; lastDetail = e.message || String(e); }
        }
        pendingAddresses = [];
        updatePendingBadge();
        if (failed > 0) { alert('Some addresses failed to save ('+failed+'). '+ (lastDetail? ('Details: '+lastDetail):'')); }
      };
      if (newId) {
        createAllAddresses(newId).then(()=>{
          bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
          if (selectedType === 'supplier') {
            window.location.href = '/?action=customers&type=supplier';
          } else {
            loadCustomers();
          }
        });
      } else {
        // No id returned (update path), just close and refresh
        bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
        if (selectedType === 'supplier') {
          window.location.href = '/?action=customers&type=supplier';
        } else {
          loadCustomers();
        }
      }
    })
    .catch(err => alert('Error: ' + err.message));
});

function escapeHtml(s){
  return String(s || '')
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#039;');
}

// Init
// Pre-select the filter (All by default for customers page)
document.getElementById('filterType').value = defaultFilterType || '';
populateCities();
loadCustomers();

const searchEl = document.getElementById('searchQ');
if (searchEl) searchEl.addEventListener('keypress', e => { if (e.key==='Enter') loadCustomers(); });

// Manage Addresses & GST button
const manageAddrBtn = document.getElementById('btnManageAddresses');
if (manageAddrBtn) manageAddrBtn.addEventListener('click', async () => {
  const id = document.getElementById('customerId').value || currentCustomerId;
  if (!id) {
    // Allow adding address before save: stage it locally
    openAddressModal({});
    return;
  }
  try {
    const cid = parseInt(id,10);
    const res = await fetch('/?action=customers&subaction=listAddresses&customer_id=' + cid);
    const rows = await res.json();
    if (Array.isArray(rows) && rows.length > 0){
      // Prefill the first address for editing
      openAddressModal({ customer_id: cid, address: rows[0] });
      // Ensure city select is set after options
      setTimeout(()=>{ const sel=document.getElementById('addrCity'); if (sel) sel.value = rows[0].city || ''; }, 200);
    } else {
      openAddressModal({ customer_id: cid });
    }
  } catch(e){ openAddressModal({ customer_id: parseInt(id,10) }); }
});


// Address Modal Implementation
function openAddressModal({ customer_id, address = null, gstin = '' } = {}){
  resetAddressForm();
  document.getElementById('addrCustomerId').value = customer_id || '';
  if (address) {
    document.getElementById('addrId').value = address.id || '';
    document.getElementById('addrTitle').value = address.title || '';
    document.getElementById('addrLine1').value = address.line1 || '';
    document.getElementById('addrLine2').value = address.line2 || '';
    document.getElementById('addrCity').value = address.city || '';
    document.getElementById('addrCountry').value = address.country || 'India';
    document.getElementById('addrState').value = address.state || '';
    document.getElementById('addrPincode').value = address.pincode || '';
    document.getElementById('addrGSTIN').value = address.gstin || '';
    document.getElementById('addrExtraKey').value = address.extra_key || '';
    document.getElementById('addrExtraValue').value = address.extra_value || '';
    document.getElementById('addressModalLabel').textContent = 'Edit Address';
  } else {
    document.getElementById('addressModalLabel').textContent = 'Add Address';
    if (gstin) document.getElementById('addrGSTIN').value = gstin;
  }
  addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
  addressModal.show();
}

function resetAddressForm(){
  document.getElementById('addressForm').reset();
  document.getElementById('addrId').value = '';
  document.getElementById('addrCustomerId').value = '';
}

document.addEventListener('DOMContentLoaded', function(){
  const addressForm = document.getElementById('addressForm');
  if (addressForm) addressForm.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(e.target);
    const addrCustomerId = (fd.get('customer_id')||'').toString().trim();
    const id = (fd.get('id')||'').toString().trim();
    if (!addrCustomerId) {
      // Stage locally
      const staged = Object.fromEntries(fd.entries());
      delete staged['id'];
      staged['customer_id'] = '';
      pendingAddresses.push(staged);
      bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
      updatePendingBadge();
      return;
    }
    // Existing customer: submit to backend
    const formData = new URLSearchParams(fd);
    const endpoint = id ? '/?action=customers&subaction=updateAddress' : '/?action=customers&subaction=createAddress';
    fetch(endpoint, {
      method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: formData.toString()
    }).then(r => r.json()).then(resp => {
      if (resp.error) { alert((resp.error||'Failed') + (resp.detail? ('\n'+resp.detail):'')); return; }
      const custId = (document.getElementById('addrCustomerId').value||'').trim();
      bootstrap.Modal.getInstance(document.getElementById('addressModal')).hide();
      if (custId) { loadCustomerAddresses(parseInt(custId,10)); }
    }).catch(err => alert('Error: ' + err.message));
  });
});

// Small visual hint of staged addresses count
function updatePendingBadge(){
  let btn = document.getElementById('btnManageAddresses');
  if (!btn) return;
  const count = pendingAddresses.length;
  const existing = btn.querySelector('.pending-badge');
  if (count > 0){
    if (!existing){
      const span = document.createElement('span');
      span.className = 'badge bg-info ms-2 pending-badge';
      span.textContent = String(count);
      btn.appendChild(span);
    } else {
      existing.textContent = String(count);
    }
  } else if (existing){
    existing.remove();
  }
}

// Populate Address modal city select from Sales Config cities
function populateAddressCities(){
  fetch('/?action=salesConfig&subaction=listCities')
    .then(r=>r.json())
    .then(rows=>{
      const sel = document.getElementById('addrCity');
      if (!sel) return;
      sel.innerHTML = '';
      const def = document.createElement('option'); def.value = ''; def.textContent = 'Select City'; sel.appendChild(def);
      (rows||[]).forEach(c=>{ const o=document.createElement('option'); o.value=c.name; o.textContent=c.name; sel.appendChild(o); });
    })
    .catch(()=>{});
}
populateAddressCities();

// Type buttons handler (global)
const typeButtons = document.getElementById('typeButtons');
if (typeButtons) typeButtons.addEventListener('click', function(e){
  if (!e.target.classList.contains('type-btn')) return;
  setType(e.target.dataset.type);
});

function setType(t){
  document.querySelectorAll('#typeButtons .type-btn').forEach(btn => btn.classList.remove('active'));
  const btn = document.querySelector(`#typeButtons .type-btn[data-type="${t}"]`);
  if (btn) btn.classList.add('active');
  const hidden = document.getElementById('type_hidden');
  if (hidden) hidden.value = t;
}
</script>

<!-- Customer Update Status Modal -->
<div class="modal" id="customerStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="frmCustomerStatus" action="/?action=customers&subaction=update" method="post">
        <div class="modal-body">
          <input type="hidden" name="id" id="custStatusId">
          <div class="mb-2 text-muted">Current: <span class="badge bg-secondary" id="custStatusCurrent">-</span></div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="is_active_radio" id="custActive" value="1">
            <label class="form-check-label" for="custActive">Active</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="is_active_radio" id="custInactive" value="0">
            <label class="form-check-label" for="custInactive">Inactive</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
  </div>

<script>
function openCustomerStatus(row){
  // prefill
  document.getElementById('custStatusId').value = row.id;
  const active = (+row.is_active === 1);
  document.getElementById('custStatusCurrent').textContent = active ? 'Active' : 'Inactive';
  document.getElementById('custActive').checked = active;
  document.getElementById('custInactive').checked = !active;

  const mEl = document.getElementById('customerStatusModal');
  const m = bootstrap.Modal.getOrCreateInstance(mEl);
  // Ensure above details modal
  mEl.addEventListener('shown.bs.modal', () => {
    mEl.style.zIndex = 1062;
    const backs = document.querySelectorAll('.modal-backdrop');
    const last = backs[backs.length - 1];
    if (last) last.style.zIndex = 1061;
  }, { once: true });
  m.show();

  // Attach submit once per open
  const frm = document.getElementById('frmCustomerStatus');
  const submitHandler = async (e) => {
    e.preventDefault();
    const id = document.getElementById('custStatusId').value;
    const isActive = document.getElementById('custActive').checked ? '1' : '0';
    const body = new URLSearchParams({ id, is_active: isActive });
    try {
      const resp = await fetch(frm.getAttribute('action'), {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString()
      });
      if (!resp.ok) throw new Error('Request failed');
      // Update UI: status badge in details modal
      const badge = document.getElementById('detailsStatusBadge');
      if (badge) {
        const activeNow = isActive === '1';
        badge.textContent = activeNow ? 'Active' : 'Inactive';
        badge.className = `badge ${activeNow ? 'text-bg-success' : 'text-bg-secondary'}`;
      }
      // Also refresh list to reflect badge change
      loadCustomers();
      const inst = bootstrap.Modal.getInstance(mEl) || bootstrap.Modal.getOrCreateInstance(mEl);
      inst.hide();
      // Update cached row's is_active for future opens
      row.is_active = parseInt(isActive, 10);
    } catch (err) {
      alert('Failed to update status');
      console.error(err);
    }
  };
  frm.addEventListener('submit', submitHandler, { once: true });
}
</script>

<style>
/* Ensure second modal stacks over first */
.modal.show:nth-of-type(2) { z-index: 1062; }
.modal-backdrop.show:nth-of-type(2) { z-index: 1061; }
</style>

<!-- Customer Details Modal (CRM-style) -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header align-items-start">
        <div>
          <h5 class="modal-title" id="customerDetailsLabel">
            <span id="detailsTitleName">-</span>
            <span id="detailsStatusBadge" class="badge text-bg-secondary ms-2">Inactive</span>
            <i class="bi bi-pencil ms-2 text-muted"></i>
          </h5>
        </div>
        <div class="d-flex gap-2">
          <span class="badge text-bg-warning">Proposal</span>
          <button id="btnDetailsModify" type="button" class="btn btn-sm btn-warning">Modify</button>
          <button id="btnDetailsDelete" type="button" class="btn btn-sm btn-danger">Delete</button>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card mb-3">
              <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Contact Information</strong>
                <div>
                  <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-4 text-muted">Name</div>
                  <div class="col-8" id="detailsContactName">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-4 text-muted">Mobile</div>
                  <div class="col-8">
                    <span id="detailsContactPhone">-</span>
                    <span class="ms-2 text-muted"><i class="bi bi-telephone"></i></span>
                    <span class="ms-2 text-muted"><i class="bi bi-telephone-outbound"></i></span>
                    <span class="ms-2 text-muted"><i class="bi bi-clipboard"></i></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-4 text-muted">Email</div>
                  <div class="col-8" id="detailsContactEmail">-</div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Business Opportunity</strong>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
              </div>
              <div class="card-body">
                <div id="detailsBusinessOpp"></div>
              </div>
            </div>
            
            <div class="card mt-3">
              <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Addresses & GST</strong>
                <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); const id=currentCustomerId || (customersCache.find(c=>c.company===document.getElementById('detailsTitleName').textContent)||{}).id; if(id){ openAddressModal({ customer_id:id }); }"><i class="bi bi-plus"></i></button>
              </div>
              <div class="card-body" id="detailsAddresses">
                <div class="text-muted">No addresses yet</div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card mb-3">
              <div class="card-header"><strong>Actions</strong></div>
              <div class="card-body d-flex flex-wrap gap-2" id="detailsActions">
                <button type="button" id="btnCustReassign" class="btn btn-secondary btn-sm">Reassign</button>
                <button type="button" id="btnCustUpdateStatus" class="btn btn-primary btn-sm">Update Status</button>
                <button type="button" id="btnCustQuote" class="btn btn-success btn-sm">Quote</button>
                <button type="button" id="btnCustPI" class="btn btn-success btn-sm">PI</button>
                <button type="button" id="btnCustOrder" class="btn btn-success btn-sm">Order</button>
                <button type="button" id="btnCustInvoice" class="btn btn-success btn-sm">Invoice</button>
                <button type="button" id="btnCustHistory" class="btn btn-success btn-sm">Business History</button>
              </div>
            </div>

            <div class="card">
              <div class="card-header"><strong>Business Interactions</strong></div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-6 text-muted">Next Appointment</div>
                  <div class="col-6 text-end">
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></button>
                  </div>
                  <div class="col-12" id="detailsNextAppt">—</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <strong>Interactions</strong>
                  <button class="btn btn-sm btn-success">+ Enter Interaction</button>
                </div>
                <div id="detailsInteractions"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">Add Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addressForm">
        <div class="modal-body">
          <input type="hidden" id="addrId" name="id">
          <input type="hidden" id="addrCustomerId" name="customer_id">

          <div class="mb-2">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="addrTitle" name="title" placeholder="Head office, Warehouse etc.">
          </div>

          <div class="mb-2">
            <label class="form-label">Address</label>
            <input type="text" class="form-control mb-2" id="addrLine1" name="line1" placeholder="Line 1">
            <input type="text" class="form-control" id="addrLine2" name="line2" placeholder="Line 2">
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">City</label>
              <select class="form-select" id="addrCity" name="city"></select>
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">Country</label>
              <select class="form-select" id="addrCountry" name="country">
                <option value="India" selected>India</option>
              </select>
            </div>
            <div class="col">
              <label class="form-label">State</label>
              <input type="text" class="form-control" id="addrState" name="state" placeholder="Select">
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">Pincode</label>
              <input type="text" class="form-control" id="addrPincode" name="pincode">
            </div>
            <div class="col">
              <label class="form-label">GST</label>
              <input type="text" class="form-control" id="addrGSTIN" name="gstin">
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Extra Field (e.g. Mobile: 9000012345)</label>
            <div class="input-group">
              <input type="text" class="form-control" id="addrExtraKey" name="extra_key" placeholder="Key">
              <span class="input-group-text">:</span>
              <input type="text" class="form-control" id="addrExtraValue" name="extra_value" placeholder="Value">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
