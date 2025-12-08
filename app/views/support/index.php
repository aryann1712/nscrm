<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <select id="ticketStatus" class="form-select form-select-sm" style="width: 140px;">
      <option value="">All</option>
      <option value="pending">Pending</option>
      <option value="open">Open</option>
      <option value="closed">Closed</option>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm" id="refreshTickets">Refresh</button>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Customer Support Tickets</h5>
  </div>
  <div class="card-body">
    <div id="ownerTicketsContainer">
      <div class="text-muted">Loading tickets...</div>
    </div>
  </div>
</div>

<!-- Ticket detail & status change modal -->
<div class="modal fade" id="ticketDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Support Ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-3">
          <dt class="col-sm-3">Ticket ID</dt>
          <dd class="col-sm-9" id="tdTicketId"></dd>

          <dt class="col-sm-3">Customer</dt>
          <dd class="col-sm-9" id="tdCustomer"></dd>

          <dt class="col-sm-3">Contact</dt>
          <dd class="col-sm-9" id="tdContact"></dd>

          <dt class="col-sm-3">Subject</dt>
          <dd class="col-sm-9" id="tdSubject"></dd>

          <dt class="col-sm-3">Issue Type</dt>
          <dd class="col-sm-9" id="tdIssueType"></dd>

          <dt class="col-sm-3">Priority</dt>
          <dd class="col-sm-9" id="tdPriority"></dd>

          <dt class="col-sm-3">Source</dt>
          <dd class="col-sm-9" id="tdSource"></dd>

          <dt class="col-sm-3">Attachment</dt>
          <dd class="col-sm-9" id="tdAttachment"></dd>

          <dt class="col-sm-3">Created At</dt>
          <dd class="col-sm-9" id="tdCreatedAt"></dd>

          <dt class="col-sm-3">Message</dt>
          <dd class="col-sm-9"><pre id="tdMessage" class="mb-0" style="white-space: pre-wrap; word-break: break-word; background:#f8f9fa; padding:.75rem; border-radius:.25rem;"></pre></dd>
        </dl>

        <hr>

        <h6>Conversation</h6>
        <div id="ticketMessagesContainer" class="border rounded p-2 mb-2" style="max-height: 260px; overflow-y: auto; background:#f8f9fa;"></div>
        <div class="mt-2">
          <textarea id="ticketReplyMessage" class="form-control form-control-sm" rows="2" placeholder="Type a reply to the customer..."></textarea>
          <small class="text-muted">Replies are visible to the customer in their portal.</small>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
          <label for="ticketStatusSelect" class="form-label mb-0">Status</label>
          <select id="ticketStatusSelect" class="form-select form-select-sm" style="width: 150px;">
            <option value="pending">Pending</option>
            <option value="open">Open</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <div>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-outline-primary btn-sm" id="sendTicketReplyBtn">Send Reply</button>
          <button type="button" class="btn btn-primary btn-sm" id="saveTicketStatusBtn">Save Status</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let _supportTickets = [];
let _currentTicketIndex = null;
let _currentTicketId = null;

function loadOwnerTickets() {
  const container = document.getElementById('ownerTicketsContainer');
  if (!container) return;
  container.innerHTML = '<div class="text-muted">Loading tickets...</div>';

  const statusSel = document.getElementById('ticketStatus');
  const status = statusSel ? statusSel.value : '';
  const url = '/?action=support&subaction=listTickets' + (status ? ('&status=' + encodeURIComponent(status)) : '');

  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        container.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || 'Failed to load tickets') + '</div>';
        return;
      }

      const tickets = Array.isArray(data.tickets) ? data.tickets : [];
      if (!tickets.length) {
        container.innerHTML = '<div class="alert alert-info">No tickets found for this filter.</div>';
        return;
      }
      _supportTickets = tickets;
      let html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm mb-0">';
      html += '<thead><tr><th>Ticket ID</th><th>Customer</th><th>Contact</th><th>Issue</th><th>Status</th><th>Priority</th><th>Created</th><th>Att.</th><th style="width:80px;">Action</th></tr></thead><tbody>';
      tickets.forEach((t, idx) => {
        const statusVal = (t.status || '').toLowerCase();
        let badgeClass = 'secondary';
        if (statusVal === 'pending') badgeClass = 'warning';
        else if (statusVal === 'open') badgeClass = 'info';
        else if (statusVal === 'closed') badgeClass = 'success';

        html += '<tr data-index="' + idx + '" style="cursor:pointer;">';
        html += '<td>' + (t.ticket_code || t.id || '') + '</td>';
        html += '<td>' + (t.company || '-') + '</td>';
        html += '<td>' + (t.contact_name || '') + '<br><small class="text-muted">' + (t.contact_email || '') + '</small></td>';
        html += '<td>' + (t.subject || t.issue_type || '') + '</td>';
        html += '<td><span class="badge bg-' + badgeClass + '">' + (t.status || '').toUpperCase() + '</span></td>';
        html += '<td>' + (t.priority || '') + '</td>';
        html += '<td>' + (t.created_at || '') + '</td>';
        if (t.attachment_path) {
          html += '<td><a href="' + t.attachment_path + '" target="_blank" onclick="event.stopPropagation();" title="View attachment"><i class="bi bi-paperclip"></i></a></td>';
        } else {
          html += '<td class="text-muted">-</td>';
        }
        html += '<td><button type="button" class="btn btn-outline-primary btn-xs view-ticket-btn">View</button></td>';
        html += '</tr>';
      });
      html += '</tbody></table></div>';
      container.innerHTML = html;

      // Attach click handlers for viewing ticket details
      container.querySelectorAll('tr[data-index]').forEach(function(row) {
        row.addEventListener('click', function(e) {
          // If the click was on the button or inside it, still open details
          const idx = parseInt(this.getAttribute('data-index'), 10);
          if (!isNaN(idx)) {
            openTicketDetail(idx);
          }
        });
      });
    })
    .catch(() => {
      container.innerHTML = '<div class="alert alert-danger">Error loading tickets. Please try again.</div>';
    });
}

function renderTicketMessages(messages) {
  const container = document.getElementById('ticketMessagesContainer');
  if (!container) return;

  if (!Array.isArray(messages) || messages.length === 0) {
    container.innerHTML = '<div class="text-muted">No replies yet.</div>';
    return;
  }

  let html = '';
  messages.forEach(m => {
    const sender = (m.sender_type === 'owner') ? 'You' : 'Customer';
    const alignClass = (m.sender_type === 'owner') ? 'text-end' : 'text-start';
    const badgeClass = (m.sender_type === 'owner') ? 'bg-primary' : 'bg-secondary';
    html += '<div class="mb-2 ' + alignClass + '">';
    html +=   '<div class="small text-muted">' + sender + ' • ' + (m.created_at || '') + '</div>';
    html +=   '<div class="d-inline-block px-2 py-1 text-white ' + badgeClass + '" style="border-radius: .4rem; max-width: 100%; white-space: pre-wrap; word-break: break-word;">' + (m.message || '') + '</div>';
    html += '</div>';
  });
  container.innerHTML = html;
  container.scrollTop = container.scrollHeight;
}

function loadTicketMessages() {
  if (!_currentTicketId) return;
  fetch('/?action=support&subaction=listMessages&ticket_id=' + encodeURIComponent(_currentTicketId))
    .then(res => res.json())
    .then(data => {
      if (!data || data.error) {
        const container = document.getElementById('ticketMessagesContainer');
        if (container) {
          container.innerHTML = '<div class="text-danger small">' + (data && data.error ? data.error : 'Failed to load conversation') + '</div>';
        }
        return;
      }
      renderTicketMessages(data.messages || []);
    })
    .catch(() => {
      const container = document.getElementById('ticketMessagesContainer');
      if (container) {
        container.innerHTML = '<div class="text-danger small">Error loading conversation.</div>';
      }
    });
}

function sendTicketReply() {
  if (!_currentTicketId) return;
  const textarea = document.getElementById('ticketReplyMessage');
  if (!textarea) return;
  const msg = textarea.value.trim();
  if (!msg) return;

  const params = 'ticket_id=' + encodeURIComponent(_currentTicketId) + '&message=' + encodeURIComponent(msg);
  fetch('/?action=support&subaction=addMessage', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  })
    .then(res => res.json())
    .then(data => {
      if (!data || data.error) {
        if (window.toast) {
          window.toast(data && data.error ? data.error : 'Failed to send reply', 'danger');
        }
        return;
      }
      textarea.value = '';
      loadTicketMessages();
    })
    .catch(() => {
      if (window.toast) {
        window.toast('Error sending reply', 'danger');
      }
    });
}

function openTicketDetail(idx) {
  if (!_supportTickets || !_supportTickets.length) return;
  const t = _supportTickets[idx];
  if (!t) return;
  _currentTicketIndex = idx;
  _currentTicketId = t.id || null;

  document.getElementById('tdTicketId').textContent = (t.ticket_code || t.id || '');
  document.getElementById('tdCustomer').textContent = t.company || '-';
  let contact = (t.contact_name || '');
  if (t.contact_email) {
    contact += (contact ? ' | ' : '') + t.contact_email;
  }
  document.getElementById('tdContact').textContent = contact || '-';
  document.getElementById('tdSubject').textContent = t.subject || '';
  document.getElementById('tdIssueType').textContent = t.issue_type || '';
  document.getElementById('tdPriority').textContent = t.priority || '';
  document.getElementById('tdSource').textContent = t.source || '';
  document.getElementById('tdCreatedAt').textContent = t.created_at || '';
  document.getElementById('tdMessage').textContent = t.message || '';

  const attEl = document.getElementById('tdAttachment');
  if (attEl) {
    if (t.attachment_path) {
      attEl.innerHTML = '<a href="' + t.attachment_path + '" target="_blank"><i class="bi bi-paperclip"></i> View attachment</a>';
    } else {
      attEl.innerHTML = '<span class="text-muted">No attachment</span>';
    }
  }

  const statusSel = document.getElementById('ticketStatusSelect');
  if (statusSel) {
    const s = (t.status || '').toLowerCase();
    statusSel.value = s === 'pending' || s === 'open' || s === 'closed' ? s : 'pending';
  }

  const replyTextarea = document.getElementById('ticketReplyMessage');
  const replyBtn = document.getElementById('sendTicketReplyBtn');
  const isClosed = (t.status || '').toLowerCase() === 'closed';
  if (replyTextarea) {
    replyTextarea.disabled = isClosed;
    if (isClosed) {
      replyTextarea.placeholder = 'Ticket is closed. Replies are disabled.';
    } else {
      replyTextarea.placeholder = 'Type a reply to the customer...';
    }
  }
  if (replyBtn) {
    replyBtn.disabled = isClosed;
  }

  const modalEl = document.getElementById('ticketDetailModal');
  if (!modalEl) return;
  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // Load conversation for this ticket
  loadTicketMessages();
}

function saveTicketStatus() {
  if (_currentTicketIndex === null || !_supportTickets[_currentTicketIndex]) return;
  const t = _supportTickets[_currentTicketIndex];
  const statusSel = document.getElementById('ticketStatusSelect');
  if (!statusSel) return;
  const newStatus = statusSel.value;
  if (!newStatus) return;

  const params = 'id=' + encodeURIComponent(t.id) + '&status=' + encodeURIComponent(newStatus);

  fetch('/?action=support&subaction=updateStatus', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  })
    .then(res => res.json())
    .then(data => {
      if (!data || data.error) {
        if (window.toast) {
          window.toast(data && data.error ? data.error : 'Failed to update ticket status', 'danger');
        }
        return;
      }
      if (window.toast) {
        window.toast('Ticket status updated', 'success');
      }
      // Close modal
      const modalEl = document.getElementById('ticketDetailModal');
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
      }
      // Reload list to reflect new status
      loadOwnerTickets();
    })
    .catch(() => {
      if (window.toast) {
        window.toast('Error updating ticket status', 'danger');
      }
    });
}

document.getElementById('ticketStatus')?.addEventListener('change', loadOwnerTickets);
document.getElementById('refreshTickets')?.addEventListener('click', function(e) {
  e.preventDefault();
  loadOwnerTickets();
});

document.getElementById('saveTicketStatusBtn')?.addEventListener('click', function(e) {
  e.preventDefault();
  saveTicketStatus();
});

document.getElementById('sendTicketReplyBtn')?.addEventListener('click', function(e) {
  e.preventDefault();
  sendTicketReply();
});

document.addEventListener('DOMContentLoaded', loadOwnerTickets);
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
