<?php ob_start(); ?>

<!-- Edit User Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-pencil"></i> Edit User Information
        </h3>
        <div class="card-tools">
            <a href="/?action=show&id=<?= $userData['id'] ?>" class="btn btn-info">
                <i class="bi bi-eye"></i> View User
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="/?action=update&id=<?= $userData['id'] ?>" data-validate>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="bi bi-person"></i> Full Name
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($userData['name']) ?>"
                               placeholder="Enter full name"
                               required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($userData['email']) ?>"
                               placeholder="Enter email address"
                               required>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                        <a href="/?action=index" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Users
                        </a>
                        <a href="/?action=show&id=<?= $userData['id'] ?>" class="btn btn-info">
                            <i class="bi bi-eye"></i> View User
                        </a>
                        <button type="reset" class="btn btn-warning">
                            <i class="bi bi-arrow-clockwise"></i> Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <small class="text-muted">
            <i class="bi bi-info-circle"></i> 
            All fields marked with * are required. Changes will be saved immediately.
        </small>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>