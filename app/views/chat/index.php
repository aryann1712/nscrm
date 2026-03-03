<?php ob_start(); ?>

<?php
$mode = ($_GET['type'] ?? 'global') === 'dm' ? 'dm' : 'global';
$otherId = $mode === 'dm' ? (int)($_GET['user_id'] ?? 0) : 0;
$currentUserId = $currentUserId ?? (int)($_SESSION['user']['id'] ?? 0);
$users = $users ?? [];
$messages = $messages ?? [];
$activeUser = $activeUser ?? null;
?>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header">
        <strong>People</strong>
      </div>
      <div class="list-group list-group-flush" style="max-height: 70vh; overflow-y: auto;">
        <a href="/?action=chat" class="list-group-item list-group-item-action <?= $mode === 'global' ? 'active' : '' ?>">
          <i class="bi bi-people-fill me-1"></i> Company Chat
        </a>
        <?php foreach ($users as $u): $uid = (int)($u['id'] ?? 0); if ($uid === $currentUserId) continue; ?>
          <a href="/?action=chat&amp;type=dm&amp;user_id=<?= $uid ?>" class="list-group-item list-group-item-action <?= ($mode === 'dm' && $otherId === $uid) ? 'active' : '' ?>">
            <i class="bi bi-person-circle me-1"></i>
            <?= htmlspecialchars((string)($u['name'] ?? ($u['email'] ?? 'User '.$uid)), ENT_QUOTES, 'UTF-8') ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <?php if ($mode === 'dm' && $activeUser): ?>
            <strong>Chat with <?= htmlspecialchars((string)($activeUser['name'] ?? ($activeUser['email'] ?? 'User')), ENT_QUOTES, 'UTF-8') ?></strong>
          <?php else: ?>
            <strong>Company Chat</strong>
          <?php endif; ?>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
      <div class="card-body" style="height: 60vh; overflow-y: auto; background:#f9fafb;">
        <?php if (empty($messages)): ?>
          <div class="text-muted">No messages yet.</div>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <?php $isMe = (int)($m['sender_user_id'] ?? 0) === $currentUserId; ?>
            <div class="d-flex mb-2 <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
              <div class="p-2 rounded-3" style="max-width: 70%; <?= $isMe ? 'background:#2563eb;color:#fff;' : 'background:#e5e7eb;' ?>">
                <div class="small fw-semibold mb-1">
                  <?= htmlspecialchars((string)($m['sender_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div><?= nl2br(htmlspecialchars((string)($m['message'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
                <div class="small text-muted mt-1" style="font-size:11px;">
                  <?= htmlspecialchars(date('d M Y, h:i A', strtotime($m['created_at'] ?? 'now'))) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="card-footer">
        <form method="post" action="/?action=chat&amp;subaction=send">
          <input type="hidden" name="type" value="<?= $mode === 'dm' ? 'dm' : 'global' ?>">
          <?php if ($mode === 'dm' && $activeUser): ?>
            <input type="hidden" name="user_id" value="<?= (int)($activeUser['id'] ?? 0) ?>">
          <?php endif; ?>
          <div class="input-group">
            <textarea name="message" class="form-control" rows="1" placeholder="Type a message..." required></textarea>
            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
