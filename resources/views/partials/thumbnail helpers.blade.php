{{--
  ============================================================
  resources/views/partials/_thumbnail_helpers.blade.php
  ============================================================
  Include this partial ONCE in your layout (e.g. app.blade.php or landing.blade.php)
  BEFORE any location thumbnail images are rendered.

  It provides:
    1. @php  ThumbnailHelper::url($rawUrl)  — PHP-side normalization for Blade <img src="">
    2. A tiny inline script that fixes any already-rendered <img> tags with spaces in src
  ============================================================
--}}

{{-- ① Inline script: fixes <img src> with encoded spaces (%20) or literal spaces on the fly --}}
<script>
(function () {
  /**
   * Normalize a filename in a URL: decode %20, replace spaces with underscores, lowercase.
   * Matches the same logic used in cesium-map-markers.js so thumbnails are consistent.
   */
  function normalizeThumbnailSrc(src) {
    if (!src || src.indexOf('data:') === 0) return src;
    try {
      // Absolute URL: normalize only the filename (last path segment)
      if (src.indexOf('http') === 0) {
        var u = new URL(src);
        var parts = u.pathname.split('/');
        var filename = parts[parts.length - 1];
        try { filename = decodeURIComponent(filename); } catch (e) {}
        filename = filename.replace(/\s+/g, '_').toLowerCase();
        parts[parts.length - 1] = filename;
        u.pathname = parts.join('/');
        return u.toString();
      }
      // Relative path: normalize the filename segment
      var parts = src.split('/');
      var filename = parts[parts.length - 1];
      try { filename = decodeURIComponent(filename); } catch (e) {}
      filename = filename.replace(/\s+/g, '_').toLowerCase();
      parts[parts.length - 1] = filename;
      return parts.join('/');
    } catch (e) {
      return src;
    }
  }

  /**
   * After DOM is ready, scan all <img> tags whose src contains %20 or spaces
   * and rewrite them to use underscores. Handles both static assets and upload URLs.
   */
  function fixAllThumbnailSrcs() {
    var imgs = document.querySelectorAll('img[src]');
    for (var i = 0; i < imgs.length; i++) {
      var img = imgs[i];
      var src = img.getAttribute('src') || '';
      // Only touch images that look like thumbnail paths or uploads
      if (
        (src.indexOf('/locations/') !== -1 || src.indexOf('/uploads/') !== -1) &&
        (src.indexOf('%20') !== -1 || src.indexOf(' ') !== -1)
      ) {
        var fixed = normalizeThumbnailSrc(src);
        if (fixed && fixed !== src) img.setAttribute('src', fixed);
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fixAllThumbnailSrcs);
  } else {
    fixAllThumbnailSrcs();
  }
})();
</script>