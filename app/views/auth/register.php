<?php ob_start(); ?>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body, html {
    height: 100%;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .register-container {
    display: flex;
    height: 100vh;
    overflow: hidden;
  }

  .left-panel {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    position: relative;
  }

  .left-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,106.7C1248,96,1344,96,1392,96L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
  }

  .register-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 2.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 480px;
    position: relative;
    z-index: 1;
    max-height: 90vh;
    overflow-y: auto;
  }

  .logo {
    text-align: center;
    margin-bottom: 2rem;
  }

  .logo h1 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
  }

  .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
  }

  .form-control:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .col-md-6 {
    flex: 1;
  }

  .btn-register {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
  }

  .btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
  }

  .login-link {
    text-align: center;
    color: #666;
  }

  .login-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
  }

  .login-link a:hover {
    text-decoration: underline;
  }

  .right-panel {
    flex: 1;
    background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
  }

  .right-panel::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
    animation: float 20s linear infinite;
  }

  @keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(-50px, -50px) rotate(360deg); }
  }

  .image-content {
    text-align: center;
    color: white;
    z-index: 1;
    padding: 2rem;
  }

  .image-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  }

  .image-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .benefits {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 2rem;
  }

  .benefit-item {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 1rem;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .benefit-item i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
  }

  .alert {
    margin-bottom: 1.5rem;
    border-radius: 10px;
    padding: 0.875rem 1rem;
  }

  .alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #dc2626;
  }

  .d-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    gap: 1rem;
  }

  .btn-secondary {
    padding: 0.75rem 1.5rem;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
  }

  @media (max-width: 768px) {
    .right-panel {
      display: none;
    }
    
    .register-form {
      margin: 1rem;
      padding: 2rem;
      max-height: none;
    }

    .row {
      flex-direction: column;
      gap: 0;
    }

    .d-flex {
      flex-direction: column;
      gap: 1rem;
    }
  }
</style>

<div class="register-container">
  <div class="left-panel">
    <div class="register-form">
      <div class="logo">
        <h1>Create Account</h1>
      </div>

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
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="company_name">Company Name *</label>
              <input type="text" name="company_name" class="form-control" placeholder="Enter company name" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="name">Full Name *</label>
              <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="email">Email Address *</label>
              <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="phone">Phone Number *</label>
              <input type="text" name="phone" class="form-control" placeholder="Enter phone number" required>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="password">Password *</label>
              <input type="password" name="password" class="form-control" placeholder="Create password" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="confirm_password">Confirm Password *</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
            </div>
          </div>
        </div>

        <div class="d-flex">
          <a href="/?action=auth" class="btn-secondary">← Back to Login</a>
          <button type="submit" class="btn-register">Create Account</button>
        </div>
      </form>
    </div>
  </div>

  <div class="right-panel">
    <div class="image-content">
      <h2>Start Your Business Journey</h2>
      <p>Join thousands of businesses managing their operations efficiently</p>
      
      <div class="benefits">
        <div class="benefit-item">
          <i>🚀</i>
          <div>Quick Setup</div>
        </div>
        <div class="benefit-item">
          <i>📈</i>
          <div>Growth Tools</div>
        </div>
        <div class="benefit-item">
          <i>🔒</i>
          <div>Secure Data</div>
        </div>
        <div class="benefit-item">
          <i>📱</i>
          <div>Mobile Ready</div>
        </div>
        <div class="benefit-item">
          <i>🎯</i>
          <div>Smart Analytics</div>
        </div>
        <div class="benefit-item">
          <i>🤝</i>
          <div>24/7 Support</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>

