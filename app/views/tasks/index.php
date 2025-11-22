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

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card task-card">
      <div class="card-header bg-white">
        <div class="task-col-title">
          <h5 class="mb-0">Inbox</h5>
          <div class="btns">
            <button class="icon-btn icon-orange" title="Create"><i class="bi bi-plus"></i></button>
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
          <div class="task-item">
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
            <button class="icon-btn icon-orange" title="Create"><i class="bi bi-plus"></i></button>
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
          <div class="task-item">
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

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
