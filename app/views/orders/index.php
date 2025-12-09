<?php ob_start(); ?>

<div class="row mb-3">
  <div class="col-md-6">
    <!-- <h1 class="h3 mb-0">
      <i class="bi bi-cart"></i> Orders
    </h1> -->
  </div>
  <div class="col-md-6 text-end">
    <a href="/?action=orders&subaction=create" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Enter Order
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-3">
  <div class="card-body">
    <form class="row g-2 align-items-end" method="get">
      <input type="hidden" name="action" value="orders">
      <input type="hidden" name="subaction" value="list">
      <div class="col-sm-4">
        <label class="form-label">Search</label>
        <input type="text" name="q" class="form-control" placeholder="Customer, contact, order no" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </div>
      <div class="col-sm-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php
          $statuses = [
            '',
            'Purchase order / Work Order Received',
            'Purchase order / Work Order Sent',
            'Advance Payment Received',
            'Advance Payment Sent',
            'Material Arrangement in process',
            'Material Dispatched',
            'Material Delivered',
            'Material Received',
            'Complete Payment Done',
            'Installation Start',
            'Installation is going on',
            'Installation is completed',
            'Project is handover to the client',
            'Bill Submitted',
            'Final Payment Received',
            'WIP',
            'Query',
            'Packed',
            'Cancelled',
            'Done',
          ];
          foreach ($statuses as $st): ?>
            <option value="<?= htmlspecialchars($st) ?>" <?= (($_GET['status'] ?? '')===$st?'selected':'') ?>>
              <?= $st===''?'All':htmlspecialchars($st) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2 text-end">
        <button class="btn btn-success w-100"><i class="bi bi-search"></i></button>
      </div>
    </form>
  </div>
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
</div>

<!-- List -->
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Order No.</th>
            <th>Item</th>
            <th>Due Date</th>
            <th>Qty</th>
            <th>Pndg</th>
            <th>Done</th>
            <th>Unit</th>
            <th>Total (₹)</th>
            <th>Status</th>
            <th style="width:80px">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="13" class="text-center text-muted">No orders</td></tr>
        <?php else: foreach ($orders as $o): ?>
          <tr>
            <td><?= (int)$o['id'] ?></td>
            <td><?= htmlspecialchars((string)($o['customer_name'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($o['contact_name'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($o['order_no'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($o['first_item_name'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($o['due_date'] ?? '')) ?></td>
            <td><?= number_format((float)($o['first_item_qty'] ?? 0), 0) ?></td>
            <td><?= number_format((float)(($o['first_item_qty'] ?? 0) - ($o['first_item_done_qty'] ?? 0)), 0) ?></td>
            <td><?= number_format((float)($o['first_item_done_qty'] ?? 0), 0) ?></td>
            <td><?= htmlspecialchars((string)($o['first_item_unit'] ?? '')) ?></td>
            <td><?= number_format((float)($o['total'] ?? 0), 2) ?></td>
            <td><span class="badge order-status" data-id="<?= (int)$o['id'] ?>"><?= htmlspecialchars((string)($o['status'] ?? '')) ?></span></td>
            <td class="text-nowrap d-flex gap-1">
              <button type="button" class="btn btn-sm btn-outline-success btn-cycle-status" data-id="<?= (int)$o['id'] ?>" data-status="<?= htmlspecialchars((string)($o['status'] ?? 'Pending')) ?>" title="Update status">
                <i class="bi bi-arrow-repeat"></i>
              </button>
              <a class="btn btn-sm btn-outline-warning btn-edit-preview" href="#" data-id="<?= (int)$o['id'] ?>" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return deleteOrder(<?= (int)$o['id'] ?>)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Order Preview Modal -->
<div class="modal fade" id="orderPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="opmTitle">Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 text-muted" id="opmId"></div>
        <div class="d-flex flex-wrap gap-2 mb-3" id="opmChips"></div>
        <div class="d-flex flex-wrap gap-2 mb-3" id="opmAmounts"></div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <span id="opmStatusBadge" class="badge"></span>
          <select id="opmStatusSelect" class="form-select form-select-sm" style="max-width:260px"></select>
        </div>
        <div class="d-flex flex-wrap gap-3" id="opmActions">
          <a href="#" id="opmEdit" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="#" id="opmPrint" class="btn btn-outline-warning"><i class="bi bi-printer me-1"></i>Print</a>
          <button type="button" id="opmDone" class="btn btn-outline-success"><i class="bi bi-check2 me-1"></i>Done</button>
          <button type="button" id="opmCancel" class="btn btn-outline-secondary"><i class="bi bi-slash-circle me-1"></i>Cancel</button>
          <a href="#" id="opmInvoice" class="btn btn-outline-primary"><i class="bi bi-clipboard me-1"></i>Invoice</a>
        </div>
      </div>
    </div>
  </div>
 </div>

<script>
  (function(){
    const STATUSES = [
      'Purchase order / Work Order Received',
      'Purchase order / Work Order Sent',
      'Advance Payment Received',
      'Advance Payment Sent',
      'Material Arrangement in process',
      'Material Dispatched',
      'Material Delivered',
      'Material Received',
      'Complete Payment Done',
      'Installation Start',
      'Installation is going on',
      'Installation is completed',
      'Project is handover to the client',
      'Bill Submitted',
      'Final Payment Received',
      'WIP',
      'Query',
      'Packed',
      'Cancelled',
      'Done',
    ];
    function statusBadgeClass(status){
      const s = String(status||'').toLowerCase();
      if (!s) return '';
      if (s.includes('cancel')) return 'bg-danger text-light';
      if (s.includes('done')) return 'bg-success text-light';
      return '';
    }
    function nextStatus(cur){
      const i = STATUSES.indexOf(cur||'');
      return STATUSES[(i >= 0 ? (i+1) : 0) % STATUSES.length];
    }
    async function updateStatus(id, status){
      const fd = new FormData();
      fd.set('id', id);
      fd.set('status', status);
      const res = await fetch('/?action=orders&subaction=updateStatus', { method:'POST', body: fd });
      if (!res.ok) throw new Error('Failed');
      const json = await res.json().catch(()=>({success:true}));
      if (json && json.success === false) throw new Error('Failed');
      return true;
    }
    function applyStatusBadge(el, status){
      if (!el) return;
      el.textContent = status || '';
      el.className = 'badge order-status ' + statusBadgeClass(status);
    }
    document.querySelectorAll('.order-status').forEach(badge=>{
      applyStatusBadge(badge, badge.textContent.trim());
    });
    document.querySelectorAll('.btn-cycle-status').forEach(btn=>{
      btn.addEventListener('click', async()=>{
        const id = btn.getAttribute('data-id');
        const badge = btn.closest('tr').querySelector('.order-status');
        const cur = badge ? badge.textContent.trim() : btn.getAttribute('data-status');
        const ns = nextStatus(cur);
        btn.disabled = true;
        try{
          await updateStatus(id, ns);
          if (badge) applyStatusBadge(badge, ns);
          btn.setAttribute('data-status', ns);
        }catch(e){
          alert('Could not update status');
        }finally{ btn.disabled = false; }
      });
    });

    // Edit Preview modal
    const modalEl = document.getElementById('orderPreviewModal');
    let bsModal = null;
    function ensureModal(){
      if (!bsModal && window.bootstrap) bsModal = new bootstrap.Modal(modalEl);
      return bsModal;
    }
    async function loadOrder(id){
      const res = await fetch('/?action=orders&subaction=showJson&id='+encodeURIComponent(id));
      if (!res.ok) throw new Error('Failed to load order');
      const json = await res.json();
      if (json.error) throw new Error(json.error);
      return json;
    }
    function fmtINR(n){
      const v = Number(n||0);
      return new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:2 }).format(v);
    }
    function pill(html){
      return `<span class="btn btn-outline-secondary">${html}</span>`;
    }
    document.querySelectorAll('.btn-edit-preview').forEach(a=>{
      a.addEventListener('click', async (e)=>{
        e.preventDefault();
        const id = a.getAttribute('data-id');
        try{
          const o = await loadOrder(id);
          // Title and ID
          document.getElementById('opmTitle').textContent = (o.customer_name || 'Order');
          document.getElementById('opmId').textContent = String(o.id || '');
          // Chips: Received on, Contact, Expected by
          const recv = o.created_at ? `<i class="bi bi-calendar3 me-1"></i>Received on ${new Date(o.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short'})}` : '';
          const contact = o.contact_name ? `<i class="bi bi-person me-1"></i>${o.contact_name}` : '';
          const due = o.due_date ? `<i class="bi bi-calendar3 me-1"></i>Expected by ${new Date(o.due_date).toLocaleDateString('en-GB', { day:'2-digit', month:'short'})}` : '';
          document.getElementById('opmChips').innerHTML = [recv, contact, due].filter(Boolean).map(pill).join('');
          // Amounts: Pre-Tax and Amount
          const pre = `<span class="btn btn-outline-success"><i class="bi bi-cash-coin me-1"></i>Pre-Tax ${fmtINR(o.pre_tax)}</span>`;
          const amt = `<span class="btn btn-outline-success"><i class="bi bi-cash-stack me-1"></i>Amount ${fmtINR(o.amount)}</span>`;
          document.getElementById('opmAmounts').innerHTML = pre + ' ' + amt;
          // Status dropdown + badge inside modal
          const statusSel = document.getElementById('opmStatusSelect');
          const statusBadge = document.getElementById('opmStatusBadge');
          const currentStatus = String(o.status || STATUSES[0] || '');
          if (statusSel) {
            statusSel.innerHTML = STATUSES.map(s=>`<option value="${s.replace(/"/g,'&quot;')}">${s}</option>`).join('');
            statusSel.value = currentStatus;
          }
          if (statusBadge) {
            applyStatusBadge(statusBadge, currentStatus);
          }
          if (statusSel) {
            statusSel.onchange = async ()=>{
              const ns = statusSel.value;
              try{
                await updateStatus(id, ns);
                const rowBadge = document.querySelector(`.order-status[data-id="${id}"]`);
                if (rowBadge) applyStatusBadge(rowBadge, ns);
                if (statusBadge) applyStatusBadge(statusBadge, ns);
              }catch(e){
                alert('Could not update status');
                statusSel.value = currentStatus;
              }
            };
          }
          // Wire actions
          document.getElementById('opmEdit').href = '/?action=orders&subaction=edit&id=' + encodeURIComponent(id);
          document.getElementById('opmPrint').href = '/?action=orders&subaction=print&id=' + encodeURIComponent(id);
          document.getElementById('opmInvoice').href = '/?action=orders&subaction=invoice&id=' + encodeURIComponent(id);
          ensureModal()?.show();
        }catch(err){
          alert('Could not load order details');
        }
      });
    });
  })();
 </script>

 <script>
 async function deleteOrder(id){
   if (!confirm('Delete this order? This action cannot be undone.')) return false;
   try {
     const res = await fetch('/?action=orders&subaction=delete&id='+encodeURIComponent(id), { method:'POST' });
     if (!res.ok) throw new Error('Failed');
     const data = await res.json();
     if (data && data.success) {
       if (typeof toast === 'function') toast('Order deleted','success');
       location.reload();
     } else {
       if (typeof toast === 'function') toast('Unable to delete order','danger');
       else alert('Unable to delete order');
     }
   } catch(e) {
     if (typeof toast === 'function') toast('Error deleting order','danger');
     else alert('Error deleting order');
   }
   return false;
 }
 </script>

 <script>
 // Row click opens the same preview/edit dialog as the Edit button
 document.addEventListener('DOMContentLoaded', function(){
   const table = document.querySelector('.table.table-striped.table-hover');
   if (!table) return;
   table.addEventListener('click', function(e){
     // ignore clicks on controls
     if (e.target.closest('a,button,.dropdown-menu,.dropdown-toggle,.form-check-input')) return;
     const tr = e.target.closest('tbody tr');
     if (!tr) return;
     const trigger = tr.querySelector('.btn-edit-preview');
     if (trigger) trigger.click();
   });
 });
 </script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
