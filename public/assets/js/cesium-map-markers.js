/**
 * Add location markers (pins) on the overview Cesium 2D map with thumbnails and clustering.
 * Uses the viewer from cesium-map.js (window.cesiumViewer). No separate "link" is needed for images:
 * both scripts run on the same page, so image URLs are resolved from the document (same base as
 * <img src="../../assets/..."> on the landing page) and load on the overview map.
 * Loads locations from MapData API (database) when available so admin add/delete is reflected; falls back to data/locations.json when API is empty or unavailable.
 * KK_OSPREY uses kkOsprey_pin_image.jpg as pin/choice-bar thumbnail. Clustering groups nearby pins when zoomed out and shows count.
 * Expected locations (5): KK Osprey, KB 3DTiles Lite, Kolombong (fisheye test), Wisma Merdeka, PPNS YS.
 * Clustering concept: The number on each pin is not fixed—it is how many locations are grouped in that cluster.
 * Zoom IN = clusters split into smaller groups (pin number decreases), down to single pins (1). Zoom OUT = nearby
 * locations merge (pin number increases). Only the location choice bar is shown on hover; it must show exactly that many cards.
 *
 * HOVER RULE (keep when adding more locations): When the cursor is inside multiple clusters' hit boxes (e.g. after
 * zoom, stale + current clusters), pick the cluster whose CENTER is closest to the cursor (smallest distSq). Do NOT
 * use cluster size (e.g. prefer largest/smallest count)—that causes the bar to show the wrong count (e.g. 5 instead
 * of 3 when hovering the zoomed-in "3" pin). This way the choice bar always matches the number on the pin at that zoom.
 */
(function () {
  // API_BASE: prefer explicit config set by blade, then current page origin.
  // NEVER auto-detect from DB thumbnail URLs — those may contain stale hosts (e.g. localhost:3000).
  var API_BASE = (typeof window !== 'undefined' && window.AppConfig && window.AppConfig.baseUrl)
    ? window.AppConfig.baseUrl.replace(/\/$/, '')
    : (typeof window !== 'undefined' && window.TemaDataPortal_API_BASE)
      ? window.TemaDataPortal_API_BASE.replace(/\/$/, '')
      : (typeof window !== 'undefined' && window.location ? window.location.origin : '');

  /**
   * Auto-detect the API base URL from a thumbnail URL returned by the API.
   * If the thumbnail points to a different origin than the current page (e.g. localhost:3000
   * when the page is on 127.0.0.1:8000), update API_BASE so all uploads load from there.
   * Only updates if window.TemaDataPortal_API_BASE was not explicitly set by the app.
   */
  // AFTER: disabled — API_BASE is now always set from window.AppConfig or page origin.
  // Auto-detecting from DB URLs caused API_BASE to be overridden with stale hosts (e.g. localhost:3000).
  function detectApiBaseFromUrl(absoluteUrl) {
    // Intentionally disabled. URLs from DB are rewritten server-side in MapDataController.
  }

  // Static thumbnail directory — relative to the project asset root.
  // Filenames are derived dynamically from location IDs (see deriveStaticThumbnailPath).
  var STATIC_THUMB_DIR = '../../assets/img/front-pages/locations/';

  // Base URL for resolving relative image paths. Prefer script location so ../../ is always project root.
  function getImageBaseUrl() {
    try {
      var script = document.currentScript;
      if (!script && typeof document !== 'undefined') {
        var scripts = document.getElementsByTagName('script');
        for (var i = scripts.length - 1; i >= 0; i--) {
          if (scripts[i].src && scripts[i].src.indexOf('cesium-map-markers') !== -1) {
            script = scripts[i];
            break;
          }
        }
      }
      if (script && script.src) {
        return script.src.replace(/\/[^/]*$/, '/');
      }
      if (typeof window !== 'undefined' && window.location) {
        var origin = window.location.origin;
        var pathname = window.location.pathname || '/';
        var dir = pathname.replace(/\/[^/]*$/, '/') || '/';
        if (origin && origin !== 'null') return origin + dir;
        return window.location.href;
      }
    } catch (e) { /* ignore */ }
    return null;
  }

  var IMAGE_BASE_URL = getImageBaseUrl();
  var DEBUG_IMAGE_URLS = false;

  /**
   * Check if a given origin (e.g. http://localhost:3000) is reachable before making image requests.
   * Results are cached per origin so only ONE network probe is made regardless of how many images
   * share the same server. This eliminates ERR_CONNECTION_REFUSED spam when the backend is down.
   * Uses mode:'no-cors' so CORS headers are not required; a refused connection still throws in .catch().
   */
  var _originReachable = {};       // origin → true | false
  var _originPending = {};         // origin → [callbacks]

  function checkOriginReachable(origin, callback) {
    // Same origin as the page is always reachable
    if (!origin || origin === window.location.origin) { callback(true); return; }
    // Cache hit
    if (_originReachable[origin] === true)  { callback(true);  return; }
    if (_originReachable[origin] === false) { callback(false); return; }
    // Already probing — queue the callback
    if (_originPending[origin]) { _originPending[origin].push(callback); return; }
    _originPending[origin] = [callback];

    var done = false;
    function resolve(reachable) {
      if (done) return;
      done = true;
      clearTimeout(timer);
      _originReachable[origin] = reachable;
      var cbs = _originPending[origin] || [];
      delete _originPending[origin];
      cbs.forEach(function (cb) { cb(reachable); });
    }
    // Timeout: if no response within 3 s treat as unreachable
    var timer = setTimeout(function () { resolve(false); }, 3000);

    // HEAD with no-cors: succeeds (opaque) if server is up, throws if connection refused
    fetch(origin + '/', { method: 'HEAD', mode: 'no-cors', cache: 'no-store' })
      .then(function () { resolve(true); })
      .catch(function () { resolve(false); });
  }

  /** Return true if a URL is an uploaded file (served from the API/backend, not a static asset). */
  function isUploadUrl(url) {
    return url && typeof url === 'string' &&
      (url.indexOf('/uploads/') !== -1 || url.indexOf('/storage/') !== -1);
  }

  /**
   * Normalize a filename: decode percent-encoding first (so %20 → space),
   * then replace all whitespace with underscores and lowercase.
   * This reconciles filenames stored with spaces in DB vs underscored files on disk.
   */
  function normalizeFilename(filename, fullPath) {
    if (!filename || typeof filename !== 'string') return filename;
    var decoded = filename;
    try { decoded = decodeURIComponent(filename); } catch (e) { /* keep as-is if malformed */ }
    var normalized = decoded.replace(/\s+/g, '_');
    
    // If it's an uploaded file, do not lowercase (preserves case-sensitive random characters and extension)
    if (fullPath && isUploadUrl(fullPath)) {
      return normalized;
    }
    return normalized.toLowerCase();
  }

  /**
   * Normalize the filename portion of a URL path (decodes %20 etc., spaces → underscores, lowercase).
   * Handles both relative paths and absolute URLs safely.
   */
  function normalizePathFilename(path) {
    if (!path || typeof path !== 'string') return path;
    var qIdx = path.indexOf('?');
    var query = qIdx !== -1 ? path.slice(qIdx) : '';
    var base = qIdx !== -1 ? path.slice(0, qIdx) : path;
    var parts = base.split('/');
    parts[parts.length - 1] = normalizeFilename(parts[parts.length - 1], path);
    return parts.join('/') + query;
  }

  function deriveStaticThumbnailPath(locId) {
    // Disabled to prevent blind 404 requests in the console. 
    // The backend MapDataController now verifies and provides the derived path if it actually exists.
    return null;
  }

  /**
   * Resolve a relative image path to an absolute URL using the script's base URL.
   * Also normalizes the filename (spaces → underscores) before resolving.
   */
  function resolveLocationImageUrl(relativePath) {
    if (!relativePath || typeof relativePath !== 'string') return null;
    var rawPath = relativePath.trim();
    if (rawPath.indexOf('data:') === 0) return rawPath;
    
    // Check if it's already an absolute URL (like Cloudinary)
    if (rawPath.indexOf('http://') === 0 || rawPath.indexOf('https://') === 0) {
      return rewriteApiUrl(rawPath); // pass raw path so it can preserve case/host if it's external
    }
    
    // Otherwise it's a relative path, normalize it
    var path = normalizePathFilename(rawPath);
    try {
      var base = IMAGE_BASE_URL || (typeof window !== 'undefined' && window.location && window.location.href) || '';
      return base ? new URL(path, base).href : null;
    } catch (e) { return null; }
  }

  /**
   * Rewrite an absolute URL that may point to a different host (e.g. localhost:3000 when
   * the page is served from 127.0.0.1:8000) by replacing the origin with API_BASE.
   * This way uploaded pin images always load regardless of which port the backend runs on.
   * If the URL already matches the current page origin, it is returned unchanged.
   */
  function rewriteApiUrl(absoluteUrl) {
    if (!absoluteUrl) return absoluteUrl;
    try {
      var parsed = new URL(absoluteUrl);
      var pageOrigin = window.location.origin;

      // Always trust Cloudinary since it natively supports CORS
      if (parsed.hostname.indexOf('cloudinary.com') !== -1) {
        return absoluteUrl;
      }

      // Our own server is accessible via multiple domain aliases (e.g. both
      // dataportal.geovidia.my and dataportal.temadigital.my point to the same
      // machine). If the URL is from any of our own server domains, strip the
      // host and return the path only so it resolves against whatever domain
      // the page is currently on — NO proxy needed, no cross-origin issue.
      var OWN_SERVER_HOSTS = [
        'dataportal.geovidia.my',
        'dataportal.temadigital.my',
        'geovidia.my',
        'temadigital.my'
      ];
      if (OWN_SERVER_HOSTS.indexOf(parsed.hostname) !== -1) {
        return normalizePathFilename(parsed.pathname) + (parsed.search || '');
      }

      // If it's a third-party different origin (e.g. sabahtourism.com, downbelowadventures.com),
      // we MUST route it through our built-in high-speed /proxy endpoint so it bypasses CORS and loads on the map pin canvas!
      if (parsed.origin !== pageOrigin && parsed.hostname !== 'localhost' && parsed.hostname !== '127.0.0.1' && (!API_BASE || parsed.origin !== new URL(API_BASE).origin)) {
        return '/proxy?url=' + encodeURIComponent(absoluteUrl);
      }


      // Same origin as the current page — no rewrite needed
      if (parsed.origin === pageOrigin) {
        return normalizePathFilename(absoluteUrl);
      }

      // Different origin: rewrite to API_BASE so the correct backend is always used
      if (API_BASE) {
        var apiOrigin = new URL(API_BASE).origin;
        // URL already points at the API server — keep as-is (just normalize filename)
        if (parsed.origin === apiOrigin) {
          return API_BASE + normalizePathFilename(parsed.pathname) + (parsed.search || '');
        }
        // URL points at an unknown host — assume the path is valid, rewrite to API_BASE
        return API_BASE + normalizePathFilename(parsed.pathname) + (parsed.search || '');
      }

      // No API_BASE configured: try rewriting to current page origin as last resort
      return pageOrigin + normalizePathFilename(parsed.pathname) + (parsed.search || '');
    } catch (e) {
      return absoluteUrl;
    }
  }

  // 1x1 transparent GIF for blank thumbnail (e.g. KK_OSPREY when no image is set)
  var BLANK_THUMBNAIL_DATAURL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  // Placeholder as inline SVG so it always shows (no external request); displays location name on a dark box
  function getPlaceholderImageUrl(name) {
    var raw = (name || 'Location').trim();
    var label = raw.length > 22 ? raw.substring(0, 20) + '…' : raw;
    label = label.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    if (!label) label = 'Location';
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="90" viewBox="0 0 160 90">' +
      '<rect width="160" height="90" fill="#1a1a2e"/>' +
      '<text x="80" y="48" text-anchor="middle" fill="#696cff" font-size="11" font-family="sans-serif">' + label + '</text>' +
      '</svg>';
    return 'data:image/svg+xml,' + encodeURIComponent(svg);
  }

  function getViewer(cb) {
    if (window.cesiumViewer) {
      cb(window.cesiumViewer);
      return;
    }
    var attempts = 0;
    var t = setInterval(function () {
      attempts++;
      if (window.cesiumViewer) {
        clearInterval(t);
        cb(window.cesiumViewer);
        return;
      }
      if (attempts > 150) clearInterval(t);
    }, 50);
  }

  function truncate(str, maxLen) {
    if (!str || typeof str !== 'string') return '';
    str = str.trim();
    if (str.length <= maxLen) return str;
    return str.substring(0, maxLen).trim() + '…';
  }

  /**
   * Resolve thumbnail URL for a location.
   * Priority: API/database thumbnail → derived static path from ID → blank/placeholder.
   * API thumbnails are rewritten via rewriteApiUrl so cross-origin uploads always load.
   * Static thumbnails are derived dynamically from the location ID (no hardcoding).
   */
  function getThumbnailUrl(loc) {
    // 1. Use API/DB thumbnail if provided (admin uploads via Edit map pin)
    var fromApi = (loc.thumbnailUrl && loc.thumbnailUrl.trim()) || '';
    if (fromApi) {
      return fromApi; // resolved later in resolveLocationImageUrl → rewriteApiUrl
    }

    // 2. Derive static thumbnail path from location ID (flexible — no hardcoded map)
    var derived = deriveStaticThumbnailPath(loc.id);
    if (derived) return derived;

    // 3. No thumbnail available
    return '';
  }

  /** Normalize API row / locations.json entry to { id, name, description, thumbnailUrl, longitude, latitude }. */
  function normalizeLocations(locationsJson, mapDataArray) {
    var list = [];
    if (locationsJson && locationsJson.locations && Array.isArray(locationsJson.locations)) {
      locationsJson.locations.forEach(function (loc) {
        list.push({
          id: loc.id,
          name: loc.name || loc.id,
          description: loc.description || '',
          thumbnailUrl: loc.thumbnailUrl || loc.thumbNailUrl || loc.previewImage || '',
          longitude: loc.coordinates && loc.coordinates.longitude != null ? loc.coordinates.longitude : null,
          latitude: loc.coordinates && loc.coordinates.latitude != null ? loc.coordinates.latitude : null
        });
      });
    }
    if (mapDataArray && Array.isArray(mapDataArray)) {
      mapDataArray.forEach(function (row) {
        var id = row.mapDataID || row.id;
        if (!id) return;
        if (list.some(function (l) { return l.id === id; })) return;
        var thumbUrl = row.thumbNailUrl || row.thumbnailUrl || '';
        // Auto-detect API_BASE from any absolute upload URL in the data (e.g. http://localhost:3000/uploads/...)
        if (thumbUrl && (thumbUrl.indexOf('http://') === 0 || thumbUrl.indexOf('https://') === 0)) {
          detectApiBaseFromUrl(thumbUrl);
        }
        list.push({
          id: id,
          name: row.title || id,
          description: row.description || '',
          thumbnailUrl: thumbUrl,
          longitude: row.xAxis != null ? row.xAxis : null,
          latitude: row.yAxis != null ? row.yAxis : null
        });
      });
    }
    return list.filter(function (l) { return l.longitude != null && l.latitude != null; });
  }

  var HOVER_RADIUS_PX = 120;
  var PIN_SIZE_SCALE = 2;
  var PIN_IMAGE_HALF = true;
  var PIN_BORDER_PX = 3;

  function addMarkersWithClustering(viewer, locations) {
    try {
      if (!viewer || !locations.length) return;
    var C = Cesium;
    var shortDesc = truncate;
    var labelMaxDesc = 50;

    function projectCartesian(scene, position) {
      if (!scene || !position) return null;
      try {
        if (typeof scene.cartesianToCanvasCoordinates === 'function') {
          var res = scene.cartesianToCanvasCoordinates(position);
          if (res && typeof res.x === 'number') return res;
        }
      } catch (e) {}
      try {
        var res = C.SceneTransforms.wgs84ToWindowCoordinates(scene, position);
        if (res && typeof res.x === 'number') return res;
      } catch (e) {}
      try {
        var res = C.SceneTransforms.worldToWindowCoordinates(scene, position);
        if (res && typeof res.x === 'number') return res;
      } catch (e) {}
      return null;
    }

    while (viewer.dataSources.length > 0) {
      var found = false;
      for (var i = 0; i < viewer.dataSources.length; i++) {
        var ds = viewer.dataSources.get(i);
        if (ds && ds.name === 'locationMarkers') {
          viewer.dataSources.remove(ds);
          found = true;
          break;
        }
      }
      if (!found) break;
    }

    if (DEBUG_IMAGE_URLS && typeof console !== 'undefined' && console.log) {
      console.log('[TemaDataPortal map images] API_BASE:', API_BASE);
      console.log('[TemaDataPortal map images] Image base URL:', IMAGE_BASE_URL || '(none)');
      console.log('[TemaDataPortal map images] Page URL:', window.location.href);
      locations.forEach(function (loc) {
        var rel = getThumbnailUrl(loc);
        if (rel && rel.indexOf('data:') !== 0) {
          var resolved = resolveLocationImageUrl(rel);
          console.log('[TemaDataPortal map images]', loc.id, '->', resolved || '(resolve failed)');
        }
      });
    }

    var dataSource = new C.CustomDataSource('locationMarkers');
    viewer._mapDataSource = dataSource;
    dataSource.clustering.enabled = true;
    dataSource.clustering.minimumClusterSize = 2;
    var clusterToLocationIds = new Map();

    var INITIAL_PIXEL_RANGE = 80; // High-quality default for zoomed out map view
    var MIN_CLUSTER_PX = 60; // Complete overlap elimination cushion (48px pin + 42px cluster radius + safe margin)
    var isZoomingToCluster = false; 
    var ZOOMED_OUT_HEIGHT_DEG = 0.06;

    function getClusterPixelRange() {
      var canvas = viewer.scene.canvas;
      if (!canvas || !canvas.clientWidth || !canvas.clientHeight) return INITIAL_PIXEL_RANGE;
      var minDim = Math.min(canvas.clientWidth, canvas.clientHeight);
      var is2D = viewer.scene.mode === C.SceneMode.SCENE2D;
      if (is2D) return getClusterPixelRange2DFallback();
      var rect = viewer.camera.computeViewRectangle(viewer.scene.globe.ellipsoid);
      if (!rect) return Math.max(INITIAL_PIXEL_RANGE, minDim * 0.9);
      var heightRad = rect.north - rect.south;
      var heightDeg = heightRad * (180 / Math.PI);
      
      // v259: Smoothly interpolate the 3D pixel range between 80px and 50px,
      // clamping the minimum range to 50px (slightly larger than a single pin's 48px width)
      // to guarantee that single map pins never visually overlap at medium/zoomed-out views.
      var zoomedInHeight = 0.05;  // ~5.5 km
      var zoomedOutHeight = 3.0;  // ~330 km (Sabah overview level)
      
      if (heightDeg >= zoomedOutHeight) return INITIAL_PIXEL_RANGE;
      if (heightDeg <= zoomedInHeight) return 50;
      
      var t = (heightDeg - zoomedInHeight) / (zoomedOutHeight - zoomedInHeight);
      return Math.max(50, Math.round(50 + t * (INITIAL_PIXEL_RANGE - 50)));
    }

    function getClusterPixelRange2DFallback() {
      try {
        var f = viewer.camera.frustum;
        if (f && typeof f.right === 'number' && typeof f.left === 'number') {
          var width = Math.abs(f.right - f.left);
          var zoomedOutWidth = 4e5; // 400 km (viewing whole Sabah)
          var zoomedInWidth = 1e4;  // 10 km (viewing KK city block)
          if (width >= zoomedOutWidth) return INITIAL_PIXEL_RANGE;
          
          // v259: Clamped to 50px to prevent visual overlaps of close markers
          if (width <= zoomedInWidth) return 50;
          
          var t = (width - zoomedInWidth) / (zoomedOutWidth - zoomedInWidth);
          return Math.max(50, Math.round(50 + t * (INITIAL_PIXEL_RANGE - 50)));
        }
      } catch (e) { /* ignore */ }
      return INITIAL_PIXEL_RANGE;
    }

    function updateClusterPixelRange() {
      // v190: If we just clicked a cluster, don't let the auto-updater override the split.
      if (isZoomingToCluster) return;
      var pr = getClusterPixelRange();
      if (dataSource.clustering.pixelRange === pr) return;
      dataSource.clustering.pixelRange = pr;
      setTimeout(function() {
        viewer._activeClusters = [];
        viewer._clusteredLocationIds = {};
        dataSource.clustering.enabled = false;
        dataSource.clustering.enabled = true;
        viewer.scene.requestRender();
      }, 50);
    }

    dataSource.clustering.pixelRange = INITIAL_PIXEL_RANGE;
    var clusterRangeThrottle = null;

    function throttledUpdateClusterPixelRange() {
      if (clusterRangeThrottle) return;
      clusterRangeThrottle = setTimeout(function () {
        clusterRangeThrottle = null;
        updateClusterPixelRange();
      }, 180);
    }

    // v220: Use a persistent Map for cluster registry.
    // CRITICAL FIX: Previously used WeakMap which made entries GC-eligible during hover,
    // and per-frame reset which wiped _activeClusters every render frame (empty on static hover).
    // Now clusters are stored persistently by a stable key (sorted location IDs string).
    var locationByIdForActiveCheck = {};
    locations.forEach(function (loc) { locationByIdForActiveCheck[loc.id] = loc; });

    function getRenderedBillboardForEntity(entity) {
      if (!entity) return null;
      var scene = viewer.scene;
      if (!scene) return null;

      function searchCollection(collection) {
        if (!collection) return null;
        
        // 1. If it has a _billboards array, it is a BillboardCollection! (Immune to instanceof module wrapping bugs)
        if (collection._billboards && Array.isArray(collection._billboards)) {
          var billboards = collection._billboards;
          for (var i = 0; i < billboards.length; i++) {
            if (billboards[i] && billboards[i].id === entity) {
              return billboards[i];
            }
          }
        }
        
        // 2. If it's a collection, recursively check its children
        if (typeof collection.get === 'function' && typeof collection.length === 'number') {
          var len = collection.length;
          for (var i = 0; i < len; i++) {
            try {
              var child = collection.get(i);
              var found = searchCollection(child);
              if (found) return found;
            } catch (e) {}
          }
        }
        return null;
      }

      return searchCollection(scene.primitives);
    }
    viewer._getRenderedBillboardForEntity = getRenderedBillboardForEntity;

    window.isClusterActive = function (cluster) {
      if (!cluster) return false;
      var ds = viewer._mapDataSource;
      
      // RULE A: If pixelRange is 1 (split mode), no clusters are active!
      if (ds && ds.clustering && ds.clustering.pixelRange === 1) {
        return false;
      }

      try {
        if (cluster.locationIds && cluster.locationIds.length > 0) {
          if (typeof viewer._isLocationClustered === 'function') {
            return viewer._isLocationClustered(cluster.locationIds[0]);
          }
        }
      } catch (e) {}
      return false;
    };

    window.pruneActiveClusters = function () {
      if (viewer._activeClusters && typeof window.isClusterActive === 'function') {
        viewer._activeClusters = viewer._activeClusters.filter(window.isClusterActive);
      }
    };

    viewer._activeClusters = [];
    viewer._clusteredLocationIds = {};

    dataSource.clustering.clusterEvent.addEventListener(function (entities, cluster) {

      // v213: Disable Cesium's default label since we are drawing the number directly into our HD canvas!
      cluster.label.show = false;
      // v174: Upscaled 42x42 Ultra-HD Rendering
      var count = entities.length;
      var dpr = 3; 
      var canvas = document.createElement('canvas');
      canvas.width = 42 * dpr; 
      canvas.height = 42 * dpr;
      var ctx = canvas.getContext('2d');
      ctx.scale(dpr, dpr);
      
      // Draw Blue Square (Upscaled to 42px)
      ctx.fillStyle = '#2c5fb3';
      ctx.fillRect(0, 0, 42, 42);
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 3; // Clean 3px border
      ctx.strokeRect(1.5, 1.5, 39, 39); // Inset at 1.5px to keep border inside 42px box
      
      // Draw Geometric Sans-Serif Number (Scaled to 42px box)
      ctx.fillStyle = '#ffffff';
      var fontSize = count > 9 ? 17 : 19; // Increased for larger box
      ctx.shadowColor = "rgba(255, 255, 255, 0.5)";
      ctx.shadowBlur = 1; 
      ctx.font = '900 ' + fontSize + 'px "Public Sans", "Montserrat", "Inter", sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(count.toString(), 21, 21); // Center at 21,21
      
      // v201: Flatten to PNG to prevent Cesium picking hit-box offset bugs
      cluster.billboard.image = canvas.toDataURL('image/png');
      cluster.billboard.width = 42;
      cluster.billboard.height = 42; 
      cluster.billboard.show = true;
      cluster.billboard.verticalOrigin = C.VerticalOrigin.CENTER;
      cluster.billboard.horizontalOrigin = C.HorizontalOrigin.CENTER;
      cluster.billboard.disableDepthTestDistance = Number.POSITIVE_INFINITY;

      if (cluster.point) cluster.point.show = false;
      var ids = entities.map(function (e) { return e.id; }).filter(Boolean);

      // v220: Compute centroid and bounds FIRST (needed for both registry and bounds storage)
      var sumLon = 0, sumLat = 0, posCount = 0;
      var lonMin = Infinity, latMin = Infinity, lonMax = -Infinity, latMax = -Infinity;
      var time = viewer.clock.currentTime;
      for (var i = 0; i < entities.length; i++) {
        var ePos = entities[i].position;
        var cartesian = ePos && typeof ePos.getValue === 'function' ? ePos.getValue(time) : ePos;
        if (cartesian) {
          var carto = C.Cartographic.fromCartesian(cartesian);
          var lon = carto.longitude, lat = carto.latitude;
          sumLon += lon; sumLat += lat; posCount++;
          if (lon < lonMin) lonMin = lon;
          if (lat < latMin) latMin = lat;
          if (lon > lonMax) lonMax = lon;
          if (lat > latMax) latMax = lat;
        }
      }
      
      // v220: Attach WGS84 centroid to cluster so the Math Scanner can find it!
      if (posCount > 0) {
        cluster._wgs84Position = C.Cartesian3.fromRadians(sumLon / posCount, sumLat / posCount, 0);
        if (cluster.billboard) {
          cluster.billboard._wgs84Position = cluster._wgs84Position;
        }
      }

      if (ids.length) {
        ids.forEach(function (id) {
          if (!viewer._clusteredLocationIds) viewer._clusteredLocationIds = {};
          viewer._clusteredLocationIds[String(id).toLowerCase()] = true;
        });
        // Stable key: sorted location IDs joined
        var clusterKey = ids.slice().sort().join(',');
        cluster._clusterKey = clusterKey;
        cluster.locationIds = ids;
        if (cluster.billboard) {
          cluster.billboard._clusterKey = clusterKey;
          cluster.billboard.locationIds = ids;
        }

        // v220: Clone properties into a custom activeCluster object to avoid Cesium's wrapper mutation
        var activeCluster = {
          _clusterKey: clusterKey,
          locationIds: ids,
          _wgs84Position: cluster._wgs84Position,
          billboard: cluster.billboard
        };

        // Stable active cluster registration with duplicate-prevention
        if (!viewer._activeClusters) {
          viewer._activeClusters = [];
        }
        var exists = false;
        for (var k = 0; k < viewer._activeClusters.length; k++) {
          if (viewer._activeClusters[k]._clusterKey === clusterKey) {
            viewer._activeClusters[k] = activeCluster; // Update reference
            exists = true;
            break;
          }
        }
        if (!exists) {
          viewer._activeClusters.push(activeCluster);
        }
      }
    });

    viewer.dataSources.add(dataSource);

    var pinImageSize = (PIN_IMAGE_HALF ? 24 : 48) * PIN_SIZE_SCALE;
    var borderPx = (PIN_IMAGE_HALF && PIN_BORDER_PX > 0) ? PIN_BORDER_PX : 0;
    var totalPinH = pinImageSize + 2 * borderPx;

    function addPinEntity(loc, position, labelText, billboardW, billboardH, imageOrDataUrl) {
      var entityOpt = {
        position: position,
        name: loc.name,
        description: '<a href="/viewer/' + encodeURIComponent(loc.id) + '" target="_blank" rel="noopener">View 3D model (opens in new page)</a>',
        id: loc.id
      };
      if (imageOrDataUrl && billboardW > 0 && billboardH > 0) {
        entityOpt.billboard = {
          image: imageOrDataUrl,
          width: billboardW,
          height: billboardH,
          verticalOrigin: C.VerticalOrigin.BOTTOM,
          disableDepthTestDistance: Number.POSITIVE_INFINITY
        };
        entityOpt.label = {
          text: labelText,
          font: (14 * PIN_SIZE_SCALE) + 'px sans-serif',
          fillColor: C.Color.WHITE,
          outlineColor: C.Color.BLACK,
          outlineWidth: 2,
          style: C.LabelStyle.FILL_AND_OUTLINE,
          verticalOrigin: C.VerticalOrigin.BOTTOM,
          pixelOffset: new C.Cartesian2(0, -billboardH - (8 * PIN_SIZE_SCALE)),
          showBackground: true,
          backgroundColor: new C.Color(0.15, 0.15, 0.2, 0.9),
          backgroundPadding: new C.Cartesian2(10 * PIN_SIZE_SCALE, 6 * PIN_SIZE_SCALE),
          disableDepthTestDistance: Number.POSITIVE_INFINITY,
          show: false
        };
      } else {
        entityOpt.point = {
          pixelSize: 12 * PIN_SIZE_SCALE,
          color: C.Color.CORNFLOWERBLUE,
          outlineColor: C.Color.WHITE,
          outlineWidth: 2,
          heightReference: C.HeightReference.NONE
        };
        entityOpt.label = {
          text: labelText,
          font: (14 * PIN_SIZE_SCALE) + 'px sans-serif',
          fillColor: C.Color.WHITE,
          outlineColor: C.Color.BLACK,
          outlineWidth: 2,
          style: C.LabelStyle.FILL_AND_OUTLINE,
          verticalOrigin: C.VerticalOrigin.BOTTOM,
          pixelOffset: new C.Cartesian2(0, -18 * PIN_SIZE_SCALE),
          showBackground: true,
          backgroundColor: new C.Color(0.15, 0.15, 0.2, 0.9),
          backgroundPadding: new C.Cartesian2(10 * PIN_SIZE_SCALE, 6 * PIN_SIZE_SCALE),
          disableDepthTestDistance: Number.POSITIVE_INFINITY,
          show: false
        };
      }
      try {
        dataSource.entities.add(entityOpt);
      } catch (err) {
        console.warn('Map marker add failed for', loc.id, err);
      }
    }

    /**
     * Generate a canvas placeholder data URL for when images are unavailable.
     * Renders the location name on a dark tile so the pin is always visible.
     */
    function makePinPlaceholderDataUrl(name, size) {
      try {
        var c = document.createElement('canvas');
        c.width = size; c.height = size;
        var ctx = c.getContext('2d');
        // v175: White border background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        // Black inset square (restored per user request)
        ctx.fillStyle = '#050505'; 
        ctx.fillRect(3, 3, size - 6, size - 6);
        
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold ' + Math.max(8, Math.round(size * 0.18)) + 'px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        var label = (name || '?').substring(0, 6);
        ctx.fillText(label, size / 2, size / 2);
        return c.toDataURL('image/png');
      } catch (e) {
        return BLANK_THUMBNAIL_DATAURL;
      }
    }

    /**
     * Pre-load an image URL and always call back with a data URL.
     * - Cross-origin upload URLs: pre-flight reachability check first so ERR_CONNECTION_REFUSED
     *   never appears in the console when the backend server is down.
     * - If image loads: draws it (with optional white border) onto a canvas → data URL.
     * - If image fails: tries the derived static path ONLY when it differs from the original,
     *   then falls back to a named placeholder — Cesium always receives a data URL.
     */
    function preloadPinImage(url, pinSize, borderPx, locId, callback) {
      var fullSize = pinSize + 2 * borderPx;
      var placeholder = makePinPlaceholderDataUrl(locId, fullSize);

      if (!url || url.indexOf('data:') === 0) {
        callback(url || placeholder, fullSize, fullSize);
        return;
      }

      function drawImageToDataUrl(imgEl, cb) {
        try {
          var c = document.createElement('canvas');
          c.width = fullSize; c.height = fullSize;
          var ctx = c.getContext('2d');
          if (borderPx > 0) { ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, fullSize, fullSize); }
          ctx.drawImage(imgEl, borderPx, borderPx, pinSize, pinSize);
          cb(c.toDataURL('image/png'), fullSize, fullSize);
        } catch (e) { cb(placeholder, fullSize, fullSize); }
      }

      function loadImage(src, onOk, onFail) {
        var img = new Image();
        img.crossOrigin = 'anonymous';
        var settled = false;
        var timer = setTimeout(function () {
          if (settled) return; settled = true;
          onFail();
        }, 8000);
        img.onload = function () {
          if (settled) return; settled = true; clearTimeout(timer); onOk(img);
        };
        img.onerror = function () {
          if (settled) return; settled = true; clearTimeout(timer); onFail();
        };
        // Add a cache buster parameter to bypass browser CORS cache race conditions
        var cacheBuster = src.indexOf('?') !== -1 ? '&_cb=' + Date.now() : '?_cb=' + Date.now();
        img.src = src.indexOf('data:') === 0 ? src : src + cacheBuster;
      }

      // Derived static path for second-chance attempt (only if different from primary URL)
      var derivedUrl = resolveLocationImageUrl(deriveStaticThumbnailPath(locId));
      var hasDifferentFallback = derivedUrl && derivedUrl !== url;

      function attemptLoad(resolvedUrl) {
        loadImage(resolvedUrl,
          function (img) { drawImageToDataUrl(img, callback); },
          function () {
            // Second chance: try derived static path only if meaningfully different
            if (hasDifferentFallback) {
              loadImage(derivedUrl,
                function (img2) { drawImageToDataUrl(img2, callback); },
                function () { callback(placeholder, fullSize, fullSize); }
              );
            } else {
              callback(placeholder, fullSize, fullSize);
            }
          }
        );
      }

      // For cross-origin upload URLs: probe server reachability first.
      // If the backend is down, skip directly to placeholder — no ERR_CONNECTION_REFUSED logged.
      if (isUploadUrl(url)) {
        try {
          var parsedOrigin = new URL(url).origin;
          if (parsedOrigin !== window.location.origin) {
            checkOriginReachable(parsedOrigin, function (reachable) {
              if (reachable) {
                attemptLoad(url);
              } else {
                // Server is down — use placeholder silently, no network request made
                callback(placeholder, fullSize, fullSize);
              }
            });
            return;
          }
        } catch (e) { /* URL parse failed, fall through to normal load */ }
      }

      attemptLoad(url);
    }

    var pendingCount = locations.length;
    var loadedPins = [];

    if (pendingCount === 0) {
      viewer.scene.requestRender();
    } else {
      locations.forEach(function (loc) {
        var position = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        var labelText = loc.name + (loc.description ? '\n' + shortDesc(loc.description, labelMaxDesc) : '');
        var thumbUrl = getThumbnailUrl(loc);
        var resolvedThumb = (thumbUrl && thumbUrl.indexOf('data:') !== 0) ? resolveLocationImageUrl(thumbUrl) : (thumbUrl || null);

        preloadPinImage(resolvedThumb, pinImageSize, borderPx, loc.id, function (dataUrl, w, h) {
          loadedPins.push({
            loc: loc,
            position: position,
            labelText: labelText,
            w: w,
            h: h,
            dataUrl: dataUrl
          });
          pendingCount--;
          if (pendingCount === 0) {
            // All images are successfully preloaded! Add all entities synchronously to the data source
            loadedPins.forEach(function (p) {
              addPinEntity(p.loc, p.position, p.labelText, p.w, p.h, p.dataUrl);
            });
            viewer._activeClusters = [];
            viewer._clusteredLocationIds = {};
            dataSource.clustering.enabled = false;
            dataSource.clustering.enabled = true;
            updateClusterPixelRange();
            viewer.scene.requestRender();
          }
        });
      });
    }

    var locationIds = {};
    locations.forEach(function (loc) { locationIds[loc.id] = true; });

    // v199: Helper to check if a single pin is currently swallowed by a cluster
    function isLocationClustered(locId) {
      if (!locId) return false;
      var ds = viewer._mapDataSource;
      if (ds && ds.clustering && ds.clustering.enabled) {
        if (ds.clustering.pixelRange === 1) return false;
        var searchId = String(locId).toLowerCase();
        if (viewer._clusteredLocationIds && viewer._clusteredLocationIds[searchId]) {
          return true;
        }
      }
      return false;
    }
    viewer._isLocationClustered = isLocationClustered;

    // Pin image height: billboard uses verticalOrigin=BOTTOM so the geographic coordinate
    // is at the BOTTOM edge of the visual pin. The pin's visual center is pinHalfH pixels ABOVE
    // the geographic coordinate. We must offset the hit-test Y to match the visual center.
    var PIN_SEARCH_HALF_H = (PIN_IMAGE_HALF ? 24 : 48) * PIN_SIZE_SCALE * 0.5; // = 24px

    window.getLocationsInRadius = function (screenX, screenY, radiusPx) {
      var scene = viewer.scene;
      var canvas = scene.canvas;
      var cameraPos = scene.camera.position;
      var R = 6371000; // Earth radius in meters
      var distToCenter = C.Cartesian3.magnitude(cameraPos);
      var cameraHeight = Math.max(distToCenter - R, 0);
      var horizonDistSq = cameraHeight * (2 * R + cameraHeight);
      
      // Calculate CSS to Buffer scale factors (for high-DPI / retina screens support)
      var scaleX = (canvas.clientWidth && canvas.width) ? (canvas.clientWidth / canvas.width) : 1;
      var scaleY = (canvas.clientHeight && canvas.height) ? (canvas.clientHeight / canvas.height) : 1;
      
      // Returns array sorted by closeness so callers can pick the nearest
      var nearby = [];
      var maxDistSq = (radiusPx || 70) * (radiusPx || 70);

      for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];

        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        
        var is2D = scene.mode === C.SceneMode.SCENE2D;
        var distSqToPoint = C.Cartesian3.distanceSquared(cameraPos, cartesian);
        if (!is2D && distSqToPoint > horizonDistSq * 1.5) continue;

        var screenPos = projectCartesian(scene, cartesian);
        if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
          // Adjust Y upward by half the pin height because verticalOrigin=BOTTOM means
          // the geographic coordinate is at the pin's BOTTOM, not its visual center.
          // This makes hover detection work across the full visual pin area.
          var pinCenterX = screenPos.x;
          var pinCenterY = screenPos.y - PIN_SEARCH_HALF_H;

          // 1. Direct match (CSS Space) — against visual center
          var dx1 = pinCenterX - screenX;
          var dy1 = pinCenterY - screenY;
          var distSq1 = dx1 * dx1 + dy1 * dy1;
          
          // 2. Projected coordinate scaled to CSS space match
          var dx2 = pinCenterX * scaleX - screenX;
          var dy2 = pinCenterY * scaleY - screenY;
          var distSq2 = dx2 * dx2 + dy2 * dy2;
          
          // 3. Mouse coordinate scaled to Buffer space match
          var dx3 = pinCenterX - screenX / scaleX;
          var dy3 = pinCenterY - screenY / scaleY;
          var distSq3 = dx3 * dx3 + dy3 * dy3;

          var minDistSq = Math.min(distSq1, distSq2, distSq3);

          if (minDistSq <= maxDistSq) {
            nearby.push({ loc: loc, distSq: minDistSq });
          }
        }
      }
      // Sort closest first so caller can always safely take [0]
      nearby.sort(function(a, b) { return a.distSq - b.distSq; });
      return nearby.map(function(item) { return item.loc; });
    }

    window.getClusterAtScreenPosition = function (screenX, screenY, radiusPx) {
      if (typeof window.pruneActiveClusters === 'function') {
        window.pruneActiveClusters();
      }
      var arr = viewer._activeClusters || [];
      if (arr.length === 0) return null;
      var scene = viewer.scene;
      var canvas = scene.canvas;
      var R = 6371000;
      var distToCenter = C.Cartesian3.magnitude(scene.camera.position);
      var horizonDistSq = Math.max(distToCenter - R, 0) * (2 * R + Math.max(distToCenter - R, 0));
      
      // Calculate CSS to Buffer scale factors (for high-DPI / retina screens support)
      var scaleX = (canvas.clientWidth && canvas.width) ? (canvas.clientWidth / canvas.width) : 1;
      var scaleY = (canvas.clientHeight && canvas.height) ? (canvas.clientHeight / canvas.height) : 1;
      
      var closestCluster = null;
      var minVal = radiusPx * radiusPx;

      for (var i = 0; i < arr.length; i++) {
        var cluster = arr[i];
        if (!window.isClusterActive(cluster)) continue;
        // v215: Use the manually attached position because cluster has no native .position!
        var pos = cluster._wgs84Position;
        if (!pos) continue;

        var is2D = scene.mode === C.SceneMode.SCENE2D;
        if (!is2D && C.Cartesian3.distanceSquared(scene.camera.position, pos) > horizonDistSq * 1.5) continue;
        
        var screenPos = projectCartesian(scene, pos);
        if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
          // 1. Direct match (CSS Space)
          var dx1 = screenPos.x - screenX, dy1 = screenPos.y - screenY;
          var distSq1 = dx1 * dx1 + dy1 * dy1;
          
          // 2. Projected coordinate scaled to CSS space match
          var dx2 = screenPos.x * scaleX - screenX, dy2 = screenPos.y * scaleY - screenY;
          var distSq2 = dx2 * dx2 + dy2 * dy2;
          
          // 3. Mouse coordinate scaled to Buffer space match
          var dx3 = screenPos.x - screenX / scaleX, dy3 = screenPos.y - screenY / scaleY;
          var distSq3 = dx3 * dx3 + dy3 * dy3;

          var minDistSq = Math.min(distSq1, distSq2, distSq3);

          if (minDistSq <= minVal) {
            minVal = minDistSq;
            closestCluster = cluster;
          }
        }
      }
      return closestCluster;
    }

    function getCentroidCartesian(locs) {
      if (!locs || !locs.length) return null;
      var sumLon = 0, sumLat = 0;
      for (var i = 0; i < locs.length; i++) {
        sumLon += locs[i].longitude;
        sumLat += locs[i].latitude;
      }
      return C.Cartesian3.fromDegrees(sumLon / locs.length, sumLat / locs.length, 0);
    }

    function getBoundsRectForLocations(locs) {
      if (!locs || !locs.length) return null;
      var lonMin = Infinity, latMin = Infinity, lonMax = -Infinity, latMax = -Infinity;
      for (var i = 0; i < locs.length; i++) {
        var loc = locs[i];
        var lon = loc.longitude * (Math.PI / 180), lat = loc.latitude * (Math.PI / 180);
        if (lon < lonMin) lonMin = lon;
        if (lat < latMin) latMin = lat;
        if (lon > lonMax) lonMax = lon;
        if (lat > latMax) latMax = lat;
      }
      if (lonMin > lonMax || latMin > latMax) return null;
      var pad = 0.2;
      // v198: Fix 13-kilometer padding bug in fallback bounds calculation.
      var w = Math.max((lonMax - lonMin) * pad, 0.00001);
      var h = Math.max((latMax - latMin) * pad, 0.00001);
      return C.Rectangle.fromRadians(lonMin - w, latMin - h, lonMax + w, latMax + h);
    }

    function getLocationsNearPoint(lonDeg, latDeg, radiusDeg) {
      var r = (radiusDeg || 0.08) * (Math.PI / 180);
      var centerLon = lonDeg * (Math.PI / 180), centerLat = latDeg * (Math.PI / 180);
      var nearby = [];
      for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];
        var lon = loc.longitude * (Math.PI / 180), lat = loc.latitude * (Math.PI / 180);
        var dy = lat - centerLat, dx = (lon - centerLon) * Math.cos(centerLat);
        if (dx * dx + dy * dy <= r * r) nearby.push(loc);
      }
      return nearby;
    }

    function isClusterEntity(entity) {
      if (!entity) return false;
      var id = typeof entity.id === 'string' ? entity.id : (entity.id && entity.id.id);
      if (id && locationByIdForZoom[id]) return false;
      if (entity.label && entity.label.text) {
        var t = String(entity.label.text).trim();
        if (t && /^\d+$/.test(t)) return true;
      }
      return !!(entity.position && (!id || !locationByIdForZoom[id]));
    }

    function zoomInOneStepTowardCluster(clusterPosition) {
      var camera = viewer.camera;
      var scene = viewer.scene;
      try {
        var carto = C.Cartographic.fromCartesian(clusterPosition);
        var rect = camera.computeViewRectangle(scene.globe.ellipsoid);
        if (rect) {
          // v186: Zoom in 85% closer (0.15 multiplier) to force splitting
          var width = (rect.east - rect.west) * 0.15;
          var height = (rect.north - rect.south) * 0.15;
          var halfW = width * 0.5, halfH = height * 0.5;
          var newWest = C.Math.clamp(carto.longitude - halfW, -Math.PI, Math.PI);
          var newEast = C.Math.clamp(carto.longitude + halfW, -Math.PI, Math.PI);
          var newSouth = C.Math.clamp(carto.latitude - halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
          var newNorth = C.Math.clamp(carto.latitude + halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
          camera.flyTo({ 
            destination: new C.Rectangle(newWest, newSouth, newEast, newNorth), 
            duration: 0.35, 
            complete: function () { 
              // v190: Lock the auto-updater for 1.2 seconds to ensure pins STAY split.
              isZoomingToCluster = true;
              dataSource.clustering.pixelRange = 1;
              viewer._activeClusters = [];
              viewer._clusteredLocationIds = {};
              dataSource.clustering.enabled = false;
              dataSource.clustering.enabled = true;
              scene.requestRender(); 
              setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
            } 
          });
        } else {
          var lon = C.Math.toDegrees(carto.longitude);
          var lat = C.Math.toDegrees(carto.latitude);
          // v186: Much tighter span fallback (0.003 degrees)
          var span = 0.003;
          camera.flyTo({ 
            destination: C.Rectangle.fromDegrees(lon - span, lat - span * 0.6, lon + span, lat + span * 0.6), 
            duration: 0.35, 
            complete: function () { 
              // v190: Lock the auto-updater for 1.2 seconds to ensure pins STAY split.
              isZoomingToCluster = true;
              dataSource.clustering.pixelRange = 1;
              viewer._activeClusters = [];
              viewer._clusteredLocationIds = {};
              dataSource.clustering.enabled = false;
              dataSource.clustering.enabled = true;
              scene.requestRender(); 
              setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
            } 
          });
        }
      } catch (e) {
        alert("Cluster fallback zoom crashed: " + e.message);
        if (typeof console !== 'undefined' && console.warn) console.warn('Cluster zoom failed', e);
      }
    }

    var locationByIdForZoom = {};
    locations.forEach(function (loc) { locationByIdForZoom[loc.id] = loc; });

    function tryZoomToCluster(entity) {
      var bounds = null;
      var clusterPos = entity._wgs84Position || (entity.position && (typeof entity.position.getValue === 'function' ? entity.position.getValue(viewer.clock.currentTime) : entity.position));
      
      // v259: Retrieve precise locations inside the cluster using entity.locationIds
      // to avoid using the wide 0.12 degree city-wide proximity scanner which causes
      // the camera to zoom out too far, resulting in overlapping single pins on medium zoom levels.
      var clusterLocs = [];
      if (entity && entity.locationIds && entity.locationIds.length > 0) {
        entity.locationIds.forEach(function (id) {
          var loc = locationByIdForZoom[id];
          if (loc) clusterLocs.push(loc);
        });
      }
      
      if (clusterLocs.length > 0) {
        bounds = getBoundsRectForLocations(clusterLocs);
      } else if (clusterPos) {
        // Proximity Fallback
        var carto = C.Cartographic.fromCartesian(clusterPos);
        var locsNear = getLocationsNearPoint(carto.longitude * (180 / Math.PI), carto.latitude * (180 / Math.PI), 0.12);
        if (locsNear.length > 0) bounds = getBoundsRectForLocations(locsNear);
      }
      
      if (!bounds) return null;
      
      try {
        // v198: If the camera is already zoomed in close to this cluster's bounds, 
        // flying to the bounds again does nothing. We must return false to trigger 
        // the progressive 85% micro-zoom fallback!
        var rect = null;
        try { rect = viewer.camera.computeViewRectangle(viewer.scene.globe.ellipsoid); } catch(e) {}
        if (rect) {
          var currentWidth = rect.east - rect.west;
          var boundsWidth = bounds.east - bounds.west;
          if (currentWidth <= boundsWidth * 1.5) {
             return false; // Camera is already here.
          }
        }

        viewer.camera.flyTo({ 
          destination: bounds, 
          duration: 0.45, 
          complete: function () { 
            // v190: Lock the auto-updater for 1.2 seconds to ensure pins STAY split.
            isZoomingToCluster = true;
            dataSource.clustering.pixelRange = 1;
            viewer._activeClusters = [];
            viewer._clusteredLocationIds = {};
            dataSource.clustering.enabled = false;
            dataSource.clustering.enabled = true;
            viewer.scene.requestRender(); 
            setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
          } 
        });
        return true;
      } catch (e) {
        alert("Cluster primary zoom crashed: " + e.message);
        if (typeof console !== 'undefined' && console.warn) console.warn('Cluster flyTo failed', e);
        return false;
      }
    }
 
    var handler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
    handler.setInputAction(function (click) {
      try {
        var screenX = typeof click.position.x === 'number' ? click.position.x : 0;
        var screenY = typeof click.position.y === 'number' ? click.position.y : 0;
        
        // 1. Prioritize Math-based Cluster Search (Immune to WebGL picking bugs in 2D)
        var cluster = typeof window.getClusterAtScreenPosition === 'function' ? window.getClusterAtScreenPosition(screenX, screenY, 40) : null;
        if (cluster) {
           isZoomingToCluster = true;
           dataSource.clustering.pixelRange = 1;
           if (tryZoomToCluster(cluster)) return;
           var clusterPos = cluster._wgs84Position;
           if (clusterPos) { zoomInOneStepTowardCluster(clusterPos); return; }
        }
 
        // 2. Prioritize Math-based Single Pin Search (Immune to WebGL picking bugs in 2D)
        var locsInRadius = typeof window.getLocationsInRadius === 'function' ? window.getLocationsInRadius(screenX, screenY, 30) : [];
        if (locsInRadius.length === 1) {
           var pinLoc = locsInRadius[0];
           isZoomingToCluster = true;
           dataSource.clustering.pixelRange = 1;
           zoomInOneStepTowardCluster(C.Cartesian3.fromDegrees(pinLoc.longitude, pinLoc.latitude, 0));
           return;
        } else if (locsInRadius.length >= 2) {
           var bounds = getBoundsRectForLocations(locsInRadius);
           if (bounds) {
              isZoomingToCluster = true;
              dataSource.clustering.pixelRange = 1;
              try {
                viewer.camera.flyTo({ 
                  destination: bounds, 
                  duration: 0.45, 
                  complete: function () { 
                    isZoomingToCluster = true;
                    dataSource.clustering.pixelRange = 1;
                    viewer._activeClusters = [];
                    viewer._clusteredLocationIds = {};
                    dataSource.clustering.enabled = false;
                    dataSource.clustering.enabled = true;
                    viewer.scene.requestRender(); 
                    setTimeout(function() { isZoomingToCluster = false; updateClusterPixelRange(); }, 1200);
                  } 
                });
              } catch(e) {}
              return;
           }
        }

        // 3. WebGL Pick Fallback
        var picked = viewer.scene.pick(click.position);
        var entity = C.defined(picked) && picked.id ? picked.id : null;
        if (entity) {
          var id = typeof entity.id === 'string' ? entity.id : (entity.id && entity.id.id);
          if (id && locationByIdForZoom[id]) {
            var pinLoc = locationByIdForZoom[id];
            if (pinLoc) {
              isZoomingToCluster = true;
              dataSource.clustering.pixelRange = 1;
              zoomInOneStepTowardCluster(C.Cartesian3.fromDegrees(pinLoc.longitude, pinLoc.latitude, 0));
            }
            return;
          }
        }

        // 4. Debugging & Logging overlay (disabled for production)
        // var scene = viewer.scene;
        // var debugInfo = "CLICK at " + Math.round(screenX) + "," + Math.round(screenY) + " | Mode: " + scene.mode;
        // var debugDiv = document.getElementById('debug-cesium-overlay');
        // if (!debugDiv) {
        //     debugDiv = document.createElement('div');
        //     debugDiv.id = 'debug-cesium-overlay';
        //     debugDiv.style.position = 'absolute';
        //     debugDiv.style.top = '10px';
        //     debugDiv.style.left = '50%';
        //     debugDiv.style.transform = 'translateX(-50%)';
        //     debugDiv.style.background = 'rgba(0,0,0,0.8)';
        //     debugDiv.style.color = '#fff';
        //     debugDiv.style.padding = '10px 20px';
        //     debugDiv.style.zIndex = '999999';
        //     debugDiv.style.fontFamily = 'monospace';
        //     debugDiv.style.pointerEvents = 'none';
        //     document.body.appendChild(debugDiv);
        // }
        // debugDiv.innerText = debugInfo;
      } catch (clickErr) {
        if (typeof console !== 'undefined' && console.warn) console.warn("Click handler crash: " + clickErr.message);
      }
    }, C.ScreenSpaceEventType.LEFT_CLICK);



    function getLocationsForClusterEntity(clusterEntity) {
      if (!clusterEntity) return [];
      var pos = clusterEntity._wgs84Position;
      if (!pos) return [];
      var carto = C.Cartographic.fromCartesian(pos);
      return getLocationsNearPoint(carto.longitude * (180 / Math.PI), carto.latitude * (180 / Math.PI), 0.12);
    }

    setupLocationChoiceBar(viewer, locations, null, getLocationsForClusterEntity, getClusterAtScreenPosition, PIN_SEARCH_HALF_H, pinImageSize);

    var cameraChangeDebounceTimer = null;

    function handleCameraChangeEnd() {
      if (isZoomingToCluster) return;
      updateClusterPixelRange();
      viewer._activeClusters = [];
      viewer._clusteredLocationIds = {};
      dataSource.clustering.enabled = false;
      dataSource.clustering.enabled = true;
      viewer.scene.requestRender();
      if (typeof window.pruneActiveClusters === 'function') {
        window.pruneActiveClusters();
      }
    }

    viewer.camera.moveStart.addEventListener(function() {
      viewer._activeClusters = [];
      viewer._clusteredLocationIds = {};
    });

    viewer.camera.moveEnd.addEventListener(function() {
      if (cameraChangeDebounceTimer) {
        clearTimeout(cameraChangeDebounceTimer);
        cameraChangeDebounceTimer = null;
      }
      handleCameraChangeEnd();
    });

    viewer.camera.changed.addEventListener(function() {
      throttledUpdateClusterPixelRange();
      if (cameraChangeDebounceTimer) {
        clearTimeout(cameraChangeDebounceTimer);
      }
      cameraChangeDebounceTimer = setTimeout(function () {
        cameraChangeDebounceTimer = null;
        handleCameraChangeEnd();
      }, 150);
    });

    viewer.scene.requestRender();
    dataSource.clustering.pixelRange = INITIAL_PIXEL_RANGE;
    
    } catch (globalErr) {
      alert("3D Map Critical Crash: " + globalErr.message + "\nCheck browser console for full stack.");
      console.error(globalErr);
    }
  }

  function setupLocationChoiceBar(viewer, locations, clusterToLocationIds, getLocationsForClusterEntity, getClusterAtScreenPosition, parentPinSearchHalfH, parentPinImageSize) {
    if (!viewer || !locations.length) return;
    var C = Cesium;
    var PIN_SEARCH_HALF_H = typeof parentPinSearchHalfH === 'number' ? parentPinSearchHalfH : 24;
    var PIN_IMAGE_SIZE = typeof parentPinImageSize === 'number' ? parentPinImageSize : 48;

    function projectCartesian(scene, position) {
      if (!scene || !position) return null;
      try {
        if (typeof scene.cartesianToCanvasCoordinates === 'function') {
          var res = scene.cartesianToCanvasCoordinates(position);
          if (res && typeof res.x === 'number') return res;
        }
      } catch (e) {}
      try {
        var res = C.SceneTransforms.wgs84ToWindowCoordinates(scene, position);
        if (res && typeof res.x === 'number') return res;
      } catch (e) {}
      try {
        var res = C.SceneTransforms.worldToWindowCoordinates(scene, position);
        if (res && typeof res.x === 'number') return res;
      } catch (e) {}
      return null;
    }
    var bar = document.getElementById('locationChoiceBar');
    var cardsContainer = document.getElementById('locationChoiceBarCards');
    var mapContainer = document.getElementById('heroMapContainer');
    if (!bar || !cardsContainer || !mapContainer) return;
    var clusterMap = clusterToLocationIds || new WeakMap();
    var locationById = {};
    locations.forEach(function (loc) { locationById[loc.id] = loc; });
    var cameraIsMoving = false;
    viewer.camera.moveStart.addEventListener(function () { 
      cameraIsMoving = true; 
      hideBar(); 
    });
    viewer.camera.moveEnd.addEventListener(function () { 
      cameraIsMoving = false;
      canvasRect = canvas.getBoundingClientRect();
    });
    var getClusterLocs = typeof getLocationsForClusterEntity === 'function' ? getLocationsForClusterEntity : null;

    var hoverHandler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
    hoverHandler.setInputAction(function (movement) {
      try {
        if (cameraIsMoving) return;
        var screenX = movement.endPosition.x;
        var screenY = movement.endPosition.y;

        var locs = getLocationsForHover(screenX, screenY);

        var rect = viewer.scene.canvas.getBoundingClientRect();
        // screenX and screenY are already CSS pixel coordinates relative to the canvas.
        // Therefore, clientX and clientY are direct viewport client coordinates.
        var clientX = rect.left + screenX;
        var clientY = rect.top + screenY;

        if (!locs || locs.length === 0) {
          // If the cursor has moved onto the bar itself, keep it open.
          // Since the visual gap is exactly 2px, checking the expanded area allows seamless transition.
          if (!isMouseOverBarExpanded(clientX, clientY)) {
            hideBar(); // Instant hide if moved completely away!
          }
          return;
        }

        // Use the pin's exact visual box center as the anchor — always 100% consistent, never cursor-relative
        var anchor = getPinAnchor(locs, screenX, screenY);
        if (!anchor) {
          if (!isMouseOverBarExpanded(clientX, clientY)) {
            hideBar();
          }
          return;
        }

        showBar(locs, anchor.clientX, anchor.clientY);
      } catch (err) {
        if (typeof console !== 'undefined' && console.warn) console.warn('Hover update skipped:', err);
      }
    }, C.ScreenSpaceEventType.MOUSE_MOVE);

    if (viewer.scene.canvas._cesiumHoverHandler) {
      viewer.scene.canvas._cesiumHoverHandler.destroy();
    }
    viewer.scene.canvas._cesiumHoverHandler = hoverHandler;

    // Set hover radius to 30px to stop multi-pin ghost groupings
    var HOVER_RADIUS_PX = 30;

    function getNearbyLocations(screenX, screenY) {
      var scene = viewer.scene;
      var cameraPos = scene.camera.position;
      var R = 6371000;
      var distToCenter = C.Cartesian3.magnitude(cameraPos);
      var cameraHeight = Math.max(distToCenter - R, 0);
      var horizonDistSq = cameraHeight * (2 * R + cameraHeight);

      var nearby = [];
      for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];
        
        // v199: If this location is inside a cluster, its single pin is hidden. Ignore it!
        var isClustered = false;
        // With WeakMap we can't iterate. We must just trust scene.pick for culling, 
        // OR we just don't aggressively cull single pins inside getNearbyLocations!
        // Actually, since we restored scene.pick as the primary, getNearbyLocations is just a fallback.
        // We will just let it find the pin.
        if (isClustered) continue;

        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        
        var is2D = scene.mode === C.SceneMode.SCENE2D;
        // v198: Math-based horizon check (no EllipsoidalOccluder dependency)
        var distSqToPoint = C.Cartesian3.distanceSquared(cameraPos, cartesian);
        if (!is2D && distSqToPoint > horizonDistSq * 1.5) continue;

        var screenPos;
        try { screenPos = C.SceneTransforms.wgs84ToWindowCoordinates(scene, cartesian); } catch (e) { continue; }
        if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
          var dx = screenPos.x - screenX, dy = screenPos.y - screenY;
          if (dx * dx + dy * dy <= HOVER_RADIUS_PX * HOVER_RADIUS_PX) nearby.push(loc);
        }
      }
      return nearby;
    }

    function ensureExactlyNLocs(locs, n) {
      if (!locs || n < 1) return locs || [];
      return locs.length >= n ? locs.slice(0, n) : locs;
    }

    function getClusterScreenPositionFromIds(ids) {
      if (!ids || !ids.length) return null;
      var scene = viewer.scene;
      var sumX = 0, sumY = 0, count = 0;
      for (var i = 0; i < ids.length; i++) {
        var loc = locationById[ids[i]];
        if (!loc || loc.longitude == null || loc.latitude == null) continue;
        try {
          var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
          var screenPos = projectCartesian(scene, cartesian);
          if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
            sumX += screenPos.x; sumY += screenPos.y; count++;
          }
        } catch (e) { /* skip */ }
      }
      return count === 0 ? null : { x: sumX / count, y: sumY / count };
    }

    var PIN_BOX_HALF_W = 32;
    var PIN_BOX_HALF_H = 32;

    function isLocationClustered(locId) {
      if (typeof viewer._isLocationClustered === 'function') {
        return viewer._isLocationClustered(locId);
      }
      return false;
    }

    function getLocationsForHover(screenX, screenY) {
      var scene = viewer.scene;
      var pickCoords = new C.Cartesian2(screenX, screenY);
      
      // ======================================================================
      // STAGE 1: Visual Pixel-Perfect WebGL primitive Pick (The Absolute Truth)
      // ======================================================================
      try {
        var picked = scene.pick(pickCoords);
        if (picked && picked.primitive) {
          // A. Is it a cluster billboard?
          var ids = picked.primitive.locationIds;
          if (!ids || ids.length < 2) {
            // Fallback: look up by primitive reference in active clusters if Cesium wiped locationIds
            var activeClusters = viewer._activeClusters || [];
            for (var c = 0; c < activeClusters.length; c++) {
              if (activeClusters[c].billboard === picked.primitive) {
                ids = activeClusters[c].locationIds;
                break;
              }
            }
          }
          if (ids && ids.length >= 2) {
            var list = ids.map(function (id) { return locationById[id]; }).filter(Boolean);
            if (list.length >= 2) {
              return ensureExactlyNLocs(list, ids.length);
            }
          }
          
          // B. Is it a single pin entity?
          var entityId = picked.id && typeof picked.id === 'object' ? picked.id.id : picked.id;
          if (typeof entityId === 'string' && locationById[entityId]) {
            return [locationById[entityId]];
          }
        }
      } catch (e) {
        if (typeof console !== 'undefined' && console.warn) console.warn('[3DHub] WebGL Visual Pick failed, falling back:', e);
      }

      // ======================================================================
      // STAGE 2: Coordinate Math Scanner (Robust Fallback & Padding Search)
      // ======================================================================
      // 1. Prioritize Math-based Cluster Search (Immune to WebGL picking bugs in 2D)
      // Only returns multiple locations when there is a VISIBLE NUMBERED cluster pin at the cursor.
      var cluster = typeof window.getClusterAtScreenPosition === 'function' ? window.getClusterAtScreenPosition(screenX, screenY, 40) : null;
      if (cluster) {
        var ids = cluster.locationIds;
        if (ids && ids.length >= 2) {
          var list = ids.map(function (id) { return locationById[id]; }).filter(Boolean);
          if (list.length >= 2) return ensureExactlyNLocs(list, ids.length);
        }
      }

      // 2. Single Pin Search — when NO numbered cluster covers the cursor, always return
      // only the CLOSEST single pin. Never return multiple individual pins together,
      // even if several are physically close, because each has its own visible icon.
      // Radius 36px: pin visual center is now correctly offset, 36px covers the full 48px pin image.
      var near = typeof window.getLocationsInRadius === 'function' ? window.getLocationsInRadius(screenX, screenY, 36) : [];
      
      // Filter out any locations that are currently swallowed inside an active cluster
      var visibleNear = [];
      for (var i = 0; i < near.length; i++) {
        if (!isLocationClustered(near[i].id)) {
          visibleNear.push(near[i]);
        }
      }

      if (visibleNear.length > 0) {
        // Always take only the nearest one — caller gets a single-card box, not the multi-card bar
        return [visibleNear[0]];
      }

      return [];
    }

    function getPinCenterClientPosition(nearby) {
      if (!nearby || !nearby.length) return null;
      var scene = viewer.scene;
      var sumX = 0, sumY = 0, count = 0;
      for (var i = 0; i < nearby.length; i++) {
        var loc = nearby[i];
        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        try {
          var screenPos = projectCartesian(scene, cartesian);
          if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
            sumX += screenPos.x; sumY += screenPos.y; count++;
          }
        } catch (e) { /* skip */ }
      }
      if (count === 0) return null;
      var rect = canvas.getBoundingClientRect();
      var centerX = rect.left + (sumX / count);
      // verticalOrigin=BOTTOM: geographic coordinate is at pin BOTTOM.
      // Shift upward by PIN_SEARCH_HALF_H so the anchor is at the visual center.
      var centerY = rect.top + (sumY / count) - PIN_SEARCH_HALF_H;
      return { clientX: centerX, clientY: centerY };
    }

    function getPinAnchor(locs, cursorX, cursorY) {
      if (!locs || !locs.length) return null;

      // 1. Try cluster visual centroid first ONLY when showing a cluster group (multiple locations)
      if (locs.length >= 2) {
        var cluster = typeof getClusterAtScreenPosition === 'function' ? getClusterAtScreenPosition(cursorX, cursorY, 40) : null;
        if (cluster && cluster._wgs84Position) {
          var screenPos = projectCartesian(viewer.scene, cluster._wgs84Position);
          if (screenPos && typeof screenPos.x === 'number') {
            var rect = canvas.getBoundingClientRect();
            return {
              clientX: rect.left + screenPos.x,
              clientY: rect.top + screenPos.y
            };
          }
        }
      }

      // 2. Try the average of the locations in locs (for single pins, this resolves to its exact center)
      var center = getPinCenterClientPosition(locs);
      if (center) return center;

      // 3. Absolute fallback to the first location's coordinate
      if (locs && locs.length) {
        for (var i = 0; i < locs.length; i++) {
          var loc = locs[i];
          if (loc && loc.longitude != null && loc.latitude != null) {
            var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
            try {
              var screenPos = projectCartesian(viewer.scene, cartesian);
              if (screenPos && typeof screenPos.x === 'number') {
                var rect = canvas.getBoundingClientRect();
                var centerY = rect.top + screenPos.y;
                if (locs.length === 1) centerY -= PIN_SEARCH_HALF_H;
                return {
                  clientX: rect.left + screenPos.x,
                  clientY: centerY
                };
              }
            } catch (e) { /* skip */ }
          }
        }
      }

      return null;
    }

    /**
     * Resolve the best image URL for a location card in the hover bar.
     * Handles API uploads (cross-origin rewrite) and static assets (derived from ID).
     */
    function getImgSrc(loc) {
      var thumbUrl = getThumbnailUrl(loc);
      if (!thumbUrl) return null;
      if (thumbUrl.indexOf('data:') === 0) return thumbUrl;
      return resolveLocationImageUrl(thumbUrl);
    }

    function renderBarCards(nearby) {
      cardsContainer.innerHTML = '';
      if (!nearby.length) return;
      var isSingle = nearby.length === 1;
      bar.classList.toggle('location-choice-bar-single', isSingle);
      var blankUrl = BLANK_THUMBNAIL_DATAURL;
      nearby.forEach(function (loc) {
        var imgSrc = getImgSrc(loc);
        var placeholderSrc = getPlaceholderImageUrl(loc.name || loc.id);
        var desc = truncate(loc.description || '', 70);
        var card = document.createElement('div');
        card.className = 'location-choice-card' + (isSingle ? ' location-choice-card-single' : '');
        card.setAttribute('data-location-id', loc.id);
        var wrap = document.createElement('div');
        wrap.className = 'location-choice-card-image-wrap';
        var img = document.createElement('img');
        img.alt = loc.name || '';
        img.src = imgSrc || placeholderSrc;
        img.setAttribute('data-placeholder', placeholderSrc);
        img.setAttribute('data-blank-src', blankUrl);
        img.onerror = function () {
          // On first error: try the derived static path (handles DB thumbnail 404)
          var derivedPath = deriveStaticThumbnailPath(loc.id);
          var derivedResolved = derivedPath ? resolveLocationImageUrl(derivedPath) : null;
          if (derivedResolved && this.src !== derivedResolved) {
            this.src = derivedResolved;
            this.onerror = function () {
              this.src = this.dataset.placeholder || this.dataset.blankSrc || blankUrl;
              this.onerror = null;
            };
          } else {
            this.src = this.dataset.placeholder || this.dataset.blankSrc || blankUrl;
            this.onerror = null;
          }
        };
        wrap.appendChild(img);
        card.appendChild(wrap);
        var body = document.createElement('div');
        body.className = 'location-choice-card-body';
        body.innerHTML = '<p class="location-choice-card-title">' + (loc.name || loc.id).replace(/</g, '&lt;') + '</p>' +
          '<p class="location-choice-card-desc">' + desc.replace(/</g, '&lt;') + '</p>';
        card.appendChild(body);
        card.addEventListener('click', function () {
          window.open('/loading-3d?id=' + encodeURIComponent(loc.id) + '&source=overview', '_blank', 'noopener');
        });
        cardsContainer.appendChild(card);
      });
    }

    function placeFloatingBox(clientX, clientY, singlePin) {
      var pinGap = 2; // Exactly 2 pixels away from the map pin box
      var screenPad = 14; // Viewport edge safety padding
      // Precise half-width: dynamic PIN_IMAGE_SIZE * 0.5 for single pin, 21px for cluster pin (42px total)
      var pinHalfW = singlePin ? (PIN_IMAGE_SIZE * 0.5) : 21;
      var maxW = window.innerWidth, maxH = window.innerHeight;
      var barW = bar.offsetWidth || (singlePin ? 220 : 320);
      var barH = bar.offsetHeight || (singlePin ? 100 : 280);

      // CONSISTENT PLACEMENT: Always try RIGHT of pin first.
      var left = clientX + pinHalfW + pinGap;

      // Only flip to the LEFT of the pin if it overflows the right edge AND fits completely on-screen left.
      // This prevents a box on a left-aligned pin from being forced onto the left edge and covering the pin.
      if (left + barW > maxW - screenPad && (clientX - pinHalfW - pinGap - barW >= screenPad)) {
        left = clientX - pinHalfW - pinGap - barW;
      }

      // Hard clamp — never go off-screen left or right
      if (left < screenPad) left = screenPad;
      if (left + barW > maxW - screenPad) left = maxW - screenPad - barW;

      // Vertically center the bar on the pin's projected center
      var top = clientY - barH * 0.5;
      if (top < screenPad) top = screenPad;
      if (top + barH > maxH - screenPad) top = maxH - barH - screenPad;

      bar.style.left = left + 'px';
      bar.style.top = top + 'px';
    }

    var barVisible = false;
    var _lastRenderedKey = null; // Tracks last rendered card set to avoid unnecessary DOM rebuilds
    var hideRafId = null;        // Track hide requestAnimationFrame to prevent race conditions
    var placeRafId = null;       // Track place requestAnimationFrame to prevent race conditions

    function showBar(nearby, clientX, clientY) {
      // Cancel any pending hide animation frame to prevent it from cleaning up style/classes in this tick
      if (hideRafId) {
        cancelAnimationFrame(hideRafId);
        hideRafId = null;
      }

      // Only rebuild cards when the set of locations changes — avoids DOM jitter on every mouse move
      var key = nearby.map(function(l) { return l.id; }).sort().join(',');
      var isSingle = nearby.length === 1;
      if (key !== _lastRenderedKey) {
        _lastRenderedKey = key;
        renderBarCards(nearby);
      }
      bar.classList.add('location-choice-bar-floating', 'is-visible');
      bar.setAttribute('aria-hidden', 'false');
      
      // Always update the box position on every call to align to the pin's center anchor
      if (typeof clientX === 'number' && typeof clientY === 'number') {
        if (placeRafId) {
          cancelAnimationFrame(placeRafId);
        }
        placeRafId = requestAnimationFrame(function () {
          placeFloatingBox(clientX, clientY, isSingle);
          placeRafId = null;
        });
      }
      barVisible = true;
    }

    function hideBar() {
      barVisible = false;
      _lastRenderedKey = null; // Reset so cards re-render fresh when user returns to a pin
      bar.classList.remove('location-choice-bar-single');
      bar.setAttribute('aria-hidden', 'true');
      bar.style.transition = 'none';
      bar.classList.remove('is-visible');

      // Cancel any pending position animation frame
      if (placeRafId) {
        cancelAnimationFrame(placeRafId);
        placeRafId = null;
      }

      // Cancel and schedule fresh cleanup frame
      if (hideRafId) {
        cancelAnimationFrame(hideRafId);
      }
      hideRafId = requestAnimationFrame(function () {
        bar.classList.remove('location-choice-bar-floating');
        bar.removeAttribute('style');
        hideRafId = null;
      });
    }

    function isMouseOverBar(clientX, clientY) {
      if (!bar.classList.contains('is-visible')) return false;
      var rect = bar.getBoundingClientRect();
      return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
    }

    function isMouseOverBarExpanded(clientX, clientY) {
      if (!bar.classList.contains('is-visible')) return false;
      var rect = bar.getBoundingClientRect();
      var pad = 30; // 30px padding covers the entire 2px gap and the pin edge area on transition
      return clientX >= (rect.left - pad) && clientX <= (rect.right + pad) && clientY >= (rect.top - pad) && clientY <= (rect.bottom + pad);
    }

    var canvas = viewer.scene.canvas;
    var canvasRect = canvas.getBoundingClientRect();
    mapContainer.addEventListener('mouseleave', hideBar);

    document.addEventListener('mousemove', function (e) {
      if (!barVisible) return;
      var rect = canvas.getBoundingClientRect();
      var overCanvas = rect.left <= e.clientX && e.clientX <= rect.right && rect.top <= e.clientY && e.clientY <= rect.bottom;
      if (!isMouseOverBarExpanded(e.clientX, e.clientY) && !overCanvas) {
        hideBar();
      }
    });

    // v162: Robust layout refresh with forced Cesium resize
    var resizeTimer = null;
    function refreshLayout() {
      cameraIsMoving = false;
      canvasRect = canvas.getBoundingClientRect();
      
      // v162: Force Cesium to re-sync its internal coordinate engine
      if (viewer && viewer.resize) {
        viewer.resize();
        viewer.scene.requestRender();
      }
      
      hideBar();
      if (typeof console !== 'undefined') console.log('[3DHub] Layout refreshed & Cesium resized');
    }
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(refreshLayout, 200);
    });
    document.addEventListener('fullscreenchange', refreshLayout);
    document.addEventListener('webkitfullscreenchange', refreshLayout);
    document.addEventListener('mozfullscreenchange', refreshLayout);
  }

  var markersLoaded = false;
  function loadAndAddMarkers() {
    if (markersLoaded) return;
    markersLoaded = true;
    getViewer(function (viewer) {
      if (typeof Cesium === 'undefined') return;
      var locationsJson = null, mapDataArray = null, doneCount = 0;

      function maybeDone() {
        doneCount++;
        if (doneCount < 2) return;
        var list;
        if (mapDataArray && Array.isArray(mapDataArray) && mapDataArray.length > 0) {
          list = normalizeLocations(null, mapDataArray);
        } else {
          list = normalizeLocations(locationsJson || null, null);
        }
        addMarkersWithClustering(viewer, list);
      }

      fetch('../../data/locations.json')
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) { locationsJson = data; maybeDone(); })
        .catch(function () { locationsJson = null; maybeDone(); });

      fetch(API_BASE + '/api/map-data')
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
          mapDataArray = Array.isArray(data) && data.length ? data : null;
          maybeDone();
        })
        .catch(function () { mapDataArray = null; maybeDone(); });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAndAddMarkers);
  } else {
    setTimeout(loadAndAddMarkers, 200);
  }
})();