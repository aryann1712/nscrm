<?php
require_once __DIR__ . '/../models/BankAccount.php';
require_once __DIR__ . '/../models/Term.php';
require_once __DIR__ . '/../models/LeadSource.php';
require_once __DIR__ . '/../models/LeadProduct.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/StoreSetting.php';

class SalesConfigController {
    private BankAccount $banks;
    private Term $terms;
    private LeadSource $sources;
    private LeadProduct $leadProducts;
    private City $cities;
    private Tag $tags;
    private StoreSetting $store;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->banks = new BankAccount();
        $this->terms = new Term();
        $this->sources = new LeadSource();
        $this->leadProducts = new LeadProduct();
        $this->cities = new City();
        $this->tags = new Tag();
        $this->store = new StoreSetting();
    }

    private function json($data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Banks
    public function listBanks(): void { $this->json($this->banks->getAll()); }

    public function createBank(): void {
        $id = $this->banks->create([
            'bank_name' => $_POST['bank_name'] ?? '',
            'account_no' => $_POST['account_no'] ?? '',
            'branch' => $_POST['branch'] ?? null,
            'ifsc' => $_POST['ifsc'] ?? null,
            'is_default' => !empty($_POST['is_default'])
        ]);
        $this->json(['id'=>$id]);
    }

    public function updateBank(): void {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->banks->update($id, [
            'bank_name' => $_POST['bank_name'] ?? '',
            'account_no' => $_POST['account_no'] ?? '',
            'branch' => $_POST['branch'] ?? null,
            'ifsc' => $_POST['ifsc'] ?? null,
            'is_default' => !empty($_POST['is_default'])
        ]);
        $this->json(['success'=>$ok]);
    }

    public function deleteBank(): void {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->banks->delete($id);
        $this->json(['success'=>$ok]);
    }

    // Terms
    public function listTerms(): void { $this->json($this->terms->getAll()); }

    public function createTerm(): void {
        $id = $this->terms->create($_POST['text'] ?? '', !empty($_POST['is_active']), (int)($_POST['display_order'] ?? 1000));
        $this->json(['id'=>$id]);
    }

    public function updateTerm(): void {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->terms->update($id, $_POST['text'] ?? '', !empty($_POST['is_active']));
        $this->json(['success'=>$ok]);
    }

    public function toggleTerm(): void {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->terms->toggle($id, !empty($_POST['is_active']));
        $this->json(['success'=>$ok]);
    }

    public function deleteTerm(): void {
        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->terms->delete($id);
        $this->json(['success'=>$ok]);
    }

    public function reorderTerms(): void {
        // expects orders as JSON string {id: order}
        $orders = json_decode($_POST['orders'] ?? '{}', true) ?: [];
        $this->terms->reorder($orders);
        $this->json(['success'=>true]);
    }

    // Lead Sources
    public function listSources(): void {
        $this->json($this->sources->getAll());
    }

    public function createSource(): void {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { $this->json(['error'=>'Name is required'], 400); }
        try {
            $id = $this->sources->create($name, !empty($_POST['is_active']));
            $this->json(['id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to create','detail'=>$e->getMessage()], 500);
        }
    }

    public function updateSource(): void {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') { $this->json(['error'=>'Invalid input'], 400); }
        try {
            $ok = $this->sources->update($id, $name, !empty($_POST['is_active']));
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to update','detail'=>$e->getMessage()], 500);
        }
    }

    public function deleteSource(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            $ok = $this->sources->delete($id);
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to delete','detail'=>$e->getMessage()], 500);
        }
    }

    // Lead Products (simple master list)
    public function listLeadProducts(): void {
        $this->json($this->leadProducts->getAll());
    }

    public function createLeadProduct(): void {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { $this->json(['error'=>'Name is required'], 400); }
        try {
            $id = $this->leadProducts->create($name);
            $this->json(['id'=>$id]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to create','detail'=>$e->getMessage()], 500);
        }
    }

    public function deleteLeadProduct(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->json(['error'=>'Invalid ID'], 400); }
        try {
            $ok = $this->leadProducts->delete($id);
            $this->json(['success'=>$ok]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to delete','detail'=>$e->getMessage()], 500);
        }
    }

    // Cities
    public function listCities(): void { $this->json($this->cities->getAll()); }
    public function createCity(): void {
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($name==='') { $this->json(['error'=>'Name is required'], 400); }
        try { $id = $this->cities->create(['name'=>$name,'is_active'=>$is_active]); $this->json(['id'=>$id]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to create','detail'=>$e->getMessage()], 500); }
    }
    public function updateCity(): void {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($id<=0 || $name==='') { $this->json(['error'=>'Invalid data'], 400); }
        try { $ok = $this->cities->update($id, ['name'=>$name,'is_active'=>$is_active]); $this->json(['success'=>$ok]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to update','detail'=>$e->getMessage()], 500); }
    }
    public function deleteCity(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0) { $this->json(['error'=>'Invalid ID'], 400); }
        try { $ok = $this->cities->delete($id); $this->json(['success'=>$ok]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to delete','detail'=>$e->getMessage()], 500); }
    }

    // Tags
    public function listTags(): void { $this->json($this->tags->getAll()); }
    public function createTag(): void {
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($name==='') { $this->json(['error'=>'Name is required'], 400); }
        try { $id = $this->tags->create(['name'=>$name,'is_active'=>$is_active]); $this->json(['id'=>$id]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to create','detail'=>$e->getMessage()], 500); }
    }
    public function updateTag(): void {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if ($id<=0 || $name==='') { $this->json(['error'=>'Invalid data'], 400); }
        try { $ok = $this->tags->update($id, ['name'=>$name,'is_active'=>$is_active]); $this->json(['success'=>$ok]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to update','detail'=>$e->getMessage()], 500); }
    }
    public function deleteTag(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id<=0) { $this->json(['error'=>'Invalid ID'], 400); }
        try { $ok = $this->tags->delete($id); $this->json(['success'=>$ok]); }
        catch (Throwable $e) { $this->json(['error'=>'Failed to delete','detail'=>$e->getMessage()], 500); }
    }

    // --- Digital Signature ---
    private function basePublicPath(): string {
        // Do not rely on realpath to avoid false on symlinks; compute from project structure
        return rtrim(dirname(__DIR__, 2) . '/public', '/');
    }

    private function signaturePath(): string {
        return $this->basePublicPath() . '/uploads/settings/signature.png';
    }

    private function ensureUploadsDir(): void {
        $uploads = $this->basePublicPath() . '/uploads';
        $settings = $uploads . '/settings';
        if (!is_dir($uploads)) { @mkdir($uploads, 0775, true); }
        if (!is_dir($settings)) { @mkdir($settings, 0775, true); }
    }

    // Picks the first successfully uploaded file from a list of field names
    private function firstUploadFile(array $names): ?array {
        foreach ($names as $name) {
            if (!isset($_FILES[$name])) continue;
            $f = $_FILES[$name];
            // handle simple (non-multiple) upload
            if (is_array($f) && isset($f['error']) && $f['error'] === UPLOAD_ERR_OK && is_uploaded_file($f['tmp_name'] ?? '')) {
                return $f;
            }
            // handle multiple file inputs: name[]
            if (is_array($f) && isset($f['error']) && is_array($f['error'])) {
                $count = count($f['error']);
                for ($i=0; $i<$count; $i++) {
                    if ((int)$f['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($f['tmp_name'][$i] ?? '')) {
                        return [
                            'name' => $f['name'][$i] ?? '',
                            'type' => $f['type'][$i] ?? '',
                            'tmp_name' => $f['tmp_name'][$i] ?? '',
                            'error' => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                            'size' => $f['size'][$i] ?? 0,
                        ];
                    }
                }
            }
        }
        return null;
    }

    // Generic helpers for other assets (print header/footer)
    private function assetPath(string $name): string {
        return $this->basePublicPath() . "/uploads/settings/{$name}.png";
    }
    private function assetUrl(string $name): string {
        return "/uploads/settings/{$name}.png?ts=" . time();
    }

    public function getSignature(): void {
        $this->ensureUploadsDir();
        $file = $this->signaturePath();
        $exists = is_file($file) && filesize($file) > 0;
        $url = $exists ? '/uploads/settings/signature.png?ts=' . time() : null;
        $this->json(['exists' => $exists, 'url' => $url]);
    }

    public function uploadSignature(): void {
        $this->ensureUploadsDir();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_FILES['signature']) || ($_FILES['signature']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $err = (int)($_FILES['signature']['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->json(['success'=>false, 'error'=>'Upload error','code'=>$err], 400);
        }
        $tmp = $_FILES['signature']['tmp_name'];
        $size = (int)($_FILES['signature']['size'] ?? 0);
        if ($size <= 0 || $size > 8*1024*1024) { // 8 MB limit
            $this->json(['success'=>false, 'error'=>'File too large (max 8MB)'], 400);
        }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) {
            $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400);
        }
        $dest = $this->signaturePath();
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        // Always save as PNG file name; keep original format content
        if (!@move_uploaded_file($tmp, $dest)) {
            // fallback to copy
            if (!@copy($tmp, $dest)) {
                $this->json(['success'=>false, 'error'=>'Failed to save file'], 500);
            }
        }
        $this->json(['success'=>true, 'url'=>'/uploads/settings/signature.png?ts=' . time()]);
    }

    public function removeSignature(): void {
        $this->ensureUploadsDir();
        $file = $this->signaturePath();
        $ok = true;
        if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok]);
    }

    // --- Print Header ---
    public function getPrintHeader(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('print_header');
        $exists = is_file($file) && filesize($file) > 0;
        $this->json(['exists'=>$exists, 'url'=>$exists ? $this->assetUrl('print_header') : null]);
    }
    public function uploadPrintHeader(): void {
        $this->ensureUploadsDir();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        // accept multiple field names for flexibility
        $file = $this->firstUploadFile(['image','header','file','upload']);
        if ($file === null) { $this->json(['success'=>false, 'error'=>'No file uploaded'], 400); }
        $tmp = $file['tmp_name'];
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > 8*1024*1024) { $this->json(['success'=>false, 'error'=>'File too large (max 8MB)'], 400); }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) { $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400); }
        $dest = $this->assetPath('print_header');
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        if (!@move_uploaded_file($tmp, $dest)) { if (!@copy($tmp, $dest)) { $this->json(['success'=>false, 'error'=>'Failed to save file'], 500); } }
        $this->json(['success'=>true, 'url'=>$this->assetUrl('print_header')]);
    }
    public function removePrintHeader(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('print_header');
        $ok = true; if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok]);
    }

    // --- Print Footer ---
    public function getPrintFooter(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('print_footer');
        $exists = is_file($file) && filesize($file) > 0;
        $this->json(['exists'=>$exists, 'url'=>$exists ? $this->assetUrl('print_footer') : null]);
    }
    public function uploadPrintFooter(): void {
        $this->ensureUploadsDir();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $file = $this->firstUploadFile(['image','footer','file','upload']);
        if ($file === null) { $this->json(['success'=>false, 'error'=>'No file uploaded'], 400); }
        $tmp = $file['tmp_name'];
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > 2*1024*1024) { $this->json(['success'=>false, 'error'=>'File too large (max 2MB)'], 400); }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) { $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400); }
        $dest = $this->assetPath('print_footer');
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        if (!@move_uploaded_file($tmp, $dest)) { if (!@copy($tmp, $dest)) { $this->json(['success'=>false, 'error'=>'Failed to save file'], 500); } }
        $this->json(['success'=>true, 'url'=>$this->assetUrl('print_footer')]);
    }
    public function removePrintFooter(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('print_footer');
        $ok = true; if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok]);
    }

    // --- Store Header Banner (Your Store header image) ---
    public function getStoreHeader(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('store_header');
        $exists = is_file($file) && filesize($file) > 0;
        $this->json(['exists'=>$exists, 'url'=>$exists ? $this->assetUrl('store_header') : null]);
    }

    public function uploadStoreHeader(): void {
        $this->ensureUploadsDir();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $err = (int)($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->json(['success'=>false, 'error'=>'Upload error','code'=>$err], 400);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $size = (int)($_FILES['image']['size'] ?? 0);
        if ($size <= 0 || $size > 8*1024*1024) { $this->json(['success'=>false, 'error'=>'File too large (max 8MB)'], 400); }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) { $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400); }
        $dest = $this->assetPath('store_header');
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        if (!@move_uploaded_file($tmp, $dest)) { if (!@copy($tmp, $dest)) { $this->json(['success'=>false, 'error'=>'Failed to save file'], 500); } }
        $this->json(['success'=>true, 'url'=>$this->assetUrl('store_header')]);
    }

    public function removeStoreHeader(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('store_header');
        $ok = true; if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok]);
    }

    // --- About Company Image ---
    public function getAboutImage(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('about_image');
        $exists = is_file($file) && filesize($file) > 0;
        $this->json(['exists'=>$exists, 'url'=>$exists ? $this->assetUrl('about_image') : null]);
    }
    public function uploadAboutImage(): void {
        $this->ensureUploadsDir();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $err = (int)($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->json(['success'=>false, 'error'=>'Upload error','code'=>$err], 400);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $size = (int)($_FILES['image']['size'] ?? 0);
        if ($size <= 0 || $size > 8*1024*1024) { $this->json(['success'=>false, 'error'=>'File too large (max 8MB)'], 400); }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) { $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400); }
        $dest = $this->assetPath('about_image');
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        if (!@move_uploaded_file($tmp, $dest)) { if (!@copy($tmp, $dest)) { $this->json(['success'=>false, 'error'=>'Failed to save file'], 500); } }
        $this->json(['success'=>true, 'url'=>$this->assetUrl('about_image')]);
    }
    public function removeAboutImage(): void {
        $this->ensureUploadsDir();
        $file = $this->assetPath('about_image');
        $ok = true; if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok]);
    }

    // --- Team Member Images (idx = 1..10) ---
    private function teamAssetName(int $idx): string { return 'team_' . $idx; }
    public function getTeamImage(): void {
        $this->ensureUploadsDir();
        $idx = max(1, min(10, (int)($_GET['idx'] ?? 1)));
        $file = $this->assetPath($this->teamAssetName($idx));
        $exists = is_file($file) && filesize($file) > 0;
        $this->json(['exists'=>$exists, 'url'=>$exists ? $this->assetUrl($this->teamAssetName($idx)) : null, 'idx'=>$idx]);
    }
    public function uploadTeamImage(): void {
        $this->ensureUploadsDir();
        $idx = max(1, min(10, (int)($_POST['idx'] ?? 1)));
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $err = (int)($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->json(['success'=>false, 'error'=>'Upload error','code'=>$err], 400);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $size = (int)($_FILES['image']['size'] ?? 0);
        if ($size <= 0 || $size > 8*1024*1024) { $this->json(['success'=>false, 'error'=>'File too large (max 8MB)'], 400); }
        $type = mime_content_type($tmp) ?: '';
        $allowed = ['image/png','image/jpeg','image/webp'];
        if (!in_array($type, $allowed, true)) { $this->json(['success'=>false, 'error'=>'Only PNG, JPEG or WEBP allowed'], 400); }
        $dest = $this->assetPath($this->teamAssetName($idx));
        $dir = dirname($dest);
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        if (!is_writable($dir)) { $this->json(['success'=>false, 'error'=>'Upload directory is not writable'], 500); }
        if (!is_uploaded_file($tmp)) { $this->json(['success'=>false, 'error'=>'Invalid upload temp file'], 400); }
        if (!@move_uploaded_file($tmp, $dest)) { if (!@copy($tmp, $dest)) { $this->json(['success'=>false, 'error'=>'Failed to save file'], 500); } }
        $this->json(['success'=>true, 'url'=>$this->assetUrl($this->teamAssetName($idx)), 'idx'=>$idx]);
    }
    public function removeTeamImage(): void {
        $this->ensureUploadsDir();
        $idx = max(1, min(10, (int)($_POST['idx'] ?? 1)));
        $file = $this->assetPath($this->teamAssetName($idx));
        $ok = true; if (is_file($file)) { $ok = @unlink($file); }
        $this->json(['success'=>(bool)$ok, 'idx'=>$idx]);
    }

    // --- Generic Store Settings (key-value) ---
    // GET /?action=salesConfig&subaction=getStoreSettings&keys=a,b,c
    public function getStoreSettings(): void {
        $keysParam = trim((string)($_GET['keys'] ?? ''));
        if ($keysParam === '') { $this->json($this->store->getAll()); return; }
        $keys = array_values(array_filter(array_map('trim', explode(',', $keysParam))));
        $this->json($this->store->getByKeys($keys));
    }

    // POST /?action=salesConfig&subaction=saveStoreSettings body: JSON object
    public function saveStoreSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['error'=>'Method Not Allowed'], 405); }
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) { $this->json(['error'=>'Invalid JSON'], 400); }
        // Normalize values to strings (JSON encode arrays/objects)
        $map = [];
        foreach ($data as $k=>$v) {
            if (is_array($v) || is_object($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            $map[(string)$k] = (string)$v;
        }
        try {
            $this->store->setMany($map);
            $this->json(['success'=>true]);
        } catch (Throwable $e) {
            $this->json(['error'=>'Failed to save','detail'=>$e->getMessage()], 500);
        }
    }
}
