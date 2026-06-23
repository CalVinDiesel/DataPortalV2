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
                <div id="heroMapContainer">
                  <div id="cesiumContainer"></div>
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
      getViewer(function(viewer) {
        var C = Cesium;
        
        // Helper function to generate a premium bordered square pin dynamically, falling back to a CSS styled pin
        function makePinImage(imageUrl, size, border, callback) {
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
              callback(imageUrl);
            }
          };
          img.onerror = function() {
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
              
              // KK abbreviation
              ctx.fillStyle = '#ffffff';
              ctx.font = 'bold ' + Math.round(size * 0.4) + 'px sans-serif';
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';
              ctx.fillText('KK', canvas.width / 2, canvas.height / 2);
              callback(canvas.toDataURL('image/png'));
            } catch (err) {
              callback(imageUrl);
            }
          };
          img.src = imageUrl;
        }

        // 1. Create a Pin/Billboard for KK Osprey on the 2D map (at height 0 so it aligns correctly)
        var kkOspreyCoords = C.Cartesian3.fromDegrees(116.070466, 5.957839, 0);
        var currentTileset = null;
        var defaultPinUrl = "{{ asset('assets/img/front-pages/locations/kkosprey_pin_image.jpg') }}";
        
        var kkOspreyEntity = viewer.entities.add({
          id: 'KK_OSPREY',
          name: 'KK OSPREY',
          position: kkOspreyCoords,
          billboard: {
            image: defaultPinUrl,
            width: 54,
            height: 54,
            verticalOrigin: C.VerticalOrigin.BOTTOM,
            disableDepthTestDistance: Number.POSITIVE_INFINITY
          },
          label: {
            text: 'KK OSPREY',
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
        
        // Dynamically load the bordered version and update the billboard
        makePinImage(defaultPinUrl, 48, 3, function(dataUrl) {
          kkOspreyEntity.billboard.image = dataUrl;
          viewer.scene.requestRender();
        });

        // Force an initial render since requestRenderMode is true
        viewer.scene.requestRender();
        
        // 2. Click Handler: Transition to 3D and Load tileset
        var handler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
        handler.setInputAction(function(click) {
          var pickedObject = viewer.scene.pick(click.position);
          if (C.defined(pickedObject) && pickedObject.id === kkOspreyEntity) {
            switchTo3D();
          }
        }, C.ScreenSpaceEventType.LEFT_CLICK);

        // Hover Handler: Cursor style change
        handler.setInputAction(function(movement) {
          var pickedObject = viewer.scene.pick(movement.endPosition);
          if (C.defined(pickedObject) && pickedObject.id === kkOspreyEntity) {
            viewer.canvas.style.cursor = 'pointer';
          } else {
            viewer.canvas.style.cursor = '';
          }
        }, C.ScreenSpaceEventType.MOUSE_MOVE);

        function switchTo3D() {
          if (currentTileset) return; // already loaded

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

          // Hide the 2D pin
          kkOspreyEntity.show = false;

          // Switch scene mode to 3D
          viewer.scene.mode = C.SceneMode.SCENE3D;

          // Load the 3D model (tileset)
          var tilesetUrl = 'https://3dhub.geosabah.my/3dmodel/KK_OSPREY/tileset.json';
          C.Cesium3DTileset.fromUrl(new C.Resource({
            url: tilesetUrl,
            proxy: new C.DefaultProxy('/proxy?url=')
          }))
          .then(function(tileset) {
            currentTileset = tileset;
            viewer.scene.primitives.add(tileset);

            // Clean up loader UI
            var indicator = document.getElementById('mapLoadingIndicator');
            if (indicator) indicator.remove();

            // Zoom to the 3D tileset with a beautiful camera perspective
            var boundingSphere = tileset.boundingSphere;
            viewer.camera.flyToBoundingSphere(boundingSphere, {
              offset: new C.HeadingPitchRange(C.Math.toRadians(0), C.Math.toRadians(-35), boundingSphere.radius * 2.5),
              duration: 2.0
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
            kkOspreyEntity.show = true;
            viewer.scene.mode = C.SceneMode.SCENE2D;
            viewer.scene.requestRender();
          });
        }

        // Intercept Reset View button click to clean up 3D tileset and switch back to 2D view
        var resetBtn = document.getElementById('resetViewBtn');
        if (resetBtn) {
          resetBtn.addEventListener('click', function() {
            // Restore 2D mode
            viewer.scene.mode = C.SceneMode.SCENE2D;
            
            // Re-show 2D pin
            kkOspreyEntity.show = true;
            
            // Remove 3D tileset
            if (currentTileset) {
              viewer.scene.primitives.remove(currentTileset);
              currentTileset = null;
            }
            viewer.scene.requestRender();
          });
        }
      });
  </script>

  <script>
    // 2. Handle Logout Form submit
    (function() {
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
    })();

    // 3. Form Submit handling (Store Quotation)
    document.getElementById('purchaseQuoteForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      // Check if at least one checkbox is checked
      var checkedCategories = ["3D Tiles", "OSGB"];
      document.querySelectorAll('input[name="output_categories[]"]:checked').forEach(function(cb) {
        if (!checkedCategories.includes(cb.value)) {
          checkedCategories.push(cb.value);
        }
      });

      var btnSubmit = document.getElementById('btnSubmitQuotation');
      var originalHtml = btnSubmit.innerHTML;
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

      // For now, area coordinates placeholder (since logic will be added later)
      var areaCoordinatesPlaceholder = {
        center: [5.9804, 116.0735],
        zoom: 11
      };

      var payload = {
        purchase_id: document.getElementById('purchase_id').value,
        output_categories: checkedCategories,
        area_coordinates: areaCoordinatesPlaceholder
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
          // Show success message and reset form or disable
          document.getElementById('successAlert').classList.remove('d-none');
          document.getElementById('purchaseQuoteForm').reset();
          
          // Disable form controls
          document.querySelectorAll('#purchaseQuoteForm input, #purchaseQuoteForm button').forEach(function(el) {
            el.disabled = true;
          });
          
          // Smooth scroll to top of page to see success alert
          window.scrollTo({ top: 0, behavior: 'smooth' });
          
          // Redirect to my-quotations page after 3 seconds
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
  </script>
</body>
</html>
