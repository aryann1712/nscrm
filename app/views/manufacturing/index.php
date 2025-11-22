<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <div class="btn-group btn-group-sm" role="group">
      <button type="button" class="btn btn-outline-secondary active">Pending</button>
      <button type="button" class="btn btn-outline-secondary">History</button>
    </div>
    <div class="btn-group btn-group-sm" role="group">
      <button type="button" class="btn btn-outline-secondary">WIP</button>
      <button type="button" class="btn btn-outline-secondary">Overdue</button>
      <button type="button" class="btn btn-outline-secondary">Total Jobs</button>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">Quick Data</button>
    <a class="btn btn-warning btn-sm" href="#">+ Create Job</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Create a Production Job</h5>
        <p class="text-muted">Create a production job to keep your tasks in progress and optimize workflow.</p>
        <a href="#" class="btn btn-warning btn-sm">Create Job</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h5>Add a Product</h5>
        <p class="text-muted">Enter a product for which you want to launch a production job.</p>
        <a href="/?action=inventory&subaction=create" class="btn btn-warning btn-sm">Add Product</a>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
