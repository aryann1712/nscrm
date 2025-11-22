<div class="card">
  <div class="card-header bg-warning-subtle">Purchases</div>
  <div class="card-body">
    <div class="mb-3">
      <h6 class="mb-1">Who do you purchase from?</h6>
      <small class="text-muted">Select key business segments that you purchase from.</small>
    </div>

    <div class="vstack gap-3">
      <div>
        <select class="form-select mb-2" id="purch_seg1">
          <option selected>1. Select Industry / Segment</option>
          <option>Electronics</option>
          <option>Industrial</option>
          <option>Software</option>
          <option>Retail</option>
        </select>
        <input id="purch_desc1" class="form-control" placeholder="Description your Purchases in this Segment" />
      </div>
      <div>
        <select class="form-select mb-2" id="purch_seg2">
          <option selected>2. Select Industry / Segment</option>
          <option>Electronics</option>
          <option>Industrial</option>
          <option>Software</option>
          <option>Retail</option>
        </select>
        <input id="purch_desc2" class="form-control" placeholder="Description your Purchases in this Segment" />
      </div>
      <div>
        <select class="form-select mb-2" id="purch_seg3">
          <option selected>3. Select Industry / Segment</option>
          <option>Electronics</option>
          <option>Industrial</option>
          <option>Software</option>
          <option>Retail</option>
        </select>
        <input id="purch_desc3" class="form-control" placeholder="Description your Purchases in this Segment" />
      </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
      <a href="/?action=store&step=products" class="btn btn-outline-secondary me-2">Back</a>
      <button class="btn btn-success" id="purch_save">Save</button>
    </div>
  </div>
</div>
