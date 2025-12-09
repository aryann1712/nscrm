<?php
// Variables available: $row (quotation), $items (array), $terms (array)
// Simplified printable view. Use browser's Print to save as PDF.
// Also used by Dompdf -> prefer absolute URLs for remote assets
if (session_status() === PHP_SESSION_NONE) { @session_start(); }
require_once __DIR__ . '/../../models/User.php';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host;
$ownerId = (int)($_SESSION['user']['owner_id'] ?? 0);
$assetBase = '/uploads/settings' . ($ownerId > 0 ? '/owner_' . $ownerId : '');
// Prepare signature source: for PDF, embed as base64 if available; for browser use URL
$signatureData = null;
$signatureUrl = $baseUrl . $assetBase . '/signature.png';
$sigFsPath = dirname(__DIR__, 3) . '/public' . $assetBase . '/signature.png';
if (is_file($sigFsPath) && filesize($sigFsPath) > 0) {
  if (!empty($forPdf)) {
    $mime = 'image/png';
    $raw = @file_get_contents($sigFsPath);
    if ($raw !== false) { $signatureData = 'data:' . $mime . ';base64,' . base64_encode($raw); }
  }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quotation #<?= htmlspecialchars($row['quote_no']) ?> - <?= htmlspecialchars($row['customer']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if (empty($forPdf)): ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <?php endif; ?>
  <style>
    @media print { .no-print { display: none !important; } }
    body { background: #fff; }
    .doc { width: 900px; margin: 20px auto; padding: 24px; background: #fff; border: 1px solid #ddd; position: relative; }
    .doc h1 { font-size: 20px; letter-spacing: .06em; }
    .box { border: 1px solid #333; padding: 8px; }
    .small { font-size: 12px; }
    table.table-sm th, table.table-sm td { padding: .25rem .5rem; }
    .sig { height: 64px; }
    .logo { height: 64px; }
    .waterline { font-size: 11px; color: #555; }
    .summary-table td, .summary-table th { padding: .3rem .5rem; }
    /* Watermark using header image */
    .doc::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('<?= htmlspecialchars($baseUrl . $assetBase) ?>/print_header.png') no-repeat center center;
      background-size middle; /* visible all over */
      opacity: 0.06; /* light so it doesn't block text */
      pointer-events: none;
      z-index: 0;
    }
    .doc > * { position: relative; z-index: 1; }
    /* Ensure content blocks remain fully readable over watermark */
    .doc .box, .doc table { background-color: #ffffff; }
  </style>
</head>
<body>
  <div class="doc">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <img class="logo" src="<?= htmlspecialchars($baseUrl . $assetBase) ?>/print_header.png" alt="Logo" onerror="this.style.display='none'"/>
      </div>
      <div class="text-end small" style="min-width:260px;">
        <div class="fw-bold" style="font-size:16px; white-space:normal;">
          <?php
          $userCompany = trim((string)($_SESSION['user']['company_name'] ?? ''));
          $userName    = trim((string)($_SESSION['user']['name'] ?? ''));
          $userType    = (string)($_SESSION['user']['type'] ?? '');
          if ($userType === 'customer') {
            // Customer portal: show the owner user's company_name / name (id = owner_id, is_owner = 1)
            $printCompany = 'Your Company';
            $ownerKey = (int)($_SESSION['user']['owner_id'] ?? 0);
            if ($ownerKey > 0) {
              try {
                $uModel = new User();
                $ownerUser = $uModel->get($ownerKey);
                if ($ownerUser && (int)($ownerUser['is_owner'] ?? 0) === 1) {
                  $oc = trim((string)($ownerUser['company_name'] ?? ''));
                  $on = trim((string)($ownerUser['name'] ?? ''));
                  $printCompany = $oc !== '' ? $oc : ($on !== '' ? $on : $printCompany);
                }
              } catch (Throwable $e) {
                // ignore and keep default
              }
            }
          } else {
            // Admin/employee side: use the user's company_name / name
            $printCompany = $userCompany !== '' ? $userCompany : ($userName !== '' ? $userName : 'Your Company');
          }
          echo htmlspecialchars($printCompany);
          ?>
        </div>
        <div><?= htmlspecialchars(($settings['basic_city'] ?? '') . (empty($settings['basic_state'])?'':(', ' . $settings['basic_state']))) ?></div>
        <?php if (!empty($settings['basic_gstin'])): ?>
          <div>GSTIN: <?= htmlspecialchars($settings['basic_gstin']) ?></div>
        <?php endif; ?>
        <div class="mt-2 small">
          <div><span class="text-muted">Quotation No.:</span> <strong><?= htmlspecialchars($row['quote_no']) ?></strong></div>
          <div><span class="text-muted">Date:</span> <strong><?= htmlspecialchars($row['issued_on'] ?? date('Y-m-d')) ?></strong></div>
          <?php if(!empty($row['valid_till'])): ?><div><span class="text-muted">Valid till:</span> <strong><?= htmlspecialchars($row['valid_till']) ?></strong></div><?php endif; ?>
          <?php if(!empty($row['contact_person'])): ?><div><span class="text-muted">Contact Person:</span> <strong><?= htmlspecialchars($row['contact_person']) ?></strong></div><?php endif; ?>
          <?php if(!empty($row['issued_by'])): ?><div><span class="text-muted">Issued By:</span> <strong><?= htmlspecialchars($row['issued_by']) ?></strong></div><?php endif; ?>
        </div>
      </div>
    </div>

    <h1 class="text-center mb-3">QUOTATION</h1>

    <div class="row g-2 mb-2">
      <div class="col-6">
        <div class="box small">
          <div class="fw-semibold text-uppercase">Customer: <?= htmlspecialchars(mb_strtoupper((string)($row['customer'] ?? ''))) ?></div>
          <div class="fw-semibold">Billing Address</div>
          <div><?= nl2br(htmlspecialchars($row['party_address'] ?? ($row['customer'] ?? ''))) ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="box small">
          <div class="fw-semibold text-uppercase">Customer: <?= htmlspecialchars(mb_strtoupper((string)($row['customer'] ?? ''))) ?></div>
          <div class="fw-semibold">Shipping Address</div>
          <div><?= nl2br(htmlspecialchars($row['shipping_address'] ?? '')) ?></div>
        </div>
      </div>
    </div>

    <table class="table table-bordered table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 28px;">No.</th>
          <th>Item & Description</th>
          <th style="width:80px;">HSN / SAC</th>
          <th class="text-end" style="width:70px;">Qty</th>
          <th style="width:60px;">Unit</th>
          <th class="text-end" style="width:90px;">Rate (₹)</th>
          <th class="text-end" style="width:90px;">Discount (₹)</th>
          <th class="text-end" style="width:100px;">Taxable (₹)</th>
          <th class="text-end" style="width:70px;">GST %</th>
          <th class="text-end" style="width:110px;">Amount (₹)</th>
        </tr>
      </thead>
      <tbody>
        <?php
          // Helpers
          $num = function($v,$d=2){ return number_format((float)$v,$d); };
          $n=0; $totalTaxable=0.0; $perItemGstTotal=0.0; $hasItemDisc=false; $hasItemGst=false;
          foreach ($items as $it) {
            $n++;
            $qty = (float)($it['qty'] ?? 1);
            $rate = (float)($it['rate'] ?? 0);
            $discAmt = (float)($it['discount'] ?? 0);
            $hsn = trim((string)($it['hsn_sac'] ?? ''));
            $unit = trim((string)($it['unit'] ?? 'nos'));
            $gstPct = (float)($it['gst'] ?? $it['igst'] ?? 0);
            $gstIncluded = (int)($it['gst_included'] ?? 0) === 1;
            $lineGross = max(0.0, $qty * $rate - $discAmt);
            if ($discAmt > 0) $hasItemDisc = true;
            if ($gstPct > 0) $hasItemGst = true;
            $taxable = 0.0; $itemGst = 0.0; $amount = 0.0;
            if ($gstIncluded && $gstPct > 0) {
              $taxable = $lineGross / (1 + ($gstPct/100));
              $itemGst = $lineGross - $taxable;
              $amount = $lineGross; // already inclusive
            } else {
              $taxable = $lineGross;
              $itemGst = $taxable * ($gstPct/100);
              $amount = $taxable + $itemGst;
            }
            $totalTaxable += $taxable;
            $perItemGstTotal += $itemGst;
        ?>
        <tr>
          <td class="small"><?= $n ?></td>
          <td>
            <div class="fw-semibold small"><?= htmlspecialchars($it['name'] ?? $it['description'] ?? 'Item') ?></div>
            <?php if (!empty($it['description'])): ?><div class="small text-muted"><?= nl2br(htmlspecialchars($it['description'])) ?></div><?php endif; ?>
          </td>
          <td class="small"><?= htmlspecialchars($hsn) ?></td>
          <td class="text-end small"><?= $num($qty,2) ?></td>
          <td class="small"><?= htmlspecialchars($unit) ?></td>
          <td class="text-end small"><?= $num($rate,2) ?></td>
          <td class="text-end small"><?= $num($discAmt,2) ?></td>
          <td class="text-end small"><?= $num($taxable,2) ?></td>
          <td class="text-end small"><?= $num($gstPct,2) ?></td>
          <td class="text-end small"><?= $num($amount,2) ?></td>
        </tr>
        <?php } ?>
        <?php if (empty($items)): ?>
        <tr><td colspan="10" class="text-center small text-muted">No items</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <?php
      $freight = 0.0; // Freight removed
      $overallDiscInput = (float)($row['overall_discount'] ?? 0);
      $overallGstPctInput = (float)($row['overall_gst_pct'] ?? 0);
      // Business rules
      $effectiveOverallDiscount = ($hasItemDisc || $hasItemGst) ? 0.0 : $overallDiscInput;
      $effectiveOverallGstPct = ($hasItemDisc || $hasItemGst) ? 0.0 : $overallGstPctInput;
      // Subtotal before GST = (Total Taxable + Freight) - Overall Discount
      $baseBeforeDiscount = max(0.0, $totalTaxable + $freight);
      $subtotalBeforeGst = max(0.0, $baseBeforeDiscount - $effectiveOverallDiscount);
      // GST
      $overallGst = $subtotalBeforeGst * ($effectiveOverallGstPct/100.0);
      $totalTax = $overallGst + $perItemGstTotal;
      // Grand total
      $grand = $subtotalBeforeGst + $totalTax;
    
      // Amount in words (simple Indian format)
      function inr_words($number) {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = array('', 'Hundred','Thousand','Lakh','Crore');
        $divisors = [100, 1000, 100000, 10000000];
        $str = [];
        for ($i=0,$j=0; $i < count($divisors); $i++, $j++) {
          $divider = $divisors[$i];
          if ($no >= $divider) continue; // find starting divider
        }
        // Build words
        $words = array(
          0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
          10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
          20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $levels = ['','Thousand','Lakh','Crore'];
        $digits = [];
        $n = $no;
        $digits[] = $n % 1000; $n = (int)($n/1000);
        $digits[] = $n % 100;  $n = (int)($n/100);
        $digits[] = $n % 100;  $n = (int)($n/100);
        $digits[] = $n % 100;  // Crore level (approx)
        $text = [];
        for($i=0;$i<count($digits);$i++){
          $num = $digits[$i]; if($num==0) continue;
          $level = $levels[$i] ?? '';
          $h = (int)($num/100); $rem = $num%100; $tens = '';
          if ($h>0) { $tens .= $words[$h] . ' Hundred '; }
          if ($rem<20) { $tens .= $words[$rem]; }
          else { $tens .= ($words[(int)($rem/10)*10] . ' ' . $words[$rem%10]); }
          $text[] = trim($tens . ' ' . $level);
        }
        $result = trim(implode(' ', array_reverse($text)));
        if ($result==='') $result = 'Zero';
        if ($point>0) { $result .= ' and ' . sprintf('%02d', $point) . ' Paise'; }
        return $result . ' only';
      }
    ?>
    <div class="row g-2">
      <div class="col-7">
        <div class="box small" style="min-height:110px;">
          <div class="fw-semibold mb-1">Terms & Conditions</div>
          <ol class="m-0 ps-3">
            <?php if (!empty($terms)): ?>
              <?php foreach ($terms as $t): ?>
                <li><?= htmlspecialchars(is_array($t)?($t['text'] ?? ''):$t) ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>Prices are exclusive of taxes unless specified.</li>
              <li>Warranty as per manufacturer policy.</li>
              <li>Delivery subject to availability.</li>
            <?php endif; ?>
          </ol>
        </div>
        <?php if (!empty($bank) && is_array($bank)): ?>
        <div class="box small mt-2">
          <div class="fw-semibold">Bank Details</div>
          <div>Bank: <?= htmlspecialchars($bank['bank_name'] ?? '') ?></div>
          <div>Account No: <?= htmlspecialchars($bank['account_no'] ?? '') ?></div>
          <?php if (!empty($bank['branch'])): ?><div>Branch: <?= htmlspecialchars($bank['branch']) ?></div><?php endif; ?>
          <?php if (!empty($bank['ifsc'])): ?><div>IFSC: <?= htmlspecialchars($bank['ifsc']) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="col-5">
        <table class="table table-bordered table-sm mb-0 summary-table">
          <tr><th class="small">Total Taxable</th><td class="text-end small"><?= number_format($totalTaxable,2) ?></td></tr>
          <tr><th class="small">Less: Overall Discount</th><td class="text-end small">-<?= number_format($effectiveOverallDiscount,2) ?></td></tr>
          <tr><th class="small">Subtotal (before GST)</th><td class="text-end small"><?= number_format($subtotalBeforeGst,2) ?></td></tr>
          <tr><th class="small">GST %</th><td class="text-end small"><?= number_format($effectiveOverallGstPct,2) ?>%</td></tr>
          <tr><th class="small">GST Total (Overall + Per-item)</th><td class="text-end small"><?= number_format($totalTax,2) ?></td></tr>
          <tr class="table-light"><th class="small">Grand Total</th><td class="text-end fw-semibold small"><?= number_format($grand,2) ?></td></tr>
        </table>
      </div>
    </div>

    <div class="row mt-4 small">
      <div class="col-6">
        <div class="fw-semibold">Total Amount in Words</div>
        <div><?= htmlspecialchars(inr_words($grand)) ?></div>
        <div class="fw-semibold mt-2">Notes</div>
        <div><?= nl2br(htmlspecialchars($row['notes'] ?? '')) ?></div>
      </div>
      <div class="col-6 text-end">
        <div class="fw-semibold">For <?= htmlspecialchars($settings['basic_company'] ?? 'Your Company') ?></div>
        <?php if (empty($hideSignature)): ?>
        <img src="<?= htmlspecialchars($signatureData ?: $signatureUrl) ?>" alt="Signature" class="sig" style="height:64px;" onerror="this.style.display='none'"/>
        <div>Authorised Signatory</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-3 text-center">
      <img src="<?= htmlspecialchars($baseUrl . $assetBase) ?>/print_footer.png" alt="Footer" style="max-width:100%;" onerror="this.style.display='none'"/>
    </div>
  </div>

  <div class="text-center mb-4 no-print">
    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
    <a class="btn btn-outline-secondary" href="javascript:history.back()">Back</a>
  </div>
  <?php if (empty($forPdf)): ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <?php endif; ?>
  <script>
    (function(){
      try {
        const url = new URL(window.location.href);
        if (url.searchParams.get('auto') === '1' && !<?= isset($forPdf) ? 'true' : 'false' ?>) {
          setTimeout(function(){ window.print(); }, 400);
        }
      } catch(e) {}
    })();
  </script>
</body>
</html>
