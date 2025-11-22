<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Portal - NS Technology</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    
    <style>
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
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block"><a href="/?action=customer_dashboard" class="nav-link">Dashboard</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="/?action=auth&subaction=logout" class="nav-link">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <!--end::Header-->
        
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="/?action=customer_dashboard" class="brand-link">
                    <img src="/dist/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
                    <span class="brand-text fw-light">NS Technology</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
                        <li class="nav-item">
                            <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard'); return false;">
                                <i class="nav-icon bi bi-house"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-header">MY ACCOUNT</li>
                        <li class="nav-item">
                            <a href="#quotes" class="nav-link" onclick="showSection('quotes'); return false;">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>My Quotations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#invoices" class="nav-link" onclick="showSection('invoices'); return false;">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>My Invoices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#orders" class="nav-link" onclick="showSection('orders'); return false;">
                                <i class="nav-icon bi bi-cart"></i>
                                <p>My Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#support" class="nav-link" onclick="showSection('support'); return false;">
                                <i class="nav-icon bi bi-headset"></i>
                                <p>Support</p>
                            </a>
                        </li>
                        <li class="nav-header">SETTINGS</li>
                        <li class="nav-item">
                            <a href="#settings" class="nav-link" onclick="showSection('settings'); return false;">
                                <i class="nav-icon bi bi-gear"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!--end::Sidebar-->
        
        <!--begin::Main-->
        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Customer Portal</h1>
                        </div>
                    </div>
                    <?= $content ?>
                </div>
            </div>
        </main>
        <!--end::Main-->
    </div>
    <!--end::App Wrapper-->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="/dist/js/adminlte.min.js"></script>
    <script>
    function showSection(section) {
        // Hide all sections
        document.querySelectorAll('.tab-pane, .section-content').forEach(pane => {
            pane.classList.remove('show', 'active');
            pane.style.display = 'none';
        });
        // Remove active from all sidebar nav links
        document.querySelectorAll('.sidebar-menu .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Handle dashboard section (show welcome card, hide others)
        const welcomeCardRow = document.querySelector('.row:first-child');
        if (section === 'dashboard') {
            if (welcomeCardRow) {
                welcomeCardRow.style.display = 'block';
            }
        } else {
            // Show selected section
            const targetPane = document.getElementById(section);
            const targetLink = document.querySelector(`.sidebar-menu a[href="#${section}"]`);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                targetPane.style.display = 'block';
            }
            if (targetLink) {
                targetLink.classList.add('active');
            }
            // Hide welcome card when showing other sections
            if (welcomeCardRow) {
                welcomeCardRow.style.display = 'none';
            }
        }
        
        // Set active link
        const targetLink = document.querySelector(`.sidebar-menu a[href="#${section}"]`);
        if (targetLink) {
            targetLink.classList.add('active');
        }
    }
    </script>
</body>
</html>

