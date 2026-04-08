<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets/') }}/"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Create Project via Google Drive | 3DHub Data Portal</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/') }}/img/favicon/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/fonts/iconify-icons.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/css/client-responsive.css">
  <link rel="stylesheet" href="{{ asset('assets/') }}/vendor/css/pages/front-page.css">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets/') }}/js/front-config.js"></script>
  <script>
    (function () {
      window.userRole = '{{ Auth::user()->role }}';
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

    #mapPicker {
      height: 300px;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      border: 1px solid #d9dee3;
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
  </style>
</head>

<body>
  <div class="split-layout">
    <div class="right-panel">
      <!-- Background Map for ambiance -->
      <div id="bgMap" style="height: 100%; width: 100%; opacity: 0.3;"></div>
    </div>
    <div class="left-panel">
      <div class="left-header">
        <h4 class="mb-1 fw-bold">Create Project via Google Drive</h4>
        <p class="text-muted small mb-0">Provide a public Google Drive link to your flight dataset. Our team will verify the accessibility and process it for processing.</p>
      </div>
      <div class="left-content">
        <div id="successView" style="display: none;" class="text-center pt-5 pb-5">
          <div class="mb-4">
            <div class="success-icon-wrap">
              <i class="bx bx-check"></i>
            </div>
          </div>
          <h3 class="fw-bold mb-3" style="color: #2e3b4e; font-size: 1.75rem;">Project Created!</h3>
          <p class="text-muted mb-4 px-5" style="font-size: 0.95rem; line-height: 1.6;">Your Google Drive project has been successfully submitted. We will verify the link and begin processing your data shortly.</p>
          <div class="d-inline-flex align-items-center text-success bg-label-success py-2 px-3 rounded-pill" style="font-size: 0.85rem; font-weight: 600;">
            <i class="bx bx-shield-check me-2"></i> Our team will notify you once processing starts.
          </div>
        </div>

        <form id="gdriveForm" novalidate>
          <div class="form-section-title mt-0">Google Drive Link</div>
          <div class="mb-3">
            <label class="form-label" for="googleDriveLink">Public Shared Link <span class="text-danger">*</span></label>
            <input type="url" id="googleDriveLink" class="form-control" placeholder="https://drive.google.com/drive/folders/..." required>
            <div class="form-text mt-1"><i class="bx bx-info-circle"></i> Must be set to <strong>"Anyone with the link"</strong></div>
          </div>

          <div class="form-section-title">Project Details</div>
          <div class="mb-3">
            <label class="form-label" for="projectTitle">Project Title <span class="text-danger">*</span></label>
            <input type="text" id="projectTitle" class="form-control" placeholder="e.g., Riverside Survey A" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="projectDescription">Project Description <span class="text-danger">*</span></label>
            <textarea id="projectDescription" class="form-control" rows="2" placeholder="Briefly describe the dataset..." required></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="cameraConfiguration">Camera Configuration <span class="text-danger">*</span></label>
              <select class="form-select" id="cameraConfiguration" name="cameraConfiguration" required>
                <option value="single">Single-Lens</option>
                <option value="multiple">Multi-Lens</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
              <select class="form-select" id="category" name="category" required
                onchange="toggleOtherInput('category', 'categoryOtherDiv', 'categoryOther')">
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
            <input type="text" class="form-control" id="categoryOther" name="categoryOther" placeholder="Enter custom category">
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Output Category <span class="text-danger">*</span></label>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="out3DTiles" value="3D Tiles" checked onclick="return false;" style="pointer-events: none; opacity: 0.7;">
              <label class="form-check-label" for="out3DTiles">3D Tiles</label>
            </div>
            <div class="form-check form-check-inline mt-1">
              <input class="form-check-input" type="checkbox" name="outputCategory" id="outOSGB" value="OSGB" checked onclick="return false;" style="pointer-events: none; opacity: 0.7;">
              <label class="form-check-label" for="outOSGB">OSGB</label>
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

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="imageMetadata">Image Metadata <span class="text-danger">*</span></label>
              <select class="form-select" id="imageMetadata" name="imageMetadata" required>
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
        </form>
      </div>
      <div class="left-footer" id="formFooter">
        <button type="button" class="btn btn-secondary text-white fw-medium border-0 px-4" style="background:#8b9eb0;" onclick="window.location.href='{{ route('create_project') }}'">Cancel</button>
        <button type="submit" form="gdriveForm" id="btnSubmitForm" class="btn btn-primary px-5">Submit Project</button>
      </div>
      <div class="left-footer" id="successFooter" style="display: none;">
        <div class="w-100 text-center">
          <button type="button" class="btn btn-primary w-100" onclick="window.location.href='{{ route('my_uploads') }}'">Go to My Projects</button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
  <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
  <script>
    // Theme-compatible background map
    const bgMap = L.map('bgMap', { zoomControl: false, attributionControl: false }).setView([5.9804, 116.0735], 11);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(bgMap);

    // Interactive Map Picker removed as per user request
    /*
    const pickerMap = L.map('mapPicker', { zoomControl: true }).setView([5.9804, 116.0735], 13);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(pickerMap);
    
    let marker = null;
    pickerMap.on('click', function(e) {
      if (marker) pickerMap.removeLayer(marker);
      marker = L.marker(e.latlng).addTo(pickerMap);
      document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
      document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
    });
    */

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

    document.getElementById('gdriveForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      if (!this.checkValidity()) {
        this.reportValidity();
        return;
      }

      const btn = document.getElementById('btnSubmitForm');
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

      let categoryVal = document.getElementById('category').value;
      if (categoryVal === 'Other') {
        categoryVal = document.getElementById('categoryOther').value;
      }

      const outputCheckboxes = document.querySelectorAll('input[name="outputCategory"]:checked');
      const outputs = Array.from(outputCheckboxes).map(cb => cb.value);

      const payload = {
        projectTitle: document.getElementById('projectTitle').value,
        projectDescription: document.getElementById('projectDescription').value,
        cameraConfiguration: document.getElementById('cameraConfiguration').value,
        googleDriveLink: document.getElementById('googleDriveLink').value,
        latitude: null,
        longitude: null,
        category: categoryVal,
        outputCategory: outputs,
        imageMetadata: document.getElementById('imageMetadata').value,
        captureDate: document.getElementById('captureDate').value
      };

      try {
        const res = await fetch('/api/upload/google-drive-project', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
          document.getElementById('gdriveForm').style.display = 'none';
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

    // Handle initial state of "Other" category
    toggleOtherInput('category', 'categoryOtherDiv', 'categoryOther');
  </script>
</body>

</html>
