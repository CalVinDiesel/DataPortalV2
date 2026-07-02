<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Inquiries - Admin | 3DHub</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/admin-responsive.css" />
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>

  <!-- CesiumJS for embedded map preview -->
  <link href="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
  <script src="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Cesium.js"></script>

  <style>
    /* === Admin Glass Nav === */
    .admin-glass-nav {
      position: fixed; top: 1.5rem; left: 1.5rem; right: 1.5rem; z-index: 1050;
      background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4);
      border-radius: 1.25rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      padding: 0.5rem 1.5rem; display: flex; align-items: center; transition: all 0.3s ease;
    }
    [data-bs-theme="dark"] .admin-glass-nav { background: rgba(15,23,42,0.7); border-color: rgba(255,255,255,0.08); }
    .admin-nav-links { display: flex; gap: 0.5rem; margin-left: 1.5rem; align-items: center; }
    .admin-nav-link { color: #566a7f; font-weight: 500; text-decoration: none; transition: all 0.2s; font-size: 0.82rem; padding: 0.4rem 0.6rem; border-radius: 0.75rem; white-space: nowrap; }
    .admin-nav-link:hover { color: #696cff; background: rgba(105,108,255,0.08); }
    .admin-nav-link.active { color: #696cff; background: rgba(105,108,255,0.12); font-weight: 700; }
    .email-hover-link { color: #8e94a3 !important; transition: color 0.2s; }
    .email-hover-link:hover { color: #696cff !important; }
    .content-wrapper-premium { margin-top: 8.5rem !important; }
    .layout-page { padding: 0 !important; }
    @media(max-width:1199.98px) { .admin-nav-links { display: none; } }

    /* === Status Badges === */
    .sb { display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .7rem;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap; }
    .sb .dot { width:7px;height:7px;border-radius:50%; }
    .sb-pending    { background:#fffbeb;color:#92400e;border:1.5px solid #fcd34d; } .sb-pending .dot    { background:#f59e0b; }
    .sb-reviewed   { background:#f0f9ff;color:#0c4a6e;border:1.5px solid #7dd3fc; } .sb-reviewed .dot   { background:#0ea5e9; }
    .sb-quoted     { background:#f0f0ff;color:#3730a3;border:1.5px solid #c7d2fe; } .sb-quoted .dot     { background:#696cff; }
    .sb-awaiting_payment { background:#fff7ed;color:#7c2d12;border:1.5px solid #fed7aa; } .sb-awaiting_payment .dot { background:#f97316; }
    .sb-processing { background:#f5f3ff;color:#4c1d95;border:1.5px solid #ddd6fe; } .sb-processing .dot { background:#8b5cf6; }
    .sb-completed  { background:#f0fdf4;color:#065f46;border:1.5px solid #6ee7b7; } .sb-completed .dot  { background:#10b981; }
    .sb-rejected   { background:#fef2f2;color:#7f1d1d;border:1.5px solid #fca5a5; } .sb-rejected .dot   { background:#ef4444; }

    /* === Filter Tabs === */
    .filter-tabs { display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.25rem; }
    .filter-tab { background:#f3f4f6;color:#6b7280;border:none;border-radius:20px;padding:.35rem .85rem;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .2s; }
    .filter-tab:hover { background:#ede9fe;color:#7c3aed; }
    .filter-tab.active { background:#696cff;color:#fff; }
    .filter-tab .count { background:rgba(255,255,255,.25);border-radius:20px;padding:.05rem .5rem;margin-left:.3rem;font-size:11px; }

    /* === Table === */
    .q-table { font-size: 13.5px; }
    .q-table th { font-size: 11px; text-transform: uppercase; letter-spacing: .7px; color: #9ca3af; font-weight: 700; padding: .75rem 1rem; }
    .q-table td { padding: .8rem 1rem; vertical-align: middle; }
    .q-table tr { cursor: pointer; transition: background .15s; }
    .q-table tr:hover td { background: #f5f3ff !important; }
    .inquiry-id-cell { font-family: monospace; font-weight: 700; color: #4f46e5; font-size: 13px; }

    /* === Detail Modal === */
    .modal-xxl { max-width: 1100px; }
    .detail-section { border-bottom: 1px solid #f0f0f0; padding-bottom: 1.25rem; margin-bottom: 1.25rem; }
    .detail-section:last-child { border-bottom: none; }
    .ds-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #9ca3af; margin-bottom: .75rem; }
    .info-pair { margin-bottom: .5rem; font-size: 13.5px; }
    .info-pair .lbl { color: #9ca3af; font-weight: 600; font-size: 12px; }
    .fmt-tag { display:inline-block;background:#ede9fe;color:#6d28d9;border-radius:20px;padding:.15rem .6rem;font-size:11.5px;font-weight:600;margin:.1rem .15rem .1rem 0; }

    /* Cesium map container */
    #adminCesiumMap { width: 100%; height: 350px; border-radius: 10px; overflow: hidden; background: #1a1a2e; }
    #adminCesiumMap:fullscreen { width: 100% !important; height: 100% !important; border-radius: 0; }
    #adminCesiumMap .cesium-viewer-bottom, 
    #adminCesiumMap .cesium-viewer-cesiumionContainer, 
    #adminCesiumMap .cesium-credit-logoContainer, 
    #adminCesiumMap .cesium-credit-textContainer, 
    #adminCesiumMap .cesium-credit-expand-link, 
    #adminCesiumMap .cesium-credit-imageContainer { display: none !important; }

    /* Form sections in modal */
    .admin-form-section { background: #f8fafc; border-radius: 10px; padding: 1rem 1.1rem; margin-top: 1rem; }
    .admin-form-section h6 { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #6b7280; margin-bottom: .85rem; }

    /* Status step indicator inside modal */
    .modal-status-steps { display:flex;gap:0;overflow-x:auto;padding-bottom:.5rem;margin-bottom:1rem; }
    .mss-step { flex:1;min-width:70px;display:flex;flex-direction:column;align-items:center;position:relative; }
    .mss-step .mss-dot { width:26px;height:26px;border-radius:50%;background:#e5e7eb;color:#aaa;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;z-index:1; }
    .mss-step .mss-lbl { font-size:9.5px;font-weight:600;color:#aaa;margin-top:5px;text-align:center; }
    .mss-step::before { content:'';position:absolute;top:13px;left:calc(-50% + 13px);right:calc(50% + 13px);height:2px;background:#e5e7eb; }
    .mss-step:first-child::before { display:none; }
    .mss-step.mss-done .mss-dot { background:#10b981;color:#fff; }
    .mss-step.mss-done::before { background:#10b981; }
    .mss-step.mss-done .mss-lbl { color:#10b981; }
    .mss-step.mss-active .mss-dot { background:#696cff;color:#fff;box-shadow:0 0 0 4px rgba(105,108,255,.18); }
    .mss-step.mss-active .mss-lbl { color:#696cff;font-weight:700; }
    .mss-step.mss-rejected .mss-dot { background:#ef4444;color:#fff; }
    .mss-step.mss-rejected .mss-lbl { color:#ef4444; }

    /* Action buttons */
    .btn-save-send { background: linear-gradient(135deg,#059669,#0d9488); color: #fff; border: none; }
    .btn-save-send:hover { background: linear-gradient(135deg,#047857,#0f766e); color: #fff; }
    .btn-update-status { background: linear-gradient(135deg,#696cff,#9155fd); color: #fff; border: none; }
    .btn-update-status:hover { background: linear-gradient(135deg,#4338ca,#7c3aed); color: #fff; }

    /* Conditional form sections */
    .cond-section { display: none; }
    .cond-section.visible { display: block; }

    /* === Delivery Section === */
    .delivery-path-box {
      background: #0f172a; color: #7dd3fc; border-radius: 8px;
      padding: .65rem 1rem; font-family: monospace; font-size: 12.5px;
      word-break: break-all; margin-bottom: .75rem;
      border: 1px solid #1e3a5f;
    }
    .delivery-file-list { list-style: none; padding: 0; margin: 0; }
    .delivery-file-list li {
      display: flex; justify-content: space-between; align-items: center;
      padding: .35rem .5rem; border-bottom: 1px solid #f0f0f0; font-size: 12.5px;
    }
    .delivery-file-list li:last-child { border-bottom: none; }
    .delivery-file-list .fname { font-family: monospace; color: #374151; }
    .delivery-file-list .fsize { color: #9ca3af; font-size: 11.5px; }
    .delivery-ready-badge-on  { background:#f0fdf4;color:#065f46;border:1.5px solid #6ee7b7;border-radius:20px;padding:.2rem .8rem;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:.35rem; }
    .delivery-ready-badge-off { background:#f8fafc;color:#9ca3af;border:1.5px solid #e5e7eb;border-radius:20px;padding:.2rem .8rem;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:.35rem; }
  </style>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Premium Glass Top Nav -->
    <nav class="admin-glass-nav">
      <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
        <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height:56px;width:auto;max-height:56px;object-fit:contain;display:block;" /></span>
        <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size:1.1em;">3DHub Admin</span>
      </a>

      <div class="admin-nav-links d-none d-xl-flex">
        <a href="{{ route('admin_dashboard') }}" class="admin-nav-link">Dashboard</a>
        <a href="{{ route('admin.add_3d_model') }}" class="admin-nav-link">Add 3D Model</a>
        <a href="{{ route('admin.manage_map_pins') }}" class="admin-nav-link">Manage Map Pins</a>
        <a href="{{ route('admin.manage_showcases') }}" class="admin-nav-link">Manage Showcase</a>
        <a href="{{ route('admin.client_uploads') }}" class="admin-nav-link">Client Uploads</a>
        <a href="{{ route('admin.inquiries') }}" class="admin-nav-link active">Inquiries</a>
        <a href="{{ route('admin.manage_users') }}" class="admin-nav-link">Manage Users</a>
        <a href="{{ route('landing') }}" class="admin-nav-link" target="_blank">View Portal</a>
      </div>

      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="nav-item dropdown-style-switcher dropdown me-2">
          <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light"><span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span></button></li>
            <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"><span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span></button></li>
            <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"><span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span></button></li>
          </ul>
        </div>
        @auth
        <div class="d-none d-md-flex align-items-center gap-3 border-start ps-3 ms-2">
          <a href="{{ route('profile') }}" class="small text-muted fw-medium text-decoration-none email-hover-link">{{ Auth::user()->email }}</a>
          <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm" class="d-inline">
            @csrf
            <button type="button" id="adminLogoutBtn" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold">Log out</button>
          </form>
        </div>
        @endauth
        <button class="admin-menu-toggle btn btn-icon d-xl-none border-0 bg-transparent p-0" type="button"><i class="bx bx-menu icon-lg"></i></button>
      </div>
    </nav>

    <div class="layout-page">
      <div class="content-wrapper content-wrapper-premium">
        <div class="container-xxl flex-grow-1 container-p-y">

          <!-- Page Header -->
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
              <h4 class="fw-bold mb-1">Manage Inquiries</h4>
              <p class="text-muted mb-0 small">Review client requests, preview their selected area on the 3D map, and send formal quotations with pricing</p>
            </div>
            <a href="{{ route('admin_dashboard') }}" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
          </div>

          <!-- Global Alert -->
          <div id="pageAlert" class="alert d-none mb-4" role="alert"></div>

          <!-- Stats Row -->
          <div class="row mb-4" id="statsRow">
            <!-- filled dynamically -->
          </div>

          <!-- Filter Tabs + Search -->
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div class="filter-tabs" id="filterTabs">
              <button class="filter-tab active" data-status="all">All</button>
              <button class="filter-tab" data-status="pending">⏳ Pending</button>
              <button class="filter-tab" data-status="reviewed">🔍 Reviewed</button>
              <!-- <button class="filter-tab" data-status="quoted">💼 Quoted</button> -->
              <!-- <button class="filter-tab" data-status="awaiting_payment">🏦 Awaiting Payment</button> -->
              <button class="filter-tab" data-status="processing">⚙️ Processing</button>
              <button class="filter-tab" data-status="completed">✅ Completed</button>
              <button class="filter-tab" data-status="rejected">❌ Rejected</button>
            </div>
            <div>
              <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Search ID or email…" style="min-width:220px;">
            </div>
          </div>

          <!-- Table Card -->
          <div class="card">
            <div class="table-responsive">
              <table class="table q-table mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Inquiry ID</th>
                    <th>Client</th>
                    <th>3D Model</th>
                    <th>Output Formats</th>
                    <th>Date</th>
                    <th>Quotation PDF</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="quotationsTableBody">
                  <tr><td colspan="8" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================
     DETAIL / EDIT MODAL
================================================================ -->
<div class="modal fade" id="quotationDetailModal" tabindex="-1" aria-labelledby="quotationDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xxl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="quotationDetailModalLabel">
          <i class="bx bx-receipt me-2 text-primary"></i>
          <span id="modalInquiryId">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <div class="row g-0" style="min-height:500px;">

          <!-- LEFT: Info + Map -->
          <div class="col-lg-5 p-4 border-end">

            <div class="detail-section">
              <div class="ds-title">📋 Inquiry Info</div>
              <div class="info-pair"><span class="lbl">Inquiry ID</span><br><strong id="dInquiryId" style="font-family:monospace;color:#4f46e5;"></strong></div>
              <div class="info-pair"><span class="lbl">Client Email</span><br><strong id="dUserEmail"></strong></div>
              <div class="info-pair"><span class="lbl">Client Name</span><br><span id="dUserName"></span></div>
              <div class="info-pair"><span class="lbl">Date Submitted</span><br><span id="dCreatedAt"></span></div>
              <div class="info-pair"><span class="lbl">Last Updated</span><br><span id="dUpdatedAt"></span></div>
            </div>

            <div class="detail-section">
              <div class="ds-title">📍 3D Model & Area</div>
              <div class="info-pair"><span class="lbl">Model Title</span><br><strong id="dMapTitle"></strong></div>
              <div class="info-pair"><span class="lbl">Output Formats Requested</span><br><div id="dOutputCategories"></div></div>
              <div class="info-pair d-flex justify-content-between align-items-center mb-0">
                <div>
                  <span class="lbl">Calculated Area</span><br>
                  <strong id="dCalculatedArea">—</strong>
                </div>
                <a id="btnDownloadKml" href="#" class="btn btn-sm btn-outline-secondary py-1 px-2 mt-1" style="font-size:11px;">
                  <i class="bx bx-download me-1"></i> Download KML
                </a>
              </div>
            </div>

            <!-- Cesium Map -->
            <div class="detail-section">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="ds-title mb-0">🗺️ Selected Area Preview</div>
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:11px;" id="btnToggleMapFullscreen" onclick="toggleMapFullscreen()">
                  <i class="bx bx-fullscreen me-1"></i> Full Screen
                </button>
              </div>
              <div id="adminCesiumMap"></div>
              <p class="text-muted small mt-2 mb-0"><i class="bx bx-info-circle me-1"></i>The highlighted polygon shows exactly the area the client has selected for data extraction.</p>
            </div>

            <!-- Status Timeline -->
            <div class="detail-section">
              <div class="ds-title">📊 Status Timeline</div>
              <div class="modal-status-steps" id="modalStatusSteps"></div>
            </div>

          </div>

          <!-- RIGHT: Admin Actions -->
          <div class="col-lg-7 p-4">

            <div class="detail-section">
              <div class="ds-title">⚙️ Update Inquiry Status</div>

              <div id="modalAlert" class="alert d-none mb-3" role="alert"></div>

              <div class="mb-3">
                <label for="statusSelect" class="form-label fw-semibold small">Status</label>
                <select id="statusSelect" class="form-select">
                  <option value="pending">⏳ Pending Review</option>
                  <option value="reviewed">🔍 Under Review</option>
                  <!-- <option value="quoted">💼 Quotation Sent</option> -->
                  <!-- <option value="awaiting_payment">🏦 Awaiting Payment</option> -->
                  <option value="processing">⚙️ Processing</option>
                  <option value="completed">✅ Completed</option>
                  <option value="rejected">❌ Rejected</option>
                </select>
                <div class="form-text" id="statusHelpText"></div>
              </div>

              <!-- PDF Upload section (shown when status = quoted) -->
              <div class="admin-form-section cond-section" id="sectionQuotationPdf">
                <h6>📄 Upload Quotation PDF <span class="text-danger">*</span></h6>
                <div class="mb-3">
                  <div class="border border-dashed p-3 text-center rounded bg-light position-relative" id="pdfUploadZone" style="border-style: dashed !important; border-width: 2px !important; cursor: pointer;">
                    <input type="file" id="quotationPdfInput" accept=".pdf" class="position-absolute w-100 h-100 top-0 start-0 opacity-0" style="cursor: pointer;">
                    <div id="pdfUploadPrompt">
                      <i class="bx bx-cloud-upload display-6 text-primary mb-2"></i>
                      <p class="mb-1 fw-semibold">Click to upload or drag & drop</p>
                      <p class="text-muted small mb-0">Only PDF files (max 20MB)</p>
                    </div>
                    <div id="pdfSelectedInfo" class="d-none">
                      <i class="bx bxs-file-pdf display-6 text-danger mb-2"></i>
                      <p id="pdfFileName" class="mb-1 fw-semibold text-truncate px-3"></p>
                      <p id="pdfFileSize" class="text-muted small mb-2"></p>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSelectedPdf(event)">Remove</button>
                    </div>
                  </div>
                  <div class="mt-2 text-danger small d-none" id="pdfUploadError"></div>
                </div>

                <!-- Existing Quotation PDF if any -->
                <div id="existingPdfContainer" class="d-none mt-2">
                  <span class="small fw-semibold text-muted">Current Quotation PDF:</span>
                  <div class="d-flex align-items-center gap-2 mt-1">
                    <a id="btnDownloadQuotationPdf" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                      <i class="bx bx-download me-1"></i> Download Quotation PDF
                    </a>
                  </div>
                </div>
              </div>

              <!-- Payment Receipt section (shown when receipt exists) -->
              <div class="admin-form-section d-none" id="sectionPaymentReceipt">
                <h6>🧾 Client Payment Receipt</h6>
                <div class="alert alert-info py-2 px-3 mb-0 small d-flex align-items-center justify-content-between">
                  <span><i class="bx bx-check-circle me-1"></i> A payment receipt has been uploaded by the client.</span>
                  <a id="btnDownloadPaymentReceipt" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-download me-1"></i> View Receipt
                  </a>
                </div>
              </div>

              <!-- Delivery Section (shown when status = completed) -->
              <div class="admin-form-section cond-section" id="sectionDelivery">
                <h6>📦 3D Model Tiles Delivery</h6>

                <!-- Delivery status indicator -->
                <div class="mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <div class="small fw-semibold text-muted mb-1">Delivery Status</div>
                    <div id="deliveryStatusBadge" class="delivery-ready-badge-off"><span>⏳</span> Not Ready</div>
                  </div>
                  <button type="button" id="btnToggleDelivery" class="btn btn-sm btn-outline-success px-3" onclick="toggleDeliveryReady()">
                    <i class="bx bx-check-circle me-1"></i> Mark as Ready
                  </button>
                </div>

                <!-- WinSCP upload path -->
                <div class="small fw-semibold text-muted mb-1 mt-2">📂 WinSCP Upload Path</div>
                <div class="mb-3">
                  <div class="card p-3 border shadow-none mb-2" style="background: rgba(105, 108, 255, 0.04); border-color: rgba(105, 108, 255, 0.15);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <span class="badge bg-label-primary px-2 py-1" style="font-size: 10px;">SFTPGo / Admin (Port 2222)</span>
                      <button type="button" class="btn btn-xs btn-primary py-0 px-2" style="font-size: 11px;" onclick="launchWinSftp('host')">
                        <i class="bx bx-link-external me-1"></i> Launch WinSCP
                      </button>
                    </div>
                    <div class="small text-muted mb-2">
                      Host: <strong><span class="text-dark" id="sftpHostHost">—</span></strong> · Port: <strong><span class="text-dark" id="sftpPortHost">—</span></strong> · User: <strong><span class="text-dark" id="sftpUserHost">—</span></strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <div class="delivery-path-box mb-0 flex-grow-1" id="deliverySftpPathHost" style="background:#f1f5f9; color:#1e293b; border-color:#cbd5e1; font-size:12px; padding:0.5rem 0.75rem;">—</div>
                      <button type="button" class="btn btn-xs btn-outline-secondary px-2 flex-shrink-0" onclick="copyPathToClipboard('deliverySftpPathHost', this)" title="Copy Path">
                        <i class="bx bx-copy"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <p class="text-muted mb-2" style="font-size:11.5px;">
                  <i class="bx bx-info-circle me-1"></i>
                  Connect to the SFTP server with WinSCP and upload the 3D model tiles into the path above.
                  Then click <strong>Check Files on SFTPGo</strong> to verify, and <strong>Mark as Ready</strong> to notify the client.
                </p>

                <!-- Check Files button + result -->
                <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="checkDeliveryFiles()">
                  <i class="bx bx-search me-1"></i> Check Files on SFTPGo
                </button>
                <div id="deliveryFileResult" class="d-none mt-2"></div>
              </div>

              <!-- Rejection reason (shown when status = rejected) -->
              <div class="admin-form-section cond-section" id="sectionRejection">
                <h6>❌ Rejection Reason</h6>
                <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Explain why this inquiry is being rejected…"></textarea>
              </div>

              <!-- Admin Notes (always visible) -->
              <div class="admin-form-section mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0">📝 Admin Notes <small class="text-muted fw-normal">(shown to client)</small></h6>
                  <button type="button" id="btnEditAdminNotes" class="btn btn-xs btn-outline-primary py-1 px-2 d-none" onclick="unfreezeAdminNotes()" style="font-size: 11px;">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                  </button>
                </div>
                <textarea id="adminNotes" class="form-control" rows="3" placeholder="Any additional notes or instructions for the client…"></textarea>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2 mt-4 flex-wrap">
                <button id="btnSaveAndSend" class="btn btn-save-send px-4 d-none">
                  <i class="bx bx-send me-1"></i> Save & Send Quotation Email to Client
                </button>
                <button id="btnUpdateStatus" class="btn btn-update-status px-4">
                  <i class="bx bx-save me-1"></i> Update Status
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="btnDeleteQuotation" type="button" class="btn btn-danger px-4" onclick="confirmDeleteQuotation()">
                  <i class="bx bx-trash me-1"></i> Delete Request
                </button>
              </div>

              <!-- Existing data section -->
              <div id="existingDataSection" class="mt-4 d-none">
                <hr>
                <div class="ds-title mb-2">📌 Current Inquiry Data</div>
                <div id="existingDataContent" class="small text-muted"></div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Are you sure you want to log out?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="logoutConfirmBtn">Log out</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('assets') }}/js/admin-responsive.js"></script>
<script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
<script>
(function () {
  'use strict';

  var CSRF  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  var API   = window.location.origin;

  // ─── State ───────────────────────────────────────────────────────────────
  var allInquiries = [];
  var currentFilter = 'all';
  var currentSearch = '';
  var currentQuotation = null;
  var cesiumViewer   = null;
  var detailModal    = null;

  var STATUS_ORDER = ['pending','reviewed','processing','completed'];
  var STATUS_LABELS = {
    pending:          'Pending Review',
    reviewed:         'Under Review',
    quoted:           'Quotation Sent',
    awaiting_payment: 'Awaiting Payment',
    processing:       'Processing',
    completed:        'Completed',
    rejected:         'Rejected',
  };
  var STATUS_ICONS = {
    pending:'⏳', reviewed:'🔍', quoted:'💼',
    awaiting_payment:'🏦', processing:'⚙️', completed:'✅', rejected:'❌'
  };

  // ─── Init ─────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    detailModal = new bootstrap.Modal(document.getElementById('quotationDetailModal'));
    loadQuotations();
    bindFilterTabs();
    bindSearch();
    bindStatusSelect();
    bindSaveButtons();

    // Logout
    var logoutBtn = document.getElementById('adminLogoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () {
        new bootstrap.Modal(document.getElementById('logoutConfirmModal')).show();
        document.getElementById('logoutConfirmBtn').onclick = function () {
          document.getElementById('adminLogoutForm').submit();
        };
      });
    }

    // Reset Cesium when modal closes
    document.getElementById('quotationDetailModal').addEventListener('hidden.bs.modal', function () {
      destroyCesium();
    });
  });

  // ─── Load Inquiries ─────────────────────────────────────────────────────
  function loadQuotations() {
    fetch(API + '/api/admin/inquiries', { credentials: 'include' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        allInquiries = data;
        renderStats(data);
        renderTable(data);
      })
      .catch(function (err) {
        document.getElementById('quotationsTableBody').innerHTML =
          '<tr><td colspan="8" class="text-danger text-center py-4">Failed to load inquiries. ' + err.message + '</td></tr>';
      });
  }

  // ─── Stats ───────────────────────────────────────────────────────────────
  function renderStats(data) {
    var counts = { all: data.length };
    STATUS_ORDER.concat(['rejected']).forEach(function (s) {
      counts[s] = data.filter(function (q) { return q.status === s; }).length;
    });

    var statsEl = document.getElementById('statsRow');
    var items = [
      { label: 'Total',          key: 'all',              icon: 'bx-file', color: 'primary' },
      { label: 'Pending',        key: 'pending',          icon: 'bx-time-five', color: 'warning' },
      { label: 'Processing',     key: 'processing',       icon: 'bx-loader-alt', color: 'purple' },
      { label: 'Completed',      key: 'completed',        icon: 'bx-check-circle', color: 'success' },
    ];
    statsEl.innerHTML = items.map(function (item) {
      return '<div class="col-6 col-sm-4 col-lg-3 mb-2">' +
        '<div class="card text-center p-3 h-100" style="border-radius:12px;">' +
          '<div class="fs-4 fw-bold text-' + item.color + '">' + (counts[item.key] || 0) + '</div>' +
          '<div class="small text-muted fw-semibold">' + item.label + '</div>' +
        '</div></div>';
    }).join('');

    // Update filter tab counts
    document.querySelectorAll('.filter-tab').forEach(function (tab) {
      var s = tab.getAttribute('data-status');
      var c = counts[s] || 0;
      var existing = tab.querySelector('.count');
      if (existing) existing.remove();
      if (c > 0) {
        var span = document.createElement('span');
        span.className = 'count';
        span.textContent = c;
        tab.appendChild(span);
      }
    });
  }

  // ─── Filter Tabs ─────────────────────────────────────────────────────────
  function bindFilterTabs() {
    document.querySelectorAll('.filter-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.filter-tab').forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        currentFilter = tab.getAttribute('data-status');
        applyFilters();
      });
    });
  }

  function bindSearch() {
    var inp = document.getElementById('searchInput');
    var timer;
    inp.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        currentSearch = inp.value.trim().toLowerCase();
        applyFilters();
      }, 300);
    });
  }

  function applyFilters() {
    var filtered = allInquiries.filter(function (q) {
      var matchStatus = currentFilter === 'all' || q.status === currentFilter;
      var matchSearch = !currentSearch ||
        q.inquiry_id.toLowerCase().includes(currentSearch) ||
        q.user_email.toLowerCase().includes(currentSearch) ||
        (q.user_name || '').toLowerCase().includes(currentSearch);
      return matchStatus && matchSearch;
    });
    renderTable(filtered);
  }

  // ─── Table ────────────────────────────────────────────────────────────────
  function renderTable(data) {
    var tbody = document.getElementById('quotationsTableBody');
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="bx bx-inbox display-6 d-block mb-2"></i>No inquiries found</td></tr>';
      return;
    }
    tbody.innerHTML = data.map(function (q) {
      var fmts = (q.output_categories || []).map(function (c) {
        return '<span class="fmt-tag">' + esc(c) + '</span>';
      }).join('');
      var pdfHtml = q.quotation_pdf_url 
        ? '<a href="' + q.quotation_pdf_url + '" target="_blank" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 11px;" onclick="event.stopPropagation();"><i class="bx bxs-file-pdf me-1"></i>PDF</a>'
        : '<span class="text-muted">—</span>';
      var statusBadge = '<span class="sb sb-' + esc(q.status) + '"><span class="dot"></span>' + esc(q.status_label) + '</span>';
      if (q.payment_receipt_path) {
        statusBadge += '<span class="badge bg-label-success ms-1" style="font-size:10px;" title="Payment receipt uploaded"><i class="bx bx-receipt"></i> Paid</span>';
      }
      return '<tr data-id="' + q.id + '">' +
        '<td class="inquiry-id-cell">' + esc(q.inquiry_id) + '</td>' +
        '<td><div class="fw-semibold" style="font-size:13px;">' + esc(q.user_email) + '</div><div class="text-muted" style="font-size:11.5px;">' + esc(q.user_name) + '</div></td>' +
        '<td>' + esc(q.map_title) + '</td>' +
        '<td>' + (fmts || '<span class="text-muted">—</span>') + '</td>' +
        '<td style="white-space:nowrap;font-size:12px;">' + esc(q.created_at) + '</td>' +
        '<td><strong>' + pdfHtml + '</strong></td>' +
        '<td>' + statusBadge + '</td>' +
        '<td><button class="btn btn-sm btn-outline-primary" onclick="openDetail(' + q.id + ',event)"><i class="bx bx-edit me-1"></i>Manage</button></td>' +
      '</tr>';
    }).join('');

    tbody.querySelectorAll('tr[data-id]').forEach(function (row) {
      row.addEventListener('click', function (e) {
        if (e.target.closest('button')) return;
        openDetail(parseInt(row.getAttribute('data-id')), e);
      });
    });
  }

  // ─── Open Detail Modal ────────────────────────────────────────────────────
  window.openDetail = function (id, e) {
    if (e) e.stopPropagation();
    var q = allInquiries.find(function (x) { return x.id === id; });
    if (!q) return;
    currentQuotation = q;
    populateModal(q);
    detailModal.show();
    setTimeout(function () { initCesium(q); }, 400);
  };

  window.toggleMapFullscreen = function () {
    var mapContainer = document.getElementById('adminCesiumMap');
    if (!mapContainer) return;
    if (!document.fullscreenElement) {
      mapContainer.requestFullscreen().catch(function (err) {
        console.error("Fullscreen error:", err);
      });
    } else {
      document.exitFullscreen();
    }
  };

  document.addEventListener('fullscreenchange', function () {
    var btn = document.getElementById('btnToggleMapFullscreen');
    if (btn) {
      if (document.fullscreenElement) {
        btn.innerHTML = '<i class="bx bx-exit-fullscreen me-1"></i> Exit Full Screen';
      } else {
        btn.innerHTML = '<i class="bx bx-fullscreen me-1"></i> Full Screen';
      }
    }
    if (cesiumViewer && !cesiumViewer.isDestroyed()) {
      cesiumViewer.resize();
    }
  });

  function populateModal(q) {
    // Header
    document.getElementById('modalInquiryId').textContent = q.inquiry_id;
    // Left panel
    document.getElementById('dInquiryId').textContent  = q.inquiry_id;
    document.getElementById('dUserEmail').textContent   = q.user_email;
    document.getElementById('dUserName').textContent    = q.user_name;
    document.getElementById('dCreatedAt').textContent   = q.created_at;
    document.getElementById('dUpdatedAt').textContent   = q.updated_at;
    document.getElementById('dMapTitle').textContent    = q.map_title;
    var fmts = (q.output_categories || []).map(function (c) {
      return '<span class="fmt-tag">' + esc(c) + '</span>';
    }).join('');
    document.getElementById('dOutputCategories').innerHTML = fmts || '<span class="text-muted">—</span>';

    // Calculate area and estimated price
    var coords = q.area_coordinates;
    var points = [];
    if (coords && coords.type === "Polygon" && coords.coordinates && coords.coordinates[0]) {
      var points = coords.coordinates[0];
      var positions = points.map(function (pt) {
        return Cesium.Cartesian3.fromDegrees(pt[0], pt[1]);
      });
      points = coords.coordinates[0];
    } else if (Array.isArray(coords) && coords.length >= 3) {
      points = coords.map(function (c) {
        var lng = c.longitude !== undefined ? c.longitude : c.lng || c[0];
        var lat = c.latitude  !== undefined ? c.latitude  : c.lat || c[1];
        return [lng, lat];
      });
    }

    if (points.length >= 3) {
      var areaM2 = calculatePolygonArea(points);
      document.getElementById('dCalculatedArea').textContent = areaM2.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m²';
    } else {
      document.getElementById('dCalculatedArea').textContent = '—';
    }

    // Status timeline
    renderModalTimeline(q.status);

    // Right panel — pre-fill form
    document.getElementById('statusSelect').value     = q.status;
    document.getElementById('adminNotes').value       = (q.admin_notes && q.admin_notes[q.status]) || '';
    document.getElementById('rejectionReason').value  = q.rejection_reason || '';
    resetPdfUploadState();

    // Check existing PDF
    var existingPdfContainer = document.getElementById('existingPdfContainer');
    var btnDownloadQuotationPdf = document.getElementById('btnDownloadQuotationPdf');
    if (q.quotation_pdf_url) {
      existingPdfContainer.classList.remove('d-none');
      btnDownloadQuotationPdf.href = q.quotation_pdf_url;
    } else {
      existingPdfContainer.classList.add('d-none');
      btnDownloadQuotationPdf.href = '#';
    }

    // Check payment receipt
    var sectionPaymentReceipt = document.getElementById('sectionPaymentReceipt');
    var btnDownloadPaymentReceipt = document.getElementById('btnDownloadPaymentReceipt');
    if (q.payment_receipt_path) {
      sectionPaymentReceipt.classList.remove('d-none');
      btnDownloadPaymentReceipt.href = q.payment_receipt_url;
    } else {
      sectionPaymentReceipt.classList.add('d-none');
      btnDownloadPaymentReceipt.href = '#';
    }

    updateConditionalSections(q.status);
    showExistingData(q);
    freezeOrUnfreezeAdminNotes();
    clearModalAlert();

    // Reset Delete Request button state from loading
    var deleteBtn = document.getElementById('btnDeleteQuotation');
    if (deleteBtn) {
      deleteBtn.disabled = false;
      deleteBtn.innerHTML = '<i class="bx bx-trash me-1"></i> Delete Request';
    }

    // Dynamic KML download link binding
    var kmlBtn = document.getElementById('btnDownloadKml');
    if (kmlBtn) {
      kmlBtn.href = '/api/admin/inquiries/' + q.id + '/download-kml';
    }

    if (q.status === 'completed') {
      updateDeliverySection(q);
    }
  }

  function renderModalTimeline(status) {
    var isRejected = status === 'rejected';
    var currentIdx = isRejected ? -1 : STATUS_ORDER.indexOf(status);
    var container  = document.getElementById('modalStatusSteps');
    container.innerHTML = STATUS_ORDER.map(function (step, i) {
      var cls;
      if (isRejected) {
        cls = '';
      } else if (status === 'completed') {
        cls = 'mss-done';
      } else if (i < currentIdx) {
        cls = 'mss-done';
      } else if (i === currentIdx) {
        cls = 'mss-active';
      } else {
        cls = '';
      }
      var icon = (status === 'completed' || i < currentIdx) ? '✓' : (i + 1);
      return '<div class="mss-step ' + cls + '">' +
        '<div class="mss-dot">' + icon + '</div>' +
        '<div class="mss-lbl">' + STATUS_ICONS[step] + ' ' + STATUS_LABELS[step].split(' ')[0] + '</div>' +
      '</div>';
    }).join('');
    if (isRejected) {
      container.innerHTML += '<div class="mss-step mss-rejected" style="margin-left:.5rem;">' +
        '<div class="mss-dot">✕</div><div class="mss-lbl">❌ Rejected</div></div>';
    }
  }

  function showExistingData(q) {
    var sec = document.getElementById('existingDataSection');
    var con = document.getElementById('existingDataContent');
    if (q.quotation_pdf_path || q.payment_receipt_path || q.rejection_reason || q.quoted_at || q.processing_started_at) {
      sec.classList.remove('d-none');
      var lines = [];
      if (q.quotation_pdf_path)   lines.push('<strong>Quoted PDF:</strong> Uploaded');
      if (q.payment_receipt_path) lines.push('<strong>Payment Receipt:</strong> Uploaded');
      if (q.quoted_at)            lines.push('<strong>Quoted At:</strong> ' + esc(q.quoted_at));
      if (q.processing_started_at)lines.push('<strong>Processing Started:</strong> ' + esc(q.processing_started_at));
      if (q.rejection_reason)     lines.push('<strong>Rejection Reason:</strong> ' + esc(q.rejection_reason));
      con.innerHTML = lines.join('<br>');
    } else {
      sec.classList.add('d-none');
    }
  }

  // ─── Status Select Logic ──────────────────────────────────────────────────
  function bindStatusSelect() {
    document.getElementById('statusSelect').addEventListener('change', function () {
      updateConditionalSections(this.value);
      var notesMap = (currentQuotation && currentQuotation.admin_notes) || {};
      document.getElementById('adminNotes').value = notesMap[this.value] || '';
      freezeOrUnfreezeAdminNotes();
    });
  }

  function updateConditionalSections(status) {
    var helpTexts = {
      pending:          'Mark as received and not yet reviewed.',
      reviewed:         'You have reviewed this request and are calculating the price.',
      quoted:           'Enter the price and bank details, then click "Save & Send" to email the client.',
      awaiting_payment: 'Client has been notified; waiting for bank transfer confirmation.',
      processing:       'Payment received and verified. Actively processing the 3D model for delivery.',
      completed:        '3D model has been delivered. Upload tiles via WinSCP then mark delivery as ready for client download.',
      rejected:         'Enter a reason for rejection so the client understands.',
    };
    document.getElementById('statusHelpText').textContent = helpTexts[status] || '';

    document.getElementById('sectionQuotationPdf').classList.toggle('visible', status === 'quoted');
    document.getElementById('sectionRejection').classList.toggle('visible', status === 'rejected');
    document.getElementById('sectionDelivery').classList.toggle('visible', status === 'completed');

    if (status === 'completed' && currentQuotation) {
      updateDeliverySection(currentQuotation);
    }

    document.getElementById('btnSaveAndSend').classList.toggle('d-none', status !== 'quoted');
    document.getElementById('btnUpdateStatus').textContent =
      status === 'quoted' ? 'Save Without Sending Email' : 'Update Status';
  }

  // ─── Save Buttons ─────────────────────────────────────────────────────────
  function bindSaveButtons() {
    document.getElementById('btnSaveAndSend').addEventListener('click', function () {
      submitUpdate(true);
    });
    document.getElementById('btnUpdateStatus').addEventListener('click', function () {
      submitUpdate(false);
    });
  }

  function submitUpdate(sendEmail) {
    if (!currentQuotation) return;
    var status = document.getElementById('statusSelect').value;

    if (status === 'quoted') {
      var hasExisting = !!currentQuotation.quotation_pdf_path;
      var hasNew = document.getElementById('quotationPdfInput').files.length > 0;
      if (!hasExisting && !hasNew) {
        showModalAlert('Please upload a quotation PDF file.', false);
        return;
      }
    }

    var formData = new FormData();
    formData.append('status', status);
    formData.append('admin_notes', document.getElementById('adminNotes').value.trim());
    formData.append('rejection_reason', document.getElementById('rejectionReason').value.trim());
    formData.append('send_email', sendEmail ? '1' : '0');
    formData.append('_token', CSRF);

    var fileInput = document.getElementById('quotationPdfInput');
    if (fileInput && fileInput.files.length > 0) {
      formData.append('quotation_pdf', fileInput.files[0]);
    }

    setButtonLoading(true);
    clearModalAlert();

    fetch(API + '/api/admin/inquiries/' + currentQuotation.id + '/status', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF },
      credentials: 'include',
      body: formData,
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        showModalAlert(data.message, true);
        var idx = allInquiries.findIndex(function (q) { return q.id === currentQuotation.id; });
        if (idx !== -1) { allInquiries[idx] = data.data; currentQuotation = data.data; }
        renderStats(allInquiries);
        applyFilters();
        populateModal(data.data);
        renderModalTimeline(data.data.status);
      } else {
        showModalAlert(data.message || 'Update failed.', false);
      }
    })
    .catch(function (err) {
      showModalAlert('Network error: ' + err.message, false);
    })
    .finally(function () { setButtonLoading(false); });
  }

  function setButtonLoading(loading) {
    var btn1 = document.getElementById('btnSaveAndSend');
    var btn2 = document.getElementById('btnUpdateStatus');
    [btn1, btn2].forEach(function (b) { b.disabled = loading; });
    if (loading) {
      btn2.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    } else {
      var s = document.getElementById('statusSelect').value;
      btn2.innerHTML = '<i class="bx bx-save me-1"></i>' +
        (s === 'quoted' ? 'Save Without Sending Email' : 'Update Status');
    }
  }

  // ─── Alerts ───────────────────────────────────────────────────────────────
  function showModalAlert(msg, success) {
    var el = document.getElementById('modalAlert');
    el.textContent = msg;
    el.className = 'alert mb-3 ' + (success ? 'alert-success' : 'alert-danger');
    el.classList.remove('d-none');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  function clearModalAlert() {
    var el = document.getElementById('modalAlert');
    el.className = 'alert d-none mb-3';
    el.textContent = '';
  }
  function showPageAlert(msg, success) {
    var el = document.getElementById('pageAlert');
    el.textContent = msg;
    el.className = 'alert mb-4 ' + (success ? 'alert-success' : 'alert-danger');
    el.classList.remove('d-none');
    setTimeout(function () { el.classList.add('d-none'); }, 5000);
  }

  // ─── Cesium Map Preview ───────────────────────────────────────────────────
  function initCesium(q) {
    destroyCesium();
    var container = document.getElementById('adminCesiumMap');
    if (!container) return;

    if (typeof Cesium.Ion !== 'undefined') {
      Cesium.Ion.defaultAccessToken = '';
    }

    try {
      cesiumViewer = new Cesium.Viewer('adminCesiumMap', {
        timeline: false, animation: false, baseLayerPicker: false,
        fullscreenButton: false, homeButton: false, sceneModePicker: false,
        navigationHelpButton: false, geocoder: false, vrButton: false,
        infoBox: false, selectionIndicator: false,
        sceneMode: Cesium.SceneMode.SCENE3D,
        requestRenderMode: true,
        terrainProvider: new Cesium.EllipsoidTerrainProvider(),
        baseLayer: new Cesium.ImageryLayer(new Cesium.OpenStreetMapImageryProvider({
          url: 'https://tile.openstreetmap.org/'
        }))
      });
      cesiumViewer.scene.globe.show = true;
      cesiumViewer.scene.globe.enableLighting = false;

      var ctrl = cesiumViewer.scene.screenSpaceCameraController;
      ctrl.enableTilt  = false;
      ctrl.enableLook  = false;
      ctrl.tiltEventTypes  = [];
      ctrl.lookEventTypes  = [];
      ctrl.rotateEventTypes = [Cesium.CameraEventType.LEFT_DRAG];
      ctrl.zoomEventTypes   = [Cesium.CameraEventType.WHEEL, Cesium.CameraEventType.PINCH];

      if (q.map_3d_tiles) {
        var tilesetResource = new Cesium.Resource({
          url: q.map_3d_tiles,
          proxy: new Cesium.DefaultProxy('/proxy?url=')
        });
        Cesium.Cesium3DTileset.fromUrl(tilesetResource).then(function (tileset) {
          cesiumViewer.scene.primitives.add(tileset);
        }).catch(function (err) {
          console.error('[CesiumTilesetError]', err);
        });
      }

      var coords = q.area_coordinates;
      var positions = [];

      if (coords && coords.type === "Polygon" && coords.coordinates && coords.coordinates[0]) {
        var points = coords.coordinates[0];
        positions = points.map(function (pt) {
          return Cesium.Cartesian3.fromDegrees(pt[0], pt[1]);
        });
      } else if (Array.isArray(coords) && coords.length >= 3) {
        positions = coords.map(function (c) {
          var lng = c.longitude !== undefined ? c.longitude : c.lng || c[0];
          var lat = c.latitude  !== undefined ? c.latitude  : c.lat || c[1];
          return Cesium.Cartesian3.fromDegrees(lng, lat);
        });
      }

      if (positions.length >= 3) {
        cesiumViewer.entities.add({
          polygon: {
            hierarchy: new Cesium.PolygonHierarchy(positions),
            material: Cesium.Color.fromCssColorString('#696cff').withAlpha(0.35),
            classificationType: Cesium.ClassificationType.BOTH
          }
        });

        cesiumViewer.entities.add({
          polyline: {
            positions: positions,
            width: 3,
            material: Cesium.Color.fromCssColorString('#696cff'),
            classificationType: Cesium.ClassificationType.BOTH
          }
        });

        var boundingSphere = Cesium.BoundingSphere.fromPoints(positions);
        var radius = boundingSphere.radius;
        var zoomRange = Math.max(radius * 3.0, 500.0);
        var offset = new Cesium.HeadingPitchRange(0.0, Cesium.Math.toRadians(-90), zoomRange);

        cesiumViewer.zoomTo(cesiumViewer.entities, offset).then(function () {
          cesiumViewer.scene.requestRender();
        });

      } else if (q.map_x_axis && q.map_y_axis) {
        cesiumViewer.camera.setView({
          destination: Cesium.Cartesian3.fromDegrees(
            parseFloat(q.map_x_axis), parseFloat(q.map_y_axis), 5000
          ),
          orientation: {
            heading: 0.0,
            pitch: Cesium.Math.toRadians(-90),
            roll: 0.0
          }
        });
      }

      cesiumViewer.scene.requestRender();

    } catch (e) {
      console.error('[CesiumInitError]', e);
      container.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small"><i class="bx bx-map-alt me-2 fs-3"></i>Map preview unavailable<div class="text-danger mt-2 font-monospace" style="font-size:11px; max-width: 90%; word-break: break-all;">' + esc(e.message || e) + '</div></div>';
    }
  }

  function destroyCesium() {
    if (cesiumViewer && !cesiumViewer.isDestroyed()) {
      cesiumViewer.destroy();
    }
    cesiumViewer = null;
    var el = document.getElementById('adminCesiumMap');
    if (el) el.innerHTML = '';
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function calculatePolygonArea(points) {
    if (points.length < 3) return 0;
    var baseLat = points[0][1];
    var cosLat = Math.cos(baseLat * Math.PI / 180.0);
    var metersX = [];
    var metersY = [];
    for (var i = 0; i < points.length; i++) {
      metersX.push(points[i][0] * 111320.0 * cosLat);
      metersY.push(points[i][1] * 111320.0);
    }
    var area = 0.0;
    var j = points.length - 1;
    for (var i = 0; i < points.length; i++) {
      area += (metersX[j] + metersX[i]) * (metersY[j] - metersY[i]);
      j = i;
    }
    return Math.abs(area / 2.0);
  }

  function freezeOrUnfreezeAdminNotes() {
    var notesInput = document.getElementById('adminNotes');
    var editBtn = document.getElementById('btnEditAdminNotes');
    if (!notesInput || !editBtn) return;

    var currentVal = notesInput.value.trim();
    if (currentVal !== '') {
      notesInput.readOnly = true;
      notesInput.style.backgroundColor = '#f1f5f9';
      editBtn.classList.remove('d-none');
    } else {
      notesInput.readOnly = false;
      notesInput.style.backgroundColor = '';
      editBtn.classList.add('d-none');
    }
  }

  window.unfreezeAdminNotes = function () {
    var notesInput = document.getElementById('adminNotes');
    var editBtn = document.getElementById('btnEditAdminNotes');
    if (!notesInput || !editBtn) return;

    notesInput.readOnly = false;
    notesInput.style.backgroundColor = '';
    notesInput.focus();
    editBtn.classList.add('d-none');
  }

  window.confirmDeleteQuotation = function () {
    if (!currentQuotation) return;
    if (!confirm("Are you sure you want to permanently delete this inquiry request? This will cleanly remove the record from the database and delete all associated delivery files from the SFTP/local storage. This action CANNOT be undone.")) {
      return;
    }

    var btn = document.getElementById('btnDeleteQuotation');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting…';
    }

    fetch(API + '/api/admin/inquiries/' + currentQuotation.id, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      credentials: 'include'
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        detailModal.hide();
        showPageAlert(data.message, true);
        loadQuotations();
      } else {
        showModalAlert(data.message || 'Delete failed.', false);
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = '<i class="bx bx-trash me-1"></i> Delete Request';
        }
      }
    })
    .catch(function (err) {
      showModalAlert('Network error: ' + err.message, false);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-trash me-1"></i> Delete Request';
      }
    });
  };

  window.toggleDeliveryReady = function () {
    if (!currentQuotation) return;
    var isReady = currentQuotation.delivery_ready;
    var newReady = !isReady;

    var btn = document.getElementById('btnToggleDelivery');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; }

    fetch(API + '/api/admin/inquiries/' + currentQuotation.id + '/delivery', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      credentials: 'include',
      body: JSON.stringify({ delivery_ready: newReady, _token: CSRF }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        var idx = allInquiries.findIndex(function (q) { return q.id === currentQuotation.id; });
        if (idx !== -1) { allInquiries[idx] = data.data; currentQuotation = data.data; }
        renderStats(allInquiries);
        applyFilters();
        updateDeliverySection(data.data);
        showModalAlert(data.message, true);
      } else {
        showModalAlert(data.message || 'Failed to update delivery status.', false);
        if (btn) { btn.disabled = false; }
      }
    })
    .catch(function (err) {
      showModalAlert('Network error: ' + err.message, false);
      if (btn) { btn.disabled = false; }
    })
    .finally(function () {
      updateDeliveryToggleBtn(currentQuotation);
    });
  };

  window.checkDeliveryFiles = function () {
    if (!currentQuotation) return;
    var resultEl = document.getElementById('deliveryFileResult');
    resultEl.className = 'mt-2';
    resultEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking SFTP server…';

    fetch(API + '/api/admin/inquiries/' + currentQuotation.id + '/check-delivery', {
      credentials: 'include',
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success && data.file_count > 0) {
        var rows = (data.files || []).map(function (f) {
          return '<li><span class="fname"><i class="bx bx-file me-1 text-success"></i>' + esc(f.name) + '</span>' +
                 '<span class="fsize">' + esc(f.size_human) + '</span></li>';
        }).join('');
        resultEl.innerHTML =
          '<div class="alert alert-success py-2 px-3 mb-2 small"><i class="bx bx-check-circle me-1"></i>' +
          '<strong>' + data.file_count + ' file(s) found</strong> — Total: ' + esc(data.total_size_human) + '</div>' +
          '<ul class="delivery-file-list border rounded p-2">' + rows + '</ul>';
      } else if (data.success && data.file_count === 0) {
        resultEl.innerHTML =
          '<div class="alert alert-warning py-2 px-3 small"><i class="bx bx-error me-1"></i>' +
          'No files found in the delivery folder yet. Please upload via WinSCP first.</div>';
      } else {
        resultEl.innerHTML =
          '<div class="alert alert-danger py-2 px-3 small"><i class="bx bx-x-circle me-1"></i>' +
          esc(data.message || 'Could not check delivery folder.') + '</div>';
      }
    })
    .catch(function (err) {
      resultEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small">Error: ' + esc(err.message) + '</div>';
    });
  };

  function updateDeliverySection(q) {
    if (!q) return;

    var sftpHost = @json(config('filesystems.disks.sftp_delivery.host') ?: request()->getHost());
    var sftpUserHost = @json(config('filesystems.disks.sftp_delivery.username') ?: 'root');
    var sftpPortHost = @json(config('filesystems.disks.sftp_delivery.port') ?: 22);

    var hostEl = document.getElementById('sftpHostHost');
    if (hostEl) hostEl.textContent = sftpHost;
    var userHostEl = document.getElementById('sftpUserHost');
    if (userHostEl) userHostEl.textContent = sftpUserHost;
    var portHostEl = document.getElementById('sftpPortHost');
    if (portHostEl) portHostEl.textContent = sftpPortHost;

    var pathHostEl = document.getElementById('deliverySftpPathHost');
    if (pathHostEl) {
      pathHostEl.textContent = q.sftp_delivery_relative ? ('/' + q.sftp_delivery_relative) : '—';
    }

    var badge = document.getElementById('deliveryStatusBadge');
    if (badge) {
      if (q.delivery_ready) {
        badge.className = 'delivery-ready-badge-on';
        badge.innerHTML = '<span>✅</span> Ready for Download';
        if (q.delivered_at) {
          badge.innerHTML += '<span class="text-muted fw-normal" style="font-size:11px;"> · ' + esc(q.delivered_at) + '</span>';
        }
      } else {
        badge.className = 'delivery-ready-badge-off';
        badge.innerHTML = '<span>⏳</span> Not Ready';
      }
    }
    updateDeliveryToggleBtn(q);
  }

  window.launchWinSftp = function (type) {
    if (!currentQuotation) return;
    var host = @json(config('filesystems.disks.sftp_delivery.host') ?: request()->getHost());
    var path = currentQuotation.sftp_delivery_relative ? ('/' + currentQuotation.sftp_delivery_relative) : '';
    var user = @json(config('filesystems.disks.sftp_delivery.username') ?: 'root');
    var port = @json(config('filesystems.disks.sftp_delivery.port') ?: 22);
    
    var sftpUrl = 'sftp://' + encodeURIComponent(user) + '@' + host + ':' + port + path;
    window.location.href = sftpUrl;
  };

  window.copyPathToClipboard = function (elementId, btn) {
    var text = document.getElementById(elementId).textContent;
    if (text === '—') return;
    navigator.clipboard.writeText(text).then(function() {
      var icon = btn.querySelector('i');
      var originalClass = icon.className;
      icon.className = 'bx bx-check';
      btn.classList.remove('btn-outline-secondary');
      btn.classList.add('btn-success');
      setTimeout(function() {
        icon.className = originalClass;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
      }, 2000);
    });
  };

  function updateDeliveryToggleBtn(q) {
    var btn = document.getElementById('btnToggleDelivery');
    if (!btn) return;
    btn.disabled = false;
    if (q && q.delivery_ready) {
      btn.className = 'btn btn-sm btn-outline-warning px-3';
      btn.innerHTML = '<i class="bx bx-x-circle me-1"></i> Mark as Not Ready';
    } else {
      btn.className = 'btn btn-sm btn-outline-success px-3';
      btn.innerHTML = '<i class="bx bx-check-circle me-1"></i> Mark as Ready';
    }
  }

  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var pdfInput = document.getElementById('quotationPdfInput');
    if (pdfInput) {
      pdfInput.addEventListener('change', function () {
        var file = this.files[0];
        if (file) {
          if (file.type !== 'application/pdf') {
            showPdfUploadError('Please select a valid PDF file.');
            resetPdfUploadState();
            return;
          }
          if (file.size > 20 * 1024 * 1024) {
            showPdfUploadError('File size exceeds the 20MB limit.');
            resetPdfUploadState();
            return;
          }
          hidePdfUploadError();
          document.getElementById('pdfUploadPrompt').classList.add('d-none');
          document.getElementById('pdfSelectedInfo').classList.remove('d-none');
          document.getElementById('pdfFileName').textContent = file.name;
          document.getElementById('pdfFileSize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
      });
    }
  });

  window.removeSelectedPdf = function (event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    resetPdfUploadState();
  };

  function resetPdfUploadState() {
    var pdfInput = document.getElementById('quotationPdfInput');
    if (pdfInput) pdfInput.value = '';
    var prompt = document.getElementById('pdfUploadPrompt');
    if (prompt) prompt.classList.remove('d-none');
    var info = document.getElementById('pdfSelectedInfo');
    if (info) info.classList.add('d-none');
    hidePdfUploadError();
  }

  function showPdfUploadError(msg) {
    var el = document.getElementById('pdfUploadError');
    if (el) {
      el.textContent = msg;
      el.classList.remove('d-none');
    }
  }

  function hidePdfUploadError() {
    var el = document.getElementById('pdfUploadError');
    if (el) {
      el.classList.add('d-none');
      el.textContent = '';
    }
  }

})();
</script>
</body>
</html>
