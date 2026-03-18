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

  .login-container {
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

  .login-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 3rem 2.5rem;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 1;
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
    margin-bottom: 1.5rem;
  }

  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
  }

  .form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
  }

  .form-control:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .btn-login {
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

  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
  }

  .signup-link {
    text-align: center;
    color: #666;
  }

  .signup-link a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
  }

  .signup-link a:hover {
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

  .features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 2rem;
  }

  .feature-item {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 1rem;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .feature-item i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
  }

  .alert {
    margin-bottom: 1.5rem;
    border-radius: 10px;
    padding: 0.875rem 1rem;
  }

  .alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #15803d;
  }

  .alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #dc2626;
  }

  @media (max-width: 768px) {
    .right-panel {
      display: none;
    }
    
    .login-form {
      margin: 1rem;
      padding: 2rem;
    }
  }
</style>

<div class="login-container">
  <div class="left-panel">
    <div class="login-form">
      <div class="logo">
        <h1>Welcome Back</h1>
      </div>

      <?php if (!empty($_GET['verified'])): ?>
        <div class="alert alert-success" role="alert">
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
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/?action=auth&subaction=login" autocomplete="current-password" novalidate>
        <div class="form-group">
          <label for="login">Mobile number or email</label>
          <input type="text" class="form-control" id="login" name="login" placeholder="Enter your mobile or email" required autofocus>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        
        <button type="submit" class="btn-login">Sign In</button>
      </form>
      
      <div class="signup-link">
        Don't have an account? <a href="/?action=auth&subaction=register">Sign Up</a>
      </div>
    </div>
  </div>

  <div class="right-panel">
    <div class="image-content">
      <h2>Manage Your Business Better</h2>
      <p>Complete ERP + CRM solution for modern businesses</p>
      
      <div class="features">
        <div class="feature-item">
          <i>📊</i>
          <div></div>
        </div>
        <div class="feature-item">
          <i>👥</i>
          <div>Lead Management</div>
        </div>
        <div class="feature-item">
          <i>📝</i>
          <div>Quotations</div>
        </div>
        <div class="feature-item">
          <i>🛒</i>
          <div>Orders</div>
        </div>
        <div class="feature-item">
          <i>💰</i>
          <div>Billing</div>
        </div>
        <div class="feature-item">
          <i>📦</i>
          <div>Inventory</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// no client-side constraints beyond required; server validates
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
