<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="<?php echo e(asset('assets')); ?>/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Create Project via SFTP | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/core.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/demo.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/css/client-responsive.css">
  <link rel="stylesheet" href="<?php echo e(asset('assets')); ?>/vendor/css/pages/front-page.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    #map {
      height: 250px;
      width: 100%;
      border-radius: 12px;
      border: 1px solid #d9dee3;
      margin-top: 0.5rem;
      z-index: 1 !important;
    }
  </style>

  <script src="<?php echo e(asset('assets')); ?>/vendor/js/helpers.js"></script>
  <script src="<?php echo e(asset('assets')); ?>/js/front-config.js"></script>
  <script>
    (function () {
      window.userRole = '<?php echo e(Auth::user()->role); ?>';
    })();
  </script>
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    .split-layout {
      height: 100vh;
      width: 100vw;
      position: relative;
    }

    .right-panel {
      position: absolute;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: 1;
    }

    .left-panel {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100%;
      max-width: 800px;
      max-height: calc(100vh - 48px);
      display: flex;
      flex-direction: column;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      z-index: 10;
      border-radius: 16px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15), 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.5);
      overflow: hidden;
    }

    .left-header {
      padding: 1.5rem 1.75rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      background: rgba(255, 255, 255, 0.5);
    }

    .left-content {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem 1.75rem;
      scrollbar-width: thin;
      scrollbar-color: #d1d5db transparent;
    }

    .left-footer {
      padding: 1.25rem 1.75rem;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      background: rgba(255, 255, 255, 0.5);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .form-section-title {
      font-size: 0.8rem;
      font-weight: 700;
      color: #697a8d;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 1.25rem;
      margin-top: 2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-section-title::after {
      content: '';
      flex: 1;
      height: 1px;
      background: rgba(0, 0, 0, 0.05);
    }

    .sftp-fields-card {
      background: #f8f9fa;
      border: 1px solid #ebedf2;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    /* Premium Success View Styles */
    .success-icon-wrap {
      width: 90px;
      height: 90px;
      background: linear-gradient(135deg, #2ed573 0%, #7bed9f 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      box-shadow: 0 10px 20px -5px rgba(46, 213, 115, 0.5);
      animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    @keyframes popIn {
      0% { transform: scale(0.5); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }

    .success-icon-wrap i {
      color: #fff;
      font-size: 3.5rem;
    }

    .info-badge {
      display: inline-flex;
      align-items: center;
      padding: 6px 12px;
      border-radius: 20px;
      background: #f0f2f5;
      font-size: 0.85rem;
      font-weight: 600;
      color: #566a7f;
      margin-bottom: 10px;
    }

    .premium-credential-card {
      background: #ffffff;
      border: 1px solid #e1e4e8;
      border-radius: 16px;
      padding: 1.75rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.03);
      position: relative;
      overflow: hidden;
    }

    .cred-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #a1acb8;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .cred-value {
      font-size: 1.1rem;
      font-weight: 700;
      color: #32475c;
    }
    
    .cred-value-primary {
      color: #696cff;
      font-size: 1.25rem;
      word-break: break-all;
    }

    .target-path-wrapper {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      border: 1px dashed #d9dee3;
      margin-top: 1rem;
    }

    /* 🌙 DARK MODE OPTIMIZATIONS (v280) */
    [data-bs-theme="dark"] .left-panel { background: rgba(26, 26, 46, 0.95); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .left-header, [data-bs-theme="dark"] .left-footer { background: rgba(26, 26, 46, 0.6); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .form-section-title { color: #e1e4e8; }
    [data-bs-theme="dark"] .form-section-title::after { background: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .form-label { color: #d1d5db; }
    [data-bs-theme="dark"] .text-muted { color: #a1acb8 !important; }
    [data-bs-theme="dark"] .sftp-fields-card { background: rgba(30, 41, 59, 0.5); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .premium-credential-card { background: #1a1a2e; border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .cred-label { color: #8a94a6; }
    [data-bs-theme="dark"] .cred-value { color: #e1e4e8; }
    [data-bs-theme="dark"] .target-path-wrapper { background: rgba(0, 0, 0, 0.2); border-color: rgba(255, 255, 255, 0.2); }
    [data-bs-theme="dark"] #map { border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] h3, [data-bs-theme="dark"] h4 { color: #e1e4e8 !important; }
  </style>
</head>

<body>
  <div class="split-layout">
    <div class="left-panel">
      <div class="left-header">
        <h4 class="mb-1 fw-bold">Create Project using SFTP</h4>
        <p class="text-muted small mb-0">Fill in your project details and the system will securely provision a target folder for you. Use the credentials below to upload your data later.</p>
      </div>
      <div class="left-content">
        <div id="successView" style="display: none;" class="text-center pt-2">
          <div class="mb-4">
            <div class="success-icon-wrap">
              <i class="bx bx-check"></i>
            </div>
          </div>
          <h3 class="fw-bold mb-3" style="color: #2e3b4e; font-size: 1.75rem;">Project Provisioned</h3>
          <p class="text-muted mb-4 px-2" style="font-size: 0.95rem; line-height: 1.6;">Your private data folder is ready on the server. Connect via any SFTP client to start uploading your flights securely.</p>
          
          <div class="premium-credential-card text-start mb-4">
            <div class="row g-4">
              <div class="col-md-6">
                <div class="cred-label">Host / IP Address</div>
                <div class="cred-value" id="resHost">-</div>
              </div>
              <div class="col-md-6">
                <div class="cred-label">Port</div>
                <div class="cred-value" id="resPort">-</div>
              </div>
              <div class="col-md-6">
                <div class="cred-label">Username</div>
                <div class="cred-value" id="resUser">-</div>
              </div>
              <div class="col-md-6">
                <div class="cred-label">Password</div>
                <div class="cred-value" id="resPass">-</div>
              </div>
              <div class="col-12 mt-2">
                <div class="cred-label">WinSCP Path (Copy this into WinSCP)</div>
                <div class="d-flex align-items-center">
                  <div class="cred-value cred-value-primary flex-grow-1" id="resClientPath" style="word-break: break-all;">-</div>
                  <button type="button" class="btn btn-sm btn-outline-primary ms-2 flex-shrink-0" onclick="copyToClipboard('resClientPath', this)" title="Copy Path">
                    <i class="bx bx-copy"></i>
                  </button>
                </div>
              </div>
              <div class="col-12 mt-1">
                <div class="cred-label text-muted">Full Server Path (For Reference)</div>
                <div class="cred-value small text-muted" id="resAbsolutePath" style="word-break: break-all;">-</div>
              </div>
            </div>
          </div>
          
          <div class="d-inline-flex align-items-center text-danger bg-label-danger py-2 px-3 rounded-pill" style="font-size: 0.85rem; font-weight: 600;">
            <i class="bx bx-shield-quarter me-2"></i> Save your credentials, this is the only time they are shown!
          </div>
        </div>

        <form id="sftpForm" novalidate>
          <div class="form-section-title mt-0">Project Details</div>
          <div class="mb-3">
            <label class="form-label" for="projectTitle">Project Title <span class="text-danger">*</span></label>
            <input type="text" id="projectTitle" class="form-control" placeholder="e.g., Riverside Survey A" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="projectDescription">Project Description</label>
            <textarea id="projectDescription" class="form-control" rows="2" placeholder="Describe the survey area or purpose..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label" for="lensType">Lens Type <span class="text-danger">*</span></label>
            <select class="form-select" id="lensType" name="lensType" required onchange="toggleSftpCameraDetails()">
              <option value="single">Single-Lens</option>
              <option value="multiple">Multi-Lens</option>
            </select>
          </div>
          <div class="mb-3" id="cameraDetailsDiv" style="display: none;">
            <label class="form-label" for="cameraModels">Camera Models</label>
            <input type="text" id="cameraModels" class="form-control" placeholder="RGB, Thermal...">
          </div>
          <div class="mb-3">
            <label class="form-label" for="imageMetadata">Metadata Format <span class="text-danger">*</span></label>
            <select class="form-select" id="imageMetadata" name="imageMetadata" required>
              <option value="EXIF (embedded)">EXIF (embedded)</option>
              <option value="POS file">POS file</option>
              <option value="EXIF & POS">EXIF & POS</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="captureDate">Capture Date</label>
            <input type="date" id="captureDate" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
            <select class="form-select" id="category" name="category" required
              onchange="toggleOtherInput('category', 'categoryOtherDiv', 'categoryOther')">
              <option value="">-- Select a category --</option>
              <option value="Agricultural">Agricultural</option>
              <option value="Coastal">Coastal Area</option>
              <option value="Environmental">Environmental</option>
              <option value="Infrastructure">Infrastructure</option>
              <option value="Urban">Urban Development</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="mb-3 d-none" id="categoryOtherDiv">
            <input type="text" class="form-control" id="categoryOther" name="categoryOther"
              placeholder="Enter custom category">
          </div>
          <div class="mb-3">
            <label class="form-label d-block">Output Category <span class="text-danger">*</span></label>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="out3DTiles" value="3D Tiles" checked onclick="return false;" style="pointer-events: none; opacity: 0.7;">
              <label class="form-check-label" for="out3DTiles" style="opacity: 0.7; cursor: not-allowed;">3D Tiles</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="outOSGB" value="OSGB" checked onclick="return false;" style="pointer-events: none; opacity: 0.7;">
              <label class="form-check-label" for="outOSGB" style="opacity: 0.7; cursor: not-allowed;">OSGB</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="outDSM" value="DSM">
              <label class="form-check-label" for="outDSM">DSM</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="out3DGS" value="3DGS">
              <label class="form-check-label" for="out3DGS">3DGS</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="outOrthophoto" value="Orthophoto">
              <label class="form-check-label" for="outOrthophoto">Orthophoto</label>
            </div>
          </div>
          <div class="form-section-title">Project Location (Optional)</div>
          <p class="text-muted small mb-2">Click on the map to set where this survey was captured. If skipped, the project won't show a map pin.</p>
          <div id="map"></div>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <label class="form-label small">Latitude</label>
              <input type="number" step="any" id="latitude" class="form-control" placeholder="e.g. 6.6102">
            </div>
            <div class="col-6">
              <label class="form-label small">Longitude</label>
              <input type="number" step="any" id="longitude" class="form-control" placeholder="e.g. 116.9428">
            </div>
          </div>
        </form>
      </div>
      <div class="left-footer" id="formFooter">
        <button type="button" class="btn btn-secondary text-white fw-medium border-0 px-4" style="background:#8b9eb0;" onclick="window.location.href='<?php echo e(route('create_project')); ?>'">Cancel</button>
        <button type="submit" form="sftpForm" id="btnSubmitForm" class="btn btn-primary px-5">Submit Project</button>
      </div>
      <div class="left-footer" id="successFooter" style="display: none;">
        <div class="w-100 text-center">
          <button type="button" class="btn btn-primary w-100" onclick="window.location.href='<?php echo e(route('my_uploads')); ?>'">Done Workspace</button>
        </div>
      </div>
    </div>
    <div class="right-panel">
      <div id="bgMap" style="width: 100%; height: 100%;"></div>
    </div>
  </div>

  <script src="<?php echo e(asset('assets/vendor/js/bootstrap.js')); ?>"></script>
  <script>
    const userRole = '<?php echo e(Auth::user()->role); ?>';
    const isAdmin = (userRole === 'admin' || userRole === 'superadmin');

    // 🌍 BACKGROUND MAP (v128)
    const bgMap = L.map('bgMap', { zoomControl: false, attributionControl: false }).setView([4.2105, 101.9758], 6);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(bgMap);

    // INITIALIZE FORM MAP
    try {
      const map = L.map('map').setView([4.2105, 101.9758], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(map);

      let marker;
      
      const updateMarker = (lat, lng, centerMap = true) => {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        if (centerMap) map.setView([lat, lng], 14);
      };

      map.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng, false);
        document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
      });

      // 🎯 MANUAL COORDINATE INPUT (v130)
      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');

      [latInput, lngInput].forEach(input => {
        input.addEventListener('input', () => {
          const lat = parseFloat(latInput.value);
          const lng = parseFloat(lngInput.value);
          if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateMarker(lat, lng, true);
          }
        });
      });
    } catch (err) {
      console.error("Map initialization failed:", err);
      document.getElementById('map').innerHTML = '<div class="p-3 text-center text-muted small">Map failed to load. You can still enter coordinates manually or skip this step.</div>';
    }

    // Project ID Generator with User Prefix to prevent collisions
    const projectTitleInput = document.getElementById('projectTitle');
    const generatedIdInput = document.createElement('input');
    generatedIdInput.type = 'hidden';
    generatedIdInput.id = 'projectID';
    document.getElementById('sftpForm').appendChild(generatedIdInput);

    projectTitleInput.addEventListener('input', function() {
      const title = this.value;
      if (!title) {
        generatedIdInput.value = '';
        return;
      }
      
      // Prefix with slugified user name
      const userPrefix = '<?php echo e(Str::slug(Auth::user()->name)); ?>';
      let slug = title.toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
      
      if (slug.length > 0) {
        const randomChars = Math.random().toString(36).substring(2, 6);
        // Guarantee unique path per user
        slug = userPrefix + '-' + slug + '-' + randomChars;
      }
      generatedIdInput.value = slug;
    });

    function toggleSftpCameraDetails() {
      const val = document.getElementById('lensType').value;
      document.getElementById('cameraDetailsDiv').style.display = (val === 'multiple' ? 'block' : 'none');
    }

    document.getElementById('sftpForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // 🛡️ STRICT COLUMN VALIDATION (v306)
      // Ensures ALL columns are filled as requested by the user
      const title = projectTitleInput.value.trim();
      const desc = document.getElementById('projectDescription').value.trim();
      const lens = document.getElementById('lensType').value;
      const cam = document.getElementById('cameraModels').value.trim();
      const meta = document.getElementById('imageMetadata').value;
      const date = document.getElementById('captureDate').value;
      const cat = document.getElementById('category').value;
      const otherCat = document.getElementById('categoryOther').value.trim();
      const lat = document.getElementById('latitude').value;
      const lng = document.getElementById('longitude').value;

      let missing = [];
      if (!title) missing.push("Project Title");
      if (!desc) missing.push("Project Description");
      if (!lens) missing.push("Lens Type");
      if (lens === 'multiple' && !cam) missing.push("Camera Models");
      if (!meta) missing.push("Metadata Format");
      if (!date) missing.push("Capture Date");
      if (!cat) missing.push("Category");
      if (cat === 'Other' && !otherCat) missing.push("Custom Category Specification");

      if (missing.length > 0) {
          return alert("Form Incomplete! The following mandatory fields must be filled:\n\n• " + missing.join("\n• "));
      }

      const btn = document.getElementById('btnSubmitForm');
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Provisioning...';

      let categoryVal = document.getElementById('category').value;
      if (categoryVal === 'Other') {
        categoryVal = document.getElementById('categoryOther').value;
      }

      const outputCheckboxes = document.querySelectorAll('input[name="outputCategory"]:checked');
      const outputs = Array.from(outputCheckboxes).map(cb => cb.value);
      
      const isMulti = document.getElementById('lensType').value === 'multiple';
      const customCam = document.getElementById('cameraModels').value;
      const cameraLine = isMulti ? ("Multi-Lens" + (customCam ? (": " + customCam) : "")) : "Single-Lens";

      const payload = {
        projectTitle: projectTitleInput.value,
        projectID: generatedIdInput.value,
        projectDescription: document.getElementById('projectDescription').value,
        cameraConfiguration: cameraLine,
        category: categoryVal,
        outputCategory: outputs,
        latitude: document.getElementById('latitude').value || null,
        longitude: document.getElementById('longitude').value || null,
        imageMetadata: document.getElementById('imageMetadata').value,
        captureDate: document.getElementById('captureDate').value || new Date().toISOString().split('T')[0]
      };

      try {
        const res = await fetch('/api/upload/sftp-project', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success && data.sftpDetails) {
          document.getElementById('sftpForm').style.display = 'none';
          document.getElementById('formFooter').style.display = 'none';
          
          document.getElementById('successView').style.display = 'block';
          document.getElementById('successFooter').style.display = 'flex';
          
          // Show basic connection info
          document.getElementById('resHost').innerText = data.sftpDetails.host || '<?php echo e(config('filesystems.disks.sftp_delivery.host', '172.21.107.151')); ?>';
          document.getElementById('resPort').innerText = data.sftpDetails.port || '<?php echo e(env('SFTP_USER_PORT', 2223)); ?>';
          
          // 🚀 SMART-PATH SYNC (v118)
          document.getElementById('resClientPath').innerText = data.sftpDetails.clientPath || data.sftpDetails.remotePath;
          document.getElementById('resAbsolutePath').innerText = data.sftpDetails.absolutePath || data.sftpDetails.remotePath;

          // Populate individual credentials (Username/Password matching the user)
          document.getElementById('resUser').innerText = data.sftpDetails.username || 'Not Assigned';
          document.getElementById('resPass').innerText = data.sftpDetails.password || 'Contact Admin';

          // Ensure connection instruction is clear
          const instruction = document.createElement('div');
          instruction.className = 'alert alert-info mt-3 text-start';
          instruction.innerHTML = '<i class="bx bx-info-circle me-2"></i> <strong>WinSCP Tip:</strong> If you get a "Permission Denied" while dragging files, go to WinSCP <strong>Options > Preferences > Transfer > Default > Edit</strong> and <strong>UNCHECK "Preserve timestamp"</strong>. Then try again.';
          document.getElementById('successView').appendChild(instruction);
          
        } else {
          alert('Error: ' + (data.message || 'Validation failed'));
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      } catch (err) {
        console.error(err);
        alert('Server error.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    });

    function toggleOtherInput(selectId, divId, inputId, targetValue = 'Other') {
      const selectElem = document.getElementById(selectId);
      const otherDiv = document.getElementById(divId);
      const otherInput = document.getElementById(inputId);
      if (!selectElem || !otherDiv || !otherInput) return;

      if (selectElem.value === targetValue) {
        otherDiv.classList.remove('d-none');
        otherInput.required = true;
      } else {
        otherDiv.classList.add('d-none');
        otherInput.required = false;
        otherInput.value = '';
      }
    }
    function copyToClipboard(elementId, btn) {
      const text = document.getElementById(elementId).innerText;
      navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'bx bx-check';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        setTimeout(() => {
          icon.className = originalClass;
          btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
      });
    }
  </script>
</body>

</html>
<?php /**PATH C:\Users\User\.antigravity\Projects\DataPortalV2\resources\views/portal/upload-sftp.blade.php ENDPATH**/ ?>