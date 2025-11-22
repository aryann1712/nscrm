<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <select id="period" class="form-select form-select-sm" style="width: 140px;">
      <option value="this_month">This Month</option>
      <option value="last_month">Last Month</option>
      <option value="this_quarter">This Quarter</option>
      <option value="this_year">This Year</option>
      <option value="all">All</option>
    </select>
    <input type="date" id="selectDate" class="form-control form-control-sm" style="width: 150px;" placeholder="Select Date">
    <div class="input-group" style="width: 240px;">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" id="searchQ" class="form-control form-control-sm" placeholder="Search customer or invoice no" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </div>
    <select id="invoiceType" class="form-select form-select-sm" style="width: 140px;">
      <option value="all">All Invoices</option>
      <option value="party">Party Invoices</option>
      <option value="retail">POS/Retail Invoices</option>
    </select>
    <select id="executive" class="form-select form-select-sm" style="width: 160px;">
      <option value="">All Executives</option>
    </select>
    <button id="btnApply" class="btn btn-outline-secondary btn-sm">Apply</button>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">Print Settings</button>
    <button class="btn btn-outline-secondary btn-sm">Create Note</button>
    <a class="btn btn-primary btn-sm" href="/?action=invoices&subaction=create&type=Invoice">+ Create Invoice</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Create a Party Invoice</h5>
        <p class="text-muted">Generate an invoice for business clients with detailed billing.</p>
        <a href="/?action=invoices&subaction=create&type=Invoice" class="btn btn-warning">+ Create Party Invoice</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Create a POS/Retail Invoice</h5>
        <p class="text-muted">Generate a quick invoice for retail customers.</p>
        <a href="/?action=invoices&subaction=create&type=Retail" class="btn btn-warning">+ Create POS / Retail Invoice</a>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<?php
  // Compute KPI badges: count, pre-tax total, total, pending
  $kpi_count = 0; $kpi_pretax = 0.0; $kpi_total = 0.0; $kpi_pending = 0.0;
  foreach (($rows ?? []) as $r) {
    $kpi_count++;
    $amountRow = (float)($r['amount'] ?? 0);
    $receivedRow = (float)($r['received_amount'] ?? 0);
    $kpi_total += $amountRow;
    $items = json_decode($r['items_json'] ?? '[]', true) ?: [];
    $pretax = 0.0;
    foreach ($items as $it) { $pretax += (float)($it['taxable'] ?? 0); }
    $kpi_pretax += $pretax;
    $kpi_pending += max(0, $amountRow - $receivedRow);
  }
?>

<div class="d-flex flex-wrap gap-2 my-2">
  <span class="badge rounded-pill text-bg-secondary">Count <span class="ms-1" id="kpiCount"><?= number_format($kpi_count) ?></span></span>
  <span class="badge rounded-pill text-bg-secondary">Pre-Tax: ₹ <span id="kpiPretax"><?= number_format($kpi_pretax, 2) ?></span></span>
  <span class="badge rounded-pill text-bg-secondary">Total: ₹ <span id="kpiTotal"><?= number_format($kpi_total, 2) ?></span></span>
  <span class="badge rounded-pill text-bg-secondary">Pending: ₹ <span id="kpiPending"><?= number_format($kpi_pending, 2) ?></span></span>
  <div class="ms-auto"></div>
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
  <div></div>
</div>

<!-- Invoices List -->
<div class="card mt-2">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Customer</th>
            <th style="width:140px">Invoice No.</th>
            <th style="width:150px">Invoice Date</th>
            <th class="text-end" style="width:150px">Taxable (₹)</th>
            <th class="text-end" style="width:150px">Amount (₹)</th>
            <th style="width:120px">Status</th>
            <th class="text-end" style="width:150px">Pending (₹)</th>
            <th style="width:110px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows ?? [])) : ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">No invoices found for selected period.</td></tr>
          <?php else: foreach (($rows ?? []) as $r): ?>
            <?php
              $items = json_decode($r['items_json'] ?? '[]', true) ?: [];
              $pretax = 0.0; foreach ($items as $it) { $pretax += (float)($it['taxable'] ?? 0); }
              $amt = (float)($r['amount'] ?? 0);
              $received = (float)($r['received_amount'] ?? 0);
              $pending = max(0, $amt - $received);
              $status = trim((string)($r['status'] ?? 'Open'));
              if ($status === 'Open') { $status = 'Pending'; }
            ?>
            <tr data-invoice-row="<?= (int)$r['id'] ?>" data-customer="<?= htmlspecialchars((string)($r['customer'] ?? '')) ?>" data-amount="<?= number_format($amt,2,'.','') ?>" data-received="<?= number_format($received,2,'.','') ?>" data-pretax="<?= number_format($pretax,2,'.','') ?>">
              <td><?= htmlspecialchars((string)($r['customer'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($r['invoice_no'] ?? '')) ?></td>
              <td><?= !empty($r['issued_on']) ? (date('Y-m-d') === date('Y-m-d', strtotime($r['issued_on'])) ? 'Today' : date('d-M-Y', strtotime($r['issued_on']))) : '-' ?></td>
              <td class="text-end"><?= number_format($pretax, 2) ?></td>
              <td class="text-end">₹ <span data-invoice-amount><?= number_format($amt, 2) ?></span></td>
              <td>
                <a href="#" class="text-decoration-none" data-open-receive>
                  <span class="badge text-bg-warning" data-invoice-status><?= htmlspecialchars($status) ?></span>
                </a>
              </td>
              <td class="text-end">
                <a href="#" class="text-decoration-none" data-open-receive>
                  ₹ <span data-invoice-pending><?= number_format($pending, 2) ?></span>
                </a>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" title="Edit" data-open-actions><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-secondary" title="Print"><i class="bi bi-printer"></i></button>
                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-x"></i></button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer small text-muted">Showing up to 200 invoices</div>
 </div>

<script>
// Apply filters: reload with query params
const periodSel = document.getElementById('period');
const searchEl = document.getElementById('searchQ');
const applyBtn = document.getElementById('btnApply');

// Set current period from URL if any
(function initFilters(){
  const p = new URLSearchParams(window.location.search).get('period') || 'this_month';
  periodSel.value = p;
})();

applyBtn?.addEventListener('click', () => {
  const params = new URLSearchParams();
  const p = periodSel.value || 'this_month';
  const q = (searchEl?.value || '').trim();
  if (p) params.set('period', p);
  if (q) params.set('q', q);
  window.location.href = '/?action=invoices' + (params.toString() ? ('&' + params.toString()) : '');
});

searchEl?.addEventListener('keypress', (e) => { if (e.key === 'Enter') applyBtn.click(); });

// Receive Payment Modal behavior
let receiveModal;
function openReceiveModal(rowEl){
  const id = parseInt(rowEl.getAttribute('data-invoice-row')||'0',10);
  const customer = rowEl.getAttribute('data-customer')||'Invoice';
  const amount = parseFloat(rowEl.getAttribute('data-amount')||'0');
  const received = parseFloat(rowEl.getAttribute('data-received')||'0');
  const pending = Math.max(0, amount - received);
  document.getElementById('rcvInvoiceId').value = id;
  document.getElementById('rcvCustomer').textContent = customer;
  document.getElementById('rcvCurrent').textContent = '₹ ' + pending.toFixed(2);
  document.getElementById('rcvAmount').value = pending.toFixed(2);
  document.getElementById('rcvNotes').value = '';
  const m = new bootstrap.Modal(document.getElementById('receiveModal'));
  receiveModal = m; m.show();
}

document.querySelectorAll('[data-open-receive]')?.forEach(a => {
  a.addEventListener('click', (e) => {
    e.preventDefault();
    const tr = e.currentTarget.closest('tr[data-invoice-row]');
    if (tr) openReceiveModal(tr);
  });
});

document.getElementById('rcvForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id = document.getElementById('rcvInvoiceId').value;
  const amt = parseFloat(document.getElementById('rcvAmount').value||'0');
  const status = document.getElementById('rcvStatus').value;
  const notes = document.getElementById('rcvNotes').value.trim();
  const fd = new URLSearchParams();
  fd.set('id', id);
  fd.set('received_amount', isNaN(amt)? '0' : String(amt));
  fd.set('status', status);
  fd.set('notes', notes);
  try {
    const resp = await fetch('/?action=invoices&subaction=receive', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: fd.toString() });
    const data = await resp.json();
    if (!resp.ok || data.error) throw new Error(data.error || 'Failed');
    // Update row UI
    const tr = document.querySelector(`tr[data-invoice-row="${id}"]`);
    if (tr) {
      // Use server value to avoid drift (handles caps/rounding)
      if (typeof data.received_amount !== 'undefined') {
        tr.setAttribute('data-received', Number(data.received_amount).toFixed(2));
      } else {
        tr.setAttribute('data-received', (parseFloat(tr.getAttribute('data-received')||'0') + amt).toFixed(2));
      }
      const pendingSpan = tr.querySelector('[data-invoice-pending]');
      if (pendingSpan) pendingSpan.textContent = Number(data.pending || 0).toFixed(2);
      const statusBadge = tr.querySelector('[data-invoice-status]');
      if (statusBadge) {
        const newStatus = data.status || status;
        statusBadge.textContent = newStatus;
        statusBadge.classList.remove('text-bg-warning','text-bg-success','text-bg-secondary','text-bg-danger');
        if (newStatus === 'Paid') statusBadge.classList.add('text-bg-success');
        else if (newStatus === 'Partial') statusBadge.classList.add('text-bg-warning');
        else if (newStatus === 'Cancelled') statusBadge.classList.add('text-bg-secondary');
        else statusBadge.classList.add('text-bg-warning');
      }
    }
    // Recompute KPIs across the table
    updateKpis();
    if (receiveModal) receiveModal.hide();
  } catch(err){
    alert('Error: ' + (err?.message || 'Failed'));
  }
});

// Recompute KPI badges from table rows
function updateKpis(){
  const rows = document.querySelectorAll('tr[data-invoice-row]');
  let count = 0, pretax = 0, total = 0, pending = 0;
  rows.forEach(r => {
    count += 1;
    pretax += parseFloat(r.getAttribute('data-pretax')||'0');
    total += parseFloat(r.getAttribute('data-amount')||'0');
    const amt = parseFloat(r.getAttribute('data-amount')||'0');
    const rec = parseFloat(r.getAttribute('data-received')||'0');
    pending += Math.max(0, amt - rec);
  });
  const fmt = (n)=> (isNaN(n)? '0.00' : n.toFixed(2));
  const num = (n)=> (isNaN(n)? '0' : n.toString());
  const cEl = document.getElementById('kpiCount'); if (cEl) cEl.textContent = num(count);
  const pEl = document.getElementById('kpiPretax'); if (pEl) pEl.textContent = fmt(pretax);
  const tEl = document.getElementById('kpiTotal'); if (tEl) tEl.textContent = fmt(total);
  const penEl = document.getElementById('kpiPending'); if (penEl) penEl.textContent = fmt(pending);
}

// Keep summary in the status table in sync when amount changes
const rcvAmtEl = document.getElementById('rcvAmount');
function syncReceiveSummary(){
  const id = document.getElementById('rcvInvoiceId').value;
  const tr = document.querySelector(`tr[data-invoice-row="${id}"]`);
  if (!tr) return;
  const amt = parseFloat(tr.getAttribute('data-amount')||'0');
  const received = parseFloat(tr.getAttribute('data-received')||'0');
  const add = parseFloat(rcvAmtEl.value||'0');
  const pendingBefore = Math.max(0, amt - received);
  const pendingAfter = Math.max(0, pendingBefore - Math.max(0, add));
  document.getElementById('rcvPending').textContent = '₹ ' + pendingAfter.toFixed(2);
  document.getElementById('rcvReceived').textContent = '₹ ' + (received + Math.max(0, add)).toFixed(2);
  // Update current receivable badge to reflect pre-save receivable
  const currEl = document.getElementById('rcvCurrent');
  if (currEl) currEl.textContent = '₹ ' + pendingBefore.toFixed(2);
  // Auto-pick status
  const statusSel = document.getElementById('rcvStatus');
  if (statusSel) {
    if (pendingAfter <= 0.005) statusSel.value = 'Paid';
    else if (add > 0) statusSel.value = 'Partial';
    else statusSel.value = 'Pending';
  }
  document.getElementById('rcvInvNo').textContent = tr.children[1]?.textContent || '—';
}
rcvAmtEl?.addEventListener('input', syncReceiveSummary);
document.getElementById('receiveModal')?.addEventListener('shown.bs.modal', syncReceiveSummary);

// Actions (Edit) modal
function openActionsModal(tr){
  const modalEl = document.getElementById('invoiceActionsModal');
  const id = tr.getAttribute('data-invoice-row');
  const customer = tr.getAttribute('data-customer')||'';
  const invNo = tr.children[1]?.textContent || '';
  const date = tr.children[2]?.textContent || '';
  const pretax = parseFloat(tr.getAttribute('data-pretax')||'0');
  const amount = parseFloat(tr.getAttribute('data-amount')||'0');
  const received = parseFloat(tr.getAttribute('data-received')||'0');
  const pending = Math.max(0, amount - received);
  modalEl.querySelector('[data-a-customer]').textContent = customer;
  modalEl.querySelector('[data-a-invno]').textContent = invNo;
  modalEl.querySelector('[data-a-date]').textContent = date;
  modalEl.querySelector('[data-a-pretax]').textContent = '₹ ' + pretax.toFixed(2);
  modalEl.querySelector('[data-a-amount]').textContent = '₹ ' + amount.toFixed(2);
  modalEl.querySelector('[data-a-pending]').textContent = '₹ ' + pending.toFixed(2);
  const editBtn = modalEl.querySelector('[data-a-edit]');
  if (editBtn) editBtn.setAttribute('href', `/?action=invoices&subaction=edit&id=${id}`);
  const m = new bootstrap.Modal(modalEl);
  m.show();
}
document.querySelectorAll('button[data-open-actions]')?.forEach(btn => {
  btn.addEventListener('click', (e)=>{
    const tr = e.currentTarget.closest('tr[data-invoice-row]');
    if (tr) openActionsModal(tr);
  });
});
</script>

<!-- Receive Payment / Update Status Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="small text-muted">Receive payment from</div>
          <h5 class="modal-title" id="rcvCustomer">Invoice</h5>
          <div class="mt-2"><span class="badge text-bg-success">Current Receivable: <span id="rcvCurrent">₹ 0.00</span></span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rcvForm">
        <input type="hidden" id="rcvInvoiceId" value="0" />
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Payment Received</label>
            <div class="input-group" style="max-width: 320px;">
              <span class="input-group-text">₹</span>
              <input type="number" step="0.01" min="0" class="form-control" id="rcvAmount" value="0.00">
            </div>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="1" id="rcvSendThanks">
            <label class="form-check-label" for="rcvSendThanks">Send Thank you Note</label>
          </div>
          <div class="mb-3">
            <textarea id="rcvNotes" class="form-control" rows="2" placeholder="Notes"></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Update Invoice Status</label>
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:140px">Invoice No.</th>
                    <th style="width:140px">Date</th>
                    <th style="width:160px">Status</th>
                    <th class="text-end" style="width:160px">Pending Amt (₹)</th>
                    <th class="text-end" style="width:160px">Received Amt (₹)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span id="rcvInvNo">—</span></td>
                    <td><?= date('d-M-Y') ?></td>
                    <td>
                      <select id="rcvStatus" class="form-select form-select-sm">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Cancelled">Cancelled</option>
                      </select>
                    </td>
                    <td class="text-end"><span id="rcvPending">—</span></td>
                    <td class="text-end"><span id="rcvReceived">—</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="rcvUpdateAccounts" checked>
            <label class="form-check-label" for="rcvUpdateAccounts">Update Accounts</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
  <script>
  // Keep summary in the status table in sync when amount changes
  const rcvAmtEl = document.getElementById('rcvAmount');
  function syncReceiveSummary(){
    const id = document.getElementById('rcvInvoiceId').value;
    const tr = document.querySelector(`tr[data-invoice-row="${id}"]`);
    if (!tr) return;
    const amt = parseFloat(tr.getAttribute('data-amount')||'0');
    const received = parseFloat(tr.getAttribute('data-received')||'0');
    const add = parseFloat(rcvAmtEl.value||'0');
    const pendingBefore = Math.max(0, amt - received);
    const pendingAfter = Math.max(0, pendingBefore - Math.max(0, add));
    document.getElementById('rcvPending').textContent = '₹ ' + pendingAfter.toFixed(2);
    document.getElementById('rcvReceived').textContent = '₹ ' + (received + Math.max(0, add)).toFixed(2);
    document.getElementById('rcvInvNo').textContent = tr.children[1]?.textContent || '—';
  }
  rcvAmtEl?.addEventListener('input', syncReceiveSummary);
  document.getElementById('receiveModal')?.addEventListener('shown.bs.modal', syncReceiveSummary);
  </script>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

<!-- Invoice Actions Modal -->
<div class="modal fade" id="invoiceActionsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" data-a-customer>Customer</h5>
          <div class="small text-muted"><span data-a-date>—</span></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <span class="badge text-bg-light border me-2">Invoice No: <span class="fw-semibold" data-a-invno>—</span></span>
          <span class="badge text-bg-light border me-2">Pre-Tax: <span class="fw-semibold" data-a-pretax>₹ 0.00</span></span>
          <span class="badge text-bg-light border me-2">Amount: <span class="fw-semibold" data-a-amount>₹ 0.00</span></span>
          <span class="badge text-bg-success me-2">Total Receivable: <span class="fw-semibold" data-a-pending>₹ 0.00</span></span>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <div class="me-3 fw-semibold">Actions</div>
          <a href="#" class="btn btn-sm btn-outline-secondary" title="Edit" data-a-edit><i class="bi bi-pencil"></i> Edit</a>
          <a href="#" class="btn btn-sm btn-outline-secondary disabled" title="Convert (coming soon)"><i class="bi bi-arrow-repeat"></i> Convert</a>
          <a href="#" class="btn btn-sm btn-outline-danger disabled" title="Delete (coming soon)"><i class="bi bi-trash"></i> Delete</a>
        </div>
        <hr>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <div class="me-3 fw-semibold">Share</div>
          <a href="#" class="btn btn-sm btn-outline-primary disabled"><i class="bi bi-envelope"></i> Email</a>
          <a href="#" class="btn btn-sm btn-outline-success disabled"><i class="bi bi-whatsapp"></i> WhatsApp</a>
          <a href="#" class="btn btn-sm btn-outline-secondary disabled"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
          <a href="#" class="btn btn-sm btn-outline-secondary disabled"><i class="bi bi-download"></i> Download</a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
