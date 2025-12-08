<?php ob_start(); ?>

<!-- Dashboard Summary -->
<div class="row mb-4 section-content" id="dashboard" role="tabpanel" style="display: none;">
    <div class="col-md-4 col-12 mb-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= isset($myQuotations) ? count($myQuotations) : 0 ?></h3>
                <p>My Quotations</p>
            </div>
            <div class="small-box-icon"><i class="bi bi-receipt"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= isset($myInvoices) ? count($myInvoices) : 0 ?></h3>
                <p>My Invoices</p>
            </div>
            <div class="small-box-icon"><i class="bi bi-file-earmark-text"></i></div>
        </div>
    </div>
    <div class="col-md-4 col-12 mb-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= isset($myOrders) ? count($myOrders) : 0 ?></h3>
                <p>My Orders</p>
            </div>
            <div class="small-box-icon"><i class="bi bi-cart"></i></div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myQuotations as $q): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($q['quote_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($q['issued_on'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($q['amount'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($q['status'] ?? '-') ?></span></td>
                                        <td>
                                            <button onclick="viewQuotation(<?= (int)($q['id'] ?? 0) ?>)" class="btn btn-sm btn-outline-info me-1" title="View Full Details">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <a href="/?action=customers&subaction=printQuotation&id=<?= (int)($q['id'] ?? 0) ?>&format=pdf" target="_blank" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                        </td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myInvoices as $inv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inv['invoice_no'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($inv['issued_on'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($inv['amount'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-<?= $inv['status'] === 'Paid' ? 'success' : ($inv['status'] === 'Pending' ? 'warning' : 'info') ?>"><?= htmlspecialchars($inv['status'] ?? '-') ?></span></td>
                                        <td>
                                            <button onclick="viewInvoice(<?= (int)($inv['id'] ?? 0) ?>)" class="btn btn-sm btn-outline-info me-1" title="View Full Details">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <a href="/?action=customers&subaction=printInvoice&id=<?= (int)($inv['id'] ?? 0) ?>&format=pdf" target="_blank" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                        </td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myOrders as $od): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($od['order_no'] ?? $od['id'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($od['created_at'] ?? '-') ?></td>
                                        <td>₹ <?= htmlspecialchars(number_format((float)($od['total'] ?? 0), 2)) ?></td>
                                        <td><span class="badge bg-<?= $od['status'] === 'Completed' ? 'success' : ($od['status'] === 'Pending' ? 'warning' : 'info') ?>"><?= htmlspecialchars($od['status'] ?? '-') ?></span></td>
                                        <td>
                                            <button onclick="viewOrder(<?= (int)($od['id'] ?? 0) ?>)" class="btn btn-sm btn-outline-info me-1" title="View Full Details">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <a href="/?action=customers&subaction=printOrder&id=<?= (int)($od['id'] ?? 0) ?>&format=pdf" target="_blank" class="btn btn-sm btn-outline-primary" title="Download PDF">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                        </td>
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
                <p>Facing issues? Raise a support ticket and our team will get back to you.</p>
                <form id="supportForm" method="post" action="/?action=customers&subaction=createSupportTicket" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="issue_type" class="form-label">What is the issue about?</label>
                        <select name="issue_type" id="issue_type" class="form-select" required>
                            <option value="">Select an option</option>
                            <option value="crm_problem">CRM problem</option>
                            <option value="login_issue">Login / access issue</option>
                            <option value="billing_issue">Billing / invoice issue</option>
                            <option value="data_correction">Data correction / update</option>
                            <option value="order_issue">Order / purchase issue</option>
                            <option value="feature_request">Feature request</option>
                            <option value="other">Other reason</option>
                        </select>
                    </div>
                    <div class="mb-3" id="orderIssueGroup" style="display:none;">
                        <label for="related_order_id" class="form-label">Related Order</label>
                        <select name="related_order_id" id="related_order_id" class="form-select">
                            <option value="">Select order</option>
                            <?php if (!empty($myOrders)): ?>
                                <?php foreach ($myOrders as $od): ?>
                                    <option value="<?= (int)($od['id'] ?? 0) ?>">
                                        <?= htmlspecialchars($od['order_no'] ?? $od['id'] ?? '') ?> - <?= htmlspecialchars($od['status'] ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Choose the order related to this issue (optional but recommended).</small>
                    </div>
                    <div class="mb-3" id="otherReasonGroup" style="display:none;">
                        <label for="other_reason" class="form-label">Other reason</label>
                        <input type="text" name="other_reason" id="other_reason" class="form-control" placeholder="Briefly describe the main issue">
                    </div>
                    <div class="mb-3">
                        <label for="supportMessage" class="form-label">Describe your issue</label>
                        <textarea name="message" id="supportMessage" class="form-control" rows="5" required placeholder="Give more details about the problem..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="supportAttachment" class="form-label">Attach a file (optional)</label>
                        <input type="file" name="attachment" id="supportAttachment" class="form-control" accept=".pdf,image/*">
                        <small class="text-muted">You can upload a PDF or image (max 10MB) related to this issue.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </form>
                <div id="supportResponse" class="mt-3"></div>
                <hr class="my-4">
                <h5>My Tickets</h5>
                <div id="ticketsContainer">
                    <div class="text-muted">Loading tickets...</div>
                </div>
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
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($customer['contact_email'] ?? '') ?>" readonly>
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
                <form id="customerAddressForm" method="post" action="/?action=customers&subaction=createAddress" class="mb-4">
                    <input type="hidden" name="customer_id" value="<?= isset($customer['id']) ? (int)$customer['id'] : 0 ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addr_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="addr_title" name="title" placeholder="e.g. Head Office, Billing Address">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addr_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="addr_line1" name="line1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addr_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="addr_line2" name="line2">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="addr_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="addr_city" name="city">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="addr_state" class="form-label">State</label>
                            <input type="text" class="form-control" id="addr_state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="addr_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="addr_country" name="country">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="addr_pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="addr_pincode" name="pincode">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addr_gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control" id="addr_gstin" name="gstin">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Address</button>
                    <div id="addressResponse" class="mt-3"></div>
                </form>
                <?php if (!empty($customerAddresses)): ?>
                    <?php foreach ($customerAddresses as $addr): ?>
                        <div class="card mb-3" data-address-id="<?= (int)($addr['id'] ?? 0) ?>">
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
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="openEditAddress(<?= (int)($addr['id'] ?? 0) ?>)">Edit</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAddress(<?= (int)($addr['id'] ?? 0) ?>)">Delete</button>
                                </div>
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
    const form = this;
    const responseBox = document.getElementById('supportResponse');
    responseBox.innerHTML = '';

    const formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            responseBox.innerHTML = '<div class="alert alert-success">Your support ticket has been created. Ticket ID: ' + (data.ticket_id || '') + '</div>';
            form.reset();
            const issueSelect = document.getElementById('issue_type');
            const otherGroup = document.getElementById('otherReasonGroup');
            if (issueSelect && otherGroup) {
                otherGroup.style.display = 'none';
            }
            // Reload tickets list
            loadSupportTickets();
        } else {
            responseBox.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to create support ticket') + '</div>';
        }
    })
    .catch(() => {
        responseBox.innerHTML = '<div class="alert alert-danger">Error submitting ticket. Please try again.</div>';
    });
});

// Toggle Other reason field
document.getElementById('issue_type')?.addEventListener('change', function() {
    const otherGroup = document.getElementById('otherReasonGroup');
    const orderGroup = document.getElementById('orderIssueGroup');
    if (otherGroup) {
        if (this.value === 'other') {
            otherGroup.style.display = '';
        } else {
            otherGroup.style.display = 'none';
            const otherInput = document.getElementById('other_reason');
            if (otherInput) otherInput.value = '';
        }
    }
    if (orderGroup) {
        if (this.value === 'order_issue') {
            orderGroup.style.display = '';
        } else {
            orderGroup.style.display = 'none';
            const sel = document.getElementById('related_order_id');
            if (sel) sel.value = '';
        }
    }
});

function renderCustomerTicketMessages(messages) {
    const container = document.getElementById('customerTicketMessages');
    if (!container) return;

    if (!Array.isArray(messages) || messages.length === 0) {
        container.innerHTML = '<div class="text-muted">No replies yet.</div>';
        return;
    }

    let html = '';
    messages.forEach(m => {
        const sender = (m.sender_type === 'customer') ? 'You' : 'Owner';
        const alignClass = (m.sender_type === 'customer') ? 'text-end' : 'text-start';
        const badgeClass = (m.sender_type === 'customer') ? 'bg-primary' : 'bg-secondary';
        html += '<div class="mb-2 ' + alignClass + '">';
        html +=   '<div class="small text-muted">' + sender + ' • ' + (m.created_at || '') + '</div>';
        html +=   '<div class="d-inline-block px-2 py-1 text-white ' + badgeClass + '" style="border-radius: .4rem; max-width: 100%; white-space: pre-wrap; word-break: break-word;">' + (m.message || '') + '</div>';
        html += '</div>';
    });
    container.innerHTML = html;
    container.scrollTop = container.scrollHeight;
}

let _currentCustomerTicketId = null;
let _currentCustomerTicketStatus = null;

function openCustomerTicketDetail(ticketId, status) {
    _currentCustomerTicketId = ticketId;
    _currentCustomerTicketStatus = (status || '').toLowerCase();
    document.getElementById('customerTicketIdLabel').textContent = ticketId;

    // Load messages
    fetch('/?action=customers&subaction=listTicketMessages&ticket_id=' + encodeURIComponent(ticketId))
        .then(res => res.json())
        .then(data => {
            if (!data || data.error) {
                const container = document.getElementById('customerTicketMessages');
                if (container) {
                    container.innerHTML = '<div class="text-danger small">' + (data && data.error ? data.error : 'Failed to load conversation') + '</div>';
                }
                return;
            }
            renderCustomerTicketMessages(data.messages || []);

            const textarea = document.getElementById('customerTicketReply');
            const sendBtn = document.querySelector('#customerTicketModal .btn.btn-primary');
            const isClosed = _currentCustomerTicketStatus === 'closed';
            if (textarea) {
                textarea.disabled = isClosed;
                if (isClosed) {
                    textarea.placeholder = 'Ticket is closed.';
                } else {
                    textarea.placeholder = 'Type your message to the support team...';
                }
            }
            if (sendBtn) {
                sendBtn.disabled = isClosed;
            }
        })
        .catch(() => {
            const container = document.getElementById('customerTicketMessages');
            if (container) {
                container.innerHTML = '<div class="text-danger small">Error loading conversation.</div>';
            }
        });

    const modalEl = document.getElementById('customerTicketModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function sendCustomerTicketReply() {
    if (!_currentCustomerTicketId) return;
    if ((_currentCustomerTicketStatus || '') === 'closed') return;
    const textarea = document.getElementById('customerTicketReply');
    if (!textarea) return;
    const msg = textarea.value.trim();
    if (!msg) return;

    const formData = new FormData();
    formData.append('ticket_id', _currentCustomerTicketId);
    formData.append('message', msg);

    fetch('/?action=customers&subaction=addTicketMessage', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (!data || data.error) {
                alert(data && data.error ? data.error : 'Failed to send reply');
                return;
            }
            textarea.value = '';
            // Reload messages
            openCustomerTicketDetail(_currentCustomerTicketId);
        })
        .catch(() => {
            alert('Error sending reply. Please try again.');
        });
}

function loadSupportTickets() {
    const container = document.getElementById('ticketsContainer');
    if (!container) return;
    container.innerHTML = '<div class="text-muted">Loading tickets...</div>';

    fetch('/?action=customers&subaction=listSupportTickets')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to load tickets') + '</div>';
                return;
            }

            const tickets = Array.isArray(data.tickets) ? data.tickets : [];
            if (!tickets.length) {
                container.innerHTML = '<div class="alert alert-info">No tickets found.</div>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm mb-0">';
            html += '<thead><tr><th>Ticket ID</th><th>Issue</th><th>Status</th><th>Priority</th><th>Created</th><th>Attachment</th></tr></thead><tbody>';
            tickets.forEach(t => {
                const status = (t.status || '').toLowerCase();
                let badgeClass = 'secondary';
                if (status === 'pending') badgeClass = 'warning';
                else if (status === 'open') badgeClass = 'info';
                else if (status === 'closed') badgeClass = 'success';
                const ticketId = t.id || '';
                html += '<tr data-ticket-id="' + ticketId + '" data-status="' + (t.status || '') + '" style="cursor:pointer;">';
                html += '<td>' + (t.ticket_code || ticketId || '') + '</td>';
                html += '<td>' + (t.subject || t.issue_type || '') + '</td>';
                html += '<td><span class="badge bg-' + badgeClass + '">' + (t.status || '').toUpperCase() + '</span></td>';
                html += '<td>' + (t.priority || '') + '</td>';
                html += '<td>' + (t.created_at || '') + '</td>';
                if (t.attachment_path) {
                    html += '<td><a href="' + t.attachment_path + '" target="_blank" onclick="event.stopPropagation();" title="View attachment"><i class="bi bi-paperclip"></i></a></td>';
                } else {
                    html += '<td class="text-muted">-</td>';
                }
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            container.innerHTML = html;

            // Click to open ticket chat
            container.querySelectorAll('tr[data-ticket-id]').forEach(function(row) {
                row.addEventListener('click', function() {
                    const ticketId = this.getAttribute('data-ticket-id');
                    const status = this.getAttribute('data-status') || '';
                    if (ticketId) {
                        openCustomerTicketDetail(ticketId, status);
                    }
                });
            });
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger">Error loading tickets. Please try again.</div>';
        });
}

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

// Customer address form handler
document.getElementById('customerAddressForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const responseBox = document.getElementById('addressResponse');
    responseBox.innerHTML = '';

    const formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            responseBox.innerHTML = '<div class="alert alert-success">Address added successfully.</div>';
            form.reset();
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            responseBox.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to add address') + '</div>';
        }
    })
    .catch(() => {
        responseBox.innerHTML = '<div class="alert alert-danger">Error adding address. Please try again.</div>';
    });
});

function openEditAddress(id) {
    const card = document.querySelector('.card.mb-3[data-address-id="' + id + '"]');
    if (!card) return;

    const title = card.querySelector('h6')?.textContent.trim() || '';
    const lines = card.querySelectorAll('p.mb-1');
    const line1 = lines[0]?.textContent.trim() || '';
    let line2 = '';
    let cityStateCountryPincode = '';
    if (lines.length === 3) {
        line2 = lines[1].textContent.trim();
        cityStateCountryPincode = lines[2].textContent.trim();
    } else if (lines.length === 2) {
        cityStateCountryPincode = lines[1].textContent.trim();
    }

    let city = '', state = '', country = '', pincode = '';
    if (cityStateCountryPincode) {
        const parts = cityStateCountryPincode.split(',').map(p => p.trim()).filter(Boolean);
        city = parts[0] || '';
        state = parts[1] || '';
        country = parts[2] || '';
        pincode = parts[3] || '';
    }

    const gstWrapper = card.querySelector('p.mb-0');
    let gstin = '';
    if (gstWrapper && gstWrapper.textContent.includes('GSTIN')) {
        gstin = gstWrapper.textContent.replace('GSTIN:', '').trim();
    }

    // Fill modal form
    document.getElementById('edit_addr_id').value = id;
    document.getElementById('edit_addr_title').value = title;
    document.getElementById('edit_addr_line1').value = line1;
    document.getElementById('edit_addr_line2').value = line2;
    document.getElementById('edit_addr_city').value = city;
    document.getElementById('edit_addr_state').value = state;
    document.getElementById('edit_addr_country').value = country;
    document.getElementById('edit_addr_pincode').value = pincode;
    document.getElementById('edit_addr_gstin').value = gstin;

    const modalEl = document.getElementById('editAddressModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function deleteAddress(id) {
    if (!confirm('Are you sure you want to delete this address?')) return;
    const responseBox = document.getElementById('addressResponse');
    responseBox.innerHTML = '';
    const formData = new FormData();
    formData.append('id', id);
    fetch('/?action=customers&subaction=deleteAddress', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            responseBox.innerHTML = '<div class="alert alert-success">Address deleted successfully.</div>';
            setTimeout(() => { window.location.reload(); }, 800);
        } else {
            responseBox.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to delete address') + '</div>';
        }
    })
    .catch(() => {
        responseBox.innerHTML = '<div class="alert alert-danger">Error deleting address. Please try again.</div>';
    });
}

// View functions for quotations, invoices, orders
function viewQuotation(id) {
    fetch('/?action=customers&subaction=getQuotationDetails&id=' + id)
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || 'Failed to load quotation');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            showDetailsModal('Quotation', data.quotation, data.items, data.terms);
        })
        .catch(error => {
            alert('Error loading quotation details: ' + error.message);
            console.error('Quotation fetch error:', error);
        });
}

function viewInvoice(id) {
    fetch('/?action=customers&subaction=getInvoiceDetails&id=' + id)
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || 'Failed to load invoice');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            showDetailsModal('Invoice', data.invoice, data.items, data.terms);
        })
        .catch(error => {
            alert('Error loading invoice details: ' + error.message);
            console.error('Invoice fetch error:', error);
        });
}

function viewOrder(id) {
    fetch('/?action=customers&subaction=getOrderDetails&id=' + id)
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.error || 'Failed to load order');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            showDetailsModal('Order', data.order, data.items, data.terms);
        })
        .catch(error => {
            alert('Error loading order details: ' + error.message);
            console.error('Order fetch error:', error);
        });
}

function showDetailsModal(type, doc, items, terms) {
    const modal = document.getElementById('detailsModal');
    const modalTitle = document.getElementById('detailsModalTitle');
    const modalBody = document.getElementById('detailsModalBody');
    
    modalTitle.textContent = type + ' Details';
    
    let html = '<div class="row mb-3">';
    html += '<div class="col-md-6"><strong>Document No:</strong> ' + (doc[type === 'Quotation' ? 'quote_no' : (type === 'Invoice' ? 'invoice_no' : 'order_no')] || doc.id || '-') + '</div>';
    html += '<div class="col-md-6"><strong>Date:</strong> ' + (doc[type === 'Invoice' ? 'issued_on' : (type === 'Order' ? 'created_at' : 'issued_on')] || '-') + '</div>';
    html += '</div>';
    
    if (type === 'Quotation' || type === 'Invoice') {
        html += '<div class="row mb-3">';
        html += '<div class="col-md-6"><strong>Customer:</strong> ' + (doc.customer || '-') + '</div>';
        html += '<div class="col-md-6"><strong>Status:</strong> <span class="badge bg-info">' + (doc.status || '-') + '</span></div>';
        html += '</div>';
        if (doc.valid_till) {
            html += '<div class="mb-3"><strong>Valid Till:</strong> ' + doc.valid_till + '</div>';
        }
    } else {
        html += '<div class="row mb-3">';
        html += '<div class="col-md-6"><strong>Customer:</strong> ' + (doc.customer_name || '-') + '</div>';
        html += '<div class="col-md-6"><strong>Status:</strong> <span class="badge bg-info">' + (doc.status || '-') + '</span></div>';
        html += '</div>';
    }
    
    html += '<div class="mb-3"><strong>Amount:</strong> ₹ ' + parseFloat(doc.amount || doc.total || 0).toFixed(2) + '</div>';
    
    if (items && items.length > 0) {
        html += '<h6 class="mt-4 mb-2">Items</h6>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead><tbody>';
        items.forEach(item => {
            html += '<tr>';
            html += '<td>' + (item.name || item.item_name || item.description || '-') + '</td>';
            html += '<td>' + parseFloat(item.qty || 0).toFixed(2) + ' ' + (item.unit || 'nos') + '</td>';
            html += '<td>₹ ' + parseFloat(item.rate || 0).toFixed(2) + '</td>';
            html += '<td>₹ ' + parseFloat(item.amount || 0).toFixed(2) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    }
    
    if (terms && terms.length > 0) {
        html += '<h6 class="mt-4 mb-2">Terms & Conditions</h6><ol>';
        terms.forEach(term => {
            html += '<li>' + (typeof term === 'object' ? (term.text || '') : term) + '</li>';
        });
        html += '</ol>';
    }
    
    if (doc.notes) {
        html += '<div class="mt-3"><strong>Notes:</strong><p>' + (doc.notes || '-') + '</p></div>';
    }
    
    modalBody.innerHTML = html;
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// On load, layout.js restores last active section; just load tickets list.
document.addEventListener('DOMContentLoaded', function() {
    loadSupportTickets();
});
</script>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAddressForm" method="post" action="/?action=customers&subaction=updateAddress">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_addr_id">
                    <div class="mb-3">
                        <label for="edit_addr_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit_addr_title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="edit_addr_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="edit_addr_line1" name="line1" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_addr_line2" class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" id="edit_addr_line2" name="line2">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_addr_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="edit_addr_city" name="city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_addr_state" class="form-label">State</label>
                            <input type="text" class="form-control" id="edit_addr_state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_addr_country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="edit_addr_country" name="country">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_addr_pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="edit_addr_pincode" name="pincode">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_addr_gstin" class="form-label">GSTIN</label>
                        <input type="text" class="form-control" id="edit_addr_gstin" name="gstin">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('editAddressForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const responseBox = document.getElementById('addressResponse');
        responseBox.innerHTML = '';
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                responseBox.innerHTML = '<div class="alert alert-success">Address updated successfully.</div>';
                const modalEl = document.getElementById('editAddressModal');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                setTimeout(() => { window.location.reload(); }, 800);
            } else {
                responseBox.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to update address') + '</div>';
            }
        })
        .catch(() => {
            responseBox.innerHTML = '<div class="alert alert-danger">Error updating address. Please try again.</div>';
        });
    });
    </script>
</div>

<!-- Customer Ticket Conversation Modal -->
<div class="modal fade" id="customerTicketModal" tabindex="-1" aria-labelledby="customerTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerTicketModalLabel">Ticket Conversation - <span id="customerTicketIdLabel"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="customerTicketMessages" class="border rounded p-2 mb-2" style="max-height: 260px; overflow-y:auto; background:#f8f9fa;"></div>
                <div class="mt-2">
                    <label for="customerTicketReply" class="form-label">Your reply</label>
                    <textarea id="customerTicketReply" class="form-control" rows="2" placeholder="Type your message to the support team..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="sendCustomerTicketReply()">Send Reply</button>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalTitle">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/customer_layout.php'; ?>
