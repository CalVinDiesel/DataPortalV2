<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Showcase - Admin | 3DHub</title>
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/admin-responsive.css" />
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
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
  </style>
</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      
      <!-- Premium Glass Top Nav -->
      <nav class="admin-glass-nav">
        <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
          <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size: 1.1em;">3DHub Admin</span>
        </a>
        
        <div class="admin-nav-links d-none d-xl-flex">
          <a href="{{ route('admin_dashboard') }}" class="admin-nav-link">Dashboard</a>
          <a href="{{ route('admin.add_3d_model') }}" class="admin-nav-link">Add 3D Model</a>
          <a href="{{ route('admin.manage_map_pins') }}" class="admin-nav-link">Manage Map Pins</a>
          <a href="{{ route('admin.manage_showcases') }}" class="admin-nav-link active">Manage Showcase</a>
          {{-- TEMPORARILY HIDDEN FOR PRE-LAUNCH (3D MODEL SALES FIRST)
          <a href="{{ route('admin.client_uploads') }}" class="admin-nav-link">Client Uploads</a>
          --}}
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
              <h4 class="fw-bold mb-0">Manage Showcase</h4>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="syncShowcaseFromJsonBtn">Sync from showcases.json</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="exportShowcasesJsonBtn" title="Backfill data/showcases.json from current database showcase items">Export to showcases.json</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="renumberOrdersBtn" title="Fix order numbers to 0, 1, 2, …">Renumber orders</button>
                <button type="button" class="btn btn-sm btn-primary" id="addToShowcaseBtn">Add to showcase</button>
              </div>
            </div>
            <p class="text-muted mb-4">Manage which locations appear in the <strong>landing page showcase</strong> (tiles section). Use <strong>Sync from showcases.json</strong> to sync showcase settings from <code>data/showcases.json</code>. The showcase is independent of the map: if you delete a pin from the overview map, it stays in the showcase until you remove it here.</p>
            <div id="showcaseAlert" class="alert alert-info d-none"></div>
            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr><th>Order</th><th>Location ID</th><th>Title</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="showcaseTableBody">
                      <tr><td colspan="4" class="text-center text-muted">Loading…</td></tr>
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
  <!-- Add to showcase modal -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add to showcase</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <p class="text-muted small">Choose a map location to show in the landing page showcase. You can choose any location from the overview map. If the selected location has not been registered as a database map pin yet, it will be automatically registered when added.</p>
          <label class="form-label" for="addMapDataId">Map location</label>
          <select class="form-select" id="addMapDataId">
            <option value="">-- Select a location --</option>
          </select>
          <label class="form-label mt-2" for="addDisplayOrder">Display order (0 = first, 1 = second, …)</label>
          <input type="number" class="form-control" id="addDisplayOrder" value="0" min="0" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="addConfirmBtn">Add</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Delete options modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Remove from showcase</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <p>Remove this item from the landing page showcase. You can also remove it from the overview map at the same time.</p>
          <div class="form-check"><input class="form-check-input" type="radio" name="deleteFrom" id="deleteShowcaseOnly" value="showcase_only" checked /><label class="form-check-label" for="deleteShowcaseOnly">Remove from showcase only</label></div>
          <div class="form-check"><input class="form-check-input" type="radio" name="deleteFrom" id="deleteBoth" value="both" /><label class="form-check-label" for="deleteBoth">Remove from showcase and from overview map</label></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Remove</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Sync showcases.json modal -->
  <div class="modal fade" id="confirmSyncShowcasesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Sync from showcases.json</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-warning fw-bold mb-2">⚠️ WARNING: This will overwrite your active showcase database records!</p>
          <p>Syncing will replace all database showcases with the entries currently saved in <code>data/showcases.json</code>.</p>
          <p class="text-danger fw-bold">Important Result:</p>
          <ul>
            <li>Any showcase items you previously deleted from the dashboard <strong>will be recovered and restored</strong> back to the database and homepage if they are still listed in <code>showcases.json</code>.</li>
            <li>Any custom showcases added to the dashboard that were not exported will be deleted from the database.</li>
          </ul>
          <p>Are you sure you want to proceed with this sync?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="confirmSyncShowcasesBtn">Proceed Sync</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirm Export showcases.json modal -->
  <div class="modal fade" id="confirmExportShowcasesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Export to showcases.json</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-warning fw-bold mb-2">⚠️ WARNING: This will overwrite the showcases.json configuration file!</p>
          <p>Exporting will save the current database showcase list directly to <code>public/data/showcases.json</code>.</p>
          <p class="text-danger fw-bold">Important Result:</p>
          <ul>
            <li>Any showcase locations you deleted from the dashboard <strong>will be permanently deleted</strong> from <code>showcases.json</code> on the server filesystem.</li>
            <li>This saves your current dashboard curation permanently as the offline/fallback list.</li>
          </ul>
          <p>Are you sure you want to proceed with this export?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmExportShowcasesBtn">Proceed Export</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var API_BASE = (typeof window !== 'undefined' && window.TemaDataPortal_API_BASE) || (window.location && window.location.origin) || 'http://localhost:3000';
      var pendingDeleteId = null;
      var existingShowcaseMapIds = new Set();

      function escapeHtml(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
      function showAlert(msg, type) {
        var el = document.getElementById('showcaseAlert'); if (!el) return;
        el.textContent = msg; el.className = 'alert alert-' + (type || 'info'); el.classList.remove('d-none');
        setTimeout(function () { el.classList.add('d-none'); }, 4000);
      }

      function loadShowcase() {
        fetch(API_BASE + '/api/showcases').then(function (r) { return r.json(); }).then(function (rows) {
          var tbody = document.getElementById('showcaseTableBody');
          existingShowcaseMapIds = new Set((Array.isArray(rows) ? rows : []).map(function (r) { return (r && r.map_data_id) ? String(r.map_data_id) : ''; }).filter(Boolean));
          if (!Array.isArray(rows) || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No showcase items yet. Add map pins from <a href="{{ route('admin.manage_map_pins') }}">Manage Map Pins</a>, then add them here.</td></tr>';
            return;
          }
          tbody.innerHTML = rows.map(function (r) {
            return '<tr><td>' + (r.display_order != null ? r.display_order : '') + '</td><td><code>' + escapeHtml(r.map_data_id || '') + '</code></td><td>' + escapeHtml(r.title || r.map_data_id || '') + '</td><td><button type="button" class="btn btn-sm btn-outline-primary me-1 edit-order-btn" data-id="' + r.id + '" data-order="' + (r.display_order != null ? r.display_order : 0) + '">Edit order</button><button type="button" class="btn btn-sm btn-outline-danger delete-showcase-btn" data-id="' + r.id + '">Remove</button></td></tr>';
          }).join('');

          tbody.querySelectorAll('.edit-order-btn').forEach(function (btn) {
            btn.onclick = function () {
              var id = btn.getAttribute('data-id'); var order = parseInt(btn.getAttribute('data-order'), 10);
              var newOrder = prompt('Display order (0 = first, 1 = second, …):', order); if (newOrder === null) return;
              newOrder = parseInt(newOrder, 10); if (isNaN(newOrder)) newOrder = 0;
              fetch(API_BASE + '/api/showcases/' + id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ display_order: newOrder }) })
                .then(function (r) { return r.json(); }).then(function (data) { if (data.success) loadShowcase(); showAlert('Order updated.', 'success'); }).catch(function () { alert('Request failed.'); });
            };
          });
          tbody.querySelectorAll('.delete-showcase-btn').forEach(function (btn) {
            btn.onclick = function () {
              pendingDeleteId = btn.getAttribute('data-id');
              document.getElementById('deleteShowcaseOnly').checked = true;
              var modal = new bootstrap.Modal(document.getElementById('deleteModal')); modal.show();
            };
          });
        }).catch(function () {
          document.getElementById('showcaseTableBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Could not load showcase. Ensure the server is running and PostgreSQL is configured. Run <code>06-showcase-table.sql</code> in pgAdmin if the Showcase table does not exist.</td></tr>';
        });
      }

      var renumberBtn = document.getElementById('renumberOrdersBtn');
      if (renumberBtn) renumberBtn.addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        fetch(API_BASE + '/api/admin/showcases-renumber', { method: 'POST' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) { loadShowcase(); showAlert(data.message || 'Orders renumbered to 0, 1, 2, …', 'success'); }
            else alert(data.message || 'Renumber failed.');
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { btn.disabled = false; });
      });
      var syncShowcaseBtn = document.getElementById('syncShowcaseFromJsonBtn');
      if (syncShowcaseBtn) syncShowcaseBtn.addEventListener('click', function () {
        var modal = new bootstrap.Modal(document.getElementById('confirmSyncShowcasesModal'));
        modal.show();
      });

      var confirmSyncShowcasesBtn = document.getElementById('confirmSyncShowcasesBtn');
      if (confirmSyncShowcasesBtn) confirmSyncShowcasesBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmSyncShowcasesModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        var btn = syncShowcaseBtn;
        btn.disabled = true;
        fetch(API_BASE + '/api/admin/seed-showcases-from-locations', { method: 'POST' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) { loadShowcase(); showAlert(data.message || 'Showcase synced from showcases.json.', 'success'); }
            else alert(data.message || 'Sync failed.');
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { btn.disabled = false; });
      });

      var exportShowcasesBtn = document.getElementById('exportShowcasesJsonBtn');
      if (exportShowcasesBtn) exportShowcasesBtn.addEventListener('click', function () {
        var modal = new bootstrap.Modal(document.getElementById('confirmExportShowcasesModal'));
        modal.show();
      });

      var confirmExportShowcasesBtn = document.getElementById('confirmExportShowcasesBtn');
      if (confirmExportShowcasesBtn) confirmExportShowcasesBtn.addEventListener('click', function () {
        var modalEl = document.getElementById('confirmExportShowcasesModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        var btn = exportShowcasesBtn;
        btn.disabled = true;
        fetch(API_BASE + '/api/admin/export-showcases-json', { method: 'POST' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data && data.success) {
              showAlert(data.message || 'Exported to showcases.json.', 'success');
            } else {
              alert((data && data.message) || 'Export failed.');
            }
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { btn.disabled = false; });
      });

      var allLocations = [];
      var dbPinIds = new Set();

      document.getElementById('addToShowcaseBtn').addEventListener('click', function () {
        var p1 = fetch(API_BASE + '/api/map-data').then(function (r) { return r.ok ? r.json() : []; }).catch(function () { return []; });
        var p2 = fetch('../../data/locations.json').then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; });
        
        Promise.all([p1, p2]).then(function (results) {
          var mapRows = results[0];
          var locsJson = results[1];
          
          dbPinIds = new Set();
          var merged = [];
          var seenIds = new Set();
          
          if (Array.isArray(mapRows)) {
            mapRows.forEach(function (row) {
              var id = row.mapDataID || row.id;
              if (id) {
                dbPinIds.add(id);
                if (!seenIds.has(id)) {
                  seenIds.add(id);
                  merged.push({
                    id: id,
                    title: row.title || id,
                    description: row.description || '',
                    xAxis: row.xAxis != null ? parseFloat(row.xAxis) : 0,
                    yAxis: row.yAxis != null ? parseFloat(row.yAxis) : 0,
                    '3dTiles': row['3dTiles'] || '',
                    thumbNailUrl: row.thumbNailUrl || '',
                    inDb: true
                  });
                }
              }
            });
          }
          
          if (locsJson && Array.isArray(locsJson.locations)) {
            locsJson.locations.forEach(function (loc) {
              var id = loc.id;
              if (id && !seenIds.has(id)) {
                seenIds.add(id);
                merged.push({
                  id: id,
                  title: loc.name || id,
                  description: loc.description || '',
                  xAxis: loc.coordinates && loc.coordinates.longitude != null ? parseFloat(loc.coordinates.longitude) : 0,
                  yAxis: loc.coordinates && loc.coordinates.latitude != null ? parseFloat(loc.coordinates.latitude) : 0,
                  '3dTiles': loc.dataPaths && loc.dataPaths.tileset ? loc.dataPaths.tileset : '',
                  thumbNailUrl: loc.thumbnailUrl || loc.thumbNailUrl || loc.previewImage || '',
                  inDb: false
                });
              }
            });
          }
          
          allLocations = merged;
          
          var select = document.getElementById('addMapDataId');
          select.innerHTML = '<option value="">-- Select a location --</option>';
          merged.forEach(function (loc) {
            select.appendChild(new Option(loc.title + ' (' + loc.id + ')', loc.id));
          });
          
          var modal = new bootstrap.Modal(document.getElementById('addModal'));
          modal.show();
        }).catch(function () {
          alert('Could not load map locations.');
        });
      });

      document.getElementById('addConfirmBtn').addEventListener('click', function () {
        var mapDataId = document.getElementById('addMapDataId').value.trim();
        var order = parseInt(document.getElementById('addDisplayOrder').value, 10); if (isNaN(order)) order = 0;
        if (!mapDataId) { alert('Select a location.'); return; }
        if (existingShowcaseMapIds && existingShowcaseMapIds.has(mapDataId)) {
          alert('This specific 3D model showcase has been added into the showcase already, cannot add duplicate 3D model in showcase section.');
          return;
        }

        var confirmBtn = this;
        confirmBtn.disabled = true;

        var selectedLoc = allLocations.find(function (loc) { return loc.id === mapDataId; });

        function saveToShowcase() {
          fetch(API_BASE + '/api/showcases', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ map_data_id: mapDataId, display_order: order }) 
          })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) { 
              bootstrap.Modal.getInstance(document.getElementById('addModal')).hide(); 
              loadShowcase(); 
              showAlert('Added to showcase.', 'success'); 
            } else { 
              alert(data.message || 'Failed to add to showcase.'); 
            }
          })
          .catch(function () { alert('Request failed.'); })
          .finally(function () { confirmBtn.disabled = false; });
        }

        if (selectedLoc && !dbPinIds.has(mapDataId)) {
          // Auto-provision map pin in the database first
          fetch(API_BASE + '/api/map-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              mapDataID: selectedLoc.id,
              title: selectedLoc.title,
              description: selectedLoc.description,
              xAxis: selectedLoc.xAxis,
              yAxis: selectedLoc.yAxis,
              '3dTiles': selectedLoc['3dTiles'],
              thumbNailUrl: selectedLoc.thumbNailUrl,
              is_update: false
            })
          })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.success) {
              saveToShowcase();
            } else {
              alert(data.message || 'Failed to auto-register map pin in database.');
              confirmBtn.disabled = false;
            }
          })
          .catch(function () {
            alert('Request failed during map pin auto-provisioning.');
            confirmBtn.disabled = false;
          });
        } else {
          saveToShowcase();
        }
      });

      document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
        if (!pendingDeleteId) return;
        var from = document.querySelector('input[name="deleteFrom"]:checked').value || 'showcase_only';
        fetch(API_BASE + '/api/showcases/' + pendingDeleteId + '?from=' + from, { method: 'DELETE' })
          .then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) { bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide(); pendingDeleteId = null; loadShowcase(); showAlert(data.message || 'Removed.', 'success'); }
            else alert(data.message || 'Failed.');
          }).catch(function () { alert('Request failed.'); });
      });

      loadShowcase();
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
