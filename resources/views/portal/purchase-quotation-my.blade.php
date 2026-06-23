<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>My Purchase Quotations | 3DHub Data Portal</title>
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
      #navPurchaseQuotation:hover .dropdown-menu,
      #navUpload:hover .dropdown-menu {
        display: block;
        margin-top: 0;
      }
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
          <h4 class="m-0 fw-bold"><i class="bx bx-list-ul me-2 text-primary"></i>My Purchase Quotations</h4>
          <a href="{{ route('purchase_quotation.new') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>New Quotation</a>
        </div>
        <div class="card-body p-0">
          @if($quotations->isEmpty())
            <div class="text-center py-5 px-4">
              <i class="bx bx-file-blank display-3 text-muted mb-3"></i>
              <h5 class="fw-semibold">No purchase quotations yet</h5>
              <p class="text-muted mb-4">You have not submitted any purchase quotation requests. Click below to create one.</p>
              <a href="{{ route('purchase_quotation.new') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>New Purchase Quotation</a>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="py-3 ps-4 px-md-5">Purchase ID</th>
                    <th class="py-3">Date Requested</th>
                    <th class="py-3">Output Formats</th>
                    <th class="py-3 pe-4 px-md-5 text-end">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($quotations as $quote)
                    <tr>
                      <td class="fw-bold text-primary ps-4 px-md-5">{{ $quote->purchase_id }}</td>
                      <td>{{ $quote->created_at->format('d M Y, h:i A') }}</td>
                      <td>
                        @if(is_array($quote->output_categories))
                          @foreach($quote->output_categories as $cat)
                            <span class="badge bg-label-secondary me-1">{{ $cat }}</span>
                          @endforeach
                        @else
                          <span class="badge bg-label-secondary">{{ $quote->output_categories }}</span>
                        @endif
                      </td>
                      <td class="pe-4 px-md-5 text-end">
                        @if($quote->status === 'pending')
                          <span class="badge bg-label-warning"><i class="bx bx-time-five me-1"></i>Pending Review</span>
                        @elseif($quote->status === 'processed')
                          <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Processed</span>
                        @else
                          <span class="badge bg-label-info">{{ ucfirst($quote->status) }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
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
    // Handle Logout Form submit
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
  </script>
</body>
</html>
