<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets/') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>New Project | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/') }}/img/favicon/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/pages/front-page.css">
  <script src="{{ asset('assets/') }}/js/theme-init.js"></script>
  <script>
    (function () {
      window.userRole = '{{ Auth::user()->role }}';
      // If role is registered, show alert on SFTP click (handled by separate script below)
    })();
  </script>

<style>
  .selection-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    height: 100%;
  }
  .selection-card:hover {
    transform: translateY(-10px);
    border-color: #696cff;
    box-shadow: 0 10px 30px rgba(105, 108, 255, 0.15);
  }

  /* ✅ Light mode hero background */
  .hero-section {
    background: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
    padding: 100px 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }

  /* ✅ Dark mode hero background — overrides when data-bs-theme="dark" is on <html> */
  [data-bs-theme="dark"] .hero-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  }

  /* ✅ Dark mode card text fix */
  [data-bs-theme="dark"] .selection-card .text-muted {
    color: var(--bs-secondary-color) !important;
  }
</style>
</head>
<body>
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-lg landing-navbar px-3">
        <a href="{{ route('landing') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets/') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;">
          </span>
          <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
          <!-- Style Switcher -->
          <ul class="navbar-nav flex-row align-items-center">
            <li class="nav-item dropdown-style-switcher dropdown me-2">
              <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                <li>
                  <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light">
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
          </ul>
          <!-- / Style Switcher -->
          <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm">Back to Home</a>
        </div>
      </div>
    </div>
  </nav>

  <section class="hero-section">
    <div class="container">
      <div class="text-center mb-10">
        <h2 class="display-5 fw-bold mb-3">Create New Project</h2>
        <p class="lead text-muted">Choose your preferred method to submit your geospatial data for processing.</p>
      </div>

      <div class="row g-6 justify-content-center">
        <!-- Data Portal Option -->
        <div class="col-md-4">
          <div class="card selection-card text-center p-8 h-100" onclick="window.location.href='{{ route('upload_data') }}'">
            <div class="card-body">
              <h3 class="fw-bold mb-4">Create Project using Data Portal</h3>
              <p class="text-muted mb-6">Directly upload your drone imagery and POS files through our secure web interface. Best for individual projects and smaller datasets.</p>
              <button class="btn btn-primary btn-lg px-6">Select Data Portal</button>
            </div>
          </div>
        </div>

        <!-- SFTP Option -->
        <div class="col-md-4">
          <div class="card selection-card text-center p-8 h-100" id="sftpCard">
            <div class="card-body">
              <h3 class="fw-bold mb-4">Create Project using SFTP</h3>
              <p class="text-muted mb-6">Provision a dedicated SFTP drop-folder on our secure server. Use an SFTP client to comfortably upload massive datasets without internet browser limits.</p>
              <button class="btn btn-primary btn-lg px-6">Select SFTP</button>
            </div>
          </div>
        </div>

        <!-- Google Drive Option -->
        <div class="col-md-4">
          <div class="card selection-card text-center p-8 h-100" id="gdriveCard" onclick="window.location.href='{{ route('upload_gdrive') }}'">
            <div class="card-body">
              <h3 class="fw-bold mb-4">Create Project using Google Drive</h3>
              <p class="text-muted mb-6">Provide a shared link to your raw drone imagery stored in Google Drive. Ensure the link is public ("Anyone with the link") for us to read.</p>
              <button class="btn btn-primary btn-lg px-6">Select Google Drive</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets/') }}/js/theme-switcher.js"></script>
  <script>
    document.getElementById('sftpCard').addEventListener('click', function() {
      if (window.userRole === 'registered') {
        alert('Create project using SFTP is only available for trusted users. Please proceed with using Data Portal to create your project.');
      } else {
        window.location.href = '{{ route('upload_sftp') }}';
      }
    });
  </script>
</body>
</html>
