<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Accounts</h5>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">New Voucher</button>
    <button class="btn btn-outline-secondary btn-sm">Purchase</button>
    <button class="btn btn-outline-secondary btn-sm">Sales</button>
    <button class="btn btn-outline-secondary btn-sm">Print Ledger</button>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">Groups & Ledgers</div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          <?php foreach (($groups ?? []) as $g): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><?= htmlspecialchars($g) ?></span>
              <span class="text-muted small">₹ 0.00</span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">13,862 accounts</div>
        <button class="btn btn-outline-warning btn-sm">Create Ledger / Sub-Group</button>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Favourite Ledgers</div>
      <div class="card-body"><input class="form-control form-control-sm" placeholder="Click to enter the name of a ledger to mark it as favourite"></div>
    </div>
    <div class="card">
      <div class="card-header">Quick Access</div>
      <div class="card-body">
        <div class="row g-2">
          <?php foreach (($quick ?? []) as $q): ?>
            <div class="col-6"><a class="btn btn-sm btn-success w-100" href="#"><?= htmlspecialchars($q) ?></a></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
