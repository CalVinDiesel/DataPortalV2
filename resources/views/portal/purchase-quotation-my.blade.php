<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>My Purchase Quotations | 3DHub Data Portal</title>
  <meta name="description" content="View and track all your purchase quotation requests on the 3DHub Data Portal.">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/client-responsive.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>

  <style>
    :root {
      --status-pending:   #f59e0b;
      --status-reviewed:  #0ea5e9;
      --status-quoted:    #696cff;
      --status-awaiting:  #f97316;
      --status-processing:#8b5cf6;
      --status-completed: #10b981;
      --status-rejected:  #ef4444;
    }

    body { font-family: 'Inter', sans-serif; }

    /* Navbar hover dropdowns */
    @media (min-width: 1200px) {
      #navPurchaseQuotation:hover .dropdown-menu,
      #navUpload:hover .dropdown-menu { display: block; margin-top: 0; }
    }

    /* ── Page Hero ── */
    .pq-hero {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 8rem 0 4.5rem;
      position: relative;
      overflow: hidden;
    }
    @media (max-width: 991px) {
      .pq-hero {
        padding-top: 7rem;
      }
    }
    .pq-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .pq-hero h1 { color: #fff; font-weight: 800; font-size: 2rem; margin: 0; }
    .pq-hero p  { color: rgba(255,255,255,.8); margin: .5rem 0 0; }
    .pq-hero .btn-new { background: #fff; color: #764ba2; font-weight: 700; border: none; border-radius: 10px; padding: .6rem 1.4rem; text-decoration: none; display: inline-flex; align-items: center; gap:.4rem; transition: transform .2s, box-shadow .2s; }
    .pq-hero .btn-new:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.15); }

    /* ── Cards area ── */
    .pq-content { margin-top: -2.5rem; padding-bottom: 4rem; }

    /* ── Status badges ── */
    .status-badge {
      display: inline-flex; align-items: center; gap: .35rem;
      padding: .28rem .75rem; border-radius: 20px; font-size: 12px; font-weight: 700;
      white-space: nowrap;
    }
    .status-badge .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
    .sb-pending    { background: #fffbeb; color: #92400e; border: 1.5px solid #fcd34d; }
    .sb-pending .dot    { background: var(--status-pending); }
    .sb-reviewed   { background: #f0f9ff; color: #0c4a6e; border: 1.5px solid #7dd3fc; }
    .sb-reviewed .dot   { background: var(--status-reviewed); }
    .sb-quoted     { background: #f0f0ff; color: #3730a3; border: 1.5px solid #c7d2fe; }
    .sb-quoted .dot     { background: var(--status-quoted); }
    .sb-awaiting_payment { background: #fff7ed; color: #7c2d12; border: 1.5px solid #fed7aa; }
    .sb-awaiting_payment .dot { background: var(--status-awaiting); }
    .sb-processing { background: #f5f3ff; color: #4c1d95; border: 1.5px solid #ddd6fe; }
    .sb-processing .dot { background: var(--status-processing); }
    .sb-completed  { background: #f0fdf4; color: #065f46; border: 1.5px solid #6ee7b7; }
    .sb-completed .dot  { background: var(--status-completed); }
    .sb-rejected   { background: #fef2f2; color: #7f1d1d; border: 1.5px solid #fca5a5; }
    .sb-rejected .dot   { background: var(--status-rejected); }

    /* ── Quotation Card ── */
    .q-card {
      background: #fff;
      border: 1.5px solid #e5e7eb;
      border-radius: 14px;
      margin-bottom: 1rem;
      overflow: hidden;
      transition: box-shadow .2s, border-color .2s;
    }
    .q-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); border-color: #c4b5fd; }
    .q-card-header {
      padding: 1.1rem 1.4rem;
      cursor: pointer;
      display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
      user-select: none;
      position: relative;
    }
    .q-card-header .purchase-id {
      font-weight: 700; color: #4f46e5; font-size: 14px; font-family: 'Courier New', monospace;
      background: #f0f0ff; padding: .2rem .6rem; border-radius: 6px;
    }
    .q-card-header .meta { flex: 1; min-width: 0; }
    .q-card-header .meta .model-name { font-weight: 600; color: #111; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .q-card-header .meta .date-info  { font-size: 12px; color: #888; margin-top: 2px; }
    .q-card-header .chevron { color: #aaa; transition: transform .3s; flex-shrink: 0; font-size: 18px; }
    .q-card-header.open .chevron { transform: rotate(180deg); }

    /* ── Detail panel ── */
    .q-card-body { display: none; border-top: 1.5px solid #f0f0f0; }
    .q-card-body.open { display: block; }
    .q-card-body-inner { padding: 1.4rem; }

    /* Timeline */
    .status-timeline {
      display: flex; align-items: center; gap: 0; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: .5rem;
    }
    .tl-step {
      display: flex; flex-direction: column; align-items: center; min-width: 80px; position: relative; flex: 1;
    }
    .tl-step .tl-dot {
      width: 28px; height: 28px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;
      z-index: 1; position: relative;
    }
    .tl-step .tl-label { font-size: 10px; font-weight: 600; color: #aaa; margin-top: 6px; text-align: center; }
    .tl-step::before {
      content: ''; position: absolute; top: 14px; left: calc(-50% + 14px); right: calc(50% + 14px);
      height: 2px; background: #e5e7eb;
    }
    .tl-step:first-child::before { display: none; }
    .tl-step.tl-done .tl-dot   { background: #10b981; color: #fff; }
    .tl-step.tl-done::before   { background: #10b981; }
    .tl-step.tl-done .tl-label { color: #10b981; }
    .tl-step.tl-active .tl-dot { background: #696cff; color: #fff; box-shadow: 0 0 0 4px rgba(105,108,255,.18); }
    .tl-step.tl-active .tl-label { color: #696cff; font-weight: 700; }
    .tl-step.tl-rejected .tl-dot { background: #ef4444; color: #fff; }
    .tl-step.tl-rejected .tl-label { color: #ef4444; }
    .tl-step.tl-pending .tl-dot { background: #e5e7eb; color: #aaa; }

    /* Info grid */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    @media(max-width: 576px) { .detail-grid { grid-template-columns: 1fr; } }
    .detail-block { background: #f8fafc; border-radius: 10px; padding: .85rem 1rem; }
    .detail-block .db-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: .7px; margin-bottom: .3rem; }
    .detail-block .db-value { font-size: 14px; font-weight: 600; color: #111; }

    /* Bank box */
    .bank-payment-box {
      background: linear-gradient(135deg, #fffbeb, #fefce8);
      border: 2px solid #fcd34d; border-radius: 12px; padding: 1.25rem 1.4rem; margin-top: 1rem;
    }
    .bank-payment-box h6 { font-weight: 700; color: #92400e; margin: 0 0 .75rem; display: flex; align-items: center; gap:.4rem; }
    .bank-row { display: flex; justify-content: space-between; align-items: center; padding: .55rem 0; border-bottom: 1px solid #fde68a; font-size: 13.5px; }
    .bank-row:last-child { border-bottom: none; }
    .bank-row .bl { color: #b45309; font-weight: 600; }
    .bank-row .bv { font-weight: 700; color: #1a1a1a; }
    .bank-row .bv.price { color: #059669; font-size: 16px; }
    .bank-row .bv.deadline { color: #dc2626; }

    /* Rejection box */
    .rejection-box { background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 0 8px 8px 0; padding: .85rem 1rem; margin-top: 1rem; font-size: 13.5px; color: #7f1d1d; }
    .rejection-box .label { font-weight: 700; margin-bottom: .25rem; }

    /* Admin notes box */
    .notes-box { background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 0 8px 8px 0; padding: .85rem 1rem; margin-top: .75rem; font-size: 13.5px; color: #0c4a6e; }
    .notes-box .label { font-weight: 700; margin-bottom: .25rem; }

    /* Format tags */
    .fmt-tag { display: inline-block; background: #ede9fe; color: #6d28d9; border-radius: 20px; padding: .2rem .7rem; font-size: 12px; font-weight: 600; margin: .15rem .2rem .15rem 0; }

    /* Empty state */
    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state .icon { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
    .empty-state h5 { font-weight: 700; color: #374151; margin-bottom: .5rem; }
    .empty-state p  { color: #9ca3af; margin-bottom: 1.5rem; }

    /* Download button */
    .btn-download-tiles {
      display: inline-flex; align-items: center; gap: .5rem;
      background: linear-gradient(135deg, #059669, #0d9488);
      color: #fff; font-weight: 700; border: none;
      border-radius: 10px; padding: .65rem 1.5rem;
      font-size: 14px; text-decoration: none;
      transition: transform .2s, box-shadow .2s;
      cursor: pointer;
    }
    .btn-download-tiles:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(5,150,105,.35); color: #fff; }
    .btn-download-tiles:disabled { opacity: .6; pointer-events: none; }
    .delivery-preparing {
      display: flex; align-items: center; gap: .75rem;
      background: #f5f3ff; border: 1.5px solid #ddd6fe;
      border-radius: 10px; padding: .85rem 1.1rem;
      color: #4c1d95; font-size: 13.5px;
    }

    /* ── Navbar Contrast & Unified Color Fix ── */
    /* Logo text */
    .landing-navbar .app-brand-text {
      color: rgba(255, 255, 255, 0.95) !important;
    }
    
    /* Navigation links (Desktop only to prevent white-on-white in mobile drawer) */
    @media (min-width: 1200px) {
      .landing-navbar .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
        transition: color 0.2s ease;
      }
      .landing-navbar .navbar-nav .nav-link:hover,
      .landing-navbar .navbar-nav .nav-link:focus,
      .landing-navbar .navbar-nav .nav-link.active,
      .landing-navbar .navbar-nav .show > .nav-link {
        color: #cbd5ff !important;
      }
    }
    
    /* Theme switcher icon */
    .landing-navbar #nav-theme {
      color: rgba(255, 255, 255, 0.85) !important;
    }
    .landing-navbar #nav-theme:hover {
      color: #cbd5ff !important;
    }

    /* Email text */
    #navUserWrap .navbar-text,
    .landing-navbar .navbar-text {
      color: rgba(255, 255, 255, 0.85) !important;
      transition: color 0.25s ease;
    }
    #navUserWrap .navbar-text:hover,
    .landing-navbar .navbar-text:hover {
      color: #cbd5ff !important;
    }

    /* Logout button */
    #navLogoutBtn {
      color: #ffffff !important;
      border-color: rgba(255, 255, 255, 0.4) !important;
      background-color: rgba(255, 255, 255, 0.08) !important;
      transition: all 0.2s ease-in-out;
    }
    #navLogoutBtn:hover {
      color: #cbd5ff !important;
      border-color: #cbd5ff !important;
      background-color: rgba(255, 255, 255, 0.2) !important;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Mobile toggler icon */
    .landing-navbar .navbar-toggler i {
      color: rgba(255, 255, 255, 0.9) !important;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-xl landing-navbar px-3 px-md-8">
        <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
          <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <i class="icon-base bx bx-menu icon-lg align-middle text-heading fw-medium"></i>
          </button>
          <a href="{{ route('landing') }}" class="app-brand-link">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub Logo" style="height:80px;width:auto;max-height:80px;object-fit:contain;display:block;" />
            <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub</span>
          </a>
        </div>

        <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
          <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <i class="icon-base bx bx-x icon-lg"></i>
          </button>
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link fw-medium" href="{{ route('landing') }}#landingHero">Home</a></li>
            <li class="nav-item"><a class="nav-link fw-medium" href="{{ route('landing') }}#landingShowCase">ShowCase</a></li>
            @auth
            <li class="nav-item dropdown d-none d-xl-block" id="navPurchaseQuotation">
              <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" data-bs-toggle="dropdown">PurchaseQuotation</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('purchase_quotation.new') }}">New PurchaseQuotation</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item fw-bold text-primary" href="{{ route('purchase_quotation.my') }}">My PurchaseQuotation</a></li>
              </ul>
            </li>
            <li class="nav-item d-xl-none">
              <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navPQCollapse">PurchaseQuotation</a>
              <div class="collapse nav-upload-mobile-sub" id="navPQCollapse">
                <a class="nav-link fw-medium" href="{{ route('purchase_quotation.new') }}">New PurchaseQuotation</a>
                <hr class="dropdown-divider">
                <a class="nav-link fw-medium" href="{{ route('purchase_quotation.my') }}">My PurchaseQuotation</a>
              </div>
            </li>
            @endauth
            <li class="nav-item"><a class="nav-link fw-medium" href="{{ route('landing') }}#landingFAQ">FAQ</a></li>
            <li class="nav-item"><a class="nav-link fw-medium" href="{{ route('landing') }}#landingContact">Contact us</a></li>
          </ul>
        </div>
        <div class="landing-menu-overlay d-xl-none"></div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
          <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"><span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span></button></li>
              <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"><span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span></button></li>
              <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"><span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span></button></li>
            </ul>
          </li>
          @auth
          <li id="navUserWrap" class="d-flex align-items-center">
            <a href="{{ route('profile') }}" class="navbar-text text-body me-3 d-none d-md-inline text-decoration-none fw-medium">{{ Auth::user()->email }}</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="button" id="navLogoutBtn" class="btn btn-outline-secondary btn-sm"><span class="tf-icons icon-base bx bx-log-out me-1"></span>Log out</button>
            </form>
          </li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>
  <!-- /Navbar -->

  <!-- Hero -->
  <div class="pq-hero">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
          <h1><i class="bx bx-receipt me-2"></i>My Purchase Quotations</h1>
          <p>Track all your 3D model data purchase requests and their current status</p>
        </div>
        <a href="{{ route('purchase_quotation.new') }}" class="btn-new">
          <i class="bx bx-plus"></i> New Quotation
        </a>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="pq-content">
    <div class="container">

      @php
        $statusOrder = ['pending','reviewed','quoted','awaiting_payment','processing','completed'];
        $statusLabels = [
          'pending'          => 'Pending',
          'reviewed'         => 'Reviewed',
          'quoted'           => 'Quoted',
          'awaiting_payment' => 'Awaiting Payment',
          'processing'       => 'Processing',
          'completed'        => 'Completed',
        ];
        $statusIcons = [
          'pending'          => '⏳',
          'reviewed'         => '🔍',
          'quoted'           => '💼',
          'awaiting_payment' => '🏦',
          'processing'       => '⚙️',
          'completed'        => '✅',
        ];
      @endphp

      @if($quotations->isEmpty())
        <div class="card shadow-sm">
          <div class="card-body empty-state">
            <i class="bx bx-file-blank icon"></i>
            <h5>No purchase quotations yet</h5>
            <p>You have not submitted any purchase quotation requests.<br>Get started by creating your first one.</p>
            <a href="{{ route('purchase_quotation.new') }}" class="btn btn-primary px-4"><i class="bx bx-plus me-1"></i>New Purchase Quotation</a>
          </div>
        </div>
      @else
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <p class="text-muted mb-0 small">Showing <strong>{{ $quotations->count() }}</strong> quotation{{ $quotations->count() !== 1 ? 's' : '' }} — click any row to view details</p>
        </div>

        @foreach($quotations as $quote)
          @php
            $isRejected = $quote->status === 'rejected';
            $currentIdx = $isRejected ? -1 : array_search($quote->status, $statusOrder);
          @endphp

          <div class="q-card" id="qcard-{{ $quote->id }}">
            <!-- Card Header (clickable) -->
            <div class="q-card-header" onclick="toggleCard({{ $quote->id }})">
              <div>
                <span class="purchase-id">{{ $quote->purchase_id }}</span>
              </div>
              <div class="meta">
                <div class="model-name">{{ $quote->mapData->title ?? $quote->map_data_id }}</div>
                <div class="date-info">
                  <i class="bx bx-calendar me-1"></i>{{ $quote->created_at->format('d M Y, h:i A') }}
                  &nbsp;·&nbsp;
                  @foreach(is_array($quote->output_categories) ? $quote->output_categories : [] as $cat)
                    <span class="fmt-tag">{{ $cat }}</span>
                  @endforeach
                </div>
              </div>
              <div>
                <span class="status-badge sb-{{ $quote->status }}">
                  <span class="dot"></span>
                  {{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quote->status] ?? ucfirst($quote->status) }}
                </span>
              </div>
              <i class="bx bx-chevron-down chevron"></i>
            </div>

            <!-- Card Body (expandable) -->
            <div class="q-card-body" id="qbody-{{ $quote->id }}">
              <div class="q-card-body-inner">

                <!-- Status Timeline -->
                @if(!$isRejected)
                  <div class="status-timeline">
                    @foreach($statusOrder as $si => $step)
                      @php
                        if ($currentIdx === -1) {
                          $cls = 'tl-pending';
                        } elseif ($si < $currentIdx) {
                          $cls = 'tl-done';
                        } elseif ($si === $currentIdx) {
                          $cls = 'tl-active';
                        } else {
                          $cls = 'tl-pending';
                        }
                      @endphp
                      <div class="tl-step {{ $cls }}">
                        <div class="tl-dot">
                          @if($si < $currentIdx) <i class="bx bx-check" style="font-size:14px;"></i>
                          @else {{ $si + 1 }}
                          @endif
                        </div>
                        <div class="tl-label">{{ $statusIcons[$step] }} {{ $statusLabels[$step] }}</div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:#fef2f2;border:1.5px solid #fca5a5;">
                    <span style="font-size:20px;">❌</span>
                    <div>
                      <div style="font-weight:700;color:#7f1d1d;">This quotation has been rejected</div>
                      <div style="font-size:13px;color:#991b1b;">Please contact us if you have any questions</div>
                    </div>
                  </div>
                @endif

                <!-- Info Grid -->
                <div class="detail-grid">
                  <div class="detail-block">
                    <div class="db-label">📋 Purchase ID</div>
                    <div class="db-value" style="font-family:monospace;">{{ $quote->purchase_id }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">📍 3D Model</div>
                    <div class="db-value">{{ $quote->mapData->title ?? $quote->map_data_id }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">📅 Date Submitted</div>
                    <div class="db-value">{{ $quote->created_at->format('d M Y, h:i A') }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">🗂️ Output Formats</div>
                    <div class="db-value">
                      @foreach(is_array($quote->output_categories) ? $quote->output_categories : [] as $cat)
                        <span class="fmt-tag">{{ $cat }}</span>
                      @endforeach
                    </div>
                  </div>
                </div>

                <!-- Bank Payment Details (only when quoted/awaiting_payment/processing/completed) -->
                @if(in_array($quote->status, ['quoted','awaiting_payment','processing','completed']) && $quote->quoted_price)
                  <div class="bank-payment-box">
                    <h6><i class="bx bx-money"></i> Payment Details</h6>
                    <div class="bank-row">
                      <span class="bl">Quoted Price</span>
                      <span class="bv price">RM {{ number_format($quote->quoted_price, 2) }}</span>
                    </div>
                    @if($quote->bank_name)
                    <div class="bank-row">
                      <span class="bl">Bank Name</span>
                      <span class="bv">{{ $quote->bank_name }}</span>
                    </div>
                    @endif
                    @if($quote->bank_account_number)
                    <div class="bank-row">
                      <span class="bl">Account Number</span>
                      <span class="bv" style="font-family:monospace;">{{ $quote->bank_account_number }}</span>
                    </div>
                    @endif
                    @if($quote->bank_account_name)
                    <div class="bank-row">
                      <span class="bl">Account Holder</span>
                      <span class="bv">{{ $quote->bank_account_name }}</span>
                    </div>
                    @endif
                    @if($quote->payment_deadline)
                    <div class="bank-row">
                      <span class="bl">Payment Deadline</span>
                      <span class="bv deadline">{{ $quote->payment_deadline->format('d M Y') }}</span>
                    </div>
                    @endif
                    <div class="bank-row" style="padding-top:.75rem;">
                      <span class="bl">Status</span>
                      <span class="status-badge sb-{{ $quote->status }}">
                        <span class="dot"></span>
                        {{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quote->status] ?? ucfirst($quote->status) }}
                      </span>
                    </div>
                  </div>

                  @if(in_array($quote->status, ['quoted','awaiting_payment']))
                    <div class="alert alert-warning mt-3 mb-3 small" role="alert">
                      <i class="bx bx-info-circle me-1"></i>
                      <strong>Action required:</strong> Please transfer <strong>RM {{ number_format($quote->quoted_price, 2) }}</strong> to the bank account above
                      @if($quote->payment_deadline) before <strong>{{ $quote->payment_deadline->format('d M Y') }}</strong>@endif. Keep your payment receipt as proof.
                    </div>

                    <div class="alert alert-info p-3 mb-0 small" role="alert" style="background-color: rgba(105, 108, 255, 0.03); border: 1.5px solid rgba(105, 108, 255, 0.15); border-radius: 8px;">
                      <h6 class="alert-heading mb-2 fw-bold text-primary" style="display: flex; align-items: center; gap: 0.4rem; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 8px 0;">
                        <i class="bx bx-info-circle fs-5"></i> 3D Model Pricing Notice
                      </h6>
                      <ul class="ps-3 mb-0 text-muted" style="font-size: 11.5px; line-height: 1.5; list-style-type: decimal;">
                        <li class="mb-2">
                          <strong class="text-dark">Different Year/Capture Pricing:</strong> The same 3D model captured in different years will have different prices. A more recent capture is more expensive than older ones.
                        </li>
                        <li>
                          <strong class="text-dark">Custom/Larger Area Requests:</strong> You can request a 3D model area larger than the boundaries shown on the map. This custom service is more expensive because it requires deploying a drone to capture the area specifically for you.
                        </li>
                      </ul>
                    </div>
                  @endif
                @endif

                <!-- Rejection Reason -->
                @if($isRejected && $quote->rejection_reason)
                  <div class="rejection-box">
                    <div class="label">❌ Reason for Rejection:</div>
                    {{ $quote->rejection_reason }}
                  </div>
                @endif

                <!-- Admin Notes -->
                @if($quote->current_admin_note)
                  <div class="notes-box">
                    <div class="label">📝 Notes from our team ({{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quote->status] ?? $quote->status }}):</div>
                    {{ $quote->current_admin_note }}
                  </div>
                @endif

                @php
                  $allNotes = $quote->admin_notes ?: [];
                  $pastNotes = [];
                  if (is_array($allNotes)) {
                    foreach ($allNotes as $st => $noteText) {
                      if ($st !== $quote->status && !empty($noteText)) {
                        $pastNotes[$st] = $noteText;
                      }
                    }
                  }
                @endphp
                @if(!empty($pastNotes))
                  <div class="mt-2 small mb-3">
                    <a href="javascript:void(0);" onclick="togglePastNotes({{ $quote->id }})" class="text-primary fw-semibold" style="text-decoration: underline;">
                      <i class="bx bx-history me-1"></i>View notes history from previous statuses
                    </a>
                    <div id="pastNotes-{{ $quote->id }}" class="d-none mt-2 p-3 rounded" style="background-color: #f8fafc; border: 1.5px solid #e5e7eb;">
                      @foreach($pastNotes as $st => $noteText)
                        <div class="mb-2 last-mb-0 border-bottom pb-2 last-pb-0 last-border-0">
                          <strong class="text-dark d-block mb-1" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ \App\Models\PurchaseQuotation::STATUS_LABELS[$st] ?? ucfirst($st) }}:
                          </strong>
                          <div class="text-muted ps-2" style="font-size: 13px;">{{ $noteText }}</div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif

                <!-- Processing / Completed status info -->
                @if($quote->status === 'processing')
                  <div class="alert alert-info mt-3 mb-0 small">
                    <i class="bx bx-loader-alt bx-spin me-1"></i>
                    <strong>Your payment has been confirmed</strong> — our team is currently processing and preparing your 3D model data. You will be notified once it is ready for delivery.
                  </div>
                @elseif($quote->status === 'completed')
                  @if($quote->delivery_ready)
                    {{-- Delivery is ready — show download button --}}
                    <div class="mt-3 p-4 rounded-3" style="background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border: 2px solid #6ee7b7;">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:22px;">✅</span>
                        <div>
                          <div style="font-weight:700;color:#065f46;font-size:15px;">Your 3D model tiles are ready!</div>
                          <div style="font-size:12.5px;color:#047857;">Click the button below to download your 3D model tile files.</div>
                        </div>
                      </div>
                      @if($quote->delivered_at)
                        <div class="small text-muted mb-3"><i class="bx bx-calendar me-1"></i>Made available on {{ $quote->delivered_at->format('d M Y, h:i A') }}</div>
                      @endif
                      <button
                        type="button"
                        class="btn-download-tiles"
                        id="btnDownload-{{ $quote->id }}"
                        onclick="downloadTiles({{ $quote->id }}, '{{ $quote->purchase_id }}')"
                      >
                        <i class="bx bx-download" style="font-size:18px;"></i>
                        Download 3D Model Tiles
                      </button>
                    </div>
                  @else
                    {{-- Delivery not yet ready --}}
                    <div class="delivery-preparing mt-3">
                      <span class="spinner-border spinner-border-sm flex-shrink-0" style="color:#8b5cf6;"></span>
                      <div>
                        <div style="font-weight:700;">Preparing your 3D model tiles…</div>
                        <div style="font-size:12.5px;opacity:.8;">Our team is finalising the delivery package. This page will reflect the download link once it is ready.</div>
                      </div>
                    </div>
                  @endif
                @endif

              </div>
            </div>
          </div>
        @endforeach
      @endif

    </div>
  </div>

  <!-- Logout Confirm Modal -->
  <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Log out</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">Are you sure you want to log out?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="logoutConfirmBtn">Log out</button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  <script>
    // Initial state of quotations requiring polling (processing, or completed but not ready)
    var pollingQuotations = [
      @foreach($quotations as $quote)
        @if($quote->status === 'processing' || ($quote->status === 'completed' && !$quote->delivery_ready))
          {
            id: {{ $quote->id }},
            status: '{{ $quote->status }}',
            delivery_ready: {{ $quote->delivery_ready ? 'true' : 'false' }}
          },
        @endif
      @endforeach
    ];

    // Toggle expandable card
    function toggleCard(id) {
      var header = document.querySelector('#qcard-' + id + ' .q-card-header');
      var body   = document.getElementById('qbody-' + id);
      if (!header || !body) return;
      var isOpen = body.classList.contains('open');
      // Close all
      document.querySelectorAll('.q-card-header').forEach(function(h) { h.classList.remove('open'); });
      document.querySelectorAll('.q-card-body').forEach(function(b) { b.classList.remove('open'); });
      if (!isOpen) {
        header.classList.add('open');
        body.classList.add('open');
      }
    }

    // Toggle past status notes
    function togglePastNotes(id) {
      var el = document.getElementById('pastNotes-' + id);
      if (el) {
        el.classList.toggle('d-none');
      }
    }

    // Download 3D model tiles
    function downloadTiles(quotationId, purchaseId) {
      var btn = document.getElementById('btnDownload-' + quotationId);
      if (!btn) return;

      // Show loading state
      var origHTML = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Preparing Download\u2026';

      // Trigger download via a hidden anchor (preserves button feedback)
      var a = document.createElement('a');
      a.href = '/api/purchase-quotation/' + quotationId + '/download';
      a.style.display = 'none';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);

      // Re-enable button after delay (download starts in browser)
      setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = origHTML;
      }, 4000);
    }

    // Logout confirm
    (function() {
      var navLogoutBtn = document.getElementById('navLogoutBtn');
      if (navLogoutBtn) {
        navLogoutBtn.addEventListener('click', function() {
          new bootstrap.Modal(document.getElementById('logoutConfirmModal')).show();
          document.getElementById('logoutConfirmBtn').onclick = function() {
            document.querySelector('form[action*="logout"]').submit();
          };
        });
      }
    })();

    // Poll status of quotations that are processing or completed but not ready
    if (typeof pollingQuotations !== 'undefined' && pollingQuotations.length > 0) {
      var pollInterval = setInterval(function() {
        pollingQuotations.forEach(function(q) {
          fetch('/api/purchase-quotation/' + q.id + '/status')
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.success) {
                // If status changed or delivery is now ready, reload the page
                if (data.status !== q.status || data.delivery_ready !== q.delivery_ready) {
                  clearInterval(pollInterval);
                  window.location.reload();
                }
              }
            })
            .catch(function(err) {
              console.error('Error polling quotation ' + q.id + ':', err);
            });
        });
      }, 10000); // Check every 10 seconds
    }
  </script>
</body>
</html>
