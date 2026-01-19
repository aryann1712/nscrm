<?php
ob_start();
require_once __DIR__ . '/../../helpers/permissions.php';
$canEditQuotes   = function_exists('user_can') ? user_can('crm','quotations','edit') : true;
$canDeleteQuotes = function_exists('user_can') ? user_can('crm','quotations','full') : true;
$canCreateInvoiceFromQuote = function_exists('user_can') ? user_can('crm','invoices','edit') : true;
?>

<div class="card">
	<div class="card-header d-flex align-items-center justify-content-between">
		<div class="d-flex gap-2">
			<div class="btn-group" role="group">
				<a href="/?action=quotations&tab=all" class="btn btn-outline-secondary <?= (($_GET['tab'] ?? 'quotations')==='all'?'active':'') ?>">All</a>
				<a href="/?action=quotations&tab=quotations" class="btn btn-outline-secondary <?= (($_GET['tab'] ?? 'quotations')==='quotations'?'active':'') ?>">Quotations</a>
				<a href="/?action=quotations&tab=proforma" class="btn btn-outline-secondary <?= (($_GET['tab'] ?? 'quotations')==='proforma'?'active':'') ?>">Proforma Invoices</a>
			</div>
			<div class="ms-2">
				<select class="form-select" onchange="location.href='/?action=quotations&tab=<?= urlencode($_GET['tab'] ?? 'quotations') ?>&period='+this.value">
					<?php $p = $_GET['period'] ?? 'this_month'; ?>
					<option value="today" <?= $p==='today'?'selected':'' ?>>Today</option>
					<option value="this_week" <?= $p==='this_week'?'selected':'' ?>>This Week</option>
					<option value="this_month" <?= $p==='this_month'?'selected':'' ?>>This Month</option>
					<option value="all" <?= $p==='all'?'selected':'' ?>>All</option>
				</select>
			</div>
		</div>
		<div class="d-flex align-items-center gap-3">
			<div class="text-nowrap"><span class="badge bg-secondary">Count <?= (int)$stats['count'] ?></span></div>
			<div class="text-nowrap"><span class="badge bg-warning text-dark">Total ₹ <?= number_format((float)$stats['total'],2) ?></span></div>
			<form class="d-flex" method="get">
				<input type="hidden" name="action" value="quotations"/>
				<input type="hidden" name="tab" value="<?= htmlspecialchars($_GET['tab'] ?? 'quotations') ?>"/>
				<input type="hidden" name="period" value="<?= htmlspecialchars($_GET['period'] ?? 'this_month') ?>"/>
				<input class="form-control" name="q" placeholder="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
			</form>
			<?php if ($canEditQuotes): ?>
			<a href="/?action=quotations&subaction=create" class="btn btn-primary"><i class="bi bi-plus"></i> Create Quotation</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover mb-0">
				<thead>
					<tr>
						<th>Quote No.</th>
						<th>Customer</th>
						<th class="text-end">Amount (₹)</th>
						<th>Valid till</th>
						<th>Issued on</th>
						<th>Issued by</th>
						<th>Type</th>
						<th>Executive</th>
						<th>Response</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows as $r): ?>
					<tr>
						<td><?= htmlspecialchars($r['quote_no']) ?></td>
						<td><?= htmlspecialchars($r['customer']) ?></td>
						<td class="text-end"><?= number_format((float)$r['amount'],2) ?></td>
						<td><?= htmlspecialchars($r['valid_till']) ?></td>
						<td><?= htmlspecialchars($r['issued_on']) ?></td>
						<td><?= htmlspecialchars($r['issued_by']) ?></td>
						<td><?= htmlspecialchars($r['type']) ?></td>
						<td><?= htmlspecialchars($r['executive']) ?></td>
						<td><?= htmlspecialchars($r['response'] ?? '-') ?></td>
						<td>
							<?php
								$status = isset($r['status']) && $r['status'] !== null ? (string)$r['status'] : 'Open';
								$map = [
									'Open' => 'secondary',
									'Expired' => 'warning',
									'Cancelled' => 'dark',
									'Rejected' => 'danger',
									'Replaced' => 'info',
									'Converted' => 'success',
								];
								$cls = $map[$status] ?? 'secondary';
							?>
							<span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($status) ?></span>
						</td>
						<td class="text-nowrap d-flex gap-1">
							<button class="btn btn-sm btn-info" title="Send Reminder" onclick="return sendQuoteEmail(<?= (int)$r['id'] ?>, '<?= htmlspecialchars($r['customer'], ENT_QUOTES) ?>')">
								<i class="bi bi-envelope"></i>
							</button>
							<a
								class="btn btn-sm btn-warning js-quote-quickview"
								title="Edit"
								href="#"
								data-id="<?= (int)$r['id'] ?>"
								data-quote_no="<?= htmlspecialchars($r['quote_no'], ENT_QUOTES) ?>"
								data-customer="<?= htmlspecialchars($r['customer'], ENT_QUOTES) ?>"
								data-amount="<?= number_format((float)$r['amount'],2) ?>"
								data-valid_till="<?= htmlspecialchars($r['valid_till'], ENT_QUOTES) ?>"
								data-issued_on="<?= htmlspecialchars($r['issued_on'], ENT_QUOTES) ?>"
								data-issued_by="<?= htmlspecialchars($r['issued_by'], ENT_QUOTES) ?>"
								data-type="<?= htmlspecialchars($r['type'], ENT_QUOTES) ?>"
								data-executive="<?= htmlspecialchars($r['executive'], ENT_QUOTES) ?>"
								data-status="<?= htmlspecialchars($status, ENT_QUOTES) ?>"
								data-edit_url="/?action=quotations&subaction=edit&id=<?= (int)$r['id'] ?>"
							>
								<i class="bi bi-pencil"></i>
							</a>
							<?php if ($canDeleteQuotes): ?>
							<button class="btn btn-sm btn-danger" title="Delete" onclick="return deleteQuote(<?= (int)$r['id'] ?>)">
								<i class="bi bi-trash"></i>
							</button>
							<?php endif; ?>
							<div class="btn-group">
								<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Status</button>
								<ul class="dropdown-menu dropdown-menu-end">
									<?php foreach (['Open','Expired','Cancelled','Rejected','Replaced','Converted'] as $st): ?>
									<li><a class="dropdown-item" href="#" onclick="return setQuoteStatus(<?= (int)$r['id'] ?>,'<?= $st ?>')"><?= $st ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if (empty($rows)): ?>
					<tr><td colspan="12" class="text-center text-muted">No records</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
// Quick View Modal for quotations
const quickViewHtml = `
<div class="modal fade" id="quoteQuickView" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span id="qvCustomer"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="small text-muted">Quote #<span id="qvQuoteNo"></span> • <span id="qvIssuedOn"></span> • <span id="qvIssuedBy"></span></div>
          <span class="badge" id="qvStatus"></span>
        </div>
        <div class="mb-2">
          <div class="fw-semibold">Contact Details</div>
          <div class="small text-muted">Executive: <span id="qvExecutive"></span></div>
          <div class="small text-muted">Valid Till: <span id="qvValidTill"></span></div>
          <div class="small text-muted">Type: <span id="qvType"></span></div>
        </div>
        <div class="mb-3">
          <div class="fw-semibold mb-1">Financials</div>
          <span class="badge bg-success-subtle text-success border">Amount ₹ <span id="qvAmount"></span></span>
        </div>
        <div class="fw-semibold mb-1">Actions</div>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <a id="qvEdit" class="btn btn-outline-primary"><i class="bi bi-pencil-square"></i> Edit</a>
          <button id="qvDelete" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
          <button id="qvInvoice" class="btn btn-outline-success"><i class="bi bi-receipt"></i> Invoice</button>
          <button id="qvOrder" class="btn btn-outline-warning"><i class="bi bi-cart"></i> Order</button>
        </div>
        <div class="fw-semibold mb-1">Share</div>
        <div class="d-flex flex-wrap gap-2">
          <button id="qvShareWhatsApp" class="btn btn-outline-dark"><i class="bi bi-whatsapp"></i> WhatsApp</button>
          <button id="qvShareEmail" class="btn btn-outline-primary"><i class="bi bi-envelope"></i> Email</button>
          <button id="qvSharePrint" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>`;
let qvModalInstance = null;
function ensureQuickViewModal(){
  if (!document.getElementById('quoteQuickView')) {
    document.body.insertAdjacentHTML('beforeend', quickViewHtml);
  }
  if (!qvModalInstance && window.bootstrap && bootstrap.Modal) {
    qvModalInstance = new bootstrap.Modal(document.getElementById('quoteQuickView'));
  }
  return qvModalInstance;
}
// Pre-create after DOM is ready (Bootstrap script is loaded later in layout before user interaction)
document.addEventListener('DOMContentLoaded', function(){
  ensureQuickViewModal();
});

function badgeClassForStatus(st){
  const map = {Open:'bg-secondary',Expired:'bg-warning',Cancelled:'bg-dark',Rejected:'bg-danger',Replaced:'bg-info',Converted:'bg-success'};
  return map[st]||'bg-secondary';
}

document.addEventListener('click', function(e){
  const a = e.target.closest('.js-quote-quickview');
  if (!a) return;
  e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) e.stopImmediatePropagation();
  const d = a.dataset;
  document.getElementById('qvCustomer').textContent = d.customer||'';
  document.getElementById('qvQuoteNo').textContent = d.quote_no||'';
  document.getElementById('qvIssuedOn').textContent = d.issued_on||'';
  document.getElementById('qvIssuedBy').textContent = d.issued_by||'';
  document.getElementById('qvExecutive').textContent = d.executive||'';
  document.getElementById('qvValidTill').textContent = d.valid_till||'';
  document.getElementById('qvType').textContent = d.type||'';
  document.getElementById('qvAmount').textContent = d.amount||'';
  const st = d.status||'Open';
  const stEl = document.getElementById('qvStatus');
  stEl.className = 'badge ' + badgeClassForStatus(st);
  stEl.textContent = st;
  // Actions
  const id = d.id;
  const editUrl = d.edit_url || `/?action=quotations&subaction=edit&id=${id}`;
  document.getElementById('qvEdit').setAttribute('href', editUrl);
  document.getElementById('qvDelete').onclick = ()=> deleteQuote(id);
  document.getElementById('qvInvoice').onclick = ()=> {
    // Open invoice creation with prefilled data from this quotation
    const url = `/?action=invoices&subaction=create&from_quote=${id}`;
    window.location.href = url;
  };
  document.getElementById('qvOrder').onclick = ()=> { toast('Order flow coming soon','info'); };
  // Share
  document.getElementById('qvShareWhatsApp').onclick = ()=> {
    const text = encodeURIComponent(`Quotation #${d.quote_no} for ${d.customer}\nAmount: ₹ ${d.amount}\nLink: ${location.origin}/?action=quotations&subaction=edit&id=${id}`);
    const url = `https://wa.me/?text=${text}`;
    window.open(url, '_blank');
  };
  document.getElementById('qvShareEmail').onclick = ()=> {
    const subject = encodeURIComponent(`Quotation #${d.quote_no} - ${d.customer}`);
    const body = encodeURIComponent(`Hello,\n\nPlease find the quotation details below:\n\nQuote No: ${d.quote_no}\nCustomer: ${d.customer}\nAmount: ₹ ${d.amount}\nValid Till: ${d.valid_till}\n\nView/Edit: ${location.origin}/?action=quotations&subaction=edit&id=${id}\n\nThanks`);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
  };
  document.getElementById('qvSharePrint').onclick = ()=> {
    const printUrl = `/?action=quotations&subaction=print&id=${id}`;
    window.open(printUrl, '_blank');
  };
  // PDF button removed
  const inst = ensureQuickViewModal();
  if (inst && inst.show) { inst.show(); }
  else {
    // Fallback: force display if bootstrap is not yet ready
    const m = document.getElementById('quoteQuickView');
    if (m) { m.classList.add('show'); m.style.display = 'block'; m.removeAttribute('aria-hidden'); m.setAttribute('role','dialog'); }
  }
});
async function sendQuoteEmail(id, customer){
  const email = prompt(`Enter ${customer}'s email address:`);
  if (!email) return false;
  try {
    const fd = new FormData();
    fd.append('email', email);
    const res = await fetch(`/?action=quotations&subaction=sendEmail&id=${id}`, { method: 'POST', body: fd });
    if (!res.ok) throw new Error('Failed');
    const data = await res.json();
    if (data.success) toast('Reminder email sent','success'); else toast('Failed to send email','danger');
  } catch(e){
    toast('Error sending email','danger');
  }
  return false;
}
async function deleteQuote(id){
  if (!confirm('Delete this quotation? This action cannot be undone.')) return false;
  try {
    const res = await fetch(`/?action=quotations&subaction=delete&id=${id}`, { method: 'POST' });
    if (!res.ok) throw new Error('Failed');
    const data = await res.json();
    if (data.success) { toast('Quotation deleted','success'); location.reload(); }
    else { toast('Unable to delete quotation','danger'); }
  } catch(e) {
    toast('Error deleting quotation','danger');
  }
  return false;
}
async function setQuoteStatus(id, status){
  try {
    const fd = new FormData();
    fd.append('status', status);
    const res = await fetch(`/?action=quotations&subaction=updateStatus&id=${id}`, { method: 'POST', body: fd });
    if (!res.ok) throw new Error('Failed');
    const data = await res.json();
    if (data.success) { toast('Status updated','success'); location.reload(); }
    else { toast('Unable to update status','danger'); }
  } catch(e) {
    toast('Error updating status','danger');
  }
  return false;
}
</script>

<script>
// Row click opens the same Quick View edit dialog as the edit icon
document.addEventListener('DOMContentLoaded', function(){
  const table = document.querySelector('.table.table-hover');
  if (!table) return;
  table.addEventListener('click', function(e){
    // ignore if user clicked a control
    if (e.target.closest('a,button,.dropdown-menu,.dropdown-toggle,.form-check-input')) return;
    const tr = e.target.closest('tbody tr');
    if (!tr) return;
    const trigger = tr.querySelector('.js-quote-quickview');
    if (trigger) { trigger.click(); }
  });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

