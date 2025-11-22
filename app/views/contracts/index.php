<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="btn-group btn-group-sm" role="group">
      <button type="button" class="btn btn-outline-secondary active">Default View</button>
      <button type="button" class="btn btn-outline-secondary">Tile View</button>
    </div>
    <select id="status" class="form-select form-select-sm" style="width: 120px;">
      <option value="active">Active</option>
      <option value="expired">Expired</option>
    </select>
    <select id="period" class="form-select form-select-sm" style="width: 140px;">
      <option value="this_month">This Month</option>
      <option value="next_month">Next Month</option>
      <option value="twelve_months">12 Months</option>
      <option value="all">All</option>
    </select>
  </div>
  <div>
    <a href="#" class="btn btn-warning btn-sm">Add Contract</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-body">
        <button class="btn btn-outline-warning btn-sm mb-3">+ Click here to add a contract.</button>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
          <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3">
    <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
      <div>Renewals This Month</div><div class="fw-bold">0 (₹ 0)</div>
    </div></div>
    <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
      <div>Renewals Next Month</div><div class="fw-bold">0 (₹ 0)</div>
    </div></div>
    <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
      <div>Renewals 12 Months</div><div class="fw-bold">0 (₹ 0)</div>
    </div></div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
