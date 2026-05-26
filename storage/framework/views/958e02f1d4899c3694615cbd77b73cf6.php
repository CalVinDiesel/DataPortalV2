<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Complete Setup | 3DHub</title>
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/core.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/demo.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/pages/front-page.css">
  <script src="<?php echo e(asset('assets')); ?>/js/theme-init.js"></script>
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
                <img src="<?php echo e(asset('assets')); ?>/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 60px;" class="mb-3">
                <h3 class="fw-bold">Welcome, <?php echo e($user->name); ?>!</h3>
                <p class="text-muted">Complete your account setup to access the Data Portal.</p>
              </div>

              <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </ul>
                </div>
              <?php endif; ?>

              <form method="POST" action="<?php echo e(url('/setup')); ?>" id="setupForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <input type="hidden" name="action" id="authAction" value="<?php echo e(old('action', 'password')); ?>">

                <div class="mb-4">
                  <label for="contact_number" class="form-label fw-bold text-dark">Contact Number <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control form-control-lg" id="contact_number" name="contact_number" placeholder="e.g. +1 234 567 890" value="<?php echo e(old('contact_number', $user->contact_number)); ?>" autocomplete="tel" required>
                  <div class="form-text mt-2"><i class="bx bx-info-circle me-1"></i> A contact number is required before proceeding.</div>
                </div>

                <hr class="my-4">
                
                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.8rem;">Select Account Type</h6>

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

                <div class="text-center my-3 text-muted">OR</div>

                <!-- Password Auth Toggle -->
                <button type="button" class="btn <?php echo e(old('action') === 'password' || $errors->has('password') ? 'btn-outline-primary' : 'btn-primary'); ?> w-100 auth-btn" id="togglePasswordBtn">
                  <i class="bx bx-lock-alt"></i> Create a Password
                </button>

                <!-- Password Block -->
                <div id="passwordBlock" class="<?php echo e(old('action') === 'password' || $errors->has('password') ? '' : 'd-none'); ?> mt-4 p-3 bg-light rounded border">
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

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="<?php echo e(asset('assets')); ?>/vendor/libs/popper/popper.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/bootstrap.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-switcher.js"></script>
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
      if (!document.getElementById('contact_number').value.trim()) {
        alert('Please enter your Contact Number before continuing.');
        document.getElementById('contact_number').focus();
        return;
      }
      
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
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/portal/setup.blade.php ENDPATH**/ ?>