<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Data Portal | 3DHub</title>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/core.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/demo.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/admin-responsive.css" />
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/helpers.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/bootstrap.js"></script>

</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
  <style>
    /* 💎 ADMIN PREMIUM TOP NAV (v250) */
    .admin-glass-nav {
      position: fixed;
      top: 1.5rem;
      left: 1.5rem;
      right: 1.5rem;
      z-index: 1050;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 1.25rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      padding: 0.5rem 1.5rem;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }
    [data-bs-theme="dark"] .admin-glass-nav {
      background: rgba(15, 23, 42, 0.7);
      border-color: rgba(255, 255, 255, 0.08);
    }
    .admin-nav-links {
      display: flex;
      gap: 0.5rem;
      margin-left: 1.5rem;
      align-items: center;
    }
    .admin-nav-link {
      color: #566a7f;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
      font-size: 0.82rem;
      padding: 0.4rem 0.6rem;
      border-radius: 0.75rem;
      white-space: nowrap;
    }
    .admin-nav-link:hover {
      color: #696cff;
      background: rgba(105, 108, 255, 0.08);
    }
    .admin-nav-link.active {
      color: #696cff;
      background: rgba(105, 108, 255, 0.12);
      font-weight: 700;
    }
    .email-hover-link { color: #8e94a3 !important; transition: color 0.2s ease; } .email-hover-link:hover {
      color: #696cff !important;
    }
    .content-wrapper-premium {
      margin-top: 7.5rem !important;
    }
    .layout-page {
        padding: 0 !important;
    }
    @media (max-width: 1199.98px) {
      .admin-nav-links { display: none; }
    }
  </style>
</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      
      <!-- Premium Glass Top Nav -->
      <nav class="admin-glass-nav">
        <a href="<?php echo e(route('admin_dashboard')); ?>" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo me-2"><img src="<?php echo e(asset('assets')); ?>/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
          <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size: 1.1em;">3DHub Admin</span>
        </a>
        
        <div class="admin-nav-links d-none d-xl-flex">
          <a href="<?php echo e(route('admin_dashboard')); ?>" class="admin-nav-link active">Dashboard</a>
          <a href="<?php echo e(route('admin.add_3d_model')); ?>" class="admin-nav-link">Add 3D Model</a>
          <a href="<?php echo e(route('admin.manage_map_pins')); ?>" class="admin-nav-link">Manage Map Pins</a>
          <a href="<?php echo e(route('admin.manage_showcases')); ?>" class="admin-nav-link">Manage Showcase</a>
          <a href="<?php echo e(route('admin.client_uploads')); ?>" class="admin-nav-link">Client Uploads</a>
          <a href="<?php echo e(route('admin.manage_users')); ?>" class="admin-nav-link">Manage Users</a>
          <a href="<?php echo e(route('landing')); ?>" class="admin-nav-link" target="_blank">View Portal</a>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Style Switcher -->
            <div class="nav-item dropdown-style-switcher dropdown me-2">
              <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light"><span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span></button></li>
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"><span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span></button></li>
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"><span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span></button></li>
              </ul>
            </div>

            <?php if(auth()->guard()->check()): ?>
            <div class="d-none d-md-flex align-items-center gap-3 border-start ps-3 ms-2">
                <a href="<?php echo e(route('profile')); ?>" class="small text-muted fw-medium text-decoration-none email-hover-link"><?php echo e(Auth::user()->email); ?></a>
                <form method="POST" action="<?php echo e(route('logout')); ?>" id="adminLogoutForm" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button type="button" id="adminLogoutBtn" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold">Log out</button>
                </form>
            </div>
            <?php endif; ?>

            <button class="admin-menu-toggle btn btn-icon d-xl-none border-0 bg-transparent p-0" type="button" aria-label="Toggle menu"><i class="bx bx-menu icon-lg"></i></button>
        </div>
      </nav>

      <div class="layout-page">
        <div class="content-wrapper content-wrapper-premium">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold mb-4">Admin Dashboard</h4>
            <div class="row">
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?php echo e(route('admin.add_3d_model')); ?>" class="card text-decoration-none h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-cube bx-lg"></i></span>
                      </div>
                      <div>
                        <h5 class="card-title mb-0">Add 3D Model</h5>
                        <p class="text-muted small mb-0">Create a new 3D model entry and add it to the overview map and showcases.</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?php echo e(route('admin.manage_map_pins')); ?>" class="card text-decoration-none h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-map-pin bx-lg"></i></span>
                      </div>
                      <div>
                        <h5 class="card-title mb-0">Manage Map Pins</h5>
                        <p class="text-muted small mb-0">View, edit, or delete pin locations and 3D models on the overview map and showcases.</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?php echo e(route('admin.manage_showcases')); ?>" class="card text-decoration-none h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-grid-alt bx-lg"></i></span>
                      </div>
                      <div>
                        <h5 class="card-title mb-0">Manage Showcase</h5>
                        <p class="text-muted small mb-0">Choose which locations appear on the landing page showcase; remove from showcase only, map only, or both.</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?php echo e(route('admin.client_uploads')); ?>" class="card text-decoration-none h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-cloud-upload bx-lg"></i></span>
                      </div>
                      <div>
                        <h5 class="card-title mb-0">Client Uploads</h5>
                        <p class="text-muted small mb-0">View client requests for custom image-to-3D processing; process their images and deliver the 3D model back to them (paid service).</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-6 col-lg-4 mb-4">
                <a href="<?php echo e(route('admin.manage_users')); ?>" class="card text-decoration-none h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-user bx-lg"></i></span>
                      </div>
                      <div>
                        <h5 class="card-title mb-0">Manage Users</h5>
                        <p class="text-muted small mb-0">Promote client accounts to admin so they can access the admin portal.</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="logoutConfirmMessage">Are you sure you want to log out? You will need to sign in again to use the Admin Data Portal.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="logoutConfirmBtn">Log out</button>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo e(asset('assets')); ?>/js/admin-responsive.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-switcher.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var logoutBtn = document.getElementById('adminLogoutBtn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          var modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
          modal.show();
          document.getElementById('logoutConfirmBtn').onclick = function() {
            document.getElementById('adminLogoutForm').submit();
          };
        });
      }
    });
  </script>
</body>
</html>
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/admin/index.blade.php ENDPATH**/ ?>