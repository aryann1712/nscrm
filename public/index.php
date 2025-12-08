<?php
// Composer autoloader (for libraries like dompdf)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) { require_once $autoload; }
// Start session for authentication state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If session user is in an invalid/partial state (no id), treat as logged out to avoid redirect loops
if (isset($_SESSION['user']) && empty($_SESSION['user']['id'])) {
    unset($_SESSION['user']);
}
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/InventoryController.php';
require_once __DIR__ . '/../app/controllers/QuotationController.php';
require_once __DIR__ . '/../app/controllers/OrdersController.php';
require_once __DIR__ . '/../app/controllers/InvoicesController.php';
require_once __DIR__ . '/../app/controllers/RecoveryController.php';
require_once __DIR__ . '/../app/controllers/ContractsController.php';
require_once __DIR__ . '/../app/controllers/SupportController.php';
require_once __DIR__ . '/../app/controllers/AccountsController.php';
require_once __DIR__ . '/../app/controllers/PurchasesController.php';
require_once __DIR__ . '/../app/controllers/PurchaseOrdersController.php';
require_once __DIR__ . '/../app/controllers/ManufacturingController.php';
require_once __DIR__ . '/../app/controllers/SalesConfigController.php';
require_once __DIR__ . '/../app/controllers/TasksController.php';
require_once __DIR__ . '/../app/controllers/StoreController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$action = $_GET['action'] ?? 'dashboard';
$subaction = $_GET['subaction'] ?? null;
$id = $_GET['id'] ?? null;

// Global guard: require login for all non-auth routes.
// Since sessions are only created after email verification, this blocks access until verified.
if (!isset($_SESSION['user']) && $action !== 'auth') {
    header('Location: /?action=auth');
    exit;
}

// Protect admin routes from customers
$userType = $_SESSION['user']['type'] ?? null;
$isOwner = (int)($_SESSION['user']['is_owner'] ?? 0);
$isCustomer = ($userType === 'customer' || ($userType === null && $isOwner === 0));

// Customer-accessible subactions in admin routes (for JSON endpoints)
$customerAllowedSubactions = [
    'getQuotationDetails',
    'getInvoiceDetails',
    'getOrderDetails',
    'printQuotation',
    'printInvoice',
    'printOrder',
    'updateCustomerDetails',
    'changePassword',
    'createSupportTicket',
    'listSupportTickets',
    // Customer address management
    'listAddresses',
    'createAddress',
    'updateAddress',
    'deleteAddress',
    // Customer support chat
    'listTicketMessages',
    'addTicketMessage',
];

// Admin-only routes that customers cannot access
$adminOnlyRoutes = ['dashboard', 'inventory', 'quotations', 'orders', 'invoices', 'crm', 'customers', 'settings', 'accounts', 'purchases', 'purchaseOrders', 'manufacturing', 'tasks', 'recovery', 'contracts', 'support', 'store', 'salesConfig'];

if ($isCustomer && in_array($action, $adminOnlyRoutes) && $action !== 'customer_dashboard') {
    // Allow customer-specific subactions in customers route (JSON endpoints)
    if ($action === 'customers' && !empty($subaction) && in_array($subaction, $customerAllowedSubactions)) {
        // Allow this - customer accessing their own data endpoints, continue to route handler
        // No redirect needed
    } else {
        // Customer trying to access admin route, redirect to their dashboard
        header('Location: /?action=customer_dashboard');
        exit;
    }
}

// Handle inventory actions
if ($action === 'inventory') {
    $controller = new InventoryController();
    
    switch ($subaction) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'show':
            $controller->show($id);
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
            break;
        case 'toggleStatus':
            $controller->toggleStatus($id);
            break;
        case 'listSubCategories':
            $controller->listSubCategories();
            break;
        case 'listCategories':
            $controller->listCategories();
            break;
        case 'createCategory':
            $controller->createCategory();
            break;
        case 'createSubCategory':
            $controller->createSubCategory();
            break;
        case 'updateCategory':
            $controller->updateCategory();
            break;
        case 'deleteCategoryApi':
            $controller->deleteCategoryApi();
            break;
        case 'updateSubCategory':
            $controller->updateSubCategory();
            break;
        case 'deleteSubCategoryApi':
            $controller->deleteSubCategoryApi();
            break;
        case 'listCategoriesWithSubs':
            $controller->listCategoriesWithSubs();
            break;
        case 'taxonomy':
            $controller->taxonomy();
            break;
        case 'settings':
            $controller->settings();
            break;
        case 'stores':
            $controller->stores();
            break;
        case 'save_store':
            $controller->saveStore();
            break;
        case 'delete_store':
            $controller->deleteStore();
            break;
        case 'listStoresWithUsers':
            $controller->listStoresWithUsers();
            break;
        case 'units':
            $controller->units();
            break;
        case 'hsn':
            $controller->hsn();
            break;
        case 'listUnits':
            $controller->listUnits();
            break;
        case 'createUnit':
            $controller->createUnit();
            break;
        case 'updateUnit':
            $controller->updateUnit();
            break;
        case 'deleteUnit':
            $controller->deleteUnit();
            break;
        case 'listHsn':
            $controller->listHsn();
            break;
        case 'createHsn':
            $controller->createHsn();
            break;
        case 'updateHsn':
            $controller->updateHsn();
            break;
        case 'deleteHsn':
            $controller->deleteHsn();
            break;
        case 'downloadTemplate':
            $controller->downloadTemplate();
            break;
        case 'importExcel':
            $controller->importExcel();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'orders') {
    $controller = new OrdersController();
    switch ($subaction) {
        case 'list':
            $controller->list();
            break;
        case 'edit':
            $controller->edit((int)$id);
            break;
        case 'pdf':
            $controller->pdf((int)$id);
            break;
        case 'print':
            $controller->print((int)$id);
            break;
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'updateStatus':
            $controller->updateStatus();
            break;
        case 'showJson':
            $controller->showJson();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'invoices') {
    $controller = new InvoicesController();
    switch ($subaction) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'receive':
            $controller->receive();
            break;
        case 'edit':
            $controller->edit((int)$id);
            break;
        case 'update':
            $controller->update((int)$id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'quotations') {
    $controller = new QuotationController();
    switch ($subaction) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit((int)$id);
            break;
        case 'pdf':
            $controller->pdf((int)$id);
            break;
        case 'print':
            $controller->print((int)$id);
            break;
        case 'update':
            $controller->update((int)$id);
            break;
        case 'delete':
            $controller->delete((int)$id);
            break;
        case 'listContacts':
            $controller->listContacts();
            break;
        case 'getContact':
            $controller->getContact();
            break;
        case 'sendEmail':
            $controller->sendEmail((int)$id);
            break;
        case 'updateStatus':
            $controller->updateStatus((int)$id);
            break;
        case 'convertToInvoice':
            $controller->convertToInvoice((int)$id);
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'salesConfig') {
    // Lightweight JSON API for Sales Configuration (banks & terms)
    $controller = new SalesConfigController();
    switch ($subaction) {
        // Banks
        case 'listBanks':
            $controller->listBanks();
            break;
        case 'createBank':
            $controller->createBank();
            break;
        case 'updateBank':
            $controller->updateBank();
            break;
        case 'deleteBank':
            $controller->deleteBank();
            break;
        // Terms
        case 'listTerms':
            $controller->listTerms();
            break;
        case 'createTerm':
            $controller->createTerm();
            break;
        case 'updateTerm':
            $controller->updateTerm();
            break;
        case 'toggleTerm':
            $controller->toggleTerm();
            break;
        case 'reorderTerms':
            $controller->reorderTerms();
            break;
        case 'deleteTerm':
            $controller->deleteTerm();
            break;
        // Digital Signature
        case 'getSignature':
            $controller->getSignature();
            break;
        case 'uploadSignature':
            $controller->uploadSignature();
            break;
        case 'removeSignature':
            $controller->removeSignature();
            break;
        // Print Header
        case 'getPrintHeader':
            $controller->getPrintHeader();
            break;
        case 'uploadPrintHeader':
            $controller->uploadPrintHeader();
            break;
        case 'removePrintHeader':
            $controller->removePrintHeader();
            break;
        // Print Footer
        case 'getPrintFooter':
            $controller->getPrintFooter();
            break;
        case 'uploadPrintFooter':
            $controller->uploadPrintFooter();
            break;
        case 'removePrintFooter':
            $controller->removePrintFooter();
            break;
        // Store Header Banner
        case 'getStoreHeader':
            $controller->getStoreHeader();
            break;
        case 'uploadStoreHeader':
            $controller->uploadStoreHeader();
            break;
        case 'removeStoreHeader':
            $controller->removeStoreHeader();
            break;
        // About Company Image
        case 'getAboutImage':
            $controller->getAboutImage();
            break;
        case 'uploadAboutImage':
            $controller->uploadAboutImage();
            break;
        case 'removeAboutImage':
            $controller->removeAboutImage();
            break;
        // Team member images
        case 'getTeamImage':
            $controller->getTeamImage();
            break;
        case 'uploadTeamImage':
            $controller->uploadTeamImage();
            break;
        case 'removeTeamImage':
            $controller->removeTeamImage();
            break;
        // Generic store settings
        case 'getStoreSettings':
            $controller->getStoreSettings();
            break;
        case 'saveStoreSettings':
            $controller->saveStoreSettings();
            break;
        // Lead Sources
        case 'listSources':
            $controller->listSources();
            break;
        case 'createSource':
            $controller->createSource();
            break;
        case 'updateSource':
            $controller->updateSource();
            break;
        case 'deleteSource':
            $controller->deleteSource();
            break;
        // Lead Products
        case 'listLeadProducts':
            $controller->listLeadProducts();
            break;
        case 'createLeadProduct':
            $controller->createLeadProduct();
            break;
        case 'deleteLeadProduct':
            $controller->deleteLeadProduct();
            break;
        // Cities
        case 'listCities':
            $controller->listCities();
            break;
        case 'createCity':
            $controller->createCity();
            break;
        case 'updateCity':
            $controller->updateCity();
            break;
        case 'deleteCity':
            $controller->deleteCity();
            break;
        // Tags
        case 'listTags':
            $controller->listTags();
            break;
        case 'createTag':
            $controller->createTag();
            break;
        case 'updateTag':
            $controller->updateTag();
            break;
        case 'deleteTag':
            $controller->deleteTag();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unknown salesConfig endpoint']);
            break;
    }
} elseif ($action === 'crm') {
    require_once __DIR__ . '/../app/controllers/CrmController.php';
    $controller = new CrmController();
    switch ($subaction) {
        case 'customize':
            $controller->customize();
            break;
        case 'listJson':
            $controller->listJson();
            break;
        case 'showJson':
            $controller->showJson();
            break;
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
        case 'deleteAll':
            $controller->deleteAll();
            break;
        case 'toggleStar':
            $controller->toggleStar();
            break;
        case 'updateStage':
            $controller->updateStage();
            break;
        case 'reject':
            $controller->reject();
            break;
        case 'convertToCustomer':
            $controller->convertToCustomer();
            break;
        case 'updateLastContact':
            $controller->updateLastContact();
            break;
        case 'updateNextFollowup':
            $controller->updateNextFollowup();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'recovery') {
    $controller = new RecoveryController();
    $controller->index();
} elseif ($action === 'contracts') {
    $controller = new ContractsController();
    $controller->index();
} elseif ($action === 'support') {
    $controller = new SupportController();
    switch ($subaction) {
        case 'listTickets':
            $controller->listTickets();
            break;
        case 'updateStatus':
            $controller->updateStatus();
            break;
        case 'listMessages':
            $controller->listMessages();
            break;
        case 'addMessage':
            $controller->addMessage();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'accounts') {
    $controller = new AccountsController();
    $controller->index();
} elseif ($action === 'purchases') {
    $controller = new PurchasesController();
    switch ($subaction) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'listContacts':
            $controller->listContacts();
            break;
        case 'getContact':
            $controller->getContact();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'purchaseOrders') {
    $controller = new PurchaseOrdersController();
    $controller->index();
} elseif ($action === 'manufacturing') {
    $controller = new ManufacturingController();
    $controller->index();
} elseif ($action === 'tasks') {
    $controller = new TasksController();
    $controller->index();
} elseif ($action === 'store') {
    $controller = new StoreController();
    $controller->index();
} elseif ($action === 'customers') {
    require_once __DIR__ . '/../app/controllers/CustomersController.php';
    $controller = new CustomersController();
    switch ($subaction) {
        case 'list':
            $controller->list();
            break;
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
        // Address management
        case 'listAddresses':
            $controller->listAddresses();
            break;
        case 'createAddress':
            $controller->createAddress();
            break;
        case 'updateAddress':
            $controller->updateAddress();
            break;
        case 'deleteAddress':
            $controller->deleteAddress();
            break;
        case 'updateCustomerDetails':
            $controller->updateCustomerDetails();
            break;
        case 'changePassword':
            $controller->changePassword();
            break;
        case 'printQuotation':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->printQuotation($id);
            } else {
                header('Location: /?action=customer_dashboard');
            }
            break;
        case 'printInvoice':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->printInvoice($id);
            } else {
                header('Location: /?action=customer_dashboard');
            }
            break;
        case 'printOrder':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->printOrder($id);
            } else {
                header('Location: /?action=customer_dashboard');
            }
            break;
        case 'getQuotationDetails':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->getQuotationDetails($id);
            } else {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error'=>'Invalid ID']);
                exit;
            }
            break;
        case 'getInvoiceDetails':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->getInvoiceDetails($id);
            } else {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error'=>'Invalid ID']);
                exit;
            }
            break;
        case 'getOrderDetails':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $controller->getOrderDetails($id);
            } else {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error'=>'Invalid ID']);
                exit;
            }
            break;
        case 'createSupportTicket':
            $controller->createSupportTicket();
            break;
        case 'listSupportTickets':
            $controller->listSupportTickets();
            break;
        case 'listTicketMessages':
            $controller->listTicketMessages();
            break;
        case 'addTicketMessage':
            $controller->addTicketMessage();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'customer_dashboard') {
    require_once __DIR__ . '/../app/controllers/CustomersController.php';
    $controller = new CustomersController();
    $controller->customerDashboard();
} elseif ($action === 'settings') {
    require_once __DIR__ . '/../app/controllers/SettingsController.php';
    $controller = new SettingsController();
    switch ($subaction) {
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
        case 'verifyEmail':
            $controller->verifyEmail();
            break;
        case 'resendVerification':
            $controller->resendVerification();
            break;
        case 'salesConfiguration':
            $controller->salesConfiguration();
            break;
        default:
            $controller->index();
            break;
    }
} elseif ($action === 'auth') {
    $controller = new AuthController();
    switch ($subaction) {
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'register':
            $controller->showRegister();
            break;
        case 'registerSubmit':
            $controller->registerSubmit();
            break;
        case 'verify':
            $controller->showVerify();
            break;
        case 'verifySubmit':
            $controller->verifySubmit();
            break;
        case 'resendOtp':
            $controller->resendOtp();
            break;
        default:
            $controller->showLogin();
            break;
    }
} else {
    // Handle user actions
    $controller = new UserController();
    
    switch ($action) {
        case 'index':
            $controller->index();
            break;
        case 'dashboard':
            // Require login for dashboard
            if (!isset($_SESSION['user'])) {
                header('Location: /?action=auth');
                exit;
            }
            $controller->dashboard();
            break;
        case 'show':
            $controller->show($id);
            break;
        case 'create':
            $controller->create();
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
            break;
        case 'auth':
            // Backward-compat, redirect to new auth routes
            header('Location: /?action=auth');
            exit;
        default:
            // If not logged in, show login page
            if (!isset($_SESSION['user'])) {
                header('Location: /?action=auth');
                exit;
            }
            $controller->dashboard();
            break;
    }
}