<?php ob_start(); ?>
<!--begin::Customer Dashboard-->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">Welcome, <?= htmlspecialchars($customer['contact_name'] ?? $customer['company'] ?? 'Customer') ?></h3>
            </div>
            <div class="card-body">
                <h5>Company: <?= htmlspecialchars($customer['company'] ?? '-') ?></h5>
                <p><strong>Email:</strong> <?= htmlspecialchars($customer['contact_email'] ?? '-') ?><br>
                   <strong>Phone:</strong> <?= htmlspecialchars($customer['contact_phone'] ?? '-') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- My Quotations Section -->
<div class="row tab-pane fade section-content" id="quotes" role="tabpanel" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Quotations</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($myQuotations)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Quote No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myQuotations as $q): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($q['quote_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($q['issued_on'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($q['amount'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($q['status'] ?? '-') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No quotations found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Invoices Section -->
<div class="row tab-pane fade section-content" id="invoices" role="tabpanel" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Invoices</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($myInvoices)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myInvoices as $inv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inv['invoice_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($inv['issued_on'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($inv['amount'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-<?= $inv['status'] === 'Paid' ? 'success' : ($inv['status'] === 'Pending' ? 'warning' : 'info') ?>"><?= htmlspecialchars($inv['status'] ?? '-') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No invoices found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Orders Section -->
<div class="row tab-pane fade section-content" id="orders" role="tabpanel" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Orders</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($myOrders)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myOrders as $od): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($od['order_no'] ?? $od['id'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($od['created_at'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($od['total'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-<?= $od['status'] === 'Completed' ? 'success' : ($od['status'] === 'Pending' ? 'warning' : 'info') ?>"><?= htmlspecialchars($od['status'] ?? '-') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No orders found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Support Section -->
<div class="row tab-pane fade section-content" id="support" role="tabpanel" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Support / Help</h3>
            </div>
            <div class="card-body">
                <p>Facing issues? You can contact our support team below:</p>
                <form id="supportForm" method="post" action="#">
                    <div class="mb-3">
                        <label for="supportMessage" class="form-label">Your Message</label>
                        <textarea name="message" id="supportMessage" class="form-control" rows="5" required placeholder="Describe your issue or question..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
                <div id="supportResponse" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Settings Section -->
<div class="row tab-pane fade section-content" id="settings" role="tabpanel" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Settings</h3>
            </div>
            <div class="card-body">
                <!-- Customer Details -->
                <h5 class="mb-3">My Details</h5>
                <form id="customerDetailsForm" method="post" action="/?action=customers&subaction=updateCustomerDetails">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company" name="company" value="<?= htmlspecialchars($customer['company'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name" value="<?= htmlspecialchars($customer['contact_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($customer['contact_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($customer['contact_phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($customer['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($customer['state'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($customer['country'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="text" class="form-control" id="website" name="website" value="<?= htmlspecialchars($customer['website'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Details</button>
                    <div id="detailsResponse" class="mt-3"></div>
                </form>

                <hr class="my-4">

                <!-- Change Password -->
                <h5 class="mb-3">Change Password</h5>
                <form id="changePasswordForm" method="post" action="/?action=customers&subaction=changePassword">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_password" class="form-label">Current Password (PIN)</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter your current 4-digit PIN">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">New Password (4-digit PIN)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Enter new 4-digit PIN" maxlength="4" pattern="[0-9]{4}">
                            <small class="form-text text-muted">Must be 4 digits</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm new 4-digit PIN" maxlength="4" pattern="[0-9]{4}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                    <div id="passwordResponse" class="mt-3"></div>
                </form>

                <hr class="my-4">

                <!-- Address Details -->
                <h5 class="mb-3">My Addresses</h5>
                <?php if (!empty($customerAddresses)): ?>
                    <?php foreach ($customerAddresses as $addr): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6><?= htmlspecialchars($addr['title'] ?? 'Address') ?></h6>
                                <p class="mb-1"><?= htmlspecialchars($addr['line1'] ?? '') ?></p>
                                <?php if (!empty($addr['line2'])): ?>
                                    <p class="mb-1"><?= htmlspecialchars($addr['line2']) ?></p>
                                <?php endif; ?>
                                <p class="mb-1">
                                    <?= htmlspecialchars(trim(implode(', ', array_filter([
                                        $addr['city'] ?? '',
                                        $addr['state'] ?? '',
                                        $addr['country'] ?? '',
                                        $addr['pincode'] ?? ''
                                    ])))) ?>
                                </p>
                                <?php if (!empty($addr['gstin'])): ?>
                                    <p class="mb-0"><strong>GSTIN:</strong> <?= htmlspecialchars($addr['gstin']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">No addresses found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Support form handler
document.getElementById('supportForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('supportResponse').innerHTML = '<div class="alert alert-success">Thank you! Our team will reply soon.</div>';
    this.reset();
});

// Customer details form handler
document.getElementById('customerDetailsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('detailsResponse').innerHTML = '<div class="alert alert-success">Details updated successfully!</div>';
        } else {
            document.getElementById('detailsResponse').innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to update') + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('detailsResponse').innerHTML = '<div class="alert alert-danger">Error updating details</div>';
    });
});

// Change password form handler
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        document.getElementById('passwordResponse').innerHTML = '<div class="alert alert-danger">New passwords do not match!</div>';
        return;
    }
    
    if (!/^\d{4}$/.test(newPass)) {
        document.getElementById('passwordResponse').innerHTML = '<div class="alert alert-danger">Password must be exactly 4 digits!</div>';
        return;
    }
    
    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('passwordResponse').innerHTML = '<div class="alert alert-success">Password changed successfully! Please login again.</div>';
            this.reset();
            setTimeout(() => {
                window.location.href = '/?action=auth&subaction=logout';
            }, 2000);
        } else {
            document.getElementById('passwordResponse').innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to change password') + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('passwordResponse').innerHTML = '<div class="alert alert-danger">Error changing password</div>';
    });
});

// Default show dashboard on page load
document.addEventListener('DOMContentLoaded', function() {
    // Keep welcome card visible by default
});
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/customer_layout.php'; ?>
