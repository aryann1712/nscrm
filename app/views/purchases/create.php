<?php ob_start(); ?>

<form id="supplierInvoiceForm" method="POST" action="/?action=purchases&subaction=store" enctype="multipart/form-data">
    <input type="hidden" name="items_json" id="items_json" value="[]"/>
    <input type="hidden" name="terms_json" id="terms_json" value="[]"/>
    <input type="hidden" name="taxable_total" id="taxable_total_input" value="0"/>
    <input type="hidden" name="gst_total" id="gst_total_input" value="0"/>
    <input type="hidden" name="total_amount" id="total_amount_input" value="0"/>

    <div class="d-flex justify-content-between mb-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/?action=purchases">Purchases</a></li>
                <li class="breadcrumb-item active">Create Supplier Invoice</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="/?action=purchases" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-check"></i> Save</button>
        </div>
    </div>

    <!-- Basic Information & Document Details -->
    <div class="card mb-3">
        <div class="card-header">Basic Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Supplier</label>
                    <div class="input-group">
                        <input class="form-control" name="supplier" id="supplier_input" value="<?= htmlspecialchars($prefill['supplier'] ?? '') ?>" required/>
                        <button class="btn btn-outline-secondary" type="button" title="Pick Supplier" onclick="openContactPicker()"><i class="bi bi-search"></i></button>
                        <button class="btn btn-outline-success" type="button" title="Add Supplier" onclick="openAddCustomerInline()"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice No.</label>
                    <input class="form-control" name="invoice_no" value="<?= (int)$nextNo ?>" required/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference</label>
                    <input class="form-control" name="reference" value="<?= htmlspecialchars($prefill['reference'] ?? '') ?>" placeholder="Enter reference"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" name="invoice_date" value="<?= date('Y-m-d') ?>" required/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" name="due_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Executive</label>
                    <select class="form-select" name="executive">
                        <option value="">— Select Executive —</option>
                        <?php foreach (($users ?? []) as $u): $label = trim(($u['name'] ?? '').(($u['email'] ?? '')? ' — '.$u['email'] : '')); ?>
                            <option value="<?= htmlspecialchars($u['name'] ?? '') ?>"><?= htmlspecialchars($label ?: 'User') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Party Details -->
    <div class="card mb-3">
        <div class="card-header">Party Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input class="form-control" name="contact_person" value="<?= htmlspecialchars($prefill['contact_person'] ?? '') ?>"/>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Party Address</label>
                    <div class="input-group mb-1">
                      <select class="form-select" id="address_select" title="Select address" style="max-height:200px; overflow:auto"></select>
                      <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshAddressOptions()"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                    <textarea class="form-control" rows="2" name="party_address" id="party_address"></textarea>
                </div>
                <div class="col-md-12">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" id="same_shipping">
                        <label class="form-check-label" for="same_shipping">Shipping address same as party address</label>
                    </div>
                    <div id="shipping_container">
                      <label class="form-label">Shipping Address</label>
                      <div class="input-group mb-1">
                        <select class="form-select" id="address_select_shipping" title="Select shipping address" style="max-height:200px; overflow:auto"></select>
                        <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshAddressOptions()"><i class="bi bi-arrow-repeat"></i></button>
                      </div>
                      <textarea class="form-control" rows="2" name="shipping_address" id="shipping_address"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item List -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Item List</span>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()"><i class="bi bi-plus"></i> Add Item</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">No.</th>
                            <th>Item & Description</th>
                            <th style="width:120px">HSN/SAC</th>
                            <th style="width:100px">Qty</th>
                            <th style="width:90px">Unit</th>
                            <th style="width:120px">Rate (₹)</th>
                            <th style="width:120px">Discount (₹)</th>
                            <th style="width:120px">Taxable (₹)</th>
                            <th style="width:100px">GST %</th>
                            <th style="width:120px">Amt (₹)</th>
                            <th style="width:80px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <div class="d-inline-block text-start">
                <div>Total Taxable: ₹ <span id="total">0.00</span></div>
                <!-- Freight Charge removed as requested -->
                <div>Overall Discount (₹): <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" name="overall_discount" id="overall_discount" value="0" oninput="recalc()"></div>
                <div>Subtotal (before GST): ₹ <span id="subtotal_before_gst">0.00</span></div>
                <div class="mb-1">GST %: <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" name="overall_gst_pct" id="overall_gst_pct" value="0" oninput="recalc()"></div>
                <div>GST Total (Overall + Per-item): ₹ <span id="gst_total">0.00</span></div>
                <div class="fw-bold">Grand Total: ₹ <span id="grand_total">0.00</span></div>
                <input type="hidden" name="grand_total" id="grand_total_input" value="0"/>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
            <span>Terms & Conditions</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearTerms()"><i class="bi bi-trash"></i> Clear All</button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTerm()"><i class="bi bi-plus"></i> Add Term / Condition</button>
            </div>
        </div>
        <div class="card-body" id="termsBox"></div>
    </div>

    <!-- Notes, Bank Details and Attachment -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Notes</div>
                <div class="card-body">
                    <textarea class="form-control" rows="4" name="notes"></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bank Details</div>
                <div class="card-body">
                    <input type="hidden" name="bank_account_id" id="bank_account_id" value="">
                    <select class="form-select" id="bank_select"><option value="">— Select —</option></select>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">Attachment (optional)</div>
                <div class="card-body">
                    <input type="file" class="form-control" name="attachment" accept=".pdf,image/*">
                    <small class="text-muted">Max 10 MB. Allowed: PDF, images</small>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Contact Picker Modal -->
<div class="modal fade" id="contactPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="btn-group" role="group" aria-label="Type">
            <input type="radio" class="btn-check" name="cp_type" id="cp_type_supplier" autocomplete="off" checked>
            <label class="btn btn-outline-primary" for="cp_type_supplier">Supplier</label>
          </div>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="cp_search" placeholder="Search by name, email, mobile, city, or state...">
          </div>
        </div>
        <div id="cp_results" class="list-group" style="max-height: 360px; overflow:auto;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-success me-auto" onclick="openAddCustomerInline()"><i class="bi bi-plus"></i> Add Supplier</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<script>
const pickerItems = <?= json_encode($pickerItems ?? []) ?>;

// ================= Contact Picker =================
let cpModal, cpType = 'supplier', cpDebounce;
function openContactPicker(){
  if (!cpModal) { cpModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('contactPickerModal')); }
  // defaults
  document.getElementById('cp_type_supplier').checked = true;
  document.getElementById('cp_search').value = '';
  document.getElementById('cp_results').innerHTML = '';
  cpModal.show();
  // initial load
  setTimeout(()=> searchContacts(), 50);
}

// Inline Add Customer modal
function openAddCustomerInline(){
  const el = document.getElementById('addCustomerInlineModal');
  const m = bootstrap.Modal.getOrCreateInstance(el);
  // Reset form
  document.getElementById('addCustomerForm').reset();
  document.getElementById('addCustomerId').value = '';
  document.getElementById('addCustomerTypeHidden').value = 'supplier';
  // Reset pending addresses
  addCustomerPendingAddresses = [];
  updateAddCustomerPendingBadge();
  // Load cities if not already loaded
  populateAddCustomerCities();
  // Ensure submit handler is bound
  bindAddCustomerFormOnce();
  m.show();
}

// Populate city dropdown for add customer form
function populateAddCustomerCities() {
  const citySel = document.getElementById('addCustomerCity');
  if (!citySel || citySel.options.length > 1) return; // Already populated
  
  fetch('/?action=salesConfig&subaction=listCities')
    .then(r => r.json())
    .then(data => {
      citySel.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'Select City';
      citySel.appendChild(opt);
      (data || []).forEach(c => {
        const option = document.createElement('option');
        option.value = c.name;
        option.textContent = c.name;
        citySel.appendChild(option);
      });
    })
    .catch(() => {});
}

// Type buttons handler for add customer
document.addEventListener('DOMContentLoaded', () => {
  const addCustomerTypeButtons = document.getElementById('addCustomerTypeButtons');
  if (addCustomerTypeButtons) {
    addCustomerTypeButtons.addEventListener('click', function(e){
      if (!e.target.classList.contains('type-btn')) return;
      setAddCustomerType(e.target.dataset.type);
    });
  }
});

function setAddCustomerType(t){
  const buttons = document.querySelectorAll('#addCustomerTypeButtons .type-btn');
  buttons.forEach(btn => btn.classList.remove('active'));
  const btn = document.querySelector(`#addCustomerTypeButtons .type-btn[data-type="${t}"]`);
  if (btn) btn.classList.add('active');
  const hidden = document.getElementById('addCustomerTypeHidden');
  if (hidden) hidden.value = t;
}
function currentCpType(){ return 'supplier'; }
async function searchContacts(){
  const q = document.getElementById('cp_search').value.trim();
  const type = 'supplier';
  try {
    const res = await fetch(`/?action=purchases&subaction=listContacts&type=${encodeURIComponent(type)}&q=${encodeURIComponent(q)}`);
    const rows = await res.json();
    const box = document.getElementById('cp_results');
    if (!Array.isArray(rows)) { box.innerHTML = '<div class="text-muted px-2 py-1">No results</div>'; return; }
    box.innerHTML = rows.map(r=>
      `<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="pickContact(${r.id}, '${type}')">
        <span>
          <div class="fw-semibold">${escapeHtml(r.label||'')}</div>
          <small class="text-muted">${escapeHtml(r.sublabel||'')} ${r.city? '• '+escapeHtml(r.city): ''}</small>
        </span>
        <span class="badge bg-light text-dark border">Supplier</span>
      </button>`
    ).join('');
  } catch(e){
    document.getElementById('cp_results').innerHTML = '<div class="text-danger px-2 py-1">Failed to load</div>';
  }
}
async function pickContact(id, type){
  try {
    const res = await fetch(`/?action=purchases&subaction=getContact&type=${encodeURIComponent(type)}&id=${id}`);
    const data = await res.json();
    if (data && !data.error){
      const supp = document.getElementById('supplier_input'); if (supp) supp.value = data.supplier || '';
      const cp = document.querySelector('input[name="contact_person"]'); if (cp) cp.value = data.contact_person || '';
      const pa = document.getElementById('party_address'); if (pa) pa.value = data.party_address || '';
      // Populate address selectors if provided
      if (Array.isArray(data.addresses)) {
        const selParty = document.getElementById('address_select');
        const selShip = document.getElementById('address_select_shipping');
        const partyAddr = document.getElementById('party_address');
        const shipAddr = document.getElementById('shipping_address');
        const sameShipping = document.getElementById('same_shipping');
        
        const optsHtml = '<option value="">— Select —</option>' + data.addresses.map(a=>`<option value="${(a.formatted||'').replaceAll('"','&quot;')}">${(a.title||'').trim()||'Address'}</option>`).join('');
        
        if (selParty && partyAddr) {
          selParty.innerHTML = optsHtml;
          selParty.onchange = ()=>{
            if (selParty.value && partyAddr) {
              partyAddr.value = selParty.value;
              if (sameShipping && sameShipping.checked && shipAddr) {
                shipAddr.value = selParty.value;
              }
            }
          };
        }
        if (selShip && shipAddr) {
          selShip.innerHTML = optsHtml;
          selShip.onchange = ()=>{
            if (selShip.value && shipAddr) {
              shipAddr.value = selShip.value;
            }
          };
        }
      }
      // Do not auto-fill shipping address; user can opt-in with the checkbox
      if (cpModal) cpModal.hide();
    }
  } catch(e){}
}
document.addEventListener('DOMContentLoaded', ()=>{
  const inp = document.getElementById('cp_search');
  if (inp){ inp.addEventListener('input', ()=>{ clearTimeout(cpDebounce); cpDebounce = setTimeout(searchContacts, 250); }); }
  // Auto-fill when typing a company name directly in Supplier field
  const suppInput = document.getElementById('supplier_input');
  if (suppInput){
    suppInput.addEventListener('blur', tryAutoFillFromSupplierName);
    suppInput.addEventListener('keydown', (e)=>{ if (e.key === 'Enter') { e.preventDefault(); tryAutoFillFromSupplierName(); } });
  }
});

// Basic escape util shared above
function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[s])); }

// Try to auto-fill based on typed company name (Suppliers only)
async function tryAutoFillFromSupplierName(){
  const val = (document.getElementById('supplier_input')?.value || '').trim();
  if (!val) return;
  try {
    const res = await fetch(`/?action=purchases&subaction=listContacts&type=supplier&q=${encodeURIComponent(val)}`);
    const rows = await res.json();
    if (!Array.isArray(rows) || rows.length === 0) return;
    // Prefer exact (case-insensitive) match, otherwise single result
    const exact = rows.find(r=> String(r.label||'').toLowerCase() === val.toLowerCase());
    const chosen = exact || (rows.length === 1 ? rows[0] : null);
    if (!chosen) return;
    // Fetch details for chosen id
    const dres = await fetch(`/?action=purchases&subaction=getContact&type=supplier&id=${chosen.id}`);
  const data = await dres.json();
  if (data && !data.error){
    const cp = document.querySelector('input[name="contact_person"]'); if (cp && !cp.value) cp.value = data.contact_person || '';
    const pa = document.getElementById('party_address'); if (pa && !pa.value) pa.value = data.party_address || '';
  }
  } catch(e) { /* ignore */ }
}

function addItemRow(pref = {}) {
  const tbody = document.querySelector('#itemsTable tbody');
  const rowIndex = tbody.children.length + 1;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="text-center">${rowIndex}</td>
    <td>
      <input class="form-control form-control-sm item-name" placeholder="Search item..." list="itemList${rowIndex}">
      <datalist id="itemList${rowIndex}">${pickerItems.map(i=>`<option value="${i.name} (${i.code})" data-id="${i.id}" data-hsn="${i.hsn_sac}" data-unit="${i.unit}" data-rate="${i.rate}" data-gst="${i.gst}"></option>`).join('')}</datalist>
      <small class="text-muted d-block"><input class="form-control form-control-sm item-desc" placeholder="Description"></small>
    </td>
    <td><input class="form-control form-control-sm hsn" value="${pref.hsn_sac||''}"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm qty" value="${pref.qty||1}" oninput="recalc()"></td>
    <td><input class="form-control form-control-sm unit" value="${pref.unit||'no.s'}"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm rate" value="${pref.rate||0}" oninput="recalc()"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm discount" value="${pref.discount||0}" oninput="recalc()"></td>
    <td class="text-end taxable">0.00</td>
    <td>
      <div class="input-group input-group-sm">
        <input type="number" min="0" step="0.01" class="form-control form-control-sm gst" value="${pref.gst||0}" oninput="recalc()">
        <span class="input-group-text">
          <input type="checkbox" class="form-check-input gst-included" ${pref.gst_included? 'checked':''} title="GST included in rate" onchange="recalc()">
        </span>
      </div>
      <small class="text-muted">Incl. in rate</small>
    </td>
    <td class="text-end amount">0.00</td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalc();"><i class="bi bi-trash"></i></button></td>
  `;
  tbody.appendChild(tr);
  // Auto fill from picker when choosing
  const nameInput = tr.querySelector('.item-name');
  nameInput.addEventListener('change', () => {
    const option = Array.from(document.querySelectorAll(`#itemList${rowIndex} option`)).find(o => o.value === nameInput.value);
    if (option) {
      tr.querySelector('.hsn').value = option.dataset.hsn || '';
      tr.querySelector('.unit').value = option.dataset.unit || 'no.s';
      tr.querySelector('.rate').value = Number(option.dataset.rate||0).toFixed(2);
      tr.querySelector('.gst').value = Number(option.dataset.gst||0).toFixed(2);
      recalc();
    }
  });
  recalc();
}

function recalc() {
  let totalTaxable = 0;
  const rows = document.querySelectorAll('#itemsTable tbody tr');
  const items = [];
  let perItemGstTotal = 0;
  let hasPerItemDiscount = false;
  let hasPerItemGst = false;
  rows.forEach((tr, idx) => {
    const qty = parseFloat(tr.querySelector('.qty').value)||0;
    const rate = parseFloat(tr.querySelector('.rate').value)||0;
    const disc = parseFloat(tr.querySelector('.discount').value)||0;
    const gst = parseFloat(tr.querySelector('.gst').value)||0;
    const gstIncluded = !!(tr.querySelector('.gst-included') && tr.querySelector('.gst-included').checked);
    const lineGross = Math.max(qty*rate - disc, 0);
    let taxable = 0, thisItemGst = 0, amountForDisplay = 0;
    if (gstIncluded && gst > 0) {
      taxable = lineGross / (1 + (gst/100));
      thisItemGst = lineGross - taxable;
      amountForDisplay = lineGross; // already inclusive
    } else {
      taxable = lineGross;
      thisItemGst = taxable * (gst/100);
      amountForDisplay = taxable + thisItemGst;
    }
    tr.querySelector('.taxable').textContent = taxable.toFixed(2);
    tr.querySelector('.amount').textContent = amountForDisplay.toFixed(2);
    const amount = taxable; // taxable used for totals base
    totalTaxable += taxable;
    perItemGstTotal += thisItemGst;
    if (disc > 0) hasPerItemDiscount = true;
    if (gst > 0) hasPerItemGst = true;
    items.push({
      name: tr.querySelector('.item-name').value,
      description: tr.querySelector('.item-desc').value,
      hsn_sac: tr.querySelector('.hsn').value,
      qty, unit: tr.querySelector('.unit').value,
      rate, discount: disc, gst, gst_included: gstIncluded?1:0, taxable, amount
    });
  });
  document.getElementById('total').textContent = totalTaxable.toFixed(2);
  const extra = 0; // Freight removed; treat as 0
  const overallDiscEl = document.getElementById('overall_discount');
  const overallGstEl = document.getElementById('overall_gst_pct');
  const overallDiscInput = parseFloat(overallDiscEl.value)||0;
  const overallGstPctInput = parseFloat(overallGstEl.value)||0;
  const blockOverall = (hasPerItemDiscount || hasPerItemGst);
  // disable/enable inputs in UI
  overallDiscEl.disabled = blockOverall;
  overallGstEl.disabled = blockOverall;
  // ignore both when blocked
  const effectiveOverallDiscount = blockOverall ? 0 : overallDiscInput;
  const effectiveOverallGstPct = blockOverall ? 0 : overallGstPctInput;
  // Subtotal before GST = (Total Taxable + 0 Freight) - Overall Discount
  const baseBeforeDiscount = Math.max(totalTaxable + extra, 0);
  const gstBase = Math.max(baseBeforeDiscount - effectiveOverallDiscount, 0);
  const overallGst = gstBase * (effectiveOverallGstPct/100);
  const gstTotal = overallGst + perItemGstTotal;
  const grand = Math.max(gstBase + gstTotal, 0);
  const subSpan = document.getElementById('subtotal_before_gst'); if (subSpan) subSpan.textContent = gstBase.toFixed(2);
  const gstSpan = document.getElementById('gst_total'); if (gstSpan) gstSpan.textContent = gstTotal.toFixed(2);
  document.getElementById('grand_total').textContent = grand.toFixed(2);
  document.getElementById('grand_total_input').value = grand.toFixed(2);
  document.getElementById('taxable_total_input').value = gstBase.toFixed(2);
  document.getElementById('gst_total_input').value = gstTotal.toFixed(2);
  document.getElementById('total_amount_input').value = grand.toFixed(2);
  document.getElementById('items_json').value = JSON.stringify(items);
}

function addTerm(text='') {
  const box = document.getElementById('termsBox');
  const div = document.createElement('div');
  div.className = 'input-group mb-2';
  div.innerHTML = `
    <input class="form-control term-input" value="${text}"/>
    <button type="button" class="btn btn-outline-secondary" title="Move Up" onclick="moveTerm(this,-1)"><i class='bi bi-arrow-up'></i></button>
    <button type="button" class="btn btn-outline-secondary" title="Move Down" onclick="moveTerm(this,1)"><i class='bi bi-arrow-down'></i></button>
    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="this.parentElement.remove(); syncTerms();"><i class='bi bi-x'></i></button>`;
  box.appendChild(div);
  syncTerms();
}
function syncTerms() {
  const terms = Array.from(document.querySelectorAll('.term-input')).map(i=>i.value).filter(t=>t.trim().length>0);
  document.getElementById('terms_json').value = JSON.stringify(terms);
}

function moveTerm(btn, dir){
  const row = btn.parentElement;
  if (!row) return;
  if (dir < 0 && row.previousElementSibling) {
    row.parentElement.insertBefore(row, row.previousElementSibling);
  } else if (dir > 0 && row.nextElementSibling) {
    row.parentElement.insertBefore(row.nextElementSibling, row);
  }
  syncTerms();
}

function clearTerms(){
  document.getElementById('termsBox').innerHTML = '';
  syncTerms();
}

// Shipping: same as party behavior + hide/show container
document.addEventListener('DOMContentLoaded', () => {
  const party = document.getElementById('party_address');
  const ship = document.getElementById('shipping_address');
  const same = document.getElementById('same_shipping');
  if (same && party && ship) {
    const apply = () => {
      if (same.checked && ship && party) {
        ship.value = party.value || '';
        ship.setAttribute('readonly','readonly');
        const cont = document.getElementById('shipping_container'); 
        if (cont) cont.style.display = 'none';
      } else {
        if (ship) ship.removeAttribute('readonly');
        const cont = document.getElementById('shipping_container'); 
        if (cont) cont.style.display = '';
      }
    };
    same.addEventListener('change', apply);
    if (party) {
      party.addEventListener('input', () => { 
        if (same && same.checked && ship && party) { 
          ship.value = party.value || '';
        }
      });
    }
    apply();
  }
  // Ensure terms sync before submit
  const form = document.getElementById('supplierInvoiceForm');
  if (form) {
    form.addEventListener('submit', () => { if (typeof syncTerms === 'function') syncTerms(); });
  }
});

// Load active default terms from master
async function loadDefaultTerms(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listTerms');
    const all = await res.json();
    const active = all.filter(t=>String(t.is_active)==='1').sort((a,b)=> (a.display_order||0)-(b.display_order||0));
    active.forEach(t=> addTerm(t.text||''));
  } catch(e) { /* ignore */ }
}

// Load bank accounts for selection
async function loadBanks(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listBanks');
    const banks = await res.json();
    const sel = document.getElementById('bank_select');
    sel.innerHTML = '<option value="">— Select —</option>' + banks.map(b=>`<option value="${b.id}" ${b.is_default? 'selected':''}>${b.bank_name} — ${b.account_no}</option>`).join('');
    const def = banks.find(b=>b.is_default==1);
    document.getElementById('bank_account_id').value = def? def.id : '';
    sel.addEventListener('change', ()=>{
      document.getElementById('bank_account_id').value = sel.value;
    });
  } catch(e) { /* ignore */ }
}

// Start with one empty row and load defaults
addItemRow();
loadDefaultTerms();
loadBanks();

function refreshAddressOptions(){ /* no-op placeholder, options are set on pick */ }

// Address management for add customer modal
let addCustomerPendingAddresses = [];
let addCustomerCurrentId = null;

// Manage Addresses & GST button handler
document.addEventListener('DOMContentLoaded', () => {
  const manageAddrBtn = document.getElementById('btnAddCustomerManageAddresses');
  if (manageAddrBtn) {
    manageAddrBtn.addEventListener('click', () => {
      openAddCustomerAddressModal();
    });
  }

  // Address form submission
  const addCustomerAddressForm = document.getElementById('addCustomerAddressForm');
  if (addCustomerAddressForm) {
    addCustomerAddressForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const fd = new FormData(e.target);
      const staged = Object.fromEntries(fd.entries());
      delete staged['id'];
      staged['customer_id'] = '';
      addCustomerPendingAddresses.push(staged);
      bootstrap.Modal.getInstance(document.getElementById('addCustomerAddressModal')).hide();
      updateAddCustomerPendingBadge();
    });
  }
});

function openAddCustomerAddressModal() {
  document.getElementById('addCustomerAddressModalLabel').textContent = 'Add Address';
  document.getElementById('addCustomerAddressForm').reset();
  document.getElementById('addCustomerAddrId').value = '';
  document.getElementById('addCustomerAddrCustomerId').value = '';
  populateAddCustomerAddressCities();
  const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('addCustomerAddressModal'));
  m.show();
}

function resetAddCustomerAddressForm() {
  document.getElementById('addCustomerAddressForm').reset();
  document.getElementById('addCustomerAddrId').value = '';
  document.getElementById('addCustomerAddrCustomerId').value = '';
}

function populateAddCustomerAddressCities() {
  const citySel = document.getElementById('addCustomerAddrCity');
  if (!citySel || citySel.options.length > 1) return;
  
  fetch('/?action=salesConfig&subaction=listCities')
    .then(r => r.json())
    .then(rows => {
      citySel.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'Select City';
      citySel.appendChild(opt);
      (rows || []).forEach(c => {
        const option = document.createElement('option');
        option.value = c.name;
        option.textContent = c.name;
        citySel.appendChild(option);
      });
    })
    .catch(() => {});
}

function updateAddCustomerPendingBadge() {
  const btn = document.getElementById('btnAddCustomerManageAddresses');
  if (!btn) return;
  const count = addCustomerPendingAddresses.length;
  const existing = btn.querySelector('.pending-badge');
  if (count > 0) {
    if (!existing) {
      const span = document.createElement('span');
      span.className = 'badge bg-info ms-2 pending-badge';
      span.textContent = String(count);
      btn.appendChild(span);
    } else {
      existing.textContent = String(count);
    }
  } else if (existing) {
    existing.remove();
  }
}

// Bind Add Customer form submit (ensure form exists before binding)
function bindAddCustomerFormOnce(){
  const form = document.getElementById('addCustomerForm');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';
  form.addEventListener('submit', async function(e){
  e.preventDefault();
  const form = e.target;
  
  // Compose contact_name from parts
  const title = document.getElementById('addCustomerTitle').value.trim();
  const first = document.getElementById('addCustomerFirstName').value.trim();
  const last = document.getElementById('addCustomerLastName').value.trim();
  const composed = (title + ' ' + first + ' ' + last).trim();
  document.getElementById('addCustomerContactNameHidden').value = composed;

  // Ensure type from buttons is submitted
  const selectedType = (document.querySelector('#addCustomerTypeButtons .type-btn.active')?.dataset.type) || 'customer';
  document.getElementById('addCustomerTypeHidden').value = selectedType;

  const formData = new URLSearchParams(new FormData(form));

  // Normalize checkbox for backend expected field name (active vs is_active)
  if (!formData.has('is_active')) formData.append('is_active', '0');

  try {
    const resp = await fetch('/?action=customers&subaction=create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData.toString()
    });
    
    const result = await resp.json();
    
    if (result.error) {
      alert('Error: ' + result.error);
      return;
    }
    
    const newId = result.id;
    
    // If there are pending addresses, create them now against the customer id
    if (addCustomerPendingAddresses.length > 0 && newId) {
      let failed = 0;
      let lastDetail = '';
      
      for (const addr of addCustomerPendingAddresses) {
        const params = new URLSearchParams({ ...addr, customer_id: newId });
        try {
          const r = await fetch('/?action=customers&subaction=createAddress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
          });
          const respA = await r.json();
          if (respA && respA.error) {
            failed++;
            lastDetail = respA.detail || '';
          }
        } catch (e) {
          failed++;
          lastDetail = e.message || String(e);
        }
      }
      
      addCustomerPendingAddresses = [];
      updateAddCustomerPendingBadge();
      
      if (failed > 0) {
        alert('Customer created but some addresses failed to save (' + failed + '). ' + (lastDetail ? ('Details: ' + lastDetail) : ''));
      }
    }
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('addCustomerInlineModal')).hide();
    
    // Auto-select the newly created supplier
    const companyName = document.getElementById('addCustomerCompany').value;
    const supplierInput = document.getElementById('supplier_input');
    if (supplierInput) {
      supplierInput.value = companyName;
      
      // Optionally trigger auto-fill to get full details
      setTimeout(() => {
        tryAutoFillFromSupplierName();
      }, 100);
    }
  } catch (err) {
    alert('Error: ' + err.message);
  }
  });
}

// Bind on DOM ready
document.addEventListener('DOMContentLoaded', bindAddCustomerFormOnce);
</script>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerInlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enter Connection</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('addCustomerForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }))"><i class="bi bi-check2"></i> Save</button>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <form id="addCustomerForm" onsubmit="return false;">
        <div class="modal-body">
          <input type="hidden" name="id" id="addCustomerId">
          <input type="hidden" name="contact_name" id="addCustomerContactNameHidden">
          <input type="hidden" name="type" id="addCustomerTypeHidden" value="customer">
          
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Business <span class="text-danger">*</span></label>
              <input type="text" name="company" id="addCustomerCompany" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col-2 col-md-2">
                  <select class="form-select" id="addCustomerTitle" name="title">
                    <option value="Mr.">Mr.</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Mrs.">Mrs.</option>
                    <option value="Dr.">Dr.</option>
                  </select>
                </div>
                <div class="col-5 col-md-5">
                  <input type="text" class="form-control" id="addCustomerFirstName" name="first_name" placeholder="First Name">
                </div>
                <div class="col-5 col-md-5">
                  <input type="text" class="form-control" id="addCustomerLastName" name="last_name" placeholder="Last Name">
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mobile</label>
              <input type="text" name="contact_phone" id="addCustomerPhone" class="form-control" placeholder="e.g., +91 98765 43210">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="contact_email" id="addCustomerEmail" class="form-control" placeholder="name@example.com">
            </div>

            <div class="col-12">
              <div class="d-flex gap-2 flex-wrap mt-2" id="addCustomerTypeButtons">
                <button class="btn btn-outline-warning type-btn active" data-type="customer" type="button">Customer</button>
                <button class="btn btn-outline-primary type-btn" data-type="supplier" type="button">Supplier</button>
                <button class="btn btn-outline-success type-btn" data-type="neighbour" type="button">Neighbour</button>
                <button class="btn btn-outline-secondary type-btn" data-type="friend" type="button">Friend</button>
              </div>
            </div>

            <div class="col-12">
              <a class="d-inline-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="collapse" href="#addCustomerMoreDetails" role="button" aria-expanded="false" aria-controls="addCustomerMoreDetails">
                <i class="bi bi-chevron-down"></i> Enter More Details
              </a>
              <div class="collapse mt-3" id="addCustomerMoreDetails">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text" name="website" id="addCustomerWebsite" class="form-control" placeholder="https://...">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Industry & Segment</label>
                    <input type="text" name="industry_segment" id="addCustomerIndustrySegment" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" id="addCustomerCountry" class="form-control" value="India">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" id="addCustomerState" class="form-control" placeholder="Select/Type">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">City</label>
                    <select name="city" id="addCustomerCity" class="form-select"></select>
                  </div>

                  <div class="col-12">
                    <button type="button" id="btnAddCustomerManageAddresses" class="btn btn-warning"><i class="bi bi-geo-alt"></i> Manage Addresses & GST</button>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Relation</label>
                    <input type="text" name="relation" id="addCustomerRelation" class="form-control" placeholder="e.g., Client, Dealer">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Executive</label>
                    <input type="text" name="executive" id="addCustomerExecutive" class="form-control" placeholder="Owner/Executive">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Last Talk</label>
                    <input type="date" name="last_talk" id="addCustomerLastTalk" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Next Action</label>
                    <input type="date" name="next_action" id="addCustomerNextAction" class="form-control">
                  </div>

                  <div class="col-12 d-flex align-items-center">
                    <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" value="1" id="addCustomerIsActive" name="is_active" checked>
                      <label class="form-check-label" for="addCustomerIsActive"> Active</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" onclick="document.getElementById('addCustomerForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }))"><i class="bi bi-check2"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Address Modal for Add Customer -->
<div class="modal fade" id="addCustomerAddressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCustomerAddressModalLabel">Add Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addCustomerAddressForm">
        <div class="modal-body">
          <input type="hidden" id="addCustomerAddrId" name="id">
          <input type="hidden" id="addCustomerAddrCustomerId" name="customer_id">

          <div class="mb-2">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="addCustomerAddrTitle" name="title" placeholder="Head office, Warehouse etc.">
          </div>

          <div class="mb-2">
            <label class="form-label">Address</label>
            <input type="text" class="form-control mb-2" id="addCustomerAddrLine1" name="line1" placeholder="Line 1">
            <input type="text" class="form-control" id="addCustomerAddrLine2" name="line2" placeholder="Line 2">
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">City</label>
              <select class="form-select" id="addCustomerAddrCity" name="city"></select>
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">Country</label>
              <select class="form-select" id="addCustomerAddrCountry" name="country">
                <option value="India" selected>India</option>
              </select>
            </div>
            <div class="col">
              <label class="form-label">State</label>
              <input type="text" class="form-control" id="addCustomerAddrState" name="state" placeholder="Select">
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label">Pincode</label>
              <input type="text" class="form-control" id="addCustomerAddrPincode" name="pincode">
            </div>
            <div class="col">
              <label class="form-label">GST</label>
              <input type="text" class="form-control" id="addCustomerAddrGSTIN" name="gstin">
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Extra Field (e.g. Mobile: 9000012345)</label>
            <div class="input-group">
              <input type="text" class="form-control" id="addCustomerAddrExtraKey" name="extra_key" placeholder="Key">
              <span class="input-group-text">:</span>
              <input type="text" class="form-control" id="addCustomerAddrExtraValue" name="extra_value" placeholder="Value">
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

