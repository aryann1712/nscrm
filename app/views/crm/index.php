 <script>
// ===== Mini dialogs for inline add (Source / City) =====
function openAddSourceModal(){
  const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('miniAddSourceModal'));
  const input = document.getElementById('miniAddSourceName'); if (input) input.value='';
  const active = document.getElementById('miniAddSourceActive'); if (active) active.checked = true;
  m.show();
}
async function submitMiniAddSource(){
  const name = (document.getElementById('miniAddSourceName')?.value || '').trim();
  const is_active = document.getElementById('miniAddSourceActive')?.checked ? '1':'0';
  if (!name) { alert('Enter a source name'); return; }
  try {
    const resp = await fetch('/?action=salesConfig&subaction=createSource', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ name, is_active }).toString()
    });
    if (!resp.ok) throw new Error('Create failed');
    bootstrap.Modal.getInstance(document.getElementById('miniAddSourceModal')).hide();
    await refreshSources();
    const sel = document.getElementById('sourceSelect'); if (sel) sel.value = name;
  } catch(e){ alert('Failed to add source'); }
}

function openAddCityModal(){
  const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('miniAddCityModal'));
  const input = document.getElementById('miniAddCityName'); if (input) input.value='';
  const active = document.getElementById('miniAddCityActive'); if (active) active.checked = true;
  m.show();
}
async function submitMiniAddCity(){
  const name = (document.getElementById('miniAddCityName')?.value || '').trim();
  const is_active = document.getElementById('miniAddCityActive')?.checked ? '1':'0';
  if (!name) { alert('Enter a city name'); return; }
  try {
    const resp = await fetch('/?action=salesConfig&subaction=createCity', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ name, is_active }).toString()
    });
    if (!resp.ok) throw new Error('Create failed');
    bootstrap.Modal.getInstance(document.getElementById('miniAddCityModal')).hide();
    await refreshCities();
    const input = document.getElementById('cityInput'); if (input) input.value = name;
  } catch(e){ alert('Failed to add city'); }
}

// Reusable refreshers
async function refreshSources(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listSources');
    const data = await res.json();
    const sel = document.getElementById('sourceSelect');
    if (sel && Array.isArray(data)) {
      const current = sel.value;
      sel.innerHTML = '<option value="">Select Source</option>' + data.map(s=>`<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('');
      if (current) sel.value = current;
    }
  } catch(e){}
}
async function refreshProducts(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listLeadProducts');
    const data = await res.json();
    const box = document.getElementById('productSuggestions');
    const input = document.getElementById('productInput');
    if (box && Array.isArray(data)) {
      box.innerHTML = '';
      data.forEach(p => { const b=document.createElement('span'); b.className='badge bg-light text-dark border me-1 mb-1'; b.textContent=p.name; b.style.cursor='pointer'; b.onclick=()=>{ if(input) input.value=p.name; }; box.appendChild(b); });
    }
  } catch(e){}
}
async function refreshCities(){
  try {
    const res = await fetch('/?action=salesConfig&subaction=listCities');
    const data = await res.json();
    const box = document.getElementById('citySuggestions');
    const input = document.getElementById('cityInput');
    if (box && Array.isArray(data)) {
      box.innerHTML = '';
      data.forEach(c => { const b=document.createElement('span'); b.className='badge bg-light text-dark border me-1 mb-1'; b.textContent=c.name; b.style.cursor='pointer'; b.onclick=()=>{ if(input) input.value=c.name; }; box.appendChild(b); });
    }
  } catch(e){}
}

// Sync assigned_to hidden id in Add modal
document.addEventListener('DOMContentLoaded', function(){
  const sel = document.getElementById('assigned_to_add_modal');
  const hid = document.getElementById('assigned_to_user_id_add_modal');
  if (sel && hid){
    const sync = ()=>{ const opt = sel.options[sel.selectedIndex]; hid.value = opt ? (opt.getAttribute('data-id')||'') : ''; };
    sel.addEventListener('change', sync); sync();
  }
});

// Stack multiple Bootstrap modals so mini dialogs appear on top of the Add Lead modal
document.addEventListener('shown.bs.modal', function (event) {
  const openModals = document.querySelectorAll('.modal.show');
  const zBaseModal = 1055; // Bootstrap default for .modal
  const zBaseBackdrop = 1050; // default for .modal-backdrop
  const idx = openModals.length - 1; // 0-based topmost index
  // Raise current modal
  event.target.style.zIndex = String(zBaseModal + (10 * idx));
  // Raise backdrops in order
  setTimeout(() => {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach((bd, i) => {
      bd.style.zIndex = String(zBaseBackdrop + (10 * i));
    });
  }, 0);
});
 </script>
<?php ob_start(); ?>

<!-- Header with Actions and Filters -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <!-- <h1 class="h3 mb-0">Leads & Prospects</h1> -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                All Leads
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">All Leads</a></li>
                <li><a class="dropdown-item" href="#">My Leads</a></li>
                <li><a class="dropdown-item" href="#">Unassigned</a></li>
            </ul>
        </div>
        <button class="btn btn-outline-secondary">Filters (0)</button>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <div class="input-group" style="width: 300px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search leads..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <a href="/?action=crm&subaction=create" class="btn btn-primary"><i class="bi bi-plus"></i>  Add Lead</a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importLeadsModal">Import</button>
        <button class="btn btn-outline-secondary">Customize</button>
        <a href="/?action=settings&subaction=salesConfiguration&ctype=crm#crmSection" class="btn btn-outline-secondary" title="Sales Configuration">
            <i class="bi bi-gear"></i>
        </a>
        <button class="btn btn-outline-secondary"><i class="bi bi-list"></i></button>
        <button class="btn btn-outline-secondary"><i class="bi bi-grid"></i></button>
    </div>
</div>

<!-- Mini Add Source Modal -->
<div class="modal fade" id="miniAddSourceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Source</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Source Name</label>
          <input type="text" class="form-control" id="miniAddSourceName" placeholder="Enter source">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="miniAddSourceActive" checked>
          <label class="form-check-label" for="miniAddSourceActive">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitMiniAddSource()">Save</button>
      </div>
    </div>
  </div>
  </div>

<!-- Mini Add City Modal -->
<div class="modal fade" id="miniAddCityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">City Name</label>
          <input type="text" class="form-control" id="miniAddCityName" placeholder="Enter city">
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="miniAddCityActive" checked>
          <label class="form-check-label" for="miniAddCityActive">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitMiniAddCity()">Save</button>
      </div>
    </div>
  </div>
  </div>

<!-- Update Status Modal -->
<div class="modal modal-status" id="updateStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 text-muted">Current Stage: <span class="badge bg-secondary" id="statusCurrentStage">-</span></div>

        <div class="list-group">
          <label class="list-group-item d-flex align-items-start gap-2">
            <input class="form-check-input mt-1" type="radio" name="statusAction" id="statusActionChange" value="change" onclick="toggleStatusAction('change')">
            <div class="w-100">
              <div class="fw-semibold">Change Stage to</div>
              <div id="sectionChange" class="mt-2" style="display:none;">
                <form id="frmStatusChange" method="POST" action="/?action=crm&subaction=updateStage" class="d-flex gap-2 align-items-center">
                  <input type="hidden" name="id" id="statusLeadIdChange">
                  <select class="form-select" name="new_stage" id="newStageSelect" style="max-width: 240px;">
                    <option value="Raw">Raw (Unqualified)</option>
                    <option value="New">New</option>
                    <option value="Discussion">Discussion</option>
                    <option value="Demo">Demo</option>
                    <option value="Proposal">Proposal</option>
                    <option value="Decided">Decided</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                  <button type="submit" class="btn btn-primary">Update</button>
                </form>
              </div>
            </div>
          </label>

          <label class="list-group-item d-flex align-items-start gap-2 mt-2">
            <input class="form-check-input mt-1" type="radio" name="statusAction" value="reject" onclick="toggleStatusAction('reject')">
            <div class="w-100">
              <div class="fw-semibold">Reject with Reason</div>
              <div id="sectionReject" class="mt-2" style="display:none;">
                <form id="frmStatusReject" method="POST" action="/?action=crm&subaction=reject" class="d-flex gap-2 align-items-center">
                  <input type="hidden" name="id" id="statusLeadIdReject">
                  <select class="form-select" name="reason" style="max-width: 260px;">
                    <option value="Budget Issue">Budget Issue</option>
                    <option value="No Requirement Now">No Requirement Now</option>
                    <option value="Chose Competitor">Chose Competitor</option>
                    <option value="Not Reachable">Not Reachable</option>
                    <option value="Other">Other</option>
                  </select>
                  <button type="submit" class="btn btn-danger">Reject</button>
                </form>
              </div>
            </div>
          </label>

          <label class="list-group-item d-flex align-items-start gap-2 mt-2">
            <input class="form-check-input mt-1" type="radio" name="statusAction" value="convert" onclick="toggleStatusAction('convert')">
            <div class="w-100">
              <div class="fw-semibold">Convert to Customer</div>
              <div id="sectionConvert" class="mt-2" style="display:none;">
                <form id="frmStatusConvert" method="POST" action="/?action=crm&subaction=convertToCustomer" class="d-flex gap-2 align-items-center">
                  <input type="hidden" name="id" id="statusLeadIdConvert">
                  <button type="submit" class="btn btn-warning"><i class="bi bi-box-arrow-in-right"></i> Convert</button>
                </form>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>
  </div>
 </div>

<!-- Import Leads Modal -->
<div class="modal fade" id="importLeadsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Leads</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <div class="fw-semibold mb-2">Pull Integrations</div>
          <div class="d-flex flex-wrap gap-3">
            <a href="#" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <i class="bi bi-file-earmark-excel text-success fs-3"></i>
                <div class="fw-semibold">Excel</div>
              </div>
            </a>
            <a href="#" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <i class="bi bi-facebook text-primary fs-3"></i>
                <div class="fw-semibold">Meta</div>
              </div>
            </a>
          </div>
        </div>

        <div class="mb-4">
          <div class="fw-semibold mb-2">Push Integrations</div>
          <div class="d-flex flex-wrap gap-3">
            <a href="#" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <span class="fs-3">Jd</span>
                <div class="fw-semibold">Justdial</div>
              </div>
            </a>
            <a href="#" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <span class="fs-3">mb</span>
                <div class="fw-semibold">MagicBricks</div>
              </div>
            </a>
            <a href="#" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <i class="bi bi-globe2 fs-3"></i>
                <div class="fw-semibold">Website</div>
              </div>
            </a>
          </div>
        </div>

        <div class="mb-2">
          <div class="fw-semibold mb-2">No Integrations</div>
          <div class="d-flex flex-wrap gap-3">
            <a href="/?action=crm&subaction=customize#platform" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <span class="fs-3">IM</span>
                <div class="fw-semibold">IndiaMART</div>
              </div>
            </a>
            <a href="/?action=crm&subaction=customize#platform" class="text-decoration-none">
              <div class="border rounded p-3 shadow-sm d-flex align-items-center gap-2" style="width:160px;">
                <span class="fs-3">TI</span>
                <div class="fw-semibold">TradeIndia</div>
              </div>
            </a>
          </div>
          <div class="small text-muted mt-2">Please visit <a href="/?action=crm&subaction=customize#platform">Customization &raquo; Platform Integrations</a> to set up your integrations.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Stage Filter Buttons -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="/?action=crm&stage=active" class="btn btn-outline-primary <?= ($_GET['stage'] ?? 'active') === 'active' ? 'active' : '' ?>">
            All Active Leads & Prospects
        </a>
        <a href="/?action=crm&stage=raw" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'raw' ? 'active' : '' ?>">
            Raw (Unqualified)
        </a>
        <a href="/?action=crm&stage=new" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'new' ? 'active' : '' ?>">
            New
        </a>
        <a href="/?action=crm&stage=discussion" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'discussion' ? 'active' : '' ?>">
            Discussion
        </a>
        <a href="/?action=crm&stage=demo" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'demo' ? 'active' : '' ?>">
            Demo
        </a>
        <a href="/?action=crm&stage=proposal" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'proposal' ? 'active' : '' ?>">
            Proposal
        </a>
        <a href="/?action=crm&stage=decided" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'decided' ? 'active' : '' ?>">
            Decided
        </a>
        <a href="/?action=crm&stage=inactive" class="btn btn-outline-primary <?= ($_GET['stage'] ?? '') === 'inactive' ? 'active' : '' ?>">
            Inactive
        </a>
    </div>
</div>

<!-- View Options -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
        <a href="/?action=crm&view=appointments" class="btn btn-outline-secondary <?= ($_GET['view'] ?? '') === 'appointments' ? 'active' : '' ?>">
            Appointments
        </a>
        <a href="/?action=crm&view=newest" class="btn btn-outline-secondary <?= ($_GET['view'] ?? 'newest') === 'newest' ? 'active' : '' ?>">
            Newest First
        </a>
        <a href="/?action=crm&view=oldest" class="btn btn-outline-secondary <?= ($_GET['view'] ?? '') === 'oldest' ? 'active' : '' ?>">
            Oldest First
        </a>
        <a href="/?action=crm&view=kanban" class="btn btn-outline-secondary <?= ($_GET['view'] ?? '') === 'kanban' ? 'active' : '' ?>">
            Kanban (Prospects)
        </a>
        <a href="/?action=crm&view=starred" class="btn btn-outline-secondary <?= ($_GET['view'] ?? '') === 'starred' ? 'active' : '' ?>">
            Star Leads
        </a>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Count: <?= $stats['count'] ?></span>
        <span class="text-muted">Potential: ₹<?= number_format($stats['potential_value'], 2) ?></span>
    </div>
</div>

<!-- Leads Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="leadsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th style="width: 40px;"></th>
                        <th>Business</th>
                        <th>Contact</th>
                        <th>Source</th>
                        <th>Stage</th>
                        <th>Since</th>
                        <th>Assigned to</th>
                        <th>Last Talk</th>
                        <th>Next</th>
                        <th>Requirements</th>
                        <th>Notes</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                No leads found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr data-lead-id="<?= (int)$lead['id'] ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link p-0 star-btn" 
                                            data-id="<?= $lead['id'] ?>" 
                                            onclick="toggleStar(<?= $lead['id'] ?>)">
                                        <i class="bi bi-star<?= $lead['is_starred'] ? '-fill text-warning' : '' ?>"></i>
                                    </button>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($lead['business_name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($lead['contact_person']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= htmlspecialchars($lead['source']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($lead['stage'])): ?>
                                        <span class="badge bg-primary" id="stageBadge-<?= (int)$lead['id'] ?>"><?= htmlspecialchars($lead['stage']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted" id="stageBadge-<?= (int)$lead['id'] ?>">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d-M', strtotime($lead['created_at'])) ?></td>
                                <td><?= htmlspecialchars($lead['assigned_to']) ?></td>
                                <td>
                                    <?php if ($lead['last_contact']): ?>
                                        <span id="cellLast-<?= (int)$lead['id'] ?>"><?= date('d-M', strtotime($lead['last_contact'])) ?></span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-primary btn-set-last" data-id="<?= (int)$lead['id'] ?>">+ Enter</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['next_followup']): ?>
                                        <span id="cellNext-<?= (int)$lead['id'] ?>"><?= date('d-M', strtotime($lead['next_followup'])) ?></span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary btn-set-next" data-id="<?= (int)$lead['id'] ?>">+ Set</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lead['requirements'])): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="<?= htmlspecialchars($lead['requirements']) ?>">
                                            <?= htmlspecialchars($lead['requirements']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lead['notes'])): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                              title="<?= htmlspecialchars($lead['notes']) ?>">
                                            <?= htmlspecialchars($lead['notes']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-warning" title="Edit"
                                                onclick="openLeadDetails(<?= (int)$lead['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="/?action=crm&subaction=view&id=<?= $lead['id'] ?>" 
                                           class="btn btn-outline-success" title="View">
                                            <i class="bi bi-chat-dots"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination and Bottom Actions -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted">Items per page:</span>
        <select class="form-select form-select-sm" style="width: auto;">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        
        <nav aria-label="Pagination">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">4</a></li>
                <li class="page-item"><a class="page-link" href="#">5</a></li>
                <li class="page-item"><a class="page-link" href="#">6</a></li>
                <li class="page-item"><a class="page-link" href="#">7</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                <li class="page-item"><a class="page-link" href="#">13</a></li>
                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <a href="/?action=crm&subaction=create" class="btn btn-primary btn-sm">+ Add Lead</a>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importLeadsModal">Import</button>
        <button class="btn btn-outline-secondary btn-sm">Customize</button>
        <button class="btn btn-outline-secondary btn-sm">Reports</button>
        <button class="btn btn-outline-secondary btn-sm">Filters (0)</button>
        <button class="btn btn-outline-info btn-sm">Training Console</button>
        <button class="btn btn-outline-info btn-sm">Watch Training</button>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeadModalLabel">Enter Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeadForm" method="POST" action="/?action=crm&subaction=store">
                    <!-- Core Data Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-success fw-bold mb-3">Core Data</h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business *</label>
                            <input type="text" class="form-control" name="business_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <div class="row g-2">
                                <div class="col-3">
                                    <select class="form-select" name="salutation">
                                        <option value="Mr." selected>Mr.</option>
                                        <option value="Mrs.">Mrs.</option>
                                        <option value="Ms.">Ms.</option>
                                        <option value="Dr.">Dr.</option>
                                        <option value="Prof.">Prof.</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                                </div>
                                <div class="col-5">
                                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" name="designation">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile</label>
                            <div class="input-group">
                                <select class="form-select country-code" style="width: 80px;" onchange="updatePhoneCode(this)">
                                    <option value="+91" data-country="India" selected>🇮🇳 +91</option>
                                    <option value="+1" data-country="USA">🇺🇸 +1</option>
                                    <option value="+44" data-country="UK">🇬🇧 +44</option>
                                    <option value="+86" data-country="China">🇨🇳 +86</option>
                                    <option value="+81" data-country="Japan">🇯🇵 +81</option>
                                    <option value="+49" data-country="Germany">🇩🇪 +49</option>
                                    <option value="+33" data-country="France">🇫🇷 +33</option>
                                    <option value="+39" data-country="Italy">🇮🇹 +39</option>
                                    <option value="+34" data-country="Spain">🇪🇸 +34</option>
                                    <option value="+31" data-country="Netherlands">🇳🇱 +31</option>
                                    <option value="+46" data-country="Sweden">🇸🇪 +46</option>
                                    <option value="+47" data-country="Norway">🇳🇴 +47</option>
                                    <option value="+45" data-country="Denmark">🇩🇰 +45</option>
                                    <option value="+358" data-country="Finland">🇫🇮 +358</option>
                                    <option value="+41" data-country="Switzerland">🇨🇭 +41</option>
                                    <option value="+43" data-country="Austria">🇦🇹 +43</option>
                                    <option value="+32" data-country="Belgium">🇧🇪 +32</option>
                                    <option value="+351" data-country="Portugal">🇵🇹 +351</option>
                                    <option value="+30" data-country="Greece">🇬🇷 +30</option>
                                    <option value="+48" data-country="Poland">🇵🇱 +48</option>
                                    <option value="+420" data-country="Czech Republic">🇨🇿 +420</option>
                                    <option value="+36" data-country="Hungary">🇭🇺 +36</option>
                                    <option value="+380" data-country="Ukraine">🇺🇦 +380</option>
                                    <option value="+7" data-country="Russia">🇷🇺 +7</option>
                                    <option value="+90" data-country="Turkey">🇹🇷 +90</option>
                                    <option value="+971" data-country="UAE">🇦🇪 +971</option>
                                    <option value="+966" data-country="Saudi Arabia">🇸🇦 +966</option>
                                    <option value="+20" data-country="Egypt">🇪🇬 +20</option>
                                    <option value="+27" data-country="South Africa">🇿🇦 +27</option>
                                    <option value="+234" data-country="Nigeria">🇳🇬 +234</option>
                                    <option value="+254" data-country="Kenya">🇰🇪 +254</option>
                                    <option value="+91" data-country="India">🇮🇳 +91</option>
                                    <option value="+880" data-country="Bangladesh">🇧🇩 +880</option>
                                    <option value="+92" data-country="Pakistan">🇵🇰 +92</option>
                                    <option value="+94" data-country="Sri Lanka">🇱🇰 +94</option>
                                    <option value="+977" data-country="Nepal">🇳🇵 +977</option>
                                    <option value="+880" data-country="Bangladesh">🇧🇩 +880</option>
                                    <option value="+95" data-country="Myanmar">🇲🇲 +95</option>
                                    <option value="+66" data-country="Thailand">🇹🇭 +66</option>
                                    <option value="+84" data-country="Vietnam">🇻🇳 +84</option>
                                    <option value="+65" data-country="Singapore">🇸🇬 +65</option>
                                    <option value="+60" data-country="Malaysia">🇲🇾 +60</option>
                                    <option value="+62" data-country="Indonesia">🇮🇩 +62</option>
                                    <option value="+63" data-country="Philippines">🇵🇭 +63</option>
                                    <option value="+82" data-country="South Korea">🇰🇷 +82</option>
                                    <option value="+61" data-country="Australia">🇦🇺 +61</option>
                                    <option value="+64" data-country="New Zealand">🇳🇿 +64</option>
                                    <option value="+55" data-country="Brazil">🇧🇷 +55</option>
                                    <option value="+54" data-country="Argentina">🇦🇷 +54</option>
                                    <option value="+56" data-country="Chile">🇨🇱 +56</option>
                                    <option value="+57" data-country="Colombia">🇨🇴 +57</option>
                                    <option value="+58" data-country="Venezuela">🇻🇪 +58</option>
                                    <option value="+51" data-country="Peru">🇵🇪 +51</option>
                                    <option value="+593" data-country="Ecuador">🇪🇨 +593</option>
                                    <option value="+595" data-country="Paraguay">🇵🇾 +595</option>
                                    <option value="+598" data-country="Uruguay">🇺🇾 +598</option>
                                    <option value="+52" data-country="Mexico">🇲🇽 +52</option>
                                    <option value="+506" data-country="Costa Rica">🇨🇷 +506</option>
                                    <option value="+502" data-country="Guatemala">🇬🇹 +502</option>
                                    <option value="+504" data-country="Honduras">🇭🇳 +504</option>
                                    <option value="+503" data-country="El Salvador">🇸🇻 +503</option>
                                    <option value="+505" data-country="Nicaragua">🇳🇮 +505</option>
                                    <option value="+507" data-country="Panama">🇵🇦 +507</option>
                                    <option value="+501" data-country="Belize">🇧🇿 +501</option>
                                    <option value="+504" data-country="Honduras">🇭🇳 +504</option>
                                    <option value="+503" data-country="El Salvador">🇸🇻 +503</option>
                                    <option value="+505" data-country="Nicaragua">🇳🇮 +505</option>
                                    <option value="+507" data-country="Panama">🇵🇦 +507</option>
                                    <option value="+501" data-country="Belize">🇧🇿 +501</option>
                                </select>
                                <input type="tel" class="form-control" name="contact_phone">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="contact_email">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country</label>
                            <div class="input-group">
                                <select class="form-select" name="country" id="countrySelect" onchange="updateStates(this.value)">
                                    <option value="India" selected>🇮🇳 India</option>
                                    <option value="USA">🇺🇸 United States</option>
                                    <option value="UK">🇬🇧 United Kingdom</option>
                                    <option value="China">🇨🇳 China</option>
                                    <option value="Japan">🇯🇵 Japan</option>
                                    <option value="Germany">🇩🇪 Germany</option>
                                    <option value="France">🇫🇷 France</option>
                                    <option value="Italy">🇮🇹 Italy</option>
                                    <option value="Spain">🇪🇸 Spain</option>
                                    <option value="Netherlands">🇳🇱 Netherlands</option>
                                    <option value="Sweden">🇸🇪 Sweden</option>
                                    <option value="Norway">🇳🇴 Norway</option>
                                    <option value="Denmark">🇩🇰 Denmark</option>
                                    <option value="Finland">🇫🇮 Finland</option>
                                    <option value="Switzerland">🇨🇭 Switzerland</option>
                                    <option value="Austria">🇦🇹 Austria</option>
                                    <option value="Belgium">🇧🇪 Belgium</option>
                                    <option value="Portugal">🇵🇹 Portugal</option>
                                    <option value="Greece">🇬🇷 Greece</option>
                                    <option value="Poland">🇵🇱 Poland</option>
                                    <option value="Czech Republic">🇨🇿 Czech Republic</option>
                                    <option value="Hungary">🇭🇺 Hungary</option>
                                    <option value="Ukraine">🇺🇦 Ukraine</option>
                                    <option value="Russia">🇷🇺 Russia</option>
                                    <option value="Turkey">🇹🇷 Turkey</option>
                                    <option value="UAE">🇦🇪 UAE</option>
                                    <option value="Saudi Arabia">🇸🇦 Saudi Arabia</option>
                                    <option value="Egypt">🇪🇬 Egypt</option>
                                    <option value="South Africa">🇿🇦 South Africa</option>
                                    <option value="Nigeria">🇳🇬 Nigeria</option>
                                    <option value="Kenya">🇰🇪 Kenya</option>
                                    <option value="Bangladesh">🇧🇩 Bangladesh</option>
                                    <option value="Pakistan">🇵🇰 Pakistan</option>
                                    <option value="Sri Lanka">🇱🇰 Sri Lanka</option>
                                    <option value="Nepal">🇳🇵 Nepal</option>
                                    <option value="Myanmar">🇲🇲 Myanmar</option>
                                    <option value="Thailand">🇹🇭 Thailand</option>
                                    <option value="Vietnam">🇻🇳 Vietnam</option>
                                    <option value="Singapore">🇸🇬 Singapore</option>
                                    <option value="Malaysia">🇲🇾 Malaysia</option>
                                    <option value="Indonesia">🇮🇩 Indonesia</option>
                                    <option value="Philippines">🇵🇭 Philippines</option>
                                    <option value="South Korea">🇰🇷 South Korea</option>
                                    <option value="Australia">🇦🇺 Australia</option>
                                    <option value="New Zealand">🇳🇿 New Zealand</option>
                                    <option value="Brazil">🇧🇷 Brazil</option>
                                    <option value="Argentina">🇦🇷 Argentina</option>
                                    <option value="Chile">🇨🇱 Chile</option>
                                    <option value="Colombia">🇨🇴 Colombia</option>
                                    <option value="Venezuela">🇻🇪 Venezuela</option>
                                    <option value="Peru">🇵🇪 Peru</option>
                                    <option value="Ecuador">🇪🇨 Ecuador</option>
                                    <option value="Paraguay">🇵🇾 Paraguay</option>
                                    <option value="Uruguay">🇺🇾 Uruguay</option>
                                    <option value="Mexico">🇲🇽 Mexico</option>
                                    <option value="Costa Rica">🇨🇷 Costa Rica</option>
                                    <option value="Guatemala">🇬🇹 Guatemala</option>
                                    <option value="Honduras">🇭🇳 Honduras</option>
                                    <option value="El Salvador">🇸🇻 El Salvador</option>
                                    <option value="Nicaragua">🇳🇮 Nicaragua</option>
                                    <option value="Panama">🇵🇦 Panama</option>
                                    <option value="Belize">🇧🇿 Belize</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="city" id="cityInput">
                                <button type="button" class="btn btn-outline-success" onclick="openAddCityModal()">+</button>
                            </div>
                            <div id="citySuggestions" class="mt-1"></div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <select class="form-select" name="state" id="stateSelect">
                                <option value="">Select State</option>
                                <!-- Indian States -->
                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Haryana">Haryana</option>
                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                <option value="Jharkhand">Jharkhand</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Kerala">Kerala</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Manipur">Manipur</option>
                                <option value="Meghalaya">Meghalaya</option>
                                <option value="Mizoram">Mizoram</option>
                                <option value="Nagaland">Nagaland</option>
                                <option value="Odisha">Odisha</option>
                                <option value="Punjab">Punjab</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Sikkim">Sikkim</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Telangana">Telangana</option>
                                <option value="Tripura">Tripura</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Uttarakhand">Uttarakhand</option>
                                <option value="West Bengal">West Bengal</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                <option value="Ladakh">Ladakh</option>
                                <option value="Chandigarh">Chandigarh</option>
                                <option value="Dadra and Nagar Haveli">Dadra and Nagar Haveli</option>
                                <option value="Daman and Diu">Daman and Diu</option>
                                <option value="Lakshadweep">Lakshadweep</option>
                                <option value="Puducherry">Puducherry</option>
                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GSTIN</label>
                            <input type="text" class="form-control" name="gstin">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" name="code">
                        </div>
                    </div>
                    
                    <!-- Business Opportunity Section -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-success fw-bold mb-3">Business Opportunity</h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Source</label>
                            <div class="input-group">
                                <select class="form-select" name="source" id="sourceSelect">
                                    <option value="">Select Source</option>
                                </select>
                                <button type="button" class="btn btn-outline-success" onclick="openAddSourceModal()">+</button>
                            </div>
                            <div id="sourceSuggestions" class="mt-1"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Since</label>
                            <input type="date" class="form-control" name="since" value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Requirement</label>
                            <input type="text" class="form-control" name="requirements">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-select" name="category" id="categorySelect">
                                    <option value="">Select Category</option>
                                    <option value="CCTV">CCTV</option>
                                    <option value="Access Control">Access Control</option>
                                    <option value="Intercom">Intercom</option>
                                    <option value="Fire Alarm">Fire Alarm</option>
                                    <option value="Biometric">Biometric</option>
                                    <option value="Network">Network</option>
                                    <option value="Software">Software</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="Consulting">Consulting</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Installation">Installation</option>
                                    <option value="Training">Training</option>
                                    <option value="Support">Support</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" name="notes">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="product" id="productInput">
                                <button type="button" class="btn btn-outline-success" onclick="addProduct()">+</button>
                            </div>
                            <div id="productSuggestions" class="mt-1"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Potential (₹)</label>
                            <input type="number" class="form-control" name="potential_value" min="0" step="0.01" value="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assigned to</label>
                            <select class="form-select" name="assigned_to" id="assigned_to_add_modal">
                                <option value="" selected>Select Assignee</option>
                                <?php if (!empty($ownerUsers)):
                                  foreach ($ownerUsers as $u): $uid=(int)$u['id']; $uname=trim($u['name']??''); $isOwner=(int)($u['is_owner']??0)===1; ?>
                                    <option value="<?= htmlspecialchars($uname) ?>" data-id="<?= $uid ?>"><?= htmlspecialchars($uname . ($isOwner?" (Owner)":"")) ?></option>
                                <?php endforeach; endif; ?>
                                <option value="Unassigned">Unassigned</option>
                            </select>
                            <input type="hidden" name="assigned_to_user_id" id="assigned_to_user_id_add_modal" value="">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stage</label>
                            <select class="form-select" name="stage">
                                <option value="Raw" selected>Raw (Unqualified)</option>
                                <option value="New">New</option>
                                <option value="Discussion">Discussion</option>
                                <option value="Demo">Demo</option>
                                <option value="Proposal">Proposal</option>
                                <option value="Decided">Decided</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tags</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="tags" id="tagsInput">
                            </div>
                            <div id="tagsSuggestions" class="mt-1"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addLeadForm" class="btn btn-success">
                    <i class="bi bi-check"></i> Save & Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const search = this.value.trim();
        const currentUrl = new URL(window.location);
        if (search) {
            currentUrl.searchParams.set('search', search);
        } else {
            currentUrl.searchParams.delete('search');
        }
        window.location.href = currentUrl.toString();
    }
});

// Prefill Add Lead modal for editing an existing lead
async function openEditInAddModal(id){
  try {
    const res = await fetch('/?action=crm&subaction=showJson&id=' + encodeURIComponent(id));
    const lead = await res.json();
    if (!res.ok || !lead || lead.error){ alert('Failed to load lead'); return; }
    const form = document.getElementById('addLeadForm');
    if (!form) return;
    // Switch action to update
    form.setAttribute('action','/?action=crm&subaction=update');
    // Ensure hidden id field exists
    let hid = form.querySelector('input[name="id"]');
    if (!hid){ hid = document.createElement('input'); hid.type='hidden'; hid.name='id'; form.appendChild(hid); }
    hid.value = String(lead.id||'');
    // Basic fields
    const set = (name, val) => { const el=form.querySelector(`[name="${name}"]`); if (el) el.value = val==null?'':String(val); };
    set('business_name', lead.business_name);
    // Contact person: also set individual name parts if present
    set('salutation', lead.salutation);
    set('first_name', lead.first_name);
    set('last_name', lead.last_name);
    set('designation', lead.designation);
    set('contact_email', lead.contact_email);
    set('contact_phone', lead.contact_phone);
    // Web & address
    set('website', lead.website);
    set('address_line1', lead.address_line1);
    set('address_line2', lead.address_line2);
    set('country', lead.country);
    set('state', lead.state);
    set('gstin', lead.gstin);
    set('code', lead.code);
    // Business data
    set('since', lead.since);
    set('category', lead.category);
    set('requirements', lead.requirements);
    set('notes', lead.notes);
    set('potential_value', lead.potential_value);
    set('last_contact', lead.last_contact);
    set('next_followup', lead.next_followup);
    // Selects
    const srcSel = document.getElementById('sourceSelect'); if (srcSel) srcSel.value = lead.source || '';
    const stageSel = form.querySelector('select[name="stage"]'); if (stageSel) stageSel.value = lead.stage || '';
    const assignedSel = document.getElementById('assigned_to_add_modal'); if (assignedSel) {
      assignedSel.value = lead.assigned_to || '';
      const hidAss = document.getElementById('assigned_to_user_id_add_modal'); if (hidAss) hidAss.value = lead.assigned_to_user_id || '';
    }
    // City/Product/Tags in Add modal
    const cityInp = document.getElementById('cityInput'); if (cityInp) cityInp.value = lead.city || '';
    const prodInp = document.getElementById('productInput'); if (prodInp) prodInp.value = lead.product || '';
    const tagsInp = document.getElementById('tagsInput'); if (tagsInp) tagsInp.value = lead.tags || '';
    // Hide details dialog if open, then open Add Lead modal as edit
    const detailsEl = document.getElementById('leadDetailsModal');
    const details = detailsEl ? bootstrap.Modal.getInstance(detailsEl) : null;
    if (details) { details.hide(); }
    const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('addLeadModal'));
    m.show();
  } catch(e) {
    console.error(e); alert('Unable to open edit form');
  }
}
function setStageUI(id, stage) {
  const nice = normalizeStage(stage);
  // Update cache
  if (leadsCache[id]) leadsCache[id].stage = nice;
  // Lead details modal header
  const stageSpan = document.getElementById('leadDetailsStage');
  if (stageSpan) stageSpan.textContent = nice;
  // Update Status modal current badge
  const currentBadge = document.getElementById('statusCurrentStage');
  if (currentBadge) currentBadge.textContent = nice;
  // Table row badge
  const badge = document.getElementById(`stageBadge-${id}`);
  if (badge) {
    badge.textContent = nice;
    // Ensure it has badge styling
    if (!badge.classList.contains('badge')) badge.classList.add('badge');
    if (!badge.classList.contains('bg-primary')) badge.classList.add('bg-primary');
  }
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Toggle star functionality
function toggleStar(id) {
    fetch('/?action=crm&subaction=toggleStar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert('Error toggling star: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error toggling star');
    });
}

// Auto-submit search on input change (with debounce)
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = this.value.trim();
        const currentUrl = new URL(window.location);
        if (search) {
            currentUrl.searchParams.set('search', search);
        } else {
            currentUrl.searchParams.delete('search');
        }
        window.location.href = currentUrl.toString();
    }, 500);
});

// Update the Add Lead button to trigger modal
document.addEventListener('DOMContentLoaded', function() {
    // Replace the Add Lead link with a button that opens the modal
    const addLeadLink = document.querySelector('a[href="/?action=crm&subaction=create"]');
    if (addLeadLink) {
        addLeadLink.href = '#';
        addLeadLink.setAttribute('data-bs-toggle', 'modal');
        addLeadLink.setAttribute('data-bs-target', '#addLeadModal');
    }
    
    // Also update the bottom Add Lead button
    const bottomAddLeadBtn = document.querySelector('a[href="/?action=crm&subaction=create"].btn-sm');
    if (bottomAddLeadBtn) {
        bottomAddLeadBtn.href = '#';
        bottomAddLeadBtn.setAttribute('data-bs-toggle', 'modal');
        bottomAddLeadBtn.setAttribute('data-bs-target', '#addLeadModal');
    }
});

// Update phone code when country changes
function updatePhoneCode(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const phoneCode = selectedOption.value;
    const country = selectedOption.getAttribute('data-country');
    
    // Update the country select to match
    const countrySelect = document.getElementById('countrySelect');
    if (countrySelect) {
        countrySelect.value = country;
        updateStates(country);
    }
}

// Update states when country changes
function updateStates(country) {
    const stateSelect = document.getElementById('stateSelect');
    const cityInput = document.getElementById('cityInput');
    
    // Clear current options
    stateSelect.innerHTML = '<option value="">Select State</option>';
    cityInput.value = '';
    
    if (country === 'India') {
        // Add Indian states
        const indianStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
            'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
            'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
            'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi',
            'Jammu and Kashmir', 'Ladakh', 'Chandigarh', 'Dadra and Nagar Haveli',
            'Daman and Diu', 'Lakshadweep', 'Puducherry', 'Andaman and Nicobar Islands'
        ];
        
        indianStates.forEach(state => {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            stateSelect.appendChild(option);
        });
    } else if (country === 'USA') {
        // Add US states
        const usStates = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
            'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
            'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
            'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
            'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
            'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
            'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
            'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];
        
        usStates.forEach(state => {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            stateSelect.appendChild(option);
        });
    } else if (country === 'UK') {
        // Add UK countries/regions
        const ukRegions = [
            'England', 'Scotland', 'Wales', 'Northern Ireland',
            'London', 'Manchester', 'Birmingham', 'Liverpool', 'Leeds',
            'Sheffield', 'Bristol', 'Glasgow', 'Edinburgh', 'Cardiff',
            'Belfast', 'Newcastle', 'Nottingham', 'Southampton', 'Oxford',
            'Cambridge', 'Brighton', 'Bath', 'York', 'Canterbury'
        ];
        
        ukRegions.forEach(region => {
            const option = document.createElement('option');
            option.value = region;
            option.textContent = region;
            stateSelect.appendChild(option);
        });
    } else if (country === 'Canada') {
        // Add Canadian provinces
        const canadianProvinces = [
            'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick',
            'Newfoundland and Labrador', 'Nova Scotia', 'Ontario',
            'Prince Edward Island', 'Quebec', 'Saskatchewan',
            'Northwest Territories', 'Nunavut', 'Yukon'
        ];
        
        canadianProvinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province;
            option.textContent = province;
            stateSelect.appendChild(option);
        });
    }
    // Add more countries as needed
}

// Update phone code when country changes
function updatePhoneCode(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const phoneCode = selectedOption.value;
    const country = selectedOption.getAttribute('data-country');
    
    // Update the country select to match
    const countrySelect = document.getElementById('countrySelect');
    if (countrySelect) {
        countrySelect.value = country;
    }
}

// Update states when country changes
function updateStates(country) {
    const stateSelect = document.getElementById('stateSelect');
    const cityInput = document.getElementById('cityInput');
    
    // Clear current options
    stateSelect.innerHTML = '<option value="">Select State</option>';
    cityInput.value = '';
    
    if (country === 'India') {
        // Add Indian states
        const indianStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
            'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
            'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
            'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
            'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi',
            'Jammu and Kashmir', 'Ladakh', 'Chandigarh', 'Dadra and Nagar Haveli',
            'Daman and Diu', 'Lakshadweep', 'Puducherry', 'Andaman and Nicobar Islands'
        ];
        
        indianStates.forEach(state => {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            stateSelect.appendChild(option);
        });
    } else if (country === 'USA') {
        // Add US states
        const usStates = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
            'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
            'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
            'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
            'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
            'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
            'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
            'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];
        
        usStates.forEach(state => {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            stateSelect.appendChild(option);
        });
    }
    // Add more countries as needed
}

// Add new city
function addCity() {
    const cityInput = document.getElementById('cityInput');
    const city = cityInput.value.trim();
    
    if (city) {
        const suggestions = document.getElementById('citySuggestions');
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary me-1 mb-1';
        badge.textContent = city;
        badge.style.cursor = 'pointer';
        badge.onclick = function() {
            cityInput.value = city;
            this.remove();
        };
        suggestions.appendChild(badge);
        cityInput.value = '';
    }
}

// Add new source (quick add to current dropdown only)
function addSource() {
  const sourceSelect = document.getElementById('sourceSelect');
  const source = prompt('Enter new source:');
  if (!source || !source.trim()) return;
  const val = source.trim();
  const option = document.createElement('option');
  option.value = val; option.textContent = val;
  sourceSelect.appendChild(option);
  sourceSelect.value = val;
}

// Add new product (quick add to UI only)
function addProduct() {
  const productInput = document.getElementById('productInput');
  const product = productInput.value.trim();
  if (!product) return;
  const suggestions = document.getElementById('productSuggestions');
  const badge = document.createElement('span');
  badge.className = 'badge bg-info me-1 mb-1';
  badge.textContent = product;
  badge.style.cursor = 'pointer';
  badge.onclick = function(){ productInput.value = product; };
  suggestions.appendChild(badge);
  productInput.value = '';
}

// ==== Link to Sales Configuration master data ====
document.addEventListener('DOMContentLoaded', function(){
  // Populate Sources
  (async function(){
    try {
      const res = await fetch('/?action=salesConfig&subaction=listSources');
      const data = await res.json();
      const sel = document.getElementById('sourceSelect');
      if (sel && Array.isArray(data)) {
        const current = sel.value;
        sel.innerHTML = '<option value="">Select Source</option>' + data.map(s=>`<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('');
        if (current) sel.value = current;
      }
    } catch(e) { console.warn('Failed to load sources', e); }
  })();

  // Populate Product suggestions
  (async function(){
    try {
      const res = await fetch('/?action=salesConfig&subaction=listLeadProducts');
      const data = await res.json();
      const box = document.getElementById('productSuggestions');
      const input = document.getElementById('productInput');
      if (box && Array.isArray(data)) {
        box.innerHTML = '';
        data.forEach(p => {
          const badge = document.createElement('span');
          badge.className = 'badge bg-light text-dark border me-1 mb-1';
          badge.textContent = p.name;
          badge.style.cursor = 'pointer';
          badge.onclick = () => { if (input) input.value = p.name; };
          box.appendChild(badge);
        });
      }
    } catch(e) { console.warn('Failed to load products', e); }
  })();

  // Populate City suggestions
  (async function(){
    try {
      const res = await fetch('/?action=salesConfig&subaction=listCities');
      const data = await res.json();
      const box = document.getElementById('citySuggestions');
      const input = document.getElementById('cityInput');
      if (box && Array.isArray(data)) {
        box.innerHTML = '';
        data.forEach(c => {
          const badge = document.createElement('span');
          badge.className = 'badge bg-light text-dark border me-1 mb-1';
          badge.textContent = c.name;
          badge.style.cursor = 'pointer';
          badge.onclick = () => { if (input) input.value = c.name; };
          box.appendChild(badge);
        });
      }
    } catch(e) { console.warn('Failed to load cities', e); }
  })();
});

// Add new tag
function addTag() {
    const tagsInput = document.getElementById('tagsInput');
    const tag = tagsInput.value.trim();
    
    if (tag) {
        const suggestions = document.getElementById('tagsSuggestions');
        const badge = document.createElement('span');
        badge.className = 'badge bg-warning me-1 mb-1';
        badge.textContent = tag;
        badge.style.cursor = 'pointer';
        badge.onclick = function() {
            tagsInput.value = tag;
            this.remove();
        };
        suggestions.appendChild(badge);
        tagsInput.value = '';
    }
}
</script>

<!-- Lead Details Modal (CRM-style) -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" aria-labelledby="leadDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header align-items-start">
        <div>
          <h5 class="modal-title" id="leadDetailsLabel"><span id="leadDetailsTitle">-</span></h5>
          <div class="small text-muted">Stage: <span id="leadDetailsStage">-</span> • Source: <span id="leadDetailsSource">-</span> • Assigned: <span id="leadDetailsAssigned">-</span></div>
        </div>
        <div class="d-flex gap-2">
          <button id="btnLeadModify" type="button" class="btn btn-sm btn-warning">Modify</button>
          <button id="btnLeadDelete" type="button" class="btn btn-sm btn-danger">Delete</button>
          <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="px-3 pt-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="text-muted me-1">Actions:</span>
          <button id="btnLeadStatus" type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat"></i> Update Status</button>
          <button id="btnLeadQuote" type="button" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-plus"></i> + Quote</button>
          <button id="btnLeadPI" type="button" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-text"></i> + PI</button>
          <button id="btnLeadOrder" type="button" class="btn btn-sm btn-success"><i class="bi bi-cart"></i> + Order</button>
          <button id="btnLeadInvoice" type="button" class="btn btn-sm btn-success"><i class="bi bi-receipt"></i> + Invoice</button>
        </div>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card mb-3">
              <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Contact Information</strong>
              </div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-4 text-muted">Business</div>
                  <div class="col-8" id="leadDetailsBusiness">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-4 text-muted">Contact</div>
                  <div class="col-8" id="leadDetailsContact">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-4 text-muted">Mobile</div>
                  <div class="col-8" id="leadDetailsPhone">-</div>
                </div>
                <div class="row">
                  <div class="col-4 text-muted">Email</div>
                  <div class="col-8" id="leadDetailsEmail">-</div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Notes</strong>
              </div>
              <div class="card-body">
                <div id="leadDetailsNotes" class="text-wrap">-</div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card mb-3">
              <div class="card-header"><strong>Business</strong></div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-6 text-muted">Requirements</div>
                  <div class="col-6" id="leadDetailsRequirements">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-6 text-muted">Last Talk</div>
                  <div class="col-6" id="leadDetailsLast">-</div>
                </div>
                <div class="row mb-2">
                  <div class="col-6 text-muted">Next Follow-up</div>
                  <div class="col-6" id="leadDetailsNext">-</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Build a lightweight cache of current page leads for quick JS lookup
$__leadMap = [];
if (!empty($leads)) {
    foreach ($leads as $__l) {
        $__leadMap[(int)$__l['id']] = [
            'id' => (int)$__l['id'],
            'business_name' => $__l['business_name'] ?? '',
            'contact_person' => $__l['contact_person'] ?? '',
            'contact_email' => $__l['contact_email'] ?? '',
            'contact_phone' => $__l['contact_phone'] ?? '',
            'source' => $__l['source'] ?? '',
            'stage' => $__l['stage'] ?? '',
            'assigned_to' => $__l['assigned_to'] ?? '',
            'requirements' => $__l['requirements'] ?? '',
            'notes' => $__l['notes'] ?? '',
            'last_contact' => $__l['last_contact'] ?? null,
            'next_followup' => $__l['next_followup'] ?? null,
        ];
    }
}
?>
<script>
// Safe text helper for dynamic option HTML
function escapeHtml(s){
  return String(s==null?'':s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
const leadsCache = <?php echo json_encode($__leadMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

function openLeadDetails(id) {
  const lead = leadsCache?.[id];
  if (!lead) return;

  // Fill header
  const fmtDate = (d) => {
    if (!d) return '-';
    const dt = new Date(d);
    if (isNaN(dt.getTime())) return '-';
    return dt.toLocaleDateString(undefined, { day: '2-digit', month: 'short' });
  };

  document.getElementById('leadDetailsTitle').textContent = lead.business_name || '-';
  document.getElementById('leadDetailsStage').textContent = normalizeStage(lead.stage) || '-';
  document.getElementById('leadDetailsSource').textContent = lead.source || '-';
  document.getElementById('leadDetailsAssigned').textContent = lead.assigned_to || '-';

  // Contact section
  document.getElementById('leadDetailsBusiness').textContent = lead.business_name || '-';
  document.getElementById('leadDetailsContact').textContent = lead.contact_person || '-';
  document.getElementById('leadDetailsPhone').textContent = lead.contact_phone || '-';
  document.getElementById('leadDetailsEmail').textContent = lead.contact_email || '-';

  // Business section
  document.getElementById('leadDetailsRequirements').textContent = lead.requirements || '-';
  document.getElementById('leadDetailsLast').textContent = fmtDate(lead.last_contact);
  document.getElementById('leadDetailsNext').textContent = fmtDate(lead.next_followup);
  document.getElementById('leadDetailsNotes').textContent = lead.notes || '-';

  // Buttons
  const modify = document.getElementById('btnLeadModify');
  const del = document.getElementById('btnLeadDelete');
  const statusBtn = document.getElementById('btnLeadStatus');
  if (modify) modify.onclick = () => { openEditInAddModal(id); };
  if (del) del.onclick = () => {
    if (confirm('Delete this lead?')) {
      window.location.href = '/?action=crm&subaction=delete&id=' + id;
    }
  };
  if (statusBtn) statusBtn.onclick = () => openUpdateStatus(id);

  // Quick actions: Quote / PI / Order / Invoice
  const qParams = (obj) => Object.entries(obj)
    .filter(([,v]) => v !== undefined && v !== null && String(v).length > 0)
    .map(([k,v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`)
    .join('&');

  const quoteBtn = document.getElementById('btnLeadQuote');
  const piBtn = document.getElementById('btnLeadPI');
  const orderBtn = document.getElementById('btnLeadOrder');
  const invoiceBtn = document.getElementById('btnLeadInvoice');

  const basePrefill = {
    customer: lead.business_name || '',
    contact_person: lead.contact_person || '',
    reference: `Lead #${id}`
  };

  if (quoteBtn) quoteBtn.onclick = () => {
    const params = qParams({ ...basePrefill, type: 'Quotation' });
    window.location.href = `/?action=quotations&subaction=create&${params}`;
  };
  if (piBtn) piBtn.onclick = () => {
    const params = qParams({ ...basePrefill, type: 'Proforma' });
    window.location.href = `/?action=quotations&subaction=create&${params}`;
  };
  if (orderBtn) orderBtn.onclick = () => {
    alert('Orders module is not available yet. We can wire this to a new Orders controller.');
  };
  if (invoiceBtn) invoiceBtn.onclick = () => {
    alert('Invoices module is not available yet. We can wire this to a new Invoices controller.');
  };

  const modalEl = document.getElementById('leadDetailsModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

// ===== Update Status Modal =====
function openUpdateStatus(id) {
  const lead = leadsCache?.[id];
  if (!lead) return;

  document.getElementById('statusLeadIdChange').value = id;
  document.getElementById('statusLeadIdReject').value = id;
  document.getElementById('statusLeadIdConvert').value = id;
  document.getElementById('statusCurrentStage').textContent = normalizeStage(lead.stage) || '-';

  // Default to Change Stage
  document.getElementById('statusActionChange').checked = true;
  toggleStatusAction('change');

  const sel = document.getElementById('newStageSelect');
  if (sel && lead.stage) sel.value = normalizeStage(lead.stage);

  const mEl = document.getElementById('updateStatusModal');
  const m = bootstrap.Modal.getOrCreateInstance(mEl);
  // Ensure this modal and its latest backdrop stack above the details modal
  mEl.addEventListener('shown.bs.modal', () => {
    mEl.style.zIndex = 1062;
    const backs = document.querySelectorAll('.modal-backdrop');
    const last = backs[backs.length - 1];
    if (last) last.style.zIndex = 1061;
  }, { once: true });
  m.show();
}

function toggleStatusAction(val) {
  const secChange = document.getElementById('sectionChange');
  const secReject = document.getElementById('sectionReject');
  const secConvert = document.getElementById('sectionConvert');
  secChange.style.display = (val==='change') ? 'block' : 'none';
  secReject.style.display = (val==='reject') ? 'block' : 'none';
  secConvert.style.display = (val==='convert') ? 'block' : 'none';
}

function normalizeStage(s) {
  if (!s) return '';
  const map = {
    'raw': 'Raw',
    'new': 'New',
    'discussion': 'Discussion',
    'demo': 'Demo',
    'proposal': 'Proposal',
    'decided': 'Decided',
    'inactive': 'Inactive',
    'rejected': 'Rejected',
    'converted': 'Converted'
  };
  const key = String(s).toLowerCase();
  return map[key] || s;
}

// AJAX handlers for status forms to avoid full page reload
document.addEventListener('DOMContentLoaded', () => {
  const changeForm = document.getElementById('frmStatusChange');
  const rejectForm = document.getElementById('frmStatusReject');
  const convertForm = document.getElementById('frmStatusConvert');
  const statusModalEl = document.getElementById('updateStatusModal');

  async function submitForm(form, onSuccess) {
    if (!form) return;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const action = form.getAttribute('action');
      const fd = new URLSearchParams(new FormData(form));
      try {
        const resp = await fetch(action, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: fd.toString(),
          redirect: 'follow',
        });
        // Consider 2xx and 3xx as success (PHP often responds with 302 redirects)
        if (resp.status >= 400) throw new Error(`Request failed: ${resp.status}`);
        if (typeof onSuccess === 'function') onSuccess();
        const sm = bootstrap.Modal.getInstance(statusModalEl) || bootstrap.Modal.getOrCreateInstance(statusModalEl);
        sm.hide();
      } catch (err) {
        console.error('Status update failed', err);
        alert('Failed to update status.');
      }
    });
  }

  submitForm(changeForm, () => {
    const id = document.getElementById('statusLeadIdChange').value;
    const newStage = document.getElementById('newStageSelect').value;
    const prevStage = leadsCache[id]?.stage;
    setStageUI(id, newStage);
    // If moving away from Rejected, strip previously appended rejection notes in UI/cache
    if (prevStage && prevStage.toLowerCase() === 'rejected' && newStage.toLowerCase() !== 'rejected') {
      const rejRe = /^Rejected on \d{4}-\d{2}-\d{2}:.+$/gm;
      const prevNotes = leadsCache[id]?.notes || '';
      const cleaned = prevNotes.replace(rejRe, '').replace(/\n{3,}/g, '\n\n').trim();
      leadsCache[id].notes = cleaned;
      const notesDiv = document.getElementById('leadDetailsNotes');
      if (notesDiv) notesDiv.textContent = cleaned || '-';
    }
  });

  submitForm(rejectForm, () => {
    const id = document.getElementById('statusLeadIdReject').value;
    const reasonSel = rejectForm.querySelector('select[name="reason"]');
    const reason = reasonSel ? reasonSel.value : '';
    if (leadsCache[id]) {
      const date = new Date().toISOString().slice(0,10);
      const prevNotes = leadsCache[id].notes || '';
      leadsCache[id].notes = (prevNotes + "\nRejected on " + date + ": " + reason).trim();
    }
    setStageUI(id, 'Rejected');
    const notesDiv = document.getElementById('leadDetailsNotes');
    if (notesDiv && leadsCache[id]) notesDiv.textContent = leadsCache[id].notes || '-';
  });

  submitForm(convertForm, () => {
    const id = document.getElementById('statusLeadIdConvert').value;
    const prevStage = leadsCache[id]?.stage;
    setStageUI(id, 'Converted');
    if (prevStage && prevStage.toLowerCase() === 'rejected') {
      const rejRe = /^Rejected on \d{4}-\d{2}-\d{2}:.+$/gm;
      const prevNotes = leadsCache[id]?.notes || '';
      const cleaned = prevNotes.replace(rejRe, '').replace(/\n{3,}/g, '\n\n').trim();
      leadsCache[id].notes = cleaned;
      const notesDiv = document.getElementById('leadDetailsNotes');
      if (notesDiv) notesDiv.textContent = cleaned || '-';
    }
  });
});
</script>

<style>
.btn-group .btn.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.star-btn {
    color: #6c757d;
    text-decoration: none;
}

.star-btn:hover {
    color: #ffc107;
}

.badge {
    font-size: 0.75em;
}

.pagination .page-link {
    color: #007bff;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

/* Stacked modals: ensure Update Status (second modal) shows above the first */
.modal.show:nth-of-type(2) {
    z-index: 1062;
}
.modal-backdrop.show:nth-of-type(2) {
    z-index: 1061;
}
</style>

<!-- Set Date Modal (reusable for Last Talk / Next Follow-up) -->
<div class="modal fade" id="setDateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setDateTitle">Set Date</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="setDateForm">
          <input type="hidden" id="setDateLeadId" value="">
          <input type="hidden" id="setDateType" value="">
          <div class="mb-2">
            <label class="form-label" id="setDateLabel">Date</label>
            <input type="date" class="form-control" id="setDateInput" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="setDateSaveBtn">Save</button>
      </div>
    </div>
  </div>
  </div>

<script>
// Open Details dialog when clicking a row (except on controls/links)
document.addEventListener('DOMContentLoaded', function(){
  const table = document.getElementById('leadsTable');
  if (!table) return;
  table.addEventListener('click', function(e){
    const target = e.target;
    if (target.closest('a,button,.form-check-input')) return; // ignore control clicks
    const tr = target.closest('tr[data-lead-id]');
    if (!tr) return;
    const id = tr.getAttribute('data-lead-id');
    if (!id) return;
    openLeadDetails(id);
  });
});

// Inline set Last Talk / Next Follow-up handlers
document.addEventListener('DOMContentLoaded', function(){
  // delegate clicks for dynamic rows
  document.body.addEventListener('click', function(e){
    const btnLast = e.target.closest('.btn-set-last');
    const btnNext = e.target.closest('.btn-set-next');
    if (!btnLast && !btnNext) return;
    e.preventDefault();
    const id = (btnLast||btnNext).getAttribute('data-id');
    if (!id) return;
    const type = btnLast ? 'last' : 'next';
    const title = type==='last' ? 'Set Last Talk' : 'Set Next Follow-up';
    const label = type==='last' ? 'Last Talk Date' : 'Next Follow-up Date';
    document.getElementById('setDateLeadId').value = id;
    document.getElementById('setDateType').value = type;
    document.getElementById('setDateTitle').textContent = title;
    document.getElementById('setDateLabel').textContent = label;
    document.getElementById('setDateInput').value = '';
    const mEl = document.getElementById('setDateModal');
    const m = bootstrap.Modal.getOrCreateInstance(mEl);
    m.show();
  });

  document.getElementById('setDateSaveBtn').addEventListener('click', async function(){
    const id = document.getElementById('setDateLeadId').value;
    const type = document.getElementById('setDateType').value;
    const date = document.getElementById('setDateInput').value;
    if (!id || !type || !date) return;
    const url = type==='last' ? '/?action=crm&subaction=updateLastContact' : '/?action=crm&subaction=updateNextFollowup';
    const body = type==='last' ? new URLSearchParams({ id, last_contact: date }) : new URLSearchParams({ id, next_followup: date });
    try {
      const resp = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() });
      if (!resp.ok) throw new Error('Failed');
      // Update UI in table and cache
      const d = new Date(date);
      const short = d.toLocaleDateString(undefined, { day: '2-digit', month: 'short' });
      if (type==='last') {
        const cell = document.getElementById('cellLast-' + id);
        if (cell) { cell.textContent = short; }
        else {
          const btn = document.querySelector('.btn-set-last[data-id="' + id + '"]');
          if (btn) { btn.outerHTML = '<span id="cellLast-' + id + '">' + short + '</span>'; }
        }
        if (leadsCache[id]) leadsCache[id].last_contact = date;
      } else {
        const cell = document.getElementById('cellNext-' + id);
        if (cell) { cell.textContent = short; }
        else {
          const btn = document.querySelector('.btn-set-next[data-id="' + id + '"]');
          if (btn) { btn.outerHTML = '<span id="cellNext-' + id + '">' + short + '</span>'; }
        }
        if (leadsCache[id]) leadsCache[id].next_followup = date;
      }
      bootstrap.Modal.getInstance(document.getElementById('setDateModal')).hide();
    } catch (err) {
      alert('Failed to save date');
    }
  });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
