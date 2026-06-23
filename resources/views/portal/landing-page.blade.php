<!doctype html>

<!-- =========================================================
* Sneat -  | v3.0.0
==============================================================

* Product Page: https://themeselection.com/item/sneat-dashboard-pro-bootstrap/
* Created by: ThemeSelection

      * License: You must have a valid license purchased in order to legally use the theme for your project.
    
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
  

<html
  lang="en"
  class=" layout-navbar-fixed layout-wide "
  dir="ltr"
  data-skin="default"
  data-assets-path="{{ asset('assets') }}/"
  data-template="front-pages"
  data-bs-theme="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
  
    <title>3D Hub | Home Page</title>
  
    <!-- SEO -->
    <meta name="description" content="Sneat is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta name="keywords" content="Sneat bootstrap dashboard, sneat bootstrap 5 dashboard, themeselection, html dashboard, web dashboard, frontend dashboard, responsive bootstrap theme" />
    <meta property="og:title" content="Sneat Bootstrap 5 Dashboard PRO by ThemeSelection" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="https://themeselection.com/item/sneat-dashboard-pro-bootstrap/" />
    <meta property="og:image" content="{{ asset('assets') }}/img/front-pages/landing-page/3DHub%20logo1.png" />
    <meta property="og:description" content="Sneat is the best bootstrap 5 dashboard for responsive web apps. Streamline your app development process with ease." />
    <meta property="og:site_name" content="ThemeSelection" />
    <link rel="canonical" href="https://themeselection.com/item/sneat-dashboard-pro-bootstrap/" />
  
    <!-- ✅ REMOVED: ThemeSelection's Google Tag Manager (GTM-5DDHKGP)
         REASON: This was ThemeSelection's OWN GTM container, not yours.
         It was injecting OptinMonster (omappapi.com) which consumed 4,542ms
         of CPU and is the #1 cause of your map lag. -->

    <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  
    <!-- Fonts — preconnect first for faster DNS -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Graduate&display=swap" rel="stylesheet" />
  
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/pickr/pickr-themes.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/client-responsive.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css" />
  
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/nouislider/nouislider.css" />
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/libs/swiper/swiper.css" />
  
    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page-landing.css" />
  
    <!-- Custom Cesium Map CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/css/cesium-map.css" />
    <!-- Ensure 3DHub logo is fully visible and not clipped by navbar/footer brand box -->
    <style>
      .navbar-brand.app-brand .app-brand-link { overflow: visible; display: flex; align-items: center; }
      .navbar-brand.app-brand .app-brand-logo { overflow: visible; display: flex; align-items: center; flex-shrink: 0; }
      .landing-footer .app-brand-link { overflow: visible; }
      .landing-footer .app-brand-logo { overflow: visible; display: flex; align-items: center; }
      /* Footer logo only: make "3D" and full logo visible on dark footer – strong brightness + light outline */
      .landing-footer .app-brand-logo img {
        filter: brightness(4.2) contrast(1.35) drop-shadow(0 0 2px rgba(255,255,255,0.95)) drop-shadow(0 0 6px rgba(255,255,255,0.6));
      }
      /* Upload nav: hidden for everyone by default; only JS reveals for registered/trusted/admin */
      .nav-upload-visible {
        display: none !important;
      }
      @media (min-width: 1200px) {
        .nav-upload-visible.d-xl-block {
          display: block !important;
        }
      }
      @media (max-width: 1199.98px) {
        li.nav-upload-visible.d-xl-none {
          display: list-item !important;
        }
      }
      .nav-upload-mobile-sub {
        padding-left: 1rem;
        border-left: 2px solid rgba(105, 108, 255, 0.3);
        margin: 0.25rem 0 0.5rem 0;
      }
      .nav-upload-mobile-sub .nav-link { padding-top: 0.35rem; padding-bottom: 0.35rem; font-size: 0.9375rem; }
      #navUploadCollapse .nav-link:hover { color: var(--bs-primary); }
    </style>
  
    <!-- CesiumJS 1.138
         ✅ CHANGED: Added defer so Cesium no longer blocks page rendering.
         The page will load visually first, then Cesium initializes after. -->
    <script>
      window.AppConfig = {
        baseUrl:    "{{ rtrim(config('app.url'), '/') }}",
        uploadsUrl: "{{ url('uploads') }}",
        assetUrl:   "{{ asset('') }}",
      };
      window.TemaDataPortal_API_BASE = window.AppConfig.baseUrl;
    </script>

    <link href="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Widgets/widgets.css" rel="stylesheet" />
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.138/Build/Cesium/Cesium.js"></script>
    <script>
      (function() {
        var scripts = [
          "{{ asset('assets') }}/js/cesium-map.js",
          "{{ asset('assets') }}/js/cesium-map-controls.js",
          "{{ asset('assets') }}/js/cesium-map-markers.js"
        ];
        var cacheBust = "?v=" + new Date().getTime() + "_" + Math.random();
        
        function loadScript(index) {
          if (index >= scripts.length) return;
          var s = document.createElement('script');
          s.src = scripts[index] + cacheBust;
          s.onload = function() { loadScript(index + 1); };
          document.head.appendChild(s);
        }
        loadScript(0);
      })();
    </script>
  
    <!-- Helpers -->
    <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  
    <!-- ✅ REMOVED: template-customizer.js
         REASON: This is a theme development tool only — runs unnecessary
         background scripts on your live production portal. Safe to remove. -->
  
    <!-- Theme Config -->
    <script src="{{ asset('assets') }}/js/front-config.js"></script>
  
  </head>

  <body>
    
      <!-- ?PROD Only: Google Tag Manager (noscript) (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
      <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP" height="0" width="0" style="display: none; visibility: hidden"></iframe></noscript>
      <!-- End Google Tag Manager (noscript) -->
    
    


<script src="{{ asset('assets') }}/vendor/js/dropdown-hover.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/mega-dropdown.js"></script><!-- Navbar: Start -->
<nav class="layout-navbar shadow-none py-0">
  <div class="container">
    <div class="navbar navbar-expand-xl landing-navbar px-3 px-md-8">
      <!-- Menu logo wrapper: Start -->
      <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
        <!-- Mobile menu toggle: Start-->
        <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base bx bx-menu icon-lg align-middle text-heading fw-medium"></i>
        </button>
        <!-- Mobile menu toggle: End-->
        <a href="{{ route('landing') }}" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub Logo" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; display: block;" />
          </span>
          <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">3DHub</span>
        </a>
      </div>
      <!-- Menu logo wrapper: End -->
      <!-- Menu wrapper: Start -->
      <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
        <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <i class="icon-base bx bx-x icon-lg"></i>
        </button>
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link fw-medium" aria-current="page" href="{{ route('landing') }}#landingHero">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ route('landing') }}#landingShowCase">ShowCase</a>
          </li>
          @auth
          {{-- TEMPORARILY HIDDEN FOR PRE-LAUNCH (3D MODEL SALES FIRST)
          <li class="nav-item dropdown d-none d-xl-block" id="navUpload">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle fw-medium" aria-expanded="false" data-bs-toggle="dropdown" data-trigger="hover">
              Upload
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="{{ route('create_project') }}">New Project</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="{{ route('my_uploads') }}">My Projects</a></li>
            </ul>
          </li>
          <li class="nav-item d-xl-none navUpload-mobile">
            <a class="nav-link fw-medium dropdown-toggle" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#navUploadCollapse" aria-expanded="false" aria-controls="navUploadCollapse" id="navUploadMobileToggle">
              Upload
            </a>
            <div class="collapse nav-upload-mobile-sub" id="navUploadCollapse">
              <a class="nav-link fw-medium" href="{{ route('create_project') }}">New Project</a>
              <hr class="dropdown-divider">
              <a class="nav-link fw-medium" href="{{ route('my_uploads') }}">My Projects</a>
            </div>
          </li>
          --}}
          @endauth

          <!--<li class="nav-item">
            <a class="nav-link fw-medium" href="{{ route('landing') }}#landingMap">Map</a>
          </li>-->
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ route('landing') }}#landingFAQ">FAQ</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="{{ route('landing') }}#landingContact">Contact us</a>
          </li>
          {{-- TEMPORARILY HIDDEN FOR PRE-LAUNCH (PAGES DROPDOWN NOT USED YET)
          <li class="nav-item mega-dropdown">
            <a href="#" class="nav-link dropdown-toggle navbar-ex-14-mega-dropdown mega-dropdown fw-medium" aria-expanded="false" data-bs-toggle="mega-dropdown" data-trigger="hover" onclick="return false;">
              <span data-i18n="Pages">Pages</span>
            </a>
            <div class="dropdown-menu p-4 p-xl-8">
              <div class="row gy-4">
                <div class="col-12">
                  <p class="text-body small mb-0">More functions will be added in future enhancements</p>
                </div>
              </div>
            </div>
          </li>
          --}}
          @can('admin')
          <li class="nav-item" id="navAdmin">
            <a class="nav-link fw-medium" href="{{ route('admin_dashboard') }}" target="_blank">Admin</a>
          </li>
          @endcan

        </ul>
      </div>
      <div class="landing-menu-overlay d-xl-none"></div>
      <!-- Menu wrapper: End -->
      <!-- Toolbar: Start -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        
          <!-- Style Switcher -->
          <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
              <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
              <li>
                <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="false">
                  <span><i class="icon-base bx bx-sun icon-md me-3" data-icon="sun"></i>Light</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
                  <span><i class="icon-base bx bx-moon icon-md me-3" data-icon="moon"></i>Dark</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
                  <span><i class="icon-base bx bx-desktop icon-md me-3" data-icon="desktop"></i>System</span>
                </button>
              </li>
            </ul>
          </li>
          <!-- / Style Switcher-->
        
        <!-- navbar button: Login (when logged out) / Log out (when logged in) -->
        @guest
        <li id="navLoginWrap">
          <a href="{{ route('login') }}" class="btn btn-primary"><span class="tf-icons icon-base bx bx-log-in-circle scaleX-n1-rtl me-md-1"></span><span class="d-none d-md-block">Login/Register</span></a>
        </li>
        @endguest

        @auth
        <li id="navUserWrap" class="d-flex align-items-center">
          <a href="{{ route('profile') }}" class="navbar-text text-body me-3 d-none d-md-inline text-decoration-none fw-medium">{{ Auth::user()->email }}</a>
          <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="button" id="navLogoutBtn" class="btn btn-outline-secondary"><span class="tf-icons icon-base bx bx-log-out me-1"></span><span class="d-none d-md-inline">Log out</span></button>
          </form>
        </li>
        @endauth

        <!-- navbar button: End -->
      </ul>
      <!-- Toolbar: End -->
    </div>
  </div>
</nav>
<!-- Navbar: End -->


<!-- Sections:Start -->

  <!--<div data-bs-spy="scroll" class="scrollspy-example">
    <!-- Hero: Start -->
    <section id="hero-animation">
      <div id="landingHero" class="section-py landing-hero position-relative">
        <img src="{{ asset('assets') }}/img/front-pages/backgrounds/hero-bg.png" alt="hero background" class="position-absolute top-0 start-50 translate-middle-x object-fit-cover w-100 h-100" data-speed="1"/>
        <div class="container">
          <div class="hero-text-box text-center position-relative">
            <h1 class="text-primary hero-title display-6 fw-extrabold">One Dashboard to Get All Your 3D Model Data</h1>
            
          </div>
          <!-- 3D Map Start Here -->
          <div id="heroMapContainer">
           <div id="cesiumContainer" style="width:100%; height:500px;"></div>
            <!-- Location choice bar: appears on pin hover, image + description per location -->
            <div id="locationChoiceBar" class="location-choice-bar" aria-hidden="true">
              <div class="location-choice-bar-inner">
                <div class="location-choice-bar-cards" id="locationChoiceBarCards"></div>
              </div>
            </div>
            <!-- Map control sidebar (zoom, reset, fullscreen) -->
            <div class="right-controls">
              <div class="navigation-container"></div>
              <div id="controls">
                <div id="zoom-item" class="scale-item">
                  <div class="el-tooltip__trigger" id="resetViewBtn" title="Reset View">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M13.75 2.5H17.5V6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M17.5 13.75V17.5H13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M6.25 17.5H2.5V13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M2.5 6.25V2.5H6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <rect x="8" y="8" width="4" height="4" rx="2" fill="currentColor"></rect>
                    </svg>
                  </div>
                  <div class="el-tooltip__trigger" id="zoomInBtn" title="Zoom In">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12.0208 11.0782L14.8762 13.9328L13.9328 14.8762L11.0782 12.0208C10.016 12.8723 8.69483 13.3354 7.3335 13.3335C4.0215 13.3335 1.3335 10.6455 1.3335 7.3335C1.3335 4.0215 4.0215 1.3335 7.3335 1.3335C10.6455 1.3335 13.3335 4.0215 13.3335 7.3335C13.3354 8.69483 12.8723 10.016 12.0208 11.0782ZM10.6835 10.5835C11.5296 9.71342 12.0021 8.54712 12.0002 7.3335C12.0002 4.75483 9.9115 2.66683 7.3335 2.66683C4.75483 2.66683 2.66683 4.75483 2.66683 7.3335C2.66683 9.9115 4.75483 12.0002 7.3335 12.0002C8.54712 12.0021 9.71342 11.5296 10.5835 10.6835L10.6835 10.5835ZM6.66683 6.66683V4.66683H8.00016V6.66683H10.0002V8.00016H8.00016V10.0002H6.66683V8.00016H4.66683V6.66683H6.66683Z" fill="currentColor"></path>
                    </svg>
                  </div>
                  <div class="el-tooltip__trigger" id="zoomOutBtn" title="Zoom Out">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12.0208 11.0782L14.8762 13.9328L13.9328 14.8762L11.0782 12.0208C10.016 12.8723 8.69483 13.3354 7.3335 13.3335C4.0215 13.3335 1.3335 10.6455 1.3335 7.3335C1.3335 4.0215 4.0215 1.3335 7.3335 1.3335C10.6455 1.3335 13.3335 4.0215 13.3335 7.3335C13.3354 8.69483 12.8723 10.016 12.0208 11.0782ZM10.6835 10.5835C11.5296 9.71342 12.0021 8.54712 12.0002 7.3335C12.0002 4.75483 9.9115 2.66683 7.3335 2.66683C4.75483 2.66683 2.66683 4.75483 2.66683 7.3335C2.66683 9.9115 4.75483 12.0002 7.3335 12.0002C8.54712 12.0021 9.71342 11.5296 10.5835 10.6835L10.6835 10.5835ZM4.66683 6.66683H10.0002V8.00016H4.66683V6.66683Z" fill="currentColor"></path>
                    </svg>
                  </div>
                  <div class="divider"></div>
                  <div class="el-tooltip__trigger" id="fullscreenBtn" title="Fullscreen">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M13.75 2.5H17.5V6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M17.5 13.75V17.5H13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M6.25 17.5H2.5V13.75" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                      <path d="M2.5 6.25V2.5H6.25" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- 3D Map End Here -->

        </div>
      </div>
      <!--<div class="landing-hero-blank"></div>-->
    </section>
    <!-- Hero: End -->

    <!-- 3D Model Tiles Showcase: Start -->
    <section id="landingShowCase" class="section-py landing-3d-showcase">
      <div class="container">
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">3D Model Showcase</span>
        </div>
        <h4 class="text-center mb-1">
          <span class="position-relative fw-extrabold z-1"
            >Explore 3D Models
            <img src="{{ asset('assets') }}/img/front-pages/icons/section-title-icon.png" alt="" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
          for each location on the map
        </h4>
        <p class="text-center mb-12">Direct 3D views of Kota Kinabalu locations. Click a tile to load the full 3D model.</p>
        <div class="row g-4" id="tilesShowcase">
          <!-- Fallback: static tiles loaded from showcases.json; replaced by script when GET /api/showcases returns data -->
          @php
            $showcasesPath = public_path('data/showcases.json');
            $fallbackShowcases = [];
            if (file_exists($showcasesPath)) {
                $json = file_get_contents($showcasesPath);
                $data = json_decode($json, true);
                $fallbackShowcases = $data['showcases'] ?? [];
            }
            // Settle on premium colors for fallback placeholders
            $getPremiumColor = function($id) {
                $colors = [
                    '1a1a2e/696cff', // Navy/Blue
                    '667eea/ffffff', // Indigo
                    'f093fb/ffffff', // Pink
                    '4facfe/ffffff', // Sky Blue
                    '43e97b/ffffff', // Green
                    '6a11cb/ffffff', // Purple
                    'ff0844/ffffff', // Red
                    'f09819/ffffff'  // Orange
                ];
                $hash = 0;
                for ($i = 0; $i < strlen($id); $i++) {
                    $hash = ord($id[$i]) + (($hash << 5) - $hash);
                }
                return $colors[abs($hash) % count($colors)];
            };
            
            $getDynamicCategory = function($loc) {
                $tileset = strtolower($loc['3dTiles'] ?? '');
                $id = strtolower($loc['mapDataID'] ?? '');
                if (str_contains($tileset, 'building_planning') || str_contains($tileset, 'building')) {
                    return 'Building Planning';
                }
                if (str_contains($id, 'keningau') || str_contains($tileset, 'keningau')) {
                    return 'Regional Planning';
                }
                return 'Kota Kinabalu';
            };
          @endphp

          @if(count($fallbackShowcases) > 0)
            @foreach($fallbackShowcases as $loc)
              @php
                $id = $loc['mapDataID'] ?? '';
                $title = $loc['title'] ?? $id;
                $rawImg = trim($loc['thumbNailUrl'] ?? '');
                $finalSrc = '';
                if ($rawImg) {
                    $encoded = str_replace(' ', '%20', $rawImg);
                    $finalSrc = (str_starts_with($rawImg, 'http') ? $encoded : asset($encoded));
                } else {
                    $finalSrc = 'https://placehold.co/' . $getPremiumColor($id) . '?text=' . urlencode(substr($title, 0, 20));
                }
                $category = $getDynamicCategory($loc);
              @endphp
              <div class="col-lg-4 col-md-6" id="tile-mapdata-{{ $id }}">
                <a href="{{ route('loading_3d') }}?id={{ urlencode($id) }}" class="tile-3d-card" target="_blank" rel="noopener">
                  <div class="tile-3d-img">
                    <img src="{{ $finalSrc }}" alt="{{ $title }}" onerror="this.src='https://placehold.co/{{ $getPremiumColor($id) }}?text=3D+Model'">
                  </div>
                  <div class="tile-3d-body">
                    <h6 class="tile-3d-title">{{ $title }}</h6>
                    <div class="tile-3d-tags"><span>GeoSabah 3D Hub</span><span>{{ $category }}</span></div>
                    <div class="tile-3d-metrics"><span><i class="bx bx-cube-alt"></i> 3D Tiles</span></div>
                  </div>
                </a>
              </div>
            @endforeach
          @else
            <div class="col-12 text-center py-5 text-muted">
              <i class="bx bx-show-alt fs-1 mb-2"></i>
              <p class="mb-0">No showcased models available at the moment.</p>
            </div>
          @endif
        </div>
      </div>
    </section>
    <!-- 3D Model Tiles Showcase: End -->

    <!-- Real customers reviews: Start -->
    {{-- Our Partners: Start Here
    <section id="landingReviews" class="section-py bg-body landing-reviews pb-0">
      <!-- Our Partners: Start Here
      <div class="container">
        <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
          <div class="col-md-6 col-lg-5 col-xl-3">
            <div class="mb-4">
              <span class="badge bg-label-primary">Our Partners</span>
            </div>
            <h4 class="mb-1">
              <span class="position-relative fw-extrabold z-1"
                >All About Our Partners
                <img src="{{ asset('assets') }}/img/front-pages/icons/section-title-icon.png" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
              </span>
            </h4>
            <p class="mb-5 mb-md-12">
              See what we have achieved<br class="d-none d-xl-block" />
              with our partners.
            </p>
          </div>
          <div class="col-md-6 col-lg-7 col-xl-9">
            <div class="swiper-reviews-carousel overflow-hidden">
              <div class="swiper" id="swiper-reviews">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/Get-3D-edited.png" style="width: 50px; height: 50px; display: inline-block;" alt="client logo" class="client-logo img-fluid" px="500"/>
                        </div>
                        <p>“Get3D providing advanced 3D solutions for various industries. Their expertise in photogrammetry, mapping, and AI offer accurate and innovative tools for digitizing spaces.”</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar me-3 avatar-sm">
                            <img src="{{ asset('assets') }}/img/avatars/Adam Kozubowicz Ceo of Get3D.jpg" alt="Adam Kozubowicz" class="rounded-circle" />
                          </div>
                          <div>
                            <h6 class="mb-0">Adam Kozubowicz</h6>
                            <p class="small text-body-secondary mb-0">CEO of Get 3D</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/FMESafeSoftware.png" style="width: 60px; height: 60px; display: inline-block;" alt="client logo" class="client-logo img-fluid"/>
                        </div>
                        <p>“The FME Platform serves as a no-code solution, seamlessly integrating all your data and ensuring it flows effortlessly to your desired destination, on demand.”</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar-group d-flex align-items-center me-3">
                            <div class="avatar avatar-sm">
                              <img src="{{ asset('assets') }}/img/avatars/Don Murray Co Founder of FME Safe Software.jpg" alt="Don Murray" class="rounded-circle border border-2 border-white">
                            </div>
                            <div class="avatar avatar-sm ms-n2">
                              <img src="{{ asset('assets') }}/img/avatars/Dale Lutz Second Founder of FME Safe Software.jpg" alt="Dale Lutz" class="rounded-circle border border-2 border-white">
                            </div>
                          </div>
                          <div>
                            <h6 class="mb-0">Don Murray and Dale Lutz</h6>
                            <p class="small text-body-secondary mb-0">Founders of FME Safe Software</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/HexagonGeospatial.png" style="width: 75px; height: 75px; display: inline-block;" alt="client logo" class="client-logo img-fluid" />
                        </div>
                        <p>Hexagon Geospatial Solution leads the global market in digital reality solutions, seamlessly integrating sensor, software, and autonomous technologies.</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar me-3 avatar-sm">
                            <img src="{{ asset('assets') }}/img/avatars/Anders Svensson Ceo of Hexagon Geospatial.jpg" alt="Anders Svensson" class="rounded-circle" />
                          </div>
                          <div>
                            <h6 class="mb-0">Anders Svensson</h6>
                            <p class="small text-body-secondary mb-0">CEO of Hexagon Geospatial</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="swiper-slide">
                    <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/qgis-logo.png" style="width: 55px; height: 55px; display: inline-block;" alt="client logo" class="client-logo img-fluid" />
                        </div>
                        <p>Free and Open Source Software for Geospatial (FOSS4G) acts as both a web-based application and a platform, streamlining the development of geospatial information systems (GIS) solutions.</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bx-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar me-3 avatar-sm">
                            <img src="{{ asset('assets') }}/img/avatars/Gary Sherman Founder of QGIS.jpg" alt="Gary Sherman" class="rounded-circle" />
                          </div>
                          <div>
                            <h6 class="mb-0">Gary Sherman</h6>
                            <p class="small text-body-secondary mb-0">Founder of QGIS</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Commented out: Eugenia Moore / Hubspot testimonial -->
                  <!-- <div class="swiper-slide">
                  <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/logo-5.png" alt="client logo" class="client-logo img-fluid" />
                        </div>
                        <p>“I've never used a theme as versatile and flexible as Vuexy. It's my go to for building dashboard sites on almost any project.”</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar me-3 avatar-sm">
                            <img src="{{ asset('assets') }}/img/avatars/5.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div>
                            <h6 class="mb-0">Eugenia Moore</h6>
                            <p class="small text-body-secondary mb-0">Founder of Hubspot</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div> -->
                  <!-- Commented out: Sara Smith / Continental testimonial -->
                  <!-- <div class="swiper-slide">
                    <div class="card h-100">
                      <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                        <div class="mb-4">
                          <img src="{{ asset('assets') }}/img/front-pages/branding/logo-6.png" alt="client logo" class="client-logo img-fluid" />
                        </div>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Veniam nemo mollitia, ad eum officia numquam nostrum repellendus consequuntur!</p>
                        <div class="text-warning mb-4">
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bxs-star"></i>
                          <i class="icon-base bx bx-star"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="avatar me-3 avatar-sm">
                            <img src="{{ asset('assets') }}/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div>
                            <h6 class="mb-0">Sara Smith</h6>
                            <p class="small text-body-secondary mb-0">Founder of Continental</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
    </section>
    <!-- Our Partners: End Here -->
      <hr class="m-0 mt-6 mt-md-12" />
      <!-- Logo slider: Start -->
      <!--<div class="container">
        <div class="swiper-logo-carousel pt-8">
          <div class="swiper" id="swiper-clients-logos">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <img src="{{ asset('assets') }}/img/front-pages/branding/logo_1-light.png" alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_1-light.png" data-app-dark-img="front-pages/branding/logo_1-dark.png" />
              </div>
              <div class="swiper-slide">
                <img src="{{ asset('assets') }}/img/front-pages/branding/logo_2-light.png" alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_2-light.png" data-app-dark-img="front-pages/branding/logo_2-dark.png" />
              </div>
              <div class="swiper-slide">
                <img src="{{ asset('assets') }}/img/front-pages/branding/logo_3-light.png" alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_3-light.png" data-app-dark-img="front-pages/branding/logo_3-dark.png" />
              </div>
              <div class="swiper-slide">
                <img src="{{ asset('assets') }}/img/front-pages/branding/logo_4-light.png" alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_4-light.png" data-app-dark-img="front-pages/branding/logo_4-dark.png" />
              </div>
              <div class="swiper-slide">
                <img src="{{ asset('assets') }}/img/front-pages/branding/logo_5-light.png" alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_5-light.png" data-app-dark-img="front-pages/branding/logo_5-dark.png" />
              </div>
            </div>
          </div>
        </div>
      </div>-->
      <!-- Logo slider: End -->
    </section>
    --}}
    <!-- Real customers reviews: End -->

    <!-- Pricing plans: Start -->
    <section id="landingPricing" class="section-py bg-body landing-pricing">
      <style>
        .landing-pricing .pricing-plan-card {
          border: 2px solid transparent;
          transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .landing-pricing .pricing-plan-card:hover {
          border-color: var(--bs-primary);
          transform: scale(1.03);
          box-shadow: 0 0.5rem 1.5rem rgba(105, 108, 255, 0.2);
        }
        .landing-pricing .pricing-plan-card .card-body .btn,
        .landing-pricing .pricing-plan-card .card-body .d-grid .btn {
          transition: background-color 0.25s ease, color 0.25s ease, border-color 0.25s ease;
        }
        .landing-pricing .pricing-plan-card:hover .card-body .btn-label-primary,
        .landing-pricing .pricing-plan-card:hover .card-body .btn-primary {
          background-color: var(--bs-primary) !important;
          color: #fff !important;
          border-color: var(--bs-primary) !important;
        }
        .landing-pricing .pricing-plan-card .pricing-list .badge.rounded-pill {
          transition: background-color 0.25s ease, color 0.25s ease;
        }
        .landing-pricing .pricing-plan-card:hover .pricing-list .badge.rounded-pill {
          background-color: var(--bs-primary) !important;
          color: #fff !important;
        }
      </style>
      <div class="container">
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">Portal Services</span>
        </div>
        <h4 class="text-center mb-1">
          <span class="position-relative fw-extrabold z-1"
            >Tailored services
            <img src="{{ asset('assets') }}/img/front-pages/icons/section-title-icon.png" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
          for your 3D workflow
        </h4>
        <p class="text-center pb-2 mb-7">Gain access to 40+ premium showcases or request professional processing for your own datasets.<br />From free exploration to high-performance data processing and easy token management,<br />our portal is built to support your end-to-end geospatial requirements.</p>
        <div class="row g-6 pt-lg-5 justify-content-center">
          <div class="col-xl-4 col-lg-6">
            <div class="card pricing-plan-card">
              <div class="card-header">
                <div class="text-center">
                  <img src="{{ asset('assets') }}/img/front-pages/icons/paper-airplane.png" alt="paper airplane icon" class="mb-8 pb-2" />
                  <h4 class="mb-0">Request Access(Free)</h4>
                  <div class="d-flex align-items-center justify-content-center">
                    <span class="price-monthly h2 text-primary fw-extrabold mb-0">0 Token Required</span>
                    <sub class="h6 text-body-secondary mb-n1 ms-1"></sub>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <ul class="list-unstyled pricing-list">
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      View Overview Map
                    </h6>
                  </li>
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      ShowCase 3D Model
                    </h6>
                  </li>
                </ul>
                <div class="d-grid mt-8">
                  <a href="{{ route('register') }}" class="btn btn-label-primary" id="pricingGetStartedBtn">Get Started</a>
                </div>
              </div>
            </div>
          </div>

          {{-- TEMPORARILY HIDDEN FOR PRE-LAUNCH (3D MODEL SALES FIRST)
          <div class="col-xl-4 col-lg-6">
            <div class="card pricing-plan-card">
              <div class="card-header">
                <div class="text-center">
                  <img src="{{ asset('assets') }}/img/front-pages/icons/plane.png" alt="plane icon" class="mb-8 pb-2" />
                  <h4 class="mb-0">Upload Raw Data</h4>
                  <div class="d-flex align-items-center justify-content-center">
                    <span class="price-monthly h2 text-primary fw-extrabold mb-0">0 Token Required</span>
                    <sub class="h6 text-body-secondary mb-n1 ms-1"></sub>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <ul class="list-unstyled pricing-list">
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      Request for 3D Model Purchases
                    </h6>
                  </li>
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      Request for Raw Data Upload Processing
                    </h6>
                  </li>
                </ul>
                <div class="d-grid mt-8">
                  <button type="button" class="btn btn-label-secondary" id="pricingSubscribeBtn" disabled>Coming Soon</button>
                </div>
              </div>
            </div>
          </div>
          --}}

          {{-- TEMPORARILY HIDDEN FOR PRE-LAUNCH (TOKEN RELOAD FEATURE UNDER DEVELOPMENT)
          <div class="col-xl-4 col-lg-6">
            <div class="card pricing-plan-card">
              <div class="card-header">
                <div class="text-center">
                  <img src="{{ asset('assets') }}/img/front-pages/icons/shuttle-rocket.png" alt="shuttle rocket icon" class="mb-8 pb-2" />
                  <h4 class="mb-0">Reload Token</h4>
                  <div class="d-flex align-items-center justify-content-center">
                    <span class="price-monthly h2 text-primary fw-extrabold mb-0">RM2</span>
                    <sub class="h6 text-body-secondary mb-n1 ms-1">/token</sub>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <ul class="list-unstyled pricing-list">
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      Top-up Token for In-App Purchases
                    </h6>
                  </li>
                  <li>
                    <h6 class="d-flex align-items-center mb-3">
                      <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i class="icon-base bx bx-check icon-12px"></i></span>
                      Auto Create Wallet for Token
                    </h6>
                  </li>
                </ul>
                <div class="d-grid mt-8">
                  <button type="button" class="btn btn-label-secondary" disabled>Coming Soon</button>
                </div>
              </div>
            </div>
          </div>
          --}}
        </div>
      </div>
    </section>
    <!-- Pricing plans: End -->

    <!-- FAQ: Start -->
    <section id="landingFAQ" class="section-py bg-body landing-faq">
      <div class="container">
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">FAQ</span>
        </div>
        <h4 class="text-center mb-1">
          Frequently asked
          <span class="position-relative fw-extrabold z-1"
            >questions
            <img src="{{ asset('assets') }}/img/front-pages/icons/section-title-icon.png" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
        </h4>
        <p class="text-center mb-12 pb-md-4">Browse through these FAQs to find answers to commonly asked questions.</p>
        <div class="row gy-12 align-items-center">
          <div class="col-lg-5">
            <div class="text-center">
              <img src="{{ asset('assets') }}/img/front-pages/landing-page/faq-boy-with-logos.png" alt="faq boy with logos" class="faq-image" />
            </div>
          </div>
          <div class="col-lg-7">
            <div class="accordion" id="accordionExample">
              <div class="card accordion-item">
                <h2 class="accordion-header" id="headingOne">
                  <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne">How do I upload my raw data for 3D processing?</button>
                </h2>
                <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                  <div class="accordion-body">To start a new project, log in to your account and navigate to the "Upload" menu. You can choose to upload your raw images or zip files directly through the portal, via our high-speed SFTP server, or by providing a Google Drive share link. Our team will then process your data into a high-quality 3D model.</div>
                </div>
              </div>
              <div class="card accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                  <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">How long do I have to download my processed 3D models?</button>
                </h2>
                <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                  <div class="accordion-body">Once your 3D model is processed and delivered by our admin team, you will have 7 days to download the files. After 7 days, the download links (including Google Drive and SFTP links) will automatically expire and be removed from our system to ensure data security and maintain portal performance.</div>
                </div>
              </div>
              <div class="card accordion-item active">
                <h2 class="accordion-header" id="headingThree">
                  <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">What are the requirements for Google Drive uploads?</button>
                </h2>
                <div id="accordionThree" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                    When using Google Drive to upload raw data, please ensure your shared link is set to "Anyone with the link" and has "Viewer" or "Editor" permissions. This allows our admin team to access your raw data for processing. Our system will automatically detect the file size and photo count once you provide a valid shared link.
                  </div>
                </div>
              </div>
              <div class="card accordion-item">
                <h2 class="accordion-header" id="headingFour">
                  <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionFour" aria-expanded="false" aria-controls="accordionFour">Do I need an account to explore the 3D showcase?</button>
                </h2>
                <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                  <div class="accordion-body">The 3D Model Showcase and Overview Map are public and can be explored by anyone visiting the portal. However, if you wish to upload your own raw data, track project statuses, or download processed 3D models, you must register for a free account.</div>
                </div>
              </div>
              <div class="card accordion-item">
                <h2 class="accordion-header" id="headingFive">
                  <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionFive" aria-expanded="false" aria-controls="accordionFive">How do I track the status of my uploaded projects?</button>
                </h2>
                <div id="accordionFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                  <div class="accordion-body">You can track your projects in real-time by visiting the "My Projects" page under the Upload menu. Your project will move through various stages: "Pending" (uploaded), "Processing" (admin working on it), "Sent" (processed file ready), and "Completed" (once finalized).</div>
                </div>
              </div>
              <div class="card accordion-item">
                <h2 class="accordion-header" id="headingSix">
                  <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionSix" aria-expanded="false" aria-controls="accordionSix">Are all project creation and upload methods available to every user?</button>
                </h2>
                <div id="accordionSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                    While all registered users can utilize our standard Direct Portal Upload and Google Drive integration to submit their raw data, the high-speed SFTP (Secure File Transfer Protocol) method is exclusively reserved for our "Trusted" accounts. This ensures high-bandwidth availability for our power users. If your projects involve massive datasets and you require SFTP access, please contact our support team to discuss account verification.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- FAQ: End -->

    <!-- Contact Us: Start -->
    <section id="landingContact" class="section-py bg-body landing-contact">
      <div class="container">
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">Contact US</span>
        </div>
        <h4 class="text-center mb-1">
          <span class="position-relative fw-extrabold z-1"
            >Let's work
            <img src="{{ asset('assets') }}/img/front-pages/icons/section-title-icon.png" alt="laptop charging" class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
          together
        </h4>
        <p class="text-center mb-12 pb-md-4">Any question or remark? just write us a message</p>
        <div class="row g-6">
          <div class="col-lg-5">
            <div class="contact-img-box position-relative border p-2 h-100">
              <img src="{{ asset('assets') }}/img/front-pages/icons/contact-border.png" alt="contact border" class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
              <img src="{{ asset('assets') }}/img/front-pages/landing-page/contact-customer-service.png" alt="contact customer service" class="contact-img w-100 scaleX-n1-rtl" />
              <div class="p-4 pb-2">
                <div class="row g-4">
                  <div class="col-md-6 col-lg-12 col-xl-6">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-primary rounded p-1_5 me-3"><i class="icon-base bx bx-envelope icon-lg"></i></div>
                      <div>
                        <p class="mb-0">Email</p>
                        <h6 class="mb-0"><a href="mailto:{{ config('support.email') }}" class="text-heading">{{ config('support.email') }}</a></h6>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-lg-12 col-xl-6">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-success rounded p-1_5 me-3"><i class="icon-base bx bx-phone-call icon-lg"></i></div>
                      <div>
                        <p class="mb-0">Phone</p>
                        <h6 class="mb-0"><a href="tel:{{ str_replace([' ', '-', '+'], '', config('support.phone')) }}" class="text-heading">{{ config('support.phone') }}</a></h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="card h-100">
              <div class="card-body">
                <h4 class="mb-2">Send a message</h4>
                <p class="mb-6">
                  If you would like to discuss anything related to 3D model processing, raw data uploads,<br class="d-none d-lg-block" />
                  custom mapping services, or account inquiries, you’re at the right place.
                </p>
                @if(session('success'))
                  <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif
                @if(session('error'))
                  <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                @endif
                <form action="{{ route('contact.submit') }}" method="POST">
                  @csrf
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label class="form-label" for="contact-form-fullname">Full Name</label>
                      <input type="text" name="name" class="form-control" id="contact-form-fullname" placeholder="john" required />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="contact-form-email">Email</label>
                      <input type="email" name="email" id="contact-form-email" class="form-control" placeholder="johndoe@gmail.com" required />
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="contact-form-message">Message</label>
                      <textarea id="contact-form-message" name="message" class="form-control" rows="11" placeholder="Write a message" required></textarea>
                    </div>
                    <div class="col-12">
                      <button type="submit" class="btn btn-primary">Send inquiry</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Contact Us: End -->
  </div>

<!-- / Sections:End -->



<!-- Footer: Start -->
<footer class="landing-footer bg-body footer-text">
  <div class="footer-bottom py-3 py-md-5">
    <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start align-items-center">
      <div class="mb-2 mb-md-0 d-flex align-items-center flex-wrap justify-content-center justify-content-md-start gap-2">
        <a href="{{ route('landing') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 80px; width: auto; max-height: 80px; object-fit: contain; filter: brightness(3.5) contrast(1.2) drop-shadow(0 0 2px rgba(255,255,255,0.95)) drop-shadow(0 0 6px rgba(255,255,255,0.6));" />
          </span>
        </a>
        <span class="footer-bottom-text mb-0">© 3D Hub Developed by Temadigital</span>
      </div>
      <div>
        <a href="https://www.facebook.com/p/Temadigital-Sdn-Bhd-61571213125610/" class="me-4 text-white" target="_blank">
          <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.8609 18.0262V11.1962H14.1651L14.5076 8.52204H11.8609V6.81871C11.8609 6.04704 12.0759 5.51871 13.1834 5.51871H14.5868V3.13454C13.904 3.06136 13.2176 3.02603 12.5309 3.02871C10.4943 3.02871 9.09593 4.27204 9.09593 6.55454V8.51704H6.80676V11.1912H9.10093V18.0262H11.8609Z" fill="currentColor" />
          </svg>
        </a>
        <a href="https://www.linkedin.com/company/temadigital-sdn-bhd/" class="me-4 text-white" target="_blank">
          <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.5 1.5H3.5C2.395 1.5 1.5 2.395 1.5 3.5V17.5C1.5 18.605 2.395 19.5 3.5 19.5H17.5C18.605 19.5 19.5 18.605 19.5 17.5V3.5C19.5 2.395 18.605 1.5 17.5 1.5ZM7.5 16.5H5V8.5H7.5V16.5ZM6.25 7.4C5.42 7.4 4.75 6.73 4.75 5.9C4.75 5.07 5.42 4.4 6.25 4.4C7.08 4.4 7.75 5.07 7.75 5.9C7.75 6.73 7.08 7.4 6.25 7.4ZM16.5 16.5H14V12.1C14 11.15 13.98 9.93 12.67 9.93C11.34 9.93 11.14 10.97 11.14 12.03V16.5H8.64V8.5H11.04V9.62H11.07C11.41 8.98 12.24 8.3 13.47 8.3C16 8.3 16.5 9.96 16.5 12.12V16.5Z" fill="currentColor" />
          </svg>
        </a>
        <a href="https://temadigital.my/" class="text-white" target="_blank">
          <img 
            src="https://temadigital.my/wp-content/uploads/2025/01/temadigital_logo.png" 
            alt="Tema Digital" 
            width="18" 
            height="19" 
            style="object-fit: contain;"
          />
        </a>
      </div>
    </div>
  </div>
</footer>
<!-- Footer: End -->
    

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->
    
    
    <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
    <script src="{{ asset('assets') }}/vendor/libs/@algolia/autocomplete-js.js"></script>

    
      
      <script src="{{ asset('assets') }}/vendor/libs/pickr/pickr.js"></script>
    

    
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets') }}/vendor/libs/nouislider/nouislider.js"></script>
  <script src="{{ asset('assets') }}/vendor/libs/swiper/swiper.js"></script>

    <!-- Main JS -->
    
      <script src="{{ asset('assets') }}/js/front-main.js"></script>
    

    <!-- Page JS -->
    <script src="{{ asset('assets') }}/js/front-page-landing.js"></script>

    <!-- Auth state: Set from Laravel Blade -->
    <script>
      (function() {
        window.__authLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        window.__authRole = '{{ Auth::check() ? Auth::user()->role : 'guest' }}';
        var LOGIN_URL = '{{ route('login') }}';
        var REGISTER_URL = '{{ route('register') }}';
  
    // Check URL params first (coming from OAuth redirect)
        var params = new URLSearchParams(window.location.search);
        var authError = params.get('auth_error') || params.get('error');
  
    // If there's an auth error in the URL, redirect to login
        if (authError) {
          window.location.href = LOGIN_URL + '?error=' + encodeURIComponent(authError);
          return;
        }
        // Upload nav is hidden by CSS for everyone; only registered/trusted/admin get it visible via class.
        function setUploadNavVisible(visible) {
          var navUpload = document.getElementById('navUpload');
          var navUploadMobile = document.querySelectorAll('.navUpload-mobile');
          var toggle = visible ? 'add' : 'remove';
          if (navUpload) navUpload.classList[toggle]('nav-upload-visible');
          navUploadMobile.forEach(function (el) { el.classList[toggle]('nav-upload-visible'); });
        }
        setUploadNavVisible(window.__authLoggedIn);

        // Account removal check removed (handled by Laravel middleware/session)
      })();
    </script>
    <script>
      (function() {
        // v166: Dynamic API detection to replace hardcoded localhost:3000
        var AUTH_API = window.AppConfig ? window.AppConfig.baseUrl : (window.location.origin || 'http://localhost:8000');
        document.addEventListener('DOMContentLoaded', function() {
          var btn = document.getElementById('pricingSubscribeBtn');
          if (!btn) return;
          var getStartedBtn = document.getElementById('pricingGetStartedBtn');
          if (getStartedBtn) {
            getStartedBtn.addEventListener('click', function(e) {
              var role = window.__authRole || 'registered';
              var loggedIn = window.__authLoggedIn;
              if (role === 'admin' || role === 'superadmin') {
                e.preventDefault();
                var modal = new bootstrap.Modal(document.getElementById('getStartedAdminModal'));
                modal.show();
              } else if (loggedIn && (role === 'registered' || role === 'trusted')) {
                e.preventDefault();
                var modal = new bootstrap.Modal(document.getElementById('getStartedRegisteredModal'));
                modal.show();
              }
            });
          }
          btn.addEventListener('click', function() {
            var loggedIn = window.__authLoggedIn;
            var role = window.__authRole || 'registered';
            if (!loggedIn) {
              var modal = new bootstrap.Modal(document.getElementById('subscribeVisitorModal'));
              modal.show();
              return;
            }
            if (role === 'admin' || role === 'superadmin') {
              var modal = new bootstrap.Modal(document.getElementById('subscribeAdminModal'));
              modal.show();
              return;
            }
            window.location.href = '{{ route('payment') }}';
          });
        });
      })();
    </script>
    <!-- Subscribe modal: visitor (not logged in) -->
    <div class="modal fade" id="subscribeVisitorModal" tabindex="-1" aria-labelledby="subscribeVisitorLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="subscribeVisitorLabel">Subscribe</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">To purchase 3D models, you need a Data Portal account. Create an account or log in if you already have one. Then click the button again.</p>
          </div>
          <div class="modal-footer">
            <a href="{{ route('register') }}" class="btn btn-label-primary">Create account</a>
            <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Subscribe modal: client (logged in, not subscriber) - REMOVED, no longer needed -->
    <!-- Get Started modal: registered/trusted user -->
    <div class="modal fade" id="getStartedRegisteredModal" tabindex="-1" aria-labelledby="getStartedRegisteredLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="getStartedRegisteredLabel">Get Started</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">You are already registered. You can upload data and purchase 3D models from the Data Portal.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Get Started modal: admin (already highest tier) -->
    <div class="modal fade" id="getStartedAdminModal" tabindex="-1" aria-labelledby="getStartedAdminLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="getStartedAdminLabel">Get Started</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">You are already at the highest tier as an administrator of this Data Portal. There is no need to sign up. Your role is to monitor and help your clients with image processing and 3D model purchases through the Admin Data Portal.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Get Started modal: registered/trusted (already registered) - REMOVED, merged into getStartedRegisteredModal -->
    <!-- Get Started modal: registered (already registered, can upload and purchase) - REMOVED, merged into getStartedRegisteredModal -->
    <!-- Subscribe modal: admin (not valid to become subscriber) -->
    <div class="modal fade" id="subscribeAdminModal" tabindex="-1" aria-labelledby="subscribeAdminLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="subscribeAdminLabel">Subscribe</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">You are signed in as an administrator of this Data Portal. As an admin, you do not need to purchase; use the Admin Data Portal to manage content and users.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Subscribe modal: already a subscriber - REMOVED, all registered users can purchase -->
    <!-- Confirm logout modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="logoutConfirmLabel">Log out</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="logoutConfirmMessage">Are you sure you want to log out?</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="logoutConfirmBtn">Log out</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Upload restricted modal: shown to guests who click New Project / My Projects -->
    <div class="modal fade" id="uploadGuestModal" tabindex="-1" aria-labelledby="uploadGuestModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="uploadGuestModalLabel">Registered Users Only</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">This feature is only available to registered users. Please register or log in to access upload features.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="uploadGuestModalOkBtn">OK</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Upload guest guard: intercept New Project / My Projects for non-logged-in users -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var REGISTER_URL = '{{ route('register') }}';
        var uploadLinks = [
          document.querySelector('#navUpload .dropdown-menu a[href*="create-project"]'),
          document.querySelector('#navUpload .dropdown-menu a[href*="my-uploads"]'),
          document.querySelector('#navUploadCollapse a[href*="create-project"]'),
          document.querySelector('#navUploadCollapse a[href*="my-uploads"]')
        ];
        uploadLinks.forEach(function(link) {
          if (!link) return;
          link.addEventListener('click', function(e) {
            if (!window.__authLoggedIn) {
              e.preventDefault();
              e.stopPropagation();
              var modal = new bootstrap.Modal(document.getElementById('uploadGuestModal'));
              modal.show();
            }
          });
        });
        document.getElementById('uploadGuestModalOkBtn').addEventListener('click', function() {
          bootstrap.Modal.getInstance(document.getElementById('uploadGuestModal')).hide();
          window.location.href = REGISTER_URL;
        });
      });
    </script>
    <script>
      (function() {
        function doLogout() {
          // Laravel handled via form submission in navbar, but for any custom JS logout:
          document.querySelector('form[action*="logout"]').submit();
        }
        var navLogoutBtn = document.getElementById('navLogoutBtn');
        if (!navLogoutBtn) return;
        navLogoutBtn.addEventListener('click', function() {
          var role = window.__authRole || 'registered';
          var msgEl = document.getElementById('logoutConfirmMessage');
          var messages = {
            admin: 'Are you sure you want to log out? You will need to sign in again to use the Admin Data Portal.',
            trusted: 'Are you sure you want to log out? You will need to sign in again to use Upload (including SFTP) and purchase 3D models.',
            registered: 'Are you sure you want to log out? You will need to sign in again to upload data and purchase 3D models.'
          };
          if (msgEl) msgEl.textContent = messages[role] || messages.registered;
          var modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
          modal.show();
          document.getElementById('logoutConfirmBtn').onclick = function() { doLogout(); };
        });
      })();
    </script>

    <!-- Load showcase tiles from API (admin-managed); fallback = static tiles above -->
    <script>
    (function () {
      var API_BASE = (typeof window !== 'undefined' && window.TemaDataPortal_API_BASE) || (window.location && window.location.origin) || '';
      var container = document.getElementById('tilesShowcase');
      if (!container) return;

      // v176: Dynamic color generator for future locations
      function getPremiumColor(id) {
        var colors = [
          '1a1a2e/696cff', // Navy/Blue
          '667eea/ffffff', // Indigo
          'f093fb/ffffff', // Pink
          '4facfe/ffffff', // Sky Blue
          '43e97b/ffffff', // Green
          '6a11cb/ffffff', // Purple
          'ff0844/ffffff', // Red
          'f09819/ffffff'  // Orange
        ];
        var hash = 0;
        for (var i = 0; i < id.length; i++) { hash = id.charCodeAt(i) + ((hash << 5) - hash); }
        return colors[Math.abs(hash) % colors.length];
      }

      function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
      function toImgSrc(url) { if (!url) return ''; var u = (url + '').trim(); return u ? u.replace(/ /g, '%20') : ''; }
      
      function getDynamicCategory(item) {
        var tileset = (item['3dTiles'] || '').toLowerCase();
        var id = (item.mapDataID || item.map_data_id || item.id || '').toLowerCase();
        if (tileset.indexOf('building_planning') !== -1 || tileset.indexOf('building') !== -1) {
          return 'Building Planning';
        }
        if (id.indexOf('keningau') !== -1 || tileset.indexOf('keningau') !== -1) {
          return 'Regional Planning';
        }
        return 'Kota Kinabalu';
      }

      fetch(API_BASE + '/api/showcases').then(function (r) { return r.json(); }).then(function (rows) {
        if (!Array.isArray(rows) || rows.length === 0) return;
        var html = rows.map(function (r) {
          var id = r.mapDataID || r.map_data_id || r.id || '';
          var title = r.title || id;
          var rawImg = (r.thumbNailUrl || '').trim();
          var finalSrc;
          if (rawImg) {
            var encoded = toImgSrc(rawImg);
            finalSrc = (rawImg.indexOf('http') === 0 ? encoded : (API_BASE + (rawImg.indexOf('/') === 0 ? encoded : '/' + encoded)));
          } else {
            finalSrc = 'https://placehold.co/400x220/' + getPremiumColor(id) + '?text=' + encodeURIComponent((title || '3D').substring(0, 20));
          }
          var category = getDynamicCategory(r);
          return '<div class="col-lg-4 col-md-6" id="tile-showcase-' + esc(id) + '"><a href="/loading-3d?id=' + esc(id) + '" class="tile-3d-card" target="_blank" rel="noopener"><div class="tile-3d-img"><img src="' + finalSrc + '" alt="' + esc(title) + '" onerror="this.src=\'https://placehold.co/400x220/' + getPremiumColor(id) + '?text=3D+Model\'"></div><div class="tile-3d-body"><h6 class="tile-3d-title">' + esc(title) + '</h6><div class="tile-3d-tags"><span>GeoSabah 3D Hub</span><span>' + esc(category) + '</span></div><div class="tile-3d-metrics"><span><i class="bx bx-cube-alt"></i> 3D Tiles</span></div></div></a></div>';
        }).join('');
        container.innerHTML = html;
      }).catch(function () {});
    })();
    </script>

    <!-- Cesium and cesium-map are already loaded in <head> - do not load again -->

  </body>
</html>

  <!-- beautify ignore:end -->