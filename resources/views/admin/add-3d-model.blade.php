<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add 3D Model - Admin | 3DHub</title>
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/admin-responsive.css" />
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
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
        <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
          <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size: 1.1em;">3DHub Admin</span>
        </a>
        
        <div class="admin-nav-links d-none d-xl-flex">
          <a href="{{ route('admin_dashboard') }}" class="admin-nav-link">Dashboard</a>
          <a href="{{ route('admin.add_3d_model') }}" class="admin-nav-link active">Add 3D Model</a>
          <a href="{{ route('admin.manage_map_pins') }}" class="admin-nav-link">Manage Map Pins</a>
          <a href="{{ route('admin.manage_showcases') }}" class="admin-nav-link">Manage Showcase</a>
          <a href="{{ route('admin.client_uploads') }}" class="admin-nav-link">Client Uploads</a>
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
              <h4 class="fw-bold mb-0">Add 3D Model to Map</h4>
              <a href="{{ route('admin_dashboard') }}" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
            </div>
            <p class="text-muted mb-4">Create a new 3D model entry. It will appear on the overview map and in showcases. Store a tileset URL (e.g. Cesium 3D Tiles) and position (latitude/longitude).</p>
            <div class="card">
              <div class="card-body">
                <form id="addModelForm">
                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="mapDataID">Model ID <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="mapDataID" name="mapDataID" placeholder="e.g. my-building-2025" required />
                      <div class="form-text">Unique ID (letters, numbers, hyphens, underscores only).</div>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="title" name="title" placeholder="Display name on map" required />
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="description">Description</label>
                      <textarea class="form-control" id="description" name="description" rows="2" placeholder="Short description"></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="yAxis">Latitude <span class="text-danger">*</span></label>
                      <input type="number" step="any" class="form-control" id="yAxis" name="yAxis" placeholder="e.g. 5.957839" required />
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="xAxis">Longitude <span class="text-danger">*</span></label>
                      <input type="number" step="any" class="form-control" id="xAxis" name="xAxis" placeholder="e.g. 116.070466" required />
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="tilesetUrl">3D Tiles URL (tileset.json) <span class="text-danger">*</span></label>
                      <input type="url" class="form-control" id="tilesetUrl" name="3dTiles" placeholder="https://example.com/tileset.json" required />
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="thumbnailFile">Thumbnail image (overview map &amp; showcase)</label>
                      <div class="mb-2">
                        <input type="file" class="form-control" id="thumbnailFile" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp" />
                        <div class="form-text">Upload an image (JPEG, PNG, GIF, WebP; max 5MB). Used for the map pin and showcase tile.</div>
                      </div>
                      <label class="form-label small text-muted" for="thumbNailUrl">Or paste a thumbnail URL</label>
                      <input type="url" class="form-control" id="thumbNailUrl" name="thumbNailUrl" placeholder="Optional: image URL if not uploading" />
                    </div>
                    <div class="col-12">
                      <button type="button" class="btn btn-outline-secondary me-2" id="clearBtn">Clear Filled Box</button>
                      <button type="submit" class="btn btn-primary" id="submitBtn">Save 3D Model</button>
                      <span id="formMessage" class="ms-3"></span>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var API_BASE = (typeof window !== 'undefined' && window.TemaDataPortal_API_BASE) || (window.location ? window.location.origin : '') || 'http://localhost:3000';
      var form = document.getElementById('addModelForm');
      var submitBtn = document.getElementById('submitBtn');
      var formMessage = document.getElementById('formMessage');
      var clearBtn = document.getElementById('clearBtn');
      var thumbnailFile = document.getElementById('thumbnailFile');

      if (clearBtn) clearBtn.addEventListener('click', function () {
        if (form) form.reset();
        if (thumbnailFile) thumbnailFile.value = '';
        if (formMessage) {
          formMessage.textContent = '';
          formMessage.className = 'ms-3';
        }
      });

      document.getElementById('mapDataID').addEventListener('input', function () {
        var v = this.value.replace(/[^a-zA-Z0-9_-]/g, '-');
        if (v !== this.value) this.value = v;
      });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        var mapDataID = document.getElementById('mapDataID').value.trim();
        var title = document.getElementById('title').value.trim();
        var yAxis = document.getElementById('yAxis').value.trim();
        var xAxis = document.getElementById('xAxis').value.trim();
        var tilesetUrl = document.getElementById('tilesetUrl').value.trim();
        var description = document.getElementById('description').value.trim();
        var thumbNailUrl = document.getElementById('thumbNailUrl').value.trim();
        var hasFile = thumbnailFile && thumbnailFile.files && thumbnailFile.files.length > 0;

        // 1. Strict Missing Field Check (alert if any single field is omitted, Scenario 3)
        var missing = [];
        if (!mapDataID) missing.push("Model ID");
        if (!title) missing.push("Title");
        if (!description) missing.push("Description");
        if (!yAxis) missing.push("Latitude");
        if (!xAxis) missing.push("Longitude");
        if (!tilesetUrl) missing.push("3D Tiles URL");
        if (!hasFile && !thumbNailUrl) {
            missing.push("Thumbnail Image (Upload a file OR paste an image URL)");
        }

        if (missing.length > 0) {
            alert("⚠️ FORM INCOMPLETE\n\nPlease fill out all fields before continuing. The following fields are missing:\n\n• " + missing.join("\n• "));
            return;
        }

        // 2. Strict Format Validations (Scenario 4)
        // Model ID Format Check
        var idRegex = /^[a-zA-Z0-9_-]+$/;
        if (!idRegex.test(mapDataID)) {
            alert("⚠️ INVALID FORMAT: Model ID can only contain letters, numbers, hyphens, and underscores (no spaces or special characters).");
            return;
        }

        // Latitude Range Format Check
        var latNum = parseFloat(yAxis);
        if (isNaN(latNum) || latNum < -90 || latNum > 90) {
            alert("⚠️ INVALID FORMAT: Latitude must be a valid number between -90 and 90.");
            return;
        }

        // Longitude Range Format Check
        var lonNum = parseFloat(xAxis);
        if (isNaN(lonNum) || lonNum < -180 || lonNum > 180) {
            alert("⚠️ INVALID FORMAT: Longitude must be a valid number between -180 and 180.");
            return;
        }

        // URL format validation regex
        var urlRegex = /^https?:\/\/[^\s/$.?#].[^\s]*$/i;

        // 3D Tiles URL Format Check
        if (!urlRegex.test(tilesetUrl)) {
            alert("⚠️ INVALID FORMAT: 3D Tiles URL must be a valid URL starting with http:// or https://");
            return;
        }

        // Thumbnail URL Format Check (if URL was provided)
        if (thumbNailUrl && !urlRegex.test(thumbNailUrl)) {
            alert("⚠️ INVALID FORMAT: Thumbnail URL must be a valid URL starting with http:// or https://");
            return;
        }

        formMessage.textContent = '';
        formMessage.className = 'ms-3';
        
        var file = thumbnailFile && thumbnailFile.files && thumbnailFile.files[0];
        
        var payload = {
          mapDataID: mapDataID,
          title: title || mapDataID,
          description: description,
          yAxis: parseFloat(yAxis),
          xAxis: parseFloat(xAxis),
          '3dTiles': tilesetUrl,
          thumbNailUrl: thumbNailUrl
        };

        if (isNaN(payload.yAxis) || isNaN(payload.xAxis)) {
          formMessage.textContent = 'Valid latitude and longitude are required.';
          formMessage.classList.add('text-danger');
          return;
        }

        submitBtn.disabled = true;

        function normalizeStr(s) { return (s || '').toString().trim(); }
        function normalizeLower(s) { return (s || '').toString().trim().toLowerCase(); }
        function normalizePure(s) { return (s || '').toString().toLowerCase().replace(/[^a-z0-9]/g, ''); }

        function checkDuplicates() {
          console.log("🔍 STARTING DUPLICATION CHECK for:", mapDataID);
          return fetch(API_BASE + '/api/map-data')
            .then(function (r) { 
                if (!r.ok) throw new Error("API responded with status " + r.status);
                return r.json(); 
            })
            .then(function (rows) {
              console.log("📊 ROWS RECEIVED FROM API:", rows.length, rows);
              rows = Array.isArray(rows) ? rows : [];
              var newId = normalizeStr(mapDataID);
              var pureNewId = normalizePure(newId);
              var newTitle = normalizeLower(title || mapDataID);
              var newLat = String(parseFloat(yAxis));
              var newLon = String(parseFloat(xAxis));
              var newTiles = normalizeStr(tilesetUrl);

              var dup = { id: false, duplicateName: '', partial: false, partialName: '', title: false, lat: false, lon: false, tiles: false };
              
              for (var i = 0; i < rows.length; i++) {
                var r0 = rows[i] || {};
                var rawId = '';
                var rid = '';
                
                // 🕵️ Find ID key case-insensitively
                for (var k in r0) {
                    if (k.toLowerCase() === 'mapdataid' || k.toLowerCase() === 'id') {
                        rawId = r0[k];
                        rid = normalizeStr(rawId).toLowerCase();
                        break;
                    }
                }
                
                if (!rid) {
                    console.log("Row " + i + ": No ID key found. Keys are:", Object.keys(r0));
                    continue;
                }

                var pureRid = normalizePure(rawId);

                var rtitle = normalizeLower(r0.title || '');
                var rlat = (r0.yAxis != null && r0.yAxis !== '') ? String(Number(r0.yAxis)) : '';
                var rlon = (r0.xAxis != null && r0.xAxis !== '') ? String(Number(r0.xAxis)) : '';
                var rtiles = normalizeStr(r0['3dTiles'] || r0.tilesetUrl || r0.tileset || '');
                
                console.log("Row " + i + ": Comparing '" + newId.toLowerCase() + "' with '" + rid + "' (Pure: '" + pureNewId + "' vs '" + pureRid + "')");

                // 🛑 EXACT DUPLICATION CHECK (Scenario 1)
                // Case-insensitive, symbol-ignoring (letters, numbers, and their order must be exactly identical)
                if (pureRid === pureNewId) {
                    console.warn("❗ EXACT DUPLICATE FOUND at Row " + i + ":", rawId);
                    dup.id = true;
                    dup.duplicateName = rawId;
                    break; // Critical duplicate, stop further checking
                } 
                // ⚠️ SIMILAR ID CHECK (Scenario 2)
                // Only part of the name matches, e.g. there is an extra character/number
                else if (pureRid.length > 3 && (pureNewId.indexOf(pureRid) !== -1 || pureRid.indexOf(pureNewId) !== -1)) {
                    console.warn("⚠️ PARTIAL MATCH FOUND at Row " + i + ":", rawId);
                    dup.partial = true;
                    dup.partialName = rawId;
                }

                if (rtitle && rtitle === newTitle) dup.title = true;
                if (rlat && rlat === newLat) dup.lat = true;
                if (rlon && rlon === newLon) dup.lon = true;
                if (rtiles && rtiles === newTiles) dup.tiles = true;
              }
              console.log("✅ CHECK FINISHED. Result:", dup);
              return dup;
            })
            .catch(function(err) {
                console.error("❌ DUPLICATION CHECK FAILED:", err);
                return { id: false, duplicateName: '', partial: false, partialName: '' }; // Allow proceed on error but log it
            });
        }

        function saveMapData(finalThumbUrl) {
          console.log("💾 INITIATING SAVE for:", mapDataID, "with thumb:", finalThumbUrl);
          var savePayload = {
            mapDataID: mapDataID,
            title: title || mapDataID,
            description: description,
            yAxis: parseFloat(yAxis),
            xAxis: parseFloat(xAxis),
            '3dTiles': tilesetUrl,
            thumbNailUrl: finalThumbUrl || ''
          };
          return fetch(API_BASE + '/api/map-data', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(savePayload)
          }).then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); });
        }

        function done(x) {
          if (x.status === 200 && x.body.success) {
            formMessage.textContent = 'Saved. The model will appear on the overview map and can be added to the showcase.';
            formMessage.className = 'ms-3 text-success';
            form.reset();
            if (thumbnailFile) thumbnailFile.value = '';
          } else {
            formMessage.textContent = x.body.message || 'Save failed.';
            formMessage.className = 'ms-3 text-danger';
          }
        }

        // Execute async validation checks, followed by manual confirmations, followed by final save
        checkDuplicates().then(function (dup) {
          // A. Duplication Check (Scenario 1)
          if (dup && dup.id) {
            alert("❌ DUPLICATION DETECTED\n\nUnable to add 3D model. A 3D model with an identical ID '" + dup.duplicateName + "' already exists in the system (ignoring casing and symbol differences).\n\nPlease choose a completely unique Model ID to continue.");
            submitBtn.disabled = false;
            return;
          }

          // B. Similar ID Warning (Scenario 2)
          if (dup && dup.partial) {
            var proceedSimilar = confirm("⚠️ SIMILAR MODEL ID DETECTED\n\nAn existing model shares a very similar ID: '" + dup.partialName + "'.\n\nPlease verify and ensure that the new 3D model you are adding is not a duplication of this existing entry.\n\nDo you want to proceed with adding this new 3D model anyway?");
            if (!proceedSimilar) {
              submitBtn.disabled = false;
              return;
            }
          }

          // C. Potential Duplicate Check (Title, Coordinates, 3D Tiles URL)
          if (dup && (dup.title || (dup.lat && dup.lon) || dup.tiles)) {
            var isPlaceholderTiles = tilesetUrl.indexOf('example.com') !== -1 || tilesetUrl.indexOf('localhost') !== -1;
            if (dup.title || (dup.lat && dup.lon) || (dup.tiles && !isPlaceholderTiles)) {
              var dupReasons = [];
              if (dup.title) dupReasons.push("Title ('" + title + "')");
              if (dup.lat && dup.lon) dupReasons.push("Coordinates (" + yAxis + ", " + xAxis + ")");
              if (dup.tiles && !isPlaceholderTiles) dupReasons.push("3D Tiles URL ('" + tilesetUrl + "')");
              
              if (dupReasons.length > 0) {
                var proceedPotential = confirm("⚠️ POTENTIAL DUPLICATE DETECTED\n\nThis 3D model shares matching details with an existing entry:\n• " + dupReasons.join("\n• ") + "\n\nPlease ensure this is not a duplicate. Do you want to proceed with saving anyway?");
                if (!proceedPotential) {
                  submitBtn.disabled = false;
                  return;
                }
              }
            }
          }

          // D. Sabah Region Boundary Check (Soft warning)
          // Sabah lies roughly between Latitude: 4.0 to 7.5 and Longitude: 114.0 to 120.0
          if (latNum < 4.0 || latNum > 7.5 || lonNum < 114.0 || lonNum > 120.0) {
            var proceedBoundary = confirm("⚠️ REGIONAL BOUNDARY WARNING\n\nThe coordinates entered (Lat: " + yAxis + ", Lon: " + xAxis + ") lie outside the typical boundaries of Sabah, Malaysia (Lat: 4.0 to 7.5, Lon: 114.0 to 120.0).\n\nThis may cause the pin to display off-map or in an incorrect geographical area.\n\nDo you want to proceed with these coordinates?");
            if (!proceedBoundary) {
              submitBtn.disabled = false;
              return;
            }
          }

          // E. Save Warning Reminder Confirmation (Absolute final check)
          // "this message that shows here is for when every details are correct and no duplication at all before admin click save 3D model button"
          var confirmSave = confirm("⚠️ WARNING REMINDER\n\nPlease ensure all entered details are verified and correct before saving.\n\nAre you sure you want to save this 3D model?");
          if (!confirmSave) {
            submitBtn.disabled = false;
            return;
          }

          // F. Save Execution
          if (file) {
            var fd = new FormData();
            fd.append('thumbnail', file);
            fd.append('mapDataID', mapDataID);

            fetch(API_BASE + '/api/admin/upload-map-thumbnail', { method: 'POST', body: fd })
              .then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
              .then(function (up) {
                if (up.status === 200 && up.body.success && up.body.url) {
                  var fullUrl = (up.body.url.indexOf('http') === 0) ? up.body.url : (API_BASE + up.body.url);
                  return saveMapData(fullUrl).then(done);
                }
                formMessage.textContent = up.body.message || 'Thumbnail upload failed.';
                formMessage.className = 'ms-3 text-danger';
              })
              .catch(function (err) {
                formMessage.textContent = 'Thumbnail upload failed: ' + (err.message || 'check server');
                formMessage.className = 'ms-3 text-danger';
              })
              .finally(function () { submitBtn.disabled = false; });
          } else {
            saveMapData(thumbNailUrl)
              .then(done)
              .catch(function (err) {
                formMessage.textContent = 'Request failed: ' + (err.message || 'check server');
                formMessage.className = 'ms-3 text-danger';
              })
              .finally(function () { submitBtn.disabled = false; });
          }
        }).catch(function (err) {
          console.error("❌ Pre-save flow failed:", err);
          submitBtn.disabled = false;
        });
      });
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
