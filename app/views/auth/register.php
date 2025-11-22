<?php ob_start(); ?>

<style>
  .card-shadow { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
</style>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card card-shadow">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h3 class="card-title mb-0"><i class="bi bi-person-plus"></i> Create New User</h3>
          <a href="/?action=auth" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Login</a>
        </div>
        <div class="card-body">
          <?php if (!empty($_GET['error'])): ?>
            <?php
              $err = $_GET['error'];
              $msg = 'Please fix the highlighted errors and try again.';
              if ($err === 'empty')  $msg = 'All fields are required.';
              if ($err === 'email')  $msg = 'Please enter a valid email address.';
              if ($err === 'match')  $msg = 'Passwords do not match.';
              if ($err === 'exists') $msg = 'An account with this email or phone already exists.';
              if ($err === 'server') $msg = 'A server error occurred. Please try again.';
            ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($msg) ?></div>
          <?php endif; ?>

          <form method="post" action="/?action=auth&subaction=registerSubmit" autocomplete="off" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Company Name *</label>
                <input type="text" name="company_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
              <a href="/?action=auth" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Login</a>
              <button type="submit" class="btn btn-warning"><i class="bi bi-download"></i> Create User</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

