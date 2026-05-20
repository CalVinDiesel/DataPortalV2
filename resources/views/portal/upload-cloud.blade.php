<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Create Project via Cloud Storage | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/client-responsive.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">

  <!-- Leaflet CSS & JS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/leaflet/leaflet.css') }}" />
  <script src="{{ asset('assets/vendor/leaflet/leaflet.js') }}"></script>


  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>
  <script>
    (function () {
      window.userRole = '{{ Auth::user()->role }}';
    })();
  </script>
  <style>
    body { margin: 0; padding: 0; overflow: hidden; }
    .split-layout { height: 100vh; width: 100vw; position: relative; }
    .right-panel { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; }
    #map {
      height: 250px;
      width: 100%;
      border-radius: 12px;
      border: 1px solid #d9dee3;
      margin-top: 0.5rem;
      z-index: 1 !important;
    }
    .left-panel {
      position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
      width: 100%; max-width: 800px; max-height: calc(100vh - 48px);
      display: flex; flex-direction: column; background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 10;
      border-radius: 16px; box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.5); overflow: hidden;
    }
    .left-header { padding: 1.5rem 1.75rem; border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .left-content { flex: 1; overflow-y: auto; padding: 1.5rem 1.75rem; }
    .left-footer { padding: 1.25rem 1.75rem; border-top: 1px solid rgba(0, 0, 0, 0.05); display: flex; justify-content: space-between; align-items: center; }
    
    .provider-selector {
      display: flex; gap: 1rem; margin-bottom: 2rem;
    }
    .provider-btn {
      flex: 1; padding: 1.25rem; border: 2px solid #e2e8f0; border-radius: 12px;
      display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
      cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: white;
    }
    .provider-btn img { height: 42px; width: auto; transition: transform 0.3s ease; }
    .provider-btn.active { 
      border-color: #696cff; 
      background: rgba(105, 108, 255, 0.05); 
      box-shadow: 0 4px 12px rgba(105, 108, 255, 0.12);
    }
    .provider-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }
    .provider-btn:hover img { transform: scale(1.1); }
    .provider-btn:hover:not(.active) { border-color: #cbd5e1; }

    .form-section-title {
      font-size: 0.8rem; font-weight: 700; color: #697a8d; text-transform: uppercase;
      letter-spacing: 0.05em; margin-bottom: 1.25rem; margin-top: 2rem; display: flex; align-items: center; gap: 0.5rem;
    }
    .form-section-title::after { content: ''; flex: 1; height: 1px; background: rgba(0, 0, 0, 0.05); }

    .success-icon-wrap {
      width: 90px; height: 90px; background: linear-gradient(135deg, #2ed573 0%, #7bed9f 100%);
      border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;
      box-shadow: 0 10px 20px -5px rgba(46, 213, 115, 0.5); animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    @keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

    /* 🌙 DARK MODE OPTIMIZATIONS (v280) */
    [data-bs-theme="dark"] .left-panel { background: rgba(26, 26, 46, 0.95); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .left-header, [data-bs-theme="dark"] .left-footer { background: rgba(26, 26, 46, 0.6); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .provider-btn { background: rgba(30, 41, 59, 0.5); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .provider-btn.active { background: rgba(105, 108, 255, 0.1); border-color: #696cff; }
    [data-bs-theme="dark"] .provider-btn:hover:not(.active) { border-color: rgba(255, 255, 255, 0.3); background: rgba(45, 55, 72, 0.6); }
    [data-bs-theme="dark"] .provider-btn span { color: #e1e4e8; }
    [data-bs-theme="dark"] .form-section-title { color: #e1e4e8; }
    [data-bs-theme="dark"] .form-section-title::after { background: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .form-label { color: #d1d5db; }
    [data-bs-theme="dark"] .text-muted { color: #a1acb8 !important; }
    [data-bs-theme="dark"] h3, [data-bs-theme="dark"] h4 { color: #e1e4e8 !important; }
    [data-bs-theme="dark"] #map { border-color: rgba(255, 255, 255, 0.1); }
  </style>
</head>

<body>
  <div class="split-layout">
    <div class="right-panel">
      <div id="bgMap" style="height: 100%; width: 100%; opacity: 0.3;"></div>
    </div>
    <div class="left-panel">
      <div class="left-header">
        <h4 class="mb-1 fw-bold">Create Project via Cloud Storage</h4>
        <p class="text-muted small mb-0">Select your cloud provider and provide a shared link to your flight dataset.</p>
      </div>
      <div class="left-content">
        <div id="successView" style="display: none;" class="text-center pt-5 pb-5">
          <div class="mb-4">
            <div class="success-icon-wrap"><i class="bx bx-check" style="color:white; font-size: 3.5rem;"></i></div>
          </div>
          <h3 class="fw-bold mb-3">Project Created!</h3>
          <p class="text-muted mb-4 px-5">Your cloud storage project has been successfully submitted. We will verify the data and begin processing shortly.</p>
          <div class="d-inline-flex align-items-center text-success bg-label-success py-2 px-3 rounded-pill" style="font-size: 0.85rem; font-weight: 600;">
            <i class="bx bx-shield-check me-2"></i> Our team will notify you once processing starts.
          </div>
        </div>

        <form id="cloudForm" novalidate>
          <div class="form-section-title mt-0">Select Provider</div>
          <div class="provider-selector">
            <div class="provider-btn active" data-provider="google_drive" id="btnGoogle">
              <img src="https://img.icons8.com/color/512/google-drive--v2.png" alt="Google Drive">
              <span class="small fw-bold">Google Drive</span>
            </div>
            <div class="provider-btn" data-provider="onedrive" id="btnOneDrive">
              <img src="https://img.icons8.com/color/512/microsoft-onedrive-2019.png" alt="OneDrive">
              <span class="small fw-bold">OneDrive</span>
            </div>
          </div>

          <div id="gdriveSection">
            <div class="form-section-title mt-0">Google Drive Details</div>
            <div class="mb-3">
              <label class="form-label" for="googleDriveLink">Public Shared Link <span class="text-danger">*</span></label>
              <input type="url" id="googleDriveLink" name="googleDriveLink" class="form-control" placeholder="https://drive.google.com/drive/folders/..." required>
              <div class="form-text mt-1"><i class="bx bx-info-circle"></i> Must be set to <strong>"Anyone with the link"</strong></div>
            </div>
          </div>

          <div id="onedriveSection" style="display: none;">
            <div class="form-section-title mt-0">OneDrive File Link</div>
            <div class="mb-3">
              <label class="form-label" for="onedriveLink">Shared ZIP File Link <span class="text-danger">*</span></label>
              <input type="url" id="onedriveLink" name="onedriveLink" class="form-control" placeholder="https://1drv.ms/u/s!..." required>
              <input type="hidden" id="onedriveItemId">
              <input type="hidden" id="onedriveDriveId">
              <div class="form-text mt-1 text-primary"><i class="bx bx-bolt-circle"></i> <strong>Tip:</strong> Sharing a direct ZIP file link is up to 3x faster than a folder link.</div>
              <div class="form-text mt-1"><i class="bx bx-info-circle"></i> Ensure the ZIP file is shared with <strong>"Anyone with the link"</strong></div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label" for="onedriveSize">Total Size (Bytes) <span class="text-danger">*</span></label>
                <input type="number" id="onedriveSize" name="onedriveSize" class="form-control" placeholder="e.g. 5368709120" min="1" required>
                <div class="form-text mt-1">Please enter the exact size in <strong>Bytes</strong>.</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label" for="onedriveCount">Photo Count <span class="text-danger">*</span></label>
                <input type="number" id="onedriveCount" name="onedriveCount" class="form-control" placeholder="e.g. 1200" min="1" required>
              </div>
            </div>
          </div>

          <div class="form-section-title">Project Details</div>
          <div class="mb-3">
            <label class="form-label" for="projectTitle">Project Title <span class="text-danger">*</span></label>
            <input type="text" id="projectTitle" name="projectTitle" class="form-control" placeholder="e.g., Riverside Survey A" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="projectDescription">Project Description <span class="text-danger">*</span></label>
            <textarea id="projectDescription" name="projectDescription" class="form-control" rows="2" placeholder="Briefly describe the dataset..." required></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Camera Configuration</label>
              <select class="form-select" id="cameraConfiguration" required onchange="toggleCameraDetails()">
                <option value="single">Single-Lens</option>
                <option value="multiple">Multi-Lens</option>
              </select>
            </div>
            <div class="col-md-12 mb-3" id="cameraDetailsDiv" style="display: none;">
              <label class="form-label" for="cameraModels">Camera Models</label>
              <input type="text" id="cameraModels" class="form-control" placeholder="RGB, Thermal...">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
              <select class="form-select" id="category" required onchange="toggleOtherInput('category', 'categoryOtherDiv', 'categoryOther')">
                <option value="">-- Select --</option>
                <option value="Agricultural">Agricultural</option>
                <option value="Coastal">Coastal Area</option>
                <option value="Environmental">Environmental</option>
                <option value="Infrastructure">Infrastructure</option>
                <option value="Urban">Urban Development</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>
          <div class="mb-3 d-none" id="categoryOtherDiv">
            <input type="text" class="form-control" id="categoryOther" placeholder="Enter custom category">
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Output Category <span class="text-danger">*</span></label>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" value="3D Tiles" checked disabled>
              <label class="form-check-label">3D Tiles</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" value="OSGB" checked disabled>
              <label class="form-check-label">OSGB</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" value="DSM">
              <label class="form-check-label">DSM</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" value="3DGS">
              <label class="form-check-label">3DGS</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" value="Orthophoto">
              <label class="form-check-label">Orthophoto</label>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="imageMetadata">Image Metadata <span class="text-danger">*</span></label>
              <select class="form-select" id="imageMetadata" required>
                <option value="EXIF (embedded)">EXIF (embedded)</option>
                <option value="POS file">POS file</option>
                <option value="EXIF & POS">EXIF & POS</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="captureDate">Capture Date</label>
              <input type="date" id="captureDate" class="form-control">
            </div>
          </div>

          <div class="form-section-title">Project Location (Optional)</div>
          <p class="text-muted small mb-2">Click on the map to set where this survey was captured. If skipped, the project won't show a map pin.</p>
          <div id="map"></div>
          <div class="row g-2 mt-2">
            <div class="col-6">
              <label class="form-label small">Latitude</label>
              <input type="number" step="any" id="latitude" class="form-control" placeholder="e.g. 5.9804">
            </div>
            <div class="col-6">
              <label class="form-label small">Longitude</label>
              <input type="number" step="any" id="longitude" class="form-control" placeholder="e.g. 116.0735">
            </div>
          </div>
        </form>
      </div>
      <div class="left-footer" id="formFooter">
        <button type="button" class="btn btn-secondary text-white fw-medium border-0 px-4" style="background:#8b9eb0;" onclick="window.location.href='{{ route('create_project') }}'">Cancel</button>
        <button type="submit" form="cloudForm" id="btnSubmitForm" class="btn btn-primary px-5">Submit Project</button>
      </div>
      <div class="left-footer" id="successFooter" style="display: none;">
        <div class="w-100 text-center">
          <button type="button" class="btn btn-primary w-100" onclick="window.location.href='{{ route('my_uploads') }}'">Go to My Projects</button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script>
    // Theme-compatible background map
    const bgMap = L.map('bgMap', { zoomControl: false, attributionControl: false }).setView([5.9804, 116.0735], 11);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(bgMap);

    // INITIALIZE FORM MAP (v155)
    try {
      const pickerMap = L.map('map').setView([5.9804, 116.0735], 11);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(pickerMap);

      let marker;

      const updateMarker = (lat, lng, centerMap = true) => {
        if (marker) pickerMap.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(pickerMap);
        if (centerMap) pickerMap.setView([lat, lng], 14);
      };

      pickerMap.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng, false);
        document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
      });

      // 🎯 MANUAL COORDINATE INPUT (v155)
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
      document.getElementById('map').innerHTML = '<div class="p-3 text-center text-muted small">Map failed to load.</div>';
    }

    let currentProvider = 'google_drive';

    // 🚀 PROVIDER SYNC (v282): Ensure required attributes match the active tab on load
    function updateRequiredFields() {
        if (currentProvider === 'google_drive') {
            document.getElementById('gdriveSection').style.display = 'block';
            document.getElementById('onedriveSection').style.display = 'none';
            document.getElementById('googleDriveLink').required = true;
            document.getElementById('onedriveLink').required = false;
            document.getElementById('onedriveSize').required = false;
            document.getElementById('onedriveCount').required = false;
        } else {
            document.getElementById('gdriveSection').style.display = 'none';
            document.getElementById('onedriveSection').style.display = 'block';
            document.getElementById('googleDriveLink').required = false;
            document.getElementById('onedriveLink').required = true;
            document.getElementById('onedriveSize').required = true;
            document.getElementById('onedriveCount').required = true;
        }
    }

    // Provider Toggle Logic
    document.querySelectorAll('.provider-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.provider-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentProvider = this.dataset.provider;
        updateRequiredFields();
      });
    });

    // Run once on load to initialize
    updateRequiredFields();

    // 🚀 SIMPLIFIED ONEDRIVE (v152): Manual Link Entry (No Login Required for Client)
    // The "Pick Folder" button and complex SDK initialization have been removed to match the GDrive experience.

    function toggleCameraDetails() {
      const val = document.getElementById('cameraConfiguration').value;
      document.getElementById('cameraDetailsDiv').style.display = (val === 'multiple' ? 'block' : 'none');
    }

    function toggleOtherInput(selectId, divId, inputId, targetValue = 'Other') {
      const selectElem = document.getElementById(selectId);
      const otherDiv = document.getElementById(divId);
      if (selectElem.value === targetValue) {
        otherDiv.classList.remove('d-none');
      } else {
        otherDiv.classList.add('d-none');
      }
    }

    document.getElementById('cloudForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // 🛡️ STRICT COLUMN VALIDATION (v305)
      const title = document.getElementById('projectTitle').value.trim();
      const desc = document.getElementById('projectDescription').value.trim();
      const cat = document.getElementById('category').value;
      const otherCat = document.getElementById('categoryOther').value.trim();
      const date = document.getElementById('captureDate').value;
      
      let missing = [];
      if (!title) missing.push("Project Title");
      if (!desc) missing.push("Project Description");
      if (!cat) missing.push("Category");
      if (cat === 'Other' && !otherCat) missing.push("Custom Category Specification");
      if (!date) missing.push("Capture Date");
      
      const isMulti = document.getElementById('cameraConfiguration').value === 'multiple';
      if (isMulti && !document.getElementById('cameraModels').value.trim()) {
          missing.push("Camera Models");
      }

      const gLink = document.getElementById('googleDriveLink').value.trim();
      const oLink = document.getElementById('onedriveLink').value.trim();
      const oSize = document.getElementById('onedriveSize').value;
      const oCount = document.getElementById('onedriveCount').value;

      if (currentProvider === 'google_drive') {
          if (!gLink) missing.push("Google Drive Shared Link");
      } else if (currentProvider === 'onedrive') {
          if (!oLink) missing.push("OneDrive Shared Link");
          if (!oSize) missing.push("Total Size (Bytes)");
          if (!oCount) missing.push("Photo Count");
      }

      if (missing.length > 0) {
          return alert("Form Incomplete! The following required fields are missing:\n\n• " + missing.join("\n• "));
      }

      const btn = document.getElementById('btnSubmitForm');
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

      let categoryVal = document.getElementById('category').value;
      if (categoryVal === 'Other') categoryVal = document.getElementById('categoryOther').value;

      const outputs = ["3D Tiles", "OSGB"];
      document.querySelectorAll('input[name="outputCategory"]:checked').forEach(cb => {
        if (!outputs.includes(cb.value)) {
            outputs.push(cb.value);
        }
      });

      // Reuse isMulti defined in validation section above
      const customCam = document.getElementById('cameraModels').value;
      const cameraLine = isMulti ? ("Multi-Lens" + (customCam ? (": " + customCam) : "")) : "Single-Lens";

      const payload = {
        projectTitle: title,
        projectDescription: desc,
        cameraConfiguration: cameraLine,
        category: categoryVal,
        outputCategory: outputs,
        imageMetadata: document.getElementById('imageMetadata').value,
        captureDate: date,
        latitude: document.getElementById('latitude').value || null,
        longitude: document.getElementById('longitude').value || null,
        provider: currentProvider
      };

      if (currentProvider === 'google_drive') {
        payload.googleDriveLink = gLink;
      } else {
        payload.onedriveLink = oLink;
        payload.onedriveSize = oSize;
        payload.onedriveCount = oCount;
        payload.onedriveItemId = null; // Backend will resolve this from the link
        payload.onedriveDriveId = null;
      }

      try {
        const endpoint = (currentProvider === 'google_drive') ? '{{ route('api.upload.google_drive_project') }}' : '{{ route('api.upload.onedrive_project') }}';
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          document.getElementById('cloudForm').style.display = 'none';
          document.getElementById('formFooter').style.display = 'none';
          document.getElementById('successView').style.display = 'block';
          document.getElementById('successFooter').style.display = 'flex';
        } else {
          alert('Error: ' + data.message);
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      } catch (err) {
        alert('Server error.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    });
  </script>
</body>
</html>
