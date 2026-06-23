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
    
    #cesiumMapContainer {
      height: 420px;
      width: 100%;
      border-radius: 12px;
      border: 1px solid var(--bs-border-color);
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
                <div id="cesiumMapContainer"></div>
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

  <script>
    // 1. Initialize Cesium Ion Map (Empty / Default View first)
    var viewer;
    try {
      viewer = new Cesium.Viewer('cesiumMapContainer', {
        terrainProvider: Cesium.createWorldTerrain ? Cesium.createWorldTerrain() : undefined,
        baseLayerPicker: true,
        geocoder: true,
        homeButton: true,
        infoBox: false,
        navigationHelpButton: false,
        sceneModePicker: true,
        timeline: false,
        animation: false,
        fullscreenButton: true,
        selectionIndicator: false
      });
      
      // Zoom to Sabah/Malaysia area default coordinates
      viewer.camera.setView({
        destination: Cesium.Cartesian3.fromDegrees(116.0735, 5.9804, 15000.0)
      });
    } catch (e) {
      console.error("Cesium Map failed to load:", e);
      document.getElementById('cesiumMapContainer').innerHTML = 
        '<div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted"><p class="m-0"><i class="bx bx-error me-1"></i>Failed to load Cesium Map. Check your internet connection or console errors.</p></div>';
    }

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
