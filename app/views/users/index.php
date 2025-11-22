<?php ob_start(); ?>

<!-- Users Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-people"></i> Users List
        </h3>
        <div class="card-tools">
            <a href="/?action=create" class="btn btn-primary">
                <i class="bi bi-plus"></i> Create User
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No users found. <a href="/?action=create">Create your first user</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped data-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-hash"></i> ID</th>
                            <th><i class="bi bi-person"></i> Name</th>
                            <th><i class="bi bi-envelope"></i> Email</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td>
                                <i class="bi bi-person-circle text-primary"></i>
                                <?= htmlspecialchars($user['name']) ?>
                            </td>
                            <td>
                                <i class="bi bi-envelope text-info"></i>
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/?action=show&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View User">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/?action=edit&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/?action=delete&id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Delete User"
                                       onclick="return confirmDelete('Are you sure you want to delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> 
                Total Users: <?= count($users) ?>
            </small>
            <a href="/?action=create" class="btn btn-success">
                <i class="bi bi-plus"></i> Add New User
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>