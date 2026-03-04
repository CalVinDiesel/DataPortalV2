/**
 * Admin data portal: overlay sidebar from top (like landing page) with close X button.
 * Run on DOMContentLoaded; works with assets/css/admin-responsive.css.
 */
(function () {
  function init() {
    var wrapper = document.querySelector('.layout-wrapper.layout-content-navbar');
    var menu = document.getElementById('layout-menu');
    if (!wrapper || !menu) return;
    var toggle = document.querySelector('.admin-menu-toggle');
    if (!toggle) return;
    var overlay = wrapper.querySelector('.layout-menu-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'layout-menu-overlay';
      overlay.setAttribute('aria-hidden', 'true');
      wrapper.insertBefore(overlay, wrapper.firstChild);
    }
    function closeMenu() {
      wrapper.classList.remove('layout-menu-open');
      overlay.setAttribute('aria-hidden', 'true');
    }
    function toggleMenu() {
      wrapper.classList.toggle('layout-menu-open');
      var open = wrapper.classList.contains('layout-menu-open');
      overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    }
    toggle.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);
    menu.querySelectorAll('.menu-link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth < 1200) closeMenu();
      });
    });
    /* When user resizes screen larger (>= 1200px), close overlay so normal sidebar shows and X is not needed */
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1200) closeMenu();
    });
    /* Add close (X) button at top-right of sidebar – like landing page */
    var closeBtn = menu.querySelector('.admin-menu-close');
    if (!closeBtn) {
      closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.className = 'admin-menu-close';
      closeBtn.setAttribute('aria-label', 'Close menu');
      closeBtn.innerHTML = '<i class="bx bx-x"></i>';
      closeBtn.addEventListener('click', closeMenu);
      menu.insertBefore(closeBtn, menu.firstChild);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
