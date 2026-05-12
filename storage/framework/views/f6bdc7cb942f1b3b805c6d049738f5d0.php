<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>My Projects | 3DHub Data Portal</title>

  <script src="<?php echo e(asset('assets')); ?>/js/theme-init.js"></script>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css">
    <!-- 📦 CONSOLE CLEANUP (v62): Removed external links to stop Tracking Prevention warnings -->

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/core.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/demo.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/pages/front-page.css">

  <script src="<?php echo e(asset('assets')); ?>/vendor/js/helpers.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/front-config.js"></script>

  <!-- Auth Protection -->
  <script>
    (function () {
      window.userRole = '<?php echo e(Auth::user()->role); ?>';
    })();

    function logout() {
      if (!confirm('Are you sure you want to log out?')) return;
      document.getElementById('logout-form').submit();
    }
  </script>

<style>
  body {
    background-color: var(--bs-body-bg);
  }
  .hero-bg {
    background: linear-gradient(135deg, rgba(105, 108, 255, 0.05) 0%, rgba(105, 108, 255, 0.01) 100%);
    padding: 3rem 0;
    border-bottom: 1px solid var(--bs-border-color);
  }

  .storage-card {
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    height: 100%;
  }

  .stat-card {
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.25s ease;
    height: 100%;
  }

  .stat-card:hover {
    box-shadow: 0 6px 16px rgba(105, 108, 255, 0.1);
    transform: translateY(-2px);
  }

  .back-btn {
    background: var(--bs-card-bg) !important;
    color: var(--bs-secondary-color) !important;
  }
  .back-btn:hover {
    color: #696cff !important;
    background-color: var(--bs-tertiary-bg) !important;
  }
  .back-btn:hover i { color: inherit !important; }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
  }

  /* Table Styles */
  .table-container {
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
  }

  .project-thumb {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    background-color: var(--bs-tertiary-bg);
    border: 1px solid var(--bs-border-color);
  }

  .project-name {
    font-weight: 600;
    color: var(--bs-heading-color);
    font-size: 1.05rem;
    margin-bottom: 0.2rem;
  }

  .project-meta {
    font-size: 0.85rem;
    color: var(--bs-secondary-color);
  }

  .badge-status {
    padding: 0.4rem 0.75rem;
    font-weight: 500;
    font-size: 0.75rem;
    border-radius: 6px;
  }

  /* Status Colors */
  .status-completed  { background: rgba(113, 221, 55, 0.1);  color: #71dd37; border: 1px solid rgba(113, 221, 55, 0.2); }
  .status-processing { background: rgba(105, 108, 255, 0.1); color: #696cff; border: 1px solid rgba(105, 108, 255, 0.2); }
  .status-incomplete { background: rgba(216, 16, 219, 0.1);  color: #d810db; border: 1px solid rgba(216, 16, 219, 0.2); }
  .status-failed     { background: rgba(255, 62, 29, 0.1);   color: #ff3e1d; border: 1px solid rgba(255, 62, 29, 0.2); }

  .table th {
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    color: var(--bs-secondary-color);
    background: var(--bs-tertiary-bg);
    border-bottom: 1px solid var(--bs-border-color);
    padding: 1rem;
  }

  .table td {
    padding: 1.25rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--bs-border-color);
    color: var(--bs-body-color);
  }

  .table tr:last-child td {
    border-bottom: none;
  }

  .action-btn {
    color: var(--bs-secondary-color);
    background: transparent;
    border: none;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.2s;
  }

  .action-btn:hover {
    background: rgba(105, 108, 255, 0.1);
    color: #696cff;
  }

  .btn-dropdown-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* Modal Styling */
  .detail-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--bs-secondary-color);
    font-weight: 600;
    margin-bottom: 0.25rem;
  }
  .detail-value {
    color: var(--bs-body-color);
    font-weight: 500;
    margin-bottom: 1.25rem;
  }
  .modal-header {
    background: var(--bs-tertiary-bg);
    border-bottom: 1px solid var(--bs-border-color);
  }

  /* Pagination strip */
  .bg-lighter {
    background: var(--bs-tertiary-bg) !important;
  }
</style>
</head>

<body>

  <!-- Hero Section -->
  <div class="hero-bg">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="<?php echo e(route('landing')); ?>" class="btn btn-label-secondary btn-sm fw-medium border shadow-sm back-btn" style="background: white; color: #566a7f;">
          <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <!-- Style Switcher -->
        <ul class="navbar-nav flex-row align-items-center mb-0">
          <li class="nav-item dropdown-style-switcher dropdown">
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
      </div>
      <div class="d-flex justify-content-between align-items-center mb-0">
        <div>
          <h2 class="h3 fw-bold text-dark mb-2">My Datasets</h2>
          <p class="text-muted mb-0">Manage your uploaded flight paths, 3D models, and processing jobs.</p>
        </div>
        <a href="<?php echo e(route('create_project')); ?>" class="btn btn-primary shadow-sm">
          <i class="bx bx-plus me-1"></i> New Project
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container mt-4 mb-5 pb-5">
    
    <!-- Dashboard Stats Row -->
    <div class="row g-4 mb-5">
      <!-- Storage Quota -->
      <div class="col-lg-5 col-md-12">
        <div class="storage-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Storage Quota</h5>
            <span class="badge bg-label-primary">Pro Plan</span>
          </div>
          <div class="d-flex justify-content-between text-muted small mb-2">
            <span id="storageUsedText">0 GB Used</span>
            <span>100 GB Total</span>
          </div>
          <div class="progress" style="height: 10px; border-radius: 10px;">
            <div id="storageProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div id="storageStatusText" class="mt-3 text-muted" style="font-size: 0.8rem;">
            You have used 0% of your available storage.
          </div>
        </div>
      </div>
      
      <!-- Quick Stats -->
      <div class="col-lg-7 col-md-12">
        <div class="row g-4 h-100">
          <div class="col-sm-4">
            <div class="stat-card">
              <div class="stat-icon bg-label-success">
                <i class="bx bx-folder-open"></i>
              </div>
              <div>
                <h4 class="mb-0 fw-bold" id="statTotalProjects">-</h4>
                <p class="mb-0 text-muted small">Total Projects</p>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="stat-card">
              <div class="stat-icon bg-label-info">
                <i class="bx bx-images"></i>
              </div>
              <div>
                <h4 class="mb-0 fw-bold" id="statPhotosStored">-</h4>
                <p class="mb-0 text-muted small">Photos Stored</p>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="stat-card">
              <div class="stat-icon bg-label-warning">
                <i class="bx bx-loader-circle bx-spin"></i>
              </div>
              <div>
                <h4 class="mb-0 fw-bold" id="statProcessingJobs">-</h4>
                <p class="mb-0 text-muted small">Processing Job</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
        <h5 class="mb-0 fw-bold">Recent Uploads</h5>
        <div class="input-group input-group-sm" style="width: 250px;">
          <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
          <input type="text" id="projectSearch" class="form-control" placeholder="Search projects..." onkeyup="filterProjects()">
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="width: 30%">Project Information</th>
              <th>Status</th>
              <th>Date / Size</th>
              <th>Delivered Date</th>
              <th>Configuration</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="uploadsTableBody">
            <tr>
              <td colspan="6" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted small">Loading your datasets...</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center p-3 border-top bg-lighter">
        <div class="text-muted small" id="paginationText">Loading...</div>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" id="prevPageItem">
            <a class="page-link" href="javascript:void(0)" onclick="changePage(-1)">Previous</a>
          </li>
          <li class="page-item active" id="page1Item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(1)">1</a></li>
          <li class="page-item" id="page2Item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(2)">2</a></li>
          <li class="page-item" id="page3Item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(3)">3</a></li>
          <li class="page-item" id="nextPageItem">
            <a class="page-link" href="javascript:void(0)" onclick="changePage(1)">Next</a>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <!-- Project Details Modal -->
  <div class="modal fade" id="projectDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header py-3">
          <h5 class="modal-title fw-bold" id="detailModalTitle">Project Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-12">
              <div class="detail-label">Description</div>
              <p class="detail-value mb-4" id="detailDescription"></p>
            </div>
            <div class="col-6">
              <div class="detail-label">Category</div>
              <div class="detail-value" id="detailCategory"></div>
            </div>
            <div class="col-6">
              <div class="detail-label">Coordinates</div>
              <div class="detail-value" id="detailCoordinates"></div>
            </div>
            <div class="col-6">
              <div class="detail-label">Camera Config</div>
              <div class="detail-value" id="detailConfig"></div>
            </div>
            <div class="col-6">
              <div class="detail-label">Camera Models</div>
              <div class="detail-value" id="detailModels"></div>
            </div>
            <div class="col-12">
              <div class="detail-label">Survey Date</div>
              <div class="detail-value" id="detailDate"></div>
            </div>
            <div class="col-12 d-none" id="detailDeliverySection">
              <div class="detail-label text-success"><i class="bx bx-check-shield me-1"></i> Processed Data Path (SFTP)</div>
              <div class="alert alert-success d-flex align-items-center p-2 mt-1" style="font-size: 0.8rem;">
                 <code id="detailDeliveryPath" class="flex-grow-1 bg-transparent border-0 text-dark" style="word-break: break-all;"></code>
                 <button type="button" class="btn btn-sm btn-link p-0 ms-2 text-success" onclick="copyTextFromElement('detailDeliveryPath', this)"><i class="bx bx-copy"></i></button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Metadata Modal -->
  <div class="modal fade" id="editMetadataModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header py-3">
          <h5 class="modal-title fw-bold">Edit Project Metadata</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="editProjectId">
          <div class="mb-3">
            <label class="form-label fw-bold small text-uppercase" for="editProjectTitle">Project Title</label>
            <input type="text" id="editProjectTitle" class="form-control" placeholder="Enter new project title">
          </div>
          <div class="mb-0">
            <label class="form-label fw-bold small text-uppercase" for="editProjectDescription">Project Description</label>
            <textarea id="editProjectDescription" class="form-control" rows="4" placeholder="Enter new project description"></textarea>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary px-4" id="saveEditBtn" onclick="saveProjectMetadata()">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow-lg text-center p-3">
        <div class="modal-body p-4 text-center">
          <div class="mb-3 mt-2">
            <i class="bx bx-trash text-danger" style="font-size: 3.5rem; background: rgba(255, 62, 29, 0.1); padding: 1rem; border-radius: 50%;"></i>
          </div>
          <h5 class="fw-bold mb-2 text-dark">Are you absolutely sure?</h5>
          <p class="text-muted mb-4" style="font-size: 0.9rem;">This will permanently delete your project and erase all associated data. This action <strong>cannot be undone</strong>.</p>
          <input type="hidden" id="deleteProjectId">
          <div class="d-flex flex-column gap-2">
            <button type="button" class="btn btn-danger w-100 fw-medium" id="confirmDeleteBtn" onclick="executeDeleteProject()">Yes, Delete Project</button>
            <button type="button" class="btn btn-label-secondary w-100 fw-medium" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
      <?php echo csrf_field(); ?>
  </form>

  <!-- Core Scripts -->
  <script src="<?php echo e(asset('assets')); ?>/vendor/libs/popper/popper.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/bootstrap.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-switcher.js"></script>
  
  <script>
    // 🚀 DYNAMIC CONFIG (v196)
    window.remoteBasePath = '<?php echo e(config("filesystems.disks.sftp_delivery.root", "/home/tiquan/")); ?>';
    
    function logout() {
      if (!confirm('Are you sure you want to log out?')) return;
      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      var LANDING_URL = window.location.origin + '/html/front-pages/<?php echo e(route('landing')); ?>';
      fetch(AUTH_API + '/api/auth/logout', { method: 'POST', credentials: 'include' })
        .then(function () { window.location.href = AUTH_API + '/api/auth/sign-out?callbackURL=' + encodeURIComponent(LANDING_URL); })
        .catch(function () { window.location.href = LANDING_URL; });
    }

    // Pagination Variables
    window.currentPage = 1;
    window.pageSize = 10;

    // Dynamic Data Fetching
    document.addEventListener("DOMContentLoaded", function() {
      fetchUploadsList();
    });

    function formatBytes(bytes) {
      if (bytes === 0 || !bytes) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function formatDate(dateString) {
      if (!dateString) return 'Unknown';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function fetchUploadsList() {
      // 🚀 CACHE-BUSTER (v210): Add timestamp to ensure we always get the freshest data after a sync
      fetch('/api/user/my-uploads?t=' + new Date().getTime(), { credentials: 'include' })
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('uploadsTableBody');
          tbody.innerHTML = '';
          
          if (!data || data.length === 0) {
            tbody.innerHTML = `
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="text-muted mb-2"><i class="bx bx-folder-open" style="font-size: 3rem; opacity: 0.5;"></i></div>
                  <h6 class="mb-1 fw-bold text-dark">No datasets found</h6>
                  <p class="text-muted small">You haven't uploaded any drone imagery yet.</p>
                  <a href="<?php echo e(route('create_project')); ?>" class="btn btn-primary btn-sm mt-2">Start your first upload</a>
                </td>
              </tr>
            `;
            document.getElementById('statTotalProjects').textContent = "0";
            document.getElementById('statPhotosStored').textContent = "0";
            document.getElementById('statProcessingJobs').textContent = "0";
            document.getElementById('paginationText').textContent = "Showing 0 entries";
            updateStorageUI(0);
            return;
          }

          // Store data globally for modal lookup
          window.myUploadsData = data;
          
          let totalPhotos = 0;
          let processingJobs = 0;
          let totalBytes = 0;

          data.forEach(item => {
            // Stats calculation
            const count = parseInt(item.file_count) || 0;
            totalPhotos += count;
            
            // Use exact size from DB if available, otherwise fall back to dummy estimate
            const itemSizeBytes = parseInt(item.total_size_bytes) || (count * 1024 * 1024 * 3.5);
            totalBytes += itemSizeBytes;

            // Status Logic (request_status: pending → accepted → processing → sent (Received) → completed)
            let statusHtml = '';
            let statusVal = (item.request_status || 'pending').toLowerCase();
            
            if (statusVal === 'pending') {
              statusHtml = `
                <span class="badge bg-label-warning"><i class="bx bx-time-five me-1"></i> Pending</span>
                <div class="text-muted mt-1" style="font-size: 0.7rem;">Waiting for Admin</div>
              `;
            } else if (statusVal === 'accepted') {
              statusHtml = `
                <span class="badge bg-label-info"><i class="bx bx-check me-1"></i> Accepted</span>
                <div class="text-muted mt-1" style="font-size: 0.7rem;">Admin will process</div>
  `            ;
            } else if (statusVal === 'review') {
              statusHtml = `
                <span class="badge bg-label-secondary"><i class="bx bx-search-alt me-1"></i> Under Review</span>
                <div class="text-muted mt-1" style="font-size: 0.7rem;">Admin is reviewing your files</div>
  `            ;
            } else if (statusVal === 'processing') {
              processingJobs++;
              statusHtml = `
                <span class="badge-status status-processing"><i class="bx bx-loader-alt bx-spin me-1"></i> Processing</span>
                <div class="text-muted mt-1" style="font-size: 0.7rem;">3D model in progress</div>
              `;
            } else if (statusVal === 'sent') {
              statusHtml = `
                <span class="badge bg-label-info"><i class="bx bx-package me-1"></i> Received</span>
                <div class="text-muted mt-1" style="font-size: 0.7rem;">Download 3D model below, then confirm</div>
              `;
            } else if (statusVal === 'completed') {
              statusHtml = `
                <span class="badge-status status-completed"><i class="bx bx-check-circle me-1"></i> Completed</span>
              `;
            } else if (statusVal === 'rejected') {
              statusHtml = `
                <span class="badge-status status-failed"><i class="bx bx-x-circle me-1"></i> Rejected</span>
                <div class="text-danger mt-1 text-truncate" style="font-size: 0.7rem; max-width: 150px;" title="${(item.rejected_reason || 'Unknown').replace(/"/g, '&quot;')}">${(item.rejected_reason || 'Rejected by admin').replace(/</g, '&lt;')}</div>
              `;
            }

            // Configurations UI
            let configHtml = '';
            if (item.upload_type === 'google_drive') {
              configHtml = `<span class="badge bg-label-success mb-1"><i class="bx bxl-google-cloud me-1"></i> Google Drive</span><br>`;
              configHtml += `<a href="${item.google_drive_link}" target="_blank" class="small text-primary text-truncate d-block" style="max-width: 150px;" title="${item.google_drive_link}">View Shared Link</a>`;
            } else if (item.upload_type === 'onedrive') {
              configHtml = `<span class="badge bg-label-primary mb-1"><i class="bx bx-cloud me-1"></i> OneDrive</span><br>`;
              configHtml += `<a href="${item.onedrive_link}" target="_blank" class="small text-primary text-truncate d-block" style="max-width: 150px;" title="${item.onedrive_link}">View Shared Link</a>`;
            } else if (item.upload_type === 'sftp' || (item.upload_type && item.upload_type.startsWith('sftp_'))) {
              configHtml = `<span class="badge bg-label-info mb-1"><i class="bx bx-server me-1"></i> SFTP Source</span><br>`;
              const isMulti = (item.upload_type === 'sftp_multiple');
              configHtml += `<span class="badge bg-label-secondary mb-1">${isMulti ? 'Multi-Lens' : 'Single-Lens'}</span>`;
            } else {
              const isMultiLens = (item.camera_models && item.camera_models.startsWith('Multi-Lens')) || (item.upload_type === 'multilens' || item.upload_type === 'multiple');
              const hasPos = item.drone_pos_file_path ? true : false;
              configHtml = `<span class="badge bg-label-secondary mb-1">${isMultiLens ? 'Multi-Lens' : 'Single-Lens'}</span><br>`;
              if (hasPos) configHtml += `<span class="badge bg-label-dark"><i class="bx bx-target-lock me-1"></i> POS Attached</span>`;
            }

            // Download Button / Expiry logic (v245: Robust Expiry with Fallback)
            let downloadHtml = '';
            let expiryIconHtml = '';
            let isExpired = false;
            
            const deliveredAt = item.delivered_at ? new Date(item.delivered_at) : null;
            let expiresAt = item.delivered_expires_at ? new Date(item.delivered_expires_at) : null;

            // 🚀 FALLBACK LOGIC (v245): If no expiry date exists, assume 7 days from delivery
            if (!expiresAt && deliveredAt) {
                expiresAt = new Date(deliveredAt.getTime() + (7 * 24 * 60 * 60 * 1000));
            }

            if (expiresAt) {
                const now = new Date();
                const diffMs = expiresAt - now;
                const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
                
                if (diffMs < 0) {
                    isExpired = true;
                    expiryIconHtml = `<div class="text-danger mt-1 fw-bold" style="font-size: 0.7rem;"><i class="bx bx-error-circle me-1"></i> Link Expired</div>`;
                } else {
                    expiryIconHtml = `<div class="text-warning mt-1 fw-medium" style="font-size: 0.7rem;"><i class="bx bx-time me-1"></i> Expires in ${diffDays} days</div>`;
                }
            }

            const deliveryPath = item.delivered_file_path || (item.delivery_method === 'google_drive' ? item.google_drive_link : null);

            const isCloudDelivery = (item.delivery_method === 'google_drive' || item.delivery_method === 'onedrive');

            // 🚀 ROBUST DROPDOWN (v246): Always show status in dropdown if expired, even if path is missing
            if (isExpired) {
                let icon = 'bx-download';
                let label = 'Link Expired';
                if (item.delivery_method === 'google_drive') { icon = 'bxl-google-cloud'; label = 'Link Expired (Google Drive)'; }
                else if (item.delivery_method === 'onedrive') { icon = 'bx-cloud'; label = 'Link Expired (OneDrive)'; }
                
                downloadHtml = `
                    <li class="opacity-50">
                        <a class="dropdown-item btn-dropdown-link text-muted disabled" href="javascript:void(0);">
                            <i class="bx ${icon} me-2"></i> ${label}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>`;
            } else if (isCloudDelivery && deliveryPath && (statusVal === 'sent' || statusVal === 'completed')) {
                const icon = item.delivery_method === 'google_drive' ? 'bxl-google-cloud' : 'bx-cloud';
                const color = item.delivery_method === 'google_drive' ? 'text-primary' : 'text-primary';
                const label = item.delivery_method === 'google_drive' ? 'Download (Google Drive)' : 'Download (OneDrive)';
                
                downloadHtml = `
                    <li>
                        <a class="dropdown-item btn-dropdown-link ${color} fw-bold" 
                           href="${deliveryPath}" 
                           target="_blank">
                            <i class="bx ${icon} me-2"></i> ${label}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>`;
            } else if (deliveryPath && (statusVal === 'sent' || statusVal === 'completed')) {
                downloadHtml = `
                    <li>
                        <a class="dropdown-item btn-dropdown-link text-success fw-bold" 
                           href="javascript:void(0);" 
                           onclick="downloadDeliveredFile(${item.id})">
                           <i class="bx bx-download me-2"></i> Download 3D Model
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>
                <div class="d-flex align-items-center">
                  <div class="project-thumb d-flex align-items-center justify-content-center text-${statusVal === 'completed' ? 'success' : 'primary'} fs-3 me-3">
                    <i class="bx ${statusVal === 'completed' ? 'bx-map' : 'bx-map-alt'}"></i>
                  </div>
                  <div>
                    <div class="project-name">${item.project_title || item.project_id}</div>
                    <div class="project-meta text-truncate" style="max-width: 300px;">${item.project_description || 'No description provided.'}</div>
                  </div>
                </div>
              </td>
              <td>
                ${statusHtml}
                ${((statusVal === 'completed' || statusVal === 'sent') && expiryIconHtml) ? expiryIconHtml : ''}
              </td>
              <td>
                <div class="fw-medium text-dark" style="font-size: 0.9rem;">${formatDate(item.created_at)}</div>
                <div class="project-meta">
                  ${formatBytes(itemSizeBytes)} • 
                  <span id="photoCount-${item.id}">${count} Photos</span>
                </div>
              </td>
              <td>
                ${(statusVal === 'completed') ? 
                  `<div class="fw-bold text-success" style="font-size: 0.85rem;">${formatDate(item.delivered_at)}</div>` : 
                  `<div class="text-muted small">–</div>`}
              </td>
              <td>${configHtml}</td>
              <td class="text-center">
                <div class="dropdown">
                  <button type="button" class="action-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    ${downloadHtml}
                    ${statusVal === 'sent' ? '<li><a class="dropdown-item btn-dropdown-link text-success fw-medium" href="javascript:void(0);" onclick="confirmReceived(' + item.id + ')"><i class="bx bx-check-circle"></i> Confirm Received</a></li>' : ''}
                    ${(item.upload_type && item.upload_type.includes('sftp')) ? '<li><a class="dropdown-item btn-dropdown-link text-primary" href="javascript:void(0);" onclick="syncSftpMetadata(' + item.id + ')"><i class="bx bx-refresh"></i> Sync Data Info</a></li>' : ''}
                    ${(item.upload_type === 'google_drive') ? '<li><a class="dropdown-item btn-dropdown-link text-primary" href="javascript:void(0);" onclick="syncGoogleDriveMetadata(' + item.id + ')"><i class="bx bx-refresh"></i> Sync Data Info</a></li>' : ''}
                    ${(item.upload_type === 'onedrive') ? '<li><a class="dropdown-item btn-dropdown-link text-primary" href="javascript:void(0);" onclick="syncOneDriveMetadata(' + item.id + ')"><i class="bx bx-refresh"></i> Sync Data Info</a></li>' : ''}
                    <li><a class="dropdown-item btn-dropdown-link" href="javascript:void(0);" onclick="showProjectDetails(${item.id})"><i class="bx bx-info-circle text-info"></i> View Details</a></li>
                    <li><a class="dropdown-item btn-dropdown-link" href="javascript:void(0);" onclick="showEditModal(${item.id})"><i class="bx bx-edit text-secondary"></i> Edit Metadata</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item btn-dropdown-link text-danger" href="javascript:void(0);" onclick="deleteProject('${item.id}')"><i class="bx bx-trash"></i> Delete Project</a></li>
                  </ul>
                </div>
              </td>
            `;
            tbody.appendChild(tr);

            // 🚀 AUTO-SYNC (v235): Trigger background sync if data seems incomplete
            const needsSync = (parseInt(item.total_size_bytes) === 0 || !item.total_size_bytes || parseInt(item.file_count) <= 1 || !item.file_count);
            if (item.upload_type && item.upload_type.includes('sftp') && (parseInt(item.file_count) === 0 || !item.file_count)) {
                setTimeout(() => syncSftpMetadata(item.id, true), 1000);
            }
            if (item.upload_type === 'google_drive' && needsSync) {
                setTimeout(() => syncGoogleDriveMetadata(item.id, true), 1500);
            }
            if (item.upload_type === 'onedrive' && needsSync) {
                setTimeout(() => syncOneDriveMetadata(item.id, true), 1500);
            }
          });

          // Update Stats UI
          document.getElementById('statTotalProjects').textContent = data.length.toLocaleString();
          document.getElementById('statPhotosStored').textContent = totalPhotos.toLocaleString();
          document.getElementById('statProcessingJobs').textContent = processingJobs.toLocaleString();
          
          // Initialize Pagination
          applyPagination();
          
          // Dummy UI Storage recalculate
          updateStorageUI(totalBytes);

        })
        .catch(err => {
          console.error("Error fetching uploads:", err);
          document.getElementById('uploadsTableBody').innerHTML = `
            <tr><td colspan="6" class="text-danger text-center py-4">Failed to connect to database. Please refresh.</td></tr>
          `;
        });
    }

    function confirmReceived(uploadId) {
      if (!confirm("Confirm that you have received the 3D model? This will mark the request as completed.")) return;
      fetch('/api/user/my-uploads/' + uploadId + '/confirm-received', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            fetchUploadsList();
          } else {
            alert(data.message || "Could not confirm.");
          }
        })
        .catch(function () { alert("Request failed."); });
    }

    function downloadDeliveredFile(uploadId) {
      // Navigate to the API route which streams the file from the SFTP disk
      // ProjectController@downloadDelivered handles auth + file existence checks
      window.location.href = '/api/user/my-uploads/' + uploadId + '/download-delivered';
    }

    function deleteProject(projectId) {
      document.getElementById('deleteProjectId').value = projectId;
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteProjectModal'));
      deleteModal.show();
    }

    function executeDeleteProject() {
      const projectId = document.getElementById('deleteProjectId').value;
      const deleteBtn = document.getElementById('confirmDeleteBtn');
      const originalText = deleteBtn.innerHTML;
      
      deleteBtn.disabled = true;
      deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

      fetch('/api/user/my-uploads/' + projectId, {
        method: 'DELETE',
        headers: { 
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          bootstrap.Modal.getInstance(document.getElementById('deleteProjectModal')).hide();
          fetchUploadsList(); // Automatically reload the table UI after deletion
        } else {
          alert("Could not delete project: " + (data.message || "Unknown error"));
        }
      })
      .catch(err => {
        console.error("Delete Error:", err);
        alert("A network error occurred while trying to erase the project.");
      })
      .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
      });
    }

    const syncingProjects = new Set();

    function syncSftpMetadata(uploadId, isSilent = false) {
      if (syncingProjects.has(uploadId)) return;
      syncingProjects.add(uploadId);
      
      fetch('/api/user/my-uploads/' + uploadId + '/sync-metadata', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // 🚀 UI-SYNC (v210): Trigger a full list refresh to update the Top Cards and Storage Quota
          fetchUploadsList();
        }
      })
      .catch(err => console.error("Sync Error:", err))
      .finally(() => {
         // Keep it in the set for a while to prevent immediate re-sync
         setTimeout(() => syncingProjects.delete(uploadId), 30000); 
      });
    }

    function syncGoogleDriveMetadata(uploadId, isSilent = false) {
      if (syncingProjects.has(uploadId)) return;
      syncingProjects.add(uploadId);
      
      fetch('/api/user/my-uploads/' + uploadId + '/sync-gdrive', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const countEl = document.getElementById('photoCount-' + uploadId);
          if (countEl) {
            countEl.textContent = data.count + ' Photos';
            // Update the size text which is usually the previous text node or element
            const sizeEl = document.getElementById('photoCount-' + uploadId).previousSibling;
            if (sizeEl) {
               // Update the text node directly if possible
               let sizeText = data.formattedSize + ' \u2022 ';
               if (sizeEl.nodeType === Node.TEXT_NODE) {
                   sizeEl.textContent = sizeText;
               }
            }
          }
        }
      })
      .catch(err => console.error("GDrive Sync Error:", err))
      .finally(() => {
         setTimeout(() => syncingProjects.delete(uploadId), 30000); 
      });
    }

    function syncOneDriveMetadata(uploadId, isSilent = false) {
      if (syncingProjects.has(uploadId)) return;
      syncingProjects.add(uploadId);
      
      fetch('/api/user/my-uploads/' + uploadId + '/sync-onedrive', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const countEl = document.getElementById('photoCount-' + uploadId);
          if (countEl) {
            countEl.textContent = data.count + ' Photos';
            const sizeEl = document.getElementById('photoCount-' + uploadId).previousSibling;
            if (sizeEl) {
                let sizeText = data.formattedSize + ' \u2022 ';
                if (sizeEl.nodeType === Node.TEXT_NODE) {
                    sizeEl.textContent = sizeText;
                }
            }
          }
        }
      })
      .catch(err => console.error("OneDrive Sync Error:", err))
      .finally(() => {
        setTimeout(() => syncingProjects.delete(uploadId), 30000); 
      });
    }

    function goToPage(pageNum) {
      window.currentPage = pageNum;
      applyPagination();
    }

    function changePage(delta) {
      const newPage = window.currentPage + delta;
      if (newPage >= 1 && newPage <= 3) {
        goToPage(newPage);
      }
    }

    function applyPagination() {
      const tbody = document.getElementById('uploadsTableBody');
      if (!tbody) return;

      const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-page-row)'));
      
      // Remove existing empty page row
      const existingEmpty = tbody.querySelector('.empty-page-row');
      if (existingEmpty) existingEmpty.remove();

      // Check if we are in "No datasets found" or "Loading" state
      if (rows.length === 1) {
        const firstRowText = rows[0].textContent.toLowerCase();
        if (firstRowText.includes('no datasets found') || firstRowText.includes('loading')) {
          updatePaginationUI(0);
          return;
        }
      }

      const filter = (document.getElementById('projectSearch')?.value || "").toLowerCase();
      const filteredRows = rows.filter(row => {
        const text = row.textContent.toLowerCase();
        return text.includes(filter);
      });

      // Hide all rows first
      rows.forEach(row => row.style.display = 'none');

      let visibleCount = 0;
      filteredRows.forEach((row, index) => {
        const pageOfRow = Math.floor(index / window.pageSize) + 1;
        if (pageOfRow === window.currentPage) {
          row.style.display = '';
          visibleCount++;
        }
      });

      // Handle empty page higher than page 1
      if (visibleCount === 0 && window.currentPage > 1) {
        const lastValidPage = Math.max(1, Math.ceil(filteredRows.length / window.pageSize));
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-page-row';
        emptyRow.innerHTML = `
          <td colspan="6" class="text-center py-5">
            <div class="text-muted mb-2"><i class="bx bx-folder-open" style="font-size: 3rem; opacity: 0.5;"></i></div>
            <h6 class="mb-1 fw-bold text-dark">No more history</h6>
            <p class="text-muted small">This page doesn't have any uploaded files yet.</p>
            <a href="javascript:void(0)" onclick="goToPage(${lastValidPage})" class="btn btn-primary btn-sm mt-2">Go back to view your uploaded file history</a>
          </td>
        `;
        tbody.appendChild(emptyRow);
      }

      updatePaginationUI(filteredRows.length);
    }

    function updatePaginationUI(totalEntries) {
      const start = (window.currentPage - 1) * window.pageSize + 1;
      const end = Math.min(window.currentPage * window.pageSize, totalEntries);
      
      const pagText = document.getElementById('paginationText');
      if (pagText) {
        if (totalEntries === 0) {
          pagText.textContent = 'Showing 0 entries';
        } else if (start > totalEntries) {
          // The current page starts after the last entry
          pagText.textContent = `Showing 0 entries of ${totalEntries} total`;
        } else {
          pagText.textContent = `Showing ${start} to ${end} of ${totalEntries} entries`;
        }
      }

      // Update active class on page buttons
      for (let i = 1; i <= 3; i++) {
        const item = document.getElementById(`page${i}Item`);
        if (item) {
          if (i === window.currentPage) item.classList.add('active');
          else item.classList.remove('active');
        }
      }

      // Update Previous/Next disabled state
      const prevItem = document.getElementById('prevPageItem');
      const nextItem = document.getElementById('nextPageItem');
      
      if (prevItem) {
        if (window.currentPage === 1) prevItem.classList.add('disabled');
        else prevItem.classList.remove('disabled');
      }
      
      if (nextItem) {
        if (window.currentPage === 3) nextItem.classList.add('disabled');
        else nextItem.classList.remove('disabled');
      }
    }

    // Live Search Logic
    function copyTextFromElement(elId, btn) {
      const text = document.getElementById(elId).textContent;
      navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        const oldClass = icon.className;
        icon.className = 'bx bx-check';
        setTimeout(() => icon.className = oldClass, 2000);
      });
    }

    function filterProjects() {
      window.currentPage = 1;
      applyPagination();
    }

    function showProjectDetails(projectId) {
      if (!window.myUploadsData) return;
      const project = window.myUploadsData.find(p => p.id === projectId);
      if (!project) return;

      document.getElementById('detailModalTitle').textContent = project.project_title || project.project_id;
      document.getElementById('detailDescription').textContent = project.project_description || 'No description provided.';
      document.getElementById('detailCategory').textContent = project.category || 'Environmental';
      const coords = (project.latitude != null && project.longitude != null) 
        ? `${parseFloat(project.latitude).toFixed(4)}, ${parseFloat(project.longitude).toFixed(4)}`
        : 'Pending (SFTP Scan)';
      document.getElementById('detailCoordinates').textContent = coords;
      
      const isMulti = (project.camera_models && project.camera_models.startsWith('Multi-Lens')) || (project.upload_type === 'multilens' || project.upload_type === 'multiple' || project.upload_type === 'sftp_multiple');
      document.getElementById('detailConfig').textContent = isMulti ? 'Multi-Lens' : 'Single-Lens';
      
      // 🚀 CLEAN-DISPLAY (v283): Strip redundant prefixes for a professional look
      let displayModel = project.camera_models || '';
      if (displayModel === 'Single-Lens' || displayModel === 'Multi-Lens') {
          displayModel = isMulti ? 'Multiple' : 'Standard';
      } else {
          displayModel = displayModel.replace(/^Single-Lens:\s*/i, '').replace(/^Multi-Lens:\s*/i, '');
      }
      document.getElementById('detailModels').textContent = displayModel;
      document.getElementById('detailDate').textContent = formatDate(project.created_at);

      // 🚀 DELIVERY PATH (v130): Show SFTP path if available
      const delSection = document.getElementById('detailDeliverySection');
      if (project.upload_type && project.upload_type.includes('sftp') && project.delivered_file_path) {
          delSection.classList.remove('d-none');
          
          // 🚀 JAIL-AWARE PATH STRIPPING (v217): Strip everything before and including the username for chrooted view
          let displayPath = project.delivered_file_path;
          const sftpUser = project.client_sftp_user || '';
          
          if (sftpUser && displayPath.includes('/' + sftpUser + '/')) {
              // Extract everything after the username folder
              displayPath = displayPath.substring(displayPath.indexOf('/' + sftpUser + '/') + sftpUser.length + 1);
          } else {
              // Fallback: Remove known system roots
              const rootPrefix = (window.remoteBasePath || '/home/tiquan/uploads').replace(/\/+$/, '');
              if (displayPath.startsWith(rootPrefix)) {
                  displayPath = displayPath.substring(rootPrefix.length);
              }
              if (sftpUser && displayPath.startsWith('/' + sftpUser)) {
                  displayPath = displayPath.substring(sftpUser.length + 1);
              }
          }
          
          // Ensure it starts with / for WinSCP navigation
          if (!displayPath.startsWith('/')) displayPath = '/' + displayPath;
          
          // Handle filenames vs folders
          if (displayPath.includes('.') && displayPath.lastIndexOf('/') > 0) {
              displayPath = displayPath.substring(0, displayPath.lastIndexOf('/'));
          }
          if (!displayPath.endsWith('/')) displayPath += '/';
          
          // Step 5: Final cleanup (prevent double slashes)
          displayPath = displayPath.replace(/\/+/g, '/');

          document.getElementById('detailDeliveryPath').textContent = displayPath;
      } else {
          delSection.classList.add('d-none');
      }

      const modal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
      modal.show();
    }

    function showEditModal(projectId) {
      if (!window.myUploadsData) return;
      const project = window.myUploadsData.find(p => p.id === projectId);
      if (!project) return;

      document.getElementById('editProjectId').value = project.id;
      document.getElementById('editProjectTitle').value = project.project_title || project.project_id;
      document.getElementById('editProjectDescription').value = project.project_description || '';

      const editModal = new bootstrap.Modal(document.getElementById('editMetadataModal'));
      editModal.show();
    }

    async function saveProjectMetadata() {
      const id = document.getElementById('editProjectId').value;
      const title = document.getElementById('editProjectTitle').value.trim();
      const description = document.getElementById('editProjectDescription').value.trim();

      if (!title) {
        alert("Project title is required.");
        return;
      }

      const saveBtn = document.getElementById('saveEditBtn');
      const originalText = saveBtn.textContent;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');

      try {
        const response = await fetch(`${AUTH_API}/api/user/my-uploads/${id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ project_title: title, project_description: description }),
          credentials: 'include'
        });

        const data = await response.json();
        if (data.success) {
          bootstrap.Modal.getInstance(document.getElementById('editMetadataModal')).hide();
          fetchUploadsList(); // Reload table
        } else {
          alert("Error: " + (data.message || "Failed to update metadata."));
        }
      } catch (err) {
        console.error("Save Metadata Error:", err);
        alert("A network error occurred.");
      } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
      }
    }

    function updateStorageUI(totalBytes) {
      const gb = (totalBytes / (1024 * 1024 * 1024)).toFixed(1);
      const percent = Math.min((gb / 100) * 100, 100).toFixed(1);
      
      const usedText = document.getElementById('storageUsedText');
      const progressBar = document.getElementById('storageProgressBar');
      const statusText = document.getElementById('storageStatusText');
      
      if (usedText) usedText.textContent = gb + ' GB Used';
      if (progressBar) {
        progressBar.style.width = percent + '%';
        progressBar.setAttribute('aria-valuenow', percent);
      }
      if (statusText) {
        statusText.innerHTML = `You have used ${percent}% of your available storage.`;
      }
    }
  </script>
</body>
</html>
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/portal/my-uploads.blade.php ENDPATH**/ ?>