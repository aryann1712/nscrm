<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/?action=crm">Leads & Prospects</a></li>
            <li class="breadcrumb-item active">Add New Lead</li>
        </ol>
    </nav>
    <div class="d-flex gap-2">
        <a href="/?action=crm" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
        <button type="submit" form="leadForm" class="btn btn-success"><i class="bi bi-check"></i> Save Lead</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add New Lead</h5>
    </div>
    <div class="card-body">
        <form id="leadForm" method="POST" action="/?action=crm&subaction=store">
            <div class="row g-3">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <label class="form-label">Business Name *</label>
                    <input type="text" class="form-control" name="business_name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person *</label>
                    <input type="text" class="form-control" name="contact_person" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input type="email" class="form-control" name="contact_email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="tel" class="form-control" name="contact_phone">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Source</label>
                    <select class="form-select" name="source">
                        <option value="">Select Source</option>
                        <option value="REFF LINK">REFF LINK</option>
                        <option value="Indiamart">Indiamart</option>
                        <option value="Anand Gupta Reference">Anand Gupta Reference</option>
                        <option value="Website">Website</option>
                        <option value="Cold Call">Cold Call</option>
                        <option value="Social Media">Social Media</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stage</label>
                    <select class="form-select" name="stage">
                        <option value="">Select Stage</option>
                        <option value="Raw">Raw (Unqualified)</option>
                        <option value="New">New</option>
                        <option value="Discussion">Discussion</option>
                        <option value="Demo">Demo</option>
                        <option value="Proposal">Proposal</option>
                        <option value="Decided">Decided</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assigned To</label>
                    <select class="form-select" name="assigned_to" id="assigned_to_create">
                        <option value="">Select Assignee</option>
                        <?php if (!empty($ownerUsers)):
                          foreach ($ownerUsers as $u): $uid=(int)$u['id']; $uname=trim($u['name']??''); $isOwner=(int)($u['is_owner']??0)===1; ?>
                            <option value="<?= htmlspecialchars($uname) ?>" data-id="<?= $uid ?>"><?= htmlspecialchars($uname . ($isOwner?" (Owner)":"")) ?></option>
                        <?php endforeach; endif; ?>
                        <option value="Unassigned">Unassigned</option>
                    </select>
                    <input type="hidden" name="assigned_to_user_id" id="assigned_to_user_id_create" value="">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Potential Value (₹)</label>
                    <input type="number" class="form-control" name="potential_value" min="0" step="0.01" value="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Star Lead</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_starred" value="1">
                        <label class="form-check-label">Mark as Star Lead</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Last Contact Date</label>
                    <input type="date" class="form-control" name="last_contact">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Next Follow-up Date</label>
                    <input type="date" class="form-control" name="next_followup">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Requirements</label>
                    <textarea class="form-control" name="requirements" rows="3" placeholder="Describe the requirements..."></textarea>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const sel = document.getElementById('assigned_to_create');
  const hid = document.getElementById('assigned_to_user_id_create');
  if (sel && hid) {
    const sync = ()=>{ const opt = sel.options[sel.selectedIndex]; hid.value = opt ? (opt.getAttribute('data-id')||'') : ''; };
    sel.addEventListener('change', sync); sync();
  }
});
</script>
