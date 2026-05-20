/**
 * Admin data portal: overlay sidebar from top (like landing page) with close X button.
 * Run on DOMContentLoaded; works with assets/css/admin-responsive.css.
 */
(function () {
  function init() {
    var wrapper = document.querySelector('.layout-wrapper.layout-content-navbar');
    var menu = document.getElementById('layout-menu');
    var toggle = document.querySelector('.admin-menu-toggle');
    if (!wrapper || !toggle) return;

    // v176: If menu is missing, create it dynamically from existing nav links
    if (!menu) {
      menu = document.createElement('aside');
      menu.id = 'layout-menu';
      menu.className = 'layout-menu menu-vertical menu bg-menu-theme';
      
      // Create Brand/Logo section for mobile menu
      var brand = document.createElement('div');
      brand.className = 'app-brand demo border-bottom';
      brand.innerHTML = `
        <a href="/" class="app-brand-link">
          <span class="app-brand-text demo menu-text fw-bold ms-2">3DHub Admin</span>
        </a>
      `;
      menu.appendChild(brand);

      // Clone existing navigation links
      var navLinks = document.querySelector('.admin-nav-links');
      if (navLinks) {
        var menuInner = document.createElement('ul');
        menuInner.className = 'menu-inner py-1 mt-3 list-unstyled px-3';
        
        navLinks.querySelectorAll('a').forEach(function(link) {
          var li = document.createElement('li');
          li.className = 'menu-item mb-1';
          var newLink = link.cloneNode(true);
          newLink.className = 'menu-link d-flex align-items-center py-2 px-3 rounded text-decoration-none';
          // Match active state
          if (link.classList.contains('active')) {
             newLink.style.backgroundColor = 'rgba(105, 108, 255, 0.1)';
             newLink.style.color = '#696cff';
             newLink.style.fontWeight = 'bold';
          } else {
             newLink.style.color = 'inherit';
          }
          li.appendChild(newLink);
          menuInner.appendChild(li);
        });
        menu.appendChild(menuInner);
      }
      
      wrapper.appendChild(menu);
    }

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
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1200) closeMenu();
    });
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

// ─── Auto-inject theme switcher into every admin page navbar ───────────────
(function () {
  function injectThemeSwitcher() {
    var navRight = document.querySelector('.navbar-nav-right.d-flex.align-items-center.ms-auto');
    if (!navRight || document.querySelector('.dropdown-style-switcher')) return;

    var switcher = document.createElement('ul');
    switcher.className = 'navbar-nav flex-row align-items-center';
    switcher.innerHTML =
      '<li class="nav-item dropdown-style-switcher dropdown">' +
        '<a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">' +
          '<i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>' +
        '</a>' +
        '<ul class="dropdown-menu dropdown-menu-end">' +
          '<li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light">' +
            '<span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span></button></li>' +
          '<li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark">' +
            '<span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span></button></li>' +
          '<li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system">' +
            '<span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span></button></li>' +
        '</ul>' +
      '</li>';

      navRight.appendChild(switcher);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectThemeSwitcher);
  } else {
    injectThemeSwitcher();
  }
})();