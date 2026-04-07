<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets/') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Sign up | 3DHub Data Portal</title>
  <script src="{{ asset('assets/') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/') }}/img/favicon/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/pages/front-page.css">
  <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets/') }}/js/front-config.js"></script>
  <style>
    .btn-google { background-color: #fff; color: #3c4043; border: 1px solid #dadce0; }
    .btn-google:hover { background-color: #f8f9fa; border-color: #dadce0; color: #3c4043; }
    .btn-social { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; font-weight: 500; }
    .btn-microsoft:hover { background-color: #f8f9fa !important; }

    /* Step indicators */
    .step-indicator { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .step-dot { width: 10px; height: 10px; border-radius: 50%; background-color: #dee2e6; transition: background-color 0.3s; }
    .step-dot.active { background-color: #696cff; }
    .step-dot.done { background-color: #71dd37; }

    /* Verified badge shown above form */
    .verified-badge { display: flex; align-items: center; gap: 0.5rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.5rem 0.75rem; margin-bottom: 1rem; font-size: 0.85rem; color: #166534; }
    .verified-badge img { width: 16px; height: 16px; }
  </style>
</head>
<body class="min-vh-100 d-flex flex-column">
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
          <a href="{{ route('landing') }}" id="navBackToHome" class="btn btn-outline-primary btn-sm">Back to Home</a>
        </div>
      </div>
    </div>
  </nav>

  <div class="flex-grow-1 d-flex align-items-start" style="padding-top: 100px;">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow mt-3">
            <div class="card-body p-4 p-sm-5">
          
              <!-- Step indicator -->
              <div class="step-indicator pt-2 mb-3">
                <div class="step-dot active" id="dot1"></div>
                <div class="step-dot" id="dot2"></div>
              </div>

              <!-- ─── STEP 1: Sign up with email OR social ───────────────────── -->
              <div id="step1">
                <div class="mb-4">
                  <h4 class="mb-2">Sign up with Email</h4>
                  <form id="emailSignupForm" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Your Name" autocomplete="name">
                      <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required placeholder="Pick a username" autocomplete="username">
                      <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com" autocomplete="email">
                      <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="contact_number">Contact number <span class="text-danger">*</span></label>
                      <input type="tel" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required placeholder="e.g. +60 12-345 6789" autocomplete="tel">
                      <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                      <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="password" name="password" required placeholder="At least 8 characters" autocomplete="new-password">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-body p-0 pe-2 password-toggle-btn" data-target="password" aria-label="Show password" title="Show password">
                          <i class="bx bx-show-alt icon-lg"></i>
                        </button>
                      </div>
                      <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="password_confirmation">Confirm password <span class="text-danger">*</span></label>
                      <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password" autocomplete="new-password">
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-body p-0 pe-2 password-toggle-btn" data-target="password_confirmation" aria-label="Show password" title="Show password">
                          <i class="bx bx-show-alt icon-lg"></i>
                        </button>
                      </div>
                      <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                    <div class="d-grid">
                      <button type="submit" class="btn btn-primary" id="emailSignupSubmit">Create account</button>
                    </div>
                  </form>

                </div>

                <p class="text-center text-body small my-3 mb-0">— or —</p>
                <div class="text-center mb-2">
                  <h4 class="mb-1">Create your account</h4>
                  <p class="text-body small mb-0">Continue with Google or Microsoft.</p>
                </div>

                <div class="d-grid gap-3 mb-3">
                  <a href="#" id="btnGoogle" class="btn btn-google btn-social">
                    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.27 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                    Register with Google
                  </a>
                  <a href="#" id="btnMicrosoft" class="btn btn-social btn-microsoft" style="background-color:#fff; color:#3c4043; border:1px solid #dadce0;">
                    <img src="https://learn.microsoft.com/en-us/entra/identity-platform/media/howto-add-branding-in-apps/ms-symbollockup_mssymbol_19.png" alt="Microsoft" width="18" height="18">
                    Register with Microsoft
                  </a>
                </div>

                <p id="step1Message" class="small mt-2 mb-0 text-center"></p>

                <p class="small text-body text-center mt-3 mb-0">
                  Using Google or Microsoft? You'll complete your profile in step 2.
                </p>
              </div>

              <!-- ─── Already have account (email already registered) ─────────── -->
              <div id="step2Existing" style="display:none;">
                <div class="text-center mb-4">
                  <h4 class="mb-1">Account already exists</h4>
                  <p class="text-body mb-0" id="existingAccountMessage">You have already created an account using this email.</p>
                </div>
                <div class="alert alert-info mb-4" role="alert">
                  <span id="existingAccountEmail" class="fw-medium"></span> is already registered in the Data Portal. Please log in with this account or use a different email to create a new account.
                </div>
                <div class="d-grid gap-2">
                  <a href="{{ route('login') }}" id="btnLoginWithExisting" class="btn btn-primary">Log in with this account</a>
                  <button type="button" id="btnUseDifferentAccount" class="btn btn-outline-secondary">Use a different account</button>
                </div>
                <p class="small text-center text-body mt-4 mb-0">
                  <a href="#" id="existingBackToHome" class="text-body">Back to home</a>
                </p>
              </div>

              <!-- ─── STEP 2: Complete registration form ────────────────────── -->
              <div id="step2" style="display:none;">
                <div class="text-center mb-4">
                  <h4 class="mb-1">Complete your profile</h4>
                  <p class="text-body small mb-0">Step 2 of 2 — Fill in the remaining details.</p>
                  <p class="mt-2 mb-0">
                    <a href="#" id="backToHomeLink" class="text-body small"><i class="bx bx-arrow-back me-1"></i>Back to home</a>
                    <span class="text-muted small mx-2">|</span>
                    <a href="#" id="cancelRegistrationLink" class="text-body small">Cancel and use a different account</a>
                  </p>
                </div>

                <!-- Verified badge showing which provider they used -->
                <div class="verified-badge" id="verifiedBadge">
                  <span id="verifiedIcon">✓</span>
                  <span id="verifiedText">Verified via Google</span>
                </div>

                <form id="registerForm" novalidate>
                  <!-- Hidden fields from OAuth -->
                  <input type="hidden" id="oauthEmail" name="oauthEmail">
                  <input type="hidden" id="oauthProvider" name="oauthProvider">

                  <div class="mb-3">
                    <label class="form-label" for="regName">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="regName" name="name" placeholder="Your full name">
                    <div class="invalid-feedback">Please enter your name.</div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="regEmail">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="regEmail" name="email" placeholder="your@email.com" readonly style="background:#f8f9fa;">
                    <div class="form-text">Pre-filled from your account. <a href="#" id="switchProvider">Use a different account?</a></div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="regContact">Contact number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="regContact" name="contactNumber" placeholder="e.g. +60 12-345 6789">
                    <div class="invalid-feedback">Please enter a valid contact number.</div>
                  </div>

                  <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Create account</button>
                  </div>
                  <p id="registerMessage" class="small mt-2 mb-0 text-center"></p>
                </form>
              </div>

              <p class="small text-body text-center mt-4 mb-0">
                By signing up, you agree to our <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a>.
              </p>

              <hr class="my-4">
              <div class="text-center small">
                Already have an account? <a href="{{ route('login') }}" class="fw-medium">Log in</a>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets/') }}/js/theme-switcher.js"></script>
  <script>
    // Password show/hide toggles (register form)
    document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
      var id = btn.getAttribute('data-target');
      var input = id ? document.getElementById(id) : null;
      var icon = btn.querySelector('i');
      if (!input || !icon) return;
      btn.addEventListener('click', function() {
        var isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bx bx-hide icon-lg' : 'bx bx-show-alt icon-lg';
        btn.setAttribute('aria-label', isPass ? 'Hide password' : 'Show password');
        btn.setAttribute('title', isPass ? 'Hide password' : 'Show password');
      });
    });

    // Social Registration Redirect
    document.getElementById('btnGoogle').addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = "{{ route('auth.google') }}";
    });

    document.getElementById('btnMicrosoft').addEventListener('click', function (e) {
      e.preventDefault();
      alert('Microsoft registration is not yet configured on this server. Please use the email form.');
    });

    // Email Signup form validation
    document.getElementById('emailSignupForm').addEventListener('submit', function(e) {
      var nameEl = document.getElementById('name');
      var userEl = document.getElementById('username');
      var emailEl = document.getElementById('email');
      var contactEl = document.getElementById('contact_number');
      var passEl = document.getElementById('password');
      var confirmEl = document.getElementById('password_confirmation');
      var btn = document.getElementById('emailSignupSubmit');

      var ok = true;
      [nameEl, userEl, emailEl, contactEl, passEl, confirmEl].forEach(function(el) {
        if (!el.value.trim()) {
          el.classList.add('is-invalid');
          ok = false;
        } else {
          el.classList.remove('is-invalid');
        }
      });
      
      if (passEl.value !== confirmEl.value) {
        confirmEl.classList.add('is-invalid');
        ok = false;
      }

      if (!ok) {
        e.preventDefault();
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating account...';
    });
  </script>
</body>
</html>