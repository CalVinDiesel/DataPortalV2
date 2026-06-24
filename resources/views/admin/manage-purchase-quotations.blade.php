<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Purchase Quotations - Admin | 3DHub</title>
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
  <link href="https://cesium.com/downloads/cesiumjs/releases/1.120/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
  <script src="https://cesium.com/downloads/cesiumjs/releases/1.120/Build/Cesium/Cesium.js"></script>

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
    .purchase-id-cell { font-family: monospace; font-weight: 700; color: #4f46e5; font-size: 13px; }

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
        <a href="{{ route('admin.purchase_quotations') }}" class="admin-nav-link active">Purchase Quotations</a>
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
              <h4 class="fw-bold mb-1"><i class="bx bx-receipt me-2 text-primary"></i>Manage Purchase Quotations</h4>
              <p class="text-muted mb-0 small">Review client requests, preview their selected area on the 3D map, and send formal quotations with pricing</p>
            </div>
            <a href="{{ route('admin_dashboard') }}" class="btn btn-sm btn-outline-primary">← Back to Dashboard</a>
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
              <button class="filter-tab" data-status="quoted">💼 Quoted</button>
              <button class="filter-tab" data-status="awaiting_payment">🏦 Awaiting Payment</button>
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
                    <th>Purchase ID</th>
                    <th>Client</th>
                    <th>3D Model</th>
                    <th>Output Formats</th>
                    <th>Date</th>
                    <th>Quoted Price</th>
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
          <span id="modalPurchaseId">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <div class="row g-0" style="min-height:500px;">

          <!-- LEFT: Info + Map -->
          <div class="col-lg-5 p-4 border-end">

            <div class="detail-section">
              <div class="ds-title">📋 Quotation Info</div>
              <div class="info-pair"><span class="lbl">Purchase ID</span><br><strong id="dPurchaseId" style="font-family:monospace;color:#4f46e5;"></strong></div>
              <div class="info-pair"><span class="lbl">Client Email</span><br><strong id="dUserEmail"></strong></div>
              <div class="info-pair"><span class="lbl">Client Name</span><br><span id="dUserName"></span></div>
              <div class="info-pair"><span class="lbl">Date Submitted</span><br><span id="dCreatedAt"></span></div>
              <div class="info-pair"><span class="lbl">Last Updated</span><br><span id="dUpdatedAt"></span></div>
            </div>

            <div class="detail-section">
              <div class="ds-title">📍 3D Model & Area</div>
              <div class="info-pair"><span class="lbl">Model Title</span><br><strong id="dMapTitle"></strong></div>
              <div class="info-pair"><span class="lbl">Output Formats Requested</span><br><div id="dOutputCategories"></div></div>
            </div>

            <!-- Cesium Map -->
            <div class="detail-section">
              <div class="ds-title">🗺️ Selected Area Preview</div>
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
              <div class="ds-title">⚙️ Update Quotation Status</div>

              <div id="modalAlert" class="alert d-none mb-3" role="alert"></div>

              <div class="mb-3">
                <label for="statusSelect" class="form-label fw-semibold small">Status</label>
                <select id="statusSelect" class="form-select">
                  <option value="pending">⏳ Pending Review</option>
                  <option value="reviewed">🔍 Under Review</option>
                  <option value="quoted">💼 Quotation Sent</option>
                  <option value="awaiting_payment">🏦 Awaiting Payment</option>
                  <option value="processing">⚙️ Processing</option>
                  <option value="completed">✅ Completed</option>
                  <option value="rejected">❌ Rejected</option>
                </select>
                <div class="form-text" id="statusHelpText"></div>
              </div>

              <!-- Pricing section (shown when status = quoted) -->
              <div class="admin-form-section cond-section" id="sectionPricing">
                <h6>💰 Pricing</h6>
                <div class="mb-2">
                  <label class="form-label small fw-semibold">Quoted Price (RM) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">RM</span>
                    <input type="number" id="quotedPrice" class="form-control" min="0" step="0.01" placeholder="e.g. 500.00">
                  </div>
                </div>
              </div>

              <!-- Bank Details section (shown when status = quoted) -->
              <div class="admin-form-section cond-section" id="sectionBankDetails">
                <h6>🏦 Bank Payment Details</h6>
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" id="bankName" class="form-control" placeholder="e.g. Maybank">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small fw-semibold">Account Number <span class="text-danger">*</span></label>
                    <input type="text" id="bankAccountNumber" class="form-control" placeholder="e.g. 1234-5678-9012">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small fw-semibold">Account Holder Name <span class="text-danger">*</span></label>
                    <input type="text" id="bankAccountName" class="form-control" placeholder="e.g. Tema Digital Sdn Bhd">
                  </div>
                  <div class="col-12">
                    <label class="form-label small fw-semibold">Payment Deadline</label>
                    <input type="date" id="paymentDeadline" class="form-control">
                  </div>
                </div>
              </div>

              <!-- Rejection reason (shown when status = rejected) -->
              <div class="admin-form-section cond-section" id="sectionRejection">
                <h6>❌ Rejection Reason</h6>
                <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Explain why this quotation is being rejected…"></textarea>
              </div>

              <!-- Admin Notes (always visible) -->
              <div class="admin-form-section mt-3">
                <h6>📝 Admin Notes <small class="text-muted fw-normal">(shown to client)</small></h6>
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
              </div>

              <!-- Existing data section -->
              <div id="existingDataSection" class="mt-4 d-none">
                <hr>
                <div class="ds-title mb-2">📌 Current Quotation Data</div>
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
  var allQuotations = [];
  var currentFilter = 'all';
  var currentSearch = '';
  var currentQuotation = null;
  var cesiumViewer   = null;
  var detailModal    = null;

  var STATUS_ORDER = ['pending','reviewed','quoted','awaiting_payment','processing','completed'];
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

  // ─── Load Quotations ─────────────────────────────────────────────────────
  function loadQuotations() {
    fetch(API + '/api/admin/purchase-quotations', { credentials: 'include' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        allQuotations = data;
        renderStats(data);
        renderTable(data);
      })
      .catch(function (err) {
        document.getElementById('quotationsTableBody').innerHTML =
          '<tr><td colspan="8" class="text-danger text-center py-4">Failed to load quotations. ' + err.message + '</td></tr>';
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
      { label: 'Quoted',         key: 'quoted',           icon: 'bx-money', color: 'info' },
      { label: 'Awaiting Pay',   key: 'awaiting_payment', icon: 'bx-credit-card', color: 'warning' },
      { label: 'Processing',     key: 'processing',       icon: 'bx-loader-alt', color: 'purple' },
      { label: 'Completed',      key: 'completed',        icon: 'bx-check-circle', color: 'success' },
    ];
    statsEl.innerHTML = items.map(function (item) {
      return '<div class="col-6 col-sm-4 col-lg-2 mb-2">' +
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
    var filtered = allQuotations.filter(function (q) {
      var matchStatus = currentFilter === 'all' || q.status === currentFilter;
      var matchSearch = !currentSearch ||
        q.purchase_id.toLowerCase().includes(currentSearch) ||
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
      tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5"><i class="bx bx-inbox display-6 d-block mb-2"></i>No quotations found</td></tr>';
      return;
    }
    tbody.innerHTML = data.map(function (q) {
      var fmts = (q.output_categories || []).map(function (c) {
        return '<span class="fmt-tag">' + esc(c) + '</span>';
      }).join('');
      var price = q.quoted_price ? 'RM ' + parseFloat(q.quoted_price).toFixed(2) : '<span class="text-muted">—</span>';
      return '<tr data-id="' + q.id + '">' +
        '<td class="purchase-id-cell">' + esc(q.purchase_id) + '</td>' +
        '<td><div class="fw-semibold" style="font-size:13px;">' + esc(q.user_email) + '</div><div class="text-muted" style="font-size:11.5px;">' + esc(q.user_name) + '</div></td>' +
        '<td>' + esc(q.map_title) + '</td>' +
        '<td>' + (fmts || '<span class="text-muted">—</span>') + '</td>' +
        '<td style="white-space:nowrap;font-size:12px;">' + esc(q.created_at) + '</td>' +
        '<td><strong>' + price + '</strong></td>' +
        '<td><span class="sb sb-' + esc(q.status) + '"><span class="dot"></span>' + esc(q.status_label) + '</span></td>' +
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
    var q = allQuotations.find(function (x) { return x.id === id; });
    if (!q) return;
    currentQuotation = q;
    populateModal(q);
    detailModal.show();
    setTimeout(function () { initCesium(q); }, 400);
  };

  function populateModal(q) {
    // Header
    document.getElementById('modalPurchaseId').textContent = q.purchase_id;
    // Left panel
    document.getElementById('dPurchaseId').textContent  = q.purchase_id;
    document.getElementById('dUserEmail').textContent   = q.user_email;
    document.getElementById('dUserName').textContent    = q.user_name;
    document.getElementById('dCreatedAt').textContent   = q.created_at;
    document.getElementById('dUpdatedAt').textContent   = q.updated_at;
    document.getElementById('dMapTitle').textContent    = q.map_title;
    var fmts = (q.output_categories || []).map(function (c) {
      return '<span class="fmt-tag">' + esc(c) + '</span>';
    }).join('');
    document.getElementById('dOutputCategories').innerHTML = fmts || '<span class="text-muted">—</span>';

    // Status timeline
    renderModalTimeline(q.status);

    // Right panel — pre-fill form
    document.getElementById('statusSelect').value     = q.status;
    document.getElementById('adminNotes').value       = q.admin_notes || '';
    document.getElementById('rejectionReason').value  = q.rejection_reason || '';
    document.getElementById('quotedPrice').value      = q.quoted_price || '';
    document.getElementById('bankName').value         = q.bank_name || '';
    document.getElementById('bankAccountNumber').value= q.bank_account_number || '';
    document.getElementById('bankAccountName').value  = q.bank_account_name || '';
    document.getElementById('paymentDeadline').value  = q.payment_deadline || '';

    updateConditionalSections(q.status);
    showExistingData(q);
    clearModalAlert();
  }

  function renderModalTimeline(status) {
    var isRejected = status === 'rejected';
    var currentIdx = isRejected ? -1 : STATUS_ORDER.indexOf(status);
    var container  = document.getElementById('modalStatusSteps');
    container.innerHTML = STATUS_ORDER.map(function (step, i) {
      var cls;
      if (isRejected) cls = '';
      else if (i < currentIdx) cls = 'mss-done';
      else if (i === currentIdx) cls = 'mss-active';
      else cls = '';
      var icon = (i < currentIdx) ? '✓' : (i + 1);
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
    if (q.quoted_price || q.bank_name || q.payment_deadline || q.rejection_reason) {
      sec.classList.remove('d-none');
      var lines = [];
      if (q.quoted_price)         lines.push('<strong>Quoted Price:</strong> RM ' + parseFloat(q.quoted_price).toFixed(2));
      if (q.bank_name)            lines.push('<strong>Bank:</strong> ' + esc(q.bank_name));
      if (q.bank_account_number)  lines.push('<strong>Account No:</strong> ' + esc(q.bank_account_number));
      if (q.bank_account_name)    lines.push('<strong>Account Name:</strong> ' + esc(q.bank_account_name));
      if (q.payment_deadline_fmt) lines.push('<strong>Payment Deadline:</strong> ' + esc(q.payment_deadline_fmt));
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
    });
  }

  function updateConditionalSections(status) {
    var helpTexts = {
      pending:          'Mark as received and not yet reviewed.',
      reviewed:         'You have reviewed this request and are calculating the price.',
      quoted:           'Enter the price and bank details, then click "Save & Send" to email the client.',
      awaiting_payment: 'Client has been notified; waiting for bank transfer confirmation.',
      processing:       'Payment received and verified. Actively processing the 3D model for delivery.',
      completed:        '3D model has been delivered to the client. Job is done.',
      rejected:         'Enter a reason for rejection so the client understands.',
    };
    document.getElementById('statusHelpText').textContent = helpTexts[status] || '';

    // Show/hide conditional sections
    document.getElementById('sectionPricing').classList.toggle('visible', status === 'quoted');
    document.getElementById('sectionBankDetails').classList.toggle('visible', status === 'quoted');
    document.getElementById('sectionRejection').classList.toggle('visible', status === 'rejected');

    // Show "Save & Send" button only when status = quoted
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

    // Validate quoted fields
    if (status === 'quoted') {
      var price = document.getElementById('quotedPrice').value;
      var bName = document.getElementById('bankName').value.trim();
      var bNum  = document.getElementById('bankAccountNumber').value.trim();
      var bAcc  = document.getElementById('bankAccountName').value.trim();
      if (!price || parseFloat(price) <= 0) {
        showModalAlert('Please enter a valid quoted price.', false); return;
      }
      if (!bName || !bNum || !bAcc) {
        showModalAlert('Please fill in all bank payment details.', false); return;
      }
    }

    var payload = {
      status:               status,
      admin_notes:          document.getElementById('adminNotes').value.trim(),
      rejection_reason:     document.getElementById('rejectionReason').value.trim(),
      quoted_price:         document.getElementById('quotedPrice').value || null,
      bank_name:            document.getElementById('bankName').value.trim() || null,
      bank_account_number:  document.getElementById('bankAccountNumber').value.trim() || null,
      bank_account_name:    document.getElementById('bankAccountName').value.trim() || null,
      payment_deadline:     document.getElementById('paymentDeadline').value || null,
      _token:               CSRF,
    };

    setButtonLoading(true);
    clearModalAlert();

    fetch(API + '/api/admin/purchase-quotations/' + currentQuotation.id + '/status', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      credentials: 'include',
      body: JSON.stringify(payload),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        showModalAlert(data.message, true);
        // Update local state
        var idx = allQuotations.findIndex(function (q) { return q.id === currentQuotation.id; });
        if (idx !== -1) { allQuotations[idx] = data.data; currentQuotation = data.data; }
        renderStats(allQuotations);
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
        terrainProvider: new Cesium.EllipsoidTerrainProvider(),
        baseLayer: new Cesium.ImageryLayer(new Cesium.OpenStreetMapImageryProvider({
          url: 'https://tile.openstreetmap.org/'
        }))
      });
      cesiumViewer.scene.globe.show = true;

      // Load 3D tileset if available
      if (q.map_3d_tiles) {
        var tileset = new Cesium.Cesium3DTileset({ url: q.map_3d_tiles });
        cesiumViewer.scene.primitives.add(tileset);
        tileset.readyPromise.then(function () {
          cesiumViewer.zoomTo(tileset);
        }).otherwise(function () {});
      }

      // Draw polygon from area_coordinates
      var coords = q.area_coordinates;
      if (coords && coords.length >= 3) {
        var positions = coords.map(function (c) {
          var lng = c.longitude !== undefined ? c.longitude : c.lng || c[0];
          var lat = c.latitude  !== undefined ? c.latitude  : c.lat || c[1];
          return Cesium.Cartesian3.fromDegrees(lng, lat);
        });
        cesiumViewer.entities.add({
          polygon: {
            hierarchy: new Cesium.PolygonHierarchy(positions),
            material: Cesium.Color.fromCssColorString('#696cff').withAlpha(0.3),
            outline: true,
            outlineColor: Cesium.Color.fromCssColorString('#696cff'),
            outlineWidth: 2,
          }
        });
        cesiumViewer.zoomTo(cesiumViewer.entities);
      } else if (q.map_x_axis && q.map_y_axis) {
        cesiumViewer.camera.setView({
          destination: Cesium.Cartesian3.fromDegrees(
            parseFloat(q.map_x_axis), parseFloat(q.map_y_axis), 2000
          )
        });
      }
    } catch (e) {
      container.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small"><i class="bx bx-map-alt me-2"></i>Map preview unavailable</div>';
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
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

})();
</script>
</body>
</html>
