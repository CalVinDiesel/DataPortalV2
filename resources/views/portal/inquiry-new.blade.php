<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>New Inquiry | 3DHub Data Portal</title>
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
      #navInquiry:hover .dropdown-menu,
      #navUpload:hover .dropdown-menu {
        display: block;
        margin-top: 0;
      }
    }
    
    /* Hover descriptions for output formats */
    .format-option-wrapper {
      position: relative;
      border-radius: 8px;
      padding: 0.5rem 0.75rem 0.5rem 2.25rem;
      margin-bottom: 0.75rem !important;
      border: 1px solid transparent;
      transition: all 0.2s ease-in-out;
    }
    .format-option-wrapper:hover {
      background-color: rgba(105, 108, 255, 0.04);
      border-color: rgba(105, 108, 255, 0.08);
    }
    .format-desc-text {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      font-size: 11.5px;
      line-height: 1.5;
      color: var(--bs-secondary-color);
      transition: max-height 0.3s ease-out, opacity 0.3s ease-out, margin-top 0.2s ease-out;
      margin-top: 0;
      background-color: var(--bs-body-bg);
      border-radius: 6px;
      padding: 0;
      margin-left: -1.5rem;
    }
    .format-option-wrapper:hover .format-desc-text {
      max-height: 150px;
      opacity: 1;
      margin-top: 0.5rem;
      padding: 0.6rem 0.8rem;
      border: 1px solid rgba(105, 108, 255, 0.15);
      background-color: rgba(105, 108, 255, 0.03);
    }
    .format-option-wrapper:hover .info-icon {
      color: #696cff !important;
      transform: scale(1.15);
    }
    .info-icon {
      transition: all 0.2s ease;
      cursor: help;
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
            <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub Beta</span>
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
            <!-- Inquiry Dropdown for Desktop -->
            <li class="nav-item dropdown d-none d-xl-block" id="navInquiry">
              <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" aria-expanded="false" data-bs-toggle="dropdown" data-trigger="hover">
                Inquiry
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('inquiry.new') }}">New Inquiry</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('inquiry.my') }}">My Inquiry</a></li>
              </ul>
            </li>
            <!-- Inquiry Dropdown for Mobile -->
            <li class="nav-item d-xl-none navInquiry-mobile">
              <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navInquiryCollapse" aria-expanded="false" aria-controls="navInquiryCollapse" id="navInquiryMobileToggle">
                Inquiry
              </a>
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
          <h1><i class="bx bx-file-blank me-2"></i>New Inquiry</h1>
          <p>Specify your required output formats and select your inquiry area on the map</p>
        </div>
        <a href="{{ route('inquiry.my') }}" class="btn-new">
          <i class="bx bx-list-ul"></i> My Inquiries
        </a>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="pq-content">
    <div class="container">
      <div class="card shadow-sm border">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-4 px-4 px-md-5">
          <h4 class="m-0 fw-bold"><i class="bx bx-file-blank me-2 text-primary"></i>Send Inquiry</h4>
          <span class="badge bg-label-primary">New Inquiry</span>
        </div>
        <div class="card-body p-4 p-md-5">
          
          <div id="successAlert" class="alert alert-success d-none mb-4" role="alert">
            <div class="d-flex">
              <i class="bx bx-check-circle me-2 fs-4"></i>
              <div>
                <h6 class="alert-heading mb-1 fw-bold">Inquiry Sent Successfully!</h6>
                <span>Your inquiry request has been recorded. We will review the details and get back to you shortly.</span>
              </div>
            </div>
          </div>

          <form id="inquiryForm" novalidate>
            <div class="row">
              <!-- Left Form Column -->
              <div class="col-lg-5">
                <div class="form-section-title mt-0">Inquiry Details</div>
                
                <!-- Field 1: Auto Generated Inquiry ID -->
                <div class="mb-4">
                  <label class="form-label fw-semibold" for="inquiry_id">Inquiry ID</label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-hash"></i></span>
                    <input type="text" id="inquiry_id" name="inquiry_id" class="form-control fw-bold text-primary" value="{{ $inquiryId }}" readonly style="background-color: var(--bs-tertiary-bg);">
                  </div>
                  <div class="form-text">This unique ID is auto-generated and will be saved upon submission.</div>
                </div>

                <!-- Field 2: Output Categories (The 5 3D model formats) -->
                <div class="mb-4">
                  <label class="form-label fw-semibold d-block">Required Output Formats <span class="text-danger">*</span></label>
                  <div class="form-text mb-2">Select the 3D model formats you would like to include in your quotation.</div>
                  
                  <div class="form-check format-option-wrapper">
                    <input class="form-check-input" type="checkbox" id="cat3dTiles" value="3D Tiles" checked disabled>
                    <label class="form-check-label fw-semibold text-heading d-inline-flex align-items-center" for="cat3dTiles">
                      3D Tiles (Default) <i class="bx bx-info-circle text-muted ms-1.5 fs-6 info-icon"></i>
                    </label>
                    <div class="format-desc-text">
                      <strong>Best for websites.</strong> It streams massive 3D maps smoothly in your web browser without lagging or crashing your computer.
                    </div>
                  </div>
                  
                  <div class="form-check format-option-wrapper">
                    <input class="form-check-input" type="checkbox" id="catOSGB" value="OSGB" checked disabled>
                    <label class="form-check-label fw-semibold text-heading d-inline-flex align-items-center" for="catOSGB">
                      OSGB (Default) <i class="bx bx-info-circle text-muted ms-1.5 fs-6 info-icon"></i>
                    </label>
                    <div class="format-desc-text">
                      <strong>High-precision 3D mesh models.</strong> It acts as a highly detailed digital twin of real-world environments, perfect for professional engineering and surveying software.
                    </div>
                  </div>
                  
                  <div class="form-check format-option-wrapper">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="catDSM" value="DSM">
                    <label class="form-check-label fw-semibold text-heading d-inline-flex align-items-center" for="catDSM">
                      DSM <i class="bx bx-info-circle text-muted ms-1.5 fs-6 info-icon"></i>
                    </label>
                    <div class="format-desc-text">
                      <strong>Digital Surface Model.</strong> A top-down height map. It captures the exact elevation of everything on the ground, including the tops of buildings and trees.
                    </div>
                  </div>
                  
                  <div class="form-check format-option-wrapper">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="cat3DGS" value="3DGS">
                    <label class="form-check-label fw-semibold text-heading d-inline-flex align-items-center" for="cat3DGS">
                      3DGS <i class="bx bx-info-circle text-muted ms-1.5 fs-6 info-icon"></i>
                    </label>
                    <div class="format-desc-text">
                      <strong>3D Gaussian Splatting.</strong> Next-generation, hyper-realistic 3D viewing. It captures reflections, lighting, and complex details to make the 3D scene look exactly like a real photo.
                    </div>
                  </div>
                  
                  <div class="form-check format-option-wrapper">
                    <input class="form-check-input" type="checkbox" name="output_categories[]" id="catOrthophoto" value="Orthophoto">
                    <label class="form-check-label fw-semibold text-heading d-inline-flex align-items-center" for="catOrthophoto">
                      Orthophoto <i class="bx bx-info-circle text-muted ms-1.5 fs-6 info-icon"></i>
                    </label>
                    <div class="format-desc-text">
                      <strong>Orthophoto.</strong> A perfectly flattened, distortion-free top-down map. Every measurement on this aerial photo is 100% accurate to real-world dimensions.
                    </div>
                  </div>
                </div>

                <div id="areaCalcBox" class="mb-4 p-3 rounded d-none" style="background-color: rgba(105, 108, 255, 0.05); border: 1.5px solid rgba(105, 108, 255, 0.2);">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold text-muted">Drawn Area:</span>
                    <strong class="text-primary" id="calcAreaVal" style="font-size: 13.5px;">0.00 m²</strong>
                  </div>
                </div>

                <!-- Pricing Guidance and Important Notes -->
                {{--
                <div class="alert alert-info border-info p-3" role="alert" style="background-color: rgba(105, 108, 255, 0.03); border: 1.5px solid rgba(105, 108, 255, 0.15); border-radius: 8px;">
                  <h6 class="alert-heading mb-2 fw-bold text-primary" style="display: flex; align-items: center; gap: 0.4rem; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bx bx-info-circle fs-5"></i> 3D Model Pricing Notice
                  </h6>
                  <ul class="ps-3 mb-0 small text-muted" style="font-size: 11.5px; line-height: 1.5; list-style-type: decimal;">
                    <li class="mb-2">
                      <strong class="text-dark">Different Year/Capture Pricing:</strong> The same 3D model captured in different years will have different prices. A more recent capture is more expensive than older ones.
                    </li>
                    <li>
                      <strong class="text-dark">Custom/Larger Area Requests:</strong> You can request a 3D model area larger than the boundaries shown on the map. This custom service is more expensive because it requires deploying a drone to capture the area specifically for you.
                    </li>
                  </ul>
                </div>
                --}}
              </div>

              <!-- Right Map Column -->
              <div class="col-lg-7">
                <div class="form-section-title mt-0 mt-lg-0">Select Inquiry Area</div>
                <div class="form-text mb-2">Use the Cesium map viewer to specify the area coordinates for your inquiry.</div>
                
                <!-- Field 3: Cesium Ion Map -->
                <div id="heroMapContainer" style="position: relative;">
                  <div id="cesiumContainer"></div>
                  <!-- Drawing Toolbar (always visible) -->
                  <div id="drawingToolbar" style="position: absolute; top: 12px; left: 12px; z-index: 1000; display: flex; gap: 8px;">
                    <button type="button" id="btnDrawPolygon" class="btn btn-sm btn-primary shadow-sm fw-bold d-flex align-items-center gap-1" style="border-radius: 8px; padding: 8px 14px; backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                      <i class="bx bx-pencil me-1"></i> Draw Inquiry Area
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
                        <div class="el-tooltip__trigger" id="inquiryResetViewBtn" title="Reset View">
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
                <!-- Send Inquiry Button on the bottom right -->
                <button type="submit" id="btnSubmitQuotation" class="btn btn-primary px-5 fw-bold">
                  <i class="bx bx-send me-1"></i> Send Inquiry
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

  <!-- Initialize Cesium map using common script files -->
  <script>
    window.cesiumDefaultSceneMode = Cesium.SceneMode.SCENE3D;
  </script>
  <script src="{{ asset('assets') }}/js/cesium-map.js?v={{ time() }}"></script>
  <script src="{{ asset('assets') }}/js/cesium-map-controls.js?v={{ time() }}"></script>
  <script>
    (function() {
      var C = Cesium;
      if (typeof initializeCesium === 'function' && !window.cesiumViewer) {
        try {
          initializeCesium();
        } catch (e) {
          console.error("Manual Cesium initialization failed:", e);
        }
      }

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

      var selectedModel = null;
      var dataSource = null;

      var isDrawing = false;
      var polygonPoints = [];
      var drawingEntities = [];
      var finalPolygonEntity = null;
      var editVertexEntities = [];
      var activeDrawingPreview = null;
      var mousePosition = null;
      
      var drawingHandler = null;
      var editHandler = null;
      var draggedVertexIndex = null;
      var draggedVertexEntity = null;

      getViewer(function(viewer) {
        viewer.camera.setView({
          destination: C.Cartesian3.fromDegrees(116.082, 5.975, 15000),
          orientation: {
            heading: 0.0,
            pitch: C.Math.toRadians(-90),
            roll: 0.0
          }
        });

        function calculatePolygonArea(coords) {
          if (coords.length < 3) return 0;
          var baseLat = coords[0][1];
          var cosLat = Math.cos(baseLat * Math.PI / 180.0);
          var metersX = [];
          var metersY = [];
          for (var i = 0; i < coords.length; i++) {
            metersX.push(coords[i][0] * 111320.0 * cosLat);
            metersY.push(coords[i][1] * 111320.0);
          }
          var area = 0.0;
          var j = coords.length - 1;
          for (var i = 0; i < coords.length; i++) {
            area += (metersX[j] + metersX[i]) * (metersY[j] - metersY[i]);
            j = i;
          }
          return Math.abs(area / 2.0);
        }

        window.updateCalculatedArea = function() {
          if (polygonPoints.length < 3) {
            var calcBox = document.getElementById('areaCalcBox');
            if (calcBox) calcBox.classList.add('d-none');
            return;
          }
          
          var coords = polygonPoints.map(function(pos) {
            var carto = C.Cartographic.fromCartesian(pos);
            return [
              C.Math.toDegrees(carto.longitude),
              C.Math.toDegrees(carto.latitude)
            ];
          });
          
          var areaM2 = calculatePolygonArea(coords);
          
          var calcBox = document.getElementById('areaCalcBox');
          var areaVal = document.getElementById('calcAreaVal');
          
          if (calcBox) calcBox.classList.remove('d-none');
          if (areaVal) areaVal.textContent = areaM2.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m²';
        };

        var rawLocations = @json($mapLocations);
        var locations = rawLocations.map(function(loc) {
          return {
            id: loc.mapDataID,
            name: loc.title,
            longitude: Number(loc.xAxis),
            latitude: Number(loc.yAxis),
            originalData: loc
          };
        }).filter(function(loc) {
          var tilesetUrl = loc.originalData['3dTiles'];
          return tilesetUrl && typeof tilesetUrl === 'string' && tilesetUrl.trim() !== '' && tilesetUrl.indexOf('example.com') === -1;
        });

        locations.forEach(function(loc) {
          var tilesetUrl = loc.originalData['3dTiles'];
          var tilesetOptions = {};
          if (tilesetUrl.indexOf('geosabah.my') !== -1 || tilesetUrl.indexOf('http') === 0) {
            tilesetOptions.proxy = new C.DefaultProxy('/proxy?url=');
          }
          try {
            C.Cesium3DTileset.fromUrl(new C.Resource({
              url: tilesetUrl,
              proxy: tilesetOptions.proxy
            })).then(function(tileset) {
              viewer.scene.primitives.add(tileset);

              var center = tileset.boundingSphere ? tileset.boundingSphere.center : null;
              var position = null;
              
              if (center && !(center.x === 0 && center.y === 0 && center.z === 0)) {
                position = center;
                var carto = C.Cartographic.fromCartesian(center);
                loc.longitude = C.Math.toDegrees(carto.longitude);
                loc.latitude = C.Math.toDegrees(carto.latitude);
              } else {
                position = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude);
              }
            }).catch(function(err) {
              console.error("Failed to load 3D Tileset for " + loc.name, err);
            });
          } catch(e) {
            console.error("Error creating 3D Tileset or Label for " + loc.name, e);
          }
        });

        function updateSelectedModelFromPolygon() {
          if (polygonPoints.length < 3) {
            selectedModel = null;
            return;
          }
          
          var sumLon = 0, sumLat = 0;
          polygonPoints.forEach(function(pos) {
            var carto = C.Cartographic.fromCartesian(pos);
            sumLon += C.Math.toDegrees(carto.longitude);
            sumLat += C.Math.toDegrees(carto.latitude);
          });
          var centroidLon = sumLon / polygonPoints.length;
          var centroidLat = sumLat / polygonPoints.length;
          
          var closestLoc = null;
          var minDist = Infinity;
          
          locations.forEach(function(loc) {
            var dx = loc.longitude - centroidLon;
            var dy = loc.latitude - centroidLat;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < minDist) {
              minDist = dist;
              closestLoc = loc;
            }
          });
          
          if (closestLoc) {
            selectedModel = closestLoc.originalData;
          } else {
            selectedModel = null;
          }
        }

        function cancelOrbit() {}

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
                  classificationType: C.ClassificationType.BOTH
                }
              });
              drawingEntities.push(activeDrawingPreview);
            }
          }, C.ScreenSpaceEventType.LEFT_CLICK);

          drawingHandler.setInputAction(function() {
            if (polygonPoints.length >= 3) {
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

          drawingEntities.forEach(function(ent) {
            viewer.entities.remove(ent);
          });
          drawingEntities = [];

          var drawBtn = document.getElementById('btnDrawPolygon');
          if (drawBtn) drawBtn.style.display = 'none';
          var clearBtn = document.getElementById('btnClearPolygon');
          if (clearBtn) clearBtn.style.display = 'flex';

          finalPolygonEntity = viewer.entities.add({
            polygon: {
              hierarchy: new C.CallbackProperty(function() {
                return new C.PolygonHierarchy(polygonPoints);
              }, false),
              material: C.Color.fromCssColorString('#696cff').withAlpha(0.4),
              outline: true,
              outlineColor: C.Color.WHITE,
              outlineWidth: 2,
              classificationType: C.ClassificationType.BOTH
            }
          });

          if (typeof window.updateCalculatedArea === 'function') {
            window.updateCalculatedArea();
          }

          updateSelectedModelFromPolygon();

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

          editHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);

          editHandler.setInputAction(function(click) {
            var picked = viewer.scene.pick(click.position);
            if (C.defined(picked) && picked.id && picked.id.isVertex) {
              draggedVertexIndex = picked.id.vertexIndex;
              draggedVertexEntity = picked.id;
              viewer.scene.screenSpaceCameraController.enableInputs = false;
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
              if (typeof window.updateCalculatedArea === 'function') {
                window.updateCalculatedArea();
              }
              updateSelectedModelFromPolygon();
            }
          }, C.ScreenSpaceEventType.MOUSE_MOVE);

          editHandler.setInputAction(function() {
            if (draggedVertexIndex !== null) {
              draggedVertexIndex = null;
              draggedVertexEntity = null;
              viewer.scene.screenSpaceCameraController.enableInputs = true;
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
          selectedModel = null;
          isDrawing = false;
          viewer.canvas.style.cursor = '';
          var calcBox = document.getElementById('areaCalcBox');
          if (calcBox) calcBox.classList.add('d-none');

          var drawBtn = document.getElementById('btnDrawPolygon');
          if (drawBtn) {
            drawBtn.style.display = 'flex';
            drawBtn.innerHTML = '<i class="bx bx-pencil me-1"></i> Draw Inquiry Area';
            drawBtn.className = 'btn btn-sm btn-primary shadow-sm fw-bold d-flex align-items-center gap-1';
          }
          var clearBtn = document.getElementById('btnClearPolygon');
          if (clearBtn) {
            clearBtn.style.display = 'none';
          }

          viewer.scene.requestRender();
        }

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

        window.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && isDrawing) {
            clearPolygon();
          }
        });

        var resetBtn = document.getElementById('inquiryResetViewBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', function() {
            try {
              viewer.camera.cancelFlight();
            } catch (e) {}

            clearPolygon();

            try {
              viewer.camera.lookAtTransform(C.Matrix4.IDENTITY);
            } catch (e) {}
            viewer.camera.setView({
              destination: C.Cartesian3.fromDegrees(116.082, 5.975, 15000),
              orientation: {
                heading: 0.0,
                pitch: C.Math.toRadians(-90),
                roll: 0.0
              }
            });
            viewer.scene.requestRender();
          });
        }
      });

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

      document.getElementById('inquiryForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!selectedModel) {
          alert('Please draw a polygon over one of the 3D models on the map first.');
          return;
        }

        if (polygonPoints.length < 3) {
          alert("Please outline the area you wish to purchase. Click 'Draw Inquiry Area' and click on the 3D model to draw a polygon. Double-click to complete.");
          return;
        }

        var btnSubmit = document.getElementById('btnSubmitQuotation');
        var originalHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        var C = Cesium;
        var coords = polygonPoints.map(function(pos) {
          var carto = C.Cartographic.fromCartesian(pos);
          return [
            C.Math.toDegrees(carto.longitude),
            C.Math.toDegrees(carto.latitude)
          ];
        });

        if (coords.length > 0) {
          coords.push([coords[0][0], coords[0][1]]);
        }

        var areaCoordinatesPayload = {
          type: "Polygon",
          coordinates: [coords]
        };

        var checkedCategories = ["3D Tiles", "OSGB"];
        document.querySelectorAll('input[name="output_categories[]"]:checked').forEach(function(cb) {
          if (!checkedCategories.includes(cb.value)) {
            checkedCategories.push(cb.value);
          }
        });

        var payload = {
          inquiry_id: document.getElementById('inquiry_id').value,
          map_data_id: selectedModel.mapDataID,
          output_categories: checkedCategories,
          area_coordinates: areaCoordinatesPayload
        };

        try {
          var res = await fetch('{{ route('inquiry.store') }}', {
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
            document.getElementById('inquiryForm').reset();
            
            document.querySelectorAll('#inquiryForm input, #inquiryForm button').forEach(function(el) {
              el.disabled = true;
            });
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            setTimeout(function() {
              window.location.href = '{{ route('inquiry.my') }}';
            }, 3000);
          } else {
            alert('Error: ' + (data.message || 'Failed to submit inquiry.'));
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
