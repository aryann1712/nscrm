<?php ob_start(); ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Company Employee</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/?action=settings">Settings</a></li>
                        <li class="breadcrumb-item active">Add Employee</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Create New Company Employee</h3>
                            <p class="text-muted mb-0"><small>Note: This is for company employees only. Customers are managed separately in the Customers section.</small></p>
                        </div>
                        <div class="card-body">
                            <form action="/?action=settings&subaction=store" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="/?action=settings" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to Settings
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-save me-1"></i> Create Employee
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
