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
    <select id="collect" class="form-select form-select-sm" style="width: 160px;">
      <option value="collect_date">Collect Date</option>
    </select>
    <input type="text" id="searchQ" class="form-control form-control-sm" placeholder="Search" style="width: 240px;"/>
    <button id="btnApply" class="btn btn-outline-secondary btn-sm">Apply</button>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">Create Reminder</button>
    <button class="btn btn-outline-secondary btn-sm">Update Records</button>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 my-2">
  <span class="badge rounded-pill text-bg-secondary">Total Receivable: ₹ <span id="kpiReceivable">0.00</span></span>
  <div class="ms-auto"></div>
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<div class="card mt-2">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:40px"><input type="checkbox"/></th>
            <th>Company</th>
            <th>Contact</th>
            <th class="text-end" style="width:160px">Amount (₹)</th>
            <th style="width:160px">Reminder</th>
            <th>Internal Notes</th>
            <th style="width:160px">Executive</th>
            <th style="width:110px" class="text-end"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows ?? [])) : ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">No recovery items yet.</td></tr>
          <?php else: foreach (($rows ?? []) as $r): ?>
            <tr>
              <td><input type="checkbox"/></td>
              <td><?= htmlspecialchars($r['company'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['contact'] ?? '') ?></td>
              <td class="text-end">₹ <?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
              <td><?= htmlspecialchars($r['reminder'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['executive'] ?? '-') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-x"></i></button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer d-flex align-items-center justify-content-between">
    <div class="text-muted small">Showing 0 items</div>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item disabled"><a class="page-link" href="#">»</a></li>
      </ul>
    </nav>
  </div>
</div>

<script>
const applyBtn = document.getElementById('btnApply');
applyBtn?.addEventListener('click', ()=>{
  const params = new URLSearchParams();
  params.set('action','recovery');
  const p = document.getElementById('period').value; if (p) params.set('period', p);
  const q = (document.getElementById('searchQ').value||'').trim(); if (q) params.set('q', q);
  window.location.search = params.toString();
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
