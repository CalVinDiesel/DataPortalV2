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
  function normalizeFilename(filename) {
    if (!filename || typeof filename !== 'string') return filename;
    var decoded = filename;
    try { decoded = decodeURIComponent(filename); } catch (e) { /* keep as-is if malformed */ }
    return decoded.replace(/\s+/g, '_').toLowerCase();
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
    parts[parts.length - 1] = normalizeFilename(parts[parts.length - 1]);
    return parts.join('/') + query;
  }

  /**
   * Derive a static fallback thumbnail path from a location ID.
   * Converts the ID to lowercase with underscores so new locations added to DB
   * automatically resolve without hardcoding: e.g. "KB_3DTiles_Lite" → "kb_3dtiles_lite_pin_image.jpg"
   */
  function deriveStaticThumbnailPath(locId) {
    if (!locId) return null;
    var filename = locId.toLowerCase().replace(/[\s\-]+/g, '_') + '_pin_image.jpg';
    return STATIC_THUMB_DIR + filename;
  }

  /**
   * Resolve a relative image path to an absolute URL using the script's base URL.
   * Also normalizes the filename (spaces → underscores) before resolving.
   */
  function resolveLocationImageUrl(relativePath) {
    if (!relativePath || typeof relativePath !== 'string') return null;
    var path = normalizePathFilename(relativePath.trim());
    if (path.indexOf('data:') === 0) return path;
    // Already absolute — rewrite host if it's pointing to a different origin
    if (path.indexOf('http://') === 0 || path.indexOf('https://') === 0) {
      return rewriteApiUrl(path);
    }
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

  // Single fallback for KK_OSPREY if the API/DB has no thumbnail
  var MAPDATA_KK_OSPREY_FALLBACK = {
    mapDataID: 'KK_OSPREY',
    title: 'KK OSPREY',
    description: '3D model from GeoSabah 3D Hub (Kota Kinabalu area).',
    xAxis: 116.070466,
    yAxis: 5.957839,
    '3dTiles': 'https://3dhub.geosabah.my/3dmodel/KK_OSPREY/tileset.json',
    thumbNailUrl: '',
    updateDateTime: null
  };

  var ALL_PINS_FALLBACK = [
    { id: 'KK_OSPREY', name: 'KK OSPREY', description: '3D model from GeoSabah 3D Hub (Kota Kinabalu area).', thumbnailUrl: '', longitude: 116.070466, latitude: 5.957839 }
  ];

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
          thumbnailUrl: loc.previewImage || loc.thumbNailUrl || '',
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
  var PIN_BORDER_PX = 2;

  function addMarkersWithClustering(viewer, locations) {
    if (!viewer || !locations.length) return;
    var C = Cesium;
    var shortDesc = truncate;
    var labelMaxDesc = 50;

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
    dataSource.clustering.enabled = true;
    dataSource.clustering.minimumClusterSize = 2;
    var clusterToLocationIds = new Map();

    var INITIAL_PIXEL_RANGE = 9999;
    var MIN_CLUSTER_PX = 10;
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
      if (heightDeg >= ZOOMED_OUT_HEIGHT_DEG) return Math.max(INITIAL_PIXEL_RANGE, minDim * 0.9);
      return Math.max(MIN_CLUSTER_PX, Math.min(INITIAL_PIXEL_RANGE, heightDeg * (INITIAL_PIXEL_RANGE / ZOOMED_OUT_HEIGHT_DEG)));
    }

    function getClusterPixelRange2DFallback() {
      try {
        var f = viewer.camera.frustum;
        if (f && typeof f.right === 'number' && typeof f.left === 'number') {
          var width = Math.abs(f.right - f.left);
          var zoomedOutWidth = 2e6;
          var zoomedInWidth = 4e5;
          if (width >= zoomedOutWidth) return INITIAL_PIXEL_RANGE;
          if (width <= zoomedInWidth) return MIN_CLUSTER_PX;
          var t = (width - zoomedInWidth) / (zoomedOutWidth - zoomedInWidth);
          return Math.max(MIN_CLUSTER_PX, Math.min(INITIAL_PIXEL_RANGE, Math.round(MIN_CLUSTER_PX + t * (INITIAL_PIXEL_RANGE - MIN_CLUSTER_PX))));
        }
      } catch (e) { /* ignore */ }
      return INITIAL_PIXEL_RANGE;
    }

    function updateClusterPixelRange() {
      var pr = getClusterPixelRange();
      if (dataSource.clustering.pixelRange === pr) return;
      dataSource.clustering.pixelRange = pr;
      viewer.scene.requestRender();
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

    var clusterToBounds = new Map();
    dataSource.clustering.clusterEvent.addEventListener(function (entities, cluster) {
      cluster.label.show = true;
      cluster.label.text = entities.length.toString();
      cluster.label.font = 'bold ' + (16 * PIN_SIZE_SCALE) + 'px sans-serif';
      cluster.label.fillColor = C.Color.WHITE;
      cluster.label.outlineColor = C.Color.BLACK;
      cluster.label.outlineWidth = 2;
      cluster.label.style = C.LabelStyle.FILL_AND_OUTLINE;
      cluster.label.disableDepthTestDistance = Number.POSITIVE_INFINITY;
      cluster.label.showBackground = true;
      cluster.label.backgroundColor = new C.Color(0.2, 0.4, 0.7, 0.95);
      cluster.label.backgroundPadding = new C.Cartesian2(10 * PIN_SIZE_SCALE, 8 * PIN_SIZE_SCALE);
      if (cluster.billboard) cluster.billboard.show = false;
      if (cluster.point) cluster.point.show = false;
      var ids = entities.map(function (e) { return e.id; }).filter(Boolean);
      if (ids.length) clusterToLocationIds.set(cluster, ids);
      var lonMin = Infinity, latMin = Infinity, lonMax = -Infinity, latMax = -Infinity;
      var time = viewer.clock.currentTime;
      for (var i = 0; i < entities.length; i++) {
        var pos = entities[i].position;
        var cartesian = pos && typeof pos.getValue === 'function' ? pos.getValue(time) : pos;
        if (cartesian) {
          var carto = C.Cartographic.fromCartesian(cartesian);
          var lon = carto.longitude, lat = carto.latitude;
          if (lon < lonMin) lonMin = lon;
          if (lat < latMin) latMin = lat;
          if (lon > lonMax) lonMax = lon;
          if (lat > latMax) latMax = lat;
        }
      }
      if (lonMin <= lonMax && latMin <= latMax) {
        var pad = 0.15;
        var w = Math.max((lonMax - lonMin) * pad, 0.001);
        var h = Math.max((latMax - latMin) * pad, 0.001);
        var rect = C.Rectangle.fromRadians(lonMin - w, latMin - h, lonMax + w, latMax + h);
        clusterToBounds.set(cluster, rect);
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
        description: '<a href="loading-3d.html?id=' + encodeURIComponent(loc.id) + '" target="_blank" rel="noopener">View 3D model (opens in new page)</a>',
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
        ctx.fillStyle = '#1a1a2e';
        ctx.fillRect(0, 0, size, size);
        ctx.fillStyle = '#696cff';
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
        img.src = src;
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

    locations.forEach(function (loc) {
      var position = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
      var labelText = loc.name + (loc.description ? '\n' + shortDesc(loc.description, labelMaxDesc) : '');
      var thumbUrl = getThumbnailUrl(loc);
      var resolvedThumb = (thumbUrl && thumbUrl.indexOf('data:') !== 0) ? resolveLocationImageUrl(thumbUrl) : (thumbUrl || null);

      preloadPinImage(resolvedThumb, pinImageSize, borderPx, loc.id, function (dataUrl, w, h) {
        addPinEntity(loc, position, labelText, w, h, dataUrl);
      });
    });

    var locationIds = {};
    locations.forEach(function (loc) { locationIds[loc.id] = true; });

    function getLocationsInRadius(screenX, screenY, radiusPx) {
      var scene = viewer.scene;
      var nearby = [];
      for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];
        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        var screenPos;
        try {
          screenPos = C.SceneTransforms.worldToWindowCoordinates(scene, cartesian);
        } catch (e) {
          continue;
        }
        if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
          var dx = screenPos.x - screenX;
          var dy = screenPos.y - screenY;
          if (dx * dx + dy * dy <= (radiusPx || 70) * (radiusPx || 70)) nearby.push(loc);
        }
      }
      return nearby;
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
      var w = Math.max((lonMax - lonMin) * pad, 0.001);
      var h = Math.max((latMax - latMin) * pad, 0.001);
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
      if (id && locationIds[id]) return false;
      if (entity.label && entity.label.text) {
        var t = String(entity.label.text).trim();
        if (t && /^\d+$/.test(t)) return true;
      }
      return !!(entity.position && (!id || !locationIds[id]));
    }

    function zoomInOneStepTowardCluster(clusterPosition) {
      var camera = viewer.camera;
      var scene = viewer.scene;
      try {
        var carto = C.Cartographic.fromCartesian(clusterPosition);
        var rect = camera.computeViewRectangle(scene.globe.ellipsoid);
        if (rect) {
          var width = (rect.east - rect.west) * 0.5;
          var height = (rect.north - rect.south) * 0.5;
          var halfW = width * 0.5, halfH = height * 0.5;
          var newWest = C.Math.clamp(carto.longitude - halfW, -Math.PI, Math.PI);
          var newEast = C.Math.clamp(carto.longitude + halfW, -Math.PI, Math.PI);
          var newSouth = C.Math.clamp(carto.latitude - halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
          var newNorth = C.Math.clamp(carto.latitude + halfH, -C.Math.PI_OVER_TWO, C.Math.PI_OVER_TWO);
          camera.flyTo({ destination: new C.Rectangle(newWest, newSouth, newEast, newNorth), duration: 0.35, complete: function () { scene.requestRender(); } });
        } else {
          var lon = C.Math.toDegrees(carto.longitude);
          var lat = C.Math.toDegrees(carto.latitude);
          var span = 0.015;
          camera.flyTo({ destination: C.Rectangle.fromDegrees(lon - span, lat - span * 0.6, lon + span, lat + span * 0.6), duration: 0.35, complete: function () { scene.requestRender(); } });
        }
      } catch (e) {
        if (typeof console !== 'undefined' && console.warn) console.warn('Cluster zoom failed', e);
      }
    }

    var locationByIdForZoom = {};
    locations.forEach(function (loc) { locationByIdForZoom[loc.id] = loc; });

    function tryZoomToCluster(entity) {
      var bounds = clusterToBounds.get(entity);
      if (!bounds) {
        var clusterPos = entity.position && (typeof entity.position.getValue === 'function' ? entity.position.getValue(viewer.clock.currentTime) : entity.position);
        if (clusterPos) {
          var carto = C.Cartographic.fromCartesian(clusterPos);
          var locsNear = getLocationsNearPoint(carto.longitude * (180 / Math.PI), carto.latitude * (180 / Math.PI), 0.12);
          if (locsNear.length > 0) bounds = getBoundsRectForLocations(locsNear);
        }
        if (!bounds) return null;
      }
      try {
        viewer.camera.flyTo({ destination: bounds, duration: 0.45, complete: function () { viewer.scene.requestRender(); } });
        return true;
      } catch (e) {
        if (typeof console !== 'undefined' && console.warn) console.warn('Cluster flyTo failed', e);
        return false;
      }
    }

    var handler = new C.ScreenSpaceEventHandler(viewer.scene.canvas);
    handler.setInputAction(function (click) {
      var screenX = typeof click.position.x === 'number' ? click.position.x : 0;
      var screenY = typeof click.position.y === 'number' ? click.position.y : 0;
      var locsInRadius = getLocationsInRadius(screenX, screenY, 120);
      var zoomTarget = locsInRadius.length ? getCentroidCartesian(locsInRadius) : null;
      var picked = viewer.scene.pick(click.position);
      var entity = C.defined(picked) && picked.id ? picked.id : null;
      if (entity) {
        var id = typeof entity.id === 'string' ? entity.id : (entity.id && entity.id.id);
        if (id && locationIds[id]) {
          var loc = locationByIdForZoom[id];
          if (loc) zoomInOneStepTowardCluster(C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0));
          return;
        }
        if (isClusterEntity(entity)) {
          if (tryZoomToCluster(entity)) return;
          var clusterPos = entity.position && (typeof entity.position.getValue === 'function' ? entity.position.getValue(viewer.clock.currentTime) : entity.position);
          if (clusterPos) { zoomInOneStepTowardCluster(clusterPos); return; }
        }
      }
      var probeOffsets = [[0, 0], [20, 0], [-20, 0], [0, 20], [0, -20], [15, 15], [-15, 15]];
      for (var p = 0; p < probeOffsets.length; p++) {
        var px = screenX + probeOffsets[p][0], py = screenY + probeOffsets[p][1];
        var probe = viewer.scene.pick(new C.Cartesian2(px, py));
        if (C.defined(probe) && probe.id && isClusterEntity(probe.id)) {
          if (tryZoomToCluster(probe.id)) return;
          var pos = probe.id.position && (typeof probe.id.position.getValue === 'function' ? probe.id.position.getValue(viewer.clock.currentTime) : probe.id.position);
          if (pos) { zoomInOneStepTowardCluster(pos); return; }
        }
      }
      if (locsInRadius.length >= 2) {
        var bounds = getBoundsRectForLocations(locsInRadius);
        if (bounds) {
          try { viewer.camera.flyTo({ destination: bounds, duration: 0.45, complete: function () { viewer.scene.requestRender(); } }); return; } catch (e) { /* ignore */ }
        }
      }
      if (zoomTarget && locsInRadius.length >= 1) zoomInOneStepTowardCluster(zoomTarget);
    }, C.ScreenSpaceEventType.LEFT_CLICK);

    function getLocationsForClusterEntity(clusterEntity) {
      if (!clusterEntity || !clusterEntity.position) return [];
      var pos = clusterEntity.position;
      var cartesian = typeof pos.getValue === 'function' ? pos.getValue(viewer.clock.currentTime) : pos;
      if (!cartesian) return [];
      var carto = C.Cartographic.fromCartesian(cartesian);
      return getLocationsNearPoint(carto.longitude * (180 / Math.PI), carto.latitude * (180 / Math.PI), 0.12);
    }

    setupLocationChoiceBar(viewer, locations, clusterToLocationIds, getLocationsForClusterEntity);

    viewer.camera.moveEnd.addEventListener(updateClusterPixelRange);
    viewer.camera.changed.addEventListener(throttledUpdateClusterPixelRange);
    viewer.scene.requestRender();
    dataSource.clustering.pixelRange = INITIAL_PIXEL_RANGE;
  }

  function setupLocationChoiceBar(viewer, locations, clusterToLocationIds, getLocationsForClusterEntity) {
    if (!viewer || !locations.length) return;
    var C = Cesium;
    var bar = document.getElementById('locationChoiceBar');
    var cardsContainer = document.getElementById('locationChoiceBarCards');
    var mapContainer = document.getElementById('heroMapContainer');
    if (!bar || !cardsContainer || !mapContainer) return;
    var clusterMap = clusterToLocationIds || new Map();
    var locationById = {};
    locations.forEach(function (loc) { locationById[loc.id] = loc; });
    var cameraIsMoving = false;
    viewer.camera.moveStart.addEventListener(function () { cameraIsMoving = true; hideBar(); });
    viewer.camera.moveEnd.addEventListener(function () { cameraIsMoving = false; });
    var getClusterLocs = typeof getLocationsForClusterEntity === 'function' ? getLocationsForClusterEntity : null;

    function getNearbyLocations(screenX, screenY) {
      var scene = viewer.scene;
      var nearby = [];
      for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];
        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        var screenPos;
        try { screenPos = C.SceneTransforms.worldToWindowCoordinates(scene, cartesian); } catch (e) { continue; }
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
          var screenPos = C.SceneTransforms.worldToWindowCoordinates(scene, cartesian);
          if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
            sumX += screenPos.x; sumY += screenPos.y; count++;
          }
        } catch (e) { /* skip */ }
      }
      return count === 0 ? null : { x: sumX / count, y: sumY / count };
    }

    var PIN_BOX_HALF_W = 60;
    var PIN_BOX_HALF_H = 45;

    function getLocationsForHover(screenX, screenY) {
      var picked = viewer.scene.pick(new C.Cartesian2(screenX, screenY));
      if (C.defined(picked) && picked.id) {
        if (clusterMap.has(picked.id)) {
          var ids0 = clusterMap.get(picked.id) || [];
          if (ids0.length >= 2) {
            var list0 = ids0.map(function (id) { return locationById[id]; }).filter(Boolean);
            if (list0.length >= 2) return ensureExactlyNLocs(list0, ids0.length);
          }
          return [];
        }
        var eid = typeof picked.id.id === 'string' ? picked.id.id : (picked.id.id && picked.id.id.id);
        if (eid && locationById[eid]) return [locationById[eid]];
      }
      var DIST_SQ_TIE_THRESHOLD = 200;
      var bestCluster = null, bestDistSq = Infinity, bestCount = 0;
      clusterMap.forEach(function (ids, entity) {
        if (!ids || ids.length < 2) return;
        var pos = getClusterScreenPositionFromIds(ids);
        if (!pos || typeof pos.x !== 'number' || typeof pos.y !== 'number') return;
        var dx = Math.abs(screenX - pos.x), dy = Math.abs(screenY - pos.y);
        if (dx <= PIN_BOX_HALF_W && dy <= PIN_BOX_HALF_H) {
          var distSq = (screenX - pos.x) * (screenX - pos.x) + (screenY - pos.y) * (screenY - pos.y);
          var list = ids.map(function (id) { return locationById[id]; }).filter(Boolean);
          if (list.length < 2) return;
          if (distSq < bestDistSq || (distSq <= bestDistSq + DIST_SQ_TIE_THRESHOLD && ids.length > bestCount)) {
            bestDistSq = distSq; bestCount = ids.length;
            bestCluster = ensureExactlyNLocs(list, ids.length);
          }
        }
      });
      return bestCluster && bestCluster.length ? bestCluster : [];
    }

    function getPinCenterClientPosition(nearby) {
      if (!nearby || !nearby.length) return null;
      var scene = viewer.scene;
      var sumX = 0, sumY = 0, count = 0;
      for (var i = 0; i < nearby.length; i++) {
        var loc = nearby[i];
        var cartesian = C.Cartesian3.fromDegrees(loc.longitude, loc.latitude, 0);
        try {
          var screenPos = C.SceneTransforms.worldToWindowCoordinates(scene, cartesian);
          if (screenPos && typeof screenPos.x === 'number' && typeof screenPos.y === 'number') {
            sumX += screenPos.x; sumY += screenPos.y; count++;
          }
        } catch (e) { /* skip */ }
      }
      if (count === 0) return null;
      var rect = canvas.getBoundingClientRect();
      var centerX = rect.left + (sumX / count);
      var centerY = rect.top + (sumY / count);
      if (count === 1) centerY -= (PIN_IMAGE_HALF ? 12 : 24) * PIN_SIZE_SCALE;
      return { clientX: centerX, clientY: centerY };
    }

    /**
     * Resolve the best image URL for a location card in the hover bar.
     * Handles API uploads (cross-origin rewrite) and static assets (derived from ID).
     */
    function getImgSrc(loc) {
      var thumbUrl = getThumbnailUrl(loc);
      if (!thumbUrl) return (loc.id === 'KK_OSPREY' ? BLANK_THUMBNAIL_DATAURL : null);
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
        img.src = imgSrc || (loc.id === 'KK_OSPREY' ? blankUrl : placeholderSrc);
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
          window.open('/loading-3d?id=' + encodeURIComponent(loc.id), '_blank', 'noopener');
        });
        cardsContainer.appendChild(card);
      });
    }

    function placeFloatingBox(clientX, clientY, singlePin) {
      var pad = 14;
      var pinImageWidth = (PIN_IMAGE_HALF ? 24 : 48) * PIN_SIZE_SCALE;
      var maxW = window.innerWidth, maxH = window.innerHeight;
      var barW = bar.offsetWidth || 400, barH = bar.offsetHeight || 200;
      var left = clientX + (singlePin ? pinImageWidth + pad : pad);
      var top;
      if (singlePin) {
        top = clientY - barH * 0.5;
        if (top < pad) top = pad;
        if (top + barH > maxH - pad) top = maxH - barH - pad;
      } else {
        top = clientY - barH - pad;
        if (top < pad) top = clientY + pad;
        if (top + barH > maxH - pad) top = maxH - barH - pad;
      }
      if (left + barW > maxW - pad) left = maxW - barW - pad;
      if (left < pad) left = pad;
      bar.style.left = left + 'px';
      bar.style.top = top + 'px';
    }

    var barVisible = false;

    function showBar(nearby, clientX, clientY, reposition) {
      if (reposition !== false) renderBarCards(nearby);
      if (typeof clientX === 'number' && typeof clientY === 'number') {
        bar.classList.add('location-choice-bar-floating', 'is-visible');
        bar.setAttribute('aria-hidden', 'false');
        if (reposition !== false) requestAnimationFrame(function () { placeFloatingBox(clientX, clientY, nearby.length === 1); });
      } else {
        bar.classList.add('is-visible');
        bar.setAttribute('aria-hidden', 'false');
      }
      barVisible = true;
    }

    function hideBar() {
      barVisible = false;
      bar.classList.remove('location-choice-bar-single');
      bar.setAttribute('aria-hidden', 'true');
      bar.style.transition = 'none';
      bar.classList.remove('is-visible');
      requestAnimationFrame(function () {
        bar.classList.remove('location-choice-bar-floating');
        bar.removeAttribute('style');
      });
    }

    function isMouseOverBar(clientX, clientY) {
      if (!bar.classList.contains('is-visible')) return false;
      var rect = bar.getBoundingClientRect();
      return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
    }

    var canvas = viewer.scene.canvas;
    var canvasRect = canvas.getBoundingClientRect();
    var hoverRaf = null, lastHoverX = -1, lastHoverY = -1;

    function runHoverUpdate(screenX, screenY) {
      canvasRect = canvas.getBoundingClientRect();
      var clientX = canvasRect.left + screenX, clientY = canvasRect.top + screenY;
      var nearby = getLocationsForHover(screenX, screenY);
      if (nearby.length > 0) {
        var anchor = getPinCenterClientPosition(nearby);
        showBar(nearby, anchor ? anchor.clientX : clientX, anchor ? anchor.clientY : clientY, true);
      } else {
        if (!isMouseOverBar(clientX, clientY)) hideBar();
      }
    }

    var moveHandler = new Cesium.ScreenSpaceEventHandler(canvas);
    var lastHoverTime = 0;
    var HOVER_THROTTLE_MS = 80;

    moveHandler.setInputAction(function (movement) {
      if (cameraIsMoving) return;
      var x = movement.endPosition.x, y = movement.endPosition.y;
      if (x === lastHoverX && y === lastHoverY) return;
      lastHoverX = x; lastHoverY = y;
      var now = Date.now();
      if (now - lastHoverTime < HOVER_THROTTLE_MS) return;
      lastHoverTime = now;
      if (hoverRaf) cancelAnimationFrame(hoverRaf);
      hoverRaf = requestAnimationFrame(function () { hoverRaf = null; runHoverUpdate(x, y); });
    }, Cesium.ScreenSpaceEventType.MOUSE_MOVE);

    mapContainer.addEventListener('mouseleave', hideBar);

    document.addEventListener('mousemove', function (e) {
      if (!barVisible || cameraIsMoving) return;
      var rect = canvas.getBoundingClientRect();
      var overCanvas = rect.left <= e.clientX && e.clientX <= rect.right && rect.top <= e.clientY && e.clientY <= rect.bottom;
      if (!isMouseOverBar(e.clientX, e.clientY) && !overCanvas) hideBar();
    });
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
          var fallbackData = [MAPDATA_KK_OSPREY_FALLBACK];
          list = normalizeLocations(locationsJson || null, fallbackData);
          ALL_PINS_FALLBACK.forEach(function (fallbackLoc) {
            if (!list.some(function (l) { return l.id === fallbackLoc.id; })) {
              list.push({
                id: fallbackLoc.id,
                name: fallbackLoc.name,
                description: fallbackLoc.description || '',
                thumbnailUrl: fallbackLoc.thumbnailUrl || '',
                longitude: fallbackLoc.longitude,
                latitude: fallbackLoc.latitude
              });
            }
          });
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