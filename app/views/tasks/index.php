<?php ob_start(); ?>

<style>
  .task-card { min-height: 68vh; }
  .task-item { border-bottom: 1px solid #f0f0f0; padding: .6rem .25rem; }
  .task-item:last-child { border-bottom: 0; }
  .task-title { font-weight: 600; }
  .task-sub { color: #6c757d; font-size: .875rem; }
  .icon-btn { width: 26px; height: 26px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; border: 0; }
  .icon-btn i { font-size: 14px; }
  .icon-orange { background: #ffedd5; color: #f97316; }
  .icon-blue { background: #dbeafe; color: #2563eb; }
  .icon-red { background: #ffe4e6; color: #e11d48; }
  .icon-green { background: #dcfce7; color: #16a34a; }
  .task-col-title { display:flex; align-items:center; justify-content: space-between; }
  .task-col-title .btns { display:flex; gap:.35rem; }
</style>

<?php $employees = $employees ?? []; $currentUserId = (int)($_SESSION['user']['id'] ?? 0); $isOwner = (int)($_SESSION['user']['is_owner'] ?? 0) === 1; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card task-card">
      <div class="card-header bg-white">
        <div class="task-col-title">
          <h5 class="mb-0">Inbox</h5>
          <div class="btns">
            <button class="icon-btn icon-orange js-task-create" type="button" title="Create"><i class="bi bi-plus"></i></button>
            <button class="icon-btn icon-blue" title="Refresh"><i class="bi bi-arrow-clockwise"></i></button>
            <button class="icon-btn icon-red" title="Delete"><i class="bi bi-trash"></i></button>
            <a href="#" class="icon-btn icon-green" title="Export"><i class="bi bi-download"></i></a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($inbox ?? [])) : ?>
          <div class="text-muted">No tasks.</div>
        <?php else: foreach ($inbox as $t): ?>
          <div class="task-item js-task-item"
               data-id="<?= htmlspecialchars($t['id']) ?>"
               data-title="<?= htmlspecialchars($t['title']) ?>"
               data-description="<?= htmlspecialchars($t['description'] ?? '') ?>"
               data-due="<?= htmlspecialchars($t['due'] ?? '') ?>"
               data-time="<?= htmlspecialchars($t['time_raw'] ?? '') ?>"
               data-priority="<?= htmlspecialchars($t['priority'] ?? 'medium') ?>"
               data-assigned-id="<?= (int)($t['assigned_to_user_id'] ?? 0) ?>"
               data-status="<?= htmlspecialchars($t['status'] ?? 'open') ?>">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="task-title">
                  <a href="#" class="text-decoration-none"><?= htmlspecialchars($t['title']) ?></a>
                  <span class="text-secondary small">#<?= htmlspecialchars($t['id']) ?></span>
                </div>
                <div class="task-sub">
                  <?php if (!empty($t['time'])): ?>
                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($t['time']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="text-end">
                <div>
                  <span class="badge text-bg-light border"><i class="bi bi-calendar2 me-1"></i> <?= date('d M y', strtotime($t['due'])) ?></span>
                </div>
                <div class="small text-muted mt-1"><?= htmlspecialchars($t['assignee']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card task-card">
      <div class="card-header bg-white">
        <div class="task-col-title">
          <h5 class="mb-0">Outbox</h5>
          <div class="btns">
            <button class="icon-btn icon-orange js-task-create" type="button" title="Create"><i class="bi bi-plus"></i></button>
            <button class="icon-btn icon-blue" title="Refresh"><i class="bi bi-arrow-clockwise"></i></button>
            <button class="icon-btn icon-red" title="Delete"><i class="bi bi-trash"></i></button>
            <a href="#" class="icon-btn icon-green" title="Export"><i class="bi bi-download"></i></a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($outbox ?? [])) : ?>
          <div class="text-muted">No tasks.</div>
        <?php else: foreach ($outbox as $t): ?>
          <div class="task-item js-task-item"
               data-id="<?= htmlspecialchars($t['id']) ?>"
               data-title="<?= htmlspecialchars($t['title']) ?>"
               data-description="<?= htmlspecialchars($t['description'] ?? '') ?>"
               data-due="<?= htmlspecialchars($t['due'] ?? '') ?>"
               data-time="<?= htmlspecialchars($t['time_raw'] ?? '') ?>"
               data-priority="<?= htmlspecialchars($t['priority'] ?? 'medium') ?>"
               data-assigned-id="<?= (int)($t['assigned_to_user_id'] ?? 0) ?>"
               data-status="<?= htmlspecialchars($t['status'] ?? 'open') ?>">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="task-title">
                  <a href="#" class="text-decoration-none"><?= htmlspecialchars($t['title']) ?></a>
                  <span class="text-secondary small">#<?= htmlspecialchars($t['id']) ?></span>
                </div>
                <div class="task-sub">
                  <?php if (!empty($t['time'])): ?>
                    <i class="bi bi-clock me-1"></i><?= htmlspecialchars($t['time']) ?>
                  <?php endif; ?>
                </div>
              </div>
              <div class="text-end">
                <div>
                  <?php if (($t['priority'] ?? '') === 'high'): ?>
                    <span class="badge text-bg-success"><i class="bi bi-arrow-up me-1"></i> High</span>
                  <?php endif; ?>
                  <span class="badge text-bg-light border"><i class="bi bi-calendar2 me-1"></i> <?= date('d M y', strtotime($t['due'])) ?></span>
                </div>
                <div class="small text-muted mt-1"><?= htmlspecialchars($t['assignee']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="taskCreateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/?action=tasks&amp;subaction=create">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Due Time</label>
              <input type="time" name="due_time" class="form-control">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select name="priority" class="form-select">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Assign To</label>
              <select name="assigned_to_user_id" class="form-select">
                <?php foreach ($employees as $emp): $empId = (int)($emp['id'] ?? 0); ?>
                  <option value="<?= $empId ?>" <?= $empId === $currentUserId ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string)($emp['name'] ?? ($emp['email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($emp['is_owner'])): ?>(Admin)<?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Task</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="taskEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="/?action=tasks&amp;subaction=update">
        <input type="hidden" name="id" id="taskEditId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" id="taskEditTitle" class="form-control" <?= $isOwner ? '' : 'readonly' ?>>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="taskEditDescription" class="form-control" rows="3" <?= $isOwner ? '' : 'readonly' ?>></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" id="taskEditDueDate" class="form-control" <?= $isOwner ? '' : 'readonly' ?>>
            </div>
            <div class="col-md-6">
              <label class="form-label">Due Time</label>
              <input type="time" name="due_time" id="taskEditDueTime" class="form-control" <?= $isOwner ? '' : 'readonly' ?>>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select name="priority" id="taskEditPriority" class="form-select" <?= $isOwner ? '' : 'disabled' ?> >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Assign To</label>
              <select name="assigned_to_user_id" id="taskEditAssignee" class="form-select" <?= $isOwner ? '' : 'disabled' ?> >
                <?php foreach ($employees as $emp): $empId = (int)($emp['id'] ?? 0); ?>
                  <option value="<?= $empId ?>">
                    <?= htmlspecialchars((string)($emp['name'] ?? ($emp['email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($emp['is_owner'])): ?>(Admin)<?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label d-block">Status</label>
            <select name="status" id="taskEditStatus" class="form-select form-select-sm">
              <option value="open">Open</option>
              <option value="in_progress">In Progress</option>
              <option value="done">Done</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-task-create').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var modalEl = document.getElementById('taskCreateModal');
        if (!modalEl) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    });

    document.querySelectorAll('.js-task-item').forEach(function(item) {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        var id = this.getAttribute('data-id');
        if (!id) return;
        document.getElementById('taskEditId').value = id;
        document.getElementById('taskEditTitle').value = this.getAttribute('data-title') || '';
        document.getElementById('taskEditDescription').value = this.getAttribute('data-description') || '';
        var due = this.getAttribute('data-due') || '';
        document.getElementById('taskEditDueDate').value = due;
        document.getElementById('taskEditDueTime').value = this.getAttribute('data-time') || '';
        var pr = this.getAttribute('data-priority') || 'medium';
        var prSel = document.getElementById('taskEditPriority');
        if (prSel) prSel.value = pr;
        var assId = this.getAttribute('data-assigned-id') || '';
        var asSel = document.getElementById('taskEditAssignee');
        if (asSel && assId) asSel.value = assId;
        var st = this.getAttribute('data-status') || 'open';
        var stSel = document.getElementById('taskEditStatus');
        if (stSel) stSel.value = st;

        var modalEl = document.getElementById('taskEditModal');
        if (!modalEl) return;
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      });
    });
  });
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
