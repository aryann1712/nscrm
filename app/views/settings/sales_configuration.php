<?php ob_start(); ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Sales Configuration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/?action=dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/?action=settings">Settings</a></li>
                        <li class="breadcrumb-item active">Sales Configuration</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <style>
              /* Hide all individual Configure buttons globally */
              .card-body button.btn[onclick^="configureItem"] { display: none !important; }
              /* Make whole cards feel clickable */
              .card.h-100.border-0.shadow-sm { cursor: pointer; border-radius: 12px; box-shadow: 0 4px 14px rgba(31,59,87,0.08); transition: transform .08s ease, box-shadow .2s ease; }
              .card.h-100.border-0.shadow-sm:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(31,59,87,0.12); }

              /* Tile layout: icon | title+desc (left aligned) */
              .card.h-100.border-0.shadow-sm .card-body { display: flex; align-items: center; gap: 14px; text-align: left !important; padding: 16px 18px; }
              .card.h-100.border-0.shadow-sm .card-body i.bi { flex: 0 0 auto; font-size: 1.6rem; color: #1f3b57; background: #eef3f7; padding: 10px; border-radius: 10px; }
              .card.h-100.border-0.shadow-sm .card-body h5.card-title { margin: 0 0 2px 0; font-weight: 600; color: #1f2d3d; }
              .card.h-100.border-0.shadow-sm .card-body p.card-text { margin: 0; color: #6b7b8c; font-size: .92rem; }
            </style>
            <!-- Formats Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #ff6b35, #f7931e); color: white;">
                            <h3 class="card-title">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Formats
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Print Header</h5>
                                            <p class="card-text text-muted">Upload or create a header image to include in printables.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Print Header')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-arrow-down text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Print Footer</h5>
                                            <p class="card-text text-muted">Upload or create a footer image to include in printables.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Print Footer')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Bank Details</h5>
                                            <p class="card-text text-muted">Enter your bank details to include in Invoices, Orders, etc.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Bank Details')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-check text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Terms & Conditions</h5>
                                            <p class="card-text text-muted">Manage default terms and conditions to be used in Sales orders, Invoices, etc.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Terms & Conditions')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-check text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Digital Signature</h5>
                                            <p class="card-text text-muted">Upload the digital signature of your company to include in printables.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Digital Signature')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integrations Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #ff6b35, #f7931e); color: white;">
                            <h3 class="card-title">
                                <i class="bi bi-plug me-2"></i>
                                Integrations
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-qr-code text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">QR Code</h5>
                                            <p class="card-text text-muted">Upload the QR Code of your company to include in printables or enable quicker payments.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('QR Code')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-link-45deg text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Payment Link</h5>
                                            <p class="card-text text-muted">Add Payment Link of your company to enable quicker payments.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Payment Link')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-envelope text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Email Account</h5>
                                            <p class="card-text text-muted">Link your own email account to send emails from.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Email Account')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-plus-people text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Lead Platforms</h5>
                                            <p class="card-text text-muted">Integrate with other lead platforms like IndiaMART, TradeIndia, Justdial, Google & WhatsApp.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Lead Platforms')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-google text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Google Reviews</h5>
                                            <p class="card-text text-muted">Add Google Reviews Link of your company to get google reviews from your customers.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Google Reviews')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-search-code text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Website API Integration</h5>
                                            <p class="card-text text-muted">Set API for website integration.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Website API Integration')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">E-invoice (Beta)</h5>
                                            <p class="card-text text-muted">Set up your Biziverse to generate e-invoices easily.</p>
                                            <span class="badge bg-warning text-dark mb-2">Beta</span>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('E-invoice')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRM Section -->
            <div class="row mb-4" id="crmSection">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
                            <h3 class="card-title">
                                <i class="bi bi-people me-2"></i>
                                CRM (Leads & Prospects)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Sources</h5>
                                            <p class="card-text text-muted">Add all the different sources from where your leads are coming.</p>
                                            <!-- Configure button hidden globally; whole card is clickable -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-box-seam text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Product List</h5>
                                            <p class="card-text text-muted">Add products or services provided by you.</p>
                                            <!-- Configure button hidden globally; whole card is clickable -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-building text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">City List</h5>
                                            <p class="card-text text-muted">Manage the master entries of cities used for leads & connections.</p>
                                            <!-- Configure button hidden globally; whole card is clickable -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-tag text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Tags</h5>
                                            <p class="card-text text-muted">Manage the master entries of tags used for prospects & connections.</p>
                                            <!-- Configure button hidden globally; whole card is clickable -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-text text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Extra Fields</h5>
                                            <p class="card-text text-muted">Manage additional fields for leads and connections.</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="configureItem('Extra Fields')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-x-circle text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Rejection Reasons</h5>
                                            <p class="card-text text-muted">List reasons why a prospect may reject your appointment.</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="configureItem('Rejection Reasons')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-arrow-repeat text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Duplicate Lead Policy</h5>
                                            <p class="card-text text-muted">Allow import of leads that have the same mobile/email as existing leads.</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="configureItem('Duplicate Lead Policy')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-pause-circle text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Inactive Reasons</h5>
                                            <p class="card-text text-muted">Customize the list of reasons for marking a lead or prospect as inactive.</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="configureItem('Inactive Reasons')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">B2C-only Mode</h5>
                                            <p class="card-text text-muted">Enable 'B2C-only Mode' if you only sell to end consumers, not to businesses.</p>
                                            <button class="btn btn-outline-success btn-sm" onclick="configureItem('B2C-only Mode')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Documents Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white;">
                            <h3 class="card-title">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Sales Documents
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-three-people text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Customer Categories</h5>
                                            <p class="card-text text-muted">Manage the master of categories of customers used for Quotes & Invoices.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Customer Categories')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Templates</h5>
                                            <p class="card-text text-muted">Manage document templates to quickly create new documents.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Templates')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-box text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Billables (Services/Non-Stock Items)</h5>
                                            <p class="card-text text-muted">Manage Services / Non-Stock Items.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Billables')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Document Series</h5>
                                            <p class="card-text text-muted">Manage series for invoices, quotes, etc.</p>
                                            <span class="badge bg-warning text-dark mb-2">Upgrade</span>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Document Series')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-columns text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Extra Columns</h5>
                                            <p class="card-text text-muted">Configure additional columns to be shown in quotes and invoices.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Extra Columns')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-text text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Extra Fields</h5>
                                            <p class="card-text text-muted">Configure additional fields to be shown in quotes, invoices etc.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Extra Fields')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-pen text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Contract Types</h5>
                                            <p class="card-text text-muted">Manage your contract types for contract entries.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Contract Types')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-cart text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Order Stages</h5>
                                            <p class="card-text text-muted">Manage Order Stages to give quick order updates.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Order Stages')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-clock text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Invoice Credit Days</h5>
                                            <p class="card-text text-muted">Set the number of days to calculate the Due Date from the Invoice date.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Invoice Credit Days')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #ff6b35, #f7931e); color: white;">
                            <h3 class="card-title">
                                <i class="bi bi-gear me-2"></i>
                                Settings
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-box-arrow-up text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Batch Selection</h5>
                                            <p class="card-text text-muted">Activate this option to keep track of serial/batch of Batch level, with option of Expiry also.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Batch Selection')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">MRP</h5>
                                            <p class="card-text text-muted">Show MRP (Maximum Retail Price) of your sales items.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('MRP')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-upc text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">Barcode Generation</h5>
                                            <p class="card-text text-muted">Generate a range to generate and print numeric barcodes.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('Barcode Generation')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="bi bi-currency-rupee text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-2">GST Ledgers</h5>
                                            <p class="card-text text-muted">Set up ledgers to tag GST taxes.</p>
                                            <button class="btn btn-outline-primary btn-sm" onclick="configureItem('GST Ledgers')">
                                                <i class="bi bi-gear me-1"></i>Configure
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bank Details Modal -->
<div class="modal fade" id="bankModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bank Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-end mb-2">
          <button class="btn btn-warning btn-sm" id="addBankBtn">+ Add</button>
        </div>
        <div id="banksList" class="vstack gap-2"></div>
      </div>
    </div>
  </div>
 </div>

 <!-- Cities Modal -->
 <div class="modal fade" id="citiesModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Cities</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="d-flex justify-content-end mb-2">
           <button class="btn btn-warning btn-sm" id="addCityBtn">+ Add</button>
         </div>
         <div id="citiesList" class="vstack gap-2"></div>
       </div>
     </div>
   </div>
 </div>

 <!-- Tags Modal -->
 <div class="modal fade" id="tagsModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Tags</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="d-flex justify-content-end mb-2">
           <button class="btn btn-warning btn-sm" id="addTagBtn">+ Add</button>
         </div>
         <div id="tagsList" class="vstack gap-2"></div>
       </div>
     </div>
   </div>
 </div>

 <!-- Lead Products Modal -->
 <div class="modal fade" id="leadProductsModal" tabindex="-1">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Lead Products</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="d-flex justify-content-between align-items-center mb-2">
           <div class="fw-bold">Products</div>
           <button class="btn btn-primary btn-sm" id="addLeadProductBtn"><i class="bi bi-plus-lg me-1"></i>Add</button>
         </div>
         <div id="leadProductsList" class="list-group"></div>
       </div>
     </div>
   </div>
 </div>

 <!-- Lead Sources Modal -->
 <div class="modal fade" id="sourcesModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Lead Sources</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="d-flex justify-content-end mb-2">
           <button class="btn btn-warning btn-sm" id="addSourceBtn">+ Add</button>
         </div>
         <div id="sourcesList" class="vstack gap-2"></div>
       </div>
     </div>
   </div>
  </div>

<!-- Print Header Modal -->
<div class="modal fade" id="printHeaderModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print Header</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <img id="printHeaderPreview" src="" alt="Print Header" style="max-width:100%; max-height:260px; display:none; border:1px solid #eee; border-radius:6px;"/>
        </div>
        <div class="mb-3">
          <label class="form-label">Upload header image (PNG/JPEG/WEBP)</label>
          <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control" id="printHeaderFile" />
        </div>
        <button class="btn btn-outline-secondary" id="removePrintHeaderBtn"><i class="bi bi-trash"></i> Remove Header</button>
        <button class="btn btn-success float-end" id="savePrintHeaderBtn"><i class="bi bi-check"></i> Save</button>
      </div>
    </div>
  </div>
 </div>

<!-- Print Footer Modal -->
<div class="modal fade" id="printFooterModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print Footer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <img id="printFooterPreview" src="" alt="Print Footer" style="max-width:100%; max-height:260px; display:none; border:1px solid #eee; border-radius:6px;"/>
        </div>
        <div class="mb-3">
          <label class="form-label">Upload footer image (PNG/JPEG/WEBP)</label>
          <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control" id="printFooterFile" />
        </div>
        <button class="btn btn-outline-secondary" id="removePrintFooterBtn"><i class="bi bi-trash"></i> Remove Footer</button>
        <button class="btn btn-success float-end" id="savePrintFooterBtn"><i class="bi bi-check"></i> Save</button>
      </div>
    </div>
  </div>
 </div>

<!-- Terms & Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Manage Terms & Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div></div>
          <button class="btn btn-warning btn-sm" id="addTermBtn">+ Add</button>
        </div>
        <div id="termsList" class="vstack gap-2" style="max-height:400px; overflow:auto"></div>
        <div class="mt-3">
          <button class="btn btn-success" id="saveOrdersBtn"><i class="bi bi-check"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
 </div>

<!-- Digital Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Digital Signature</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3 text-center">
          <img id="signaturePreview" src="" alt="Signature" style="max-width:100%; max-height:260px; display:none; border:1px solid #eee; border-radius:6px;"/>
        </div>
        <div class="mb-3">
          <label class="form-label">Upload signature image (PNG/JPEG/WEBP)</label>
          <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control" id="signatureFile" />
        </div>
        <button class="btn btn-outline-secondary" id="removeSignatureBtn"><i class="bi bi-trash"></i> Remove Signature</button>
        <button class="btn btn-success float-end" id="saveSignatureBtn"><i class="bi bi-check"></i> Save</button>
      </div>
    </div>
  </div>
 </div>

<script>
function showModalById(id){
  const el = document.getElementById(id);
  if (!el) return false;
  try { new bootstrap.Modal(el).show(); return true; } catch(e){ return false; }
}
// Toast helper
function showToast(message, variant='success'){
  const container = document.getElementById('toastContainer');
  if (!container) { console.log(message); return; }
  const wrapper = document.createElement('div');
  wrapper.className = `toast align-items-center text-bg-${variant} border-0`;
  wrapper.role = 'alert';
  wrapper.ariaLive = 'assertive';
  wrapper.ariaAtomic = 'true';
  wrapper.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>`;
  container.appendChild(wrapper);
  const t = new bootstrap.Toast(wrapper, { delay: 2500 });
  t.show();
  wrapper.addEventListener('hidden.bs.toast', ()=> wrapper.remove());
}
function configureItem(itemName) {
  if (itemName === 'Bank Details') {
    refreshBanks();
    if (!showModalById('bankModal')) alert('Bank Details dialog is not available on this page.');
    return;
  }
  if (itemName === 'Sources') {
    refreshSources();
    if (!showModalById('sourcesModal')) alert('Sources dialog is not available on this page.');
    return;
  }
  if (itemName === 'Print Header' || itemName === 'Print Footer') {
    // Only use modals (inline Formats Manager removed)
    if (itemName === 'Print Header') { refreshPrintHeader(); showModalById('printHeaderModal'); }
    if (itemName === 'Print Footer') { refreshPrintFooter(); showModalById('printFooterModal'); }
    return;
  }
  if (itemName === 'Terms & Conditions') {
    refreshTerms();
    if (!showModalById('termsModal')) alert('Terms & Conditions dialog is not available on this page.');
    return;
  }
  if (itemName === 'Digital Signature') {
    // Signature modal exists on page; show it
    refreshSignature();
    if (!showModalById('signatureModal')) alert('Signature dialog is not available on this page.');
    return;
  }
  if (itemName === 'Product List') {
    refreshLeadProducts();
    if (!showModalById('leadProductsModal')) alert('Lead Products dialog is not available on this page.');
    return;
  }
  if (itemName === 'Cities' || itemName === 'City List') {
    refreshCities();
    if (!showModalById('citiesModal')) alert('Cities dialog is not available on this page.');
    return;
  }
  if (itemName === 'Tags') {
    refreshTags();
    if (!showModalById('tagsModal')) alert('Tags dialog is not available on this page.');
    return;
  }
  alert(`Configuration for "${itemName}" is not available yet.`);
}

// --- Print Header ---
document.addEventListener('DOMContentLoaded', function(){
  // Print Header modal wiring
  const phFileM = document.getElementById('printHeaderFile');
  const phPrevM = document.getElementById('printHeaderPreview');
  const phSaveM = document.getElementById('savePrintHeaderBtn');
  const phRemoveM = document.getElementById('removePrintHeaderBtn');
  const phModalEl = document.getElementById('printHeaderModal');
  if (phModalEl) phModalEl.addEventListener('shown.bs.modal', ()=>{ refreshPrintHeader(); });
  if (phFileM) {
    phFileM.addEventListener('change', ()=>{
      const f = phFileM.files && phFileM.files[0]; if (!f) return;
      phPrevM.src = URL.createObjectURL(f); phPrevM.style.display='block';
    });
  }
  if (phSaveM) phSaveM.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    const f = phFileM && phFileM.files && phFileM.files[0]; if (!f) { showToast('Choose an image', 'danger'); return; }
    const fd = new FormData(); fd.append('image', f);
    const res = await fetch('/?action=salesConfig&subaction=uploadPrintHeader', {method:'POST', body:fd});
    const j = await res.json().catch(()=>({}));
    if (!res.ok || j.success===false) { showToast(j.error||'Upload failed', 'danger'); return; }
    showToast('Header saved', 'success'); refreshPrintHeader();
    try { bootstrap.Modal.getInstance(document.getElementById('printHeaderModal'))?.hide(); } catch(e){}
  });
  if (phRemoveM) phRemoveM.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    if (!confirm('Remove current header?')) return;
    const res = await fetch('/?action=salesConfig&subaction=removePrintHeader', {method:'POST'});
    const j = await res.json().catch(()=>({}));
    if (!res.ok || j.success===false) { showToast(j.error||'Remove failed', 'danger'); return; }
    showToast('Header removed', 'success'); refreshPrintHeader();
    try { bootstrap.Modal.getInstance(document.getElementById('printHeaderModal'))?.hide(); } catch(e){}
  });
});

async function refreshPrintHeader(){
  try{
    const res = await fetch('/?action=salesConfig&subaction=getPrintHeader');
    const j = await res.json();
    const img = document.getElementById('printHeaderPreview');
    if (j.exists && j.url){ img.src = j.url; img.style.display = 'block'; } else { img.removeAttribute('src'); img.style.display = 'none'; }
    const f = document.getElementById('printHeaderFile'); if (f) f.value = '';
  } catch(e) {}
}

// --- Print Footer ---
document.addEventListener('DOMContentLoaded', function(){
  const pfFileM = document.getElementById('printFooterFile');
  const pfPrevM = document.getElementById('printFooterPreview');
  const pfSaveM = document.getElementById('savePrintFooterBtn');
  const pfRemoveM = document.getElementById('removePrintFooterBtn');
  const pfModalEl = document.getElementById('printFooterModal');
  if (pfModalEl) pfModalEl.addEventListener('shown.bs.modal', ()=>{ refreshPrintFooter(); });
  if (pfFileM) {
    pfFileM.addEventListener('change', ()=>{
      const f = pfFileM.files && pfFileM.files[0]; if (!f) return;
      pfPrevM.src = URL.createObjectURL(f); pfPrevM.style.display='block';
    });
  }
  if (pfSaveM) pfSaveM.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    const f = pfFileM && pfFileM.files && pfFileM.files[0]; if (!f) { showToast('Choose an image', 'danger'); return; }
    const fd = new FormData(); fd.append('image', f);
    const res = await fetch('/?action=salesConfig&subaction=uploadPrintFooter', {method:'POST', body:fd});
    const j = await res.json().catch(()=>({}));
    if (!res.ok || j.success===false) { showToast(j.error||'Upload failed', 'danger'); return; }
    showToast('Footer saved', 'success'); refreshPrintFooter();
    try { bootstrap.Modal.getInstance(document.getElementById('printFooterModal'))?.hide(); } catch(e){}
  });
  if (pfRemoveM) pfRemoveM.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    if (!confirm('Remove current footer?')) return;
    const res = await fetch('/?action=salesConfig&subaction=removePrintFooter', {method:'POST'});
    const j = await res.json().catch(()=>({}));
    if (!res.ok || j.success===false) { showToast(j.error||'Remove failed', 'danger'); return; }
    showToast('Footer removed', 'success'); refreshPrintFooter();
    try { bootstrap.Modal.getInstance(document.getElementById('printFooterModal'))?.hide(); } catch(e){}
  });
});

async function refreshPrintFooter(){
  try{
    const res = await fetch('/?action=salesConfig&subaction=getPrintFooter');
    const j = await res.json();
    const img = document.getElementById('printFooterPreview');
    if (j.exists && j.url){ img.src = j.url; img.style.display = 'block'; } else { img.removeAttribute('src'); img.style.display = 'none'; }
    const f = document.getElementById('printFooterFile'); if (f) f.value = '';
  } catch(e) {}
}

// Ensure handlers are bound regardless of where configureItem is defined
document.addEventListener('DOMContentLoaded', function(){
  // Print Header bindings
  const phFile = document.getElementById('printHeaderFile');
  const phSave = document.getElementById('savePrintHeaderBtn');
  const phRemove = document.getElementById('removePrintHeaderBtn');
  if (phFile && !phFile.__bound) {
    phFile.addEventListener('change', ()=>{
      const file = phFile.files && phFile.files[0]; if (!file) return;
      const url = URL.createObjectURL(file);
      const img = document.getElementById('printHeaderPreview');
      img.src = url; img.style.display = 'block';
    });
    phFile.__bound = true;
  }
  if (phSave && !phSave.__bound) {
    phSave.addEventListener('click', async ()=>{
      const file = phFile && phFile.files && phFile.files[0]; if (!file) { alert('Choose an image'); return; }
      const fd = new FormData(); fd.append('image', file);
      const res = await fetch('/?action=salesConfig&subaction=uploadPrintHeader', {method:'POST', body:fd});
      let j = {}; try { j = await res.json(); } catch(e) {}
      if (!res.ok) { alert(j.error ? `Upload failed: ${j.error}${j.code? ' (code '+j.code+')':''}` : 'Upload failed'); return; }
      if (j.success) { alert('Header saved'); refreshPrintHeader(); } else { alert(j.error||'Failed to save'); }
    });
    phSave.__bound = true;
  }
  if (phRemove && !phRemove.__bound) {
    phRemove.addEventListener('click', async ()=>{
      if (!confirm('Remove current header?')) return;
      const res = await fetch('/?action=salesConfig&subaction=removePrintHeader', {method:'POST'});
      let j = {}; try { j = await res.json(); } catch(e) {}
      if (!res.ok) { alert(j.error ? `Remove failed: ${j.error}` : 'Remove failed'); return; }
      if (j.success) { alert('Header removed'); refreshPrintHeader(); } else { alert(j.error||'Failed to remove'); }
    });
    phRemove.__bound = true;
  }

  // Print Footer bindings
  const pfFile = document.getElementById('printFooterFile');
  const pfSave = document.getElementById('savePrintFooterBtn');
  const pfRemove = document.getElementById('removePrintFooterBtn');
  if (pfFile && !pfFile.__bound) {
    pfFile.addEventListener('change', ()=>{
      const file = pfFile.files && pfFile.files[0]; if (!file) return;
      const url = URL.createObjectURL(file);
      const img = document.getElementById('printFooterPreview');
      img.src = url; img.style.display = 'block';
    });
    pfFile.__bound = true;
  }
  if (pfSave && !pfSave.__bound) {
    pfSave.addEventListener('click', async ()=>{
      const file = pfFile && pfFile.files && pfFile.files[0]; if (!file) { alert('Choose an image'); return; }
      const fd = new FormData(); fd.append('image', file);
      const res = await fetch('/?action=salesConfig&subaction=uploadPrintFooter', {method:'POST', body:fd});
      let j = {}; try { j = await res.json(); } catch(e) {}
      if (!res.ok) { alert(j.error ? `Upload failed: ${j.error}${j.code? ' (code '+j.code+')':''}` : 'Upload failed'); return; }
      if (j.success) { alert('Footer saved'); refreshPrintFooter(); } else { alert(j.error||'Failed to save'); }
    });
    pfSave.__bound = true;
  }
  if (pfRemove && !pfRemove.__bound) {
    pfRemove.addEventListener('click', async ()=>{
      if (!confirm('Remove current footer?')) return;
      const res = await fetch('/?action=salesConfig&subaction=removePrintFooter', {method:'POST'});
      let j = {}; try { j = await res.json(); } catch(e) {}
      if (!res.ok) { alert(j.error ? `Remove failed: ${j.error}` : 'Remove failed'); return; }
      if (j.success) { alert('Footer removed'); refreshPrintFooter(); } else { alert(j.error||'Failed to remove'); }
    });
    pfRemove.__bound = true;
  }
});


// --- Banks ---
async function refreshBanks(){
  const res = await fetch('/?action=salesConfig&subaction=listBanks');
  const data = await res.json();
  const box = document.getElementById('banksList');
  box.innerHTML = data.map(b=>`
    <div class="p-3 bg-light rounded d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-bold">${escapeHtml(b.bank_name)}</div>
        <div>Account No  -  ${escapeHtml(b.account_no)}</div>
        <div>Branch  -  ${escapeHtml(b.branch||'')}</div>
        <div>IFSC  -  ${escapeHtml(b.ifsc||'')}</div>
      </div>
      <div class="text-end">
        ${b.is_default ? '<span class="badge bg-success me-2">Default</span>' : ''}
        <button class="btn btn-sm btn-outline-secondary me-2" onclick='editBank(${b.id})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick='deleteBank(${b.id})'><i class="bi bi-trash"></i></button>
      </div>
    </div>`).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addBankBtn'){
    bankForm();
  }
});

function bankForm(pref={}){
  const html = `
    <form id="bankForm" class="vstack gap-2">
      <input type="hidden" name="id" value="${pref.id||''}">
      <div class="row g-2">
        <div class="col-md-6"><input required name="bank_name" class="form-control" placeholder="Bank Name" value="${pref.bank_name||''}"></div>
        <div class="col-md-6"><input required name="account_no" class="form-control" placeholder="Account No" value="${pref.account_no||''}"></div>
        <div class="col-md-6"><input name="branch" class="form-control" placeholder="Branch" value="${pref.branch||''}"></div>
        <div class="col-md-6"><input name="ifsc" class="form-control" placeholder="IFSC" value="${pref.ifsc||''}"></div>
        <div class="col-12 form-check ms-2"><input type="checkbox" class="form-check-input" id="is_default" name="is_default" ${pref.is_default? 'checked':''}><label for="is_default" class="form-check-label">Default</label></div>
      </div>
      <div class="text-end"><button class="btn btn-primary">Save</button></div>
    </form>`;
  const body = document.querySelector('#bankModal .modal-body');
  body.insertAdjacentHTML('beforeend', `<div id="bankFormWrapper" class="mt-3">${html}</div>`);
  const form = document.getElementById('bankForm');
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(form);
    const isEdit = !!fd.get('id');
    await fetch('/?action=salesConfig&subaction='+(isEdit?'updateBank':'createBank'), {method:'POST', body:fd});
    document.getElementById('bankFormWrapper').remove();
    refreshBanks();
  });
}

async function editBank(id){
  const res = await fetch('/?action=salesConfig&subaction=listBanks');
  const data = await res.json();
  const b = data.find(x=>x.id==id);
  bankForm(b||{});
}
async function deleteBank(id){
  if(!confirm('Delete this bank?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteBank', {method:'POST', body:fd});
  refreshBanks();
}

// --- Lead Sources ---
async function refreshSources(){
  const res = await fetch('/?action=salesConfig&subaction=listSources');
  const data = await res.json();
  const box = document.getElementById('sourcesList');
  box.innerHTML = data.map(s=>`
    <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-bold">${escapeHtml(s.name)}</div>
        ${s.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
      </div>
      <div>
        <button class="btn btn-sm btn-outline-secondary me-2" onclick='editSource(${s.id})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick='deleteSource(${s.id})'><i class="bi bi-trash"></i></button>
      </div>
    </div>`).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addSourceBtn'){
    sourceForm();
  }
});

function sourceForm(pref={}){
  const html = `
    <form id="sourceForm" class="vstack gap-2">
      <input type="hidden" name="id" value="${pref.id||''}">
      <div class="row g-2">
        <div class="col-md-8"><input required name="name" class="form-control" placeholder="Source name" value="${pref.name||''}"></div>
        <div class="col-md-4 form-check ms-2 align-self-center"><input type="checkbox" class="form-check-input" id="src_active" name="is_active" ${pref.is_active? 'checked':''}><label for="src_active" class="form-check-label ms-2">Active</label></div>
      </div>
      <div class="text-end"><button class="btn btn-primary">Save</button></div>
    </form>`;
  const body = document.querySelector('#sourcesModal .modal-body');
  body.insertAdjacentHTML('beforeend', `<div id="sourceFormWrapper" class="mt-3">${html}</div>`);
  const form = document.getElementById('sourceForm');
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(form);
    const isEdit = !!fd.get('id');
    const sub = isEdit ? 'updateSource' : 'createSource';
    const res = await fetch('/?action=salesConfig&subaction='+sub, {method:'POST', body:fd});
    if (!res.ok) { try { const j = await res.json(); alert(j.error||'Failed'); } catch(e){ alert('Failed'); } }
    const wrap = document.getElementById('sourceFormWrapper'); if (wrap) wrap.remove();
    refreshSources();
  });
}

async function editSource(id){
  const res = await fetch('/?action=salesConfig&subaction=listSources');
  const data = await res.json();
  const s = data.find(x=>x.id==id);
  sourceForm(s||{});
}

async function deleteSource(id){
  if(!confirm('Delete this source?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteSource', {method:'POST', body:fd});
  refreshSources();
}

// --- Lead Products ---
async function refreshLeadProducts(){
  const res = await fetch('/?action=salesConfig&subaction=listLeadProducts');
  const data = await res.json();
  const box = document.getElementById('leadProductsList');
  box.innerHTML = data.map(p=>`
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>${escapeHtml(p.name)}</div>
      <button class="btn btn-sm btn-outline-danger" onclick='deleteLeadProduct(${p.id})'><i class="bi bi-trash"></i></button>
    </div>
  `).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addLeadProductBtn'){
    leadProductForm();
  }
});

function leadProductForm(){
  const html = `
    <form id="leadProductForm" class="vstack gap-2">
      <input type="text" required name="name" class="form-control" placeholder="Product name">
      <div class="text-end">
        <button class="btn btn-primary">Save</button>
      </div>
    </form>`;
  const body = document.querySelector('#leadProductsModal .modal-body');
  body.insertAdjacentHTML('beforeend', `<div id="leadProductFormWrapper" class="mt-2">${html}</div>`);
  const form = document.getElementById('leadProductForm');
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(form);
    const res = await fetch('/?action=salesConfig&subaction=createLeadProduct', {method:'POST', body:fd});
    if (!res.ok) { try { const j = await res.json(); alert(j.error||'Failed'); } catch(e){ alert('Failed'); } }
    const w = document.getElementById('leadProductFormWrapper'); if (w) w.remove();
    refreshLeadProducts();
  });
}

async function deleteLeadProduct(id){
  if(!confirm('Delete this product?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteLeadProduct', {method:'POST', body:fd});
  refreshLeadProducts();
}

// --- Cities ---
async function refreshCities(){
  const res = await fetch('/?action=salesConfig&subaction=listCities');
  const data = await res.json();
  const box = document.getElementById('citiesList');
  box.innerHTML = data.map(c=>`
    <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-bold">${escapeHtml(c.name)}</div>
        ${c.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
      </div>
      <div>
        <button class="btn btn-sm btn-outline-secondary me-2" onclick='editCity(${c.id})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick='deleteCity(${c.id})'><i class="bi bi-trash"></i></button>
      </div>
    </div>`).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addCityBtn') cityForm();
});

function cityForm(pref={}){
  const html = `
    <form id="cityForm" class="vstack gap-2">
      <input type="hidden" name="id" value="${pref.id||''}">
      <div class="row g-2">
        <div class="col-md-8"><input required name="name" class="form-control" placeholder="City name" value="${pref.name||''}"></div>
        <div class="col-md-4 form-check ms-2 align-self-center"><input type="checkbox" class="form-check-input" id="city_active" name="is_active" ${pref.is_active? 'checked':''}><label for="city_active" class="form-check-label ms-2">Active</label></div>
      </div>
      <div class="text-end"><button class="btn btn-primary">Save</button></div>
    </form>`;
  const body = document.querySelector('#citiesModal .modal-body');
  body.insertAdjacentHTML('beforeend', `<div id="cityFormWrapper" class="mt-3">${html}</div>`);
  const form = document.getElementById('cityForm');
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(form);
    const sub = fd.get('id') ? 'updateCity' : 'createCity';
    const res = await fetch('/?action=salesConfig&subaction='+sub, {method:'POST', body:fd});
    if (!res.ok) { try { const j = await res.json(); alert(j.error||'Failed'); } catch(e){ alert('Failed'); } }
    const w = document.getElementById('cityFormWrapper'); if (w) w.remove();
    refreshCities();
  });
}

async function editCity(id){
  const res = await fetch('/?action=salesConfig&subaction=listCities');
  const data = await res.json();
  const c = data.find(x=>x.id==id);
  cityForm(c||{});
}

async function deleteCity(id){
  if(!confirm('Delete this city?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteCity', {method:'POST', body:fd});
  refreshCities();
}

// --- Tags ---
async function refreshTags(){
  const res = await fetch('/?action=salesConfig&subaction=listTags');
  const data = await res.json();
  const box = document.getElementById('tagsList');
  box.innerHTML = data.map(t=>`
    <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-bold">${escapeHtml(t.name)}</div>
        ${t.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
      </div>
      <div>
        <button class="btn btn-sm btn-outline-secondary me-2" onclick='editTag(${t.id})'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick='deleteTag(${t.id})'><i class="bi bi-trash"></i></button>
      </div>
    </div>`).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addTagBtn') tagForm();
});

// Make entire cards clickable to open their respective configuration
document.addEventListener('DOMContentLoaded', function(){
  const cards = document.querySelectorAll('.card.h-100.border-0.shadow-sm');
  cards.forEach(card => {
    const body = card.querySelector('.card-body');
    const titleEl = body && body.querySelector('h5.card-title');
    if (!body || !titleEl) return;
    const name = titleEl.textContent.trim();
    body.addEventListener('click', (ev) => {
      // Ignore clicks that originated from inputs/buttons inside
      const t = ev.target;
      if (t.closest('button') || t.closest('a') || t.closest('input') || t.closest('label')) return;
      configureItem(name);
    });
  });
});

function tagForm(pref={}){
  const html = `
    <form id="tagForm" class="vstack gap-2">
      <input type="hidden" name="id" value="${pref.id||''}">
      <div class="row g-2">
        <div class="col-md-8"><input required name="name" class="form-control" placeholder="Tag name" value="${pref.name||''}"></div>
        <div class="col-md-4 form-check ms-2 align-self-center"><input type="checkbox" class="form-check-input" id="tag_active" name="is_active" ${pref.is_active? 'checked':''}><label for="tag_active" class="form-check-label ms-2">Active</label></div>
      </div>
      <div class="text-end"><button class="btn btn-primary">Save</button></div>
    </form>`;
  const body = document.querySelector('#tagsModal .modal-body');
  body.insertAdjacentHTML('beforeend', `<div id="tagFormWrapper" class="mt-3">${html}</div>`);
  const form = document.getElementById('tagForm');
  form.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(form);
    const sub = fd.get('id') ? 'updateTag' : 'createTag';
    const res = await fetch('/?action=salesConfig&subaction='+sub, {method:'POST', body:fd});
    if (!res.ok) { try { const j = await res.json(); alert(j.error||'Failed'); } catch(e){ alert('Failed'); } }
    const w = document.getElementById('tagFormWrapper'); if (w) w.remove();
    refreshTags();
  });
}

async function editTag(id){
  const res = await fetch('/?action=salesConfig&subaction=listTags');
  const data = await res.json();
  const t = data.find(x=>x.id==id);
  tagForm(t||{});
}

async function deleteTag(id){
  if(!confirm('Delete this tag?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteTag', {method:'POST', body:fd});
  refreshTags();
}
// --- Terms ---
let currentTerms = [];
async function refreshTerms(){
  const res = await fetch('/?action=salesConfig&subaction=listTerms');
  currentTerms = await res.json();
  renderTerms();
}
function renderTerms(){
  const box = document.getElementById('termsList');
  box.innerHTML = currentTerms.map((t,idx)=>`
    <div class="input-group">
      <span class="input-group-text cursor-grab">#${idx+1}</span>
      <input class="form-control" value="${escapeHtml(t.text)}" oninput="currentTerms.find(x=>x.id==${t.id}).text=this.value">
      <span class="input-group-text"><input type="checkbox" ${t.is_active? 'checked':''} onchange="currentTerms.find(x=>x.id==${t.id}).is_active=this.checked?1:0"></span>
      <button class="btn btn-outline-danger" onclick="deleteTerm(${t.id})"><i class='bi bi-trash'></i></button>
    </div>`).join('');
}

document.addEventListener('click', (e)=>{
  if(e.target && e.target.id==='addTermBtn'){
    addTermRow();
  }
  if(e.target && e.target.id==='saveOrdersBtn'){
    saveTerms();
  }
});

function addTermRow(){
  const tempId = Date.now();
  currentTerms.push({id: tempId, text:'', is_active:1, _new:true});
  renderTerms();
}

async function saveTerms(){
  // Persist edits, creates; order as current index
  for (let i=0;i<currentTerms.length;i++){
    const t = currentTerms[i];
    const fd = new FormData();
    fd.append('text', t.text);
    fd.append('is_active', t.is_active? '1':'0');
    fd.append('display_order', String((i+1)*10));
    if (t._new){
      const res = await fetch('/?action=salesConfig&subaction=createTerm', {method:'POST', body:fd});
      const j = await res.json(); t.id = j.id; delete t._new;
    } else {
      fd.append('id', t.id);
      await fetch('/?action=salesConfig&subaction=updateTerm', {method:'POST', body:fd});
    }
  }
  // Ensure order synced
  const orders = {}; currentTerms.forEach((t,idx)=> orders[t.id] = (idx+1)*10);
  const fd2 = new FormData(); fd2.append('orders', JSON.stringify(orders));
  await fetch('/?action=salesConfig&subaction=reorderTerms', {method:'POST', body:fd2});
  refreshTerms();
}

async function deleteTerm(id){
  if(!confirm('Delete this term?')) return;
  const fd = new FormData(); fd.append('id', id);
  await fetch('/?action=salesConfig&subaction=deleteTerm', {method:'POST', body:fd});
  refreshTerms();
}

function escapeHtml(str){ return (str||'').replace(/[&<>"]/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[s])); }

// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to configuration cards
    const cards = document.querySelectorAll('.card-body');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
// Wire Digital Signature actions
const fileInput = document.getElementById('signatureFile');
const saveBtn = document.getElementById('saveSignatureBtn');
const removeBtn = document.getElementById('removeSignatureBtn');
if (fileInput) {
  fileInput.addEventListener('change', ()=>{
    const f = fileInput.files && fileInput.files[0];
    if (!f) return;
    const url = URL.createObjectURL(f);
    const img = document.getElementById('signaturePreview');
    img.src = url; img.style.display = 'block';
  });
}
if (saveBtn) {
  saveBtn.addEventListener('click', async ()=>{
    const f = fileInput && fileInput.files && fileInput.files[0];
    if (!f) { alert('Please choose an image file'); return; }
    const fd = new FormData(); fd.append('signature', f);
    const res = await fetch('/?action=salesConfig&subaction=uploadSignature', { method:'POST', body: fd });
    let j = {}; try { j = await res.json(); } catch(e) {}
    if (!res.ok) { alert(j.error ? `Upload failed: ${j.error}${j.code? ' (code '+j.code+')':''}` : 'Upload failed'); return; }
    if (j.success) { alert('Signature saved'); refreshSignature(); }
    else { alert(j.error||'Failed to save'); }
  });
}
if (removeBtn) {
  removeBtn.addEventListener('click', async ()=>{
    if (!confirm('Remove current signature?')) return;
    const res = await fetch('/?action=salesConfig&subaction=removeSignature', { method:'POST' });
    let j = {}; try { j = await res.json(); } catch(e) {}
    if (!res.ok) { alert(j.error ? `Remove failed: ${j.error}` : 'Remove failed'); return; }
    if (j.success) { alert('Signature removed'); refreshSignature(); }
    else { alert(j.error||'Failed to remove'); }
  });
}

async function refreshSignature(){
  try{
    const res = await fetch('/?action=salesConfig&subaction=getSignature');
    const j = await res.json();
    const img = document.getElementById('signaturePreview');
    if (j.exists && j.url){ img.src = j.url; img.style.display = 'block'; }
    else { img.removeAttribute('src'); img.style.display = 'none'; }
    if (document.getElementById('signatureFile')) document.getElementById('signatureFile').value = '';
  }catch(e){ /* ignore */ }
}

// ----- Print Header/Footer quick UI -----
function injectFormatsQuickUI(){ /* inline Formats Manager removed intentionally */ }

async function refreshPrintHeader(){
  try{
    const res = await fetch('/?action=salesConfig&subaction=getPrintHeader');
    const j = await res.json();
    const imgM = document.getElementById('printHeaderPreviewModal');
    if (imgM) {
      if (j.exists && j.url){ imgM.src = j.url; imgM.style.display = 'block'; }
      else { imgM.removeAttribute('src'); imgM.style.display = 'none'; }
    }
  }catch(e){}
}
async function refreshPrintFooter(){
  try{
    const res = await fetch('/?action=salesConfig&subaction=getPrintFooter');
    const j = await res.json();
    const imgM = document.getElementById('printFooterPreviewModal');
    if (imgM) {
      if (j.exists && j.url){ imgM.src = j.url; imgM.style.display = 'block'; }
      else { imgM.removeAttribute('src'); imgM.style.display = 'none'; }
    }
  } catch(e) {}
}

document.addEventListener('DOMContentLoaded', injectFormatsQuickUI);

</script>

<!-- Print Header Modal -->
<div class="modal fade" id="printHeaderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print Header</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img id="printHeaderPreviewModal" style="max-width:100%;max-height:160px;display:none;border:1px dashed #ccc;padding:4px;background:#fafafa"/>
        <div class="mt-2">
          <input type="file" id="printHeaderFileModal" accept="image/png,image/jpeg,image/webp" class="form-control"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" id="removePrintHeaderBtnModal">Remove</button>
        <button type="button" class="btn btn-primary" id="savePrintHeaderBtnModal">Save</button>
      </div>
    </div>
  </div>
  
</div>

<!-- Print Footer Modal -->
<div class="modal fade" id="printFooterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print Footer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img id="printFooterPreviewModal" style="max-width:100%;max-height:160px;display:none;border:1px dashed #ccc;padding:4px;background:#fafafa"/>
        <div class="mt-2">
          <input type="file" id="printFooterFileModal" accept="image/png,image/jpeg,image/webp" class="form-control"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" id="removePrintFooterBtnModal">Remove</button>
        <button type="button" class="btn btn-primary" id="savePrintFooterBtnModal">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Global Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080"></div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';

