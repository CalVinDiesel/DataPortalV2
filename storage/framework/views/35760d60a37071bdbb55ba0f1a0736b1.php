<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/" data-template="admin-data-portal" data-bs-theme="light">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Client Uploads - Admin | 3DHub</title>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/core.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/demo.css" />
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/admin-responsive.css" />
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/helpers.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/vendor/js/bootstrap.js"></script>
  <script>
    (function() {
      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      fetch(AUTH_API + '/api/auth/me', { credentials: 'include' }).then(function(r) { return r.json(); }).then(function(d) {
        if (!d.loggedIn || (d.role !== 'admin' && d.role !== 'superadmin')) window.location.href = '<?php echo e(route('landing')); ?>?error=admin_only';
      }).catch(function() { window.location.href = '<?php echo e(route('landing')); ?>'; });
    })();
  </script>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo py-4">
          <a href="<?php echo e(route('admin_dashboard')); ?>" class="app-brand-link d-flex align-items-center">
            <span class="app-brand-logo demo me-2"><img src="<?php echo e(asset('assets')); ?>/img/front-pages/landing-page/3DHub logo1.png"
                alt="3DHub"
                style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
            <span class="app-brand-text demo menu-text fw-bold" style="font-size: 1.4em;">3DHub Admin</span>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item">
            <a href="<?php echo e(route('admin_dashboard')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div>Dashboard</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="<?php echo e(route('admin.add_3d_model')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-cube"></i>
              <div>Add 3D Model</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="<?php echo e(route('admin.manage_map_pins')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-map-pin"></i>
              <div>Manage Map Pins</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="<?php echo e(route('admin.manage_showcase')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-grid-alt"></i>
              <div>Manage Showcase</div>
            </a>
          </li>
          <li class="menu-item active">
            <a href="<?php echo e(route('admin.client_uploads')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-cloud-upload"></i>
              <div>Client Uploads</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="<?php echo e(route('admin.manage_users')); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div>Manage Users</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="<?php echo e(route('landing')); ?>" class="menu-link" target="_blank">
              <i class="menu-icon tf-icons bx bx-map"></i>
              <div>View Portal</div>
            </a>
          </li>
        </ul>
      </aside>

      <div class="layout-page">
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
          <div class="navbar-brand demo d-flex align-items-center py-0 me-3">
            <button class="admin-menu-toggle btn btn-icon d-xl-none me-2 border-0 bg-transparent p-0" type="button"
              aria-label="Toggle menu"><i class="bx bx-menu icon-lg"></i></button>
          </div>
          <div class="navbar-nav-right d-flex align-items-center ms-auto">
            <a href="<?php echo e(route('admin_dashboard')); ?>" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
          </div>
        </nav>

        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold mb-2">Client Uploads</h4>
            <p class="text-muted mb-3">Clients submit drone-captured images via SFTP or the Data Portal for
              <strong>custom image-to-3D processing</strong>. Manage request status here. All file transfers happen
              through WinSCP / SFTPGo — use this page only to update status at each step.</p>

            <!-- Status Flow Indicator -->
            <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
              <span class="badge bg-label-warning px-3 py-2"><i class="bx bx-time-five me-1"></i> Pending</span>
              <i class="bx bx-chevron-right text-muted"></i>
              <span class="badge bg-label-secondary px-3 py-2"><i class="bx bx-search-alt me-1"></i> Review</span>
              <i class="bx bx-chevron-right text-muted"></i>
              <span class="badge bg-label-primary px-3 py-2"><i class="bx bx-loader-alt me-1"></i> Processing</span>
              <i class="bx bx-chevron-right text-muted"></i>
              <span class="badge bg-label-success px-3 py-2"><i class="bx bx-check-circle me-1"></i> Completed</span>
            </div>

            <div id="uploadsAlert" class="alert d-none"></div>

            <!-- Reject reason modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel"
              aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p class="text-muted small">Give a reason for the client (required). They will see this message.</p>
                    <textarea id="rejectReasonInput" class="form-control" rows="3"
                      placeholder="e.g. Image quality insufficient for processing"></textarea>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="rejectConfirmBtn">Reject request</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete upload modal -->
            <div class="modal fade" id="deleteUploadModal" tabindex="-1" aria-labelledby="deleteUploadModalLabel"
              aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="deleteUploadModalLabel">Delete upload request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p class="mb-2">Delete this upload request?</p>
                    <p class="small text-muted mb-0" id="deleteUploadModalHint">This action cannot be undone.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="deleteUploadConfirmBtn">
                      <i class="bx bx-trash me-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Upload details modal -->
            <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel"
              aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Upload details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <!-- SFTP Location Box (Conditional) -->
                    <div class="alert alert-secondary mb-3 d-none" id="detailSftpBox">
                      <div class="fw-bold mb-1"><i class="bx bx-server me-1"></i> SFTP File Location</div>
                      <div class="small text-muted mb-2">
                        <i class="bx bx-info-circle me-1"></i> Connect via WinSCP: 
                        <strong><span id="detailSftpHostDisplay"><?php echo e(config('filesystems.disks.sftp_delivery.host', '172.21.107.151')); ?></span></strong> | Port: <strong><span id="detailSftpPortDisplay"><?php echo e(env('SFTP_USER_PORT', 2223)); ?></span></strong> | User: <strong><span id="detailSftpUserDisplay"><?php echo e(config('filesystems.disks.sftp_delivery.username', 'tiquan')); ?></span></strong>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <code id="detailSftpPath" class="d-block p-2 bg-white border rounded flex-grow-1"
                          style="font-size:0.85rem; word-break:break-all;">–</code>
                        <button type="button" id="copySftpPathBtn"
                          class="btn btn-sm btn-outline-secondary flex-shrink-0" onclick="
                          var path = document.getElementById('detailSftpPath').textContent;
                          navigator.clipboard.writeText(path).then(function() {
                            var btn = document.getElementById('copySftpPathBtn');
                            btn.innerHTML = '<i class=\'bx bx-check\'></i> Copied';
                            btn.classList.remove('btn-outline-secondary');
                            btn.classList.add('btn-success');
                            setTimeout(function() {
                              btn.innerHTML = '<i class=\'bx bx-copy\'></i> Copy';
                              btn.classList.remove('btn-success');
                              btn.classList.add('btn-outline-secondary');
                            }, 2000);
                          });
                        " title="Copy path"><i class="bx bx-copy"></i> Copy</button>
                      </div>
                      <div class="mt-2 d-flex align-items-center justify-content-between">
                        <div id="detailSftpStatus" class="small fw-bold"></div>
                        <button type="button" id="syncSftpBtn" class="btn btn-xs btn-primary d-none" onclick="syncProjectToSftp()">
                          <i class="bx bx-sync me-1"></i> Sync to SFTP
                        </button>
                      </div>
                    </div>
                    <!-- Google Drive Link Box (Conditional) -->
                    <div class="alert alert-primary mb-3 d-none" id="detailGDriveBox">
                      <div class="fw-bold mb-1"><i class="bx bxl-google-cloud me-1"></i> Google Drive Link</div>
                      <div class="small text-muted mb-1">Client shared files via this link:</div>
                      <a href="#" id="detailGDriveLink" target="_blank" class="text-break fw-bold text-primary">Browse files on Drive <i class="bx bx-link-external small mt-n1"></i></a>
                    </div>
                    <div class="row small">
                      <div class="col-12 mb-2"><strong>Project</strong></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">ID:</span> <span id="detailProjectId">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Title:</span> <span id="detailProjectTitle">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Description:</span> <span id="detailProjectDescription">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Category:</span> <span id="detailCategory">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Latitude:</span> <span id="detailLatitude">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Longitude:</span> <span id="detailLongitude">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Sensor / Lens:</span> <span id="detailCameraModels" class="fw-bold text-dark">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Geotagging:</span> <span id="detailImageMetadata">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Capture date:</span> <span id="detailCaptureDate">–</span></div>
                      <div class="col-md-6 mb-2"><span class="text-muted">Outputs:</span> <span id="detailOutputCategories">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Upload type:</span> <span id="detailUploadType">–</span></div>
                      <div class="col-md-12 mb-2" id="detailFileCountRow"><span class="text-muted">File count:</span> <span id="detailFileCount">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Submitted:</span> <span id="detailCreatedAt">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Submitted by:</span> <span id="detailCreatedBy">–</span></div>
                      <div class="col-12 mb-2"><span class="text-muted">Status:</span> <span id="detailStatus">–</span></div> 
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mark as Delivered modal -->
            <div class="modal fade" id="deliverModal" tabindex="-1" aria-labelledby="deliverModalLabel"
              aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form id="deliverForm">
                    <div class="modal-header">
                      <h5 class="modal-title" id="deliverModalLabel">Deliver Processed Results</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- SFTP Path Hint (For Manual Delivery) -->
                      <div class="alert alert-info mb-3" id="deliverPathHintBox">
                        <div class="fw-bold mb-1"><i class="bx bx-info-circle me-1"></i> Manual Delivery Path</div>
                        <div class="small mb-2">
                          Connect to: <strong><span id="deliverHostDisplay"><?php echo e(config('filesystems.disks.sftp_delivery.host', '172.21.107.151')); ?></span></strong> | Port: <strong><span id="deliverPortDisplay"><?php echo e(env('SFTP_DELIVERY_PORT', 2222)); ?></span></strong> | User: <strong><span id="deliverUserDisplay"><?php echo e(config('filesystems.disks.sftp_delivery.username', 'tiquan')); ?></span></strong>
                        </div>
                        <div class="small d-flex align-items-center justify-content-between">
                          <span>Place your file in:</span>
                          <div id="deliverPathStatus"></div>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                          <code id="deliverPathHint" class="flex-grow-1 p-2 bg-white border rounded" style="font-size:0.8rem; word-break:break-all;">–</code>
                          <button type="button" class="btn btn-sm btn-outline-info ms-2" onclick="copyDeliverPath(event)" title="Copy Path">
                            <i class="bx bx-copy"></i>
                          </button>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Delivery Method</label>
                        <select id="deliverMethodSelect" name="delivery_method" class="form-select">
                          <option value="portal" id="optPortal">Web Portal (SFTP Streamed)</option>
                          <option value="sftp" id="optSftp">Direct SFTP</option>
                          <option value="google_drive" id="optGDrive">Google Drive</option>
                        </select>
                        <div class="form-text" id="deliverMethodHint">Choose how the client receives their processed 3D model.</div>
                      </div>

                      <div class="row">
                        <div class="col-12 mb-3">
                          <label class="form-label">Upload Processed File (.zip)</label>
                          <input type="file" id="deliverFileInput" name="delivered_file" class="form-control" accept=".zip,.rar,.7z">
                          <div class="form-text">System will upload this to the SFTP deliveries folder.</div>
                        </div>
                        <div class="col-12 mb-3" id="sectionOR1">
                          <div class="text-center text-muted my-2">-- OR --</div>
                        </div>
                        <div class="col-12 mb-3" id="sectionOptionB">
                          <label class="form-label">Existing SFTP Filename</label>
                          <input type="text" id="deliverManualPathInput" name="manual_file_name" class="form-control" placeholder="e.g. results_final.zip">
                          <div class="form-text">Use this if you already moved the file to the <strong>Manual Delivery Path</strong> via WinSCP.</div>
                        </div>
                        <div class="col-12 mb-3" id="sectionOR2">
                          <div class="text-center text-muted my-2">-- OR --</div>
                        </div>
                        <div class="col-12 mb-3" id="sectionOptionC">
                          <label class="form-label text-primary fw-bold">Google Drive Share Link <span class="text-danger">* (Required)</span></label>
                          <input type="url" id="deliverGDriveLinkInput" name="google_drive_link" class="form-control" placeholder="https://drive.google.com/file/d/..." required>
                          <div class="form-text text-primary">Paste the 'Anyone with the link can view' share link here. This link is required to complete the delivery.</div>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Delivery Notes <span class="text-danger">*</span></label>
                        <textarea id="deliverNotesInput" name="delivery_notes" class="form-control" rows="3"
                          placeholder="e.g. Processed result ready for download. Please confirm receipt." required></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary" id="deliverCancelBtn">Cancel</button>
                      <button type="submit" class="btn btn-success" id="deliverConfirmBtn"><i
                          class="bx bx-check me-1"></i> Confirm Delivered</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Client Uploads Table -->
            <div class="card">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="mb-0 fw-bold">Client Upload Requests</h5>
                  <p class="text-muted small mb-0">All file transfers are managed through WinSCP / SFTPGo. Use the
                    buttons below only to update the status.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="uploadsSearch" class="form-control" placeholder="Search projects...">
                  </div>
                  <button class="btn btn-sm btn-outline-secondary" onclick="loadUploads()"><i
                      class="bx bx-refresh me-1"></i> Refresh</button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Files</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="uploadsTableBody">
                      <tr>
                        <td colspan="8" class="text-center text-muted">Loading…</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div id="uploadsPagination" class="d-flex justify-content-center mt-3"></div>
              </div>
            </div>

            <!-- Processing Requests Table -->
            <div class="card mt-4">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="mb-0 fw-bold">Processing Requests</h5>
                  <p class="text-muted small mb-0">After processing is done, place the result in the client's SFTP
                    folder via WinSCP, then click <strong>Mark as Delivered</strong>.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="requestsSearch" class="form-control" placeholder="Search projects...">
                  </div>
                  <button class="btn btn-sm btn-outline-secondary" onclick="loadRequests()"><i
                      class="bx bx-refresh me-1"></i> Refresh</button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Upload ID</th>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Delivered</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                      <tr>
                        <td colspan="8" class="text-center text-muted">Loading…</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div id="requestsPagination" class="d-flex justify-content-center mt-3"></div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
  #map {
      height: 250px;
      width: 100%;
      border-radius: 12px;
      border: 1px solid #d9dee3;
      margin-top: 0.5rem;
      z-index: 1 !important;
    }
    
    /* 🚀 PERFORMANCE OPTIMIZATION (v133): Fix CLS by reserving space */
    .modal-content { min-height: 400px; }
    .table-responsive { min-height: 300px; }
    .card { contain: content; }
  </style>

  <script>
    function copyDeliverPath(event) {
        const path = document.getElementById('deliverPathHint').textContent;
        const btn = event.currentTarget;
        const icon = btn.querySelector('i');
        
        navigator.clipboard.writeText(path).then(() => {
            const oldIcon = icon.className;
            const oldBtnClass = btn.className;
            
            icon.className = 'bx bx-check';
            btn.className = 'btn btn-sm btn-success ms-2';
            
            setTimeout(() => {
                icon.className = oldIcon;
                btn.className = oldBtnClass;
            }, 2000);
        });
    }

    (function () {
      var API_BASE = window.location.origin;

      var rejectBSModal = null;
      var deliverBSModal = null;
      var deleteUploadBSModal = null;

      var pendingRejectId = null;
      var pendingDeliverId = null;
      var pendingDeleteUploadId = null;
      var uploadRootAbsolute = '';
      var remoteBasePath = '';
      var sftpUsername = '';
      var sftpPort = '';
      var sftpHost = '';
      
      // 🚀 PAGINATION & SEARCH STATE (v238)
      var allUploads = [];
      var allRequests = [];
      var uploadsPage = 1;
      var requestsPage = 1;
      var itemsPerPage = 10;
      var uploadsSearch = '';
      var requestsSearch = '';

      // Cache upload rows for detail modal
      var uploadsRowsById = {};
      // Cache upload rows for processing table enrichment
      var uploadMetaById = {};
      var activeMeta = null; // 🚀 META TRACKER (v173)
      
      // 🚀 UPLOAD CONTROL (v169): Track active streams for cancellation
      var activeNitroXHRs = [];
      var nitroAborted = false;
      
      // 🚀 UPLOAD CONTROL (v169): Track active streams for cancellation
      var activeUploadController = null;

      function escapeHtml(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
      }

      function statusBadge(status) {
        var s = (status || 'pending').toLowerCase();
        var map = {
          'completed': { color: 'success', label: 'Completed', icon: 'bx-check-circle' },
          'sent': { color: 'info', label: 'Sent', icon: 'bx-package' },
          'processing': { color: 'primary', label: 'Processing', icon: 'bx-loader-alt' },
          'review': { color: 'secondary', label: 'Review', icon: 'bx-search-alt' },
          'accepted': { color: 'info', label: 'Accepted', icon: 'bx-check' },
          'rejected': { color: 'danger', label: 'Rejected', icon: 'bx-x-circle' },
          'pending': { color: 'warning', label: 'Pending', icon: 'bx-time-five' },
        };
        var e = map[s] || { color: 'warning', label: s.charAt(0).toUpperCase() + s.slice(1), icon: 'bx-circle' };
        return '<span class="badge bg-label-' + e.color + '"><i class="bx ' + e.icon + ' me-1"></i>' + escapeHtml(e.label) + '</span>';
      }

      function normalizePathForDisplay(p) {
        if (!p) return '';
        return String(p).replace(/\\/g, '/');
      }

      function joinDisplayPath(base, sub) {
        var b = normalizePathForDisplay(base || '').replace(/\/+$/, '');
        var s = normalizePathForDisplay(sub || '').replace(/^\/+/, '');
        if (!b) return s;
        if (!s) return b;
        return b + '/' + s;
      }

      function formatSftpPath(path) {
          if (!path) return '–';
          // 🚀 FULL PATH DISPLAY (v168): Reverted to show absolute system paths
          var p = normalizePathForDisplay(path);
          return p.replace(/\/+/g, '/');
      }

      function buildSftpPath(row) {
          if (!row || !row.file_paths) return '–';
          let path = '';
          try {
            const paths = Array.isArray(row.file_paths) ? row.file_paths : JSON.parse(row.file_paths);
            if (!paths || paths.length === 0) return '–';
            path = paths[0];
          } catch (e) {
            path = String(row.file_paths);
          }

          // 🚀 SMART-PATH CLEANUP (v168): Reverted to plural 'uploads'
          const base = (uploadRootAbsolute || '/home/tiquan/uploads/').replace(/\/+$/, '');
          
          let displayPath = path;
          // Ensure it has a base if it's just a partial path
          if (!displayPath.startsWith('/') && !displayPath.startsWith('C:')) {
             displayPath = base + '/' + displayPath.replace(/^\//, '');
          }
          
          return formatSftpPath(displayPath);
      }



      function actionCells(r) {
        var status = (r.request_status || 'pending').toLowerCase();
        var wrapStart = '<div class="d-flex flex-wrap align-items-center gap-2">';
        var wrapEnd = '</div>';
        var detailsBtn = '<button type="button" class="btn btn-sm btn-outline-secondary details-btn" data-upload-id="' + r.id + '"><i class="bx bx-info-circle me-1"></i>Details</button>';
        var rejectBtn = '<button type="button" class="btn btn-sm btn-outline-danger reject-btn" data-upload-id="' + r.id + '">Reject</button>';
        var deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger delete-upload-btn" data-upload-id="' + r.id + '"><i class="bx bx-trash me-1"></i>Delete</button>';

        if (status === 'pending') {
          return wrapStart +
            detailsBtn +
            '<button type="button" class="btn btn-sm btn-success accept-btn" data-upload-id="' + r.id + '"><i class="bx bx-check me-1"></i>Accept</button>' +
            rejectBtn +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'review') {
          return wrapStart +
            detailsBtn +
            '<button type="button" class="btn btn-sm btn-primary start-processing-btn" data-upload-id="' + r.id + '"><i class="bx bx-cog me-1"></i>Start Processing</button>' +
            rejectBtn +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'accepted') {
          // Legacy fallback
          return wrapStart +
            detailsBtn +
            '<button type="button" class="btn btn-sm btn-primary start-processing-btn" data-upload-id="' + r.id + '"><i class="bx bx-cog me-1"></i>Start Processing</button>' +
            rejectBtn +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'processing') {
          return wrapStart +
            detailsBtn +
            '<span class="text-muted small"><i class="bx bx-info-circle me-1"></i>See Processing Requests below</span>' +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'rejected') {
          var reason = r.rejected_reason ? '<small class="text-muted">' + escapeHtml(r.rejected_reason) + '</small>' : '';
          return wrapStart +
            detailsBtn +
            '<span class="badge bg-label-danger">Rejected</span>' +
            reason +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'sent') {
          return wrapStart +
            detailsBtn +
            '<span class="badge bg-label-info"><i class="bx bx-package me-1"></i>Sent to client</span>' +
            deleteBtn +
            wrapEnd;
        }
        if (status === 'completed') {
          return wrapStart +
            detailsBtn +
            '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Completed</span>' +
            deleteBtn +
            wrapEnd;
        }
        return wrapStart + detailsBtn + deleteBtn + wrapEnd;
      }

      function showDetailsModal(row) {
        const text = (v) => (v != null && v !== '') ? escapeHtml(String(v)) : '–';
        const num = (v) => (v != null && v !== '' && !isNaN(Number(v))) ? String(v) : '–';

        const type = row.upload_type || 'browser';
        
        // Toggle Method-Specific UI
        const sftpBox = document.getElementById('detailSftpBox');
        const gdriveBox = document.getElementById('detailGDriveBox');
        const fileCountRow = document.getElementById('detailFileCountRow');

        sftpBox.classList.add('d-none');
        gdriveBox.classList.add('d-none');
        fileCountRow.classList.add('d-none');
        fileCountRow.classList.add('d-none');

        if (type === 'sftp' || type === 'sftp_single' || type === 'sftp_multiple' || type === 'browser' || type === 'multiple') {
          sftpBox.classList.remove('d-none');
          document.getElementById('detailSftpPath').textContent = buildSftpPath(row);
          
          // 🚀 SYNC CONNECTION DETAILS (v188)
          if (window.remoteBasePath) {
              document.getElementById('detailSftpHostDisplay').innerText = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') ? '172.21.107.151' : window.location.hostname;
              document.getElementById('detailSftpPortDisplay').innerText = window.sftpPort || '2222';
              document.getElementById('detailSftpUserDisplay').innerText = window.sftpUsername || 'tiquan';
          }
        } else if (type === 'google_drive') {
          gdriveBox.classList.remove('d-none');
          const link = document.getElementById('detailGDriveLink');
          link.href = row.google_drive_link || '#';
          link.textContent = row.google_drive_link || 'Link missing';
        } else {
          // Browser upload
          fileCountRow.classList.remove('d-none');
          document.getElementById('detailFileCount').textContent = text(row.file_count);
          document.getElementById('detailFileCount').textContent = text(row.file_count);
        }

        // Shared Details
        document.getElementById('detailProjectId').textContent = text(row.project_id);
        document.getElementById('detailProjectTitle').textContent = text(row.project_title);
        document.getElementById('detailProjectDescription').textContent = text(row.project_description);
        document.getElementById('detailCategory').textContent = text(row.category);
        document.getElementById('detailLatitude').textContent = num(row.latitude);
        document.getElementById('detailLongitude').textContent = num(row.longitude);
        
        // 📅 CLEAN DATE (v129): Strip ISO time/milliseconds (e.g. 2026-04-13T00:00:00.000000Z -> 2026-04-13)
        let rawDate = row.capture_date || '–';
        let cleanDate = (rawDate.includes('T')) ? rawDate.split('T')[0] : rawDate;
        document.getElementById('detailCaptureDate').textContent = cleanDate;
        
        // Output Categories Display
        const outputs = row.output_categories;
        const outputsArr = Array.isArray(outputs) ? outputs : (typeof outputs === 'string' ? [outputs] : []);
        document.getElementById('detailOutputCategories').innerHTML = outputsArr.length > 0 
          ? outputsArr.map(o => '<span class="badge bg-label-info me-1">' + escapeHtml(o) + '</span>').join('')
          : '–';
        
        // Metadata & Sensor Display
        var metaEl = document.getElementById('detailImageMetadata');
        var camEl  = document.getElementById('detailCameraModels');
        
        camEl.textContent = text(row.camera_models) || 'Standard Sensor';

        if (type === 'sftp' || type === 'sftp_single' || type === 'sftp_multiple') {
          metaEl.innerHTML = '<i class="bx bx-info-circle me-1"></i> Manual SFTP Upload';
        } else {
          try {
            var nitro = typeof row.image_metadata === 'string' ? JSON.parse(row.image_metadata) : row.image_metadata;
            if (nitro && typeof nitro.count !== 'undefined') {
               metaEl.innerHTML = '<span class="badge bg-label-success">' + nitro.count + ' GPS Points Detected</span>';
            } else {
               metaEl.textContent = text(row.image_metadata) || 'No metadata';
            }
          } catch(e) {
            metaEl.textContent = text(row.image_metadata) || 'No metadata';
          }
        }

        var typeDisplay = type;
        if (type === 'sftp_single') typeDisplay = 'SFTP (Single-Lens)';
        else if (type === 'sftp_multiple') typeDisplay = 'SFTP (Multi-Lens)';
        else if (type === 'sftp') typeDisplay = 'SFTP (Provisioned)';
        else if (type === 'google_drive') typeDisplay = 'Google Drive Link';
        else if (type === 'browser') typeDisplay = 'Nitro Browser Upload';
        document.getElementById('detailUploadType').textContent = typeDisplay;

        document.getElementById('detailCreatedAt').textContent = row.created_at ? new Date(row.created_at).toLocaleString() : '–';
        document.getElementById('detailCreatedBy').textContent = text(row.created_by_email);
        document.getElementById('detailStatus').innerHTML = statusBadge(row.request_status);

        var modalEl = document.getElementById('detailsModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
          checkProjectSftpStatus(row.project_id);
        }
      }

      window.checkProjectSftpStatus = function(projectId) {
          const statusDiv = document.getElementById('detailSftpStatus');
          const syncBtn = document.getElementById('syncSftpBtn');
          
          statusDiv.innerHTML = '<span class="text-muted"><i class="bx bx-loader-alt bx-spin me-1"></i> Checking SFTP storage...</span>';
          syncBtn.classList.add('d-none');

          fetch(API_BASE + '/admin/client-uploads/check-sftp-status', {
              method: 'POST',
              headers: { 
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
              },
              body: JSON.stringify({ projectID: projectId })
          })
          .then(r => r.json())
          .then(data => {
              if (data.success) {
                  if (data.exists) {
                      statusDiv.innerHTML = '<span class="text-success"><i class="bx bx-check-circle me-1"></i> Synced to SFTP</span>';
                      syncBtn.classList.add('d-none');
                  } else {
                      statusDiv.innerHTML = '<span class="text-warning"><i class="bx bx-error me-1"></i> Not on SFTP (Local Storage)</span>';
                      syncBtn.classList.remove('d-none');
                  }
              } else {
                  statusDiv.innerHTML = '<span class="text-danger"><i class="bx bx-x-circle me-1"></i> Storage check failed</span>';
              }
          })
          .catch(() => {
              statusDiv.innerHTML = '<span class="text-danger"><i class="bx bx-x-circle me-1"></i> Connection Error</span>';
          });
      };

      window.syncProjectToSftp = function() {
          const projectId = document.getElementById('detailProjectId').textContent;
          const statusDiv = document.getElementById('detailSftpStatus');
          const syncBtn = document.getElementById('syncSftpBtn');

          syncBtn.disabled = true;
          syncBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Syncing...';
          statusDiv.innerHTML = '<span class="text-primary"><i class="bx bx-cloud-upload bx-pulse me-1"></i> Moving files to SFTP server...</span>';

          fetch(API_BASE + '/admin/client-uploads/retry-sftp', {
              method: 'POST',
              headers: { 
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
              },
              body: JSON.stringify({ projectID: projectId })
          })
          .then(r => r.json())
          .then(data => {
              if (data.success) {
                  statusDiv.innerHTML = '<span class="text-success"><i class="bx bx-check-double me-1"></i> Handover Complete!</span>';
                  syncBtn.classList.add('d-none');
                  // Update path display if it changed
                  if (data.path) document.getElementById('detailSftpPath').textContent = data.path;
              } else {
                  statusDiv.innerHTML = '<span class="text-danger"><i class="bx bx-error-circle me-1"></i> Handover Failed: ' + data.message + '</span>';
                  syncBtn.disabled = false;
                  syncBtn.innerHTML = '<i class="bx bx-sync me-1"></i> Retry Sync';
              }
          })
          .catch(() => {
              statusDiv.innerHTML = '<span class="text-danger"><i class="bx bx-wifi-off me-1"></i> Sync Error</span>';
              syncBtn.disabled = false;
              syncBtn.innerHTML = '<i class="bx bx-sync me-1"></i> Retry Sync';
          });
      };

      window.loadUploads = function loadUploads() {
        document.getElementById('uploadsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';
        fetch(API_BASE + '/api/admin/client-uploads')
          .then(function (r) { return r.json(); })
          .then(function (rows) {
            allUploads = rows || [];
            uploadsPage = 1; // Reset to page 1 on fresh load
            renderUploadsTable();
          })
          .catch(function () {
            document.getElementById('uploadsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Failed to load. Ensure the server is running and PostgreSQL is configured.</td></tr>';
          });
      }

      function renderUploadsTable() {
        var tbody = document.getElementById('uploadsTableBody');
        var container = document.getElementById('uploadsPagination');
        
        // 1. Filter
        var filtered = allUploads.filter(function(r) {
          if (!uploadsSearch) return true;
          var term = uploadsSearch.toLowerCase();
          return (
            String(r.id).includes(term) ||
            (r.project_title || '').toLowerCase().includes(term) ||
            (r.project_id || '').toLowerCase().includes(term) ||
            (r.created_by_email || '').toLowerCase().includes(term)
          );
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No matching upload requests found.</td></tr>';
          container.innerHTML = '';
          return;
        }

        // 2. Paginate
        var totalPages = Math.ceil(filtered.length / itemsPerPage);
        if (uploadsPage > totalPages) uploadsPage = totalPages || 1;
        var start = (uploadsPage - 1) * itemsPerPage;
        var paginated = filtered.slice(start, start + itemsPerPage);

        // 3. Render
        tbody.innerHTML = paginated.map(function (r) {
          uploadsRowsById[r.id] = r;
          uploadMetaById[r.id] = r;

          var created = r.created_at ? new Date(r.created_at).toLocaleString() : '–';
          var typeDisplay = r.upload_type || 'browser';
          if (typeDisplay === 'google_drive') typeDisplay = '<span class="text-success"><i class="bx bxl-google-cloud me-1"></i>GDrive</span>';
          else if (typeDisplay === 'sftp_multiple') typeDisplay = '<span class="text-info">SFTP (Multi)</span>';
          else if (typeDisplay === 'sftp') typeDisplay = '<span class="text-info">SFTP</span>';
          else if (typeDisplay === 'multiple' || typeDisplay === 'multilens') typeDisplay = 'Web (Multi)';
          else if (typeDisplay === 'single') typeDisplay = 'Web (Single)';

          return '<tr>' +
            '<td>' + r.id + '</td>' +
            '<td><strong>' + escapeHtml(r.project_title || r.project_id || '–') + '</strong></td>' +
            '<td><small class="text-muted">' + escapeHtml(r.created_by_email || '–') + '</small></td>' +
            '<td>' + typeDisplay + '</td>' +
            '<td>' + (r.file_count || 0) + '</td>' +
            '<td><small>' + created + '</small></td>' +
            '<td>' + statusBadge(r.request_status) + '</td>' +
            '<td>' + actionCells(r) + '</td>' +
            '</tr>';
        }).join('');

        // Re-attach event listeners
        attachUploadListeners(tbody);
        
        // Render Pagination UI
        renderPaginationUI(container, uploadsPage, totalPages, function(p) {
          uploadsPage = p;
          renderUploadsTable();
        });
      }

      function attachUploadListeners(tbody) {
        tbody.querySelectorAll('.details-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = parseInt(this.getAttribute('data-upload-id'), 10);
            if (uploadsRowsById[id]) showDetailsModal(uploadsRowsById[id]);
          });
        });
        tbody.querySelectorAll('.accept-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            submitDecision(this.getAttribute('data-upload-id'), 'accept', '');
          });
        });
        tbody.querySelectorAll('.start-processing-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            submitDecision(this.getAttribute('data-upload-id'), 'processing', '');
          });
        });
        tbody.querySelectorAll('.reject-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            pendingRejectId = this.getAttribute('data-upload-id');
            document.getElementById('rejectReasonInput').value = '';
            if (rejectModal) rejectModal.show();
          });
        });
        tbody.querySelectorAll('.delete-upload-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = this.getAttribute('data-upload-id');
            var row = uploadsRowsById[parseInt(id, 10)];
            var label = (row && (row.project_title || row.project_id)) ? (row.project_title || row.project_id) : ('#' + id);
            pendingDeleteUploadId = id;
            var hint = document.getElementById('deleteUploadModalHint');
            if (hint) hint.textContent = 'Delete upload "' + label + '" (ID ' + id + ')? This also removes linked processing request records.';
            if (deleteUploadModal) deleteUploadModal.show();
          });
        });
      }

      function renderPaginationUI(container, current, total, onPage) {
        if (total <= 1) {
          container.innerHTML = '';
          return;
        }

        var html = '<ul class="pagination pagination-sm mb-0">';
        // Prev
        html += '<li class="page-item ' + (current === 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" data-page="' + (current - 1) + '"><i class="bx bx-chevron-left"></i></a></li>';
        
        // Page numbers (simplified version)
        for (var i = 1; i <= total; i++) {
          if (total > 7 && i > 3 && i < total - 2 && Math.abs(i - current) > 1) {
            if (i === 4 || i === total - 3) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            continue;
          }
          html += '<li class="page-item ' + (current === i ? 'active' : '') + '"><a class="page-link" href="javascript:void(0)" data-page="' + i + '">' + i + '</a></li>';
        }

        // Next
        html += '<li class="page-item ' + (current === total ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" data-page="' + (current + 1) + '"><i class="bx bx-chevron-right"></i></a></li>';
        html += '</ul>';

        container.innerHTML = html;
        container.querySelectorAll('.page-link').forEach(function(link) {
          link.addEventListener('click', function() {
            var p = parseInt(this.getAttribute('data-page'));
            if (p && p >= 1 && p <= total && p !== current) onPage(p);
          });
        });
      }

      // Search listeners
      document.getElementById('uploadsSearch').addEventListener('input', function(e) {
        uploadsSearch = e.target.value;
        uploadsPage = 1;
        renderUploadsTable();
      });

      document.getElementById('requestsSearch').addEventListener('input', function(e) {
        requestsSearch = e.target.value;
        requestsPage = 1;
        renderRequestsTable();
      });

      function loadPathConfig() {
        return fetch(API_BASE + '/api/admin/client-uploads/path-config')
          .then(function (r) { return r.json(); })
          .then(function (cfg) {
            if (!cfg || !cfg.success) return;
            uploadRootAbsolute = normalizePathForDisplay(cfg.uploadRootAbsolute || '');
            remoteBasePath = cfg.remoteBasePath;
            sftpUsername = cfg.sftpUsername;
            sftpPort = cfg.sftpPort;
            sftpHost = cfg.sftpHost;
            window.remoteBasePath = remoteBasePath;
            window.sftpUsername = sftpUsername;
            window.sftpPort = sftpPort;
            window.sftpHost = sftpHost;
          })
          .catch(function () { /* keep fallback behavior */ });
      }

      function submitDecision(id, action, reason) {
        fetch(API_BASE + '/api/admin/client-uploads/' + id + '/decision', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: action, reason: reason })
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              if (rejectModal) rejectModal.hide();
              pendingRejectId = null;
              loadUploads();
              loadRequests();
              var al = document.getElementById('uploadsAlert');
              var msg = '';
              if (action === 'accept') msg = 'Request #' + id + ' accepted → status is now Review. Open WinSCP to download the raw files to your processing machine.';
              else if (action === 'processing') msg = 'Request #' + id + ' moved to Processing. A processing request has been created below. Run your 3D processing software, then Mark as Delivered when done.';
              else msg = 'Request #' + id + ' rejected.';
              al.textContent = msg;
              al.className = 'alert ' + (action === 'reject' ? 'alert-warning' : 'alert-success');
            } else {
              alert(data.message || 'Failed.');
            }
          })
          .catch(function () { alert('Request failed.'); });
      }

      // 🚀 MODAL INITIALIZATION (v186)
      var rEl_init = document.getElementById('rejectModal');
      if (rEl_init && typeof bootstrap !== 'undefined') rejectModal = new bootstrap.Modal(rEl_init);
      var rejectConfirmBtn = document.getElementById('rejectConfirmBtn');
      if (rejectConfirmBtn) {
        rejectConfirmBtn.addEventListener('click', function () {
          var reason = (document.getElementById('rejectReasonInput').value || '').trim();
          if (!reason) { alert('Please enter a reason for rejecting this request.'); return; }
          if (pendingRejectId) submitDecision(pendingRejectId, 'reject', reason);
        });
      }


        var duEl_init = document.getElementById('deleteUploadModal');
        if (duEl_init && typeof bootstrap !== 'undefined') deleteUploadModal = new bootstrap.Modal(duEl_init);
        var delConfirmBtn = document.getElementById('deleteUploadConfirmBtn');
        if (delConfirmBtn) {
          delConfirmBtn.addEventListener('click', function () {
            if (!pendingDeleteUploadId) return;
            var id = pendingDeleteUploadId;
            delConfirmBtn.disabled = true;
            delConfirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

            fetch(API_BASE + '/api/admin/client-uploads/' + id, { method: 'DELETE' })
              .then(function (r) { return r.json(); })
              .then(function (data) {
                if (data && data.success) {
                  if (deleteUploadModal) deleteUploadModal.hide();
                  pendingDeleteUploadId = null;
                  loadUploads();
                  loadRequests();
                  var al = document.getElementById('uploadsAlert');
                  al.textContent = 'Deleted upload ID ' + id + '.';
                  al.className = 'alert alert-success';
                } else {
                  alert((data && data.message) || 'Failed to delete.');
                }
              })
              .catch(function () { alert('Delete failed.'); })
              .finally(function () {
                delConfirmBtn.disabled = false;
                delConfirmBtn.innerHTML = '<i class="bx bx-trash me-1"></i> Delete';
              });
          });
        }
        
        // 🚀 BOOTSTRAP MODAL INITIALIZATION (v183)
        var dEl = document.getElementById('deliverModal');
        if (dEl && typeof bootstrap !== 'undefined') deliverBSModal = new bootstrap.Modal(dEl);
        
        // 🛡️ RE-SYNC: Ensure all modals use the same naming pattern (v186)
        if (!rejectModal && rEl_init) rejectModal = new bootstrap.Modal(rEl_init);
        if (!deleteUploadModal && duEl_init) deleteUploadModal = new bootstrap.Modal(duEl_init);


        var form = document.getElementById('deliverForm');
        var confirmBtn = document.getElementById('deliverConfirmBtn');
        
        if (form) {
          form.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!pendingDeliverId) return;

            const fileInput = document.getElementById('deliverFileInput');
            const file = fileInput ? fileInput.files[0] : null;
            
            // 🛡️ Safety check for element IDs (v132)
            const notesEl = document.getElementById('deliverNotesInput');
            const methodEl = document.getElementById('deliverMethodSelect');
            const manualPathEl = document.getElementById('deliverManualPathInput');
            const gdriveLinkEl = document.getElementById('deliverGDriveLinkInput');

            if (!methodEl) {
                console.error("Critical UI element 'deliverMethodSelect' missing.");
                return;
            }

            const notes = notesEl ? notesEl.value : '';
            const method = methodEl.value;
            const manualPath = manualPathEl ? manualPathEl.value : '';
            const gdriveLink = gdriveLinkEl ? gdriveLinkEl.value : '';

            confirmBtn.disabled = true;
            
            // 🚀 HYPER-NITRO ADMIN DELIVERY (v130)
            if (file && method === 'portal') {
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Nitro Stream: <span id="adminNitroPercent">0%</span>';
                
                // 🚀 MODAL LOCK (v178): Prevent accidental closing via backdrop/ESC
                var modalEl = document.getElementById('deliverModal');
                modalEl.setAttribute('data-bs-backdrop', 'static');
                modalEl.setAttribute('data-bs-keyboard', 'false');
                var closeBtn = modalEl.querySelector('.btn-close');
                if (closeBtn) closeBtn.style.display = 'none';
                
                // 🚀 NAV PROTECTION (v178)
                window.onbeforeunload = function() {
                    return "A 3D model delivery is currently in progress. Leaving now will abort the upload. Are you sure?";
                };

                // 🚀 FREEZE FORM (v179): Prevent changes during active upload
                document.getElementById('deliverNotesInput').disabled = true;
                document.getElementById('deliverMethodSelect').disabled = true;
                document.getElementById('deliverFileInput').disabled = true;

                // Reset abort state
                nitroAborted = false;
                if (activeUploadController) activeUploadController.abort();
                activeUploadController = new AbortController();
                const { signal } = activeUploadController;

                try {
                    // 🚀 ADAPTIVE-NITRO (v249): Dynamically calculate shards to ensure 10MB chunks for weak-internet stability
                    const getNitroSpecs = (bytes) => {
                        const targetChunkSize = 10 * 1024 * 1024; // Aim for 10MB chunks
                        let shards = Math.ceil(bytes / targetChunkSize);
                        if (shards < 1) shards = 1;
                        if (shards > 500) shards = 500; // Cap to prevent request overhead
                        
                        let lanes = 3;
                        if (bytes < 10 * 1024 * 1024) lanes = 1;
                        
                        return { shards, lanes };
                    };

                    const specs = getNitroSpecs(file.size);
                    const chunks = specs.shards;
                    const pool = specs.lanes;
                    const chunkSize = Math.ceil(file.size / chunks);
                    const uploadId = 'admin-del-' + pendingDeliverId + '-' + Date.now();
                    let totalBytesUploaded = Array(chunks).fill(0);
                    
                    const uploadChunk = (i) => {
                        return new Promise((resolve, reject) => {
                            const start = i * chunkSize;
                            const end = Math.min(file.size, start + chunkSize);
                            const blob = file.slice(start, end);
                            
                            const xhr = new XMLHttpRequest();
                            activeNitroXHRs.push(xhr);
                            xhr.open('POST', '/api/upload/direct', true);
                            xhr.setRequestHeader('X-CSRF-TOKEN', '<?php echo e(csrf_token()); ?>');
                            
                            xhr.upload.onprogress = (e) => {
                                if (e.lengthComputable) {
                                    totalBytesUploaded[i] = e.loaded;
                                    const sumUploaded = totalBytesUploaded.reduce((a, b) => a + b, 0);
                                    const overallPercent = Math.round((sumUploaded / file.size) * 100);
                                    document.getElementById('adminNitroPercent').innerText = overallPercent + '%';
                                }
                            };
                            
                            xhr.onload = () => {
                                const idx = activeNitroXHRs.indexOf(xhr);
                                if (idx > -1) activeNitroXHRs.splice(idx, 1);
                                if (xhr.status === 200) {
                                    resolve();
                                } else {
                                    reject(new Error("Server returned " + xhr.status + " for shard " + i));
                                }
                            };
                            xhr.onerror = () => reject(new Error("Network error during shard " + i));
                            xhr.onabort = () => reject({ name: 'AbortError' });
                            
                            const formData = new FormData();
                            formData.append('file_chunk', blob);
                            formData.append('upload_id', uploadId);
                            formData.append('file_name', file.name);
                            formData.append('chunk_index', i);
                            formData.append('total_chunks', chunks);
                            formData.append('project_id', (activeMeta && activeMeta.project_id) ? activeMeta.project_id : ''); // 🚀 Shard into Project Folder (v173)
                            xhr.send(formData);
                        });
                    };

                    const uploadChunkWithRetry = async (i, attempt = 1) => {
                        try {
                            await uploadChunk(i);
                        } catch (err) {
                            if (err && err.name === 'AbortError') throw err;
                            if (attempt < 3) {
                                console.warn(`Shard ${i} failed (Attempt ${attempt}). Retrying...`);
                                await new Promise(r => setTimeout(r, 1000 * attempt)); // Exponential backoff
                                return uploadChunkWithRetry(i, attempt + 1);
                            }
                            throw err;
                        }
                    };

                    // Run in pools to prevent browser freeze
                    for (let i = 0; i < chunks; i += pool) {
                        if (nitroAborted) break;
                        const batch = [];
                        for (let j = 0; j < pool && (i + j) < chunks; j++) {
                            batch.push(uploadChunkWithRetry(i + j));
                        }
                        await Promise.all(batch);
                    }
                    
                    // 2. Finalize on Server
                    const finalRes = await fetch(API_BASE + '/api/admin/processing-requests/' + pendingDeliverId + '/delivery', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        signal: signal, // 🚀 CANCEL-AWARE (v169)
                        body: JSON.stringify({
                            nitro_delivery: true,
                            upload_id: uploadId,
                            file_name: file.name,
                            notes: notes,
                            delivery_method: method
                        })
                    });
                    
                    if (!finalRes.ok) {
                        let errMsg = "Server rejected final merge";
                        try {
                            const errData = await finalRes.json();
                            errMsg = errData.message || errMsg;
                        } catch (e) {
                            // Fallback if not JSON (e.g. 500 error page)
                        }
                        throw new Error(errMsg);
                    }

                    const data = await finalRes.json();
                    
                    // 🚀 UNLOCK MODAL (v178)
                    var modalEl = document.getElementById('deliverModal');
                    modalEl.setAttribute('data-bs-backdrop', 'true');
                    modalEl.setAttribute('data-bs-keyboard', 'true');
                    var closeBtn = modalEl.querySelector('.btn-close');
                    if (closeBtn) closeBtn.style.display = 'block';
                    window.onbeforeunload = null;

                    handleDeliveryResponse(data);

                } catch (err) {
                    if (err && err.name === 'AbortError') {
                        console.log("Nitro Delivery Aborted by user.");
                        return;
                    }
                    console.error("Nitro Delivery Failed:", err);
                    alert("Nitro Stream failed: " + (err.message || "Connection lost or server timeout."));
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bx bx-check me-1"></i> Confirm Delivered';
                } finally {
                    activeUploadController = null;
                    // Ensure cleanup on failure
                    if (nitroAborted || confirmBtn.disabled) {
                         var modalEl = document.getElementById('deliverModal');
                         modalEl.setAttribute('data-bs-backdrop', 'true');
                         modalEl.setAttribute('data-bs-keyboard', 'true');
                         var closeBtn = modalEl.querySelector('.btn-close');
                         if (closeBtn) closeBtn.style.display = 'block';
                         window.onbeforeunload = null;
                    }
                }
            } else {
                // Standard delivery for SFTP/GDrive (no file upload needed usually)
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Delivering…';
                var formData = new FormData(form);
                fetch(API_BASE + '/api/admin/processing-requests/' + pendingDeliverId + '/delivery', {
                  method: 'POST',
                  body: formData
                })
                  .then(r => r.json())
                  .then(data => handleDeliveryResponse(data))
                  .catch(err => {
                      console.error(err);
                      alert("Delivery failed.");
                      confirmBtn.disabled = false;
                      confirmBtn.innerHTML = '<i class="bx bx-check me-1"></i> Confirm Delivered';
                  });
            }
          });
        }

        function handleDeliveryResponse(data) {
            if (data.success) {
              deliverBSModal.hide();
              pendingDeliverId = null;
              document.getElementById('deliverForm').reset();
              loadRequests();
              loadUploads();
              var al = document.getElementById('uploadsAlert');
              var methodLabel = data.upload.delivery_method || 'selected method';
              var msg = 'Project delivered via ' + methodLabel + '. The client has been notified via email.';
              
              if (methodLabel.toLowerCase().includes('portal') || data.nitro_delivery) {
                  msg = '✅ Nitro Integrity Check Passed. Project delivered and data integrity verified! 🛰️';
              }

              al.textContent = msg;
              al.className = 'alert alert-success';
            } else {
              alert(data.message || 'Failed to mark as delivered.');
            }
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bx bx-check me-1"></i> Confirm Delivered';
        }

        // 🚀 SAFE-CANCEL (v170): Confirmation popup before aborting active uploads
        var cancelBtn = document.getElementById('deliverCancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                if (activeNitroXHRs.length > 0) {
                    if (confirm("Are you sure you want to cancel the active upload? All progress will be lost.")) {
                        deliverBSModal.hide();
                    }
                } else {
                    deliverBSModal.hide();
                }
            });
        }

        // 🚀 AUTO-CLEANUP (v169): Only cleanup if the user explicitly canceled
        var modalEl = document.getElementById('deliverModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                // If modal is hidden while activeNitroXHRs exist AND nitroAborted is true, we clean up.
                if (nitroAborted && activeNitroXHRs.length > 0) {
                    console.log("Cleanup: Nitro session aborted by user.");
                    if (activeUploadController) activeUploadController.abort();
                    activeNitroXHRs.forEach(xhr => xhr.abort());
                    activeNitroXHRs = [];
                }
            });
        }

      window.loadRequests = function loadRequests() {
        fetch(API_BASE + '/api/admin/processing-requests')
          .then(function (r) { return r.json(); })
          .then(function (rows) {
            allRequests = rows || [];
            requestsPage = 1;
            renderRequestsTable();
          })
          .catch(function () {
            document.getElementById('requestsTableBody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Could not load processing requests.</td></tr>';
          });
      }

      function renderRequestsTable() {
        var tbody = document.getElementById('requestsTableBody');
        var container = document.getElementById('requestsPagination');
        
        // 1. Filter
        var filtered = allRequests.filter(function(r) {
          if (!requestsSearch) return true;
          var term = requestsSearch.toLowerCase();
          var meta = uploadMetaById[r.upload_id] || {};
          return (
            String(r.id).includes(term) ||
            String(r.upload_id).includes(term) ||
            (meta.project_title || '').toLowerCase().includes(term) ||
            (meta.created_by_email || '').toLowerCase().includes(term)
          );
        });

        if (filtered.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No matching processing requests found.</td></tr>';
          container.innerHTML = '';
          return;
        }

        // 2. Paginate
        var totalPages = Math.ceil(filtered.length / itemsPerPage);
        if (requestsPage > totalPages) requestsPage = totalPages || 1;
        var start = (requestsPage - 1) * itemsPerPage;
        var paginated = filtered.slice(start, start + itemsPerPage);

        // 3. Render
        tbody.innerHTML = paginated.map(function (r) {
          var requested = r.requested_at ? new Date(r.requested_at).toLocaleString() : '–';
          var delivered = r.delivered_at ? new Date(r.delivered_at).toLocaleString() + (r.delivery_notes ? '<br><small class="text-muted">' + escapeHtml(r.delivery_notes) + '</small>' : '') : '–';

          // Enrich with upload metadata
          var meta = uploadMetaById[r.upload_id] || {};
          var projectTitle = escapeHtml(meta.project_title || meta.project_id || '–');
          var clientEmail = escapeHtml(meta.created_by_email || '–');

          // Determine status
          var displayStatus = r.delivered_at ? 'completed' : r.status;
          var statusBadgeHtml = '<span class="badge bg-label-' + (displayStatus === 'completed' ? 'success' : displayStatus === 'failed' ? 'danger' : 'primary') + '"><i class="bx ' + (displayStatus === 'completed' ? 'bx-check-circle' : displayStatus === 'failed' ? 'bx-x-circle' : 'bx-loader-alt') + ' me-1"></i>' + escapeHtml(displayStatus) + '</span>';

          var actionBtn = '–';
          if (r.delivered_at) {
            actionBtn = '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Delivered</span>';
          } else if (r.status === 'processing' || r.status === 'pending') {
            actionBtn =
              '<div class="d-flex flex-column gap-2">' +
              '<button type="button" class="btn btn-sm btn-success mark-delivered-btn" data-request-id="' + r.id + '" data-upload-id="' + r.upload_id + '"><i class="bx bx-check me-1"></i>Mark as Delivered</button>' +
              '</div>';
          }

          return '<tr>' +
            '<td>' + r.id + '</td>' +
            '<td>' + r.upload_id + '</td>' +
            '<td><strong>' + projectTitle + '</strong></td>' +
            '<td><small class="text-muted">' + clientEmail + '</small></td>' +
            '<td>' + statusBadgeHtml + '</td>' +
            '<td><small>' + requested + '</small></td>' +
            '<td><small>' + delivered + '</small></td>' +
            '<td>' + actionBtn + '</td>' +
            '</tr>';
        }).join('');

        // Re-attach listeners
        attachRequestListeners(tbody);
        
        // Pagination UI
        renderPaginationUI(container, requestsPage, totalPages, function(p) {
          requestsPage = p;
          renderRequestsTable();
        });
      }

      function attachRequestListeners(tbody) {
        tbody.querySelectorAll('.mark-delivered-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            pendingDeliverId = btn.getAttribute('data-request-id');
            var uploadId = btn.getAttribute('data-upload-id');
            activeMeta = uploadMetaById[uploadId] || {}; 
            var meta = activeMeta;
            
            var base = (uploadRootAbsolute || remoteBasePath || '/home/tiquan/').replace(/\/+$/, '');
            var clientUser = meta.client_sftp_user || 'guest';
            var targetPath = formatSftpPath(joinDisplayPath(base, 'uploads/' + clientUser + '/' + (meta.project_id || uploadId) + '/delivered/'));
            document.getElementById('deliverPathHint').textContent = targetPath;
            
            if (window.sftpHost) {
                document.getElementById('deliverHostDisplay').innerText = window.sftpHost;
            } else if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                document.getElementById('deliverHostDisplay').innerText = '172.21.107.151';
            } else {
                document.getElementById('deliverHostDisplay').innerText = window.location.hostname;
            }
            document.getElementById('deliverPortDisplay').innerText = window.sftpPort || '2222';
            document.getElementById('deliverUserDisplay').innerText = window.sftpUsername || 'tiquan';

            var pathStatusEl = document.getElementById('deliverPathStatus');
            pathStatusEl.innerHTML = '<span class="badge bg-label-warning"><i class="bx bx-loader-alt bx-spin me-1"></i>Preparing Folder...</span>';

            fetch('/api/admin/client-uploads/' + uploadId + '/ensure-delivery-folder', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    pathStatusEl.innerHTML = '<span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Folder Ready</span>';
                } else {
                    pathStatusEl.innerHTML = '<span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>Preparation Failed</span>';
                }
            })
            .catch(err => {
                pathStatusEl.innerHTML = '<span class="badge bg-label-danger"><i class="bx bx-error me-1"></i>Network Error</span>';
            });

            if (activeNitroXHRs.length === 0) {
                document.getElementById('deliverNotesInput').value = '';
                document.getElementById('deliverManualPathInput').value = '';
                document.getElementById('deliverGDriveLinkInput').value = '';
                document.getElementById('deliverFileInput').value = '';
                
                document.getElementById('deliverNotesInput').disabled = false;
                document.getElementById('deliverMethodSelect').disabled = false;
                document.getElementById('deliverFileInput').disabled = false;
            }

            var methodSelect = document.getElementById('deliverMethodSelect');
            var methodHint   = document.getElementById('deliverMethodHint');
            var type         = (meta.upload_type || '').toLowerCase();
            
            var secOR1 = document.getElementById('sectionOR1');
            var secB   = document.getElementById('sectionOptionB');
            var secOR2 = document.getElementById('sectionOR2');
            var secC   = document.getElementById('sectionOptionC');
            var secA   = document.getElementById('deliverFileInput').closest('.col-12');
            var pathBox = document.getElementById('deliverPathHintBox');
            
            const setDisplay = (a, b, c, or1, or2, path) => {
                secA.style.display    = a    ? 'block' : 'none';
                secB.style.display    = b    ? 'block' : 'none';
                secC.style.display    = c    ? 'block' : 'none';
                secOR1.style.display  = or1  ? 'block' : 'none';
                secOR2.style.display  = or2  ? 'block' : 'none';
                pathBox.style.display = path ? 'block' : 'none';
                document.getElementById('deliverGDriveLinkInput').required = c;
            };

            const filterDropdown = (val) => {
                methodSelect.value = val;
                Array.from(methodSelect.options).forEach(opt => {
                    opt.style.display = (opt.value === val) ? 'block' : 'none';
                });
            };

            if (type === 'browser' || type === 'multiple' || !type) {
                filterDropdown('portal');
                setDisplay(true, false, false, false, false, false);
            } 
            else if (type.includes('sftp')) {
                filterDropdown('sftp');
                setDisplay(false, true, false, false, false, true);
            }
            else if (type === 'google_drive') {
                filterDropdown('google_drive');
                setDisplay(false, false, true, false, false, false);
            }

            if (deliverBSModal) deliverBSModal.show();
          });
        });
      }

      loadPathConfig().finally(function () {
        loadUploads();
        loadRequests();
      });
    })();
  </script>
  <script src="<?php echo e(asset('assets')); ?>/js/admin-responsive.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/theme-switcher.js"></script>
</body>

</html><?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/admin/client-uploads.blade.php ENDPATH**/ ?>