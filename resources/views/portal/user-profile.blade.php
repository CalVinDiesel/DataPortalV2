<!DOCTYPE html>
<html lang="en" class="layout-navbar-fixed layout-wide" dir="ltr" data-assets-path="{{ asset('assets') }}"
  data-template="front-pages" data-bs-theme="light">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
  <title>Account Profile | 3DHub Data Portal</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="{{ asset('assets') }}/js/theme-init.js"></script>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css">

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css">
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/pages/front-page.css">

  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/js/front-config.js"></script>

  <!-- Auth: require any logged-in user (registered, trusted, admin) -->
  <script>
    (function () {
      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      var LANDING_URL = '{{ route('landing') }}';
      var removalHandled = false;

      function checkAccountRemoved() {
        return fetch(AUTH_API + '/api/auth/me', { credentials: 'include' })
          .then(function (r) { return r.json(); })
          .then(function (d) {
            if (d && (d.account_removed || d.removal_reason)) {
              if (!removalHandled) {
                removalHandled = true;
                alert(d.message || ('Your account has been removed.' + (d.removal_reason ? (' Reason: ' + d.removal_reason) : '')));
              }
              window.location.href = '{{ route('login') }}';
              return null;
            }
            return d;
          });
      }

      checkAccountRemoved().then(function (d) {
        if (!d) return;
        if (!d.loggedIn) {
          window.location.href = '{{ route('login') }}?redirect=' + encodeURIComponent(window.location.pathname);
          return;
        }
      }).catch(function () { });

      setInterval(function () {
        if (removalHandled) return;
        checkAccountRemoved().catch(function () { });
      }, 60000);
    })();
  </script>

<style>
  body { background-color: var(--bs-body-bg); }
  .hero-bg {
    background: linear-gradient(135deg, rgba(105, 108, 255, 0.05) 0%, rgba(105, 108, 255, 0.01) 100%);
    padding: 3rem 0;
    border-bottom: 1px solid var(--bs-border-color);
  }
  .profile-card {
    background: var(--bs-card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    margin-bottom: 1.5rem;
  }
  .profile-card h5 { margin-bottom: 1rem; font-weight: 600; }
  .profile-row {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.5rem 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--bs-border-color);
  }
  .profile-row:last-child { border-bottom: none; }
  .profile-label {
    font-weight: 500;
    color: var(--bs-secondary-color);
    min-width: 140px;
  }
  .profile-value {
    color: var(--bs-body-color);
    flex: 1;
    min-width: 0;
  }
  .profile-actions { flex-shrink: 0; }
  .profile-inline-form {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: var(--bs-tertiary-bg);
    border-radius: 8px;
    max-width: 400px;
  }
  .profile-inline-form .form-control { margin-bottom: 0.5rem; }
  .profile-inline-form .btn { margin-right: 0.5rem; margin-top: 0.25rem; }
  .back-btn {
    background: var(--bs-card-bg) !important;
    color: var(--bs-secondary-color) !important;
  }
  .back-btn:hover { color: #696cff !important; background-color: var(--bs-tertiary-bg) !important; }
  .back-btn:hover i { color: inherit !important; }

  /* heading inside hero always uses body color */
  .hero-bg h2 { color: var(--bs-heading-color); }

  /* 🚀 BORDER ALIGNMENT FIX (v150): Ensure merged input groups have seamless borders */
  .input-group-merge.form-password-toggle {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.375rem;
    overflow: hidden;
    background-color: var(--bs-body-bg);
  }
  .input-group-merge.form-password-toggle .form-control,
  .input-group-merge.form-password-toggle .input-group-text {
    border: none !important;
    box-shadow: none !important;
    background-color: transparent !important;
  }
  .input-group-merge.form-password-toggle:focus-within {
    border-color: #696cff;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
  }

  /* 🚀 BROWSER OVERRIDE (v151): Hide native browser password reveal buttons to prevent duplicates */
  input::-ms-reveal,
  input::-ms-clear,
  input::-webkit-contacts-auto-fill-button,
  input::-webkit-credentials-auto-fill-button {
    display: none !important;
  }
</style>
</head>

<body>

  <div class="hero-bg">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="{{ Auth::user() && (Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin') ? route('admin_dashboard') : route('landing') }}" class="btn btn-label-secondary btn-sm fw-medium border shadow-sm back-btn" style="background: white; color: #566a7f;">
          <i class="bx bx-arrow-back me-1"></i> Back
        </a>
        <!-- Style Switcher -->
        <ul class="navbar-nav flex-row align-items-center mb-0">
          <li class="nav-item dropdown-style-switcher dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light">
                  <span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark">
                  <span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span>
                </button>
              </li>
              <li>
                <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system">
                  <span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span>
                </button>
              </li>
            </ul>
          </li>
        </ul>
        <!-- / Style Switcher -->
      </div>
      <h2 class="h3 fw-bold text-dark mb-2">Account profile</h2>
      <p class="text-muted mb-0">View and manage your personal details. You can change your name, password, contact number, or email from the options beside each field.</p>
    </div>
  </div>

  <div class="container mt-4 mb-5 pb-5">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div id="profileLoadError" class="alert alert-danger d-none" role="alert"></div>

    <div class="profile-card">
      <h5><i class="bx bx-user me-2"></i>Personal details</h5>
      <p class="text-muted small mb-4">Details from your sign-up. Name, password, contact number, and email can be changed.</p>

      <!-- Name: value + Edit name beside it -->
      <div class="profile-row">
        <span class="profile-label">Name</span>
        <span class="profile-value" id="profile-name">—</span>
        <span class="profile-actions">
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangeName">Edit name</button>
        </span>
      </div>
      <div id="inlineFormName" class="profile-inline-form d-none">
        <label class="form-label small">Display name</label>
        <input type="text" class="form-control form-control-sm" id="newName" placeholder="Your full name">
        <button type="button" class="btn btn-sm btn-primary" id="btnNameSubmit">Update name</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnNameCancel">Cancel</button>
        <span id="nameMessage" class="small ms-2"></span>
      </div>

      <!-- Email: value + Change email beside it -->
      <div class="profile-row">
        <span class="profile-label">Email</span>
        <span class="profile-value" id="profile-email">—</span>
        <span class="profile-actions">
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangeEmail">Change email</button>
          <a href="#" class="btn btn-sm btn-outline-primary d-none" id="btnChangeEmailOAuth">Change email</a>
        </span>
      </div>
      <div id="inlineFormEmail" class="profile-inline-form d-none">
        <label class="form-label small">New email address</label>
        <input type="email" class="form-control form-control-sm" id="newEmail" placeholder="new@email.com">
        <button type="button" class="btn btn-sm btn-primary" id="btnEmailSubmit">Update email</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnEmailCancel">Cancel</button>
        <span id="emailMessage" class="small ms-2"></span>
      </div>

      <!-- Contact: value + Change contact beside it -->
      <div class="profile-row">
        <span class="profile-label">Contact number</span>
        <span class="profile-value" id="profile-contact">—</span>
        <span class="profile-actions">
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangeContact">Change contact</button>
        </span>
      </div>
      <div id="inlineFormContact" class="profile-inline-form d-none">
        <label class="form-label small">New contact number</label>
        <input type="tel" class="form-control form-control-sm" id="contactNumber" placeholder="e.g. +6012-345 6789">
        <button type="button" class="btn btn-sm btn-primary" id="btnContactSubmit">Update contact</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnContactCancel">Cancel</button>
        <span id="contactMessage" class="small ms-2"></span>
      </div>

      <!-- Password: masked or "Not set" + Change password beside it -->
      <div class="profile-row">
        <span class="profile-label">Password</span>
        <span class="profile-value" id="profile-password">••••••••</span>
        <span class="profile-actions">
          <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="btnToggleMainPassword">Show</button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangePassword">Change password</button>
        </span>
      </div>
      <form id="formChangePassword" class="profile-inline-form d-none">
        @csrf
        {{-- 🚀 BROWSER ACCESSIBILITY (v155): Added hidden username field to satisfy accessibility warnings --}}
        <input type="text" name="username" value="{{ Auth::user()->email }}" style="display:none;" autocomplete="username">
        
        <div class="mb-2">
          <label class="form-label small">Current password</label>
          <div class="input-group input-group-merge form-password-toggle">
            <input type="password" class="form-control" id="currentPassword" name="currentPassword" autocomplete="current-password" placeholder="Current password">
            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
          </div>
        </div>
        
        <div class="mb-2">
          <label class="form-label small">New password</label>
          <div class="input-group input-group-merge form-password-toggle">
            <input type="password" class="form-control" id="newPassword" name="newPassword" minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
          </div>
        </div>
        
        <button type="submit" class="btn btn-sm btn-primary" id="btnPasswordSubmit">Update password</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPasswordCancel">Cancel</button>
        <span id="passwordMessage" class="small ms-2"></span>
      </form>

      <!-- Role (read-only) -->
      <div class="profile-row">
        <span class="profile-label">Account role</span>
        <span class="profile-value" id="profile-role">—</span>
      </div>

      <!-- Sign-in method (read-only) -->
      <div class="profile-row">
        <span class="profile-label">Sign-in method</span>
        <span class="profile-value" id="profile-provider">—</span>
      </div>
    </div>

    <!-- SFTP Credentials Card -->
    <div class="profile-card">
      <h5><i class="bx bx-server me-2"></i>SFTP credentials</h5>
      <p class="text-muted small mb-4">
        Your SFTP (Secure File Transfer Protocol) credentials are used to securely upload raw drone/image data to our server for 3D model processing.
        Use these credentials in any SFTP client (e.g. FileZilla, WinSCP) to connect and upload your files.
      </p>

      <!-- SFTP Connection Details -->
      <div class="profile-row">
        <span class="profile-label">SFTP Host</span>
        <span class="profile-value" id="profile-sftp-host">{{ config('support.sftp_host') }}</span>
      </div>
      <div class="profile-row">
        <span class="profile-label">SFTP Port</span>
        <span class="profile-value text-primary fw-bold" id="profile-sftp-port">{{ env('CLIENT_SFTP_PORT', env('SFTP_PORT', 2222)) }}</span>
      </div>

      <!-- SFTP Username -->
      <div class="profile-row">
        <span class="profile-label">SFTP Username</span>
        <span class="profile-value" id="profile-sftp-username">—</span>
        <span class="profile-actions" id="sftp-username-actions">
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangeSftpUsername">Change username</button>
        </span>
      </div>
      <div id="formChangeSftpUsername" class="profile-inline-form d-none">
        @csrf
        <label class="form-label small">New SFTP username</label>
        <input type="text" class="form-control form-control-sm" id="newSftpUsername" placeholder="e.g. johndoe_sftp">
        <button type="button" class="btn btn-sm btn-primary" id="btnSftpUsernameSubmit">Update SFTP username</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSftpUsernameCancel">Cancel</button>
        <span id="sftpUsernameMessage" class="small ms-2"></span>
      </div>

      <!-- SFTP Password -->
      <div class="profile-row">
        <span class="profile-label">SFTP Password</span>
        <span class="profile-value" id="profile-sftp-password">••••••••</span>
        <span class="profile-actions">
          <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="btnToggleSftpPassword">Show</button>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btnChangeSftpPassword">Change password</button>
        </span>
      </div>
      <form id="formChangeSftpPassword" class="profile-inline-form d-none">
        @csrf
        <!-- 🚀 BROWSER ACCESSIBILITY (v155): Added hidden username field to satisfy accessibility warnings -->
        <input type="text" name="username" value="{{ Auth::user()->sftp_username ?? Auth::user()->email }}" style="display:none;" autocomplete="username">
        
        <div class="mb-2">
          <label class="form-label small">New SFTP password</label>
          <div class="input-group input-group-merge form-password-toggle">
            <input type="password" class="form-control" id="newSftpPassword" name="newSftpPassword" placeholder="At least 8 characters" minlength="8" autocomplete="new-password">
            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
          </div>
        </div>
        
        <div class="mb-2">
          <label class="form-label small">Confirm new SFTP password</label>
          <div class="input-group input-group-merge form-password-toggle">
            <input type="password" class="form-control" id="confirmSftpPassword" name="confirmSftpPassword" placeholder="Repeat new password" autocomplete="new-password">
            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
          </div>
        </div>
        
        <button type="submit" class="btn btn-sm btn-primary mt-2" id="btnSftpPasswordSubmit">Update SFTP password</button>
        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnSftpPasswordCancel">Cancel</button>
        <span id="sftpPasswordMessage" class="small ms-2"></span>
      </form>

      <!-- SFTP not set warning -->
      <div id="sftpNotSetAlert" class="alert alert-warning mt-3 d-none" role="alert">
        <i class="bx bx-info-circle me-1"></i>
        Your SFTP account has not been set up yet. Please contact the administrator.
      </div>
    </div>
  </div>

  <script src="{{ asset('assets') }}/vendor/libs/popper/popper.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  <script>
    (function () {
      var AUTH_API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      var LOGIN_URL = '{{ route('login') }}';

      function showMessage(elId, text, isError) {
        var el = document.getElementById(elId);
        if (!el) return;
        el.textContent = text || '';
        el.className = 'small ms-2 ' + (isError ? 'text-danger' : 'text-success');
      }

      function hideInlineForms() {
        const ids = ['inlineFormName', 'formChangePassword', 'inlineFormContact', 'inlineFormEmail', 'formChangeSftpPassword', 'formChangeSftpUsername'];
        ids.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.classList.add('d-none');
        });
      }

      function loadProfile() {
        fetch(AUTH_API + '/api/auth/profile', { credentials: 'include' })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data.success) {
              document.getElementById('profileLoadError').classList.remove('d-none');
              document.getElementById('profileLoadError').textContent = data.message || 'Failed to load profile.';
              return;
            }
            document.getElementById('profile-name').textContent = data.name || '—';
            document.getElementById('profile-email').textContent = data.email || '—';
            document.getElementById('profile-contact').textContent = data.contactNumber || '—';
            
            actualMainPassword = data.viewablePassword || '';
            var provider = (data.provider || 'local').toLowerCase();
            if (provider !== 'local') {
                var providerName = provider === 'google' ? 'Google' : 'Microsoft';
                document.getElementById('profile-password').innerHTML = '<span class="text-success fw-medium"><i class="bx bx-shield me-1"></i>Managed securely by ' + providerName + '</span>';
                document.getElementById('btnToggleMainPassword').classList.add('d-none');
                document.getElementById('btnChangePassword').classList.add('d-none');
                document.getElementById('btnChangeEmail').classList.add('d-none');
                
                var oAuthBtn = document.getElementById('btnChangeEmailOAuth');
                if (oAuthBtn) {
                    oAuthBtn.classList.remove('d-none');
                    oAuthBtn.href = '/auth/relink/' + provider;
                }
            } else {
                document.getElementById('profile-password').textContent = data.hasPassword ? '••••••••' : 'Not set';
                document.getElementById('btnChangePassword').classList.remove('d-none');
                document.getElementById('btnChangeEmail').classList.remove('d-none');
                
                var oAuthBtn = document.getElementById('btnChangeEmailOAuth');
                if (oAuthBtn) {
                    oAuthBtn.classList.add('d-none');
                }

                if (!data.hasPassword) {
                    document.getElementById('btnToggleMainPassword').classList.add('d-none');
                } else {
                    document.getElementById('btnToggleMainPassword').classList.remove('d-none');
                }
            }

            document.getElementById('profile-role').textContent = (data.role || 'registered').charAt(0).toUpperCase() + (data.role || '').slice(1);
            var providerLabel = (data.provider || 'local').toLowerCase();
            if (providerLabel === 'local') providerLabel = 'Email';
            else if (providerLabel === 'google') providerLabel = 'Google';
            else if (providerLabel === 'microsoft') providerLabel = 'Microsoft';
            document.getElementById('profile-provider').textContent = providerLabel;

            // Update SFTP display if loaded together or separately
            var sftpUsernameEl = document.getElementById('profile-sftp-username');
            var sftpPasswordEl = document.getElementById('profile-sftp-password');
            var sftpAlertEl = document.getElementById('sftpNotSetAlert');
            var sftpUsernameActions = document.getElementById('sftp-username-actions');
            var sftpPasswordActions = document.getElementById('btnToggleSftpPassword')?.parentElement;

            if (data.sftpUsername && data.sftpUsername !== 'Not set') {
              if (sftpUsernameEl) sftpUsernameEl.textContent = data.sftpUsername;
              actualSftpPassword = data.sftpPassword || '';
              if (sftpPasswordEl) sftpPasswordEl.textContent = '••••••••';
              if (sftpAlertEl) sftpAlertEl.classList.add('d-none');
              if (sftpUsernameActions) sftpUsernameActions.classList.remove('d-none');
              if (sftpPasswordActions) sftpPasswordActions.classList.remove('d-none');
            } else {
              if (sftpUsernameEl) sftpUsernameEl.textContent = 'Not set';
              if (sftpPasswordEl) sftpPasswordEl.textContent = 'Not set';
              if (sftpAlertEl) sftpAlertEl.classList.remove('d-none');
              if (sftpUsernameActions) sftpUsernameActions.classList.add('d-none');
              if (sftpPasswordActions) sftpPasswordActions.classList.add('d-none');
            }
          })
          .catch(function () {
            document.getElementById('profileLoadError').classList.remove('d-none');
            document.getElementById('profileLoadError').textContent = 'Failed to load profile.';
          });
      }

      document.addEventListener('DOMContentLoaded', function () {
        var mainPasswordVisible = false;
        var actualMainPassword = '';

        loadProfile();

        document.getElementById('btnToggleMainPassword').addEventListener('click', function () {
          mainPasswordVisible = !mainPasswordVisible;
          var el = document.getElementById('profile-password');
          if (mainPasswordVisible) {
            el.textContent = actualMainPassword || 'Password is encrypted (hidden for security)';
            this.textContent = 'Hide';
          } else {
            el.textContent = '••••••••';
            this.textContent = 'Show';
          }
        });

        document.getElementById('btnChangeName').addEventListener('click', function () {
          hideInlineForms();
          document.getElementById('newName').value = document.getElementById('profile-name').textContent;
          if (document.getElementById('profile-name').textContent === '—') document.getElementById('newName').value = '';
          document.getElementById('inlineFormName').classList.remove('d-none');
          showMessage('nameMessage', '');
        });
        document.getElementById('btnNameCancel').addEventListener('click', function () {
          document.getElementById('inlineFormName').classList.add('d-none');
          showMessage('nameMessage', '');
        });
        document.getElementById('btnNameSubmit').addEventListener('click', function () {
          var btn = document.getElementById('btnNameSubmit');
          var name = document.getElementById('newName').value.trim();
          if (!name) {
            showMessage('nameMessage', 'Please enter a name.', true);
            return;
          }
          btn.disabled = true;
          showMessage('nameMessage', '');
          fetch(AUTH_API + '/api/auth/profile/name', {
            method: 'PUT',
            headers: { 
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ name: name })
          })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                showMessage('nameMessage', data.message || 'Name updated.', false);
                document.getElementById('profile-name').textContent = data.name || name;
                setTimeout(function () {
                  document.getElementById('inlineFormName').classList.add('d-none');
                  showMessage('nameMessage', '');
                }, 2000);
              } else {
                showMessage('nameMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
              }
            })
            .catch(function () { showMessage('nameMessage', 'Network error.', true); })
            .finally(function () { btn.disabled = false; });
        });

        document.getElementById('btnChangePassword').addEventListener('click', function () {
          hideInlineForms();
          document.getElementById('formChangePassword').classList.remove('d-none');
          document.getElementById('currentPassword').value = '';
          document.getElementById('newPassword').value = '';
          showMessage('passwordMessage', '');
        });
        document.getElementById('btnPasswordCancel').addEventListener('click', function () {
          document.getElementById('formChangePassword').classList.add('d-none');
          showMessage('passwordMessage', '');
        });
        document.getElementById('formChangePassword').addEventListener('submit', function (e) {
          e.preventDefault();
          var btn = document.getElementById('btnPasswordSubmit');
          var current = document.getElementById('currentPassword').value;
          var newPw = document.getElementById('newPassword').value;
          if (!current || !newPw) {
            showMessage('passwordMessage', 'Please fill both fields.', true);
            return;
          }
          btn.disabled = true;
          showMessage('passwordMessage', '');
          fetch(AUTH_API + '/api/auth/profile/password', {
            method: 'PUT',
            headers: { 
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'include',
            body: JSON.stringify({ currentPassword: current, newPassword: newPw })
          })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                showMessage('passwordMessage', data.message || 'Password updated.', false);
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('profile-password').textContent = '••••••••';
                setTimeout(function () {
                  document.getElementById('formChangePassword').classList.add('d-none');
                  showMessage('passwordMessage', '');
                }, 2000);
              } else {
                showMessage('passwordMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
              }
            })
            .catch(function () { showMessage('passwordMessage', 'Network error.', true); })
            .finally(function () { btn.disabled = false; });
        });

        document.getElementById('btnChangeContact').addEventListener('click', function () {
          hideInlineForms();
          document.getElementById('contactNumber').value = document.getElementById('profile-contact').textContent;
          if (document.getElementById('profile-contact').textContent === '—') document.getElementById('contactNumber').value = '';
          document.getElementById('inlineFormContact').classList.remove('d-none');
          showMessage('contactMessage', '');
        });
        document.getElementById('btnContactCancel').addEventListener('click', function () {
          document.getElementById('inlineFormContact').classList.add('d-none');
          showMessage('contactMessage', '');
        });
        document.getElementById('btnContactSubmit').addEventListener('click', function () {
          var btn = document.getElementById('btnContactSubmit');
          var msg = document.getElementById('contactMessage');
          var contact = document.getElementById('contactNumber').value.trim();
          btn.disabled = true;
          showMessage('contactMessage', '');
          fetch(AUTH_API + '/api/auth/profile/contact', {
            method: 'PUT',
            headers: { 
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ contactNumber: contact })
          })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                showMessage('contactMessage', data.message || 'Updated.', false);
                document.getElementById('profile-contact').textContent = contact || '—';
                setTimeout(function () {
                  document.getElementById('inlineFormContact').classList.add('d-none');
                  showMessage('contactMessage', '');
                }, 2000);
              } else {
                showMessage('contactMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
              }
            })
            .catch(function () { showMessage('contactMessage', 'Network error.', true); })
            .finally(function () { btn.disabled = false; });
        });

        document.getElementById('btnChangeEmail').addEventListener('click', function () {
          hideInlineForms();
          document.getElementById('newEmail').value = '';
          document.getElementById('inlineFormEmail').classList.remove('d-none');
          showMessage('emailMessage', '');
        });
        document.getElementById('btnEmailCancel').addEventListener('click', function () {
          document.getElementById('inlineFormEmail').classList.add('d-none');
          showMessage('emailMessage', '');
        });
        document.getElementById('btnEmailSubmit').addEventListener('click', function () {
          var btn = document.getElementById('btnEmailSubmit');
          var newEmail = document.getElementById('newEmail').value.trim();
          if (!newEmail) {
            showMessage('emailMessage', 'Please enter a new email.', true);
            return;
          }
          btn.disabled = true;
          showMessage('emailMessage', '');
          fetch(AUTH_API + '/api/auth/profile/email', {
            method: 'PUT',
            headers: { 
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ newEmail: newEmail })
          })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                showMessage('emailMessage', data.message || 'Updated.', false);
                document.getElementById('profile-email').textContent = newEmail;
                if (data.requireRelogin) {
                  setTimeout(function () {
                    window.location.href = LOGIN_URL + '?message=email_updated';
                  }, 1500);
                } else {
                  setTimeout(function () {
                    document.getElementById('inlineFormEmail').classList.add('d-none');
                    showMessage('emailMessage', '');
                  }, 2000);
                }
              } else {
                showMessage('emailMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
                btn.disabled = false;
              }
            })
            .catch(function () {
              showMessage('emailMessage', 'Network error.', true);
              btn.disabled = false;
            });
        });

// ---- SFTP Credentials ----
        var sftpPasswordVisible = false;
        var actualSftpPassword = '';

        // Load SFTP credentials
        var sftpUsernameEl_main = document.getElementById('profile-sftp-username');
        if (sftpUsernameEl_main) {
          fetch(AUTH_API + '/api/auth/profile/sftp', { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
              if (data.success) {
                var sftpPasswordEl = document.getElementById('profile-sftp-password');
                var sftpAlertEl = document.getElementById('sftpNotSetAlert');
                var sftpPortEl = document.getElementById('profile-sftp-port');
                var sftpHostEl = document.getElementById('profile-sftp-host');
                var sftpChangePwBtn = document.getElementById('btnChangeSftpPassword');
                var sftpTogglePwBtn = document.getElementById('btnToggleSftpPassword');

                if (data.sftpUsername && data.sftpUsername !== 'Not set') {
                  if (sftpUsernameEl_main) sftpUsernameEl_main.textContent = data.sftpUsername;
                  actualSftpPassword = data.sftpPassword || '';
                  if (sftpPasswordEl) sftpPasswordEl.textContent = '••••••••';
                  if (sftpAlertEl) sftpAlertEl.classList.add('d-none');
                  
                  // 🚀 PORT SYNC (v142): Ensure port 2223 is shown for clients
                  if (sftpPortEl) {
                    sftpPortEl.textContent = data.sftpPort || '{{ env('CLIENT_SFTP_PORT', env('SFTP_PORT', 2222)) }}';
                  }
                  if (sftpHostEl) {
                    sftpHostEl.textContent = data.sftpHost || '{{ config('support.sftp_host') }}';
                  }
                  
                  if (sftpChangePwBtn) sftpChangePwBtn.disabled = false;
                  if (sftpTogglePwBtn) sftpTogglePwBtn.disabled = false;
                } else {
                  if (sftpUsernameEl_main) sftpUsernameEl_main.textContent = 'Not set';
                  if (sftpPasswordEl) sftpPasswordEl.textContent = 'Not set';
                  if (sftpAlertEl) sftpAlertEl.classList.remove('d-none');
                  if (sftpChangePwBtn) sftpChangePwBtn.disabled = true;
                  if (sftpTogglePwBtn) sftpTogglePwBtn.disabled = true;
                }
              }
            })
            .catch(function() {});
        }

        // Toggle show/hide SFTP password
        var btnToggleSftpPassword = document.getElementById('btnToggleSftpPassword');
        if (btnToggleSftpPassword) {
          btnToggleSftpPassword.addEventListener('click', function() {
            sftpPasswordVisible = !sftpPasswordVisible;
            var sftpPasswordEl = document.getElementById('profile-sftp-password');
            if (sftpPasswordEl) sftpPasswordEl.textContent = sftpPasswordVisible ? (actualSftpPassword || '—') : '••••••••';
            this.textContent = sftpPasswordVisible ? 'Hide' : 'Show';
          });
        }

        // Show change SFTP password form
        var btnChangeSftpPassword = document.getElementById('btnChangeSftpPassword');
        if (btnChangeSftpPassword) {
          btnChangeSftpPassword.addEventListener('click', function() {
            hideInlineForms();
            var formChangeSftpPassword = document.getElementById('formChangeSftpPassword');
            if (formChangeSftpPassword) formChangeSftpPassword.classList.remove('d-none');
            var newSftpPassword = document.getElementById('newSftpPassword');
            if (newSftpPassword) newSftpPassword.value = '';
            var confirmSftpPassword = document.getElementById('confirmSftpPassword');
            if (confirmSftpPassword) confirmSftpPassword.value = '';
            showMessage('sftpPasswordMessage', '');
          });
        }

        var btnSftpPasswordCancel = document.getElementById('btnSftpPasswordCancel');
        if (btnSftpPasswordCancel) {
          btnSftpPasswordCancel.addEventListener('click', function() {
            var formChangeSftpPassword = document.getElementById('formChangeSftpPassword');
            if (formChangeSftpPassword) formChangeSftpPassword.classList.add('d-none');
            showMessage('sftpPasswordMessage', '');
          });
        }

        // Submit new SFTP password
        var formChangeSftpPassword = document.getElementById('formChangeSftpPassword');
        if (formChangeSftpPassword) {
          formChangeSftpPassword.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnSftpPasswordSubmit');
            var newPwVal = document.getElementById('newSftpPassword').value;
            var confirmPwVal = document.getElementById('confirmSftpPassword').value;

            if (!newPwVal || newPwVal.length < 8) {
              showMessage('sftpPasswordMessage', 'Password must be at least 8 characters.', true);
              return;
            }
            if (newPwVal !== confirmPwVal) {
              showMessage('sftpPasswordMessage', 'Passwords do not match.', true);
              return;
            }

            if (btn) btn.disabled = true;
            showMessage('sftpPasswordMessage', 'Updating…');

            fetch(AUTH_API + '/api/auth/profile/sftp-password', {
              method: 'PUT',
              headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              credentials: 'include',
              body: JSON.stringify({ newPassword: newPwVal })
            })
              .then(function(r) { return r.json(); })
              .then(function(data) {
                if (data.success) {
                  showMessage('sftpPasswordMessage', data.message || 'SFTP password updated.', false);
                  actualSftpPassword = newPwVal;
                  var sftpPasswordEl = document.getElementById('profile-sftp-password');
                  if (sftpPasswordEl) sftpPasswordEl.textContent = sftpPasswordVisible ? newPwVal : '••••••••';
                  setTimeout(function () {
                    var formChangeSftpPassword = document.getElementById('formChangeSftpPassword');
                    if (formChangeSftpPassword) formChangeSftpPassword.classList.add('d-none');
                    showMessage('sftpPasswordMessage', '');
                  }, 2000);
                } else {
                  showMessage('sftpPasswordMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
                }
              })
              .catch(function() { showMessage('sftpPasswordMessage', 'Network error.', true); })
              .finally(function() { if (btn) btn.disabled = false; });
          });
        }

        // Show change SFTP username form
        var btnChangeSftpUsername = document.getElementById('btnChangeSftpUsername');
        if (btnChangeSftpUsername) {
          btnChangeSftpUsername.addEventListener('click', function() {
            hideInlineForms();
            var formChangeSftpUsername = document.getElementById('formChangeSftpUsername');
            if (formChangeSftpUsername) formChangeSftpUsername.classList.remove('d-none');
            var newSftpUsername = document.getElementById('newSftpUsername');
            if (newSftpUsername) {
              var currentSftpUsername = document.getElementById('profile-sftp-username').textContent;
              newSftpUsername.value = currentSftpUsername === 'Not set' || currentSftpUsername === '—' ? '' : currentSftpUsername;
            }
            showMessage('sftpUsernameMessage', '');
          });
        }

        var btnSftpUsernameCancel = document.getElementById('btnSftpUsernameCancel');
        if (btnSftpUsernameCancel) {
          btnSftpUsernameCancel.addEventListener('click', function() {
            var formChangeSftpUsername = document.getElementById('formChangeSftpUsername');
            if (formChangeSftpUsername) formChangeSftpUsername.classList.add('d-none');
            showMessage('sftpUsernameMessage', '');
          });
        }

        var btnSftpUsernameSubmit = document.getElementById('btnSftpUsernameSubmit');
        if (btnSftpUsernameSubmit) {
          btnSftpUsernameSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnSftpUsernameSubmit');
            var newUsernameVal = document.getElementById('newSftpUsername').value.trim();

            if (!newUsernameVal) {
              showMessage('sftpUsernameMessage', 'Username cannot be empty.', true);
              return;
            }

            if (btn) btn.disabled = true;
            showMessage('sftpUsernameMessage', 'Updating…');

            fetch(AUTH_API + '/api/auth/profile/sftp-username', {
              method: 'PUT',
              headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              credentials: 'include',
              body: JSON.stringify({ sftpUsername: newUsernameVal })
            })
              .then(function(r) { return r.json(); })
              .then(function(data) {
                if (data.success) {
                  showMessage('sftpUsernameMessage', data.message || 'SFTP username updated.', false);
                  var sftpUsernameEl = document.getElementById('profile-sftp-username');
                  if (sftpUsernameEl) sftpUsernameEl.textContent = data.sftpUsername;
                  setTimeout(function () {
                    var formChangeSftpUsername = document.getElementById('formChangeSftpUsername');
                    if (formChangeSftpUsername) formChangeSftpUsername.classList.add('d-none');
                    showMessage('sftpUsernameMessage', '');
                  }, 2000);
                } else {
                  showMessage('sftpUsernameMessage', data.message || (data.errors ? Object.values(data.errors)[0][0] : null) || 'Update failed.', true);
                }
              })
              .catch(function() { showMessage('sftpUsernameMessage', 'Network error.', true); })
              .finally(function() { if (btn) btn.disabled = false; });
          });
        }

        // 🚀 CUSTOM TOGGLE LOGIC (v152): Since theme JS is not auto-loaded here
        document.querySelectorAll('.form-password-toggle .input-group-text').forEach(function(btn) {
          btn.addEventListener('click', function() {
            var inputGroup = this.closest('.form-password-toggle');
            var input = inputGroup.querySelector('input');
            var icon = this.querySelector('i');
            
            if (input.type === 'password') {
              input.type = 'text';
              icon.classList.replace('bx-hide', 'bx-show');
            } else {
              input.type = 'password';
              icon.classList.replace('bx-show', 'bx-hide');
            }
          });
        });
      });
    })();
  </script>
</body>
</html>
