<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets/') }}/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Forgot username or password | 3DHub Data Portal</title>
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
        <div class="ms-auto">
          <a href="{{ route('landing') }}" class="btn btn-outline-primary btn-sm">Back to Home</a>
        </div>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow">
          <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
              <h4 class="mb-1">Forgot username or password?</h4>
              <p class="text-body small">Enter your registered email to recover your username or reset your password.</p>
            </div>

            <form id="forgotForm" novalidate>
              <div class="mb-3">
                <label class="form-label" for="recoverEmail">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="recoverEmail" name="recoverEmail" placeholder="Your registered email" autocomplete="email">
                <div class="invalid-feedback">Please enter a valid email ending with .com or .my</div>
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="recoverUsername" name="recoverUsername" checked>
                <label class="form-check-label" for="recoverUsername">Recover username</label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="recoverPassword" name="recoverPassword" checked>
                <label class="form-check-label" for="recoverPassword">Reset password</label>
              </div>

              <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Send recovery link</button>
              </div>
            </form>

            <div id="forgotSuccess" class="alert alert-success mt-3 d-none" role="alert">
              If an account exists for this email, you will receive instructions to recover your username and/or reset your password. Please check your inbox.
            </div>

            <hr class="my-4">
            <div class="text-center small">
              <a href="{{ route('login') }}" class="fw-medium">Back to log in</a> &nbsp;|&nbsp; <a href="{{ route('register') }}" class="fw-medium">Register</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
  <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
  <script>
    (function () {
      var form = document.getElementById('forgotForm');
      var successEl = document.getElementById('forgotSuccess');
      function validateEmail(val) {
        return /^[^\s@]+@[^\s@]+\.(com|my)$/i.test(val || '');
      }
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var emailEl = document.getElementById('recoverEmail');
        var valid = validateEmail(emailEl.value);
        emailEl.classList.toggle('is-invalid', !valid);
        if (valid) {
          successEl.classList.remove('d-none');
          form.classList.add('d-none');
        }
      });
    })();
  </script>
</body>
</html>
