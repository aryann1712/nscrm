<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <select id="period" class="form-select form-select-sm" style="width: 140px;">
      <option value="this_month">This Month</option>
      <option value="last_month">Last Month</option>
      <option value="this_quarter">This Quarter</option>
      <option value="this_year">This Year</option>
      <option value="all">All</option>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm">Trade Report</button>
    <a class="btn btn-warning btn-sm" href="/?action=purchases&subaction=create">+ Enter Supplier Invoice</a>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Supplier</th>
            <th>Contact</th>
            <th>Invoice No.</th>
            <th>Invoice Date</th>
            <th class="text-end">Taxable (₹)</th>
            <th class="text-end">Amount (₹)</th>
            <th>Credit Month</th>
            <th style="width: 100px" class="text-end"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows ?? [])) : ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">+ Click here to enter an invoice</td></tr>
          <?php else: foreach (($rows ?? []) as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['supplier'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['contact'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['invoice_no'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['invoice_date'] ?? '') ?></td>
              <td class="text-end"><?= number_format((float)($r['taxable'] ?? 0), 2) ?></td>
              <td class="text-end"><?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
              <td><?= htmlspecialchars($r['credit_month'] ?? '') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-x"></i></button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="mt-3 d-flex gap-2">
  <button class="btn btn-outline-secondary btn-sm">Training Materials</button>
  <button class="btn btn-outline-secondary btn-sm">Watch Training</button>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
