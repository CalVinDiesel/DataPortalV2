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

  <!-- Sections: Start -->
  <section class="section-py bg-body first-section-pt">
    <div class="container py-5">
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
                        <div class="el-tooltip__trigger" id="resetViewBtn" title="Reset View">
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
  </section>
  <!-- Sections: End -->

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
      var allModelEntities = {}; // Map of mapDataID -> Cesium entity

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
        
        // Helper function to generate a premium bordered square pin dynamically, falling back to a CSS styled pin
        function makePinImage(imageUrl, size, border, title, callback) {
          var abbreviation = (title || '3D').substring(0, 2).toUpperCase();
          if (!imageUrl) {
            drawFallback();
            return;
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
        var mapLocations = @json($mapLocations);
        var currentTileset = null;

        mapLocations.forEach(function(loc) {
          var pinCoords = C.Cartesian3.fromDegrees(Number(loc.xAxis), Number(loc.yAxis), 0);
          var pinUrl = loc.thumbNailUrl || '';
          
          var pinEntity = viewer.entities.add({
            id: loc.mapDataID,
            name: loc.title,
            position: pinCoords,
            billboard: {
              image: pinUrl || null,
              width: 54,
              height: 54,
              verticalOrigin: C.VerticalOrigin.BOTTOM,
              disableDepthTestDistance: Number.POSITIVE_INFINITY
            },
            label: {
              text: loc.title,
              font: 'bold 12px "Public Sans", sans-serif',
              style: C.LabelStyle.FILL_AND_OUTLINE,
              fillColor: C.Color.WHITE,
              outlineColor: C.Color.fromCssColorString('#1a1a2e'),
              outlineWidth: 3,
              verticalOrigin: C.VerticalOrigin.BOTTOM,
              pixelOffset: new C.Cartesian2(0, -62),
              disableDepthTestDistance: Number.POSITIVE_INFINITY
            }
          });
          
          pinEntity.modelData = loc;
          allModelEntities[loc.mapDataID] = pinEntity;
          
          // Generate bordered version dynamically
          makePinImage(pinUrl, 48, 3, loc.title, function(dataUrl) {
            pinEntity.billboard.image = dataUrl;
            viewer.scene.requestRender();
          });
        });

        // Force an initial render since requestRenderMode is true
        viewer.scene.requestRender();
        
        // Click Handler: Transition to 3D and Load tileset dynamically
        var handler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
        handler.setInputAction(function(click) {
          var pickedObject = viewer.scene.pick(click.position);
          if (C.defined(pickedObject) && pickedObject.id && pickedObject.id.modelData) {
            switchTo3D(pickedObject.id.modelData);
          }
        }, C.ScreenSpaceEventType.LEFT_CLICK);

        // Hover Handler: Cursor style change
        handler.setInputAction(function(movement) {
          var pickedObject = viewer.scene.pick(movement.endPosition);
          if (C.defined(pickedObject) && pickedObject.id && pickedObject.id.modelData) {
            viewer.canvas.style.cursor = 'pointer';
          } else {
            if (!isDrawing && draggedVertexIndex === null) {
              viewer.canvas.style.cursor = '';
            }
          }
        }, C.ScreenSpaceEventType.MOUSE_MOVE);

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

          selectedModel = modelData;

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
          Object.keys(allModelEntities).forEach(function(id) {
            allModelEntities[id].show = false;
          });

          // Switch scene mode to 3D
          viewer.scene.mode = C.SceneMode.SCENE3D;

          // Load the 3D model (tileset) dynamically
          var tilesetUrl = selectedModel['3dTiles'];
          var tilesetOptions = {};
          if (tilesetUrl.indexOf('geosabah.my') !== -1 || tilesetUrl.indexOf('http') === 0) {
            tilesetOptions.proxy = new C.DefaultProxy('/proxy?url=');
          }

          C.Cesium3DTileset.fromUrl(new C.Resource({
            url: tilesetUrl,
            proxy: tilesetOptions.proxy
          }))
          .then(function(tileset) {
            currentTileset = tileset;
            viewer.scene.primitives.add(tileset);

            // Clean up loader UI
            var indicator = document.getElementById('mapLoadingIndicator');
            if (indicator) indicator.remove();

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
            console.error('[CesiumMap] Failed to load 3D Tileset:', err);
            var indicator = document.getElementById('mapLoadingIndicator');
            if (indicator) {
              indicator.innerHTML = '<span class="text-danger"><i class="bx bx-error me-1"></i>Error loading 3D Model</span>';
              setTimeout(function() { indicator.remove(); }, 3000);
            }
            
            // Revert back to 2D
            clearPolygon();
            Object.keys(allModelEntities).forEach(function(id) {
              allModelEntities[id].show = true;
            });
            selectedModel = null;
            viewer.scene.mode = C.SceneMode.SCENE2D;
            document.getElementById('drawingToolbar').style.display = 'none';
            if (orbitBtn) {
              orbitBtn.style.display = 'none';
            }
            viewer.scene.requestRender();
          });
        }

        // Intercept Reset View button click to clean up 3D tileset and switch back to 2D view
        var resetBtn = document.getElementById('resetViewBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', function() {
            // Cancel any active orbit
            cancelOrbit();
            
            // Clear drawing toolbar and polygon
            clearPolygon();
            document.getElementById('drawingToolbar').style.display = 'none';
            if (orbitBtn) {
              orbitBtn.style.display = 'none';
            }

            // Restore 2D mode
            viewer.scene.mode = C.SceneMode.SCENE2D;
            
            // Re-show all 2D pins
            Object.keys(allModelEntities).forEach(function(id) {
              allModelEntities[id].show = true;
            });
            
            // Remove 3D tileset
            if (currentTileset) {
              viewer.scene.primitives.remove(currentTileset);
              currentTileset = null;
            }
            
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
