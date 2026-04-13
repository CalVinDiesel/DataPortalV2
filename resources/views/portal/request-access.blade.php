<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Request Access | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/boxicons.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>

  <style>
    .hero-section {
      background: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
      padding: 60px 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }
    [data-bs-theme="dark"] .hero-section {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    }
  </style>
</head>
<body>
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-lg landing-navbar px-3">
        <a href="{{ route('landing') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;">
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
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow-sm border-0">
            <div class="card-body p-5">
              @if(session('success'))
                <div class="text-center">
                  <div class="mb-4 text-success">
                    <i class="bx bx-check-circle" style="font-size: 64px;"></i>
                  </div>
                  <h3 class="fw-bold mb-3">Request Received</h3>
                  <p class="text-muted">{{ session('success') }}</p>
                  <a href="{{ route('landing') }}" class="btn btn-primary mt-3">Return to Home</a>
                </div>
              @else
                <div class="text-center mb-4">
                  <h3 class="fw-bold">Request Access</h3>
                  <p class="text-muted">Join our exclusive data portal. Complete the form below to join the waitlist.</p>
                </div>
                
                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form method="POST" action="{{ url('/request-access') }}">
                  @csrf
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required value="{{ old('name') }}">
                  </div>
                  
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="{{ old('email') }}">
                  </div>

                  <div class="mb-3">
                    <label for="company_name" class="form-label">Company / Organization (Optional)</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Where do you work?" value="{{ old('company_name') }}">
                  </div>
                  
                  <div class="mb-4">
                    <label for="reason_for_access" class="form-label">Reason for Request (Optional)</label>
                    <textarea class="form-control" id="reason_for_access" name="reason_for_access" rows="2" placeholder="Why do you need access?">{{ old('reason_for_access') }}</textarea>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 d-grid">Submit Request</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
</body>
</html>
