<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Create Invoice</h4>
  <div class="d-flex gap-2">
    <a href="/?action=invoices" class="btn btn-sm btn-outline-secondary">Back to Invoices</a>
  </div>
</div>

<form id="invForm" class="card" method="post" action="/?action=invoices&subaction=store" enctype="multipart/form-data">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Invoice No.</label>
        <input type="number" class="form-control" name="invoice_no" value="<?= isset($prefill['invoice_no']) ? (int)$prefill['invoice_no'] : (int)($nextNo ?? 1) ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Customer</label>
        <input type="text" class="form-control" name="customer" placeholder="Customer name" value="<?= htmlspecialchars($prefill['customer'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Executive</label>
        <select class="form-select" name="executive">
          <option value="">-- Select Executive --</option>
          <?php $prefExec = $prefill['executive'] ?? ''; foreach (($users ?? []) as $u): $name = htmlspecialchars(($u['name'] ?? ($u['email'] ?? 'User'))); ?>
            <option value="<?= $name ?>" <?= ($prefExec && $name === htmlspecialchars($prefExec)) ? 'selected' : '' ?>><?= $name ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Issued On</label>
        <input type="date" class="form-control" name="issued_on" value="<?= htmlspecialchars($prefill['issued_on'] ?? date('Y-m-d')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Type</label>
        <select class="form-select" name="type">
          <option value="Invoice" selected>Invoice</option>
          <option value="Retail">Retail</option>
        </select>
      </div>
      <!-- Attachment moved to bottom to match reference -->
    </div>

    <!-- Party Details + Document Details -->
    <div class="row g-3 mt-1">
      <div class="col-lg-8">
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Party Details</strong></div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Contact Person</label>
                <input type="text" class="form-control" name="contact_person" placeholder="Contact Person" value="<?= htmlspecialchars($prefill['contact_person'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">State / GSTIN</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="state" placeholder="State">
                  <input type="text" class="form-control" name="gstin" placeholder="GSTIN (optional)">
                </div>
              </div>
              <div class="col-md-12">
                <label class="form-label">Billing Address</label>
                <textarea class="form-control" name="party_address" rows="2" placeholder="Billing Address"><?= htmlspecialchars($prefill['party_address'] ?? '') ?></textarea>
              </div>
              <div class="col-md-12">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="sameAsBilling" checked>
                  <label class="form-check-label" for="sameAsBilling">Shipping same as Billing</label>
                </div>
                <label class="form-label">Shipping Address</label>
                <textarea class="form-control" name="shipping_address" id="shippingAddress" rows="2" placeholder="Shipping Address"><?= htmlspecialchars($prefill['shipping_address'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Document Details</strong></div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-12">
                <label class="form-label">Reference</label>
                <input type="text" class="form-control" name="reference" placeholder="Reference" value="<?= htmlspecialchars($prefill['reference'] ?? '') ?>">
              </div>
              <div class="col-6">
                <label class="form-label">Invoice Date</label>
                <input type="date" class="form-control" name="issued_on" value="<?= htmlspecialchars($prefill['issued_on'] ?? date('Y-m-d')) ?>">
              </div>
              <div class="col-6">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" name="valid_till" value="<?= htmlspecialchars($prefill['valid_till'] ?? date('Y-m-d', strtotime('+7 days'))) ?>">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>

      <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Items</h5>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus"></i> Add Item</button>
    </div>
    <div class="card mb-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered mb-0 align-middle" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th style="width:50px">No.</th>
                <th>Item & Description</th>
                <th style="width:120px">HSN/SAC</th>
                <th style="width:100px">Qty</th>
                <th style="width:90px">Unit</th>
                <th style="width:120px">Rate (₹)</th>
                <th style="width:120px">Discount (₹)</th>
                <th class="text-end" style="width:120px">Taxable (₹)</th>
                <th class="text-end" style="width:100px">GST %</th>
                <th class="text-end" style="width:140px">Amt (₹)</th>
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="card-footer text-end">
        <div class="d-inline-block text-start">
          <div>Total Taxable: ₹ <span id="total">0.00</span></div>
          <div>Extra Charge (₹): <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" id="extraCharge" value="<?= htmlspecialchars((string)($prefill['extra_charge'] ?? '0')) ?>" oninput="recalc()"></div>
          <div>Overall Discount (₹): <input type="number" step="0.01" class="form-control d-inline-block" style="width:140px" id="overallDiscount" value="<?= htmlspecialchars((string)($prefill['overall_discount'] ?? '0')) ?>" oninput="recalc()"></div>
          <div>Subtotal (before GST): ₹ <span id="subtotal_before_gst">0.00</span></div>
          <div>GST Total: ₹ <span id="gst_total">0.00</span></div>
          <div>Tax Type: <span id="tax_type" class="fw-semibold">-</span></div>
          <div class="fw-bold">Grand Total: ₹ <span id="grandTotal">0.00</span></div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2">
      <div class="col-lg-8">
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Terms & Conditions</strong></div>
          <div class="card-body">
            <div class="d-flex justify-content-end gap-2 mb-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="tcClear">Clear All</button>
              <button type="button" class="btn btn-sm btn-outline-primary" id="tcAdd">+ Add Term / Condition</button>
            </div>
            <textarea class="form-control" name="terms_text" id="termsText" rows="5" placeholder="Type terms & conditions or paste a template"></textarea>
          </div>
        </div>
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Notes</strong></div>
          <div class="card-body">
            <textarea class="form-control" name="notes" rows="3" placeholder="Notes visible to customer"><?= htmlspecialchars($prefill['notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Bank Details</strong></div>
          <div class="card-body">
            <select class="form-select" name="bank_account_id">
              <option value="">-- Select Bank --</option>
              <?php $prefBank = isset($prefill['bank_account_id']) ? (int)$prefill['bank_account_id'] : 0; foreach (($banks ?? []) as $b): ?>
                <option value="<?= (int)$b['id'] ?>" <?= ($prefBank && (int)$b['id'] === $prefBank) ? 'selected' : '' ?>><?= htmlspecialchars(($b['bank_name'] ?? 'Bank') . ' - ' . ($b['account_no'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="card mb-3">
          <div class="card-header py-2"><strong>Payment Recovery</strong></div>
          <div class="card-body">
            <div class="mb-2">
              <label class="form-label">Payment Received (₹)</label>
              <input type="number" step="0.01" class="form-control" id="paymentReceived" placeholder="0.00">
            </div>
            <div class="mb-2">
              <label class="form-label">Update Invoice Status</label>
              <select class="form-select" id="statusSelect">
                <option>Pending</option>
                <option>Paid</option>
                <option>Partial</option>
                <option>Cancelled</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Internal Note</label>
              <textarea class="form-control" name="internal_note" rows="2" placeholder="Visible to team only"></textarea>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="shareAfterSave">
              <label class="form-check-label" for="shareAfterSave">Share by Email/WhatsApp after saving</label>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header py-2"><strong>Attachment (optional)</strong></div>
          <div class="card-body">
            <input type="file" class="form-control" name="attachment" accept=".pdf,image/*">
            <div class="form-text">Max 10 MB. Allowed: PDF, images</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden JSON payloads expected by controller -->
    <input type="hidden" name="items_json" id="itemsJson" value="[]">
    <input type="hidden" name="terms_json" value="[]">
    <input type="hidden" name="grand_total" id="grandTotalInput" value="0">
    <input type="hidden" name="extra_charge" id="extraChargeInput" value="0">
    <input type="hidden" name="overall_discount" id="overallDiscountInput" value="0">
    <input type="hidden" name="payment_received" id="paymentReceivedInput" value="0">
    <input type="hidden" name="status" id="statusInput" value="Pending">
  </div>
  <div class="card-footer d-flex justify-content-end gap-2">
    <a href="/?action=invoices" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-success">Save Invoice</button>
  </div>
</form>

<script>
(function(){
  const tbody = document.querySelector('#itemsTable tbody');
  const addBtn = document.getElementById('addItem');
  const taxableTotalEl = document.getElementById('taxableTotal');
  const subTotalEl = document.getElementById('subTotal');
  const grandTotalEl = document.getElementById('grandTotal');
  const itemsJsonEl = document.getElementById('itemsJson');
  const grandTotalInput = document.getElementById('grandTotalInput');
  const statusInput = document.getElementById('statusInput');
  const extraChargeEl = document.getElementById('extraCharge');
  const overallDiscountEl = document.getElementById('overallDiscount');
  const extraChargeInput = document.getElementById('extraChargeInput');
  const overallDiscountInput = document.getElementById('overallDiscountInput');
  const paymentReceived = document.getElementById('paymentReceived');
  const paymentReceivedInput = document.getElementById('paymentReceivedInput');
  const statusSelect = document.getElementById('statusSelect');
  const taxTypeEl = document.getElementById('tax_type');

  // Inventory picker like in Quotations
  const pickerItems = <?= json_encode($pickerItems ?? []) ?>;
  const prefillItems = <?= json_encode($prefillItems ?? []) ?>;
  function rowTemplate(index){
    const dlId = `itemList${index}`;
    return `<tr>
      <td class="text-center" data-serial>${index}</td>
      <td>
        <input class="form-control form-control-sm" placeholder="Search item..." list="${dlId}" data-name>
        <datalist id="${dlId}">${(pickerItems||[]).map(i=>`<option value="${i.name} (${i.code})" data-id="${i.id}" data-hsn="${i.hsn_sac||''}" data-unit="${i.unit||'nos'}" data-rate="${i.rate||0}" data-gst="${i.gst||0}"></option>`).join('')}</datalist>
        <small class="text-muted d-block"><input class="form-control form-control-sm" placeholder="Description" data-desc></small>
      </td>
      <td><input type="text" class="form-control form-control-sm" placeholder="HSN/SAC" data-hsn></td>
      <td><input type="number" class="form-control form-control-sm" min="0" step="0.01" value="1" data-qty></td>
      <td><input type="text" class="form-control form-control-sm" placeholder="Unit" value="nos" data-unit></td>
      <td><input type="number" class="form-control form-control-sm" min="0" step="0.01" value="0" data-rate></td>
      <td><input type="number" class="form-control form-control-sm" min="0" step="0.01" value="0" data-disc_amt></td>
      <td class="text-end"><span data-taxable>0.00</span></td>
      <td>
        <div class="input-group input-group-sm">
          <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" value="0" data-gst>
          <span class="input-group-text">
            <input type="checkbox" class="form-check-input" data-gst-included title="GST included in rate">
          </span>
        </div>
        <small class="text-muted">Incl. in rate</small>
      </td>
      <td class="text-end"><span data-amount>0.00</span></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-del>&times;</button></td>
    </tr>`;
  }

  function recalc(){
    let rows = tbody.querySelectorAll('tr');
    let items = [];
    let totalTaxable = 0;
    let perItemGstTotal = 0;
    let anyGst = false, anyIncl = false, anyExcl = false;
    rows.forEach(r => {
      const name = r.querySelector('[data-name]').value.trim() || 'Item';
      const desc = r.querySelector('[data-desc]')?.value.trim() || '';
      const hsn  = r.querySelector('[data-hsn]').value.trim();
      const unit = r.querySelector('[data-unit]').value.trim() || 'nos';
      const qty  = parseFloat(r.querySelector('[data-qty]').value||'0');
      const rate = parseFloat(r.querySelector('[data-rate]').value||'0');
      const discAmt = parseFloat(r.querySelector('[data-disc_amt]').value||'0');
      const gstp = parseFloat(r.querySelector('[data-gst]').value||'0');
      const gstIncluded = !!(r.querySelector('[data-gst-included]') && r.querySelector('[data-gst-included]').checked);
      const lineGross = Math.max(0, qty * rate - Math.max(0, discAmt));
      let taxable = 0, thisItemGst = 0, amountForDisplay = 0;
      if (gstIncluded && gstp > 0) {
        taxable = lineGross / (1 + (gstp/100));
        thisItemGst = lineGross - taxable;
        amountForDisplay = lineGross; // already inclusive
        anyGst = true; anyIncl = true;
      } else {
        taxable = lineGross;
        thisItemGst = taxable * (gstp/100);
        amountForDisplay = taxable + thisItemGst;
        if (gstp > 0) { anyGst = true; anyExcl = true; }
      }
      r.querySelector('[data-taxable]').textContent = taxable.toFixed(2);
      r.querySelector('[data-amount]').textContent = amountForDisplay.toFixed(2);
      totalTaxable += taxable;
      perItemGstTotal += thisItemGst;
      items.push({name, description: desc, hsn_sac: hsn, unit, qty, rate, discount: discAmt, gst: gstp, gst_included: gstIncluded?1:0, taxable, amount: amountForDisplay});
    });
    const extra = parseFloat(extraChargeEl?.value||'0');
    const discAll = parseFloat(overallDiscountEl?.value||'0');
    const baseBeforeDiscount = Math.max(0, totalTaxable + Math.max(0, extra));
    const gstBase = Math.max(0, baseBeforeDiscount - Math.max(0, discAll));
    const overallGst = 0; // overall GST disabled; using per-item GST only
    const gstTotal = overallGst + perItemGstTotal;
    const grand = Math.max(0, gstBase + gstTotal);
    document.getElementById('total').textContent = totalTaxable.toFixed(2);
    document.getElementById('subtotal_before_gst').textContent = gstBase.toFixed(2);
    document.getElementById('gst_total').textContent = gstTotal.toFixed(2);
    grandTotalEl.textContent = grand.toFixed(2);
    itemsJsonEl.value = JSON.stringify(items);
    grandTotalInput.value = grand.toFixed(2);
    extraChargeInput.value = Math.max(0, extra).toFixed(2);
    overallDiscountInput.value = Math.max(0, discAll).toFixed(2);
    if (taxTypeEl) {
      taxTypeEl.textContent = !anyGst ? 'No Tax' : (anyIncl && !anyExcl ? 'GST Inclusive' : (!anyIncl && anyExcl ? 'GST Exclusive' : 'Mixed'));
    }
    const recv = parseFloat(paymentReceived?.value||'0');
    paymentReceivedInput.value = Math.max(0, recv).toFixed(2);
    statusInput.value = statusSelect?.value || (grand <= 0.005 ? 'Paid' : 'Pending');
  }

  addBtn.addEventListener('click', ()=>{ const idx = tbody.children.length + 1; tbody.insertAdjacentHTML('beforeend', rowTemplate(idx)); recalc(); bindRow(tbody.lastElementChild, idx); });
  function bindRow(tr, idx){
    tr.querySelectorAll('input').forEach(inp => inp.addEventListener('input', recalc));
    // Autocomplete fill
    const nameInput = tr.querySelector('[data-name]');
    nameInput?.addEventListener('change', ()=>{
      const dl = document.getElementById(`itemList${idx}`);
      const opt = Array.from(dl?.options || []).find(o=>o.value === nameInput.value);
      if (opt){
        tr.querySelector('[data-hsn]').value = opt.dataset.hsn || '';
        tr.querySelector('[data-unit]').value = opt.dataset.unit || 'nos';
        tr.querySelector('[data-rate]').value = Number(opt.dataset.rate||0).toFixed(2);
        tr.querySelector('[data-gst]').value = Number(opt.dataset.gst||0).toFixed(2);
        recalc();
      }
    });
    tr.querySelector('[data-del]').addEventListener('click', ()=>{ tr.remove(); recalc(); });
  }
  // Prefill items from quotation if available
  if (Array.isArray(prefillItems) && prefillItems.length > 0) {
    prefillItems.forEach((it, idx) => {
      const index = tbody.children.length + 1;
      tbody.insertAdjacentHTML('beforeend', rowTemplate(index));
      const tr = tbody.lastElementChild;
      // Bind inputs
      bindRow(tr, index);
      // Set values
      tr.querySelector('[data-name]').value = it.name || '';
      const dl = tr.querySelector('datalist');
      // description, hsn, unit, qty, rate, discount, gst
      const descEl = tr.querySelector('[data-desc]'); if (descEl) descEl.value = it.description || '';
      tr.querySelector('[data-hsn]').value = it.hsn_sac || '';
      tr.querySelector('[data-unit]').value = it.unit || 'nos';
      tr.querySelector('[data-qty]').value = (it.qty != null ? it.qty : 1);
      tr.querySelector('[data-rate]').value = (it.rate != null ? it.rate : 0);
      tr.querySelector('[data-disc_amt]').value = (it.discount != null ? it.discount : 0);
      tr.querySelector('[data-gst]').value = (it.gst != null ? it.gst : 0);
      const incl = (it.gst_included == 1 || it.gst_included === true);
      const inclEl = tr.querySelector('[data-gst-included]'); if (inclEl) inclEl.checked = incl;
    });
    recalc();
  } else {
    // Add one default row
    addBtn.click();
  }

  [extraChargeEl, overallDiscountEl, paymentReceived, statusSelect].forEach(el=> el && el.addEventListener('input', recalc));
  document.getElementById('sameAsBilling')?.addEventListener('change', (e)=>{
    if (e.target.checked) {
      const bill = document.querySelector('textarea[name="party_address"]').value;
      document.getElementById('shippingAddress').value = bill;
    }
  });
  // Terms & Conditions helpers
  // Terms: behave like Quotations (JSON list in terms_json)
  const termsInput = document.querySelector('input[name="terms_json"]');
  document.getElementById('tcClear')?.addEventListener('click', ()=>{ document.getElementById('termsText').value=''; if (termsInput) termsInput.value='[]'; });
  document.getElementById('tcAdd')?.addEventListener('click', ()=>{
    const ta = document.getElementById('termsText');
    ta.value = (ta.value ? (ta.value + "\n") : '') + '• New term condition';
    syncTermsJson();
  });
  function syncTermsJson(){
    const lines = (document.getElementById('termsText').value||'').split(/\n+/).map(s=>s.trim()).filter(Boolean);
    if (termsInput) termsInput.value = JSON.stringify(lines);
  }
  document.getElementById('termsText')?.addEventListener('input', syncTermsJson);
  // Load default active terms from SalesConfig like in Quotations
  (async function loadDefaultTerms(){
    try{
      const res = await fetch('/?action=salesConfig&subaction=listTerms');
      const all = await res.json();
      const active = (all||[]).filter(t=>String(t.is_active)==='1').sort((a,b)=> (a.display_order||0)-(b.display_order||0));
      const ta = document.getElementById('termsText');
      ta.value = active.map(t=>t.text||'').filter(Boolean).join('\n');
      syncTermsJson();
    }catch(e){}
  })();
  // Keep serial numbers updated
  const observer = new MutationObserver(()=>{
    const rows = tbody.querySelectorAll('tr');
    let n = 1; rows.forEach(r => { const s=r.querySelector('[data-serial]'); if (s) s.textContent = String(n++); });
  });
  observer.observe(tbody, {childList:true, subtree:true});
  document.getElementById('invForm').addEventListener('submit', (e)=>{ recalc(); });
})();
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
