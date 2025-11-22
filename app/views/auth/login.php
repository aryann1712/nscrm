<?php ob_start(); ?>

<style>
  .hero-section {
    padding: 48px 0;
  }
  .feature-badge {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 6px 10px;
    background: #fff7ed;
    color: #b45309;
    font-size: 0.9rem;
    margin: 4px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .login-input {
    max-width: 260px;
  }
  .btn-orange {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #fff;
  }
  .btn-orange:hover { background-color: #d97706; border-color: #d97706; }
  .screenshot-mock {
    background: linear-gradient(145deg, #eef2ff, #f8fafc);
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    min-height: 380px;
  }
</style>

<div class="container hero-section">
  <div class="row align-items-center">
    <div class="col-lg-6">
      <h1 class="display-5 fw-bold mb-3">Easy & Effective<br>ERP + CRM Software</h1>
      <p class="lead text-muted mb-3">Manage leads, quotations, orders, billing, accounts and more from a single dashboard.</p>

      <div class="mb-3">
        <span class="feature-badge"><i class="bi bi-kanban"></i> Lead Management</span>
        <span class="feature-badge"><i class="bi bi-receipt"></i> Quotations</span>
        <span class="feature-badge"><i class="bi bi-bag"></i> Orders</span>
        <span class="feature-badge"><i class="bi bi-cash"></i> Billing</span>
        <span class="feature-badge"><i class="bi bi-wallet2"></i> Accounts</span>
        <span class="feature-badge"><i class="bi bi-boxes"></i> Inventory</span>
        <span class="feature-badge"><i class="bi bi-person-check"></i> Multi-User</span>
      </div>

      <?php if (!empty($_GET['verified'])): ?>
        <div class="alert alert-success py-2 px-3" role="alert" style="max-width:420px;">
          Your email was verified successfully. Please log in.
        </div>
      <?php endif; ?>
      <?php if (!empty($_GET['error'])): ?>
        <?php
          $err = $_GET['error'];
          $msg = 'Invalid mobile/email or password. Please try again.';
          if ($err === 'empty')  $msg = 'Both fields are required.';
          if ($err === 'pin')    $msg = 'Invalid input.';
          if ($err === 'server') $msg = 'Server error during login. Please try again.';
          if ($err === 'invalid') $msg = 'Invalid mobile/email or password.';
        ?>
        <div class="alert alert-danger py-2 px-3" role="alert" style="max-width:420px;">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <form class="d-flex align-items-center gap-2 mb-2" method="post" action="/?action=auth&subaction=login" autocomplete="current-password" novalidate>
        <input type="text" class="form-control login-input" id="login" name="login" placeholder="Mobile number or email" required autofocus>
        <input type="password" class="form-control" style="max-width:220px" id="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn btn-orange">Login</button>
        <a href="/?action=auth&subaction=register" class="btn btn-outline-secondary">Sign Up</a>
      </form>
      <small class="text-muted d-block" style="max-width:420px;">Use your registered mobile number or email and your account password to access your dashboard.</small>
    </div>

    <div class="col-lg-6 mt-4 mt-lg-0">
      <div class="screenshot-mock p-3">
        <div class="h-100 w-100 d-flex align-items-center justify-content-center text-muted">
          <div class="text-center">
            <i class="bi bi-window-stack" style="font-size: 3rem;"></i>
            <p class="mb-0">Your Business Summary & Dashboard Preview</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// no client-side constraints beyond required; server validates
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
