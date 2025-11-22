<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <select id="status" class="form-select form-select-sm" style="width: 140px;">
      <option value="pending">Pending</option>
      <option value="open">Open</option>
      <option value="closed">Closed</option>
    </select>
    <select id="exec" class="form-select form-select-sm" style="width: 160px;">
      <option value="">Select Executive</option>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">Add</button>
    <button class="btn btn-outline-secondary btn-sm">Print Settings</button>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Enter a Support Ticket</h5>
        <p class="text-muted">Log a new support ticket to track and resolve customer issues efficiently.</p>
        <a href="#" class="btn btn-warning btn-sm">Enter Ticket</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Enter a Customer</h5>
        <p class="text-muted">Add customer details to keep a record of your clients and maintain their tickets.</p>
        <a href="/?action=customers" class="btn btn-warning btn-sm">Enter Customer</a>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
