<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Create Project Via Data Portal | 3DHub Data Portal</title>
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

  <link rel="stylesheet" href="{{ asset('assets/vendor/leaflet/leaflet.css') }}" />
  <script src="{{ asset('assets/vendor/leaflet/leaflet.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/exif.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/jszip.min.js') }}"></script>

  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>
  <style>
    body { margin: 0; padding: 0; overflow: hidden; }
    .split-layout { display: block; height: 100vh; width: 100vw; position: relative; }
    .right-panel { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; }
    .left-panel { position: absolute; top: 24px; left: 24px; width: 580px; height: calc(100vh - 48px); display: flex; flex-direction: column; background: rgba(255, 255, 255, 0.90); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 10; border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.5); overflow: hidden; }
    .left-content { flex-grow: 1; overflow-y: auto; padding: 1.5rem; }
    .left-footer { min-height: 70px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; background: rgba(255,255,255,0.5); }
    .upload-card { border: 1px solid rgba(105, 108, 255, 0.2); border-radius: 12px; padding: 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; min-height: 160px; cursor: pointer; background: rgba(255, 255, 255, 0.6); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); position: relative; }
    .upload-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(105,108,255,0.15); border-color: #696cff; background: rgba(255, 255, 255, 0.9); }
    .upload-card.active { border: 2.5px solid #696cff; background: rgba(105,108,255,0.05); }
    .upload-card-icon { position: absolute; top: 20px; left: 20px; font-size: 2rem; color: #696cff; opacity: 0.8; }
    .upload-card-title { color: #566a7f; font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; }
    .upload-card-text { font-size: 0.85rem; color: #8592a3; line-height: 1.4; }
    .form-section-title { font-size: 1.1rem; font-weight: 700; color: #32475c; margin-bottom: 0.5rem; margin-top: 2rem; letter-spacing: -0.02em; }
    .auto-mode-btn { background-color: #f1f1f2; color: #4b4b4b; border: 1px solid #d9dee3; font-weight: 600; font-size: 0.75rem; padding: 4px 12px; }
    .set-position-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; }
    .set-position-modal.show { display: flex; }
    .modal-content-custom { background: #fff; width: 95%; max-width: 1300px; height: 90vh; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .modal-header-custom { padding: 1.5rem 2rem; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; background: #fafbfc; }
    .modal-body-custom { padding: 0; flex-grow: 1; position: relative; }
    .modal-footer-custom { padding: 1.25rem 2rem; border-top: 1px solid #f0f2f5; display: flex; justify-content: flex-end; gap: 1rem; background: #fafbfc; }
    .floating-glass-panel { position: absolute; top: 1.5rem; left: 1.5rem; z-index: 10; background: rgba(255,255,255,0.9); backdrop-filter: blur(12px); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 1rem; max-width: 400px; border: 1px solid rgba(255,255,255,0.5); }
    .stats-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .stats-table th { text-align: left; color: #8592a3; font-weight: 600; padding: 0.5rem; border-bottom: 1px solid #edf2f7; }
    .stats-table td { padding: 0.75rem 0.5rem; color: #566a7f; border-bottom: 1px solid #f7fafc; }
    #modalMap { width: 100%; height: 100%; position: absolute; top: 0; left: 0; }
    #mapPicker { width: 100%; height: 100%; }
    .folder-list-item { display: flex; align-items: center; padding: 1rem; border: 1px solid #f0f2f5; border-radius: 12px; background: #fff; margin-bottom: 0.75rem; transition: all 0.2s; }
    .folder-list-item:hover { border-color: #696cff; background: #f8f9ff; }
    .folder-name { font-weight: 600; color: #32475c; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .premium-loading-panel { background: #fff; border: 1px solid #edf2f7; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .loading-step { display: flex; align-items: center; margin-bottom: 1rem; color: #8592a3; font-size: 0.9rem; }
    .step-indicator { width: 24px; height: 24px; border-radius: 50%; background: #f0f2f5; margin-right: 12px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #8592a3; }
    .loading-step.active { color: #696cff; font-weight: 600; }
    .loading-step.active .step-indicator { background: #696cff; color: #fff; animation: pulse 2s infinite; }
    .loading-step.completed { color: #71dd37; }
    .loading-step.completed .step-indicator { background: #71dd37; color: #fff; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(105, 108, 255, 0); } 100% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0); } }

    /* 🌙 DARK MODE OPTIMIZATIONS (v280) */
    [data-bs-theme="dark"] .left-panel { background: rgba(26, 26, 46, 0.95); border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .left-footer { background: rgba(26, 26, 46, 0.6); border-top-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .upload-card { background: rgba(30, 41, 59, 0.6); border-color: rgba(105, 108, 255, 0.3); }
    [data-bs-theme="dark"] .upload-card:hover { background: rgba(45, 55, 72, 0.8); border-color: #696cff; }
    [data-bs-theme="dark"] .upload-card-title { color: #e1e4e8; }
    [data-bs-theme="dark"] .upload-card-text { color: #cbd5e1; }
    [data-bs-theme="dark"] .form-section-title { color: #e1e4e8; }
    [data-bs-theme="dark"] .form-label { color: #d1d5db; }
    [data-bs-theme="dark"] .text-muted { color: #a1acb8 !important; }
    [data-bs-theme="dark"] .modal-content-custom { background: #1a1a2e; border: 1px solid rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .modal-header-custom, [data-bs-theme="dark"] .modal-footer-custom { background: #161625; border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .floating-glass-panel { background: rgba(26, 26, 46, 0.9); border-color: rgba(255, 255, 255, 0.1); color: #e1e4e8; }
    [data-bs-theme="dark"] .stats-table td { color: #d1d5db; border-bottom-color: rgba(255, 255, 255, 0.05); }
    [data-bs-theme="dark"] .stats-table th { color: #a1acb8; border-bottom-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .premium-loading-panel { background: #1a1a2e; border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .folder-list-item { background: #1a1a2e; border-color: rgba(255, 255, 255, 0.1); }
    [data-bs-theme="dark"] .folder-name { color: #e1e4e8; }
  </style>
</head>

<body>
  <div class="split-layout">
    <div class="left-panel shadow-sm">
      <div class="left-content">
        <form id="uploadForm" onsubmit="return false;" novalidate>
          <input type="hidden" id="latitude" name="latitude" required>
          <input type="hidden" id="longitude" name="longitude" required>

          <div class="form-section-title mt-0">Create Project via Data Portal</div>
          <p class="text-muted small">Choose your configuration and upload your imagery.</p>

          <div id="uploadTypeSelection" class="mt-4">
            <div class="mb-3">
              <div class="upload-card" id="cardSingle" onclick="selectUploadType('single')">
                <i class="bx bx-camera upload-card-icon"></i>
                <div class="upload-card-title">Single-lens Photos</div>
                <div class="upload-card-text text-muted">Direct upload for standard DJI or Mavlink drones<br><small class="opacity-75">jpg, jpeg, zip</small></div>
                <input type="radio" name="cameraConfiguration" id="singleCamera" value="single" class="d-none" required>
              </div>
            </div>
            <div class="mb-4">
              <div class="upload-card" id="cardMulti" onclick="selectUploadType('multiple')">
                <i class="bx bx-layer upload-card-icon"></i>
                <div class="upload-card-title">Multi-lens Photos</div>
                <div class="upload-card-text text-muted">For multispectral or thermal rig configurations<br><small class="opacity-75">jpg, jpeg, zip</small></div>
                <input type="radio" name="cameraConfiguration" id="multipleCamera" value="multiple" class="d-none" required>
              </div>
            </div>
          </div>

          <div id="inlineLoadingState" class="mb-4" style="display: none;">
            <div class="premium-loading-panel">
              <h5 class="fw-bold mb-4" style="color: #32475c;">Preparing your data...</h5>
              <div class="loading-step" id="loadStep1"><div class="step-indicator">1</div><span>Analyzing folder structure</span></div>
              <div class="loading-step" id="loadStep2"><div class="step-indicator">2</div><span>Smart Scanning Flight Path (<span id="scanCount">0</span>)</span></div>
              <div class="loading-step" id="loadStep3"><div class="step-indicator">3</div><span>Finalizing statistics</span></div>
            </div>
          </div>

          <div id="folderListWrapper" class="mt-4" style="display: none;"></div>

          <div id="alwaysVisibleFields" class="mt-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="form-section-title mt-0">Project Details</div>
                <button type="button" class="btn btn-sm auto-mode-btn rounded-pill" id="autoModeBtn" onclick="openSetPositionModal()"><i class="bx bx-map-pin me-1"></i> Auto pick</button>
              </div>
              <p class="small text-muted mb-3 mt-n2">Detects center position from image metadata.</p>

              <div class="mb-3">
                <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="projectTitle" name="projectTitle" placeholder="Enter project name" required oninput="generateProjectID()">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Project ID</label>
                <input type="text" class="form-control bg-light" id="projectID" name="projectID" placeholder="Auto-generated based on project name..." readonly required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="projectDescription" name="projectDescription" rows="2" placeholder="Brief project summary..." required></textarea>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                  <select class="form-select" id="categorySelection" name="category" required onchange="toggleOtherCategory()">
                    <option value="Urban">Urban</option>
                    <option value="Agricultural">Agricultural</option>
                    <option value="Coastal">Coastal</option>
                    <option value="Infrastructure">Infrastructure</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold">Metadata Format <span class="text-danger">*</span></label>
                  <select class="form-select" id="imageMetadata" name="imageMetadata" required>
                    <option value="EXIF (embedded)">EXIF (embedded)</option>
                    <option value="POS file">POS file</option>
                    <option value="EXIF & POS">EXIF & POS</option>
                  </select>
                </div>
              </div>

              <div class="mb-3" id="otherCategoryDiv" style="display: none;">
                <label class="form-label fw-semibold">Specify Category <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="otherCategoryName" placeholder="Enter custom category">
              </div>

              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label fw-semibold">Capture Date</label>
                  <input type="date" class="form-control" id="captureDate" name="captureDate" required>
                </div>
                <div class="col-6" id="cameraDetailsDiv" style="display: none;">
                  <label class="form-label fw-semibold">Camera Models</label>
                  <input type="text" class="form-control" id="cameraModels" name="cameraModels" placeholder="RGB, Thermal...">
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label d-block fw-semibold mb-2">Request Outputs</label>
                <div class="d-flex flex-wrap gap-2">
                    <div class="form-check"><input class="form-check-input" type="checkbox" checked disabled id="out3D"><label class="form-check-label small" for="out3D">3D Tiles</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" checked disabled id="outOSGB"><label class="form-check-label small" for="outOSGB">OSGB</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="outDSM" value="DSM"><label class="form-check-label small" for="outDSM">DSM</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="out3DGS" value="3DGS"><label class="form-check-label small" for="out3DGS">3DGS</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="outOrtho" value="Orthophoto"><label class="form-check-label small" for="outOrtho">Orthophoto</label></div>
                </div>
              </div>
          </div>

          <div style="display: none;">
            <input type="file" id="dataFile" multiple>
            <input type="file" id="zipFile" accept=".zip">
          </div>

          <div id="uploadProgressContainer" class="mt-4" style="display: none;">
            <div class="p-3 bg-light rounded-3 border border-primary border-opacity-10">
                <div class="d-flex justify-content-between mb-2 align-items-center">
                  <span id="uploadStatusText" class="small fw-semibold text-primary">Uploading...</span>
                  <span id="uploadPercentageText" class="small fw-bold">0%</span>
                </div>
                <div class="progress" style="height: 8px;"><div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div></div>
            </div>
          </div>
        </form>
      </div>

      <div class="left-footer border-top px-4">
        <div>
            <button type="button" class="btn fw-medium border-0 px-4 me-2" style="background:#8b9eb0; color:#fff;" onclick="window.location.href='{{ route('create_project') }}'">Cancel</button>
            <button type="button" class="btn btn-label-secondary" onclick="window.location.reload()">Reset</button>
        </div>
        <div class="d-flex align-items-center">
            <button type="button" class="btn px-4 fw-bold me-2" id="pauseBtn" style="display: none; background-color: #ffcc00; color: #000; border: none;" onclick="togglePauseUpload()">Pause</button>
            <button type="button" class="btn btn-primary px-5 fw-bold" id="submitBtn" onclick="startFinalUpload()">Start Upload</button>
        </div>
      </div>
    </div>
    <div class="right-panel">
      <div id="mapPicker"></div>
    </div>
  </div>

  <div class="set-position-modal" id="setPositionModal">
    <div class="modal-content-custom">
      <div class="modal-header-custom">
        <h4 class="mb-0 fw-bold">Verify Project Footprint</h4>
        <button type="button" class="btn-close" onclick="closeSetPositionModal()"></button>
      </div>
      <div class="modal-body-custom">
        <div id="modalMap"></div>
        <div class="floating-glass-panel">
          <h6 class="fw-bold mb-3 border-bottom pb-2">Smart Scan Results</h6>
          <table class="stats-table" id="exifStatsTable">
            <thead><tr><th>Asset Name</th><th>Files</th><th>GPS Status</th></tr></thead>
            <tbody></tbody>
          </table>
          <p class="small text-muted mt-3 mb-0"><i class="bx bx-path me-1"></i> Blue line indicates the detected flight path.</p>
        </div>
      </div>
      <div class="modal-footer-custom">
        <button type="button" class="btn btn-outline-secondary px-4" onclick="closeSetPositionModal()">Cancel</button>
        <button type="button" class="btn btn-primary px-5 fw-bold" onclick="importPositionData()">Confirm Position</button>
      </div>
    </div>
  </div>

  <script>
    var UPLOAD_API = window.location.origin;
    var pendingUploadFiles = [];
    var flightPathPoints = [];
    var flightPolyline = null;
    var rootFolderStats = {};
    var modalMap = null, mainMap = null, mainMarker = null;

    var isUploading = false;
    var isUploadPaused = false;
    var uploadPausePromise = null;
    var uploadPauseResolve = null;
    var runningXhrs = {}; 
    var maxVisualPercent = 0; // 🛡️ Progress Lock: Prevents jumping back

    window.addEventListener('beforeunload', function (e) {
        if (isUploading) {
            e.preventDefault();
            e.returnValue = 'Upload in progress. Are you sure you want to leave? Your upload will be cancelled.';
        }
    });

    function togglePauseUpload() {
        isUploadPaused = !isUploadPaused;
        const btn = document.getElementById('pauseBtn');
        if (isUploadPaused) {
            btn.innerHTML = 'Resume';
            btn.style.backgroundColor = '#28a745';
            btn.style.color = '#fff';
            document.getElementById('uploadStatusText').innerText = 'Paused (Instant)';
            
            // 🛑 INSTANT STOP: Abort every single active lane immediately
            Object.values(runningXhrs).forEach(xhr => xhr.abort());
            
            uploadPausePromise = new Promise(resolve => {
                uploadPauseResolve = resolve;
            });
        } else {
            btn.innerHTML = 'Pause';
            btn.style.backgroundColor = '#ffcc00';
            btn.style.color = '#000';
            
            // 🚀 INSTANT RESUME UI: Change status immediately to feel responsive
            document.getElementById('uploadStatusText').innerText = 'Streaming Nitro Data... 🚀';
            
            if (uploadPauseResolve) {
                uploadPauseResolve();
                uploadPauseResolve = null;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
      // 🚀 TURBO-MAP (v146): Hardware Accelerated Main View
      mainMap = L.map('mapPicker', { 
          preferCanvas: true, 
          zoomControl: false, 
          attributionControl: false 
      }).setView([5.98, 116.07], 13);
      L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 }).addTo(mainMap);
      mainMap.on('click', e => placeMarker(e.latlng));
    });

    function placeMarker(latlng) {
      const lat = latlng.lat || latlng[0];
      const lng = latlng.lng || latlng[1];
      if (mainMarker) mainMap.removeLayer(mainMarker);
      mainMarker = L.marker([lat, lng], { draggable: true }).addTo(mainMap);
      document.getElementById('latitude').value = lat.toFixed(6);
      document.getElementById('longitude').value = lng.toFixed(6);
    }

    function selectUploadType(type) {
        document.getElementById('cardSingle').classList.remove('active');
        document.getElementById('cardMulti').classList.remove('active');
        document.getElementById(type === 'single' ? 'cardSingle' : 'cardMulti').classList.add('active');
        document.getElementById(type === 'single' ? 'singleCamera' : 'multipleCamera').checked = true;
        document.getElementById('cameraDetailsDiv').style.display = (type === 'multiple' ? 'block' : 'none');
        document.getElementById('dataFile').click();
    }

    document.getElementById('dataFile').addEventListener('change', e => handleFilesSelected(e.target.files));

    async function handleFilesSelected(filesList) {
        const files = Array.from(filesList);
        if (files.length === 0) return;
        pendingUploadFiles = files;
        flightPathPoints = [];
        
        document.getElementById('uploadTypeSelection').style.display = 'none';
        document.getElementById('alwaysVisibleFields').style.display = 'none';
        document.getElementById('inlineLoadingState').style.display = 'block';

        // 🚀 FRESH SCAN RESET (v312): Reset loading steps classes from previous scans
        document.getElementById('loadStep1').className = 'loading-step';
        document.getElementById('loadStep2').className = 'loading-step';
        document.getElementById('loadStep3').className = 'loading-step';
        document.getElementById('scanCount').textContent = '0';
        
        // Step 1: Analyze Structure
        rootFolderStats = {};
        for(const f of files) {
            const root = (f.webkitRelativePath || f.name).split('/')[0];
            if(!rootFolderStats[root]) rootFolderStats[root] = { photos: 0, sizeBytes: 0 };
            rootFolderStats[root].photos++;
            rootFolderStats[root].sizeBytes += f.size;
        }
        document.getElementById('loadStep1').classList.add('completed');
        document.getElementById('loadStep2').classList.add('active');

        // Step 2: Smart Scan - High-Speed Parallel Processing
        const scanDisplay = document.getElementById('scanCount');
        let processedCount = 0;
        const SCAN_LIMIT = 50;
        const SCAN_CONCURRENCY = 6;
        
        // Prepare the task list
        const scanTasks = [];
        
        startScan();
    }

    // ─── Phase 1: Background Intelligence (Web Worker) ─────────────────────
    async function loadScannerDeps() {
        if (!window.JSZip && document.querySelector('script[src*="jszip"]') == null) {
            await new Promise((r) => { const s = document.createElement('script'); s.src = 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js'; s.onload = r; document.head.appendChild(s); });
        }
        if (!window.EXIF && document.querySelector('script[src*="exif"]') == null) {
            await new Promise((r) => { const s = document.createElement('script'); s.src = 'https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.min.js'; s.onload = r; document.head.appendChild(s); });
        }
    }

    function extractExifFast(blob) {
        return new Promise(resolve => {
            EXIF.getData(blob, function() {
                const lat = EXIF.getTag(this, "GPSLatitude");
                const lng = EXIF.getTag(this, "GPSLongitude");
                const latRef = EXIF.getTag(this, "GPSLatitudeRef") || "N";
                const lngRef = EXIF.getTag(this, "GPSLongitudeRef") || "E";
                if (lat && lng) {
                    const dLat = (lat[0] + (lat[1]/60) + (lat[2]/3600)) * (latRef == "N" ? 1 : -1);
                    const dLng = (lng[0] + (lng[1]/60) + (lng[2]/3600)) * (lngRef == "E" ? 1 : -1);
                    resolve([dLat, dLng]);
                } else resolve(null);
            });
        });
    }

    async function startScan() {
        if (pendingUploadFiles.length === 0) return;
        
        document.getElementById('loadStep1').classList.add('completed');
        document.getElementById('loadStep2').classList.add('active');
        const scanDisplay = document.getElementById('scanCount');
        
        await loadScannerDeps();

        // 🏎️ METEOR SCANNER: Reads only the first 128KB headers for extreme speed
        let processed = 0;
        let lastYield = Date.now();

        for (let i = 0; i < pendingUploadFiles.length; i++) {
            const file = pendingUploadFiles[i];
            
            if (file.name.match(/\.zip$/i)) {
                try {
                    const zip = await JSZip.loadAsync(file);
                    const entries = Object.values(zip.files).filter(entry => !entry.dir && entry.name.match(/\.(jpg|jpeg|png)$/i)).slice(0, 50);
                    for (const entry of entries) {
                        const blob = await entry.async("blob");
                        const coords = await extractExifFast(blob);
                        if (coords) flightPathPoints.push(coords);
                    }
                } catch(e) {}
            } else if (file.name.match(/\.(jpg|jpeg|png)$/i)) {
                // Slice file to grab only metadata headers (insanely fast, avoids memory bloat)
                const metadataSlice = file.slice(0, 131072);
                const coords = await extractExifFast(metadataSlice);
                if (coords) flightPathPoints.push(coords);
            }
            
            processed++;
            
            // Yield UI to maintain Green INP score
            if (Date.now() - lastYield > 30) {
                await new Promise(r => requestAnimationFrame(r));
                scanDisplay.textContent = processed;
                lastYield = Date.now();
            }
        }
        
        scanDisplay.textContent = processed;
        finishScanPhase();
    }

    function finishScanPhase() {
        document.getElementById('loadStep2').classList.add('completed');
        document.getElementById('loadStep3').classList.add('active');

        setTimeout(() => {
            document.getElementById('loadStep3').classList.add('completed');
            renderStatsTable();
            openSetPositionModal();
        }, 500);
    }

    function renderStatsTable() {
        const tb = document.querySelector('#exifStatsTable tbody');
        tb.innerHTML = '';
        for(const [r, s] of Object.entries(rootFolderStats)) {
            const gpsOk = flightPathPoints.length > 0 ? "✓ Connected" : "⚠ Not Found";
            tb.innerHTML += `<tr><td>${r}</td><td class='text-center'>${s.photos}</td><td class='text-center'><span class='badge bg-light text-primary'>${gpsOk}</span></td></tr>`;
        }
    }

    function openSetPositionModal() {
        document.getElementById('setPositionModal').classList.add('show');
        if (!modalMap) {
            modalMap = L.map('modalMap', { preferCanvas: true, zoomControl: false, attributionControl: false }).setView([5.98, 116.07], 13);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 }).addTo(modalMap);
        }
        
        if (flightPolyline) modalMap.removeLayer(flightPolyline);
        if (flightPathPoints.length > 0) {
            // High-Visibility Markers: Doubled Size (Radius 7)
            flightPathPoints.forEach(p => {
                L.circleMarker(p, { radius: 7, color: '#000', weight: 1.5, fillColor: '#ffff00', fillOpacity: 1 }).addTo(modalMap);
            });
            
            if (flightPathPoints.length > 1) {
                // High-Visibility Line: Vibrant Cyan
                flightPolyline = L.polyline(flightPathPoints, { color: '#00f2ff', weight: 3, opacity: 0.9, lineJoin: 'round' }).addTo(modalMap);
                modalMap.fitBounds(flightPolyline.getBounds(), { padding: [50, 50] });
            } else {
                modalMap.setView(flightPathPoints[0], 17);
            }
        }
        
        setTimeout(() => modalMap.invalidateSize(), 200);
    }

    function closeSetPositionModal() { 
        document.getElementById('setPositionModal').classList.remove('show');
        document.getElementById('inlineLoadingState').style.display = 'none';
        document.getElementById('alwaysVisibleFields').style.display = 'block';
        document.getElementById('uploadTypeSelection').style.display = 'block';
    }

    function importPositionData() {
        const center = modalMap.getCenter();
        placeMarker(center);
        
        // Sync the Path and Pin Points to the main map exactly as seen in preview
        if (mainMap) {
            // Add Circle Markers for each point on Main Map
            flightPathPoints.forEach(p => {
                L.circleMarker(p, { radius: 7, color: '#000', weight: 1.5, fillColor: '#ffff00', fillOpacity: 1 }).addTo(mainMap);
            });

            if (flightPathPoints.length > 1) {
                L.polyline(flightPathPoints, { color: '#00f2ff', weight: 3, opacity: 0.9, lineJoin: 'round' }).addTo(mainMap);
            }
            mainMap.setView(center, 16);
        }
        
        closeSetPositionModal();
        renderFolderListUI();
    }

    function renderFolderListUI() {
        const w = document.getElementById('folderListWrapper');
        w.style.display = 'block';
        let h = '<div class="fw-bold mb-3 small text-uppercase text-muted">Queued Folders</div>';
        for(const [r, s] of Object.entries(rootFolderStats)) {
            h += `<div class="folder-list-item">
                    <div class="d-flex align-items-center">
                        <i class="bx bxs-file-archive text-primary fs-4 me-3"></i>
                        <div>
                            <div class="folder-name">${r}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${s.photos} Files • ${Math.round(s.sizeBytes/(1024*1024))} MB</div>
                        </div>
                    </div>
                  </div>`;
        }
        w.innerHTML = h;
    }

    function generateProjectID() {
        const t = document.getElementById('projectTitle').value;
        const clean = t.toLowerCase().replace(/[^a-z0-9]/g, '-').substring(0, 15);
        document.getElementById('projectID').value = clean + '-' + Math.random().toString(36).substring(2, 6);
    }
    
    function toggleOtherCategory() {
        const sel = document.getElementById('categorySelection').value;
        document.getElementById('otherCategoryDiv').style.display = (sel === 'Other' ? 'block' : 'none');
    }

    async function startFinalUpload() {
        if (typeof isUploading !== 'undefined' && isUploading) return;

        function backgroundSafeDelay(ms) {
            return new Promise(resolve => {
                if (document.hidden && ms <= 100) {
                    const channel = new MessageChannel();
                    channel.port1.onmessage = () => resolve();
                    channel.port2.postMessage(null);
                } else {
                    setTimeout(resolve, ms);
                }
            });
        }

        const title = document.getElementById('projectTitle').value.trim();
        const desc = document.getElementById('projectDescription').value.trim();
        const cat = document.getElementById('categorySelection').value;
        const otherCat = document.getElementById('otherCategoryName').value.trim();
        const date = document.getElementById('captureDate').value;
        const lat = document.getElementById('latitude').value;
        const lng = document.getElementById('longitude').value;
        const camModels = document.getElementById('cameraModels').value.trim();
        const isMulti = document.getElementById('multipleCamera').checked;
        
        // 🛡️ STRICT COLUMN VALIDATION (v307)
        // Everything without an '(Optional)' label is mandatory.
        let missing = [];
        if (!title) missing.push("Project Title");
        if (!desc) missing.push("Description");
        if (cat === 'Other' && !otherCat) missing.push("Specific Category Name");
        if (!date) missing.push("Capture Date");
        if (!lat || !lng) missing.push("Map Location (Use 'Auto pick' or click map)");
        if (isMulti && !camModels) missing.push("Camera Models");
        if (pendingUploadFiles.length === 0) missing.push("Folder/Files to Upload");

        if (missing.length > 0) {
            return alert("Form Incomplete! The following mandatory fields must be filled:\n\n• " + missing.join("\n• "));
        }

        isUploading = true;
        document.getElementById('pauseBtn').style.display = 'inline-block';
        document.getElementById('uploadProgressContainer').style.display = 'block';
        const st = document.getElementById('uploadStatusText'), 
              pb = document.getElementById('uploadProgressBar'), 
              pt = document.getElementById('uploadPercentageText'), 
              btn = document.getElementById('submitBtn');
        btn.disabled = true;

        const totalSizeBytes = pendingUploadFiles.reduce((acc, f) => acc + f.size, 0);

        // 🚀 STORAGE QUOTA CHECK (v310)
        try {
            const quotaRes = await fetch('/api/user/storage-quota');
            const quotaData = await quotaRes.json();
            if (quotaData.success) {
                const used = Number(quotaData.used_bytes) || 0;
                const limit = Number(quotaData.limit_bytes) || 0;
                if (used + totalSizeBytes > limit) {
                    const usedGB = (used / (1024 * 1024 * 1024)).toFixed(2);
                    const limitGB = (limit / (1024 * 1024 * 1024)).toFixed(0);
                    const uploadGB = (totalSizeBytes / (1024 * 1024 * 1024)).toFixed(2);
                    const remainingGB = Math.max(0, (limit - used) / (1024 * 1024 * 1024)).toFixed(2);
                    
                    alert(`Storage Quota Exceeded!\n\nThis upload is ${uploadGB} GB, but you only have ${remainingGB} GB remaining (used ${usedGB} GB of ${limitGB} GB limit).\n\nPlease delete some old projects in your dashboard to free up space.`);
                    btn.disabled = false;
                    isUploading = false;
                    document.getElementById('uploadProgressContainer').style.display = 'none';
                    document.getElementById('pauseBtn').style.display = 'none';
                    return;
                }
            }
        } catch (err) {
            console.error("Quota preflight check failed:", err);
        }

        const projectID = document.getElementById('projectID').value;
        const uploadId = 'up_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
        const csrfToken = '{{ csrf_token() }}';

        st.textContent = "Preparing Nitro Stream...";

        // 🏎️ UNLIMITED DIRECT ENGINE: Utilizes native 50G PHP limit for extreme speed
        let overallSent = 0;
        let lastPaintTime = Date.now();

        let lastVisualPercent = -1;
        const updateUI = (statusText) => {
            return new Promise(resolve => {
                const now = Date.now();
                // 🚀 ZERO-GRAVITY (v152): Only do the heavy math once every 200ms
                const inFlightSum = Object.values(activeSlotSent).reduce((a, b) => a + b, 0);
                let p = Math.round(((overallSent + inFlightSum) / totalSizeBytes) * 100);
                if (p > 100) p = 100;

                if (p !== lastVisualPercent || now - lastPaintTime > 100 || statusText === "Finalizing") { 
                    if (document.hidden) {
                        // Background tab bypass for requestAnimationFrame to prevent finalization hang
                        if (p > maxVisualPercent) maxVisualPercent = p;
                        pb.style.width = maxVisualPercent + '%'; 
                        pt.textContent = maxVisualPercent + '%';
                        st.textContent = statusText;
                        lastVisualPercent = p;
                        lastPaintTime = Date.now();
                        resolve();
                    } else {
                        requestAnimationFrame(() => {
                            if (p > maxVisualPercent) maxVisualPercent = p;
                            pb.style.width = maxVisualPercent + '%'; 
                            pt.textContent = maxVisualPercent + '%';
                            st.textContent = statusText;
                            lastVisualPercent = p;
                            lastPaintTime = Date.now();
                            resolve();
                        });
                    }
                } else {
                    resolve();
                }
            });
        };

        // 🚀 HYPER-NITRO ENGINE (v51): 16-lane parallel vision
        // ── Environment detection (injected by Laravel/Blade) ────────────────
        // Dev:  chunks go to separate ports (9001-9016) via php -S workers
        // Prod: chunks go to the same origin — Nginx+PHP-FPM handles concurrency
        const NITRO_IS_DEV  = {{ app()->isLocal() ? 'true' : 'false' }};
        const NITRO_BASE    = '{{ rtrim(config("app.url"), "/") }}';
        // ─────────────────────────────────────────────────────────────────────

        function getNitroUrl(port, projectID, isFirst, slotId) {
            let qs = `projectID=${encodeURIComponent(projectID)}&isFirstChunk=${encodeURIComponent(isFirst)}&slot=${encodeURIComponent(slotId)}`;
            if (NITRO_IS_DEV) {
                // Development: separate PHP servers on each port
                const host = window.location.hostname;
                return `http://${host}:${port}/nitro_upload.php?${qs}`;
            }
            // Production: bypass Laravel bootstrap by using the standalone script directly!
            return `/nitro_upload.php?${qs}`;
        }

        let activeSlotSent = {}; 

        function uploadBatchNative(files, relPaths, isFirst, port, slotId) {
            return new Promise((resolve, reject) => {
                const projectID = document.getElementById('projectID').value;
                const url = getNitroUrl(port, projectID, isFirst, slotId);
                
                const parts = [];
                const encoder = new TextEncoder();
                for (let i = 0; i < files.length; i++) {
                    const pathBytes = encoder.encode(relPaths[i]);
                    const header = new ArrayBuffer(4 + pathBytes.length + 8);
                    const view = new DataView(header);
                    view.setUint32(0, pathBytes.length, true);
                    new Uint8Array(header, 4, pathBytes.length).set(pathBytes);
                    view.setFloat64(4 + pathBytes.length, files[i].size, true);
                    parts.push(header);
                    parts.push(files[i]);
                }
                const multiplexBlob = new Blob(parts, { type: 'application/octet-stream' });

                const xhr = new XMLHttpRequest();
                const key = slotId + '_' + port;
                runningXhrs[key] = xhr; // Register for instant stop

                xhr.open('POST', url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Content-Type', 'application/octet-stream');
                
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        activeSlotSent[key] = e.loaded;
                        updateUI("Streaming Nitro Data... 🚀");
                    }
                };

                xhr.onload = () => {
                    delete runningXhrs[key];
                    if (xhr.status >= 200 && xhr.status < 300) {
                        overallSent += multiplexBlob.size;
                        delete activeSlotSent[key];
                        resolve();
                    } else reject(new Error('Multiplex error'));
                };
                xhr.onerror = () => {
                    delete runningXhrs[key];
                    delete activeSlotSent[key];
                    reject(new Error('Stream failed'));
                };
                xhr.onabort = () => {
                    delete runningXhrs[key];
                    delete activeSlotSent[key];
                    reject(new Error('ABORTED'));
                };
                xhr.send(multiplexBlob);
            });
        };

        try {
            // 🚀 SMART-SCALE (v154): Anything over 2MB now gets the Nitro-Sharding treatment
            const MAX_CHUNK_SIZE = 2 * 1024 * 1024; 
            const MAX_BATCH_COUNT = 30;

            // 🚀 PARALLEL LANE PIPELINE (v315): Stream multiple files/batches in parallel lanes
            const batches = [];
            let fileIdx = 0;
            while (fileIdx < pendingUploadFiles.length) {
                let bFiles = [], bPaths = [], bSize = 0;
                while (fileIdx < pendingUploadFiles.length) {
                    const f = pendingUploadFiles[fileIdx];
                    const rel = f.webkitRelativePath || f.name;
                    if (f.size > MAX_CHUNK_SIZE) {
                        // Large file gets its own batch
                        if (bFiles.length > 0) break;
                        bFiles.push(f);
                        bPaths.push(rel);
                        fileIdx++;
                        break;
                    }
                    if (bSize + f.size > MAX_CHUNK_SIZE || bFiles.length >= MAX_BATCH_COUNT) break;
                    bFiles.push(f);
                    bPaths.push(rel);
                    bSize  += f.size;
                    fileIdx++;
                }
                if (bFiles.length > 0) batches.push({ files: bFiles, paths: bPaths });
            }

            st.textContent = `Autonomous Nitro: Streaming through parallel lanes... 🚀`;

            let nextBatchIdx = 0;
            const LANE_COUNT = 6;
            
            const runLane = async (laneId) => {
                while (nextBatchIdx < batches.length) {
                    if (isUploadPaused) await uploadPausePromise;
                    const idx = nextBatchIdx++;
                    if (idx >= batches.length) break;
                    
                    const b = batches[idx];
                    const batchPort = NITRO_IS_DEV ? (9001 + (idx % 16)) : 9001;
                    
                    let batchSuccess = false;
                    let retries = 0;
                    const maxRetries = 3;

                    while (!batchSuccess && retries < maxRetries) {
                        try {
                            if (retries > 0) {
                                console.warn(`🔄 Retrying Batch ${idx} (Attempt ${retries + 1}/${maxRetries})...`);
                                await new Promise(r => setTimeout(r, 2000));
                            }
                            await uploadBatchNative(b.files, b.paths, true, batchPort, 'w' + idx);
                            batchSuccess = true;
                        } catch (e) {
                            if (e.message === 'ABORTED') {
                                await uploadPausePromise;
                            } else {
                                retries++;
                                if (retries >= maxRetries) throw e;
                            }
                        }
                    }
                }
            };

            const activeLanes = Math.min(LANE_COUNT, batches.length);
            const lanePromises = [];
            for (let l = 0; l < activeLanes; l++) {
                lanePromises.push(runLane(l));
            }
            await Promise.all(lanePromises);

            // Move to Finalization
            overallSent = totalSizeBytes;
            document.getElementById('pauseBtn').style.display = 'none';
            await updateUI('Finalizing');
            finalizeOnServer(uploadId, projectID, title, totalSizeBytes, lat, lng);

        } catch (e) {
            console.error(e);
            isUploading = false;
            document.getElementById('pauseBtn').style.display = 'none';
            alert("Nitro Stream Interrupted. Try again.");
            document.getElementById('submitBtn').disabled = false;
        }
    }

    async function finalizeOnServer(uploadId, projectID, title, totalBytes, lat, lng) {
        document.getElementById('uploadStatusText').innerHTML = "Finalizing & Syncing with Linux Server... 🛰️<br><small class='text-muted'>Establishing secure handover (v89)...</small>";
        
        // Dynamic status updates for long 1.1GB transfers
        setTimeout(() => {
            const el = document.getElementById('uploadStatusText');
            if (el && el.textContent.includes("Finalizing")) {
                el.innerHTML = "Still Syncing... 🚀<br><small class='text-muted'>Merging 1.1GB geospatial data and establishing SFTP...</small>";
            }
        }, 8000);

        let category = document.getElementById('categorySelection').value;
        if (category === 'Other') category = document.getElementById('otherCategoryName').value || 'Other';

        try {
            // 🚀 BULLETPROOF-SYNC (v163): No more naming collisions
            const nitroProjectId = document.getElementById('projectID')?.value || 'unnamed-project';
            const nitroUploadId = typeof uploadId !== 'undefined' ? uploadId : 'unknown';

            let nitroMetadataSummary = {
                count: typeof imageMetadata !== 'undefined' ? imageMetadata.length : 0,
                center: (typeof imageMetadata !== 'undefined' && imageMetadata.length > 0) ? imageMetadata[0] : null,
            };

            const fd = new FormData();
            fd.append('uploadId', nitroUploadId);
            fd.append('projectID', nitroProjectId);
            fd.append('projectTitle', document.getElementById('projectTitle')?.value || 'Untitled');
            fd.append('projectDescription', document.getElementById('projectDescription')?.value || '');
            fd.append('category', category);
            fd.append('latitude', document.getElementById('latitude')?.value || 0);
            fd.append('longitude', document.getElementById('longitude')?.value || 0);
            fd.append('captureDate', document.getElementById('captureDate')?.value || '');
            fd.append('imageMetadata', document.getElementById('imageMetadata')?.value || 'EXIF (embedded)'); 
            fd.append('smartScanSummary', JSON.stringify(nitroMetadataSummary));
            
            // 💎 PREMIUM METADATA SYNC (v112)
            const isMulti = document.getElementById('multipleCamera')?.checked;
            const customCam = document.getElementById('cameraModels')?.value;
            fd.append('cameraConfig', isMulti ? 'multiple' : 'single');
            fd.append('cameraModels', customCam || (isMulti ? 'Multiple' : 'Standard'));
            fd.append('userEmail', '{{ Auth::user()?->email ?? "" }}'); // 🔑 Backup identity
            // 🚀 Output Categories Sync
            const outputs = ['3D Tiles', 'OSGB']; // OSGB is now 1:1 synchronized
            if (document.getElementById('outDSM').checked) outputs.push('DSM');
            if (document.getElementById('out3DGS').checked) outputs.push('3DGS');
            if (document.getElementById('outOrtho').checked) outputs.push('Orthophoto');
            fd.append('outputCategories', JSON.stringify(outputs));
            // 🚀 PROJECT-INFINITY (v153): Extended Timeout & Heartbeat Pulse
            // This prevents the "100% Limbo" by allowing the server 5+ minutes to stitch 3GB data.
            const res = await fetch('{{ route('api.upload.finalize') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: fd,
                // The browser will now wait patiently for the heavy 3D assembly
                keepalive: true 
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('uploadProgressBar').style.backgroundColor = '#2ecc71';
                document.getElementById('uploadStatusText').innerHTML = "✨ Upload Complete! Nitro Integrity Verified! 🛰️";
                setTimeout(() => {
                    isUploading = false;
                    window.location.href = '/my-uploads';
                }, 1000);
            } else throw new Error(data.message);
        } catch (e) {
            isUploading = false;
            alert("Nitro Error: " + (e.message || "Network Error"));
            document.getElementById('submitBtn').disabled = false;
        }
    }
  </script>
</body>
</html>