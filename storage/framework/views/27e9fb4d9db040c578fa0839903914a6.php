<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/" data-template="front-pages" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Request Access | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/boxicons.css">
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
  </style>
</head>
<body>
  <nav class="layout-navbar shadow-none py-0">
    <div class="container">
      <div class="navbar navbar-expand-lg landing-navbar px-3">
        <a href="<?php echo e(route('landing')); ?>" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo">
            <img src="<?php echo e(asset('assets')); ?>/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;">
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
          <a href="<?php echo e(route('landing')); ?>" class="btn btn-outline-primary btn-sm">Back to Home</a>
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
              <?php if(session('success')): ?>
                <div class="text-center">
                  <div class="mb-4 text-success">
                    <i class="bx bx-check-circle" style="font-size: 64px;"></i>
                  </div>
                  <h3 class="fw-bold mb-3">Request Received</h3>
                  <p class="text-muted"><?php echo e(session('success')); ?></p>
                  <a href="<?php echo e(route('landing')); ?>" class="btn btn-primary mt-3">Return to Home</a>
                </div>
              <?php else: ?>
                <div class="text-center mb-4">
                  <h3 class="fw-bold">Request Access</h3>
                  <p class="text-muted">Join our exclusive data portal. Complete the form below to join the waitlist.</p>
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

                <form method="POST" action="<?php echo e(url('/request-access')); ?>">
                  <?php echo csrf_field(); ?>
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required value="<?php echo e(old('name')); ?>">
                  </div>
                  
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="<?php echo e(old('email')); ?>">
                  </div>

                  <div class="mb-3">
                    <label for="company_name" class="form-label">Company / Organization (Optional)</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Where do you work?" value="<?php echo e(old('company_name')); ?>">
                  </div>
                  
                  <div class="mb-4">
                    <label for="reason_for_access" class="form-label">Reason for Request (Optional)</label>
                    <textarea class="form-control" id="reason_for_access" name="reason_for_access" rows="2" placeholder="Why do you need access?"><?php echo e(old('reason_for_access')); ?></textarea>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 d-grid">Submit Request</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script src="<?php echo e(asset('assets')); ?>/vendor/libs/popper/popper.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/bootstrap.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-switcher.js"></script>
</body>
</html>
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/portal/request-access.blade.php ENDPATH**/ ?>