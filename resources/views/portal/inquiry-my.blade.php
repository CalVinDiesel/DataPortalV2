<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>My Inquiries | 3DHub Data Portal</title>
  <meta name="description" content="View and track all your inquiry requests on the 3DHub Data Portal.">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/client-responsive.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">

  <!-- Helpers and front-config -->
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>

  <style>
    /* Dropdown hover behavior for desktop navigation links */
    @media (min-width: 1200px) {
      #navInquiry:hover .dropdown-menu,
      #navUpload:hover .dropdown-menu {
        display: block;
        margin-top: 0;
      }
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
    .pq-content { margin-top: -2.5rem; padding-bottom: 5rem; }

    /* Expandable list card design */
    .q-card {
      background: #fff;
      border: 1px solid var(--bs-border-color);
      border-radius: 12px;
      margin-bottom: 1rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.03);
      overflow: hidden;
      transition: box-shadow .2s;
    }
    .q-card:hover {
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .q-card-header {
      padding: 1.25rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .q-card-header .inquiry-id {
      font-family: monospace;
      font-weight: 700;
      font-size: 14px;
      color: #696cff;
      background: rgba(105,108,255,0.08);
      padding: .35rem .75rem;
      border-radius: 6px;
    }
    .q-card-header .meta {
      flex: 1;
      min-width: 200px;
    }
    .q-card-header .meta .model-name {
      font-weight: 700;
      font-size: 15px;
      color: var(--bs-heading-color);
    }
    .q-card-header .meta .date-info {
      font-size: 12px;
      color: var(--bs-secondary-color);
      margin-top: .15rem;
    }
    .q-card-header .chevron {
      font-size: 20px;
      transition: transform .25s ease-out;
      color: var(--bs-secondary-color);
    }
    .q-card-header.open .chevron {
      transform: rotate(180deg);
    }

    .q-card-body {
      max-height: 0;
      overflow: hidden;
      transition: max-height .3s ease-out;
    }
    .q-card-body.open {
      max-height: 1500px; /* high limit to accommodate content */
      border-top: 1px solid var(--bs-border-color);
    }
    .q-card-body-inner {
      padding: 1.5rem;
    }

    /* Formats tags */
    .fmt-tag {
      display: inline-block;
      background: rgba(105,108,255,0.06);
      color: #696cff;
      border: 1px solid rgba(105,108,255,0.15);
      border-radius: 20px;
      padding: .1rem .6rem;
      font-size: 11px;
      font-weight: 600;
      margin-right: .2rem;
    }

    /* Status Badges */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: .3rem;
      padding: .3rem .8rem;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
    }
    .status-badge .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
    }
    .sb-pending          { background: #fffbeb; color: #92400e; border: 1.5px solid #fcd34d; } .sb-pending .dot          { background: #f59e0b; }
    .sb-reviewed         { background: #f0f9ff; color: #0c4a6e; border: 1.5px solid #7dd3fc; } .sb-reviewed .dot         { background: #0ea5e9; }
    .sb-quoted           { background: #f0f0ff; color: #3730a3; border: 1.5px solid #c7d2fe; } .sb-quoted .dot           { background: #696cff; }
    .sb-awaiting_payment { background: #fff7ed; color: #7c2d12; border: 1.5px solid #fed7aa; } .sb-awaiting_payment .dot { background: #f97316; }
    .sb-processing       { background: #f5f3ff; color: #4c1d95; border: 1.5px solid #ddd6fe; } .sb-processing .dot       { background: #8b5cf6; }
    .sb-completed        { background: #f0fdf4; color: #065f46; border: 1.5px solid #6ee7b7; } .sb-completed .dot        { background: #10b981; }
    .sb-rejected         { background: #fef2f2; color: #7f1d1d; border: 1.5px solid #fca5a5; } .sb-rejected .dot         { background: #ef4444; }

    /* Info Grid inside card */
    .detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1.25rem;
      margin-bottom: 1.5rem;
    }
    .detail-block {
      background: var(--bs-tertiary-bg);
      padding: .85rem 1.1rem;
      border-radius: 8px;
    }
    .detail-block .db-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--bs-secondary-color);
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: .25rem;
    }
    .detail-block .db-value {
      font-size: 13.5px;
      font-weight: 600;
      color: var(--bs-heading-color);
    }

    /* Timeline Stepper */
    .status-timeline {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.75rem;
      padding: 0 .5rem;
      overflow-x: auto;
      gap: 1rem;
    }
    .tl-step {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      min-width: 80px;
    }
    .tl-step::before {
      content: '';
      position: absolute;
      top: 15px;
      left: -50%;
      right: 50%;
      height: 2px;
      background: var(--bs-border-color);
      z-index: 1;
    }
    .tl-step:first-child::before {
      display: none;
    }
    .tl-dot {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--bs-tertiary-bg);
      border: 2px solid var(--bs-border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 12px;
      color: var(--bs-secondary-color);
      z-index: 2;
    }
    .tl-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--bs-secondary-color);
      margin-top: .5rem;
      text-align: center;
      white-space: nowrap;
    }

    /* Timeline Active states */
    .tl-step.tl-done .tl-dot {
      background: #10b981;
      border-color: #10b981;
      color: #fff;
    }
    .tl-step.tl-done::before {
      background: #10b981;
    }
    .tl-step.tl-done .tl-label {
      color: #10b981;
    }

    .tl-step.tl-active .tl-dot {
      background: #696cff;
      border-color: #696cff;
      color: #fff;
      box-shadow: 0 0 0 5px rgba(105,108,255,0.15);
    }
    .tl-step.tl-active::before {
      background: #696cff;
    }
    .tl-step.tl-active .tl-label {
      color: #696cff;
    }

    /* Bank info box */
    .bank-payment-box {
      border: 1px solid var(--bs-border-color);
      border-radius: 10px;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
    }
    .bank-payment-box h6 {
      font-weight: 700;
      margin-bottom: .75rem;
      display: flex;
      align-items: center;
      gap: .4rem;
    }
    
    .btn-download-quotation {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: #0284c7;
      color: #fff !important;
      border: none;
      border-radius: 8px;
      padding: .6rem 1.4rem;
      font-weight: 700;
      font-size: 13.5px;
      text-decoration: none;
      box-shadow: 0 2px 4px rgba(2,132,199,0.25);
      transition: background .2s;
    }
    .btn-download-quotation:hover {
      background: #0369a1;
    }

    .btn-download-tiles {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: #10b981;
      color: #fff !important;
      border: none;
      border-radius: 8px;
      padding: .75rem 1.75rem;
      font-weight: 700;
      font-size: 14.5px;
      text-decoration: none;
      box-shadow: 0 3px 8px rgba(16,185,129,0.3);
      transition: transform .2s, background .2s;
    }
    .btn-download-tiles:hover {
      background: #059669;
      transform: translateY(-1px);
    }

    .notes-box, .rejection-box {
      background: var(--bs-tertiary-bg);
      border-left: 4px solid #696cff;
      border-radius: 0 8px 8px 0;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      font-size: 13.5px;
    }
    .rejection-box {
      border-left-color: #ef4444;
      background: rgba(239,68,68,0.02);
    }
    .notes-box .label, .rejection-box .label {
      font-weight: 700;
      margin-bottom: .25rem;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    .delivery-preparing {
      display: flex;
      align-items: center;
      gap: .75rem;
      background: rgba(139,92,246,0.05);
      border: 1px solid rgba(139,92,246,0.2);
      border-radius: 10px;
      padding: 1.25rem;
      color: #7c3aed;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
    }
    .empty-state .icon {
      font-size: 4rem;
      color: var(--bs-secondary-color);
      margin-bottom: 1rem;
      opacity: .5;
    }
    .empty-state h5 {
      font-weight: 700;
      margin-bottom: .5rem;
    }
    .empty-state p {
      color: var(--bs-secondary-color);
      margin-bottom: 1.5rem;
    }

    /* ── Navbar Contrast & Unified Color Fix ── */
    .landing-navbar .app-brand-text {
      color: rgba(255, 255, 255, 0.95) !important;
    }
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
    .landing-navbar #nav-theme {
      color: rgba(255, 255, 255, 0.85) !important;
    }
    .landing-navbar #nav-theme:hover {
      color: #cbd5ff !important;
    }
    #navUserWrap .navbar-text,
    .landing-navbar .navbar-text {
      color: rgba(255, 255, 255, 0.85) !important;
      transition: color 0.25s ease;
    }
    #navUserWrap .navbar-text:hover,
    .landing-navbar .navbar-text:hover {
      color: #cbd5ff !important;
    }
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
    .landing-navbar .navbar-toggler i {
      color: rgba(255, 255, 255, 0.9) !important;
    }
  </style>
</head>
<body>

  <!-- Navbar: Start -->
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-xl landing-navbar px-3 px-md-8">
        <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
          <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <i class="icon-base bx bx-menu icon-lg align-middle text-heading fw-medium"></i>
          </button>
          <a href="{{ route('landing') }}" class="app-brand-link">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub Logo" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;" />
            <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub Beta</span>
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
            <li class="nav-item dropdown d-none d-xl-block" id="navInquiry">
              <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" data-bs-toggle="dropdown">Inquiry</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('inquiry.new') }}">New Inquiry</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item fw-bold text-primary" href="{{ route('inquiry.my') }}">My Inquiry</a></li>
              </ul>
            </li>
            <li class="nav-item d-xl-none">
              <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navInquiryCollapse">Inquiry</a>
              <div class="collapse nav-upload-mobile-sub" id="navInquiryCollapse">
                <a class="nav-link fw-medium" href="{{ route('inquiry.new') }}">New Inquiry</a>
                <hr class="dropdown-divider">
                <a class="nav-link fw-medium" href="{{ route('inquiry.my') }}">My Inquiry</a>
              </div>
            </li>
            <!-- Upload Dropdown for Desktop -->
            <li class="nav-item dropdown d-none d-xl-block" id="navUpload">
              <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" aria-expanded="false" data-bs-toggle="dropdown" data-trigger="hover">
                Upload
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('create_project') }}">New Project</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('my_uploads') }}">My Projects</a></li>
              </ul>
            </li>
            <!-- Upload Dropdown for Mobile -->
            <li class="nav-item d-xl-none navUpload-mobile">
              <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navUploadCollapse" aria-expanded="false" aria-controls="navUploadCollapse" id="navUploadMobileToggle">
                Upload
              </a>
              <div class="collapse nav-upload-mobile-sub" id="navUploadCollapse">
                <a class="nav-link fw-medium" href="{{ route('create_project') }}">New Project</a>
                <hr class="dropdown-divider">
                <a class="nav-link fw-medium" href="{{ route('my_uploads') }}">My Projects</a>
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
          <h1><i class="bx bx-receipt me-2"></i>My Inquiries</h1>
          <p>Track all your 3D model data inquiry requests and their current status</p>
        </div>
        <a href="{{ route('inquiry.new') }}" class="btn-new">
          <i class="bx bx-plus"></i> New Inquiry
        </a>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="pq-content">
    <div class="container">

      @php
        $statusOrder = ['pending','reviewed','processing','completed'];
        $statusLabels = [
          'pending'          => 'Pending Review',
          'reviewed'         => 'Under Review',
          'processing'       => 'Processing',
          'completed'        => 'Completed',
        ];
        $statusIcons = [
          'pending'          => '⏳',
          'reviewed'         => '🔍',
          'processing'       => '⚙️',
          'completed'        => '✅',
        ];
      @endphp

      @if($inquiries->isEmpty())
        <div class="card shadow-sm">
          <div class="card-body empty-state">
            <i class="bx bx-file-blank icon"></i>
            <h5>No inquiries yet</h5>
            <p>You have not submitted any inquiry requests.<br>Get started by creating your first one.</p>
            <a href="{{ route('inquiry.new') }}" class="btn btn-primary px-4"><i class="bx bx-plus me-1"></i>New Inquiry</a>
          </div>
        </div>
      @else
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <p class="text-muted mb-0 small">Showing <strong>{{ $inquiries->count() }}</strong> inquir{{ $inquiries->count() !== 1 ? 'ies' : 'y' }} — click any row to view details</p>
        </div>

        @foreach($inquiries as $inquiry)
          @php
            $isRejected = $inquiry->status === 'rejected';
            $currentIdx = $isRejected ? -1 : array_search($inquiry->status, $statusOrder);
            if ($currentIdx === false && !$isRejected) {
                $currentIdx = 1; // Default fallback to Under Review
            }
          @endphp

          <div class="q-card" id="qcard-{{ $inquiry->id }}">
            <!-- Card Header (clickable) -->
            <div class="q-card-header" onclick="toggleCard({{ $inquiry->id }})">
              <div>
                <span class="purchase-id">{{ $inquiry->inquiry_id }}</span>
              </div>
              <div class="meta">
                <div class="model-name">{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</div>
                <div class="date-info">
                  <i class="bx bx-calendar me-1"></i>{{ $inquiry->created_at->format('d M Y, h:i A') }}
                  &nbsp;·&nbsp;
                  @foreach(is_array($inquiry->output_categories) ? $inquiry->output_categories : [] as $cat)
                    <span class="fmt-tag">{{ $cat }}</span>
                  @endforeach
                </div>
              </div>
              <div>
                <span class="status-badge sb-{{ $inquiry->status }}">
                  <span class="dot"></span>
                  {{ \App\Models\Inquiry::STATUS_LABELS[$inquiry->status] ?? ucfirst($inquiry->status) }}
                </span>
              </div>
              <i class="bx bx-chevron-down chevron"></i>
            </div>

            <!-- Card Body (expandable) -->
            <div class="q-card-body" id="qbody-{{ $inquiry->id }}">
              <div class="q-card-body-inner">

                <!-- Status Timeline -->
                @if(!$isRejected)
                  <div class="status-timeline">
                    @foreach($statusOrder as $si => $step)
                      @php
                        if ($currentIdx === -1) {
                          $cls = 'tl-pending';
                        } elseif ($inquiry->status === 'completed') {
                          $cls = 'tl-done';
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
                          @if($inquiry->status === 'completed' || $si < $currentIdx) <i class="bx bx-check" style="font-size:14px;"></i>
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
                      <div style="font-weight:700;color:#7f1d1d;">This inquiry has been rejected</div>
                      <div style="font-size:13px;color:#991b1b;">Please contact us if you have any questions</div>
                    </div>
                  </div>
                @endif

                <!-- Info Grid -->
                <div class="detail-grid">
                  <div class="detail-block">
                    <div class="db-label">📋 Inquiry ID</div>
                    <div class="db-value" style="font-family:monospace;">{{ $inquiry->inquiry_id }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">📍 3D Model</div>
                    <div class="db-value">{{ $inquiry->mapData->title ?? $inquiry->map_data_id }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">📅 Date Submitted</div>
                    <div class="db-value">{{ $inquiry->created_at->format('d M Y, h:i A') }}</div>
                  </div>
                  <div class="detail-block">
                    <div class="db-label">🗂️ Output Formats</div>
                    <div class="db-value">
                      @foreach(is_array($inquiry->output_categories) ? $inquiry->output_categories : [] as $cat)
                        <span class="fmt-tag">{{ $cat }}</span>
                      @endforeach
                    </div>
                  </div>
                </div>

                <!-- Quotation PDF Download -->
                @if(in_array($inquiry->status, ['quoted','awaiting_payment','processing','completed']) && $inquiry->quotation_pdf_path)
                  <div class="bank-payment-box" style="background: linear-gradient(135deg, #f0f7ff, #e0f2fe); border-color: #7dd3fc;">
                    <h6 style="color: #0369a1;"><i class="bx bxs-file-pdf"></i> Quotation Details</h6>
                    <p class="small text-muted mb-3">Your formal quotation PDF is ready for review. Please download it to view price details, bank details, and payment instructions.</p>
                    
                    <a
                      href="{{ route('inquiry.pdf', $inquiry->id) }}"
                      class="btn-download-quotation"
                      target="_blank"
                    >
                      <i class="bx bx-download" style="font-size:18px;"></i>
                      Download Quotation PDF
                    </a>
                  </div>

                  @if(in_array($inquiry->status, ['quoted','awaiting_payment']))
                    <div class="alert alert-warning mt-3 mb-3 small" role="alert">
                      <i class="bx bx-info-circle me-1"></i>
                      <strong>Action required:</strong> Please transfer the quoted price as specified in the PDF to the bank account listed inside. Upload your payment receipt to confirm your order.
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

                  @if($inquiry->status === 'awaiting_payment')
                    <!-- Payment Receipt Upload form -->
                    <div class="mt-3 p-3 rounded-3 border" style="background-color: #fafafa;">
                      <h6 class="fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px; font-size:12px;"><i class="bx bx-receipt text-primary me-1"></i> Upload Payment Receipt</h6>
                      
                      @if($inquiry->payment_receipt_path)
                        <!-- If receipt already uploaded -->
                        <div class="alert alert-success py-2 px-3 mb-2 small d-flex align-items-center justify-content-between">
                          <span><i class="bx bx-check-circle me-1"></i> Receipt uploaded successfully. Waiting for verification.</span>
                          <a href="{{ route('inquiry.receipt', $inquiry->id) }}" target="_blank" class="btn btn-xs btn-outline-success">
                            <i class="bx bx-download me-1"></i> View Receipt
                          </a>
                        </div>
                      @endif

                      <form action="javascript:void(0);" id="uploadReceiptForm-{{ $inquiry->id }}" onsubmit="uploadReceipt({{ $inquiry->id }}, this)">
                        @csrf
                        <div class="input-group input-group-sm">
                          <input type="file" name="payment_receipt" class="form-control" accept="image/*,application/pdf" required>
                          <button class="btn btn-primary" type="submit" id="btnUploadReceipt-{{ $inquiry->id }}">
                            <i class="bx bx-upload me-1"></i> {{ $inquiry->payment_receipt_path ? 'Replace Receipt' : 'Upload Receipt' }}
                          </button>
                        </div>
                        <div class="form-text small" style="font-size: 11px;">Supported formats: PDF, JPG, JPEG, PNG (max 20MB)</div>
                        <div class="mt-2 text-danger small d-none" id="errorReceipt-{{ $inquiry->id }}"></div>
                      </form>
                    </div>
                  @elseif($inquiry->payment_receipt_path)
                    <!-- If status is not awaiting_payment but receipt exists, allow viewing it -->
                    <div class="mt-3 p-3 rounded-3 border" style="background-color: #fafafa;">
                      <div class="small d-flex align-items-center justify-content-between">
                        <span><i class="bx bx-check-circle text-success me-1"></i> Payment Receipt:</span>
                        <a href="{{ route('inquiry.receipt', $inquiry->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                          <i class="bx bx-download me-1"></i> View Uploaded Receipt
                        </a>
                      </div>
                    </div>
                  @endif
                @endif

                <!-- Rejection Reason -->
                @if($isRejected && $inquiry->rejection_reason)
                  <div class="rejection-box">
                    <div class="label">❌ Reason for Rejection:</div>
                    {{ $inquiry->rejection_reason }}
                  </div>
                @endif

                <!-- Admin Notes -->
                @if($inquiry->current_admin_note)
                  <div class="notes-box">
                    <div class="label">📝 Notes from our team ({{ \App\Models\Inquiry::STATUS_LABELS[$inquiry->status] ?? $inquiry->status }}):</div>
                    {{ $inquiry->current_admin_note }}
                  </div>
                @endif

                <!-- Processing / Completed status info -->
                @if($inquiry->status === 'processing')
                  <div class="alert alert-info mt-3 mb-0 small">
                    <i class="bx bx-loader-alt bx-spin me-1"></i>
                    <strong>Your request is being processed</strong> — our team is currently processing and preparing your 3D model data. You will be notified once it is ready for delivery.
                  </div>
                @elseif($inquiry->status === 'completed')
                  @if($inquiry->delivery_ready)
                    <div class="mt-3 p-4 rounded-3" style="background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border: 2px solid #6ee7b7;">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:22px;">✅</span>
                        <div>
                          <div style="font-weight:700;color:#065f46;font-size:15px;">Your 3D model tiles are ready!</div>
                          <div style="font-size:12.5px;color:#047857;">Click the button below to download your 3D model tile files.</div>
                        </div>
                      </div>
                      @if($inquiry->delivered_at)
                        <div class="small text-muted mb-3"><i class="bx bx-calendar me-1"></i>Made available on {{ $inquiry->delivered_at->format('d M Y, h:i A') }}</div>
                      @endif
                      <button
                        type="button"
                        class="btn-download-tiles"
                        id="btnDownload-{{ $inquiry->id }}"
                        onclick="initiateDownloadWithDisclaimer({{ $inquiry->id }})"
                      >
                        <i class="bx bx-download" style="font-size:18px;"></i>
                        Download 3D Model Tiles
                      </button>
                    </div>
                  @else
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

  <!-- 3D Data Download Disclaimer Modal -->
  <div class="modal fade" id="disclaimerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header py-3 bg-light">
          <h5 class="modal-title fw-bold text-dark"><i class="bx bx-shield-quarter me-2 text-primary"></i> 3D Data Download & Usage Disclaimer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 p-md-5">
          <div class="disclaimer-content mb-4 p-3 border rounded bg-lighter" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem; line-height: 1.6;">
            <p class="fw-bold text-danger">IMPORTANT: PLEASE READ CAREFULLY BEFORE DOWNLOADING.</p>
            <p>By clicking "<strong>I AGREE</strong>" or downloading the 3D processed model (the "Data"), you acknowledge that you have read, understood, and agreed to be bound by the following terms:</p>
            
            <h6 class="fw-bold mt-4">1. For Reference Purposes Only</h6>
            <p>This 3D processed model is provided solely for <strong>self-referencing and informational purposes</strong>. It is a digital representation generated through automated processing techniques and may not reflect the actual, physical dimensions, specifications, or conditions of the subject with absolute precision.</p>

            <h6 class="fw-bold mt-4">2. No Warranty of Accuracy</h6>
            <p>The Data is provided on an <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> basis. <strong>3D Hub</strong> makes no representations or warranties of any kind, express or implied, regarding the accuracy, completeness, reliability, or suitability of the 3D model. Users are warned that digital artifacts, interpolation errors, or processing limitations may exist.</p>

            <h6 class="fw-bold mt-4">3. Limitation of Liability</h6>
            <p>In no event shall <strong>3D Hub</strong> or its parent company be held liable for any direct, indirect, incidental, special, or consequential <strong>losses or damages</strong> (including, but not limited to, financial loss, personal injury, or property damage) arising out of the use, inability to use, or reliance upon this 3D model.</p>

            <h6 class="fw-bold mt-4">4. User Responsibility</h6>
            <p>The user assumes <strong>full responsibility</strong> for verifying the accuracy of any measurements, designs, or calculations derived from this Data. If the Data is to be used for construction, manufacturing, or professional engineering, the user is advised to perform independent field verification.</p>

            <h6 class="fw-bold mt-4">5. No Modification or Redistribution</h6>
            <p>Unless otherwise stated, this Data is intended for the recipient’s personal or internal use only. Unauthorized redistribution, modification, or commercial resale of the processed model is strictly prohibited.</p>
            
            <hr>
            <p class="small text-muted italic">Note: This record of agreement will be logged with your User ID and IP address for compliance purposes.</p>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="disclaimerCheckbox" onchange="toggleDisclaimerBtn()">
            <label class="form-check-label fw-bold text-dark" for="disclaimerCheckbox">
              I have read and agree to the 3D Data Disclaimer
            </label>
          </div>
          
          <input type="hidden" id="disclaimerQuotationId">
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary px-5 fw-bold" id="agreeDownloadBtn" disabled onclick="handleDisclaimerAgreement()">
            <i class="bx bx-download me-1"></i> I Agree & Download
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  <script>
    var pollingQuotations = [
      @foreach($inquiries as $inquiry)
        @if($inquiry->status === 'processing' || ($inquiry->status === 'completed' && !$inquiry->delivery_ready))
          {
            id: {{ $inquiry->id }},
            status: '{{ $inquiry->status }}',
            delivery_ready: {{ $inquiry->delivery_ready ? 'true' : 'false' }}
          },
        @endif
      @endforeach
    ];

    function toggleCard(id) {
      var header = document.querySelector('#qcard-' + id + ' .q-card-header');
      var body   = document.getElementById('qbody-' + id);
      if (!header || !body) return;
      var isOpen = body.classList.contains('open');
      document.querySelectorAll('.q-card-header').forEach(function(h) { h.classList.remove('open'); });
      document.querySelectorAll('.q-card-body').forEach(function(b) { b.classList.remove('open'); });
      if (!isOpen) {
        header.classList.add('open');
        body.classList.add('open');
      }
    }

    function initiateDownloadWithDisclaimer(inquiryId) {
      document.getElementById('disclaimerQuotationId').value = inquiryId;
      
      const agreeBtn = document.getElementById('agreeDownloadBtn');
      document.getElementById('disclaimerCheckbox').checked = false;
      agreeBtn.disabled = true;
      agreeBtn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';
      
      const modal = new bootstrap.Modal(document.getElementById('disclaimerModal'));
      modal.show();
    }

    function toggleDisclaimerBtn() {
      const checkbox = document.getElementById('disclaimerCheckbox');
      document.getElementById('agreeDownloadBtn').disabled = !checkbox.checked;
    }

    function handleDisclaimerAgreement() {
      const inquiryId = document.getElementById('disclaimerQuotationId').value;
      const btn = document.getElementById('agreeDownloadBtn');
      
      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Logging Agreement...';

      fetch('/api/inquiry/' + inquiryId + '/accept-disclaimer', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ agreed: true })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          btn.disabled = true;
          btn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';

          const modalEl = document.getElementById('disclaimerModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
          
          executeDownloadTiles(inquiryId);
        } else {
          alert('Could not log your agreement. Please try again.');
          btn.disabled = false;
          btn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';
        }
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred. Please refresh the page.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';
      });
    }

    function executeDownloadTiles(inquiryId) {
      var btn = document.getElementById('btnDownload-' + inquiryId);
      if (!btn) return;

      var origHTML = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Preparing Download\u2026';

      var a = document.createElement('a');
      a.href = '/api/inquiry/' + inquiryId + '/download';
      a.style.display = 'none';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);

      setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = origHTML;
      }, 4000);
    }

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

    if (typeof pollingQuotations !== 'undefined' && pollingQuotations.length > 0) {
      var pollInterval = setInterval(function() {
        pollingQuotations.forEach(function(q) {
          fetch('/api/inquiry/' + q.id + '/status')
            .then(function(res) { return res.json(); })
            .then(function(data) {
              if (data.success) {
                if (data.status !== q.status || data.delivery_ready !== q.delivery_ready) {
                  clearInterval(pollInterval);
                  window.location.reload();
                }
              }
            })
            .catch(function(err) {
              console.error('Error polling inquiry ' + q.id + ':', err);
            });
        });
      }, 10000);
    }
    function uploadReceipt(id, form) {
      var btn = document.getElementById('btnUploadReceipt-' + id);
      var err = document.getElementById('errorReceipt-' + id);
      if (!btn || !err) return;

      var fileInput = form.querySelector('input[type="file"]');
      if (!fileInput || fileInput.files.length === 0) return;

      btn.disabled = true;
      var origHTML = btn.innerHTML;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading\u2026';
      err.classList.add('d-none');

      var fd = new FormData(form);

      fetch('/api/inquiry/' + id + '/payment-receipt', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.success) {
          window.location.reload();
        } else {
          err.textContent = data.message || 'Failed to upload receipt.';
          err.classList.remove('d-none');
          btn.disabled = false;
          btn.innerHTML = origHTML;
        }
      })
      .catch(function(error) {
        console.error('Error uploading receipt:', error);
        err.textContent = 'Network error occurred. Please try again.';
        err.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = origHTML;
      });
    }
  </script>
</body>
</html>
