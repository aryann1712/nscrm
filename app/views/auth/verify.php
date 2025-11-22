<?php ob_start(); ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title mb-0"><i class="bi bi-shield-lock"></i> Verify Your Email</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($_GET['resent'])): ?>
            <div class="alert alert-success" role="alert">A new verification code has been sent to your email.</div>
          <?php endif; ?>
          <?php if (!empty($_GET['error'])): ?>
            <?php
              $err = $_GET['error'];
              $msg = 'Invalid verification code.';
              if ($err === 'empty') $msg = 'Please enter the verification code.';
              if ($err === 'server') $msg = 'Server error. Please try again.';
            ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($msg) ?></div>
          <?php endif; ?>

          <p>We have sent a 6-digit verification code to <strong><?= htmlspecialchars($_GET['email'] ?? '') ?></strong>. Please enter it below to verify your account.</p>
          <form method="post" action="/?action=auth&subaction=verifySubmit" novalidate>
            <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
            <div class="mb-3">
              <label class="form-label">Verification Code</label>
              <input type="text" class="form-control" name="code" maxlength="6" inputmode="numeric" required placeholder="e.g., 123456">
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <a href="/?action=auth" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Login</a>
              <div class="d-flex align-items-center gap-2">
                <button type="submit" formaction="/?action=auth&subaction=resendOtp" formmethod="post" class="btn btn-outline-secondary">
                  <i class="bi bi-envelope"></i> Resend code
                </button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Verify</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
