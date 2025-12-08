<?php ob_start(); ?>

<?php
  // Create/Edit helpers
  $mode = $mode ?? 'create';
  $isEdit = ($mode === 'edit' && !empty($order['id']));
  $orderId = $isEdit ? (int)$order['id'] : 0;
  // Existing items/terms for edit mode (simple arrays)
  $itemsData = $items ?? [];
  $termsData = [];
  if (!empty($terms) && is_array($terms)) {
      foreach ($terms as $tRow) {
          $txt = (string)($tRow['term_text'] ?? '');
          if ($txt !== '') { $termsData[] = $txt; }
      }
  }
?>

<form id="orderForm" method="POST" action="/?action=orders&amp;subaction=<?= $isEdit ? 'update&amp;id=' . $orderId : 'store' ?>" enctype="multipart/form-data">
    <input type="hidden" name="items_json" id="items_json" value="[]"/>
    <input type="hidden" name="terms_json" id="terms_json" value="[]"/>

    <div class="d-flex justify-content-between mb-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/?action=orders">Orders</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Create' ?></li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <a href="/?action=orders" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-check"></i> <?= $isEdit ? 'Update' : 'Save' ?></button>
        </div>
    </div>

    <!-- Basic Information & Document Details -->
    <div class="card mb-3">
        <div class="card-header">Basic Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer / Lead</label>
                    <input type="hidden" name="customer_id" id="customer_id" value="<?= htmlspecialchars($order['customer_id'] ?? ($customer['id'] ?? '')) ?>">
                    <input type="hidden" name="lead_id" id="lead_id" value="">
                    <input type="hidden" name="connection_type" id="connection_type" value="<?= isset($order['customer_id']) || isset($customer['id']) ? 'customer' : '' ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" id="customerName" value="<?= htmlspecialchars($order['customer_name'] ?? ($customer['company'] ?? '')) ?>" placeholder="Select customer or lead" readonly>
                        <button type="button" class="btn btn-outline-secondary" title="Select from Connections" data-bs-toggle="modal" data-bs-target="#connectionModal" id="btnBrowse"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Order No.</label>
                    <input class="form-control" name="order_no" value="<?= htmlspecialchars($order['order_no'] ?? '') ?>" placeholder="Auto"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference</label>
                    <input class="form-control" name="customer_po" value="<?= htmlspecialchars($order['customer_po'] ?? '') ?>" placeholder="Customer PO / Ref"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Order Date</label>
                    <input type="date" class="form-control" name="order_date" value="<?= htmlspecialchars((string)($order['order_date'] ?? date('Y-m-d'))) ?>"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" name="due_date" value="<?= htmlspecialchars($order['due_date'] ?? '') ?>"/>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <?php $curStatus = (string)($order['status'] ?? 'Pending'); foreach (["Pending","Received","Bill Submitted","Delivered"] as $st): ?>
                            <option value="<?= $st ?>" <?= $curStatus === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales Credit</label>
                    <select class="form-select" name="sales_credit">
                        <option value="">— Select —</option>
                        <?php $sc = (string)($order['sales_credit'] ?? ''); foreach (($users ?? []) as $u): $label = trim(($u['name'] ?? '').(($u['email'] ?? '')? ' — '.$u['email'] : '')); $val = (string)($u['name'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= ($sc !== '' && $sc === $val) ? 'selected' : '' ?>><?= htmlspecialchars($label ?: 'User') ?></option>
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
                    <input class="form-control" name="contact_name" value="<?= htmlspecialchars($order['contact_name'] ?? ($customer['contact_name'] ?? '')) ?>"/>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Party Address</label>
                    <div class="input-group mb-1">
                      <select class="form-select" id="address_select" title="Select address" style="max-height:200px; overflow:auto"></select>
                      <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshOrderAddressOptions()"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                    <textarea class="form-control" rows="2" name="billing_address" id="party_address" placeholder="Billing / Party address"><?= htmlspecialchars($order['billing_address'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" id="same_shipping">
                        <label class="form-check-label" for="same_shipping">Shipping address is same as party address</label>
                    </div>
                    <div id="shipping_container">
                      <label class="form-label">Shipping Address</label>
                      <div class="input-group mb-1">
                        <select class="form-select" id="address_select_shipping" title="Select shipping address" style="max-height:200px; overflow:auto"></select>
                        <button class="btn btn-outline-secondary" type="button" title="Refresh addresses" onclick="refreshOrderAddressOptions()"><i class="bi bi-arrow-repeat"></i></button>
                      </div>
                      <textarea class="form-control" rows="2" name="shipping_address" id="shipping_address"><?= htmlspecialchars($order['shipping_address'] ?? '') ?></textarea>
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
                            <th>Item &amp; Description</th>
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
            <span>Terms &amp; Conditions</span>
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
                    <textarea class="form-control" rows="4" name="notes"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bank Details</div>
                <div class="card-body">
                    <input type="hidden" name="bank_account_id" id="bank_account_id" value="<?= htmlspecialchars((string)($order['bank_account_id'] ?? '')) ?>">
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
        <button type="button" class="btn btn-outline-primary" onclick="openAddCustomerInlineFromOrder()"><i class="bi bi-plus-lg"></i> Add New Connection</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<script>
// Inventory items and existing data for edit mode
const pickerItems = <?= json_encode($pickerItems ?? []) ?>;
const existingItems = <?= json_encode($itemsData ?? []) ?>;
const existingTerms = <?= json_encode($termsData ?? []) ?>;
const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

// Simple item/terms JS shared with quotation-style UI
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
    <td><input class="form-control form-control-sm hsn"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm qty" value="${pref.qty||1}" oninput="recalc()"></td>
    <td><input class="form-control form-control-sm unit" value="${pref.unit||'nos'}"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm rate" value="${pref.rate||0}" oninput="recalc()"></td>
    <td><input type="number" min="0" step="0.01" class="form-control form-control-sm discount" value="0" oninput="recalc()"></td>
    <td class="text-end taxable">0.00</td>
    <td>
      <div class="input-group input-group-sm">
        <input type="number" min="0" step="0.01" class="form-control form-control-sm gst" value="0" oninput="recalc()">
        <span class="input-group-text">
          <input type="checkbox" class="form-check-input gst-included" title="GST included in rate" onchange="recalc()">
        </span>
      </div>
      <small class="text-muted">Incl. in rate</small>
    </td>
    <td class="text-end amount">0.00</td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalc();"><i class="bi bi-trash"></i></button></td>
  `;
  tbody.appendChild(tr);
  // Prefill when editing
  if (pref) {
    const nameField = tr.querySelector('.item-name');
    const descField = tr.querySelector('.item-desc');
    const hsnField = tr.querySelector('.hsn');
    const qtyField = tr.querySelector('.qty');
    const unitField = tr.querySelector('.unit');
    const rateField = tr.querySelector('.rate');
    const discField = tr.querySelector('.discount');
    const gstField = tr.querySelector('.gst');
    const gstIncl = tr.querySelector('.gst-included');
    const nameVal = pref.name || pref.item_name || '';
    if (nameVal && nameField) nameField.value = nameVal;
    if (descField && (pref.description || pref.item_desc)) descField.value = pref.description || pref.item_desc || '';
    if (hsnField && (pref.hsn_sac || pref.hsn)) hsnField.value = pref.hsn_sac || pref.hsn || '';
    if (qtyField && typeof pref.qty !== 'undefined') qtyField.value = pref.qty;
    if (unitField && (pref.unit || pref.unit_name)) unitField.value = pref.unit || pref.unit_name || '';
    if (rateField && typeof pref.rate !== 'undefined') rateField.value = pref.rate;
    if (discField && typeof pref.discount !== 'undefined') discField.value = pref.discount;
    if (gstField && typeof pref.gst !== 'undefined') gstField.value = pref.gst;
    if (gstIncl && typeof pref.gst_included !== 'undefined') gstIncl.checked = !!pref.gst_included;
  }
  // Auto fill from inventory picker when choosing
  const nameInput = tr.querySelector('.item-name');
  nameInput.addEventListener('change', () => {
    const option = Array.from(document.querySelectorAll(`#itemList${rowIndex} option`)).find(o => o.value === nameInput.value);
    if (option) {
      tr.querySelector('.hsn').value = option.dataset.hsn || '';
      tr.querySelector('.unit').value = option.dataset.unit || 'nos';
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
  const extra = 0;
  const overallDiscEl = document.getElementById('overall_discount');
  const overallGstEl = document.getElementById('overall_gst_pct');
  const overallDiscInput = parseFloat(overallDiscEl.value)||0;
  const overallGstPctInput = parseFloat(overallGstEl.value)||0;
  const blockOverall = (hasPerItemDiscount || hasPerItemGst);
  overallDiscEl.disabled = blockOverall;
  overallGstEl.disabled = blockOverall;
  const effectiveOverallDiscount = blockOverall ? 0 : overallDiscInput;
  const effectiveOverallGstPct = blockOverall ? 0 : overallGstPctInput;
  const baseBeforeDiscount = Math.max(totalTaxable + extra, 0);
  const gstBase = Math.max(baseBeforeDiscount - effectiveOverallDiscount, 0);
  const overallGst = gstBase * (effectiveOverallGstPct/100);
  const gstTotal = overallGst + perItemGstTotal;
  const grand = Math.max(gstBase + gstTotal, 0);
  document.getElementById('subtotal_before_gst').textContent = gstBase.toFixed(2);
  document.getElementById('gst_total').textContent = gstTotal.toFixed(2);
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
function clearTerms(){
  document.getElementById('termsBox').innerHTML = '';
  syncTerms();
}

// Shipping same as party + initialize items/terms (create vs edit)
document.addEventListener('DOMContentLoaded', () => {
  const party = document.getElementById('party_address');
  const ship = document.getElementById('shipping_address');
  const same = document.getElementById('same_shipping');
  if (party && ship && same) {
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
    party.addEventListener('input', () => { if (same.checked) ship.value = party.value; });
    apply();
  }

  // Initialize items from existing data in edit, otherwise one blank row
  if (Array.isArray(existingItems) && existingItems.length) {
    existingItems.forEach(it => addItemRow(it));
  } else {
    addItemRow();
  }

  // Initialize terms: use saved terms in edit, otherwise load defaults from SalesConfig
  if (Array.isArray(existingTerms) && existingTerms.length) {
    existingTerms.forEach(t => addTerm(t));
    syncTerms();
  } else {
    loadTerms();
  }

  bindAddCustomerFormOnce();

  // In edit mode, if a connection is already selected, populate address dropdowns
  if (isEdit) {
    refreshOrderAddressOptions();
  }
});

// Load banks similar to quotations
async function loadBanks(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listBanks');
    const banks = await res.json();
    const sel = document.getElementById('bank_select');
    const hidden = document.getElementById('bank_account_id');
    const currentId = hidden ? (hidden.value || '') : '';
    sel.innerHTML = '<option value="">— Select —</option>' + banks.map(b=>{
      const selected = currentId
        ? (String(b.id) === String(currentId) ? 'selected' : '')
        : (b.is_default ? 'selected' : '');
      return `<option value="${b.id}" ${selected}>${b.bank_name} — ${b.account_no}</option>`;
    }).join('');

    // If nothing selected yet and there is a default, sync hidden field
    let effectiveId = currentId;
    if (!effectiveId) {
      const def = banks.find(b=>b.is_default==1);
      if (def) {
        effectiveId = String(def.id);
      }
    }
    if (hidden) hidden.value = effectiveId;
    if (sel && effectiveId) sel.value = effectiveId;

    sel.addEventListener('change', ()=>{
      if (hidden) hidden.value = sel.value;
    });
  } catch(e) {}
}
loadBanks();

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

async function selectCustomer(r){
  document.getElementById('connection_type').value = 'customer';
  document.getElementById('customer_id').value = r.id || '';
  document.getElementById('lead_id').value = '';
  document.getElementById('customerName').value = r.company || '';
  const contact = r.contact_name || '';
  const contactInput = document.querySelector('input[name="contact_name"]');
  if (contact && contactInput && !contactInput.value) contactInput.value = contact;
  bootstrap.Modal.getInstance(document.getElementById('connectionModal'))?.hide();
  await fetchContactDetailsForOrder('customer', r.id);
}

async function selectLead(r){
  document.getElementById('connection_type').value = 'lead';
  document.getElementById('customer_id').value = '';
  document.getElementById('lead_id').value = r.id || '';
  document.getElementById('customerName').value = (r.business_name||'') + ' (Lead)';
  const contact = r.contact_person || '';
  const contactInput = document.querySelector('input[name="contact_name"]');
  if (contact && contactInput && !contactInput.value) contactInput.value = contact;
  bootstrap.Modal.getInstance(document.getElementById('connectionModal'))?.hide();
  await fetchContactDetailsForOrder('lead', r.id);
}

// Fetch full contact/address details like quotation getContact and fill order addresses
async function fetchContactDetailsForOrder(type, id){
  try {
    const res = await fetch(`/?action=quotations&subaction=getContact&type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`);
    if (!res.ok) return;
    const data = await res.json();
    if (!data || data.error) return;

    const party = document.getElementById('party_address');
    const ship = document.getElementById('shipping_address');
    if (party && data.party_address) {
      party.value = data.party_address;
    }
    if (ship) {
      const same = document.getElementById('same_shipping');
      if (same && same.checked) {
        ship.value = data.party_address || data.shipping_address || '';
      } else if (data.shipping_address) {
        ship.value = data.shipping_address;
      }
    }

    // Populate address selectors if provided (same pattern as quotation forms)
    if (Array.isArray(data.addresses)) {
      const selParty = document.getElementById('address_select');
      const selShip = document.getElementById('address_select_shipping');
      const optsHtml = '<option value="">— Select —</option>' + data.addresses.map(a=>`<option value="${(a.formatted||'').replaceAll('"','&quot;')}">${(a.title||'').trim()||'Address'}</option>`).join('');
      if (selParty){
        selParty.innerHTML = optsHtml;
        selParty.onchange = ()=>{
          if (selParty.value) {
            const v = selParty.value;
            const p = document.getElementById('party_address'); if (p) p.value = v;
            const same = document.getElementById('same_shipping');
            if (same && same.checked) {
              const s = document.getElementById('shipping_address'); if (s) s.value = v;
            }
          }
        };
      }
      if (selShip){
        selShip.innerHTML = optsHtml;
        selShip.onchange = ()=>{
          if (selShip.value) {
            const s = document.getElementById('shipping_address'); if (s) s.value = selShip.value;
          }
        };
      }
    }

    const contactInput = document.querySelector('input[name="contact_name"]');
    if (contactInput && !contactInput.value && data.contact_person) {
      contactInput.value = data.contact_person;
    }
  } catch(e) {
    // fail silently
  }
}

async function refreshOrderAddressOptions(){
  try {
    const typeInput = document.getElementById('connection_type');
    const custInput = document.getElementById('customer_id');
    const leadInput = document.getElementById('lead_id');
    const type = (typeInput?.value || '').toLowerCase();
    let id = '';
    if (type === 'customer' && custInput && custInput.value) {
      id = custInput.value;
    } else if (type === 'lead' && leadInput && leadInput.value) {
      id = leadInput.value;
    } else {
      return;
    }

    const res = await fetch(`/?action=quotations&subaction=getContact&type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`);
    if (!res.ok) return;
    const data = await res.json();
    if (!data || data.error || !Array.isArray(data.addresses)) return;

    const selParty = document.getElementById('address_select');
    const selShip = document.getElementById('address_select_shipping');
    const optsHtml = '<option value="">— Select —</option>' + data.addresses.map(a=>`<option value="${(a.formatted||'').replaceAll('"','&quot;')}">${(a.title||'').trim()||'Address'}</option>`).join('');

    if (selParty){
      selParty.innerHTML = optsHtml;
      selParty.onchange = ()=>{
        if (selParty.value) {
          const p = document.getElementById('party_address');
          if (p) p.value = selParty.value;
          const same = document.getElementById('same_shipping');
          if (same && same.checked) {
            const s = document.getElementById('shipping_address');
            if (s) s.value = selParty.value;
          }
        }
      };
    }
    if (selShip){
      selShip.innerHTML = optsHtml;
      selShip.onchange = ()=>{
        if (selShip.value) {
          const s = document.getElementById('shipping_address');
          if (s) s.value = selShip.value;
        }
      };
    }
  } catch(e) {
    // silent
  }
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

// --- SalesConfig Terms integration (load default terms into order form) ---
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

async function loadTerms(){
  const rows = await listTerms();
  const termsBox = document.getElementById('termsBox');
  termsBox.innerHTML = '';
  (rows||[]).forEach(row => {
    const text = row.text || '';
    if (!text) return;
    const el = makeTermRow(text, !!row.is_active);
    termsBox.appendChild(el);
  });
  syncTerms();
}

document.addEventListener('DOMContentLoaded', ()=>{
  loadTerms();
  bindAddCustomerFormOnce();
});

// ===== Inline Add Customer (reuse from quotations, adapted for orders) =====
let addCustomerPendingAddresses = [];

function openAddCustomerInlineFromOrder(){
  const el = document.getElementById('addCustomerInlineModal');
  const m = bootstrap.Modal.getOrCreateInstance(el);
  const form = document.getElementById('addCustomerForm');
  if (form) {
    form.reset();
  }
  const idEl = document.getElementById('addCustomerId');
  if (idEl) idEl.value = '';
  const typeHidden = document.getElementById('addCustomerTypeHidden');
  if (typeHidden) typeHidden.value = 'customer';
  addCustomerPendingAddresses = [];
  updateAddCustomerPendingBadge();
  populateAddCustomerCities();
  bindAddCustomerFormOnce();
  m.show();
}

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

document.addEventListener('DOMContentLoaded', () => {
  const addCustomerTypeButtons = document.getElementById('addCustomerTypeButtons');
  if (addCustomerTypeButtons) {
    addCustomerTypeButtons.addEventListener('click', function(e){
      if (!e.target.classList.contains('type-btn')) return;
      setAddCustomerType(e.target.dataset.type);
    });
  }

  const addCustomerAddressForm = document.getElementById('addCustomerAddressForm');
  if (addCustomerAddressForm) {
    addCustomerAddressForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const fd = new FormData(e.target);
      const staged = Object.fromEntries(fd.entries());
      delete staged['id'];
      staged['customer_id'] = '';
      addCustomerPendingAddresses.push(staged);
      const modalEl = document.getElementById('addCustomerAddressModal');
      if (modalEl) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
      }
      updateAddCustomerPendingBadge();
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

function openAddCustomerAddressModal() {
  const titleEl = document.getElementById('addCustomerAddressModalLabel');
  if (titleEl) titleEl.textContent = 'Add Address';
  const form = document.getElementById('addCustomerAddressForm');
  if (form) form.reset();
  const idEl = document.getElementById('addCustomerAddrId');
  if (idEl) idEl.value = '';
  const cidEl = document.getElementById('addCustomerAddrCustomerId');
  if (cidEl) cidEl.value = '';
  populateAddCustomerAddressCities();
  const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('addCustomerAddressModal'));
  m.show();
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

function bindAddCustomerFormOnce(){
  const form = document.getElementById('addCustomerForm');
  if (!form || form.dataset.bound === '1') return;
  form.dataset.bound = '1';
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    const form = e.target;

    const title = (document.getElementById('addCustomerTitle')?.value || '').trim();
    const first = (document.getElementById('addCustomerFirstName')?.value || '').trim();
    const last = (document.getElementById('addCustomerLastName')?.value || '').trim();
    const composed = (title + ' ' + first + ' ' + last).trim();
    const contactHidden = document.getElementById('addCustomerContactNameHidden');
    if (contactHidden) contactHidden.value = composed;

    const selectedType = (document.querySelector('#addCustomerTypeButtons .type-btn.active')?.dataset.type) || 'customer';
    const typeHidden = document.getElementById('addCustomerTypeHidden');
    if (typeHidden) typeHidden.value = selectedType;

    const formData = new URLSearchParams(new FormData(form));
    if (!formData.has('is_active')) formData.append('is_active', '0');

    try {
      const resp = await fetch('/?action=customers&subaction=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
      });
      const result = await resp.json();
      if (result && result.error) {
        alert('Error: ' + result.error);
        return;
      }
      const newId = result?.id;

      // Create any staged addresses against this customer
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
          } catch (er) {
            failed++;
            lastDetail = er.message || String(er);
          }
        }
        addCustomerPendingAddresses = [];
        updateAddCustomerPendingBadge();
        if (failed > 0) {
          alert('Customer created but some addresses failed to save (' + failed + '). ' + (lastDetail ? ('Details: ' + lastDetail) : ''));
        }
      }

      // Close Add Customer modal
      const addModalEl = document.getElementById('addCustomerInlineModal');
      if (addModalEl) {
        bootstrap.Modal.getInstance(addModalEl)?.hide();
      }

      // Auto-select the newly created customer in order form
      const companyName = document.getElementById('addCustomerCompany')?.value || '';
      try {
        const rows = await fetchCustomers(companyName);
        const match = (rows || []).find(r => String(r.id) === String(newId)) || (rows || [])[0];
        if (match) {
          selectCustomer(match);
        }
      } catch (er) {
        // fallback: just set name field
        const custNameInput = document.getElementById('customerName');
        if (custNameInput) custNameInput.value = companyName;
      }
    } catch (err) {
      alert('Error: ' + (err?.message || err));
    }
  });
}
</script>

<!-- Add Customer Modal (inline, reused from quotations) -->
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
                    <label class="form-label">Industry &amp; Segment</label>
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
                    <button type="button" id="btnAddCustomerManageAddresses" class="btn btn-warning" onclick="openAddCustomerAddressModal()"><i class="bi bi-geo-alt"></i> Manage Addresses &amp; GST</button>
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
