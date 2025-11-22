<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="/?action=crm">CRM</a></li>
      <li class="breadcrumb-item active">Customization</li>
    </ol>
  </nav>
  <a href="/?action=crm" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<!-- Tabs (visual only for now) -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><a class="nav-link active" href="#" onclick="return false;">Customer Type</a></li>
  <li class="nav-item"><a class="nav-link" href="#" onclick="return false;">Data Fields</a></li>
  <li class="nav-item"><a class="nav-link" href="#" onclick="return false;">Prospect Funnel</a></li>
  <li class="nav-item"><a class="nav-link" href="#platform">Platform Integrations</a></li>
  <li class="nav-item"><a class="nav-link" href="#emailInt">Email Integration</a></li>
  <li class="nav-item"><a class="nav-link" href="#policies">Policies</a></li>
</ul>

<!-- Customer Type -->
<div class="card mb-3">
  <div class="card-header">Customer Type</div>
  <div class="card-body">
    <div class="text-muted mb-2">Who do you sell to?</div>
    <div class="btn-group" role="group">
      <input type="radio" class="btn-check" name="custType" id="ctBusiness" autocomplete="off" checked>
      <label class="btn btn-outline-primary" for="ctBusiness">Business</label>
      <input type="radio" class="btn-check" name="custType" id="ctIndividual" autocomplete="off">
      <label class="btn btn-outline-primary" for="ctIndividual">Individual</label>
      <input type="radio" class="btn-check" name="custType" id="ctBoth" autocomplete="off">
      <label class="btn btn-outline-primary" for="ctBoth">Both</label>
    </div>
  </div>
</div>

<!-- Data Fields -->
<div class="card mb-3">
  <div class="card-header">Data Fields</div>
  <div class="card-body">
    <div class="row g-4">
      <div class="col-md-12 text-muted">What columns do you wish to see in your lead list?</div>
      <div class="col-md-12 d-flex flex-wrap gap-4">
        <?php
          $fields = [
            'Business','Name','Designation','Mobile','Email','Website','Country','State','City','GSTIN','Product',
            'Potential','Stage','Assign To','Last Talk','Next','Requirements','Notes','Source','Store'
          ];
        ?>
        <?php foreach ($fields as $f): ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="fld_<?= strtolower(preg_replace('/\s+/','_',$f)) ?>" checked>
            <label class="form-check-label" for="fld_<?= strtolower(preg_replace('/\s+/','_',$f)) ?>"><?= htmlspecialchars($f) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="col-12 mt-3">
        <button class="btn btn-success btn-sm"><i class="bi bi-check"></i> Save Fields</button>
      </div>
    </div>
  </div>
</div>

<!-- Prospect Conversion Funnel -->
<div class="card mb-3">
  <div class="card-header">Prospect Conversion Funnel</div>
  <div class="card-body">
    <div class="text-muted mb-2">Leads will auto-import as Raw Leads (Unqualified). You can set up stages to track progress until conversion to customer.</div>
    <div class="d-flex flex-wrap gap-2">
      <?php $stages = ['Raw Lead','New','Discussion','Demo','Proposal','Decided','Customer']; ?>
      <?php foreach ($stages as $s): ?>
        <button type="button" class="btn btn-success btn-sm"><?= htmlspecialchars($s) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Platform Integrations -->
<div class="card mb-3" id="platform">
  <div class="card-header">Platform Integrations</div>
  <div class="card-body">
    <div class="text-muted mb-2">Import leads from popular platforms and your own website.</div>
    <div class="d-flex flex-wrap gap-3">
      <?php
        $platforms = [
          ['label'=>'IndiaMART','abbr'=>'IM','href'=>'#'],
          ['label'=>'TradeIndia','abbr'=>'TI','href'=>'#'],
          ['label'=>'Justdial','abbr'=>'Jd','href'=>'#'],
          ['label'=>'Meta','icon'=>'bi bi-facebook','href'=>'#'],
          ['label'=>'Housing','abbr'=>'H','href'=>'#'],
          ['label'=>'99acres','abbr'=>'99','href'=>'#'],
          ['label'=>'MagicBricks','abbr'=>'mb','href'=>'#'],
          ['label'=>'Website','icon'=>'bi bi-globe2','href'=>'#'],
        ];
      ?>
      <?php foreach ($platforms as $p): ?>
        <a href="<?= htmlspecialchars($p['href']) ?>" class="text-decoration-none">
          <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:190px;">
            <?php if (!empty($p['icon'])): ?>
              <i class="<?= htmlspecialchars($p['icon']) ?> fs-3"></i>
            <?php else: ?>
              <span class="fs-3"><?= htmlspecialchars($p['abbr']) ?></span>
            <?php endif; ?>
            <div class="fw-semibold"><?= htmlspecialchars($p['label']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Email Integration -->
<div class="card mb-3" id="emailInt">
  <div class="card-header">Email Integration</div>
  <div class="card-body">
    <div class="row g-3 align-items-center">
      <div class="col-md-6">
        <div class="form-text mb-2">Send lead assignment alerts, follow-ups, quotes, invoices, from your own email account.</div>
        <div class="input-group">
          <input type="email" class="form-control" value="noreply@example.com" readonly>
          <button class="btn btn-outline-secondary">Change</button>
          <button class="btn btn-outline-success">Verify</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Policies -->
<div class="row" id="policies">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Auto-Assignment</span>
        <span class="badge bg-warning text-dark">Inactive</span>
      </div>
      <div class="card-body">
        <div class="text-muted mb-2">Assign leads automatically to a Sales Executive from the list.</div>
        <button class="btn btn-outline-secondary btn-sm">Configure</button>
      </div>
    </div>
  </div>
  <div class="col-md-6 mt-3 mt-md-0">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Duplication</span>
        <span class="badge bg-danger">Disabled</span>
      </div>
      <div class="card-body">
        <div class="text-muted mb-2">How do you want to detect duplicate leads?</div>
        <button class="btn btn-outline-secondary btn-sm">Configure</button>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
