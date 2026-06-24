<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>New Purchase Quotation | 3DHub Data Portal</title>
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

  <!-- CesiumJS CSS & JS -->
  <link href="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Widgets/widgets.css" rel="stylesheet" />
  <script src="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Cesium.js"></script>

  <link rel="stylesheet" href="{{ asset('assets') }}/css/cesium-map.css">

  <!-- Helpers and front-config -->
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>

  <style>
    /* Dropdown hover behavior for desktop navigation links */
    @media (min-width: 1200px) {
      #navPurchaseQuotation:hover .dropdown-menu,
      #navUpload:hover .dropdown-menu {
        display: block;
        margin-top: 0;
      }
    }
    
    #heroMapContainer {
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--bs-border-color);
    }
    #cesiumContainer {
      height: 420px;
      width: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    .form-section-title {
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--bs-secondary-color);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 1.25rem;
      margin-top: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .form-section-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--bs-border-color);
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

  <!-- Navbar: Start -->
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-xl landing-navbar px-3 px-md-8">
        <!-- Menu logo wrapper: Start -->
        <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
          <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="icon-base bx bx-menu icon-lg align-middle text-heading fw-medium"></i>
          </button>
          <a href="{{ route('landing') }}" class="app-brand-link">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub Logo" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;" />
            <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub</span>
          </a>
        </div>
        <!-- Menu logo wrapper: End -->

        <!-- Menu wrapper: Start -->
        <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
          <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="icon-base bx bx-x icon-lg"></i>
          </button>
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link fw-medium" href="{{ route('landing') }}#landingHero">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fw-medium" href="{{ route('landing') }}#landingShowCase">ShowCase</a>
            </li>
            @auth
            <!-- PurchaseQuotation Dropdown for Desktop -->
            <li class="nav-item dropdown d-none d-xl-block" id="navPurchaseQuotation">
              <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" aria-expanded="false" data-bs-toggle="dropdown" data-trigger="hover">
                PurchaseQuotation
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('purchase_quotation.new') }}">New PurchaseQuotation</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('purchase_quotation.my') }}">My PurchaseQuotation</a></li>
              </ul>
            </li>
            <!-- PurchaseQuotation Dropdown for Mobile -->
            <li class="nav-item d-xl-none navPurchaseQuotation-mobile">
              <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navPurchaseQuotationCollapse" aria-expanded="false" aria-controls="navPurchaseQuotationCollapse" id="navPurchaseQuotationMobileToggle">
                PurchaseQuotation
              </a>
              <div class="collapse nav-upload-mobile-sub" id="navPurchaseQuotationCollapse">
                <a class="nav-link fw-medium" href="{{ route('purchase_quotation.new') }}">New PurchaseQuotation</a>
                <hr class="dropdown-divider">
                <a class="nav-link fw-medium" href="{{ route('purchase_quotation.my') }}">My PurchaseQuotation</a>
              </div>
            </li>
            @endauth
            <li class="nav-item">
              <a class="nav-link fw-medium" href="{{ route('landing') }}#landingFAQ">FAQ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link fw-medium" href="{{ route('landing') }}#landingContact">Contact us</a>
            </li>
          </ul>
        </div>
        <div class="landing-menu-overlay d-xl-none"></div>
        <!-- Menu wrapper: End -->

        <!-- Toolbar: Start -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">
          <!-- Style Switcher -->
          <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Toggle theme">
              <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
              <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
              <li>
                <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light">
                  <span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark">
                  <span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system">
                  <span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span>
                </button>
              </li>
            </ul>
          </li>
          <!-- / Style Switcher -->

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
        <!-- Toolbar: End -->
      </div>
    </div>
  </nav>
  <!-- Navbar: End -->

  <!-- Hero -->
  <div class="pq-hero">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1">
        <div>
          <h1><i class="bx bx-file-blank me-2"></i>New Purchase Quotation</h1>
          <p>Specify your required output formats and select your purchase area on the map</p>
        </div>
        <a href="{{ route('purchase_quotation.my') }}" class="btn-new">
          <i class="bx bx-list-ul"></i> My Quotations
        </a>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="pq-content">
    <div class="container">
      <div class="card shadow-sm border">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-4 px-4 px-md-5">
          <h4 class="m-0 fw-bold"><i class="bx bx-file-blank me-2 text-primary"></i>Send Purchase Quotation</h4>
          <span class="badge bg-label-primary">New Quotation</span>
        </div>
        <div class="card-body p-4 p-md-5">
          
          <div id="successAlert" class="alert alert-success d-none mb-4" role="alert">
            <div class="d-flex">
              <i class="bx bx-check-circle me-2 fs-4"></i>
              <div>
                <h6 class="alert-heading mb-1 fw-bold">Quotation Sent Successfully!</h6>
                <span>Your purchase quotation request has been recorded. We will review the details and get back to you shortly.</span>
              </div>
            </div>
          </div>

          <form id="purchaseQuoteForm" novalidate>
            <div class="row">
              <!-- Left Form Column -->
              <div class="col-lg-5">
                <div class="form-section-title mt-0">Quotation Details</div>
                
                <!-- Field 1: Auto Generated Purchase ID -->
                <div class="mb-4">
                  <label class="form-label fw-semibold" for="purchase_id">Purchase ID</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-hash"></i></span>
                    <input type="text" id="purchase_id" name="purchase_id" class="form-control fw-bold text-primary" value="{{ $purchaseId }}" readonly style="background-color: var(--bs-tertiary-bg);">
                  </div>
                  <div class="form-text">This unique ID is auto-generated and will be saved upon submission.</div>
                </div>

                <!-- Field 2: Output Categories (The 5 3D model formats) -->
                <div class="mb-4">
                  <label class="form-label fw-semibold d-block">Required Output Formats <span class="text-danger">*</span></label>
                  <div class="form-text mb-2">Select the 3D model formats you would like to include in your quotation.</div>
                  
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cat3dTiles" value="3D Tiles" checked disabled>
                    <label class="form-check-label fw-medium" for="cat3dTiles">3D Tiles (Default)</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="catOSGB" value="OSGB" checked disabled>
                    <label class="form-check-label fw-medium" for="catOSGB">OSGB (Default)</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="catDSM" value="DSM">
                    <label class="form-check-label" for="catDSM">DSM</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="cat3DGS" value="3DGS">
                    <label class="form-check-label" for="cat3DGS">3DGS</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="catOrthophoto" value="Orthophoto">
                    <label class="form-check-label" for="catOrthophoto">Orthophoto</label>
                  </div>
                </div>
              </div>

              <!-- Right Map Column -->
              <div class="col-lg-7">
                <div class="form-section-title mt-0 mt-lg-0">Select Purchase Area</div>
                <div class="form-text mb-2">Use the Cesium map viewer to specify the area coordinates for your purchase quotation.</div>
                
                <!-- Field 3: Cesium Ion Map -->
                <div id="heroMapContainer" style="position: relative;">
                  <div id="cesiumContainer"></div>
                  <!-- Location choice bar: appears on pin hover, image + description per location -->
                  <div id="locationChoiceBar" class="location-choice-bar" aria-hidden="true">
                    <div class="location-choice-bar-inner">
                      <div class="location-choice-bar-cards" id="locationChoiceBarCards"></div>
                    </div>
                  </div>
                  <!-- Drawing Toolbar (hidden in 2D mode, shown in 3D mode) -->
                  <div id="drawingToolbar" style="position: absolute; top: 12px; left: 12px; z-index: 1000; display: none; gap: 8px;">
                    <button type="button" id="btnDrawPolygon" class="btn btn-sm btn-primary shadow-sm fw-bold d-flex align-items-center gap-1" style="border-radius: 8px; padding: 8px 14px; backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                      <i class="bx bx-pencil me-1"></i> Draw Purchase Area
                    </button>
                    <button type="button" id="btnClearPolygon" class="btn btn-sm btn-danger shadow-sm fw-bold d-flex align-items-center gap-1" style="border-radius: 8px; padding: 8px 14px; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                      <i class="bx bx-trash me-1"></i> Clear Area
                    </button>
                  </div>
                  <!-- Map control sidebar (zoom, reset, fullscreen) -->
                  <div class="right-controls">
                    <div class="navigation-container"></div>
                    <div id="controls">
                      <div id="zoom-item" class="scale-item">
                        <div class="el-tooltip__trigger" id="purchaseResetViewBtn" title="Reset View">
                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.75 2.5H17.5V6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M17.5 13.75V17.5H13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M6.25 17.5H2.5V13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M2.5 6.25V2.5H6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                          </svg>
                        </div>
                        <div class="el-tooltip__trigger" id="zoomInBtn" title="Zoom In">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.0208 11.0782L14.8762 13.9328L13.9328 14.8762L11.0782 12.0208C10.016 12.8723 8.69483 13.3354 7.3335 13.3335C4.0215 13.3335 1.3335 10.6455 1.3335 7.3335C1.3335 4.0215 4.0215 1.3335 7.3335 1.3335C10.6455 1.3335 13.3335 4.0215 13.3335 7.3335C13.3354 8.69483 12.8723 10.016 12.0208 11.0782ZM10.6835 10.5835C11.5296 9.71342 12.0021 8.54712 12.0002 7.3335C12.0002 4.75483 9.9115 2.66683 7.3335 2.66683C4.75483 2.66683 2.66683 4.75483 2.66683 7.3335C2.66683 9.9115 4.75483 12.0002 7.3335 12.0002C8.54712 12.0021 9.71342 11.5296 10.5835 10.6835L10.6835 10.5835ZM6.66683 6.66683V4.66683H8.00016V6.66683H10.0002V8.00016H8.00016V10.0002H6.66683V8.00016H4.66683V6.66683H6.66683Z" fill="currentColor"></path>
                          </svg>
                        </div>
                        <div class="el-tooltip__trigger" id="zoomOutBtn" title="Zoom Out">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.0208 11.0782L14.8762 13.9328L13.9328 14.8762L11.0782 12.0208C10.016 12.8723 8.69483 13.3354 7.3335 13.3335C4.0215 13.3335 1.3335 10.6455 1.3335 7.3335C1.3335 4.0215 4.0215 1.3335 7.3335 1.3335C10.6455 1.3335 13.3335 4.0215 13.3335 7.3335C13.3354 8.69483 12.8723 10.016 12.0208 11.0782ZM10.6835 10.5835C11.5296 9.71342 12.0021 8.54712 12.0002 7.3335C12.0002 4.75483 9.9115 2.66683 7.3335 2.66683C4.75483 2.66683 2.66683 4.75483 2.66683 7.3335C2.66683 9.9115 4.75483 12.0002 7.3335 12.0002C8.54712 12.0021 9.71342 11.5296 10.5835 10.6835L10.6835 10.5835ZM4.66683 6.66683H10.0002V8.00016H4.66683V6.66683Z" fill="currentColor"></path>
                          </svg>
                        </div>
                        <div class="el-tooltip__trigger" id="orbit3dBtn" title="Orbit 180°" style="display: none; align-items: center; justify-content: center;">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4V1L8 5L12 9V6C16.42 6 20 9.58 20 14C20 18.42 16.42 22 12 22C7.58 22 4 18.42 4 14H2C2 19.52 6.48 24 12 24C17.52 24 22 19.52 22 14C22 8.48 17.52 4 12 4Z" fill="currentColor"/>
                          </svg>
                        </div>
                        <div class="divider"></div>
                        <div class="el-tooltip__trigger" id="fullscreenBtn" title="Fullscreen">
                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.75 2.5H17.5V6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M17.5 13.75V17.5H13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M6.25 17.5H2.5V13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M2.5 6.25V2.5H6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer Action Row -->
            <div class="row mt-4">
              <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="{{ route('landing') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <!-- Send Purchase Quotation Button on the bottom right -->
                <button type="submit" id="btnSubmitQuotation" class="btn btn-primary px-5 fw-bold">
                  <i class="bx bx-send me-1"></i> Send Purchase Quotation
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm logout modal -->
  <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Log out</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="logoutConfirmMessage">Are you sure you want to log out?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="logoutConfirmBtn">Log out</button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>

  <!-- Initialize Cesium map using common script files (matching overview map exactly) -->
  <script src="{{ asset('assets') }}/js/cesium-map.js?v={{ time() }}"></script>
  <script src="{{ asset('assets') }}/js/cesium-map-controls.js?v={{ time() }}"></script>
  <script>
    (function() {
      // Force initialization of Cesium map if it hasn't run yet (resolves timing issues with window load event)
      if (typeof initializeCesium === 'function' && !window.cesiumViewer) {
        try {
          initializeCesium();
        } catch (e) {
          console.error("Manual Cesium initialization failed:", e);
        }
      }

      // Helper function to poll/wait for cesiumViewer to be defined by cesium-map.js
      function getViewer(cb) {
        if (window.cesiumViewer) {
          cb(window.cesiumViewer);
          return;
        }
        var attempts = 0;
        var t = setInterval(function () {
          attempts++;
          if (window.cesiumViewer) {
            clearInterval(t);
            cb(window.cesiumViewer);
            return;
          }
          if (attempts > 100) clearInterval(t);
        }, 50);
      }

      // State shared across map logic and form submission
      var selectedModel = null; // Currently selected MapData item
      var dataSource = null; // Cesium CustomDataSource for pins/clusters

      // Drawing state
      var isDrawing = false;
      var polygonPoints = []; // Array of C.Cartesian3 positions
      var drawingEntities = []; // Array of C.Entity (temp vertices, lines, preview polygon)
      var finalPolygonEntity = null; // The confirmed polygon entity
      var editVertexEntities = []; // Array of C.Entity for vertex grab handles
      var activeDrawingPreview = null; // The CallbackProperty preview entity
      var mousePosition = null; // Current mouse Cartesian3
      
      var drawingHandler = null; // ScreenSpaceEventHandler for drawing
      var editHandler = null; // ScreenSpaceEventHandler for editing (dragging vertices)
      var draggedVertexIndex = null; // Index of currently dragged vertex
      var draggedVertexEntity = null; // Entity of currently dragged vertex

      getViewer(function(viewer) {
        var C = Cesium;
        
        function projectCartesian(scene, position) {
          if (!scene || !position) return null;
          try {
            if (typeof scene.cartesianToCanvasCoordinates === 'function') {
              var res = scene.cartesianToCanvasCoordinates(position);
              if (res && typeof res.x === 'number') return res;
            }
          } catch (e) {}
          try {
            var res = C.SceneTransforms.wgs84ToWindowCoordinates(scene, position);
            if (res && typeof res.x === 'number') return res;
          } catch (e) {}
          try {
            var res = C.SceneTransforms.worldToWindowCoordinates(scene, position);
            if (res && typeof res.x === 'number') return res;
          } catch (e) {}
          return null;
        }
        
        // Helper function to generate a premium bordered square pin dynamically, falling back to a CSS styled pin
        function makePinImage(imageUrl, size, border, title, callback) {
          var abbreviation = (title || '3D').substring(0, 2).toUpperCase();
          if (!imageUrl) {
            drawFallback();
            return;
          }
          // Proxy remote image URL to bypass CORS
          if (imageUrl.indexOf('http') === 0 && imageUrl.indexOf(window.location.origin) !== 0) {
            imageUrl = '/proxy?url=' + encodeURIComponent(imageUrl);
          }
          var img = new Image();
          img.crossOrigin = 'anonymous';
          img.onload = function() {
            try {
              var canvas = document.createElement('canvas');
              canvas.width = size + 2 * border;
              canvas.height = size + 2 * border;
              var ctx = canvas.getContext('2d');
              
              // White border background
              ctx.fillStyle = '#ffffff';
              ctx.fillRect(0, 0, canvas.width, canvas.height);
              
              // Draw the image
              ctx.drawImage(img, border, border, size, size);
              callback(canvas.toDataURL('image/png'));
            } catch (e) {
              drawFallback();
            }
          };
          img.onerror = function() {
            drawFallback();
          };
          img.src = imageUrl;

          function drawFallback() {
            try {
              var canvas = document.createElement('canvas');
              canvas.width = size + 2 * border;
              canvas.height = size + 2 * border;
              var ctx = canvas.getContext('2d');
              
              // White border background
              ctx.fillStyle = '#ffffff';
              ctx.fillRect(0, 0, canvas.width, canvas.height);
              
              // Purple themed square
              ctx.fillStyle = '#696cff';
              ctx.fillRect(border, border, size, size);
              
              // Dynamic abbreviation
              ctx.fillStyle = '#ffffff';
              ctx.font = 'bold ' + Math.round(size * 0.4) + 'px sans-serif';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText(abbreviation, canvas.width / 2, canvas.height / 2);
              callback(canvas.toDataURL('image/png'));
            } catch (err) {
              callback(imageUrl || '');
            }
          }
        }

        // 1. Create Pins/Billboards dynamically for all map locations
        var rawLocations = @json($mapLocations);
        var locations = rawLocations.map(function(loc) {
          return {
            id: loc.mapDataID,
            name: loc.title,
            description: loc.description || '',
            thumbnailUrl: loc.thumbNailUrl || '',
            longitude: Number(loc.xAxis),
            latitude: Number(loc.yAxis),
            originalData: loc
          };
        });

        var currentTileset = null;
        dataSource = new C.CustomDataSource('locationMarkers');
        viewer.dataSources.add(dataSource);

        var BLANK_THUMBNAIL_DATAURL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

        function makePinPlaceholderDataUrl(name, size) {
          try {
            var c = document.createElement('canvas');
            c.width = size; c.height = size;
            var ctx = c.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);
            ctx.fillStyle = '#696cff'; // Premium color
            ctx.fillRect(3, 3, size - 6, size - 6);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold ' + Math.max(8, Math.round(size * 0.18)) + 'px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var label = (name || '?').substring(0, 6);
            ctx.fillText(label, size / 2, size / 2);
            return c.toDataURL('image/png');
          } catch (e) {
            return BLANK_THUMBNAIL_DATAURL;
          }
        }

        function preloadPinImage(url, pinSize, borderPx, locId, callback) {
          var fullSize = pinSize + 2 * borderPx;
          var placeholder = makePinPlaceholderDataUrl(locId, fullSize);
          if (!url) {
            callback(placeholder, fullSize, fullSize);
            return;
          }
          // Proxy remote image URL to bypass CORS
          if (url.indexOf('http') === 0 && url.indexOf(window.location.origin) !== 0) {
            url = '/proxy?url=' + encodeURIComponent(url);
          }
          var img = new Image();
          img.crossOrigin = 'anonymous';
          img.onload = function() {
            try {
              var c = document.createElement('canvas');
              c.width = fullSize; c.height = fullSize;
              var ctx = c.getContext('2d');
              ctx.fillStyle = '#ffffff';
              ctx.fillRect(0, 0, fullSize, fullSize);
              ctx.drawImage(img, borderPx, borderPx, pinSize, pinSize);
              callback(c.toDataURL('image/png'), fullSize, fullSize);
            } catch (e) {
              callback(placeholder, fullSize, fullSize);
            }
          };
          img.onerror = function() {
            callback(placeholder, fullSize, fullSize);
          };
          img.src = url;
        }

        dataSource.clustering.clusterEvent.addEventListener(function (entities, cluster) {
          cluster.label.show = false;
          var count = entities.length;
          var dpr = 3; 
          var canvas = document.createElement('canvas');
          canvas.width = 42 * dpr; 
          canvas.height = 42 * dpr;
          var ctx = canvas.getContext('2d');
          ctx.scale(dpr, dpr);
          
          ctx.fillStyle = '#2c5fb3';
          ctx.fillRect(0, 0, 42, 42);
          ctx.strokeStyle = '#ffffff';
          ctx.lineWidth = 3;
          ctx.strokeRect(1.5, 1.5, 39, 39);
          
          ctx.fillStyle = '#ffffff';
          var fontSize = count > 9 ? 17 : 19;
          ctx.shadowColor = "rgba(255, 255, 255, 0.5)";
          ctx.shadowBlur = 1; 
          ctx.font = '900 ' + fontSize + 'px "Public Sans", sans-serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText(count.toString(), 21, 21);
          
          cluster.billboard.image = canvas.toDataURL('image/png');
          cluster.billboard.width = 42;
          cluster.billboard.height = 42; 
          cluster.billboard.show = true;
          cluster.billboard.verticalOrigin = C.VerticalOrigin.CENTER;
          cluster.billboard.horizontalOrigin = C.HorizontalOrigin.CENTER;
          cluster.billboard.disableDepthTestDistance = Number.POSITIVE_INFINITY;

          if (cluster.point) cluster.point.show = false;
          
          // Calculate centroid
          var sumLon = 0, sumLat = 0, posCount = 0;
          var time = viewer.clock.currentTime;
          var ids = [];
          for (var i = 0; i < entities.length; i++) {
            var ent = entities[i];
            if (ent.id) ids.push(ent.id);
            var ePos = ent.position;
            var cartesian = ePos && typeof ePos.getValue === 'function' ? ePos.getValue(time) : ePos;
            if (cartesian) {
              var carto = C.Cartographic.fromCartesian(cartesian);
              sumLon += carto.longitude; 
              sumLat += carto.latitude; 
              posCount++;
            }
          }
          
          if (posCount > 0) {
            cluster._wgs84Position = C.Cartesian3.fromRadians(sumLon / posCount, sumLat / posCount, 0);
            if (cluster.billboard) {
              cluster.billboard._wgs84Position = cluster._wgs84Position;
            }
          }
          
          if (ids.length) {
            ids.forEach(function (id) {
              if (!viewer._clusteredLocationIds) viewer._clusteredLocationIds = {};
              viewer._clusteredLocationIds[String(id).toLowerCase()] = true;
            });
            var clusterKey = ids.slice().sort().join(',');
            cluster._clusterKey = clusterKey;
            cluster.locationIds = ids;
            if (cluster.billboard) {
              cluster.billboard._clusterKey = clusterKey;
              cluster.billboard.locationIds = ids;
            }

            var activeCluster = {
              _clusterKey: clusterKey,
              locationIds: ids,
              _wgs84Position: cluster._wgs84Position,
              billboard: cluster.billboard
            };

            if (!viewer._activeClusters) {
              viewer._activeClusters = [];
            }
            var exists = false;
            for (var k = 0; k < viewer._activeClusters.length; k++) {
              if (viewer._activeClusters[k]._clusterKey === clusterKey) {
                viewer._activeClusters[k] = activeCluster;
                exists = true;
                break;
              }
            }
            if (!exists) {
              viewer._activeClusters.push(activeCluster);
            }
          }
        });

        var pendingCount = locations.length;
        var loadedPins = [];

        function addPinEntity(loc, position, labelText, billboardW, billboardH, imageOrDataUrl) {
          var entityOpt = {
            position: position,
            name: loc.name,
            id: loc.id
          };
          if (imageOrDataUrl && billboardW > 0 && billboardH > 0) {
            entityOpt.billboard = {
              image: imageOrDataUrl,
              width: billboardW,
              height: billboardH,
              verticalOrigin: C.VerticalOrigin.BOTTOM,
              disableDepthTestDistance: Number.POSITIVE_INFINITY
            };
            entityOpt.label = {
              text: labelText,
              font: 'bold 12px "Public Sans", sans-serif',
              fillColor: C.Color.WHITE,
              outlineColor: C.Color.fromCssColorString('#1a1a2e'),
              outlineWidth: 3,
              style: C.LabelStyle.FILL_AND_OUTLINE,
              verticalOrigin: C.VerticalOrigin.BOTTOM,
              pixelOffset: new C.Cartesian2(0, -billboardH - 8),
              disableDepthTestDistance: Number.POSITIVE_INFINITY
            };
          }
          try {
            var ent = dataSource.entities.add(entityOpt);
            ent.modelData = loc.originalData;
          } catch (err) {
            console.warn('Map marker add failed for', loc.id, err);
          }
        }

        if (pendingCount === 0) {
          viewer.scene.requestRender();
        } else {
          locations.forEach(function (loc) {
            var position = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
            preloadPinImage(loc.thumbnailUrl, 48, 3, loc.id, function (dataUrl, w, h) {
              loadedPins.push({
                loc: loc,
                position: position,
                w: w,
                h: h,
                dataUrl: dataUrl
              });
              pendingCount--;
              if (pendingCount === 0) {
                loadedPins.forEach(function (p) {
                  addPinEntity(p.loc, p.position, p.loc.name, p.w, p.h, p.dataUrl);
                });
                viewer._activeClusters = [];
                viewer._clusteredLocationIds = {};
                dataSource.clustering.enabled = false;
                dataSource.clustering.enabled = true;
                updateClusterPixelRange();
                viewer.scene.requestRender();
              }
            });
          });
        }

        var INITIAL_PIXEL_RANGE = 80;
        var isZoomingToCluster = false;

        function getClusterPixelRange() {
          var canvas = viewer.scene.canvas;
          if (!canvas || !canvas.clientWidth || !canvas.clientHeight) return INITIAL_PIXEL_RANGE;
          var minDim = Math.min(canvas.clientWidth, canvas.clientHeight);
          var is2D = viewer.scene.mode === C.SceneMode.SCENE2D;
          if (is2D) return getClusterPixelRange2DFallback();
          var rect = viewer.camera.computeViewRectangle(viewer.scene.globe.ellipsoid);
          if (!rect) return Math.max(INITIAL_PIXEL_RANGE, minDim * 0.9);
          var heightRad = rect.north - rect.south;
          var heightDeg = heightRad * (180 / Math.PI);
          
          var zoomedInHeight = 0.05;
          var zoomedOutHeight = 3.0;
          
          if (heightDeg >= zoomedOutHeight) return INITIAL_PIXEL_RANGE;
          if (heightDeg <= zoomedInHeight) return 50;
          
          var t = (heightDeg - zoomedInHeight) / (zoomedOutHeight - zoomedInHeight);
          return Math.max(50, Math.round(50 + t * (INITIAL_PIXEL_RANGE - 50)));
        }

        function getClusterPixelRange2DFallback() {
          try {
            var f = viewer.camera.frustum;
            if (f && typeof f.right === 'number' && typeof f.left === 'number') {
              var width = Math.abs(f.right - f.left);
              var zoomedOutWidth = 4e5;
              var zoomedInWidth = 1e4;
              if (width >= zoomedOutWidth) return INITIAL_PIXEL_RANGE;
              if (width <= zoomedInWidth) return 50;
              
              var t = (width - zoomedInWidth) / (zoomedOutWidth - zoomedInWidth);
              return Math.max(50, Math.round(50 + t * (INITIAL_PIXEL_RANGE - 50)));
            }
          } catch (e) {}
          return INITIAL_PIXEL_RANGE;
        }

        function updateClusterPixelRange() {
          if (isZoomingToCluster) return;
          var pr = getClusterPixelRange();
          if (dataSource.clustering.pixelRange === pr) return;
          dataSource.clustering.pixelRange = pr;
          setTimeout(function() {
            viewer._activeClusters = [];
            viewer._clusteredLocationIds = {};
            dataSource.clustering.enabled = false;
            dataSource.clustering.enabled = true;
            viewer.scene.requestRender();
          }, 50);
        }

        dataSource.clustering.pixelRange = INITIAL_PIXEL_RANGE;
        var clusterRangeThrottle = null;

        function throttledUpdateClusterPixelRange() {
          if (clusterRangeThrottle) return;
          clusterRangeThrottle = setTimeout(function () {
            clusterRangeThrottle = null;
            updateClusterPixelRange();
          }, 180);
        }

        var locationByIdForActiveCheck = {};
        locations.forEach(function (loc) { locationByIdForActiveCheck[loc.id] = loc; });

        function isClusterActive(cluster) {
          if (!cluster) return false;
          if (dataSource && dataSource.clustering && dataSource.clustering.pixelRange === 1) {
            return false;
          }
          try {
            if (cluster.locationIds && cluster.locationIds.length > 0) {
              if (typeof isLocationClustered === 'function') {
                return isLocationClustered(cluster.locationIds[0]);
              }
            }
          } catch (e) {}
          return false;
        }

        function pruneActiveClusters() {
          if (viewer._activeClusters && typeof isClusterActive === 'function') {
            viewer._activeClusters = viewer._activeClusters.filter(isClusterActive);
          }
        }

        viewer._activeClusters = [];
        viewer._clusteredLocationIds = {};

        function isLocationClustered(locId) {
          if (!locId) return false;
          if (dataSource && dataSource.clustering && dataSource.clustering.enabled) {
            if (dataSource.clustering.pixelRange === 1) return false;
            var searchId = String(locId).toLowerCase();
            if (viewer._clusteredLocationIds && viewer._clusteredLocationIds[searchId]) {
              return true;
            }
          }
          return false;
        }

        var PIN_SEARCH_HALF_H = 24;

        function getLocationsInRadius(screenX, screenY, radiusPx) {
          var scene = viewer.scene;
          var canvas = scene.canvas;
          var cameraPos = scene.camera.position;
          var R = 6371000;
          var distToCenter = C.Cartesian3.magnitude(cameraPos);
          var cameraHeight = Math.max(distToCenter - R, 0);
          var horizonDistSq = cameraHeight * (2 * R + cameraHeight);
          
          var scaleX = (canvas.clientWidth && canvas.width) ? (canvas.clientWidth / canvas.width) : 1;
          var scaleY = (canvas.clientHeight && canvas.height) ? (canvas.clientHeight / canvas.height) : 1;
          
          var nearby = [];
          var maxDistSq = (radiusPx || 70) * (radiusPx || 70);

          for (var i = 0; i < locations.length; i++) {
            var loc = locations[i];
            var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
            var is2D = scene.mode === C.SceneMode.SCENE2D;
            var distSqToPoint = C.Cartesian3.distanceSquared(cameraPos, cartesian);
            if (!is2D && distSqToPoint > horizonDistSq * 1.5) continue;

            var screenPos = projectCartesian(scene, cartesian);
            if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
              var pinCenterX = screenPos.x;
              var pinCenterY = screenPos.y - PIN_SEARCH_HALF_H;

              var dx1 = pinCenterX - screenX;
              var dy1 = pinCenterY - screenY;
              var distSq1 = dx1 * dx1 + dy1 * dy1;
              
              var dx2 = pinCenterX * scaleX - screenX;
              var dy2 = pinCenterY * scaleY - screenY;
              var distSq2 = dx2 * dx2 + dy2 * dy2;
              
              var dx3 = pinCenterX - screenX / scaleX;
              var dy3 = pinCenterY - screenY / scaleY;
              var distSq3 = dx3 * dx3 + dy3 * dy3;

              var minDistSq = Math.min(distSq1, distSq2, distSq3);
              if (minDistSq <= maxDistSq) {
                nearby.push({ loc: loc, distSq: minDistSq });
              }
            }
          }
          nearby.sort(function(a, b) { return a.distSq - b.distSq; });
          return nearby.map(function(item) { return item.loc; });
        }

        function getClusterAtScreenPosition(screenX, screenY, radiusPx) {
          pruneActiveClusters();
          var arr = viewer._activeClusters || [];
          if (arr.length === 0) return null;
          var scene = viewer.scene;
          var canvas = scene.canvas;
          var R = 6371000;
          var distToCenter = C.Cartesian3.magnitude(scene.camera.position);
          var horizonDistSq = Math.max(distToCenter - R, 0) * (2 * R + Math.max(distToCenter - R, 0));
          
          var scaleX = (canvas.clientWidth && canvas.width) ? (canvas.clientWidth / canvas.width) : 1;
          var scaleY = (canvas.clientHeight && canvas.height) ? (canvas.clientHeight / canvas.height) : 1;
          
          var closestCluster = null;
          var minVal = radiusPx * radiusPx;

          for (var i = 0; i < arr.length; i++) {
            var cluster = arr[i];
            if (!isClusterActive(cluster)) continue;
            var pos = cluster._wgs84Position;
            if (!pos) continue;

            var is2D = scene.mode === C.SceneMode.SCENE2D;
            if (!is2D && C.Cartesian3.distanceSquared(scene.camera.position, pos) > horizonDistSq * 1.5) continue;
            
            var screenPos = projectCartesian(scene, pos);
            if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
              var dx1 = screenPos.x - screenX, dy1 = screenPos.y - screenY;
              var distSq1 = dx1 * dx1 + dy1 * dy1;
              
              var dx2 = screenPos.x * scaleX - screenX, dy2 = screenPos.y * scaleY - screenY;
              var distSq2 = dx2 * dx2 + dy2 * dy2;
              
              var dx3 = screenPos.x - screenX / scaleX, dy3 = screenPos.y - screenY / scaleY;
              var distSq3 = dx3 * dx3 + dy3 * dy3;

              var minDistSq = Math.min(distSq1, distSq2, distSq3);
              if (minDistSq <= minVal) {
                minVal = minDistSq;
                closestCluster = cluster;
              }
            }
          }
          return closestCluster;
        }

        function getBoundsRectForLocations(locs) {
          if (!locs || !locs.length) return null;
          var lonMin = Infinity, latMin = Infinity, lonMax = -Infinity, latMax = -Infinity;
          for (var i = 0; i < locs.length; i++) {
            var loc = locs[i];
            var lon = loc.longitude * (Math.PI / 180), lat = loc.latitude * (Math.PI / 180);
            if (lon < lonMin) lonMin = lon;
            if (lat < latMin) latMin = lat;
            if (lon > lonMax) lonMax = lon;
            if (lat > latMax) latMax = lat;
          }
          if (lonMin > lonMax || latMin > latMax) return null;
          var pad = 0.2;
          var w = Math.max((lonMax - lonMin) * pad, 0.00001);
          var h = Math.max((latMax - latMin) * pad, 0.00001);
          return C.Rectangle.fromRadians(lonMin - w, latMin - h, lonMax + w, latMax + h);
        }

        function getLocationsNearPoint(lonDeg, latDeg, radiusDeg) {
          var r = (radiusDeg || 0.08) * (Math.PI / 180);
          var centerLon = lonDeg * (Math.PI / 180), centerLat = latDeg * (Math.PI / 180);
          var nearby = [];
          for (var i = 0; i < locations.length; i++) {
            var loc = locations[i];
            var lon = loc.longitude * (Math.PI / 180), lat = loc.latitude * (Math.PI / 180);
            var dy = lat - centerLat, dx = (lon - centerLon) * Math.cos(centerLat);
            if (dx * dx + dy * dy <= r * r) nearby.push(loc);
          }
          return nearby;
        }

        function zoomInOneStepTowardCluster(clusterPosition) {
          var camera = viewer.camera;
          var scene = viewer.scene;
          try {
            var carto = C.Cartographic.fromCartesian(clusterPosition);
            var rect = camera.computeViewRectangle(scene.globe.ellipsoid);
            if (rect) {
              var width = (rect.east - rect.west) * 0.15;
              var height = (rect.north - rect.south) * 0.15;
              var halfW = width * 0.5, halfH = height * 0.5;
              var newWest = C.Math.clamp(carto.longitude - halfW, -Math.PI, Math.PI);
              var newEast = C.Math.clamp(carto.longitude + halfW, -Math.PI, Math.PI);
              var newSouth = C.Math.clamp(carto.latitude - halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
              var newNorth = C.Math.clamp(carto.latitude + halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
              camera.flyTo({ 
                destination: new C.Rectangle(newWest, newSouth, newEast, newNorth), 
                duration: 0.35, 
                complete: function () { 
                  isZoomingToCluster = true;
                  dataSource.clustering.pixelRange = 1;
                  viewer._activeClusters = [];
                  viewer._clusteredLocationIds = {};
                  dataSource.clustering.enabled = false;
                  dataSource.clustering.enabled = true;
                  scene.requestRender(); 
                  setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
                } 
              });
            } else {
              var lon = C.Math.toDegrees(carto.longitude);
              var lat = C.Math.toDegrees(carto.latitude);
              var span = 0.003;
              camera.flyTo({ 
                destination: C.Rectangle.fromDegrees(lon - span, lat - span * 0.6, lon + span, lat + span * 0.6), 
                duration: 0.35, 
                complete: function () { 
                  isZoomingToCluster = true;
                  dataSource.clustering.pixelRange = 1;
                  viewer._activeClusters = [];
                  viewer._clusteredLocationIds = {};
                  dataSource.clustering.enabled = false;
                  dataSource.clustering.enabled = true;
                  scene.requestRender(); 
                  setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
                } 
              });
            }
          } catch (e) {
            console.warn('Cluster zoom failed', e);
          }
        }

        var locationByIdForZoom = {};
        locations.forEach(function (loc) { locationByIdForZoom[loc.id] = loc; });

        function tryZoomToCluster(entity) {
          var bounds = null;
          var clusterPos = entity._wgs84Position || (entity.position && (typeof entity.position.getValue === 'function' ? entity.position.getValue(viewer.clock.currentTime) : entity.position));
          
          var clusterLocs = [];
          if (entity && entity.locationIds && entity.locationIds.length > 0) {
            entity.locationIds.forEach(function (id) {
              var loc = locationByIdForZoom[id];
              if (loc) clusterLocs.push(loc);
            });
          }
          
          if (clusterLocs.length > 0) {
            bounds = getBoundsRectForLocations(clusterLocs);
          } else if (clusterPos) {
            var carto = C.Cartographic.fromCartesian(clusterPos);
            var locsNear = getLocationsNearPoint(carto.longitude * (180 / Math.PI), carto.latitude * (180 / Math.PI), 0.12);
            if (locsNear.length > 0) bounds = getBoundsRectForLocations(locsNear);
          }
          
          if (!bounds) return null;
          
          try {
            var rect = null;
            try { rect = viewer.camera.computeViewRectangle(viewer.scene.globe.ellipsoid); } catch(e) {}
            if (rect) {
              var currentWidth = rect.east - rect.west;
              var boundsWidth = bounds.east - bounds.west;
              if (currentWidth <= boundsWidth * 1.5) {
                 return false;
              }
            }

            viewer.camera.flyTo({ 
              destination: bounds, 
              duration: 0.45, 
              complete: function () { 
                isZoomingToCluster = true;
                dataSource.clustering.pixelRange = 1;
                viewer._activeClusters = [];
                viewer._clusteredLocationIds = {};
                dataSource.clustering.enabled = false;
                dataSource.clustering.enabled = true;
                viewer.scene.requestRender(); 
                setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
              } 
            });
            return true;
          } catch (e) {
            return false;
          }
        }

        var handler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
        handler.setInputAction(function (click) {
          try {
            if (viewer.scene.mode !== C.SceneMode.SCENE2D) return;

            var screenX = typeof click.position.x === 'number' ? click.position.x : 0;
            var screenY = typeof click.position.y === 'number' ? click.position.y : 0;
            
            // 1. Math Cluster Search
            var cluster = getClusterAtScreenPosition(screenX, screenY, 40);
            if (cluster) {
               isZoomingToCluster = true;
               dataSource.clustering.pixelRange = 1;
               if (tryZoomToCluster(cluster)) return;
               var clusterPos = cluster._wgs84Position;
               if (clusterPos) { zoomInOneStepTowardCluster(clusterPos); return; }
            }
     
            // 2. Math Single Pin Search
            var locsInRadius = getLocationsInRadius(screenX, screenY, 30);
            var visibleNear = [];
            for (var i = 0; i < locsInRadius.length; i++) {
              if (!isLocationClustered(locsInRadius[i].id)) {
                visibleNear.push(locsInRadius[i]);
              }
            }

            if (visibleNear.length === 1) {
               switchTo3D(visibleNear[0].originalData);
               return;
            } else if (visibleNear.length >= 2) {
               var bounds = getBoundsRectForLocations(visibleNear);
               if (bounds) {
                  isZoomingToCluster = true;
                  dataSource.clustering.pixelRange = 1;
                  try {
                    viewer.camera.flyTo({ 
                      destination: bounds, 
                      duration: 0.45, 
                      complete: function () { 
                        isZoomingToCluster = true;
                        dataSource.clustering.pixelRange = 1;
                        viewer._activeClusters = [];
                        viewer._clusteredLocationIds = {};
                        dataSource.clustering.enabled = false;
                        dataSource.clustering.enabled = true;
                        viewer.scene.requestRender(); 
                        setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
                      } 
                    });
                  } catch(e) {}
                  return;
               }
            }

            // 3. WebGL Pick Fallback
            var picked = viewer.scene.pick(click.position);
            var entity = C.defined(picked) && picked.id ? picked.id : null;
            if (entity) {
              var id = typeof entity.id === 'string' ? entity.id : (entity.id && entity.id.id);
              if (id && locationByIdForZoom[id]) {
                var pinLoc = locationByIdForZoom[id];
                if (pinLoc && !isLocationClustered(pinLoc.id)) {
                  switchTo3D(pinLoc.originalData);
                }
                return;
              }
            }
          } catch (clickErr) {
            console.warn("Click handler error: ", clickErr);
          }
        }, C.ScreenSpaceEventType.LEFT_CLICK);

        // Hover cursor style change handler
        handler.setInputAction(function(movement) {
          if (viewer.scene.mode !== C.SceneMode.SCENE2D) return;

          var pickedObject = viewer.scene.pick(movement.endPosition);
          var entity = C.defined(pickedObject) && pickedObject.id ? pickedObject.id : null;
          var isOverMarker = false;

          if (entity) {
            var id = typeof entity.id === 'string' ? entity.id : (entity.id && entity.id.id);
            if (id && locationByIdForZoom[id]) {
              isOverMarker = true;
            }
          }

          if (isOverMarker) {
            viewer.canvas.style.cursor = 'pointer';
          } else {
            var screenX = movement.endPosition.x;
            var screenY = movement.endPosition.y;
            var cluster = getClusterAtScreenPosition(screenX, screenY, 40);
            var locs = getLocationsInRadius(screenX, screenY, 36);
            var hasVisiblePin = false;
            for (var i = 0; i < locs.length; i++) {
              if (!isLocationClustered(locs[i].id)) {
                hasVisiblePin = true;
                break;
              }
            }

            if (cluster || hasVisiblePin) {
              viewer.canvas.style.cursor = 'pointer';
            } else {
              if (!isDrawing && draggedVertexIndex === null) {
                viewer.canvas.style.cursor = '';
              }
            }
          }
        }, C.ScreenSpaceEventType.MOUSE_MOVE);

        // Setup Location Choice Bar
        var bar = document.getElementById('locationChoiceBar');
        var cardsContainer = document.getElementById('locationChoiceBarCards');
        var mapContainer = document.getElementById('heroMapContainer');
        var canvas = viewer.scene.canvas;
        var canvasRect = canvas.getBoundingClientRect();
        var cameraIsMoving = false;
        var barVisible = false;
        var _lastRenderedKey = null;
        var hideRafId = null;
        var placeRafId = null;

        viewer.camera.moveStart.addEventListener(function () { 
          cameraIsMoving = true; 
          hideBar(); 
        });
        viewer.camera.moveEnd.addEventListener(function () { 
          cameraIsMoving = false;
          canvasRect = canvas.getBoundingClientRect();
        });

        var hoverHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
        hoverHandler.setInputAction(function (movement) {
          try {
            if (cameraIsMoving || viewer.scene.mode !== C.SceneMode.SCENE2D) return;
            var screenX = movement.endPosition.x;
            var screenY = movement.endPosition.y;

            var locs = getLocationsForHover(screenX, screenY);
            var rect = viewer.scene.canvas.getBoundingClientRect();
            var clientX = rect.left + screenX;
            var clientY = rect.top + screenY;

            if (!locs || locs.length === 0) {
              if (!isMouseOverBarExpanded(clientX, clientY)) {
                hideBar();
              }
              return;
            }

            var anchor = getPinAnchor(locs, screenX, screenY);
            if (!anchor) {
              if (!isMouseOverBarExpanded(clientX, clientY)) {
                hideBar();
              }
              return;
            }

            showBar(locs, anchor.clientX, anchor.clientY);
          } catch (err) {
            console.warn('Hover update skipped:', err);
          }
        }, C.ScreenSpaceEventType.MOUSE_MOVE);

        if (viewer.scene.canvas._cesiumHoverHandler) {
          viewer.scene.canvas._cesiumHoverHandler.destroy();
        }
        viewer.scene.canvas._cesiumHoverHandler = hoverHandler;

        var locationById = {};
        locations.forEach(function (loc) { locationById[loc.id] = loc; });

        function getLocationsForHover(screenX, screenY) {
          try {
            var picked = viewer.scene.pick(new C.Cartesian2(screenX, screenY));
            if (picked && picked.primitive) {
              var ids = picked.primitive.locationIds;
              if (!ids || ids.length < 2) {
                var activeClusters = viewer._activeClusters || [];
                for (var c = 0; c < activeClusters.length; c++) {
                  if (activeClusters[c].billboard === picked.primitive) {
                    ids = activeClusters[c].locationIds;
                    break;
                  }
                }
              }
              if (ids && ids.length >= 2) {
                var list = ids.map(function (id) { return locationById[id]; }).filter(Boolean);
                if (list.length >= 2) return list.slice(0, ids.length);
              }
              
              var entityId = picked.id && typeof picked.id === 'object' ? picked.id.id : picked.id;
              if (typeof entityId === 'string' && locationById[entityId]) {
                return [locationById[entityId]];
              }
            }
          } catch (e) {}

          var cluster = getClusterAtScreenPosition(screenX, screenY, 40);
          if (cluster) {
            var ids = cluster.locationIds;
            if (ids && ids.length >= 2) {
              var list = ids.map(function (id) { return locationById[id]; }).filter(Boolean);
              if (list.length >= 2) return list.slice(0, ids.length);
            }
          }

          var near = getLocationsInRadius(screenX, screenY, 36);
          var visibleNear = [];
          for (var i = 0; i < near.length; i++) {
            if (!isLocationClustered(near[i].id)) {
              visibleNear.push(near[i]);
            }
          }

          if (visibleNear.length > 0) {
            return [visibleNear[0]];
          }
          return [];
        }

        function getPinCenterClientPosition(nearby) {
          if (!nearby || !nearby.length) return null;
          var scene = viewer.scene;
          var sumX = 0, sumY = 0, count = 0;
          for (var i = 0; i < nearby.length; i++) {
            var loc = nearby[i];
            var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
            var screenPos = projectCartesian(scene, cartesian);
            if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
              sumX += screenPos.x; sumY += screenPos.y; count++;
            }
          }
          if (count === 0) return null;
          var rect = canvas.getBoundingClientRect();
          var centerX = rect.left + (sumX / count);
          var centerY = rect.top + (sumY / count) - PIN_SEARCH_HALF_H;
          return { clientX: centerX, clientY: centerY };
        }

        function getPinAnchor(locs, cursorX, cursorY) {
          if (!locs || !locs.length) return null;
          if (locs.length >= 2) {
            var cluster = getClusterAtScreenPosition(cursorX, cursorY, 40);
            if (cluster && cluster._wgs84Position) {
              var screenPos = projectCartesian(viewer.scene, cluster._wgs84Position);
              if (screenPos && typeof screenPos.x === 'number') {
                var rect = canvas.getBoundingClientRect();
                return { clientX: rect.left + screenPos.x, clientY: rect.top + screenPos.y };
              }
            }
          }
          var center = getPinCenterClientPosition(locs);
          if (center) return center;
          return null;
        }

        function truncateDesc(str, maxLen) {
          if (!str) return '';
          str = str.trim();
          if (str.length <= maxLen) return str;
          return str.substring(0, maxLen).trim() + '…';
        }

        function renderBarCards(nearby) {
          cardsContainer.innerHTML = '';
          if (!nearby.length) return;
          var isSingle = nearby.length === 1;
          bar.classList.toggle('location-choice-bar-single', isSingle);
          
          nearby.forEach(function (loc) {
            var card = document.createElement('div');
            card.className = 'location-choice-card' + (isSingle ? ' location-choice-card-single' : '');
            
            var wrap = document.createElement('div');
            wrap.className = 'location-choice-card-image-wrap';
            
            var img = document.createElement('img');
            img.alt = loc.name || '';
            img.src = loc.thumbnailUrl || BLANK_THUMBNAIL_DATAURL;
            img.onerror = function () {
              this.src = BLANK_THUMBNAIL_DATAURL;
            };
            
            wrap.appendChild(img);
            card.appendChild(wrap);
            
            var body = document.createElement('div');
            body.className = 'location-choice-card-body';
            body.innerHTML = '<p class="location-choice-card-title">' + (loc.name || loc.id).replace(/</g, '&lt;') + '</p>' +
              '<p class="location-choice-card-desc">' + truncateDesc(loc.description || '', 70).replace(/</g, '&lt;') + '</p>';
            
            card.appendChild(body);
            card.addEventListener('click', function () {
              switchTo3D(loc.originalData);
            });
            cardsContainer.appendChild(card);
          });
        }

        function placeFloatingBox(clientX, clientY, singlePin) {
          var pinGap = 2;
          var screenPad = 14;
          var pinHalfW = singlePin ? 24 : 21;
          var maxW = window.innerWidth, maxH = window.innerHeight;
          var barW = bar.offsetWidth || (singlePin ? 220 : 320);
          var barH = bar.offsetHeight || (singlePin ? 100 : 280);

          var left = clientX + pinHalfW + pinGap;
          if (left + barW > maxW - screenPad && (clientX - pinHalfW - pinGap - barW >= screenPad)) {
            left = clientX - pinHalfW - pinGap - barW;
          }

          if (left < screenPad) left = screenPad;
          if (left + barW > maxW - screenPad) left = maxW - screenPad - barW;

          var top = clientY - barH * 0.5;
          if (top < screenPad) top = screenPad;
          if (top + barH > maxH - screenPad) top = maxH - barH - screenPad;

          bar.style.left = left + 'px';
          bar.style.top = top + 'px';
        }

        function showBar(nearby, clientX, clientY) {
          if (hideRafId) {
            cancelAnimationFrame(hideRafId);
            hideRafId = null;
          }
          var key = nearby.map(function(l) { return l.id; }).sort().join(',');
          var isSingle = nearby.length === 1;
          if (key !== _lastRenderedKey) {
            _lastRenderedKey = key;
            renderBarCards(nearby);
          }
          bar.classList.add('location-choice-bar-floating', 'is-visible');
          bar.setAttribute('aria-hidden', 'false');
          
          if (typeof clientX === 'number' && typeof clientY === 'number') {
            if (placeRafId) cancelAnimationFrame(placeRafId);
            placeRafId = requestAnimationFrame(function () {
              placeFloatingBox(clientX, clientY, isSingle);
              placeRafId = null;
            });
          }
          barVisible = true;
        }

        function hideBar() {
          barVisible = false;
          _lastRenderedKey = null;
          bar.classList.remove('location-choice-bar-single');
          bar.setAttribute('aria-hidden', 'true');
          bar.style.transition = 'none';
          bar.classList.remove('is-visible');

          if (placeRafId) {
            cancelAnimationFrame(placeRafId);
            placeRafId = null;
          }
          if (hideRafId) cancelAnimationFrame(hideRafId);
          hideRafId = requestAnimationFrame(function () {
            bar.classList.remove('location-choice-bar-floating');
            bar.removeAttribute('style');
            hideRafId = null;
          });
        }

        function isMouseOverBarExpanded(clientX, clientY) {
          if (!bar.classList.contains('is-visible')) return false;
          var rect = bar.getBoundingClientRect();
          var pad = 30;
          return clientX >= (rect.left - pad) && clientX <= (rect.right + pad) && clientY >= (rect.top - pad) && clientY <= (rect.bottom + pad);
        }

        mapContainer.addEventListener('mouseleave', hideBar);

        document.addEventListener('mousemove', function (e) {
          if (!barVisible) return;
          var rect = canvas.getBoundingClientRect();
          var overCanvas = rect.left <= e.clientX && e.clientX <= rect.right && rect.top <= e.clientY && e.clientY <= rect.bottom;
          if (!isMouseOverBarExpanded(e.clientX, e.clientY) && !overCanvas) {
            hideBar();
          }
        });

        var cameraChangeDebounceTimer = null;

        function handleCameraChangeEnd() {
          if (isZoomingToCluster) return;
          updateClusterPixelRange();
          viewer._activeClusters = [];
          viewer._clusteredLocationIds = {};
          dataSource.clustering.enabled = false;
          dataSource.clustering.enabled = true;
          viewer.scene.requestRender();
          pruneActiveClusters();
        }

        viewer.camera.moveStart.addEventListener(function() {
          viewer._activeClusters = [];
          viewer._clusteredLocationIds = {};
        });

        viewer.camera.moveEnd.addEventListener(function() {
          if (cameraChangeDebounceTimer) {
            clearTimeout(cameraChangeDebounceTimer);
            cameraChangeDebounceTimer = null;
          }
          handleCameraChangeEnd();
        });

        viewer.camera.changed.addEventListener(function() {
          throttledUpdateClusterPixelRange();
          if (cameraChangeDebounceTimer) clearTimeout(cameraChangeDebounceTimer);
          cameraChangeDebounceTimer = setTimeout(function () {
            cameraChangeDebounceTimer = null;
            handleCameraChangeEnd();
          }, 150);
        });

        var resizeTimer = null;
        function refreshLayout() {
          cameraIsMoving = false;
          canvasRect = canvas.getBoundingClientRect();
          if (viewer && viewer.resize) {
            viewer.resize();
            viewer.scene.requestRender();
          }
          hideBar();
        }
        window.addEventListener('resize', function() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(refreshLayout, 200);
        });
        document.addEventListener('fullscreenchange', refreshLayout);
        document.addEventListener('webkitfullscreenchange', refreshLayout);
        document.addEventListener('mozfullscreenchange', refreshLayout);

        // --- 180-Degree Side/Surface Orbit Animation ---
        var orbitTickListener = null;

        function cancelOrbit() {
          if (orbitTickListener) {
            viewer.clock.onTick.removeEventListener(orbitTickListener);
            orbitTickListener = null;
            viewer.camera.lookAtTransform(C.Matrix4.IDENTITY);
            viewer.scene.requestRender();
          }
        }

        // Cancel the automated orbit animation as soon as the user interacts with the map
        var interactHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
        var eventsToCancel = [
          C.ScreenSpaceEventType.LEFT_DOWN,
          C.ScreenSpaceEventType.MIDDLE_DOWN,
          C.ScreenSpaceEventType.RIGHT_DOWN,
          C.ScreenSpaceEventType.WHEEL
        ];
        eventsToCancel.forEach(function(evType) {
          interactHandler.setInputAction(cancelOrbit, evType);
        });

        function startOrbit180(center, radius) {
          cancelOrbit();

          var duration = 6000; // Smooth 6-second rotation for the 180-degree sweep
          var startHeading = viewer.camera.heading;
          var targetPitch = viewer.camera.pitch;
          
          if (targetPitch < C.Math.toRadians(-45) || targetPitch > C.Math.toRadians(-5)) {
            targetPitch = C.Math.toRadians(-18);
          }
          
          var range = C.Cartesian3.distance(viewer.camera.position, center);
          if (range < radius || range > radius * 5) {
            range = radius * 2.2;
          }

          var startTime = null;

          orbitTickListener = function() {
            if (!startTime) {
              startTime = Date.now();
            }
            var elapsed = Date.now() - startTime;
            var progress = Math.min(elapsed / duration, 1.0);

            // Easing: easeInOutQuad for premium feel
            var easeProgress = progress < 0.5
              ? 2 * progress * progress
              : -1 + (4 - 2 * progress) * progress;

            var currentHeading = startHeading + easeProgress * Math.PI;

            viewer.camera.lookAt(center, new C.HeadingPitchRange(currentHeading, targetPitch, range));
            viewer.scene.requestRender();

            if (progress >= 1.0) {
              cancelOrbit();
            }
          };

          viewer.clock.onTick.addEventListener(orbitTickListener);
        }

        // Orbit button click listener
        var orbitBtn = document.getElementById('orbit3dBtn');
        if (orbitBtn) {
          orbitBtn.addEventListener('click', function() {
            if (currentTileset) {
              startOrbit180(currentTileset.boundingSphere.center, currentTileset.boundingSphere.radius);
            }
          });
        }

        // --- Interactive Polygon Drawing & Vertex Editing Logic ---
        function startDrawing() {
          cancelOrbit();
          clearPolygon();
          isDrawing = true;
          viewer.canvas.style.cursor = 'crosshair';

          var drawBtn = document.getElementById('btnDrawPolygon');
          if (drawBtn) {
            drawBtn.innerHTML = '<i class="bx bx-x me-1"></i> Cancel';
            drawBtn.className = 'btn btn-sm btn-secondary shadow-sm fw-bold d-flex align-items-center gap-1';
          }

          drawingHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);

          drawingHandler.setInputAction(function(movement) {
            mousePosition = viewer.scene.pickPosition(movement.endPosition);
            if (!mousePosition) {
              mousePosition = viewer.camera.pickEllipsoid(movement.endPosition);
            }
            viewer.scene.requestRender();
          }, C.ScreenSpaceEventType.MOUSE_MOVE);

          drawingHandler.setInputAction(function(click) {
            var pickedPosition = viewer.scene.pickPosition(click.position);
            if (!pickedPosition) {
              pickedPosition = viewer.camera.pickEllipsoid(click.position);
            }
            if (!pickedPosition) return;

            polygonPoints.push(pickedPosition);

            // Place vertex helper dot
            var pointEntity = viewer.entities.add({
              position: pickedPosition,
              point: {
                pixelSize: 10,
                color: C.Color.YELLOW,
                outlineColor: C.Color.WHITE,
                outlineWidth: 2,
                disableDepthTestDistance: Number.POSITIVE_INFINITY
              }
            });
            drawingEntities.push(pointEntity);

            // On first point, draw the live preview polyline and polygon fill
            if (polygonPoints.length === 1) {
              var dynamicPositions = new C.CallbackProperty(function() {
                var pts = [].concat(polygonPoints);
                if (mousePosition) {
                  pts.push(mousePosition);
                }
                return pts;
              }, false);

              activeDrawingPreview = viewer.entities.add({
                polyline: {
                  positions: dynamicPositions,
                  width: 3,
                  material: C.Color.CYAN,
                  clampToGround: true
                },
                polygon: {
                  hierarchy: new C.CallbackProperty(function() {
                    var pts = [].concat(polygonPoints);
                    if (mousePosition) {
                      pts.push(mousePosition);
                    }
                    return pts.length >= 3 ? new C.PolygonHierarchy(pts) : undefined;
                  }, false),
                  material: C.Color.CYAN.withAlpha(0.3),
                  classificationType: C.ClassificationType.CESIUM_3D_TILE
                }
              });
              drawingEntities.push(activeDrawingPreview);
            }
          }, C.ScreenSpaceEventType.LEFT_CLICK);

          drawingHandler.setInputAction(function() {
            if (polygonPoints.length >= 3) {
              // Truncate double click noise (extra click triggers)
              if (polygonPoints.length > 3) {
                polygonPoints.pop();
              }
              completeDrawing();
            }
          }, C.ScreenSpaceEventType.LEFT_DOUBLE_CLICK);
        }

        function completeDrawing() {
          isDrawing = false;
          viewer.canvas.style.cursor = '';
          
          if (drawingHandler) {
            drawingHandler.destroy();
            drawingHandler = null;
          }

          // Clear temporary drawing helper lines and dots
          drawingEntities.forEach(function(ent) {
            viewer.entities.remove(ent);
          });
          drawingEntities = [];

          // Hide draw button, show clear button
          var drawBtn = document.getElementById('btnDrawPolygon');
          if (drawBtn) drawBtn.style.display = 'none';
          var clearBtn = document.getElementById('btnClearPolygon');
          if (clearBtn) clearBtn.style.display = 'flex';

          // 1. Create premium final polygon with CallbackProperty hierarchy
          finalPolygonEntity = viewer.entities.add({
            polygon: {
              hierarchy: new C.CallbackProperty(function() {
                return new C.PolygonHierarchy(polygonPoints);
              }, false),
              material: C.Color.fromCssColorString('#696cff').withAlpha(0.4),
              outline: true,
              outlineColor: C.Color.WHITE,
              outlineWidth: 2,
              classificationType: C.ClassificationType.CESIUM_3D_TILE
            }
          });

          // 2. Create editable grab handle entities at each vertex
          polygonPoints.forEach(function(pos, idx) {
            var handle = viewer.entities.add({
              position: pos,
              point: {
                pixelSize: 12,
                color: C.Color.YELLOW,
                outlineColor: C.Color.WHITE,
                outlineWidth: 2.5,
                disableDepthTestDistance: Number.POSITIVE_INFINITY
              },
              isVertex: true,
              vertexIndex: idx
            });
            editVertexEntities.push(handle);
          });

          // 3. Set up interactive vertex dragging
          editHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);

          editHandler.setInputAction(function(click) {
            var picked = viewer.scene.pick(click.position);
            if (C.defined(picked) && picked.id && picked.id.isVertex) {
              draggedVertexIndex = picked.id.vertexIndex;
              draggedVertexEntity = picked.id;
              viewer.scene.screenSpaceCameraController.enableInputs = false; // lock camera
            }
          }, C.ScreenSpaceEventType.LEFT_DOWN);

          editHandler.setInputAction(function(movement) {
            if (draggedVertexIndex === null) {
              var picked = viewer.scene.pick(movement.endPosition);
              if (C.defined(picked) && picked.id && picked.id.isVertex) {
                viewer.canvas.style.cursor = 'grab';
              } else {
                if (!isDrawing) {
                  viewer.canvas.style.cursor = '';
                }
              }
              return;
            }

            var newPos = viewer.scene.pickPosition(movement.endPosition);
            if (!newPos) {
              newPos = viewer.camera.pickEllipsoid(movement.endPosition);
            }
            if (newPos) {
              draggedVertexEntity.position = newPos;
              polygonPoints[draggedVertexIndex] = newPos;
              viewer.scene.requestRender();
            }
          }, C.ScreenSpaceEventType.MOUSE_MOVE);

          editHandler.setInputAction(function() {
            if (draggedVertexIndex !== null) {
              draggedVertexIndex = null;
              draggedVertexEntity = null;
              viewer.scene.screenSpaceCameraController.enableInputs = true; // unlock camera
            }
          }, C.ScreenSpaceEventType.LEFT_UP);

          viewer.scene.requestRender();
        }

        function clearPolygon() {
          if (draggedVertexIndex !== null) {
            draggedVertexIndex = null;
            draggedVertexEntity = null;
            viewer.scene.screenSpaceCameraController.enableInputs = true;
          }

          if (drawingHandler) {
            drawingHandler.destroy();
            drawingHandler = null;
          }
          if (editHandler) {
            editHandler.destroy();
            editHandler = null;
          }

          drawingEntities.forEach(function(ent) { viewer.entities.remove(ent); });
          drawingEntities = [];
          editVertexEntities.forEach(function(ent) { viewer.entities.remove(ent); });
          editVertexEntities = [];

          if (finalPolygonEntity) {
            viewer.entities.remove(finalPolygonEntity);
            finalPolygonEntity = null;
          }

          polygonPoints = [];
          isDrawing = false;
          viewer.canvas.style.cursor = '';

          // Reset buttons
          var drawBtn = document.getElementById('btnDrawPolygon');
          if (drawBtn) {
            drawBtn.style.display = 'flex';
            drawBtn.innerHTML = '<i class="bx bx-pencil me-1"></i> Draw Purchase Area';
            drawBtn.className = 'btn btn-sm btn-primary shadow-sm fw-bold d-flex align-items-center gap-1';
          }
          var clearBtn = document.getElementById('btnClearPolygon');
          if (clearBtn) {
            clearBtn.style.display = 'none';
          }

          viewer.scene.requestRender();
        }

        // Wire toolbar buttons
        var drawBtn = document.getElementById('btnDrawPolygon');
        if (drawBtn) {
          drawBtn.addEventListener('click', function() {
            if (isDrawing) {
              clearPolygon();
            } else {
              startDrawing();
            }
          });
        }

        var clearBtn = document.getElementById('btnClearPolygon');
        if (clearBtn) {
          clearBtn.addEventListener('click', function() {
            clearPolygon();
          });
        }

        // Cancel drawing with Escape key
        window.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && isDrawing) {
            clearPolygon();
          }
        });

        function switchTo3D(modelData) {
          if (currentTileset) return; // already loaded
          
          // Prevent duplicate loading from double-clicks or multiple card selections
          if (document.getElementById('mapLoadingIndicator')) {
            return;
          }

          selectedModel = modelData;

          // Check if 3D Tiles URL is defined and valid
          var tilesetUrl = selectedModel ? selectedModel['3dTiles'] : null;
          if (!tilesetUrl || typeof tilesetUrl !== 'string' || tilesetUrl.trim() === '' || tilesetUrl.indexOf('example.com') !== -1) {
            alert('No 3D model is available for this location.');
            selectedModel = null;
            return;
          }

          // Add a loading overlay
          var container = document.getElementById('heroMapContainer');
          var loadingIndicator = document.createElement('div');
          loadingIndicator.id = 'mapLoadingIndicator';
          loadingIndicator.style.position = 'absolute';
          loadingIndicator.style.top = '50%';
          loadingIndicator.style.left = '50%';
          loadingIndicator.style.transform = 'translate(-50%, -50%)';
          loadingIndicator.style.background = 'rgba(26, 26, 46, 0.9)';
          loadingIndicator.style.color = '#fff';
          loadingIndicator.style.padding = '12px 24px';
          loadingIndicator.style.borderRadius = '8px';
          loadingIndicator.style.fontSize = '14px';
          loadingIndicator.style.fontWeight = 'bold';
          loadingIndicator.style.zIndex = '9999';
          loadingIndicator.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
          loadingIndicator.style.border = '1px solid rgba(255,255,255,0.1)';
          loadingIndicator.innerHTML = '<span class="spinner-border spinner-border-sm me-2 text-primary" role="status"></span>Loading 3D Model...';
          container.appendChild(loadingIndicator);

          // Hide all 2D pins
          if (dataSource) {
            dataSource.show = false;
          }

          // Switch scene mode to 3D instantly without animation
          try {
            if (typeof viewer.scene.morphTo3D === 'function') {
              viewer.scene.morphTo3D(0);
              if (typeof viewer.scene.completeMorph === 'function') {
                viewer.scene.completeMorph();
              }
            } else {
              viewer.scene.mode = C.SceneMode.SCENE3D;
            }
          } catch (e) {
            viewer.scene.mode = C.SceneMode.SCENE3D;
          }

          // Load the 3D model (tileset) dynamically
          var tilesetOptions = {};
          if (tilesetUrl.indexOf('geosabah.my') !== -1 || tilesetUrl.indexOf('http') === 0) {
            tilesetOptions.proxy = new C.DefaultProxy('/proxy?url=');
          }

          function handleLoadError(err) {
            console.error('[CesiumMap] Failed to load 3D Tileset:', err);
            
            // Prevent race conditions: if user already reset the view, just return without alert
            if (viewer.scene.mode !== C.SceneMode.SCENE3D || !selectedModel || selectedModel['3dTiles'] !== tilesetUrl) {
              return;
            }

            var indicator = document.getElementById('mapLoadingIndicator');
            if (indicator && indicator.parentNode) {
              indicator.parentNode.removeChild(indicator);
            }
            
            alert('Failed to load the 3D model for this location. Reverting to 2D map.');
            
            // Revert back to 2D
            clearPolygon();
            if (dataSource) {
              dataSource.show = true;
            }
            selectedModel = null;

            // Reset camera orientation with absolute safety timing
            var resetCamera = function() {
              try {
                viewer.camera.lookAtTransform(C.Matrix4.IDENTITY);
              } catch (e) {}
              viewer.camera.setView({
                destination: C.Cartesian3.fromDegrees(116.46905, 5.63444, 710000),
                orientation: {
                  heading: 0.0,
                  pitch: C.Math.toRadians(-90),
                  roll: 0.0
                }
              });
            };

            // If we are morphing or in 3D, listen for morph completion to reset
            if (viewer.scene.mode !== C.SceneMode.SCENE2D && viewer.scene.morphComplete) {
              try {
                var removeListener = viewer.scene.morphComplete.addEventListener(function() {
                  C.requestAnimationFrame(function() {
                    resetCamera();
                    setTimeout(resetCamera, 50);
                    setTimeout(resetCamera, 150);
                  });
                  try { removeListener(); } catch (e) {}
                });
              } catch (e) {}
            }

            // Revert back to 2D instantly
            try {
              if (typeof viewer.scene.morphTo2D === 'function') {
                viewer.scene.morphTo2D(0);
                if (typeof viewer.scene.completeMorph === 'function') {
                  viewer.scene.completeMorph();
                }
              } else {
                viewer.scene.mode = C.SceneMode.SCENE2D;
              }
            } catch (e) {
              viewer.scene.mode = C.SceneMode.SCENE2D;
            }

            document.getElementById('drawingToolbar').style.display = 'none';
            if (orbitBtn) {
              orbitBtn.style.display = 'none';
            }
            
            resetCamera();
            setTimeout(resetCamera, 50);
            setTimeout(resetCamera, 150);
            setTimeout(resetCamera, 300);
            setTimeout(resetCamera, 600);
            setTimeout(resetCamera, 1000);
            viewer.scene.requestRender();
          }

          try {
            C.Cesium3DTileset.fromUrl(new C.Resource({
              url: tilesetUrl,
              proxy: tilesetOptions.proxy
            }))
            .then(function(tileset) {
              // Prevent race conditions: check if the user went back to 2D or selected a different model while this was loading
              if (viewer.scene.mode !== C.SceneMode.SCENE3D || !selectedModel || selectedModel['3dTiles'] !== tilesetUrl) {
                return;
              }

              currentTileset = tileset;
              viewer.scene.primitives.add(tileset);

              // Clean up loader UI
              var indicator = document.getElementById('mapLoadingIndicator');
              if (indicator && indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
              }

              // Show drawing toolbar and orbit button
              document.getElementById('drawingToolbar').style.display = 'flex';
              if (orbitBtn) {
                orbitBtn.style.display = 'flex';
              }

              // Zoom to the 3D tileset with a beautiful camera perspective and start automatic orbit tour
              var boundingSphere = tileset.boundingSphere;
              viewer.camera.flyToBoundingSphere(boundingSphere, {
                offset: new C.HeadingPitchRange(C.Math.toRadians(0), C.Math.toRadians(-18), boundingSphere.radius * 2.2),
                duration: 2.0,
                complete: function() {
                  startOrbit180(boundingSphere.center, boundingSphere.radius);
                }
              });
              viewer.scene.requestRender();
            })
            .catch(function(err) {
              handleLoadError(err);
            });
          } catch (syncErr) {
            handleLoadError(syncErr);
          }
        }

        // Intercept Reset View button click to clean up 3D tileset and switch back to 2D view
        var resetBtn = document.getElementById('purchaseResetViewBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', function() {
            // Cancel any active orbit
            cancelOrbit();
            
            // Cancel any active camera flight
            try {
              viewer.camera.cancelFlight();
            } catch (e) {}

            // Instantly complete any active morph transition before morphing back to 2D
            try {
              if (typeof viewer.scene.completeMorph === 'function') {
                viewer.scene.completeMorph();
              }
            } catch (e) {}

            // Clear drawing toolbar and polygon
            clearPolygon();
            document.getElementById('drawingToolbar').style.display = 'none';
            if (orbitBtn) {
              orbitBtn.style.display = 'none';
            }

            // Reset camera orientation with absolute safety timing
            var resetCamera = function() {
              try {
                viewer.camera.lookAtTransform(C.Matrix4.IDENTITY);
              } catch (e) {}
              viewer.camera.setView({
                destination: C.Cartesian3.fromDegrees(116.46905, 5.63444, 710000),
                orientation: {
                  heading: 0.0,
                  pitch: C.Math.toRadians(-90),
                  roll: 0.0
                }
              });
            };

            // If we are morphing or in 3D, listen for morph completion to reset
            if (viewer.scene.mode !== C.SceneMode.SCENE2D && viewer.scene.morphComplete) {
              try {
                var removeListener = viewer.scene.morphComplete.addEventListener(function() {
                  C.requestAnimationFrame(function() {
                    resetCamera();
                    setTimeout(resetCamera, 50);
                    setTimeout(resetCamera, 150);
                  });
                  try { removeListener(); } catch (e) {}
                });
              } catch (e) {}
            }

            // Restore 2D mode instantly
            try {
              if (typeof viewer.scene.morphTo2D === 'function') {
                viewer.scene.morphTo2D(0);
                if (typeof viewer.scene.completeMorph === 'function') {
                  viewer.scene.completeMorph();
                }
              } else {
                viewer.scene.mode = C.SceneMode.SCENE2D;
              }
            } catch (e) {
              viewer.scene.mode = C.SceneMode.SCENE2D;
            }
            
            // Re-show all 2D pins
            if (dataSource) {
              dataSource.show = true;
            }
            
            // Remove 3D tileset
            if (currentTileset) {
              viewer.scene.primitives.remove(currentTileset);
              currentTileset = null;
            }

            // Also clean up loader UI if user reset while loading
            var indicator = document.getElementById('mapLoadingIndicator');
            if (indicator && indicator.parentNode) {
              indicator.parentNode.removeChild(indicator);
            }
            
            resetCamera();
            setTimeout(resetCamera, 50);
            setTimeout(resetCamera, 150);
            setTimeout(resetCamera, 300);
            setTimeout(resetCamera, 600);
            setTimeout(resetCamera, 1000);

            selectedModel = null;
            viewer.scene.requestRender();
          });
        }
      });

      // 2. Handle Logout Form submit
      function doLogout() {
        document.querySelector('form[action*="logout"]').submit();
      }
      var navLogoutBtn = document.getElementById('navLogoutBtn');
      if (navLogoutBtn) {
        navLogoutBtn.addEventListener('click', function() {
          var modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
          modal.show();
          document.getElementById('logoutConfirmBtn').onclick = function() { doLogout(); };
        });
      }

      // 3. Form Submit handling (Store Quotation)
      document.getElementById('purchaseQuoteForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!selectedModel) {
          alert('Please select a 3D model on the map first by clicking its pin.');
          return;
        }

        if (polygonPoints.length < 3) {
          alert("Please outline the area you wish to purchase. Click 'Draw Purchase Area' and click on the 3D model to draw a polygon. Double-click to complete.");
          return;
        }

        var btnSubmit = document.getElementById('btnSubmitQuotation');
        var originalHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        // Convert Cartesian3 points to standard WGS84 degree coordinates for GeoJSON Polygon format
        var C = Cesium;
        var coords = polygonPoints.map(function(pos) {
          var carto = C.Cartographic.fromCartesian(pos);
          return [
            C.Math.toDegrees(carto.longitude),
            C.Math.toDegrees(carto.latitude)
          ];
        });

        // Close the ring (GeoJSON exterior rings must end at the starting coordinate)
        if (coords.length > 0) {
          coords.push([coords[0][0], coords[0][1]]);
        }

        var areaCoordinatesPayload = {
          type: "Polygon",
          coordinates: [coords]
        };

        // Check if at least one checkbox is checked
        var checkedCategories = ["3D Tiles", "OSGB"];
        document.querySelectorAll('input[name="output_categories[]"]:checked').forEach(function(cb) {
          if (!checkedCategories.includes(cb.value)) {
            checkedCategories.push(cb.value);
          }
        });

        var payload = {
          purchase_id: document.getElementById('purchase_id').value,
          map_data_id: selectedModel.mapDataID,
          output_categories: checkedCategories,
          area_coordinates: areaCoordinatesPayload
        };

        try {
          var res = await fetch('{{ route('purchase_quotation.store') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
          });

          var data = await res.json();
          if (data.success) {
            document.getElementById('successAlert').classList.remove('d-none');
            document.getElementById('purchaseQuoteForm').reset();
            
            document.querySelectorAll('#purchaseQuoteForm input, #purchaseQuoteForm button').forEach(function(el) {
              el.disabled = true;
            });
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            setTimeout(function() {
              window.location.href = '{{ route('purchase_quotation.my') }}';
            }, 3000);
          } else {
            alert('Error: ' + (data.message || 'Failed to submit quotation.'));
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;
          }
        } catch (err) {
          console.error(err);
          alert('Server error occurred during submission.');
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = originalHtml;
        }
      });

    })();
  </script>
</body>
</html>
