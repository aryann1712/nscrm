<?php ob_start(); ?>

<?php
// Ensure $q is available and decode JSON safely
$itemsData = [];
$termsData = [];
try { $itemsData = json_decode($q['items_json'] ?? '[]', true) ?: []; } catch (Throwable $e) { $itemsData = []; }
try { $termsData = json_decode($q['terms_json'] ?? '[]', true) ?: []; } catch (Throwable $e) { $termsData = []; }
?>

<form id="quotationForm" method="POST" action="/?action=quotations&subaction=update&id=<?= (int)$q['id'] ?>" enctype="multipart/form-data">
    <input type="hidden" name="items_json" id="items_json" value="[]"/>
    <input type="hidden" name="terms_json" id="terms_json" value="[]"/>

    <div class="d-flex justify-content-between mb-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/?action=quotations">Quotations</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="/?action=quotations" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-check"></i> Update</button>
        </div>
    </div>

    

    <!-- Basic Information & Document Details -->
    <div class="card mb-3">
        <div class="card-header">Basic Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <div class="input-group">
                        <input class="form-control" name="customer" id="customer_input" value="<?= htmlspecialchars($q['customer'] ?? '') ?>" required/>
                        <button class="btn btn-outline-secondary" type="button" title="Pick Customer/Lead" onclick="openContactPicker()"><i class="bi bi-search"></i></button>
                        <!-- <button class="btn btn-outline-success" type="button" title="Add Customer" onclick="openAddCustomerInline()"><i class="bi bi-plus"></i></button> -->
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quotation No.</label>
                    <input class="form-control" name="quote_no" value="<?= (int)($q['quote_no'] ?? 0) ?>" required/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference</label>
                    <input class="form-control" name="reference" value="<?= htmlspecialchars($q['reference'] ?? '') ?>" placeholder="Enter reference"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quotation Date</label>
                    <input type="date" class="form-control" name="issued_on" value="<?= htmlspecialchars($q['issued_on'] ?? date('Y-m-d')) ?>" required/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valid till</label>
                    <input type="date" class="form-control" name="valid_till" value="<?= htmlspecialchars($q['valid_till'] ?? '') ?>"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <?php $t = $q['type'] ?? 'Quotation'; ?>
                        <option value="Quotation" <?= ($t==='Quotation'?'selected':'') ?>>Quotation</option>
                        <option value="Proforma" <?= ($t==='Proforma'?'selected':'') ?>>Proforma</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Executive</label>
                    <select class="form-select" name="executive">
                        <option value="">— Select Executive —</option>
                        <?php $execSel = (string)($q['executive'] ?? ''); foreach (($users ?? []) as $u): $label = trim(($u['name'] ?? '').(($u['email'] ?? '')? ' — '.$u['email'] : '')); $val = (string)($u['name'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= ($val === $execSel ? 'selected' : '') ?>><?= htmlspecialchars($label ?: 'User') ?></option>
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
                    <input class="form-control" name="contact_person" value="<?= htmlspecialchars($q['contact_person'] ?? '') ?>"/>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Party Address</label>
                    <div class="input-group mb-1">
                      <select class="form-select" id="address_select" title="Select address" style="max-height:200px; overflow:auto"></select>
                      <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshAddressOptionsEdit()"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                    <textarea class="form-control" rows="2" name="party_address" id="party_address"><?= htmlspecialchars($q['party_address'] ?? '') ?></textarea>
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
                        <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshAddressOptionsEdit()"><i class="bi bi-arrow-repeat"></i></button>
                      </div>
                      <textarea class="form-control" rows="2" name="shipping_address" id="shipping_address"><?= htmlspecialchars($q['shipping_address'] ?? '') ?></textarea>
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
                <!-- Freight Charge removed in Edit as well -->
                <div>Overall Discount: ₹ <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" name="overall_discount" id="overall_discount" value="<?= htmlspecialchars((string)($q['overall_discount'] ?? '0')) ?>" oninput="recalc()"></div>
                <div>Subtotal (before GST): ₹ <span id="subtotal_before_gst">0.00</span></div>
                <div class="mb-1">Overall GST %: <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" name="overall_gst_pct" id="overall_gst_pct" value="<?= htmlspecialchars((string)($q['overall_gst_pct'] ?? '0')) ?>" oninput="recalc()"></div>
                <div>GST Total: ₹ <span id="gst_total">0.00</span></div>
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
                    <textarea class="form-control" rows="4" name="notes"><?= htmlspecialchars($q['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bank Details</div>
                <div class="card-body">
                    <input type="hidden" name="bank_account_id" id="bank_account_id" value="<?= (int)($q['bank_account_id'] ?? 0) ?>">
                    <select class="form-select" id="bank_select"><option value="">— Select —</option></select>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">Attachment (optional)</div>
                <div class="card-body">
                    <?php if (!empty($q['attachment_path'])): ?>
                        <div class="mb-2">Current: <a href="<?= htmlspecialchars($q['attachment_path']) ?>" target="_blank">View</a></div>
                    <?php endif; ?>
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
        <h5 class="modal-title">Select Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="btn-group" role="group" aria-label="Type">
            <input type="radio" class="btn-check" name="cp_type" id="cp_type_customer" autocomplete="off" checked>
            <label class="btn btn-outline-primary" for="cp_type_customer">Customer</label>
            <input type="radio" class="btn-check" name="cp_type" id="cp_type_lead" autocomplete="off">
            <label class="btn btn-outline-primary" for="cp_type_lead">Lead</label>
          </div>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="cp_search" placeholder="Search by name, email, mobile, city, or state...">
          </div>
        </div>
        <div id="cp_results" class="list-group" style="max-height: 360px; overflow:auto;"></div>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-outline-success me-auto" onclick="openAddCustomerInline()"><i class="bi bi-plus"></i> Add Customer</button> -->
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
const pickerItems = <?= json_encode($pickerItems ?? []) ?>;
const existingItems = <?= json_encode($itemsData) ?>;
const existingTerms = <?= json_encode($termsData) ?>;

// ================= Contact Picker (Edit) =================
let cpModal, cpType = 'customer', cpDebounce;
function openContactPicker(){
  if (!cpModal) { cpModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('contactPickerModal')); }
  document.getElementById('cp_type_customer').checked = (cpType === 'customer');
  document.getElementById('cp_type_lead').checked = (cpType === 'lead');
  document.getElementById('cp_search').value = '';
  document.getElementById('cp_results').innerHTML = '';
  cpModal.show();
  setTimeout(()=> searchContacts(), 50);
}
function currentCpType(){ return document.getElementById('cp_type_lead').checked ? 'lead' : 'customer'; }
async function searchContacts(){
  const q = document.getElementById('cp_search').value.trim();
  const type = currentCpType();
  try {
    const res = await fetch(`/?action=quotations&subaction=listContacts&type=${encodeURIComponent(type)}&q=${encodeURIComponent(q)}`);
    const rows = await res.json();
    const box = document.getElementById('cp_results');
    if (!Array.isArray(rows)) { box.innerHTML = '<div class="text-muted px-2 py-1">No results</div>'; return; }
    box.innerHTML = rows.map(r=>
      `<button type=\"button\" class=\"list-group-item list-group-item-action d-flex justify-content-between align-items-center\" onclick=\"pickContact(${r.id}, '${type}')\">
        <span>
          <div class=\"fw-semibold\">${escapeHtml(r.label||'')}</div>
          <small class=\"text-muted\">${escapeHtml(r.sublabel||'')} ${r.city? '• '+escapeHtml(r.city): ''}</small>
        </span>
        <span class=\"badge bg-light text-dark border\">${r.type==='lead'?'Lead':'Customer'}</span>
      </button>`
    ).join('');
  } catch(e){
    document.getElementById('cp_results').innerHTML = '<div class="text-danger px-2 py-1">Failed to load</div>';
  }
}
async function pickContact(id, type){
  try {
    const res = await fetch(`/?action=quotations&subaction=getContact&type=${encodeURIComponent(type)}&id=${id}`);
    const data = await res.json();
    if (data && !data.error){
      const cust = document.getElementById('customer_input'); if (cust) cust.value = data.customer || '';
      const cp = document.querySelector('input[name="contact_person"]'); if (cp) cp.value = data.contact_person || '';
      const pa = document.getElementById('party_address'); if (pa) pa.value = data.party_address || '';
      // Populate address selectors if provided
      if (Array.isArray(data.addresses)) {
        const selParty = document.getElementById('address_select');
        const selShip = document.getElementById('address_select_shipping');
        const optsHtml = '<option value="">— Select —</option>' + data.addresses.map(a=>`<option value="${(a.formatted||'').replaceAll('"','&quot;')}">${(a.title||'').trim()||'Address'}</option>`).join('');
        if (selParty){ selParty.innerHTML = optsHtml; selParty.onchange = ()=>{ if (selParty.value) { document.getElementById('party_address').value = selParty.value; if (document.getElementById('same_shipping')?.checked) { document.getElementById('shipping_address').value = selParty.value; } } }; }
        if (selShip){ selShip.innerHTML = optsHtml; selShip.onchange = ()=>{ if (selShip.value) { document.getElementById('shipping_address').value = selShip.value; } }; }
      }
      // Do not auto-fill shipping address in edit either
      if (cpModal) cpModal.hide();
    }
  } catch(e){}
}
document.addEventListener('DOMContentLoaded', ()=>{
  const tCust = document.getElementById('cp_type_customer');
  const tLead = document.getElementById('cp_type_lead');
  if (tCust && tLead){ [tCust, tLead].forEach(el=> el.addEventListener('change', ()=>{ cpType = currentCpType(); searchContacts(); })); }
  const inp = document.getElementById('cp_search');
  if (inp){ inp.addEventListener('input', ()=>{ clearTimeout(cpDebounce); cpDebounce = setTimeout(searchContacts, 250); }); }
  const custInput = document.getElementById('customer_input');
  if (custInput){
    custInput.addEventListener('blur', tryAutoFillFromCustomerName);
    custInput.addEventListener('keydown', (e)=>{ if (e.key === 'Enter') { e.preventDefault(); tryAutoFillFromCustomerName(); } });
  }
});
function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[s])); }
async function tryAutoFillFromCustomerName(){
  const val = (document.getElementById('customer_input')?.value || '').trim();
  if (!val) return;
  try {
    const res = await fetch(`/?action=quotations&subaction=listContacts&type=customer&q=${encodeURIComponent(val)}`);
    const rows = await res.json();
    if (!Array.isArray(rows) || rows.length === 0) return;
    const exact = rows.find(r=> String(r.label||'').toLowerCase() === val.toLowerCase());
    const chosen = exact || (rows.length === 1 ? rows[0] : null);
    if (!chosen) return;
    const dres = await fetch(`/?action=quotations&subaction=getContact&type=customer&id=${chosen.id}`);
    const data = await dres.json();
    if (data && !data.error){
      const cp = document.querySelector('input[name="contact_person"]'); if (cp && !cp.value) cp.value = data.contact_person || '';
      const pa = document.getElementById('party_address'); if (pa && !pa.value) pa.value = data.party_address || '';
    }
  } catch(e) { /* ignore */ }
}
function openAddCustomerInline(){
  const el = document.getElementById('addCustomerInlineModal');
  const frame = document.getElementById('addCustomerInlineFrame');
  frame.src = '/?action=customers&subaction=create&embed=1';
  const m = bootstrap.Modal.getOrCreateInstance(el);
  m.show();
}

function addItemRow(pref = {}) {
  const tbody = document.querySelector('#itemsTable tbody');
  const rowIndex = tbody.children.length + 1;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="text-center">${rowIndex}</td>
    <td>
      <input class="form-control form-control-sm item-name" placeholder="Search item...">
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
  if (pref.name) tr.querySelector('.item-name').value = pref.name;
  if (pref.description) tr.querySelector('.item-desc').value = pref.description;
  recalc();
}

function recalc() {
  let totalTaxable = 0;
  const rows = document.querySelectorAll('#itemsTable tbody tr');
  const items = [];
  let perItemGstTotal = 0; let hasPerItemDiscount = false; let hasPerItemGst = false;
  rows.forEach((tr) => {
    const qty = parseFloat(tr.querySelector('.qty').value)||0;
    const rate = parseFloat(tr.querySelector('.rate').value)||0;
    const disc = parseFloat(tr.querySelector('.discount').value)||0;
    const gst = parseFloat(tr.querySelector('.gst').value)||0;
    const gstIncluded = !!(tr.querySelector('.gst-included') && tr.querySelector('.gst-included').checked);
    const lineGross = Math.max(qty*rate - disc, 0);
    let taxable = 0, thisItemGst = 0, amountForDisplay = 0;
    if (gstIncluded && gst>0){
      taxable = lineGross / (1 + (gst/100));
      thisItemGst = lineGross - taxable;
      amountForDisplay = lineGross;
    } else {
      taxable = lineGross;
      thisItemGst = taxable * (gst/100);
      amountForDisplay = taxable + thisItemGst;
    }
    tr.querySelector('.taxable').textContent = taxable.toFixed(2);
    tr.querySelector('.amount').textContent = amountForDisplay.toFixed(2);
    const amount = taxable; totalTaxable += amount; perItemGstTotal += thisItemGst;
    if (disc>0) hasPerItemDiscount = true; if (gst>0) hasPerItemGst = true;
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
  // disable/enable inputs
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

// Banks loader and select preset
async function loadBanks(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listBanks');
    const banks = await res.json();
    const sel = document.getElementById('bank_select');
    const current = String(<?= json_encode((string)($q['bank_account_id'] ?? '')) ?>);
    sel.innerHTML = '<option value="">— Select —</option>' + banks.map(b=>`<option value="${b.id}" ${String(b.id)===current? 'selected':''}>${b.bank_name} — ${b.account_no}</option>`).join('');
    document.getElementById('bank_account_id').value = current;
    sel.addEventListener('change', ()=>{
      document.getElementById('bank_account_id').value = sel.value;
    });
  } catch(e) { /* ignore */ }
}

// Prefill from existing data
(function init(){
  // Items
  if (Array.isArray(existingItems) && existingItems.length) {
    existingItems.forEach(it => addItemRow(it));
  } else {
    addItemRow();
  }
  // Terms
  if (Array.isArray(existingTerms) && existingTerms.length) {
    existingTerms.forEach(t => addTerm(t));
  }
  // Totals (freight removed)
  document.getElementById('overall_discount').value = String(<?= json_encode((string)($q['overall_discount'] ?? '0')) ?>);
  const og = document.getElementById('overall_gst_pct'); if (og) og.value = String(<?= json_encode((string)($q['overall_gst_pct'] ?? '0')) ?>);
  recalc();
  // Banks
  loadBanks();
})();

// Shipping: same as party behavior and sync terms on submit
document.addEventListener('DOMContentLoaded', () => {
  const party = document.getElementById('party_address');
  const ship = document.getElementById('shipping_address');
  const same = document.getElementById('same_shipping');
  if (same && party && ship) {
    const apply = () => {
      if (same.checked) {
        ship.value = party.value;
        ship.setAttribute('readonly','readonly');
        const cont = document.getElementById('shipping_container'); if (cont) cont.style.display = 'none';
      } else {
        ship.removeAttribute('readonly');
        const cont = document.getElementById('shipping_container'); if (cont) cont.style.display = '';
      }
    };
    same.addEventListener('change', apply);
    party.addEventListener('input', () => { if (same.checked) { ship.value = party.value; }});
    apply();
  }
  const form = document.getElementById('quotationForm');
  if (form) {
    form.addEventListener('submit', () => { if (typeof syncTerms === 'function') syncTerms(); });
  }
});

function refreshAddressOptionsEdit(){ /* options populated on pick */ }

// Inline Add Customer modal container
</script>

<div class="modal fade" id="addCustomerInlineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="height:80vh;">
      <div class="modal-header">
        <h5 class="modal-title">Add Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="addCustomerInlineFrame" src="about:blank" style="width:100%; height:100%; border:0;"></iframe>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
