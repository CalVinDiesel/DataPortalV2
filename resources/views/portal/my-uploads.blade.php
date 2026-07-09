<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>My Projects | 3DHub Data Portal</title>

  <script src="{{ asset('assets') }}/js/theme-init.js"></script>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">
    <!-- 📦 CONSOLE CLEANUP (v62): Removed external links to stop Tracking Prevention warnings -->

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">

  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>

  <!-- Auth Protection -->
  <script>
    (function () {
      window.userRole = '{{ Auth::user()->role }}';
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
        <a href="{{ route('landing') }}" class="btn btn-label-secondary btn-sm fw-medium border shadow-sm back-btn" style="background: white; color: #566a7f;">
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
        <a href="{{ route('create_project') }}" class="btn btn-primary shadow-sm">
          <i class="bx bx-plus me-1"></i> New Project
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container mt-4 mb-5 pb-5">
    
    <!-- Quota Exceeded Banner -->
    <div id="quotaWarningBanner" class="alert alert-danger d-none d-flex align-items-center mb-4 p-3 shadow-sm" role="alert" style="border-radius: 8px;">
      <i class="bx bx-error-alt me-2 fs-3 text-danger"></i>
      <div>
        <h6 class="alert-heading mb-1 fw-bold text-danger">Storage Full!</h6>
        <span style="font-size: 0.85rem;">You have exceeded your total storage limit. New project creation and raw file uploads are currently blocked. Please delete past completed projects to free up space.</span>
      </div>
    </div>

    <!-- Session Flash Error -->
    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center mb-4 p-3 shadow-sm" role="alert" style="border-radius: 8px;">
      <i class="bx bx-error-alt me-2 fs-3 text-danger"></i>
      <div>
        <span style="font-size: 0.85rem;">{{ session('error') }}</span>
      </div>
    </div>
    @endif
    
    <!-- Dashboard Stats Row -->
    @php
      $user = auth()->user();
      $usedBytes = $user ? \App\Models\ClientUpload::calculateUserStorageUsed($user->email) : 0;
      $limitBytes = $user ? \App\Models\ClientUpload::getStorageLimitBytes($user->email) : 5 * 1024 * 1024 * 1024;
      
      $usedGb = number_format($usedBytes / (1024 * 1024 * 1024), 1);
      $limitGb = number_format($limitBytes / (1024 * 1024 * 1024), 0);
      $percent = $limitBytes > 0 ? round(($usedBytes / $limitBytes) * 100, 1) : 0;
      $hasExceeded = $usedBytes >= $limitBytes;
    @endphp
    <div class="row g-4 mb-5">
      <!-- Storage Quota -->
      <div class="col-lg-5 col-md-12">
        <div class="storage-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Storage Quota</h5>
            <span class="badge bg-label-primary">Pro Plan</span>
          </div>
          <div class="d-flex justify-content-between text-muted small mb-2">
            <span id="storageUsedText">{{ $usedGb }} GB Used</span>
            <span id="storageTotalText">{{ $limitGb }} GB Total</span>
          </div>
          <div class="progress" style="height: 10px; border-radius: 10px;">
            <div id="storageProgressBar" class="progress-bar {{ $hasExceeded ? 'bg-danger' : ($percent > 85 ? 'bg-warning' : 'bg-primary') }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div id="storageStatusText" class="mt-3 text-muted" style="font-size: 0.8rem;">
            You have used {{ $percent }}% of your available storage.
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
              <th style="width: 25%">Project Information</th>
              <th>Status</th>
              <th>Date / Size</th>
              <th>Delivered Date</th>
              <th>Configuration</th>
              <th class="text-center" style="width: 12%">Preview Map</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="uploadsTableBody">
            <tr>
              <td colspan="7" class="text-center py-5">
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
                 <div id="detailDeliveryPath" class="flex-grow-1 bg-transparent border-0 text-dark" style="word-break: break-all;"></div>
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

  <!-- 🚀 3D Data Download Disclaimer Modal (v176) -->
  <div class="modal fade" id="disclaimerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header py-3 bg-light">
          <h5 class="modal-title fw-bold text-dark"><i class="bx bx-shield-quarter me-2 text-primary"></i> 3D Data Download & Usage Disclaimer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 p-md-5">
          <div class="disclaimer-content mb-4 p-3 border rounded bg-lighter" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem; line-height: 1.6;">
            <p class="fw-bold text-danger">IMPORTANT: PLEASE READ CAREFULLY BEFORE DOWNLOADING.</p>
            <p>By clicking "<strong>I AGREE</strong>" or downloading the 3D processed model (the "Data"), you acknowledge that you have read, understood, and agreed to be bound by the following terms:</p>
            
            <h6 class="fw-bold mt-4">1. For Reference Purposes Only</h6>
            <p>This 3D processed model is provided solely for <strong>self-referencing and informational purposes</strong>. It is a digital representation generated through automated processing techniques and may not reflect the actual, physical dimensions, specifications, or conditions of the subject with absolute precision.</p>

            <h6 class="fw-bold mt-4">2. No Warranty of Accuracy</h6>
            <p>The Data is provided on an <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> basis. <strong>3D Hub</strong> makes no representations or warranties of any kind, express or implied, regarding the accuracy, completeness, reliability, or suitability of the 3D model. Users are warned that digital artifacts, interpolation errors, or processing limitations may exist.</p>

            <h6 class="fw-bold mt-4">3. Limitation of Liability</h6>
            <p>In no event shall <strong>3D Hub</strong> or its parent company be held liable for any direct, indirect, incidental, special, or consequential <strong>losses or damages</strong> (including, but not limited to, financial loss, personal injury, or property damage) arising out of the use, inability to use, or reliance upon this 3D model.</p>

            <h6 class="fw-bold mt-4">4. User Responsibility</h6>
            <p>The user assumes <strong>full responsibility</strong> for verifying the accuracy of any measurements, designs, or calculations derived from this Data. If the Data is to be used for construction, manufacturing, or professional engineering, the user is advised to perform independent field verification.</p>

            <h6 class="fw-bold mt-4">5. No Modification or Redistribution</h6>
            <p>Unless otherwise stated, this Data is intended for the recipient’s personal or internal use only. Unauthorized redistribution, modification, or commercial resale of the processed model is strictly prohibited.</p>
            
            <hr>
            <p class="small text-muted italic">Note: This record of agreement will be logged with your User ID and IP address for compliance purposes.</p>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="disclaimerCheckbox" onchange="toggleDisclaimerBtn()">
            <label class="form-check-label fw-bold text-dark" for="disclaimerCheckbox">
              I have read and agree to the 3D Data Disclaimer
            </label>
          </div>
          
          <input type="hidden" id="disclaimerProjectId">
          <input type="hidden" id="disclaimerTargetUrl">
          <input type="hidden" id="disclaimerIsCloud" value="0">
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary px-5 fw-bold" id="agreeDownloadBtn" disabled onclick="handleDisclaimerAgreement()">
            <i class="bx bx-download me-1"></i> I Agree & Download
          </button>
        </div>
      </div>
    </div>
  </div>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
  </form>

  <!-- Core Scripts -->
  <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  
  <script>
    // 🚀 DYNAMIC CONFIG (v196)
    window.remoteBasePath = '{{ config("filesystems.disks.sftp_delivery.root", "/srv/sftpgo/data/") }}';
    
    function logout() {
      if (!confirm('Are you sure you want to log out?')) return;
      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      var LANDING_URL = '{{ route('landing') }}';
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
      return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit',
        timeZone: 'Asia/Kuala_Lumpur'
      });
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
                  <a href="{{ route('create_project') }}" class="btn btn-primary btn-sm mt-2">Start your first upload</a>
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
              const isMulti = (item.upload_type && item.upload_type.includes('multiple')) || 
                            (item.camera_models && (item.camera_models.toLowerCase().includes('multi-lens') || item.camera_models.toLowerCase().includes('multiple')));
              configHtml += `<span class="badge bg-label-secondary mb-1">${isMulti ? 'Multi-Lens' : 'Single-Lens'}</span>`;
            } else {
              const isMultiLens = (item.upload_type && item.upload_type.includes('multiple')) || 
                               (item.camera_models && (item.camera_models.toLowerCase().includes('multi-lens') || item.camera_models.toLowerCase().includes('multiple')));
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
            // 🚀 DISCLAIMER GATE (v176): Use initiateDownloadWithDisclaimer for all methods
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
                const label = item.delivery_method === 'google_drive' ? 'Download (Google Drive)' : 'Download (OneDrive)';
                
                downloadHtml = `
                    <li>
                        <a class="dropdown-item btn-dropdown-link text-primary fw-bold" 
                           href="javascript:void(0);" 
                           onclick="initiateDownloadWithDisclaimer(${item.id}, '${deliveryPath}', true)">
                            <i class="bx ${icon} me-2"></i> ${label}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>`;
            } else if (deliveryPath && (statusVal === 'sent' || statusVal === 'completed')) {
                downloadHtml = `
                    <li>
                        <a class="dropdown-item btn-dropdown-link text-success fw-bold" 
                           href="javascript:void(0);" 
                           onclick="initiateDownloadWithDisclaimer(${item.id}, '', false)">
                           <i class="bx bx-download me-2"></i> Download 3D Model
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>`;
            }

            // Locate where your tr.innerHTML is defined inside data.forEach(item => { ... })
            // and replace it with the structure below:

            const isReadyForPreview = (statusVal === 'completed' || statusVal === 'sent') && item.delivered_file_path;

            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>
                <div class="d-flex align-items-center">
                  <div class="project-thumb d-flex align-items-center justify-content-center text-${statusVal === 'completed' ? 'success' : 'primary'} fs-3 me-3">
                    <i class="bx ${statusVal === 'completed' ? 'bx-map' : 'bx-map-alt'}"></i>
                  </div>
                  <div>
                    <div class="project-name">${item.project_title || item.project_id}</div>
                    <div class="project-meta text-truncate" style="max-width: 250px;">${item.project_description || 'No description provided.'}</div>
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
                ${isReadyForPreview ? `
                  <button type="button" class="btn btn-sm btn-icon btn-label-primary" title="Preview 3D Map" onclick="open3DPreviewModal(${item.id})">
                    <i class="bx bx-show-alt fs-4"></i>
                  </button>
                ` : `
                  <span class="badge bg-label-secondary btn-sm" style="font-size:0.75rem;"><i class="bx bx-lock me-1"></i> Unavailable</span>
                `}
              </td>

              <td class="text-center">
                <div class="dropdown">
                  <button type="button" class="action-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    ${downloadHtml}
                    ${isReadyForPreview ? `<li><a class="dropdown-item btn-dropdown-link text-primary" href="javascript:void(0);" onclick="open3DPreviewModal(${item.id})"><i class="bx bx-show-alt"></i> Interactive Preview</a></li>` : ''}
                    ${statusVal === 'sent' ? '<li><a class="dropdown-item btn-dropdown-link text-success fw-medium" href="javascript:void(0);" onclick="confirmReceived(' + item.id + ')"><i class="bx bx-check-circle"></i> Confirm Received</a></li>' : ''}
                    ${(item.upload_type && item.upload_type.includes('sftp')) ? '<li><a class="dropdown-item btn-dropdown-link text-primary" href="javascript:void(0);" onclick="syncSftpMetadata(' + item.id + ')"><i class="bx bx-refresh"></i> Sync Data Info</a></li>' : ''}
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
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

    // 🚀 DISCLAIMER LOGIC (v176)
    function initiateDownloadWithDisclaimer(projectId, targetUrl, isCloud) {
      document.getElementById('disclaimerProjectId').value = projectId;
      document.getElementById('disclaimerTargetUrl').value = targetUrl;
      document.getElementById('disclaimerIsCloud').value = isCloud ? "1" : "0";
      
      // Reset modal state fully — including button label, in case a previous session left it in a loading state
      const agreeBtn = document.getElementById('agreeDownloadBtn');
      document.getElementById('disclaimerCheckbox').checked = false;
      agreeBtn.disabled = true;
      agreeBtn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';
      
      const modal = new bootstrap.Modal(document.getElementById('disclaimerModal'));
      modal.show();
    }

    function toggleDisclaimerBtn() {
      const checkbox = document.getElementById('disclaimerCheckbox');
      document.getElementById('agreeDownloadBtn').disabled = !checkbox.checked;
    }

    function handleDisclaimerAgreement() {
      const projectId = document.getElementById('disclaimerProjectId').value;
      const targetUrl = document.getElementById('disclaimerTargetUrl').value;
      const isCloud = document.getElementById('disclaimerIsCloud').value === "1";
      const btn = document.getElementById('agreeDownloadBtn');
      
      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Preparing...';

      // 1. Log agreement to database in the background (asynchronously)
      fetch('/api/user/my-uploads/' + projectId + '/accept-disclaimer', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ agreed: true })
      }).catch(err => console.error("Disclaimer logging failed:", err));

      // 2. Hide modal immediately
      const modalEl = document.getElementById('disclaimerModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) modalInstance.hide();
      
      // 3. Trigger Action synchronously to prevent browser popup blocking
      if (targetUrl === 'REVEAL_SFTP') {
        const unlockContainer = document.getElementById('sftpUnlockContainer');
        const actualPath = document.getElementById('sftpActualPath');
        if (unlockContainer) unlockContainer.classList.add('d-none');
        if (actualPath) {
            actualPath.classList.remove('d-none');
            actualPath.classList.add('d-flex');
            actualPath.classList.add('align-items-center');
        }
      } else if (isCloud && targetUrl) {
        window.open(targetUrl, '_blank');
      } else {
        // Trigger download directly in a separate background tab context (won't block navigation)
        const downloadUrl = '/api/user/my-uploads/' + projectId + '/download-delivered';
        window.open(downloadUrl, '_blank');
      }

      // Restore agreement button state for subsequent triggers
      setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-download me-1"></i> I Agree & Download';
      }, 1000);
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
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
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


    function syncOneDriveMetadata(uploadId, isSilent = false) {
      if (syncingProjects.has(uploadId)) return;
      syncingProjects.add(uploadId);
      
      fetch('/api/user/my-uploads/' + uploadId + '/sync-onedrive', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
      
      const existingEmpty = tbody.querySelector('.empty-page-row');
      if (existingEmpty) existingEmpty.remove();

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

      rows.forEach(row => row.style.display = 'none');

      let visibleCount = 0;
      filteredRows.forEach((row, index) => {
        const pageOfRow = Math.floor(index / window.pageSize) + 1;
        if (pageOfRow === window.currentPage) {
          row.style.display = '';
          visibleCount++;
        }
      });

      if (visibleCount === 0 && window.currentPage > 1) {
        const lastValidPage = Math.max(1, Math.ceil(filteredRows.length / window.pageSize));
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-page-row';
        emptyRow.innerHTML = `
          <td colspan="7" class="text-center py-5">
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

    // 🚀 FIXED: Re-added missing pagination UI renderer
    function updatePaginationUI(totalEntries) {
      const start = (window.currentPage - 1) * window.pageSize + 1;
      const end = Math.min(window.currentPage * window.pageSize, totalEntries);
      
      const pagText = document.getElementById('paginationText');
      if (pagText) {
        if (totalEntries === 0) {
          pagText.textContent = 'Showing 0 entries';
        } else if (start > totalEntries) {
          pagText.textContent = `Showing 0 entries of ${totalEntries} total`;
        } else {
          pagText.textContent = `Showing ${start} to ${end} of ${totalEntries} entries`;
        }
      }

      for (let i = 1; i <= 3; i++) {
        const item = document.getElementById(`page${i}Item`);
        if (item) {
          if (i === window.currentPage) item.classList.add('active');
          else item.classList.remove('active');
        }
      }

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

    // 🚀 FIXED: Added robust link handler to securely query tileset information
    function launchInteractivePreview(projectIdString) {
      const modalEl = document.getElementById('mapPreviewModal'); // Mapped to match your structural layout
      const frameEl = document.getElementById('interactivePreviewFrame');
      const titleEl = document.getElementById('mapPreviewModalTitle');
      const spinnerEl = document.getElementById('viewerLoadingSpinner'); // Grab the spinner element
      
      if (!modalEl || !frameEl) return;

      let projectTitle = 'Project 3D Model';
      if (window.myUploadsData) {
        const assetMeta = window.myUploadsData.find(p => String(p.project_id) === String(projectIdString) || String(p.id) === String(projectIdString));
        if (assetMeta) {
          projectTitle = assetMeta.project_title || assetMeta.project_id;
          titleEl.innerHTML = `<i class="bx bx-layer me-2 text-primary"></i> 3D Model Map Viewer: ${projectTitle}`;
        }
      }

      fetch(`/api/user/my-uploads/${encodeURIComponent(projectIdString)}/preview-tileset`)
        .then(res => res.json())
        .then(data => {
          if (data.success && data.tileset_url) {
            // 1. Show the spinner right before updating the src attribute
            if (spinnerEl) spinnerEl.classList.remove('d-none');
            
            // 2. Set the frame source context
            frameEl.src = `/viewer/${encodeURIComponent(projectIdString)}?model=${encodeURIComponent(projectIdString)}&tileset_url=${encodeURIComponent(data.tileset_url)}&title=${encodeURIComponent(projectTitle)}`;
            
            // 3. Attach the onload handler to clear the spinner once the pipeline completes loading
            frameEl.onload = function() {
              if (spinnerEl) spinnerEl.classList.add('d-none');
            };
          } else {
            frameEl.src = '';
            if (spinnerEl) spinnerEl.classList.add('d-none'); // Hide if it fails
            alert("The preview fileset index could not be resolved on the server.");
          }
        })
        .catch(() => {
          if (spinnerEl) spinnerEl.classList.add('d-none'); // Hide if it errors
          alert("Failed to connect to the 3D secure rendering pipeline endpoint.");
        });

      const bModal = new bootstrap.Modal(modalEl);
      bModal.show();

      // Push a history state so the browser back button triggers popstate instead of navigating away
      history.pushState({ previewModalOpen: true }, '');

      // Clean up: clear iframe when modal is dismissed normally (X button)
      let closedByPopstate = false;
      modalEl.addEventListener('hidden.bs.modal', function disposeFrame() {
        frameEl.src = '';
        titleEl.innerHTML = `<i class="bx bx-layer me-2 text-primary"></i> 3D Model Map Viewer`;
        // If closed via X button (not back button), pop the history state we pushed
        if (!closedByPopstate && history.state && history.state.previewModalOpen) {
          history.back();
        }
        closedByPopstate = false;
        modalEl.removeEventListener('hidden.bs.modal', disposeFrame);
      });

      // Store flag reference on element for popstate handler to access
      modalEl._closedByPopstate = () => { closedByPopstate = true; };
    }

    // Listen for browser back button — close the modal cleanly without navigating away
    window.addEventListener('popstate', function(e) {
      const modalEl = document.getElementById('mapPreviewModal');
      const frameEl = document.getElementById('interactivePreviewFrame');
      const titleEl = document.getElementById('mapPreviewModalTitle');
      if (!modalEl) return;

      const isVisible = modalEl.classList.contains('show');
      if (isVisible) {
        // Programmatically hide the Bootstrap modal cleanly
        const bModal = bootstrap.Modal.getInstance(modalEl);
        if (bModal) {
          // Signal that we are closing via back button so hidden.bs.modal doesn't double-back
          if (typeof modalEl._closedByPopstate === 'function') modalEl._closedByPopstate();
          frameEl.src = '';
          if (titleEl) titleEl.innerHTML = `<i class="bx bx-layer me-2 text-primary"></i> 3D Model Map Viewer`;
          bModal.hide();
        }
      }
    });

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
      
      const isMulti = (project.upload_type && project.upload_type.includes('multiple')) || 
                    (project.camera_models && (project.camera_models.toLowerCase().includes('multi-lens') || project.camera_models.toLowerCase().includes('multiple')));
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
              const rootPrefix = (window.remoteBasePath || '/srv/sftpgo/data/uploads').replace(/\/+$/, '');
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

          // 🚀 SFTP PATH PROTECTION (v176): Hide path behind disclaimer
          const detailPathEl = document.getElementById('detailDeliveryPath');
          const statusVal = (project.request_status || 'pending').toLowerCase();
          
          if (statusVal === 'completed' || statusVal === 'sent') {
              // Create an "Unlock" button if they haven't seen it yet
              detailPathEl.innerHTML = `
                  <div class="d-flex align-items-center justify-content-between w-100" id="sftpUnlockContainer">
                      <span class="text-muted italic">Path is locked for your protection</span>
                      <button type="button" class="btn btn-xs btn-primary fw-bold" onclick="initiateDownloadWithDisclaimer(${project.id}, 'REVEAL_SFTP', false)">
                          <i class="bx bx-lock-open-alt me-1"></i> Unlock Path
                      </button>
                  </div>
                  <div id="sftpActualPath" class="d-none flex-grow-1">
                      <code class="text-dark" id="sftpPathText">${displayPath}</code>
                      <button type="button" class="btn btn-sm btn-link p-0 ms-2 text-success" onclick="copyTextFromElement('sftpPathText', this)"><i class="bx bx-copy"></i></button>
                  </div>
              `;
          } else {
              detailPathEl.textContent = "Processing not complete";
          }
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
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
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

    async function updateStorageUI(totalBytes) {
      try {
        const response = await fetch('/api/user/storage-quota');
        const data = await response.json();
        
        if (data.success) {
          const usedGb = (data.used_bytes / (1024 * 1024 * 1024)).toFixed(1);
          const limitGb = (data.limit_bytes / (1024 * 1024 * 1024)).toFixed(0);
          const percent = data.percent_used.toFixed(1);
          
          const usedText = document.getElementById('storageUsedText');
          const totalText = document.getElementById('storageTotalText');
          const progressBar = document.getElementById('storageProgressBar');
          const statusText = document.getElementById('storageStatusText');
          
          if (usedText) usedText.textContent = usedGb + ' GB Used';
          if (totalText) totalText.textContent = limitGb + ' GB Total';
          if (progressBar) {
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            
            if (data.has_exceeded) {
              progressBar.className = 'progress-bar bg-danger';
            } else if (parseFloat(percent) > 85) {
              progressBar.className = 'progress-bar bg-warning';
            } else {
              progressBar.className = 'progress-bar bg-primary';
            }
          }
          if (statusText) {
            statusText.innerHTML = `You have used ${percent}% of your available storage.`;
          }
          
          const bannerContainer = document.getElementById('quotaWarningBanner');
          if (bannerContainer) {
            if (data.has_exceeded) {
              bannerContainer.classList.remove('d-none');
            } else {
              bannerContainer.classList.add('d-none');
            }
          }
        }
      } catch (err) {
        console.error("Failed to load dynamic storage quota:", err);
      }
    }

    // 🚀 BRIDGE FIX: Direct the HTML click events to the actual interactive rendering pipeline handler
    function open3DPreviewModal(projectId) {
        if (typeof launchInteractivePreview === 'function') {
            launchInteractivePreview(projectId);
        } else {
            console.error("Critical: launchInteractivePreview pipeline handler is missing.");
        }
    }
  </script>

        <div class="modal fade" id="mapPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen"> <div class="modal-content">
            <div class="modal-header py-3 bg-dark text-white">
              <h5 class="modal-title fw-bold text-white" id="mapPreviewModalTitle"><i class="bx bx-layer me-2 text-primary"></i> 3D Model Map Viewer</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative bg-light">
              <div id="viewerLoadingSpinner" class="position-absolute top-50 start-50 translate-middle text-center" style="z-index: 10;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <div class="mt-2 fw-bold text-dark">Initializing 3D Pipeline Render Engine...</div>
              </div>
              
              <div id="spatialViewerContainer" style="width: 100%; height: 100%;">
                <iframe id="interactivePreviewFrame" src="" style="width: 100%; height: 100%; border: none; background: #000;" allow="fullscreen; xr-spatial-tracking"></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
</body>
</html>
