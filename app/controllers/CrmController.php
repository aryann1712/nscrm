<?php
require_once __DIR__ . '/../models/Lead.php';

class CrmController {
    private function json($data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Return a single lead as JSON by id
    public function showJson(): void {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { $this->json(['error'=>'ID required'], 400); }
            $lead = new Lead();
            $lead->ensureTable();
            $row = $lead->get($id);
            if (!$row) { $this->json(['error'=>'Not found'], 404); }
            $this->json($row);
        } catch (Throwable $e) {
            $this->json(['error'=>$e->getMessage()], 500);
        }
    }
    public function index() {
        try {
            $lead = new Lead();
            $lead->ensureTable();
            // Seed demo data only when explicitly requested via seedDemo=1 and table is empty
            if ((($_GET['seedDemo'] ?? '') === '1')) {
                $existingStats = $lead->getStats();
                if (($existingStats['count'] ?? 0) === 0) {
                    $lead->seedDemoDataExactSix();
                }
            }
            
            // Get filter parameters
            $stage = $_GET['stage'] ?? 'all';
            $view = $_GET['view'] ?? 'newest';
            $search = $_GET['search'] ?? '';
            $assigned_to = $_GET['assigned_to'] ?? '';
            $source = $_GET['source'] ?? '';
            
            $leads = $lead->getAllFiltered([
                'stage' => $stage,
                'view' => $view,
                'search' => $search,
                'assigned_to' => $assigned_to,
                'source' => $source
            ]);
            
            $stats = $lead->getStats();
            // Load owner users for Add Lead modal's Assigned To list
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $ownerUsers = $storeModel->getOwnerUsers();
            
            require __DIR__ . '/../views/crm/index.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Lightweight JSON list for leads (for selection modal)
    public function listJson(): void {
        try {
            $lead = new Lead();
            $lead->ensureTable();
            $q = trim($_GET['q'] ?? '');
            // Reuse existing filterable query; restrict columns for light payload
            $rows = $lead->getAllFiltered([
                'search' => $q,
                'stage' => 'all',
                'view' => 'newest',
            ]);
            $out = array_map(function($r){
                return [
                    'id' => (int)($r['id'] ?? 0),
                    'business_name' => $r['business_name'] ?? '',
                    'contact_person' => $r['contact_person'] ?? '',
                ];
            }, $rows ?? []);
            $this->json($out);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // CRM Customization page (fields, funnel, integrations, etc.)
    public function customize(): void {
        try {
            require __DIR__ . '/../views/crm/customize.php';
        } catch (Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    
    public function create() {
        try {
            $lead = new Lead();
            $lead->ensureTable();
            // Load owner users for Assigned To options
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $ownerUsers = $storeModel->getOwnerUsers();
            require __DIR__ . '/../views/crm/create.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=crm');
            exit();
        }

        try {
            $lead = new Lead();
            $lead->ensureTable();
            
            // Validate required fields
            if (empty($_POST['business_name']) || empty($_POST['first_name']) || empty($_POST['last_name'])) {
                throw new Exception("Business name, first name, and last name are required.");
            }
            
            // Build contact person name from salutation, first name, and last name
            $salutation = trim($_POST['salutation'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $contactPerson = trim($salutation . ' ' . $firstName . ' ' . $lastName);
            
            $data = [
                'business_name' => trim($_POST['business_name'] ?? ''),
                'contact_person' => $contactPerson,
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'source' => trim($_POST['source'] ?? ''),
                'stage' => trim($_POST['stage'] ?? ''),
                'assigned_to' => trim($_POST['assigned_to'] ?? ''),
                'assigned_to_user_id' => (($_POST['assigned_to_user_id'] ?? '') !== '' ? (int)$_POST['assigned_to_user_id'] : null),
                'requirements' => trim($_POST['requirements'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'potential_value' => floatval($_POST['potential_value'] ?? 0),
                'last_contact' => $_POST['last_contact'] ?? null,
                'next_followup' => $_POST['next_followup'] ?? null,
                'is_starred' => isset($_POST['is_starred']) ? 1 : 0,
                'salutation' => $salutation,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'designation' => trim($_POST['designation'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                'address_line1' => trim($_POST['address_line1'] ?? ''),
                'address_line2' => trim($_POST['address_line2'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'gstin' => trim($_POST['gstin'] ?? ''),
                'code' => trim($_POST['code'] ?? ''),
                'since' => $_POST['since'] ?? null,
                'category' => trim($_POST['category'] ?? ''),
                'product' => trim($_POST['product'] ?? ''),
                'tags' => trim($_POST['tags'] ?? '')
            ];
            
            $lead->create($data);
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function edit() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: /?action=crm');
                exit();
            }
            
            $lead = new Lead();
            $leadData = $lead->get($id);
            
            if (!$leadData) {
                header('Location: /?action=crm');
                exit();
            }
            // Load owner users for Assigned To options
            require_once __DIR__ . '/../models/InventoryStore.php';
            $storeModel = new InventoryStore();
            $ownerUsers = $storeModel->getOwnerUsers();
            // If modal=1, render partial without layout for modal body
            if (($_GET['modal'] ?? '') === '1') {
                require __DIR__ . '/../views/crm/partials/edit_form.php';
                return;
            }
            require __DIR__ . '/../views/crm/edit.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?action=crm');
            exit();
        }
        
        try {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header('Location: /?action=crm');
                exit();
            }
            
            $lead = new Lead();
            $lead->ensureTable();
            
            // Allow either direct contact_person or reconstruct from parts
            $contactPerson = trim($_POST['contact_person'] ?? '');
            if ($contactPerson === '') {
                $sal = trim($_POST['salutation'] ?? '');
                $first = trim($_POST['first_name'] ?? '');
                $last = trim($_POST['last_name'] ?? '');
                $contactPerson = trim($sal . ' ' . $first . ' ' . $last);
            }

            $data = [
                // Core
                'business_name' => trim($_POST['business_name'] ?? ''),
                'contact_person' => $contactPerson,
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'source' => trim($_POST['source'] ?? ''),
                'stage' => trim($_POST['stage'] ?? ''),
                'assigned_to' => trim($_POST['assigned_to'] ?? ''),
                'assigned_to_user_id' => (($_POST['assigned_to_user_id'] ?? '') !== '' ? (int)$_POST['assigned_to_user_id'] : null),
                'requirements' => trim($_POST['requirements'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'potential_value' => floatval($_POST['potential_value'] ?? 0),
                'last_contact' => $_POST['last_contact'] ?? null,
                'next_followup' => $_POST['next_followup'] ?? null,
                'is_starred' => isset($_POST['is_starred']) ? 1 : 0,
                // Person & web
                'salutation' => trim($_POST['salutation'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'designation' => trim($_POST['designation'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                // Address
                'address_line1' => trim($_POST['address_line1'] ?? ''),
                'address_line2' => trim($_POST['address_line2'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'gstin' => trim($_POST['gstin'] ?? ''),
                'code' => trim($_POST['code'] ?? ''),
                // Business more
                'since' => $_POST['since'] ?? null,
                'category' => trim($_POST['category'] ?? ''),
                'product' => trim($_POST['product'] ?? ''),
                'tags' => trim($_POST['tags'] ?? ''),
            ];

            $lead->updateWithExtras($id, $data);
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    public function delete() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: /?action=crm');
                exit();
            }
            
            $lead = new Lead();
            $lead->delete($id);
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    // Bulk delete all leads/prospects
    public function deleteAll() {
        try {
            // Optional: require login
            if (!isset($_SESSION['user'])) { header('Location: /?action=auth'); exit(); }
            $lead = new Lead();
            $lead->ensureTable();
            $lead->deleteAll();
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    
    public function toggleStar() {
        try {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                return;
            }
            
            $lead = new Lead();
            $lead->toggleStar($id);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Update stage from Update Status dialog
    public function updateStage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=crm'); exit(); }
        try {
            $id = $_POST['id'] ?? null;
            $stage = trim($_POST['new_stage'] ?? '');
            if (!$id || $stage === '') { throw new Exception('ID and new stage required'); }

            $lead = new Lead();
            $lead->ensureTable();
            $existing = $lead->get($id);
            if (!$existing) { throw new Exception('Lead not found'); }

            // If we are changing away from Rejected, strip any previously appended rejection notes
            $notes = $existing['notes'] ?? '';
            if (strcasecmp($stage, 'Rejected') !== 0) {
                // Remove lines like: "Rejected on YYYY-MM-DD: <reason>"
                $notes = preg_replace('/^Rejected on \d{4}-\d{2}-\d{2}:.*$/m', '', $notes);
                // Clean up multiple blank lines and trim
                $notes = preg_replace("/\n{3,}/", "\n\n", $notes);
                $notes = trim($notes);
            }

            // required by update() signature
            $lead->update($id, [
                'business_name' => $existing['business_name'],
                'contact_person' => $existing['contact_person'],
                'contact_email' => $existing['contact_email'],
                'contact_phone' => $existing['contact_phone'],
                'source' => $existing['source'],
                'stage' => $stage,
                'assigned_to' => $existing['assigned_to'],
                'requirements' => $existing['requirements'],
                'notes' => $notes,
                'potential_value' => $existing['potential_value'],
                'last_contact' => $existing['last_contact'],
                'next_followup' => $existing['next_followup'],
                'is_starred' => $existing['is_starred'],
            ]);
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Reject lead with a reason: sets stage to Rejected and appends reason to notes
    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=crm'); exit(); }
        try {
            $id = $_POST['id'] ?? null;
            $reason = trim($_POST['reason'] ?? '');
            if (!$id || $reason === '') { throw new Exception('ID and reason required'); }

            $lead = new Lead();
            $existing = $lead->get($id);
            if (!$existing) { throw new Exception('Lead not found'); }

            $date = date('Y-m-d');
            $notes = trim(($existing['notes'] ?? ''));
            $newNotes = trim($notes . "\nRejected on $date: $reason");

            $lead->update($id, [
                'business_name' => $existing['business_name'],
                'contact_person' => $existing['contact_person'],
                'contact_email' => $existing['contact_email'],
                'contact_phone' => $existing['contact_phone'],
                'source' => $existing['source'],
                'stage' => 'Rejected',
                'assigned_to' => $existing['assigned_to'],
                'requirements' => $existing['requirements'],
                'notes' => $newNotes,
                'potential_value' => $existing['potential_value'],
                'last_contact' => $existing['last_contact'],
                'next_followup' => $existing['next_followup'],
                'is_starred' => $existing['is_starred'],
            ]);
            header('Location: /?action=crm');
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Convert a lead into a customer
    public function convertToCustomer() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=crm'); exit(); }
        try {
            require_once __DIR__ . '/../models/Customer.php';
            $id = $_POST['id'] ?? null;
            if (!$id) { throw new Exception('ID is required'); }

            $lead = new Lead();
            $leadData = $lead->get($id);
            if (!$leadData) { throw new Exception('Lead not found'); }

            $customer = new Customer();
            $customerId = $customer->create([
                'company' => $leadData['business_name'] ?? '',
                'contact_name' => $leadData['contact_person'] ?? null,
                'contact_phone' => $leadData['contact_phone'] ?? null,
                'contact_email' => $leadData['contact_email'] ?? null,
                'relation' => 'lead',
                'type' => 'customer',
                'executive' => $leadData['assigned_to'] ?? null,
                'city' => $leadData['city'] ?? null,
                'state' => $leadData['state'] ?? null,
                'website' => $leadData['website'] ?? null,
                'country' => $leadData['country'] ?? null,
            ]);

            // Mark lead as Converted
            $lead->update($id, [
                'business_name' => $leadData['business_name'],
                'contact_person' => $leadData['contact_person'],
                'contact_email' => $leadData['contact_email'],
                'contact_phone' => $leadData['contact_phone'],
                'source' => $leadData['source'],
                'stage' => 'Converted',
                'assigned_to' => $leadData['assigned_to'],
                'requirements' => $leadData['requirements'],
                'notes' => trim(($leadData['notes'] ?? '') . "\nConverted to Customer #$customerId on " . date('Y-m-d')),
                'potential_value' => $leadData['potential_value'],
                'last_contact' => $leadData['last_contact'],
                'next_followup' => $leadData['next_followup'],
                'is_starred' => $leadData['is_starred'],
            ]);

            // Redirect to customer list filtered by company for quick visibility
            header('Location: /?action=customers&q=' . urlencode($leadData['business_name'] ?? ''));
            exit();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Set Last Contact date inline from list view
    public function updateLastContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=crm'); exit(); }
        try {
            $id = $_POST['id'] ?? null;
            $date = $_POST['last_contact'] ?? null;
            if (!$id || !$date) { throw new Exception('ID and last_contact are required'); }
            $lead = new Lead();
            $lead->ensureTable();
            $existing = $lead->get($id);
            if (!$existing) { throw new Exception('Lead not found'); }
            $lead->update($id, [
                'business_name' => $existing['business_name'],
                'contact_person' => $existing['contact_person'],
                'contact_email' => $existing['contact_email'],
                'contact_phone' => $existing['contact_phone'],
                'source' => $existing['source'],
                'stage' => $existing['stage'],
                'assigned_to' => $existing['assigned_to'],
                'requirements' => $existing['requirements'],
                'notes' => $existing['notes'],
                'potential_value' => $existing['potential_value'],
                'last_contact' => $date,
                'next_followup' => $existing['next_followup'],
                'is_starred' => $existing['is_starred'],
            ]);
            $this->json(['success' => true]);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Set Next Follow-up date inline from list view
    public function updateNextFollowup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /?action=crm'); exit(); }
        try {
            $id = $_POST['id'] ?? null;
            $date = $_POST['next_followup'] ?? null;
            if (!$id || !$date) { throw new Exception('ID and next_followup are required'); }
            $lead = new Lead();
            $lead->ensureTable();
            $existing = $lead->get($id);
            if (!$existing) { throw new Exception('Lead not found'); }
            $lead->update($id, [
                'business_name' => $existing['business_name'],
                'contact_person' => $existing['contact_person'],
                'contact_email' => $existing['contact_email'],
                'contact_phone' => $existing['contact_phone'],
                'source' => $existing['source'],
                'stage' => $existing['stage'],
                'assigned_to' => $existing['assigned_to'],
                'requirements' => $existing['requirements'],
                'notes' => $existing['notes'],
                'potential_value' => $existing['potential_value'],
                'last_contact' => $existing['last_contact'],
                'next_followup' => $date,
                'is_starred' => $existing['is_starred'],
            ]);
            $this->json(['success' => true]);
        } catch (Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
?>
