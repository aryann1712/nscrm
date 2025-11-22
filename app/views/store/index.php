<?php ob_start(); ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h5 class="mb-0">Setup Website</h5>
        <small class="text-muted">Configure your business profile and publish your store.</small>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm">Preview</button>
        <button class="btn btn-success btn-sm">Save</button>
      </div>
    </div>

    <div class="row g-3">
      <!-- Steps Sidebar -->
      <div class="col-md-3">
        <div class="list-group small">
          <a class="list-group-item list-group-item-action <?= ($storeStep==='basic'?'active':'') ?>" href="/?action=store&step=basic">1. Basic</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='products'?'active':'') ?>" href="/?action=store&step=products">2. Products & Market</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='purchases'?'active':'') ?>" href="/?action=store&step=purchases">3. Purchases</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='header'?'active':'') ?>" href="/?action=store&step=header">4. Header</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='offer'?'active':'') ?>" href="/?action=store&step=offer">5. Offer</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='catalog'?'active':'') ?>" href="/?action=store&step=catalog">6. Catalog</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='about'?'active':'') ?>" href="/?action=store&step=about">7. About Company</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='team'?'active':'') ?>" href="/?action=store&step=team">8. Team</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='faqs'?'active':'') ?>" href="/?action=store&step=faqs">9. FAQs</a>
          <a class="list-group-item list-group-item-action <?= ($storeStep==='contact'?'active':'') ?>" href="/?action=store&step=contact">10. Contact</a>
        </div>
      </div>

      <!-- Content -->
      <div class="col-md-9">
        <?php
          $sectionPath = __DIR__ . '/sections/' . ($storeStep ?? 'basic') . '.php';
          if (is_file($sectionPath)) {
            include $sectionPath;
          } else {
            echo '<div class="alert alert-warning">Section not available.</div>';
          }
        ?>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
