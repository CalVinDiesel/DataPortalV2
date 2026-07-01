<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Map Pins - Admin | 3DHub</title>
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/admin-responsive.css" />
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <!-- Local assets for Popper and Bootstrap (prevents CDN network blocks/offline errors) -->
  <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
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
      margin-top: 8.5rem !important;
    }
    .layout-page {
        padding: 0 !important;
    }
    @media (max-width: 1199.98px) {
      .admin-nav-links { display: none; }
    }
    
    .map-table-nowrap {
      white-space: nowrap !important;
    }
    .actions-cell {
      width: 1%;
      white-space: nowrap !important;
    }
    .table-tight th, .table-tight td {
      padding-left: 0.75rem !important;
      padding-right: 0.75rem !important;
    }
  </style>
      
      <!-- Premium Glass Top Nav -->
      <nav class="admin-glass-nav">
        <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
          <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size: 1.1em;">3DHub Admin</span>
        </a>
        
        <div class="admin-nav-links d-none d-xl-flex">
          <a href="{{ route('admin_dashboard') }}" class="admin-nav-link">Dashboard</a>
          <a href="{{ route('admin.add_3d_model') }}" class="admin-nav-link">Add 3D Model</a>
          <a href="{{ route('admin.manage_map_pins') }}" class="admin-nav-link active">Manage Map Pins</a>
          <a href="{{ route('admin.manage_showcases') }}" class="admin-nav-link">Manage Showcase</a>
          <a href="{{ route('admin.client_uploads') }}" class="admin-nav-link">Client Uploads</a>
          <a href="{{ route('admin.inquiries') }}" class="admin-nav-link">Inquiries</a>
          <a href="{{ route('admin.manage_users') }}" class="admin-nav-link">Manage Users</a>
          <a href="{{ route('landing') }}" class="admin-nav-link" target="_blank">View Portal</a>
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

            @auth
            <div class="d-none d-md-flex align-items-center gap-3 border-start ps-3 ms-2">
                <a href="{{ route('profile') }}" class="small text-muted fw-medium text-decoration-none email-hover-link">{{ Auth::user()->email }}</a>
                <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm" class="d-inline">
                    @csrf
                    <button type="button" id="adminLogoutBtn" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold">Log out</button>
                </form>
            </div>
            @endauth

            <button class="admin-menu-toggle btn btn-icon d-xl-none border-0 bg-transparent p-0" type="button" aria-label="Toggle menu"><i class="bx bx-menu icon-lg"></i></button>
        </div>
      </nav>

      <div class="layout-page">
        <div class="content-wrapper content-wrapper-premium">
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold mb-0">Manage Map Pins</h4>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="syncFromJsonBtn">Sync from locations.json</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="exportToJsonBtn" title="Backfill data/locations.json from current database map pins">Export to locations.json</button>
                <a href="{{ route('admin.add_3d_model') }}" class="btn btn-sm btn-primary">Add new pin</a>
              </div>
            </div>
            <p class="text-muted mb-4">View and manage the 3D models appearing on the overview map. These pins are loaded into the Cesium viewer on the landing page.</p>
            <div id="pinsAlert" class="alert alert-info d-none"></div>
            <div class="card">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover table-tight mb-0">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Lat / Lon</th>
                        <th>3D Tiles</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="pinsTableBody">
                      <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Edit modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit map pin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editForm">
            <input type="hidden" id="editMapDataID" />
            <div class="mb-3">
              <label class="form-label" for="editTitle">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editTitle" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="editDescription">Description</label>
              <textarea class="form-control" id="editDescription" rows="2"></textarea>
            </div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label" for="editYAxis">Latitude <span class="text-danger">*</span></label>
                <input type="number" step="any" class="form-control" id="editYAxis" required />
              </div>
              <div class="col-md-6">
                <label class="form-label" for="editXAxis">Longitude <span class="text-danger">*</span></label>
                <input type="number" step="any" class="form-control" id="editXAxis" required />
              </div>
            </div>
            <div class="mb-3 mt-3">
              <label class="form-label" for="editTilesetUrl">3D Tiles URL (tileset.json) <span class="text-danger">*</span></label>
              <input type="url" class="form-control" id="editTilesetUrl" required />
            </div>
            <div class="mb-3">
              <label class="form-label" for="editThumbnailFile">Thumbnail image (overview map &amp; showcase)</label>
              <div class="mb-2">
                <div class="d-flex align-items-start gap-3 flex-wrap">
                  <div class="flex-shrink-0">
                    <img id="editThumbPreview" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Current thumbnail" class="rounded border" style="width:120px;height:80px;object-fit:cover;background:#f0f0f0" />
                    <div class="form-text small mt-1">Current</div>
                  </div>
                  <div class="flex-grow-1">
                    <input type="file" class="form-control form-control-sm" id="editThumbnailFile" accept="image/jpeg,image/png,image/gif,image/webp" />
                    <div class="form-text small">Upload a new image (JPEG, PNG, GIF, WebP; max 5MB). Used on overview map and showcase.</div>
                  </div>
                </div>
              </div>
              <div>
                <label class="form-label small text-muted" for="editThumbNailUrl">Or paste thumbnail URL</label>
                <input type="url" class="form-control" id="editThumbNailUrl" placeholder="e.g. /uploads/map-thumbnails/pin_123.jpg or full URL" />
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="editSaveBtn">Save changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Sync locations.json modal -->
  <div class="modal fade" id="confirmSyncPinsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Sync from locations.json</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-warning fw-bold mb-2">⚠️ WARNING: This will overwrite your active map pin database records!</p>
          <p>Syncing will replace all database map pins with the entries currently saved in <code>data/locations.json</code>.</p>
          <p class="text-danger fw-bold">Important Result:</p>
          <ul>
            <li>Any map pins you previously deleted from the dashboard <strong>will be recovered and restored</strong> back to the database and map if they are still listed in <code>locations.json</code>.</li>
            <li>Any custom map pins added to the dashboard that were not exported will be deleted from the database.</li>
          </ul>
          <p>Are you sure you want to proceed with this sync?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="confirmSyncPinsBtn">Proceed Sync</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Export locations.json modal -->
  <div class="modal fade" id="confirmExportPinsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Export to locations.json</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-warning fw-bold mb-2">⚠️ WARNING: This will overwrite the locations.json configuration file!</p>
          <p>Exporting will save the current database map pin list directly to <code>public/data/locations.json</code>.</p>
          <p class="text-danger fw-bold">Important Result:</p>
          <ul>
            <li>Any map pins you deleted from the dashboard <strong>will be permanently deleted</strong> from <code>locations.json</code> on the server filesystem.</li>
            <li>This saves your current dashboard curation permanently as the offline/fallback list.</li>
          </ul>
          <p>Are you sure you want to proceed with this export?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmExportPinsBtn">Proceed Export</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var API_BASE = (typeof window !== 'undefined' && window.TemaDataPortal_API_BASE) || (window.location ? window.location.origin : '') || 'http://localhost:3000';
      var currentPins = [];

      function escapeHtml(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
      }

      function truncate(str, len) {
        if (!str) return '';
        return str.length <= (len || 40) ? str : str.slice(0, len) + '…';
      }

      function setTableMessage(html) {
        var tbody = document.getElementById('pinsTableBody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">' + html + '</td></tr>';
      }

      function loadPins() {
        fetch(API_BASE + '/api/map-data')
          .then(function (r) {
            if (!r.ok) return r.json().then(function (j) { return Promise.reject({ status: r.status, body: j }); });
            return r.json();
          })
          .then(function (rows) {
            currentPins = Array.isArray(rows) ? rows : [];
            var tbody = document.getElementById('pinsTableBody');
            if (!Array.isArray(rows)) {
              setTableMessage('Server did not return a list. Make sure the auth server is running (npm start) and try <strong>Sync from locations.json</strong> above.');
              return;
            }
            if (rows.length === 0) {
              tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No map pins yet. Click <strong>Sync from locations.json</strong> above to copy pins from the map data file, or <a href="{{ route('admin.add_3d_model') }}">add a 3D model</a>.</td></tr>';
              return;
            }
            tbody.innerHTML = rows.map(function (r) {
              var id = r.mapDataID || r.id || '';
              var lat = r.yAxis != null ? Number(r.yAxis).toFixed(5) : '–';
              var lon = r.xAxis != null ? Number(r.xAxis).toFixed(5) : '–';
              var tiles = (r['3dTiles'] || r.tilesetUrl || '').trim();
              var tilesDisplay = tiles ? '<a href="' + escapeHtml(tiles) + '" target="_blank" class="text-truncate d-inline-block" style="max-width:150px">' + escapeHtml(truncate(tiles, 25)) + '</a>' : '–';
              return '<tr>' +
                '<td class="map-table-nowrap"><code class="text-primary">' + escapeHtml(id) + '</code></td>' +
                '<td class="map-table-nowrap"><strong>' + escapeHtml(r.title || '') + '</strong></td>' +
                '<td><small class="text-muted">' + escapeHtml(truncate(r.description || '', 45)) + '</small></td>' +
                '<td class="map-table-nowrap"><span class="badge bg-label-secondary">' + lat + ' / ' + lon + '</span></td>' +
                '<td>' + tilesDisplay + '</td>' +
                '<td class="actions-cell">' +
                  '<div class="d-flex flex-nowrap gap-2">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-pin-btn" data-id="' + escapeHtml(id) + '"><i class="bx bx-edit-alt"></i> Edit</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger delete-pin-btn" data-id="' + escapeHtml(id) + '"><i class="bx bx-trash"></i> Delete</button>' +
                  '</div>' +
                '</td>' +
                '</tr>';
            }).join('');

            tbody.querySelectorAll('.edit-pin-btn').forEach(function (btn) {
              btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                if (!id) return;
                
                var row = currentPins.find(function (p) {
                  return (p.mapDataID || p.id) === id;
                });

                if (!row) {
                  alert('Could not load pin data.');
                  return;
                }

                document.getElementById('editMapDataID').value = row.mapDataID || row.id || '';
                document.getElementById('editTitle').value = row.title || '';
                document.getElementById('editDescription').value = row.description || '';
                document.getElementById('editYAxis').value = row.yAxis != null ? row.yAxis : '';
                document.getElementById('editXAxis').value = row.xAxis != null ? row.xAxis : '';
                document.getElementById('editTilesetUrl').value = row['3dTiles'] || row.tilesetUrl || '';
                var thumbUrl = (row.thumbNailUrl || row.thumbnailUrl || '').trim();
                document.getElementById('editThumbNailUrl').value = thumbUrl;
                var previewEl = document.getElementById('editThumbPreview');
                if (thumbUrl) {
                  var fullUrl = thumbUrl.indexOf('http') === 0 ? thumbUrl : (API_BASE + (thumbUrl.indexOf('/') === 0 ? '' : '/') + thumbUrl);
                  previewEl.src = fullUrl;
                  previewEl.onerror = function () { previewEl.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; };
                } else {
                  previewEl.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                }
                document.getElementById('editThumbnailFile').value = '';
                var modalEl = document.getElementById('editModal');
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
              });
            });

            tbody.querySelectorAll('.delete-pin-btn').forEach(function (btn) {
              btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                if (!id) return;
                if (!confirm('Remove this pin from the overview map? It will stay in the showcase until you remove it there.')) return;
                fetch(API_BASE + '/api/map-data/' + encodeURIComponent(id), { method: 'DELETE' })
                  .then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
                  .then(function (x) {
                    if (x.status === 200 && x.body.success) {
                      loadPins();
                      var alertEl = document.getElementById('pinsAlert');
                      if (alertEl) { alertEl.textContent = x.body.message || 'Pin removed from map.'; alertEl.className = 'alert alert-success'; alertEl.classList.remove('d-none'); setTimeout(function () { alertEl.classList.add('d-none'); }, 3000); }
                    } else { alert(x.body.message || 'Delete failed.'); }
                  })
                  .catch(function () { alert('Request failed.'); });
              });
            });
          })
          .catch(function (err) {
            var msg = 'Could not load map pins. ';
            if (err && err.body && err.body.message) msg += err.body.message + ' ';
            else if (err && err.body && err.body.error) msg += err.body.error + ' ';
            msg += 'Make sure the auth server is running at <code>' + escapeHtml(API_BASE) + '</code> (e.g. <code>npm start</code> from project root). Then try <strong>Sync from locations.json</strong> above.';
            setTableMessage(msg);
          });
      }

      var syncBtn = document.getElementById('syncFromJsonBtn');
      if (syncBtn) syncBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmSyncPinsModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
      });

      var confirmSyncPinsBtn = document.getElementById('confirmSyncPinsBtn');
      if (confirmSyncPinsBtn) confirmSyncPinsBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmSyncPinsModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        var btn = syncBtn;
        btn.disabled = true;
        fetch(API_BASE + '/api/admin/seed-map_data-from-locations', { method: 'POST' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              loadPins();
              var alertEl = document.getElementById('pinsAlert');
              if (alertEl) { alertEl.textContent = data.message || 'Synced.'; alertEl.className = 'alert alert-success'; alertEl.classList.remove('d-none'); setTimeout(function () { alertEl.classList.add('d-none'); }, 5000); }
            } else {
              alert(data.message || 'Sync failed.');
            }
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { btn.disabled = false; });
      });

      var exportBtn = document.getElementById('exportToJsonBtn');
      if (exportBtn) exportBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmExportPinsModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
      });

      var confirmExportPinsBtn = document.getElementById('confirmExportPinsBtn');
      if (confirmExportPinsBtn) confirmExportPinsBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmExportPinsModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        var btn = exportBtn;
        btn.disabled = true;
        fetch(API_BASE + '/api/admin/export-locations-json', { method: 'POST' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data && data.success) {
              var alertEl = document.getElementById('pinsAlert');
              if (alertEl) { alertEl.textContent = data.message || 'Exported.'; alertEl.className = 'alert alert-success'; alertEl.classList.remove('d-none'); setTimeout(function () { alertEl.classList.add('d-none'); }, 5000); }
            } else {
              alert((data && data.message) || 'Export failed.');
            }
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { btn.disabled = false; });
      });

      var editThumbFile = document.getElementById('editThumbnailFile');
      if (editThumbFile) editThumbFile.addEventListener('change', function () {
        var file = this.files && this.files[0];
        var previewEl = document.getElementById('editThumbPreview');
        if (file && previewEl) {
          var url = (window.URL || window.webkitURL).createObjectURL(file);
          previewEl.onerror = null;
          previewEl.src = url;
        }
      });

      var editSaveBtn = document.getElementById('editSaveBtn');
      if (editSaveBtn) editSaveBtn.addEventListener('click', function () {
        var mapDataID = document.getElementById('editMapDataID').value.trim();
        if (!mapDataID) return;
        const titleVal = document.getElementById('editTitle').value.trim();
        const latVal = parseFloat(document.getElementById('editYAxis').value);
        const lonVal = parseFloat(document.getElementById('editXAxis').value);
        const tilesetVal = document.getElementById('editTilesetUrl').value.trim();

        if (!titleVal) {
          alert('Please enter a Title for the map pin.');
          return;
        }
        if (isNaN(latVal) || isNaN(lonVal)) {
          alert('Please enter valid numerical Latitude and Longitude coordinates.');
          return;
        }
        if (!tilesetVal) {
          alert('Please enter a valid 3D Tileset URL (tileset.json).');
          return;
        }
        var btn = this;
        btn.disabled = true;
        var thumbFile = document.getElementById('editThumbnailFile').files && document.getElementById('editThumbnailFile').files[0];
        
        function buildPayload(thumbNailUrl) {
          return {
            mapDataID: mapDataID,
            title: document.getElementById('editTitle').value.trim() || mapDataID,
            description: document.getElementById('editDescription').value.trim(),
            yAxis: parseFloat(document.getElementById('editYAxis').value),
            xAxis: parseFloat(document.getElementById('editXAxis').value),
            '3dTiles': document.getElementById('editTilesetUrl').value.trim(),
            thumbNailUrl: (thumbNailUrl || document.getElementById('editThumbNailUrl').value.trim() || '').trim(),
            is_update: true
          };
        }
        function saveMapData(payload) {
          return fetch(API_BASE + '/api/map-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          }).then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); });
        }
        function onSaved(x) {
          if (x.status === 200 && x.body.success) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            loadPins();
            var alertEl = document.getElementById('pinsAlert');
            alertEl.textContent = 'Pin updated. Thumbnail will appear on the overview map and showcase.';
            alertEl.className = 'alert alert-success';
            alertEl.classList.remove('d-none');
            setTimeout(function () { alertEl.classList.add('d-none'); }, 3000);
          } else {
            alert(x.body.message || 'Save failed.');
          }
        }
        
        function doUpload(fileToUpload) {
          if (!fileToUpload) {
              saveMapData(buildPayload()).then(onSaved).catch(function () { alert('Request failed.'); }).finally(function () { btn.disabled = false; });
              return;
          }
          
          var fd = new FormData();
          fd.append('mapDataID', mapDataID);
          fd.append('pin_image', fileToUpload); // Form field must match Laravel UploadController validation
          
          fetch('{{ route('upload.pin-image') }}', { 
            method: 'POST', 
            body: fd, 
            headers: { 
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            },
            credentials: 'same-origin' 
          })
            .then(function (r) {
              return r.text().then(function (text) {
                var body;
                try { body = JSON.parse(text); } catch (e) { body = {}; }
                return { status: r.status, body: body, text: text };
              });
            })
            .then(function (up) {
              if (up.status === 200 && up.body.success && up.body.url) {
                // Let the uploaded image URL be saved directly into MapData via backend
                return saveMapData(buildPayload(up.body.url)).then(onSaved);
              }
              var msg = up.body && up.body.message ? up.body.message : ('Upload failed (HTTP ' + up.status + '). ' + (up.text && up.text.length < 200 ? up.text : ''));
              if (up.body && up.body.errors) {
                  for (var key in up.body.errors) {
                      msg += '\n- ' + up.body.errors[key].join(', ');
                  }
              }
              alert(msg || 'Thumbnail upload failed.');
            })
            .catch(function (err) {
              var hint = (err.message || '').toLowerCase();
              var msg = 'Thumbnail upload failed. ';
              if (hint.indexOf('fetch') !== -1 || hint.indexOf('network') !== -1 || hint.indexOf('failed') !== -1) {
                msg += 'Cannot reach server at ' + API_BASE + '. Open the admin from the same origin (e.g. http://localhost:3000/...) or check the server is running.';
              } else {
                msg += (err.message || 'Check browser and server console.');
              }
              alert(msg);
            })
            .finally(function () { btn.disabled = false; });
        }
        
        if (thumbFile && thumbFile.size > 2 * 1024 * 1024) {
          var alertEl = document.getElementById('pinsAlert');
          if(alertEl) { alertEl.textContent = 'Compressing image...'; alertEl.className = 'alert alert-info'; alertEl.classList.remove('d-none'); }
          
          var img = new Image();
          var url = URL.createObjectURL(thumbFile);
          img.onload = function() {
              URL.revokeObjectURL(url);
              var canvas = document.createElement('canvas');
              var MAX_DIM = 1200;
              var w = img.width; 
              var h = img.height;
              
              if (w > h && w > MAX_DIM) { 
                  h *= MAX_DIM/w; w = MAX_DIM; 
              } else if (h > MAX_DIM) { 
                  w *= MAX_DIM/h; h = MAX_DIM; 
              }
              
              canvas.width = w; 
              canvas.height = h;
              var ctx = canvas.getContext('2d');
              ctx.drawImage(img, 0, 0, w, h);
              
              canvas.toBlob(function(blob) {
                  var newFile = new File([blob], thumbFile.name.replace(/\.[^/.]+$/, "") + ".jpg", { type: 'image/jpeg' });
                  doUpload(newFile);
              }, 'image/jpeg', 0.85); // 85% quality JPEG
          };
          img.onerror = function() {
              doUpload(thumbFile); // fallback to original if image load fails
          };
          img.src = url;
        } else {
          doUpload(thumbFile);
        }
      });

      loadPins();
    })();
  </script>
  <script src="{{ asset('assets') }}/js/admin-responsive.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
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
