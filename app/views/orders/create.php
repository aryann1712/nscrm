<?php ob_start(); ?>

<div class="row mb-3">
  <div class="col-md-6">
    <h1 class="h3 mb-0">
      <i class="bi bi-cart"></i> Enter Order
    </h1>
  </div>
  <div class="col-md-6 text-end">
    <a class="btn btn-secondary me-2" href="/?action=orders"><i class="bi bi-arrow-left"></i> Back</a>
    <button type="submit" form="frmOrder" class="btn btn-success"><i class="bi bi-check2"></i> Save</button>
  </div>
</div>

<form method="post" action="/?action=orders&subaction=store" id="frmOrder">
  <!-- Top details -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Customer/Lead</label>
              <input type="hidden" name="customer_id" id="customer_id" value="<?= htmlspecialchars($customer['id'] ?? '') ?>">
              <input type="hidden" name="lead_id" id="lead_id" value="">
              <input type="hidden" name="connection_type" id="connection_type" value="<?= isset($customer['id']) ? 'customer' : '' ?>">
              <div class="input-group">
                <input type="text" class="form-control" id="customerName" value="<?= htmlspecialchars($customer['company'] ?? '') ?>" placeholder="Select customer or lead" readonly>
                <button type="button" class="btn btn-outline-secondary" title="Select from Connections" data-bs-toggle="modal" data-bs-target="#connectionModal" id="btnBrowse">Browse</button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact</label>
              <input type="text" class="form-control" name="contact_name" value="<?= htmlspecialchars($customer['contact_name'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Billing Address</label>
              <div class="input-group">
                <input type="text" class="form-control" name="billing_address" placeholder="Select billing address" readonly>
                <button type="button" class="btn btn-outline-secondary">Select</button>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="sameAsBilling" checked>
                <label class="form-check-label" for="sameAsBilling">Shipping address is same as billing address</label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Order No.</label>
              <input type="text" class="form-control" name="order_no" placeholder="Auto">
            </div>
            <div class="col-6">
              <label class="form-label">Reference</label>
              <input type="text" class="form-control" name="reference">
            </div>
            <div class="col-6">
              <label class="form-label">Order Date</label>
              <input type="date" class="form-control" name="order_date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" name="due_date">
            </div>
            <div class="col-12">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php foreach (["Pending","Received","Bill Submitted","Delivered"] as $st): ?>
                  <option value="<?= $st ?>"><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Items and totals -->
  <div class="row g-3">
    <div class="col-lg-9">
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>Item List</strong>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus"></i> Add Item</button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle" id="itemsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:28%">Item / Description</th>
                  <th style="width:10%">HSN/SAC</th>
                  <th style="width:8%">Qty</th>
                  <th style="width:9%">Unit</th>
                  <th style="width:12%">Rate</th>
                  <th style="width:9%">Disc %</th>
                  <th style="width:9%">Tax %</th>
                  <th style="width:12%" class="text-end">Total (₹)</th>
                  <th style="width:3%"></th>
                </tr>
              </thead>
              <tbody id="itemsBody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>Terms & Conditions</strong>
          <div class="text-muted small">Loaded from Sales Configuration; edits apply only to this order</div>
        </div>
        <div class="card-body">
          <div id="termsContainer" class="vstack gap-2">
            <!-- terms rendered by JS -->
          </div>
          <hr>
          <div class="input-group">
            <input type="text" class="form-control" id="newTermText" placeholder="Add new term (order-only)">
            <button type="button" class="btn btn-outline-primary" id="btnAddTerm"><i class="bi bi-plus-lg"></i> Add</button>
          </div>
          <div class="form-text">Checked terms will be submitted with this order only. Changes here do not modify Sales Configuration.</div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body row g-3">
          <div class="col-md-8">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3"></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Next Action</label>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="next_action[]" value="Create Delivery">
              <label class="form-check-label">Create Delivery</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="next_action[]" value="Create Invoice">
              <label class="form-check-label">Create Invoice</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="next_action[]" value="Create Purchase Entry">
              <label class="form-check-label">Create Purchase Entry</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3">
      <div class="card">
        <div class="card-header"><strong>Totals</strong></div>
        <div class="card-body">
          <div class="d-flex justify-content-between"><span>Sub Total</span><span id="subTotal">0.00</span></div>
          <div class="d-flex justify-content-between"><span>Discount</span><span id="discountTotal">0.00</span></div>
          <div class="d-flex justify-content-between"><span>Tax</span><span id="taxTotal">0.00</span></div>
          <hr>
          <div class="d-flex justify-content-between fw-bold"><span>Grand Total</span><span id="grandTotal">0.00</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="text-end mt-3">
    <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Save Order</button>
  </div>
</form>

<!-- Connections Modal -->
<div class="modal fade" id="connectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-people"></i> Select Customer or Lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="connTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-customers" data-bs-toggle="tab" data-bs-target="#pane-customers" type="button" role="tab">Customers</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-leads" data-bs-toggle="tab" data-bs-target="#pane-leads" type="button" role="tab">Leads</button>
          </li>
        </ul>
        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="pane-customers" role="tabpanel" aria-labelledby="tab-customers">
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" id="searchCustomers" placeholder="Search customers by name, city, executive...">
            </div>
            <div class="list-group" id="listCustomers"></div>
          </div>
          <div class="tab-pane fade" id="pane-leads" role="tabpanel" aria-labelledby="tab-leads">
            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" id="searchLeads" placeholder="Search leads by business, contact...">
            </div>
            <div class="list-group" id="listLeads"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="/?action=customers" class="btn btn-outline-primary"><i class="bi bi-plus-lg"></i> Add New Connection</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<script>
function money(n){ return (n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }

function addItem(pref){
  const body = document.getElementById('itemsBody');
  const idx = body.children.length;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input name="items[${idx}][item_name]" class="form-control form-control-sm" placeholder="Item name">
      <input name="items[${idx}][desc]" class="form-control form-control-sm mt-1" placeholder="Description">
    </td>
    <td><input name="items[${idx}][hsn]" class="form-control form-control-sm"></td>
    <td><input name="items[${idx}][qty]" class="form-control form-control-sm calc" type="number" step="0.01" value="1"></td>
    <td><input name="items[${idx}][unit]" class="form-control form-control-sm" value="nos"></td>
    <td><input name="items[${idx}][rate]" class="form-control form-control-sm calc" type="number" step="0.01" value="0"></td>
    <td><input name="items[${idx}][disc]" class="form-control form-control-sm calc" type="number" step="0.01" value="0"></td>
    <td><input name="items[${idx}][tax]" class="form-control form-control-sm calc" type="number" step="0.01" value="0"></td>
    <td class="text-end fw-semibold"><span class="lineTotal">0.00</span></td>
    <td class="text-center"><button class="btn btn-sm btn-outline-danger" type="button" onclick="this.closest('tr').remove(); computeTotals();"><i class="bi bi-x"></i></button></td>
  `;
  body.appendChild(tr);
  tr.querySelectorAll('.calc').forEach(inp => inp.addEventListener('input', computeTotals));
  if (pref) {
    for (const k in pref) {
      const el = tr.querySelector(`[name="items[${idx}][${k}]\"]`);
      if (el) el.value = pref[k];
    }
  }
  computeTotals();
}

function computeTotals(){
  const rows = document.querySelectorAll('#itemsBody tr');
  let sub = 0, discTotal = 0, taxTotal = 0;
  rows.forEach(tr => {
    const qty = parseFloat(tr.querySelector('[name$="[qty]\"]').value)||0;
    const rate = parseFloat(tr.querySelector('[name$="[rate]\"]').value)||0;
    const disc = parseFloat(tr.querySelector('[name$="[disc]\"]').value)||0;
    const tax = parseFloat(tr.querySelector('[name$="[tax]\"]').value)||0;
    const line = qty * rate;
    const lineDisc = line * (disc/100);
    const lineAfterDisc = line - lineDisc;
    const lineTax = lineAfterDisc * (tax/100);
    const lineTotal = lineAfterDisc + lineTax;
    sub += line;
    discTotal += lineDisc;
    taxTotal += lineTax;
    tr.querySelector('.lineTotal').textContent = money(lineTotal);
  });
  document.getElementById('subTotal').textContent = money(sub);
  document.getElementById('discountTotal').textContent = money(discTotal);
  document.getElementById('taxTotal').textContent = money(taxTotal);
  document.getElementById('grandTotal').textContent = money(sub - discTotal + taxTotal);
}

// Legacy free-text TnC replaced by SalesConfig-backed terms

// init
addItem();
computeTotals();

// --- Connections modal logic ---
const listCustomers = document.getElementById('listCustomers');
const listLeads = document.getElementById('listLeads');
const searchCustomers = document.getElementById('searchCustomers');
const searchLeads = document.getElementById('searchLeads');

function debounce(fn, wait){ let t; return (...args)=>{ clearTimeout(t); t = setTimeout(()=>fn(...args), wait); } }

async function fetchCustomers(q=''){
  const url = new URL(window.location.origin + '/');
  url.searchParams.set('action','customers');
  url.searchParams.set('subaction','list');
  if(q) url.searchParams.set('q', q);
  const res = await fetch(url.toString());
  if(!res.ok) return [];
  return await res.json();
}

async function fetchLeads(q=''){
  const url = new URL(window.location.origin + '/');
  url.searchParams.set('action','crm');
  url.searchParams.set('subaction','listJson');
  if(q) url.searchParams.set('q', q);
  const res = await fetch(url.toString());
  if(!res.ok) return [];
  return await res.json();
}

function renderCustomers(rows){
  listCustomers.innerHTML = '';
  (rows||[]).forEach(r=>{
    const a = document.createElement('a');
    a.href = 'javascript:void(0)';
    a.className = 'list-group-item list-group-item-action';
    a.innerHTML = `<div class="d-flex w-100 justify-content-between"><strong>${r.company||''}</strong><small>#${r.id}</small></div>
                   <div class="small text-muted">${r.contact_name||''}${r.city? ' · '+r.city: ''}</div>`;
    a.addEventListener('click', ()=>selectCustomer(r));
    listCustomers.appendChild(a);
  });
}

function renderLeads(rows){
  listLeads.innerHTML = '';
  (rows||[]).forEach(r=>{
    const a = document.createElement('a');
    a.href = 'javascript:void(0)';
    a.className = 'list-group-item list-group-item-action';
    a.innerHTML = `<div class="d-flex w-100 justify-content-between"><strong>${r.business_name||''}</strong><span class="badge bg-warning text-dark">Lead</span></div>
                   <div class="small text-muted">${r.contact_person||''}</div>`;
    a.addEventListener('click', ()=>selectLead(r));
    listLeads.appendChild(a);
  });
}

function selectCustomer(r){
  document.getElementById('connection_type').value = 'customer';
  document.getElementById('customer_id').value = r.id || '';
  document.getElementById('lead_id').value = '';
  document.getElementById('customerName').value = r.company || '';
  const contact = r.contact_name || '';
  const contactInput = document.querySelector('input[name="contact_name"]');
  if (contact && contactInput && !contactInput.value) contactInput.value = contact;
  bootstrap.Modal.getInstance(document.getElementById('connectionModal'))?.hide();
}

function selectLead(r){
  document.getElementById('connection_type').value = 'lead';
  document.getElementById('customer_id').value = '';
  document.getElementById('lead_id').value = r.id || '';
  document.getElementById('customerName').value = (r.business_name||'') + ' (Lead)';
  const contact = r.contact_person || '';
  const contactInput = document.querySelector('input[name="contact_name"]');
  if (contact && contactInput && !contactInput.value) contactInput.value = contact;
  bootstrap.Modal.getInstance(document.getElementById('connectionModal'))?.hide();
}

const loadCustomers = debounce(async()=>{ renderCustomers(await fetchCustomers(searchCustomers.value.trim())); }, 250);
const loadLeads = debounce(async()=>{ renderLeads(await fetchLeads(searchLeads.value.trim())); }, 250);

document.getElementById('connectionModal').addEventListener('shown.bs.modal', async ()=>{
  // Initial loads
  searchCustomers.value = '';
  searchLeads.value = '';
  renderCustomers(await fetchCustomers(''));
  renderLeads(await fetchLeads(''));
  searchCustomers.focus();
});

searchCustomers.addEventListener('input', loadCustomers);
searchLeads.addEventListener('input', loadLeads);

// --- SalesConfig Terms integration (order-local management) ---
const termsContainer = document.getElementById('termsContainer');
const newTermText = document.getElementById('newTermText');
const btnAddTerm = document.getElementById('btnAddTerm');

async function listTerms(){
  const url = new URL(window.location.origin + '/');
  url.searchParams.set('action','salesConfig');
  url.searchParams.set('subaction','listTerms');
  const res = await fetch(url.toString());
  if(!res.ok) return [];
  return await res.json();
}

function makeTermRow(text, checked=true){
  const wrap = document.createElement('div');
  wrap.className = 'd-flex align-items-start gap-2';

  const check = document.createElement('input');
  check.type = 'checkbox';
  check.className = 'form-check-input mt-2';
  check.checked = !!checked;
  check.name = 'tnc[]';
  check.value = text;

  const textView = document.createElement('div');
  textView.className = 'flex-grow-1 form-control-plaintext py-1';
  textView.textContent = text;

  const textEdit = document.createElement('input');
  textEdit.type = 'text';
  textEdit.className = 'form-control d-none';
  textEdit.value = text;

  const actions = document.createElement('div');
  actions.className = 'btn-group btn-group-sm';

  const btnEdit = document.createElement('button');
  btnEdit.type = 'button';
  btnEdit.className = 'btn btn-outline-secondary';
  btnEdit.innerHTML = '<i class="bi bi-pencil"></i>';
  btnEdit.addEventListener('click', ()=>{
    textView.classList.add('d-none');
    textEdit.classList.remove('d-none');
    textEdit.focus();
  });

  const btnSave = document.createElement('button');
  btnSave.type = 'button';
  btnSave.className = 'btn btn-outline-primary';
  btnSave.innerHTML = '<i class="bi bi-check2"></i>';
  btnSave.addEventListener('click', ()=>{
    const val = textEdit.value.trim();
    textView.textContent = val;
    check.value = val;
    textEdit.classList.add('d-none');
    textView.classList.remove('d-none');
  });

  const btnCancel = document.createElement('button');
  btnCancel.type = 'button';
  btnCancel.className = 'btn btn-outline-secondary';
  btnCancel.innerHTML = '<i class="bi bi-x"></i>';
  btnCancel.addEventListener('click', ()=>{
    textEdit.value = check.value;
    textEdit.classList.add('d-none');
    textView.classList.remove('d-none');
  });

  const btnDelete = document.createElement('button');
  btnDelete.type = 'button';
  btnDelete.className = 'btn btn-outline-danger';
  btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
  btnDelete.addEventListener('click', ()=>{
    wrap.remove();
  });

  actions.append(btnEdit, btnSave, btnCancel, btnDelete);

  const body = document.createElement('div');
  body.className = 'flex-grow-1';
  body.append(textView, textEdit);

  wrap.append(check, body, actions);
  return wrap;
}

function renderTerms(rows){
  termsContainer.innerHTML = '';
  (rows||[]).forEach(row => {
    // Include active terms by default; others available unchecked
    const el = makeTermRow(row.text || '', !!row.is_active);
    termsContainer.appendChild(el);
  });
}

async function loadTerms(){
  const rows = await listTerms();
  renderTerms(rows);
}

btnAddTerm.addEventListener('click', ()=>{
  const text = newTermText.value.trim();
  if (!text) return;
  termsContainer.appendChild(makeTermRow(text, true));
  newTermText.value = '';
});

// Load terms on page ready (as templates only)
document.addEventListener('DOMContentLoaded', loadTerms);
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
