<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Complete Setup | 3DHub</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
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
    .auth-btn { border-radius: 8px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 12px; }
    
    /* 🚀 BORDER ALIGNMENT FIX (v150): Ensure merged input groups have seamless borders */
    .input-group-merge.form-password-toggle {
      border: 1px solid var(--bs-border-color);
      border-radius: 0.375rem;
      overflow: hidden;
      background-color: var(--bs-body-bg);
      display: flex;
    }
    .input-group-merge.form-password-toggle .form-control,
    .input-group-merge.form-password-toggle .input-group-text {
      border: none !important;
      box-shadow: none !important;
      background-color: transparent !important;
    }
    .input-group-merge.form-password-toggle:focus-within {
      border-color: #696cff;
      box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
    }
    .cursor-pointer {
      cursor: pointer;
    }
    /* Hide native browser password reveal buttons to prevent duplicates */
    input::-ms-reveal,
    input::-ms-clear,
    input::-webkit-contacts-auto-fill-button,
    input::-webkit-credentials-auto-fill-button {
      display: none !important;
    }
  </style>
</head>
<body>
  <section class="hero-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="card shadow-sm border-0">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 60px;" class="mb-3">
                <h3 class="fw-bold">Welcome, {{ $user->name }}!</h3>
                <p class="text-muted">Complete your account setup to access the Data Portal.</p>
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

              <form method="POST" action="{{ url('/setup') }}" id="setupForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="action" id="authAction" value="{{ old('action', 'password') }}">

                <div class="mb-3 bg-light p-3 rounded border">
                  <h6 class="fw-bold mb-2 text-dark">Your Registration Details:</h6>
                  <div class="small text-muted mb-1"><strong>Name:</strong> {{ $user->name }}</div>
                  <div class="small text-muted mb-1"><strong>Email:</strong> {{ $user->email }}</div>
                  <div class="small text-muted mb-0"><strong>Contact Number:</strong> {{ $user->contact_number }}</div>
                </div>

                <div class="alert alert-warning mb-3">
                  <i class="bx bx-info-circle me-1"></i>
                  <strong>Email Match Reminder:</strong> If you choose Google or Microsoft below, your login email <strong>must match exactly</strong> with your registered email (<strong>{{ $user->email }}</strong>).
                </div>

                <div class="alert alert-danger mb-4">
                  <i class="bx bx-lock-alt me-1"></i>
                  <strong>Lock Mechanism:</strong> Whichever method you choose now will be your permanent login method. You cannot change this later.
                </div>

                <hr class="my-4">
                
                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.8rem;">Select Your Permanent Login Method</h6>

                <!-- Password Auth Toggle -->
                <button type="button" class="btn {{ old('action') === 'password' || $errors->has('password') ? 'btn-outline-primary' : 'btn-primary' }} w-100 auth-btn" id="togglePasswordBtn">
                  <i class="bx bx-lock-alt"></i> Create a Password
                </button>

                <!-- Password Block -->
                <div id="passwordBlock" class="{{ old('action') === 'password' || $errors->has('password') ? '' : 'd-none' }} mt-4 mb-3 p-3 bg-light rounded border">
                  <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Password</label>
                    <div class="input-group input-group-merge form-password-toggle">
                      <input type="password" name="password" class="form-control" placeholder="Create a secure password" autocomplete="new-password">
                      <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Confirm Password</label>
                    <div class="input-group input-group-merge form-password-toggle">
                      <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" autocomplete="new-password">
                      <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success w-100 fw-bold" onclick="submitAuth('password')">Complete Setup</button>
                </div>

                <div class="text-center my-3 text-muted">OR</div>

                <!-- Google Auth -->
                <button type="button" class="btn btn-outline-dark w-100 auth-btn" onclick="submitAuth('google')">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" style="height: 20px;">
                  Continue with Google
                </button>

                <!-- Microsoft Auth -->
                <button type="button" class="btn btn-outline-dark w-100 auth-btn" onclick="submitAuth('microsoft')">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" style="height: 20px;">
                  Continue with Microsoft
                </button>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  <script>
    document.getElementById('togglePasswordBtn').addEventListener('click', function() {
      var pb = document.getElementById('passwordBlock');
      var actionInput = document.getElementById('authAction');
      if (pb.classList.contains('d-none')) {
        pb.classList.remove('d-none');
        if (this.classList.contains('btn-primary')) {
          this.classList.replace('btn-primary', 'btn-outline-primary');
        }
        actionInput.value = 'password';
      } else {
        pb.classList.add('d-none');
        if (this.classList.contains('btn-outline-primary')) {
          this.classList.replace('btn-outline-primary', 'btn-primary');
        }
        actionInput.value = '';
      }
    });

    function submitAuth(provider) {
      if (provider === 'password') {
        var passwordInput = document.getElementsByName('password')[0];
        var confirmInput = document.getElementsByName('password_confirmation')[0];
        var password = passwordInput.value;
        var confirmPassword = confirmInput.value;
        
        if (!password) {
          alert('Please enter a password.');
          passwordInput.focus();
          return;
        }
        if (password.length < 8) {
          alert('Password must be at least 8 characters long.');
          passwordInput.focus();
          return;
        }
        if (password !== confirmPassword) {
          alert('The password confirmation does not match.');
          confirmInput.focus();
          return;
        }
      }
      
      document.getElementById('authAction').value = provider;
      document.getElementById('setupForm').submit();
    }

    // Toggle password visibility
    document.querySelectorAll('.form-password-toggle .input-group-text').forEach(function(toggle) {
      toggle.addEventListener('click', function() {
        var input = this.previousElementSibling;
        var icon = this.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('bx-hide', 'bx-show');
        } else {
          input.type = 'password';
          icon.classList.replace('bx-show', 'bx-hide');
        }
      });
    });
  </script>
</body>
</html>
