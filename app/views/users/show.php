<?php ob_start(); ?>

<?php if ($userData): ?>
    <!-- User Details Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-person"></i> User Information
            </h3>
            <div class="card-tools">
                <a href="/?action=edit&id=<?= $userData['id'] ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit User
                </a>
                <a href="/?action=delete&id=<?= $userData['id'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirmDelete('Are you sure you want to delete this user?')">
                    <i class="bi bi-trash"></i> Delete User
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-hash"></i> User ID
                        </label>
                        <div class="form-control-plaintext">
                            <?= htmlspecialchars($userData['id']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Full Name
                        </label>
                        <div class="form-control-plaintext">
                            <i class="bi bi-person-circle text-primary"></i>
                            <?= htmlspecialchars($userData['name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <div class="form-control-plaintext">
                            <i class="bi bi-envelope text-info"></i>
                            <a href="mailto:<?= htmlspecialchars($userData['email']) ?>">
                                <?= htmlspecialchars($userData['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-calendar"></i> Created Date
                        </label>
                        <div class="form-control-plaintext">
                            <i class="bi bi-clock text-secondary"></i>
                            <?= date('F j, Y', strtotime('now')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/?action=index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
                <div class="btn-group" role="group">
                    <a href="/?action=edit&id=<?= $userData['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="/?action=delete&id=<?= $userData['id'] ?>" 
                       class="btn btn-danger"
                       onclick="return confirmDelete('Are you sure you want to delete this user?')">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Error Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-exclamation-triangle"></i> User Not Found
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                The requested user could not be found. It may have been deleted or the ID is invalid.
            </div>
            <a href="/?action=index" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?> 