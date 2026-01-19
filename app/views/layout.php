<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= getPageTitle() ?> - NS Technology</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    
    <style>
        /* Custom AdminLTE 4 styling to match the demo exactly */
        .app-sidebar {
            background-color: #1a1a1a !important;
        }
        
        .app-sidebar .nav-link {
            color: #ffffff !important;
        }
        
        .app-sidebar .nav-link:hover {
            background-color: #2d2d2d !important;
        }
        
        .app-sidebar .nav-link.active {
            background-color: #007bff !important;
        }
        
        .app-sidebar .nav-header {
            color: #6c757d !important;
            font-size: 0.75rem !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            padding: 0.5rem 1rem !important;
            margin-top: 1rem !important;
        }
        
        .small-box {
            border-radius: 0.375rem !important;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2) !important;
            position: relative !important;
            display: block !important;
            margin-bottom: 20px !important;
        }
        
        .small-box .inner {
            padding: 1rem !important;
        }
        
        .small-box h3 {
            font-size: 2.2rem !important;
            font-weight: 700 !important;
            margin: 0 0 10px 0 !important;
            white-space: nowrap !important;
            padding: 0 !important;
        }
        
        .small-box p {
            font-size: 1rem !important;
            margin-bottom: 0 !important;
        }
        
        .small-box .small-box-icon {
            position: absolute !important;
            top: 15px !important;
            right: 15px !important;
            z-index: 0 !important;
            font-size: 70px !important;
            color: rgba(0,0,0,.15) !important;
        }
        
        .small-box .small-box-footer {
            background-color: rgba(0,0,0,.1) !important;
            color: rgba(255,255,255,.8) !important;
            display: block !important;
            padding: 3px 0 !important;
            position: relative !important;
            text-align: center !important;
            text-decoration: none !important;
            z-index: 10 !important;
        }
        
        .small-box .small-box-footer:hover {
            background-color: rgba(0,0,0,.15) !important;
            color: #ffffff !important;
        }
        
        .navbar-badge {
            font-size: 0.6rem !important;
            font-weight: 300 !important;
            padding: 2px 4px !important;
            position: absolute !important;
            right: 5px !important;
            top: 9px !important;
        }
        
        .chart-container {
            position: relative !important;
            height: 300px !important;
        }
        
        .world-map-placeholder {
            background: linear-gradient(135deg, #007bff, #0056b3) !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            margin-bottom: 20px !important;
            position: relative !important;
            overflow: hidden !important;
        }
        
        .world-map-placeholder::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><circle cx="50" cy="50" r="25" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1"/></svg>') center/contain no-repeat !important;
        }
        
        .mini-chart h6 {
            font-size: 0.875rem !important;
            margin-bottom: 0.5rem !important;
            color: #6c757d !important;
        }
        
        .progress {
            height: 20px !important;
            border-radius: 0.375rem !important;
        }

        body.fade-out {
            opacity: 0;
            transition: opacity 0.4s ease;
        }
    </style>
    <?php
    // Hide chrome (nav/sidebar/breadcrumbs) on auth page or when not logged in
    $isLoggedIn = isset($_SESSION['user']);
    $isAuthPage = (($_GET['action'] ?? '') === 'auth');
    // Logged-in user's display name
    $displayName = ($isLoggedIn && !empty($_SESSION['user']['name'])) ? htmlspecialchars($_SESSION['user']['name']) : 'Guest';
    // Company/brand name (owner-wise), used in top navbar and sidebar logo
    $companyRaw = '';
    if ($isLoggedIn) {
        if (!empty($_SESSION['user']['company_name'])) {
            $companyRaw = (string)$_SESSION['user']['company_name'];
        } elseif (!empty($_SESSION['user']['name'])) {
            $companyRaw = (string)$_SESSION['user']['name'];
        }
    }
    if ($companyRaw === '') { $companyRaw = 'NS Technology'; }
    $companyName = htmlspecialchars($companyRaw);
    // First initial for circular icon
    $companyInitial = strtoupper(mb_substr(trim($companyRaw), 0, 1, 'UTF-8'));
    if ($companyInitial === '') { $companyInitial = 'N'; }
    // Permissions helper (for sidebar menu hiding)
    require_once __DIR__ . '/../helpers/permissions.php';
    $canViewQuotations = function_exists('user_can') ? user_can('crm','quotations','view') : true;
    $canViewLeads      = function_exists('user_can') ? user_can('crm','leads','view') : true;
    $canViewOrders     = function_exists('user_can') ? user_can('erp','orders','view') : true;
    $canViewInvoices   = function_exists('user_can') ? user_can('crm','invoices','view') : true;
    if (!$isLoggedIn || $isAuthPage): ?>
    <style>
      nav.navbar,
      .app-header,
      .app-sidebar,
      .breadcrumb,
      .sidebar-brand { display: none !important; }
      .app-main .app-content > .container-fluid { max-width: 100% !important; }
      body { background: #ffffff; }
    </style>
    <?php endif; ?>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 0.5rem 1rem;">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="/">
                <div class="me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #ff6b35, #f7931e); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;"><?= $companyInitial ?></div>
                <span style="color: #ffffff; font-weight: bold; font-size: 18px;"><?= $companyName ?></span>
            </a>
            
            <!-- Navigation Icons -->
            <div class="navbar-nav ms-auto d-flex flex-row">
                <a class="nav-link text-white me-3" href="/" title="Home">
                    <i class="bi bi-house-fill" style="font-size: 18px;"></i>
                </a>
                <a class="nav-link text-white me-3" href="#" title="Help" onclick="showHelp()">
                    <i class="bi bi-question-circle-fill" style="font-size: 18px;"></i>
                </a>
                <a class="nav-link text-white me-3" href="#" title="Reload" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise" style="font-size: 18px;"></i>
                </a>
                <a class="nav-link text-white me-3" href="/?action=settings" title="Settings">
                    <i class="bi bi-gear-fill" style="font-size: 18px;"></i>
                </a>
                <a class="nav-link text-white" href="#" title="Logout" onclick="logout()">
                    <i class="bi bi-power" style="font-size: 18px;"></i>
                </a>
            </div>
        </div>
    </nav>
    
    

    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Home</a></li>
                    <li class="nav-item d-none d-md-block"><a href="#" class="nav-link">Contact</a></li>
                </ul>
                <!--end::Start Navbar Links-->
                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    <!--begin::Navbar Search-->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="bi bi-search"></i>
                        </a>
                    </li>
                    <!--end::Navbar Search-->
                    <!--begin::Messages Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-envelope"></i>
                            <span class="badge badge-danger navbar-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <span class="dropdown-item dropdown-header">3 Messages</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-envelope me-2"></i> 4 new messages
                                <span class="float-end text-secondary fs-7">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                <span class="float-end text-secondary fs-7">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                <span class="float-end text-secondary fs-7">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer"> See All Messages </a>
                        </div>
                    </li>
                    <!--end::Messages Dropdown Menu-->
                    <!--begin::Notifications Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-envelope me-2"></i> 4 new messages
                                <span class="float-end text-secondary fs-7">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                <span class="float-end text-secondary fs-7">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                <span class="float-end text-secondary fs-7">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
                        </div>
                    </li>
                    <!--end::Notifications Dropdown Menu-->
                    <!--begin::Fullscreen Toggle-->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                        </a>
                    </li>
                    <!--end::Fullscreen Toggle-->
                    <!--begin::User Menu Dropdown-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="/dist/assets/img/user2-160x160.jpg" class="user-image rounded-circle shadow" alt="User Image" />
                            <span class="d-none d-md-inline"><?= $displayName ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <!--begin::User Image-->
                            <li class="user-header text-bg-primary">
                                <img src="/dist/assets/img/user2-160x160.jpg" class="rounded-circle shadow" alt="User Image" />
                                <p>
                                    <?= $displayName ?> - User
                                    <small>Member since Nov. 2023</small>
                                </p>
                            </li>
                            <!--end::User Image-->
                            <!--begin::Menu Body-->
                            <li class="user-body">
                                <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center"><a href="#">Followers</a></div>
                                    <div class="col-4 text-center"><a href="#">Sales</a></div>
                                    <div class="col-4 text-center"><a href="#">Friends</a></div>
                                </div>
                                <!--end::Row-->
                            </li>
                            <!--end::Menu Body-->
                            <!--begin::Menu Footer-->
                            <li class="user-footer">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                                <a href="#" class="btn btn-default btn-flat float-end" onclick="logout(); return false;">Sign out</a>
                            </li>
                            <!--end::Menu Footer-->
                        </ul>
                    </li>
                    <!--end::User Menu Dropdown-->
                </ul>
                <!--end::End Navbar Links-->
            </div>
            <!--end::Container-->
        </nav>
        <!--end::Header-->
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!--begin::Sidebar Brand-->
            <div class="sidebar-brand">
                <!--begin::Brand Link-->
                <a href="/?action=dashboard" class="brand-link">
                    <!--begin::Brand Image-->
                    <img src="/dist/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
                    <!--end::Brand Image-->
                    <!--begin::Brand Text-->
                    <span class="brand-text fw-light" style="color: #ffffff;">NS Technology</span>
                    <!--end::Brand Text-->
                </a>
                <!--end::Brand Link-->
            </div>
            <!--end::Sidebar Brand-->
            <!--begin::Sidebar Wrapper-->
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <!--begin::Sidebar Menu-->
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false" id="navigation">
                        <li class="nav-item">
                            <a href="/?action=dashboard" class="nav-link">
                                <i class="nav-icon bi bi-house"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <!-- Sales Section -->
                        <li class="nav-header">SALES</li>
                        <?php if ($canViewQuotations): ?>
                        <li class="nav-item">
                            <a href="/?action=quotations" class="nav-link <?= isActiveMenu('quotations') ?>">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Quotations</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canViewLeads): ?>
                        <li class="nav-item">
                            <a href="/?action=crm" class="nav-link <?= isActiveMenu('crm') ?>">
                                <i class="nav-icon bi bi-people"></i>
                                <p>CRM</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canViewOrders): ?>
                        <li class="nav-item">
                            <a href="/?action=orders" class="nav-link <?= isActiveMenu('orders') ?>">
                                <i class="nav-icon bi bi-cart"></i>
                                <p>Orders</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($canViewInvoices): ?>
                        <li class="nav-item">
                            <a href="/?action=invoices" class="nav-link <?= isActiveMenu('invoices') ?>">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>Invoices</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a href="/?action=recovery" class="nav-link <?= isActiveMenu('recovery') ?>">
                                <i class="nav-icon bi bi-arrow-repeat"></i>
                                <p>Recovery</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=contracts" class="nav-link <?= isActiveMenu('contracts') ?>">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>Contracts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=support" class="nav-link <?= isActiveMenu('support') ?>">
                                <i class="nav-icon bi bi-headset"></i>
                                <p>Support</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=customers" class="nav-link <?= ((($_GET['action'] ?? '') === 'customers') && (($_GET['type'] ?? '') !== 'supplier')) ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person-lines-fill"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                        
                        <!-- ERP Section -->
                        <li class="nav-header">ERP</li>
                        <li class="nav-item">
                            <a href="/?action=accounts" class="nav-link <?= isActiveMenu('accounts') ?>">
                                <i class="nav-icon bi bi-calculator"></i>
                                <p>Accounts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=purchases" class="nav-link <?= isActiveMenu('purchases') ?>">
                                <i class="nav-icon bi bi-cart-plus"></i>
                                <p>Purchases</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=purchaseOrders" class="nav-link <?= isActiveMenu('purchaseOrders') ?>">
                                <i class="nav-icon bi bi-file-earmark-check"></i>
                                <p>Purch Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=inventory" class="nav-link <?= isActiveMenu('inventory') ?>">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Inventory</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=manufacturing" class="nav-link <?= isActiveMenu('manufacturing') ?>">
                                <i class="nav-icon bi bi-gear"></i>
                                <p>Manufacturing</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=tasks" class="nav-link <?= isActiveMenu('tasks') ?>">
                                <i class="nav-icon bi bi-list-task"></i>
                                <p>Tasks</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=customers&type=supplier" class="nav-link <?= ((($_GET['action'] ?? '') === 'customers') && (($_GET['type'] ?? '') === 'supplier')) ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-truck"></i>
                                <p>Suppliers</p>
                            </a>
                        </li>
                        
                        
                        <!-- Network Section -->
                        <li class="nav-header">NETWORK</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-link-45deg"></i>
                                <p>Connections</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/?action=store" class="nav-link <?= isActiveMenu('store') ?>">
                                <i class="nav-icon bi bi-shop"></i>
                                <p>Your Store</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-search"></i>
                                <p>Search</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-graph-up"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                    </ul>
                    <!--end::Sidebar Menu-->
                </nav>
            </div>
            <!--end::Sidebar Wrapper-->
        </aside>
        <!--end::Sidebar-->
        <!--begin::Main-->
        <main class="app-main">
            <!--begin::Content-->
            <div class="app-content">
                <div class="container-fluid">
                    <!--begin::Row-->
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?= getPageTitle() ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <?= getBreadcrumbs() ?>
                            </ol>
                        </div>
                    </div>
                    <!--end::Row-->
                    <!--begin::Content-->
        <?= $content ?>
                    <!--end::Content-->
                </div>
            </div>
            <!--end::Content-->
        </main>
        <!--end::Main-->
    </div>
    <!--end::App Wrapper-->

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1080"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- AdminLTE App -->
    <script src="/dist/js/adminlte.min.js"></script>
    <script>
    // Global toast helper
    (function(){
      window.toast = function(message, variant){
        try{
          var container = document.getElementById('toast-container');
          if(!container){ console.warn('Toast container missing'); return; }
          var id = 't'+Math.random().toString(36).slice(2);
          var type = (variant||'success');
          var bg = {
            success: 'bg-success text-white',
            danger: 'bg-danger text-white',
            warning: 'bg-warning',
            info: 'bg-info text-white'
          }[type] || 'bg-secondary text-white';
          var el = document.createElement('div');
          el.className = 'toast align-items-center '+bg;
          el.id = id;
          el.role = 'alert'; el.ariaLive = 'assertive'; el.ariaAtomic = 'true';
          el.innerHTML = '<div class="d-flex">\
            <div class="toast-body">'+ (message||'') +'</div>\
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>\
          </div>';
          container.appendChild(el);
          var t = new bootstrap.Toast(el, { delay: 2500 });
          t.show();
          el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
        }catch(e){ console.warn('Toast failed', e); }
      }
    })();
    </script>
</body>
</html>

<?php
function getPageTitle() {
    $action = $_GET['action'] ?? 'dashboard';
    if ($action === 'inventory') {
        $subaction = $_GET['subaction'] ?? 'index';
        switch ($subaction) {
            case 'create':
                return 'Add Inventory Item';
            case 'edit':
                return 'Edit Inventory Item';
            case 'show':
                return 'Inventory Item Details';
            default:
                return 'Inventory Management';
        }
    }
    
    switch ($action) {
        case 'dashboard':
            return 'Dashboard';
        case 'quotations':
            return 'Quotations Management';
        case 'orders':
            return 'Orders Management';
        case 'crm':
            return 'Leads & Prospects';
        case 'settings':
            return 'Settings';
        case 'recovery':
            return 'Recovery';
        case 'contracts':
            return 'Contracts';
        case 'tasks':
            return 'Tasks';
        case 'invoices':
            return 'Invoices';
        case 'customers':
            // Distinguish when opened as Suppliers list
            if (($_GET['type'] ?? '') === 'supplier') {
                return 'Suppliers';
            }
            return 'Connections (Customers)';
        case 'store':
            return 'Your Store';
        case 'index':
            return 'Users Management';
        case 'create':
            return 'Create New User';
        case 'edit':
            return 'Edit User';
        case 'show':
            return 'User Details';
        default:
            return 'Dashboard';
    }
}

function getBreadcrumbs() {
    $action = $_GET['action'] ?? 'dashboard';
    
    if ($action === 'inventory') {
        $subaction = $_GET['subaction'] ?? 'index';
        $breadcrumbs = '<li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>';
        $breadcrumbs .= '<li class="breadcrumb-item"><a href="/?action=inventory">Inventory</a></li>';
        
        switch ($subaction) {
            case 'create':
                $breadcrumbs .= '<li class="breadcrumb-item active">Add Item</li>';
                break;
            case 'edit':
                $breadcrumbs .= '<li class="breadcrumb-item active">Edit Item</li>';
                break;
            case 'show':
                $breadcrumbs .= '<li class="breadcrumb-item active">Item Details</li>';
                break;
            default:
                $breadcrumbs .= '<li class="breadcrumb-item active">Inventory</li>';
        }
        
        return $breadcrumbs;
    }
    
    $breadcrumbs = '<li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>';
    
    switch ($action) {
        case 'dashboard':
            $breadcrumbs .= '<li class="breadcrumb-item active">Dashboard</li>';
            break;
        case 'quotations':
            $breadcrumbs .= '<li class="breadcrumb-item active">Quotations</li>';
            break;
        case 'orders':
            $breadcrumbs .= '<li class="breadcrumb-item active">Orders</li>';
            break;
        case 'crm':
            $breadcrumbs .= '<li class="breadcrumb-item active">CRM</li>';
            break;
        case 'settings':
            $breadcrumbs .= '<li class="breadcrumb-item active">Settings</li>';
            break;
        case 'recovery':
            $breadcrumbs .= '<li class="breadcrumb-item active">Recovery</li>';
            break;
        case 'contracts':
            $breadcrumbs .= '<li class="breadcrumb-item active">Contracts</li>';
            break;
        case 'tasks':
            $breadcrumbs .= '<li class="breadcrumb-item active">Tasks</li>';
            break;
        case 'invoices':
            $breadcrumbs .= '<li class="breadcrumb-item active">Invoices</li>';
            break;
        case 'customers':
            // If opened with type=supplier, show Suppliers breadcrumb
            if (($_GET['type'] ?? '') === 'supplier') {
                $breadcrumbs .= '<li class="breadcrumb-item active">Suppliers</li>';
                break;
            }
            $breadcrumbs .= '<li class="breadcrumb-item active">Customers</li>';
            break;
        case 'store':
            $breadcrumbs .= '<li class="breadcrumb-item active">Your Store</li>';
            break;
        case 'index':
            $breadcrumbs .= '<li class="breadcrumb-item active">Users</li>';
            break;
        case 'create':
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="/?action=index">Users</a></li>';
            $breadcrumbs .= '<li class="breadcrumb-item active">Create User</li>';
            break;
        case 'edit':
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="/?action=index">Users</a></li>';
            $breadcrumbs .= '<li class="breadcrumb-item active">Edit User</li>';
            break;
        case 'show':
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="/?action=index">Users</a></li>';
            $breadcrumbs .= '<li class="breadcrumb-item active">User Details</li>';
            break;
        default:
            $breadcrumbs .= '<li class="breadcrumb-item active">Dashboard</li>';
    }
    
    return $breadcrumbs;
}

function isActiveMenu($menuAction) {
    $currentAction = $_GET['action'] ?? 'dashboard';
    return ($currentAction === $menuAction) ? 'active' : '';
}
?>

<script>
// Navigation functions
function showHelp() {
    alert('Help & Support\n\nFor technical support, please contact:\nEmail: support@biziverse.com\nPhone: +1-800-BIZIVERSE\n\nDocumentation: https://docs.biziverse.com');
}

// Settings function removed - now uses dedicated settings page

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch (e) {}
        document.body.classList.add('fade-out');
        setTimeout(function() {
            window.location.href = '/?action=auth&subaction=logout';
        }, 400);
    }
}
</script>
