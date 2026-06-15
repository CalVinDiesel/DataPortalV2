<!DOCTYPE html>
<html lang="en" dir="ltr" data-assets-path="{{ asset('assets') }}/" data-template="admin-data-portal" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Users - Admin | 3DHub</title>
  <script src="{{ asset('assets') }}/js/theme-init.js"></script>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/vendor/css/core.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/demo.css" />
  <link rel="stylesheet" href="{{ asset('assets') }}/css/admin-responsive.css" />
  <script src="{{ asset('assets') }}/vendor/js/helpers.js"></script>
  <script src="{{ asset('assets') }}/vendor/js/bootstrap.js"></script>
</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
  <style>
    /* 💎 ADMIN PREMIUM TOP NAV (v250) */
    .admin-glass-nav {
      position: fixed;
      top: 1.5rem;
      left: 1.5rem;
      right: 1.5rem;
      z-index: 1050;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 1.25rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      padding: 0.5rem 1.5rem;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }
    [data-bs-theme="dark"] .admin-glass-nav {
      background: rgba(15, 23, 42, 0.7);
      border-color: rgba(255, 255, 255, 0.08);
    }
    .admin-nav-links {
      display: flex;
      gap: 0.5rem;
      margin-left: 1.5rem;
      align-items: center;
    }
    .admin-nav-link {
      color: #566a7f;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
      font-size: 0.82rem;
      padding: 0.4rem 0.6rem;
      border-radius: 0.75rem;
      white-space: nowrap;
    }
    .admin-nav-link:hover {
      color: #696cff;
      background: rgba(105, 108, 255, 0.08);
    }
    .admin-nav-link.active {
      color: #696cff;
      background: rgba(105, 108, 255, 0.12);
      font-weight: 700;
    }
    .email-hover-link { color: #8e94a3 !important; transition: color 0.2s ease; } .email-hover-link:hover {
      color: #696cff !important;
    }
    .content-wrapper-premium {
      margin-top: 8.5rem !important;
    }
    .layout-page {
        padding: 0 !important;
    }
    @media (max-width: 1199.98px) {
      .admin-nav-links { display: none; }
    }
  </style>
</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      
      <!-- Premium Glass Top Nav -->
      <nav class="admin-glass-nav">
        <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
          <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
          <span class="app-brand-text demo menu-text fw-bold text-heading" style="font-size: 1.1em;">3DHub Admin</span>
        </a>
        
        <div class="admin-nav-links d-none d-xl-flex">
          <a href="{{ route('admin_dashboard') }}" class="admin-nav-link">Dashboard</a>
          <a href="{{ route('admin.add_3d_model') }}" class="admin-nav-link">Add 3D Model</a>
          <a href="{{ route('admin.manage_map_pins') }}" class="admin-nav-link">Manage Map Pins</a>
          <a href="{{ route('admin.manage_showcases') }}" class="admin-nav-link">Manage Showcase</a>
          <a href="{{ route('admin.client_uploads') }}" class="admin-nav-link">Client Uploads</a>
          <a href="{{ route('admin.manage_users') }}" class="admin-nav-link active">Manage Users</a>
          <a href="{{ route('landing') }}" class="admin-nav-link" target="_blank">View Portal</a>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <!-- Style Switcher -->
            <div class="nav-item dropdown-style-switcher dropdown me-2">
              <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light"><span><i class="icon-base bx bx-sun icon-md me-3"></i>Light</span></button></li>
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark"><span><i class="icon-base bx bx-moon icon-md me-3"></i>Dark</span></button></li>
                <li><button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"><span><i class="icon-base bx bx-desktop icon-md me-3"></i>System</span></button></li>
              </ul>
            </div>

            @auth
            <div class="d-none d-md-flex align-items-center gap-3 border-start ps-3 ms-2">
                <a href="{{ route('profile') }}" class="small text-muted fw-medium text-decoration-none email-hover-link">{{ Auth::user()->email }}</a>
                <form method="POST" action="{{ route('logout') }}" id="adminLogoutForm" class="d-inline">
                    @csrf
                    <button type="button" id="adminLogoutBtn" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold">Log out</button>
                </form>
            </div>
            @endauth

            <button class="admin-menu-toggle btn btn-icon d-xl-none border-0 bg-transparent p-0" type="button" aria-label="Toggle menu"><i class="bx bx-menu icon-lg"></i></button>
        </div>
      </nav>

      <div class="layout-page">
        <div class="content-wrapper content-wrapper-premium">
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold mb-0">Waitlist & Pending Requests</h4>
              <a href="{{ route('admin_dashboard') }}" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
            </div>
            <div id="requestsAlert" class="alert d-none mb-4"></div>
            <div class="card mb-5">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Email</th>
                      <th>Name</th>
                      <th>Company</th>
                      <th>Reason</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="requestsTableBody">
                    <tr><td colspan="5" class="text-muted text-center py-4">Loading…</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

            <h4 class="fw-bold mb-4">Manage Users</h4>
            <p class="text-muted mb-4">Users who signed up with email/password (or completed the register form with Google/Microsoft) are listed below. The <strong>Role</strong> column shows <span class="badge bg-label-secondary">Registered</span> (standard user), <span class="badge bg-label-primary">Trusted</span> (can upload via SFTP), or <span class="badge bg-label-success">Admin</span>. Registered users can be upgraded to Trusted, and any non-admin can be promoted to Admin.</p>
            <div id="usersNoClientsNote" class="alert alert-info d-none mb-4">All users listed are admins. To see Registered, Trusted, and upgrade options, you need at least one user with role Registered or Trusted.</div>
            <div id="usersAlert" class="alert d-none mb-4"></div>
            <div class="card">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Email</th>
                      <th>Name</th>
                      <th>Username</th>
                      <th>Role</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="usersTableBody">
                    <tr><td colspan="5" class="text-muted text-center py-4">Loading…</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Remove user modal -->
  <div class="modal fade" id="removeUserModal" tabindex="-1" aria-labelledby="removeUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="removeUserModalLabel">Remove user from data portal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Enter a reason for removing this user:</p>
          <textarea id="removeUserReasonInput" class="form-control" rows="3"
            placeholder="e.g. User requested removal / violated terms / duplicate account..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="removeUserConfirmBtn"><i class="bx bx-trash me-1"></i>Remove</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function() {
      var API = (window.TemaDataPortal_API_BASE || window.location.origin || 'http://localhost:3000');
      var tbody = document.getElementById('usersTableBody');
      var alertEl = document.getElementById('usersAlert');
      var removeUserModal = null;
      var pendingRemoveEmail = null;
      
      function showAlert(msg, isSuccess) {
        if (!alertEl) return;
        alertEl.textContent = msg;
        alertEl.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' mb-4';
        alertEl.classList.remove('d-none');
      }

      var reqTbody = document.getElementById('requestsTableBody');
      var reqAlertEl = document.getElementById('requestsAlert');
      function showReqAlert(msg, isSuccess) {
        if (!reqAlertEl) return;
        reqAlertEl.textContent = msg;
        reqAlertEl.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger') + ' mb-4';
        reqAlertEl.classList.remove('d-none');
      }

      function loadAccessRequests() {
        if (!reqTbody) return;
        fetch(API + '/api/admin/access-requests', { credentials: 'include' })
          .then(function(r) { return r.json(); })
          .then(function(reqs) {
            if (!reqs || reqs.length === 0) {
              reqTbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center py-4">No pending requests in the waitlist.</td></tr>';
              return;
            }
            reqTbody.innerHTML = reqs.map(function(r) {
              var status = r.status || 'pending';
              var statusBadge = '';
              if (status === 'rejected') {
                statusBadge = ' <span class="badge bg-label-danger ms-1">Rejected</span>';
              } else {
                statusBadge = ' <span class="badge bg-label-warning ms-1">Pending</span>';
              }

              var actions = '<div class="d-flex flex-wrap gap-2">' +
                '<button type="button" class="btn btn-sm btn-success approve-req-btn" data-id="' + r.id + '">Approve</button>';
              if (status === 'pending') {
                actions += '<button type="button" class="btn btn-sm btn-danger reject-req-btn" data-id="' + r.id + '">Reject</button>';
              }
              actions += '<button type="button" class="btn btn-sm btn-outline-danger remove-req-btn" data-id="' + r.id + '">Remove</button>' +
                '</div>';
              return '<tr><td>' + (r.email || '') + statusBadge + '</td><td>' + (r.name || '') + '</td><td>' + (r.company_name || '—') + '</td><td>' + (r.reason_for_access || '—') + '</td><td>' + actions + '</td></tr>';
            }).join('');
            
            reqTbody.querySelectorAll('.approve-req-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                if (!confirm('Approve this request? This will generate an invite and email the user.')) return;
                this.disabled = true;
                fetch(API + '/api/admin/access-requests/' + id + '/approve', {
                  method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include'
                }).then(r => r.json()).then(data => {
                  if (data.success) { showReqAlert('Approved & Invite Sent!', true); loadAccessRequests(); loadUsers(); } 
                  else { showReqAlert('Failed: ' + data.message, false); btn.disabled = false; }
                });
              });
            });

            reqTbody.querySelectorAll('.reject-req-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                if (!confirm('Reject and discard this waitlist request?')) return;
                this.disabled = true;
                fetch(API + '/api/admin/access-requests/' + id + '/reject', {
                  method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include'
                }).then(r => r.json()).then(data => {
                  if (data.success) { showReqAlert('Request Rejected.', true); loadAccessRequests(); } 
                  else { showReqAlert('Failed: ' + data.message, false); btn.disabled = false; }
                });
              });
            });

            reqTbody.querySelectorAll('.remove-req-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                if (!confirm('Permanently remove this access request from the database? This cannot be undone.')) return;
                this.disabled = true;
                fetch(API + '/api/admin/access-requests/' + id, {
                  method: 'DELETE',
                  credentials: 'include'
                }).then(r => r.json()).then(data => {
                  if (data.success) {
                    showReqAlert('Request permanently removed.', true);
                    loadAccessRequests();
                    loadUsers();
                  } else {
                    showReqAlert('Failed: ' + data.message, false);
                    btn.disabled = false;
                  }
                }).catch(() => {
                  showReqAlert('Network error. Failed to remove request.', false);
                  btn.disabled = false;
                });
              });
            });
          });
      }
      loadAccessRequests();

      function loadUsers() {
          fetch(API + '/api/auth/me', { credentials: 'include' })
            .then(function(r) { return r.json(); })
            .then(function(meData) {
              var currentRole = meData.role || 'registered';
              return fetch(API + '/api/admin/users', { credentials: 'include' })
                .then(function(r) {
                  if (!r.ok) throw new Error('Failed to load users');
                  return r.json();
                })
                .then(function(users) {
                  renderUsers(users, currentRole);
                });
            })
            .catch(function(err) {
               console.error('Error loading users:', err);
               if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Failed to load users.</td></tr>';
            });
      }

      function renderUsers(users, currentRole) {
            if (!tbody) return;
            if (!users || users.length === 0) {
              tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center py-4">No users in the system yet.</td></tr>';
              return;
            }
            var hasAnyNonAdmin = users.some(function(u) { return (u.role || 'registered') !== 'admin' && (u.role || 'registered') !== 'superadmin'; });
            document.getElementById('usersNoClientsNote').classList.toggle('d-none', hasAnyNonAdmin);
            tbody.innerHTML = users.map(function(u) {
              var role = (u.role || 'registered');
              var isAdmin = role === 'admin';
              var isSuperAdmin = role === 'superadmin';
              var isPending = role === 'pending';
              var isTrusted = role === 'trusted';
              var isRemoved = !!u.removedAt;

              var roleBadge = isRemoved
                ? '<span class="badge bg-label-danger">Removed</span>'
                : (isSuperAdmin ? '<span class="badge bg-label-dark">Super Admin</span>' : (isPending ? '<span class="badge bg-label-warning">Pending</span>' : (isAdmin ? '<span class="badge bg-label-success">Admin</span>' : (isTrusted ? '<span class="badge bg-label-primary">Trusted User</span>' : '<span class="badge bg-label-secondary">Registered</span>'))));

              if (isAdmin || isSuperAdmin || isRemoved) {
                return '<tr><td>' + (u.email || '') + '</td><td>' + (u.name || '') + '</td><td>' + (u.username || '') + '</td><td>' + roleBadge + '</td><td><span class="text-muted small">—</span></td></tr>';
              }

              if (isPending) {
                var action = '<div class="d-flex flex-wrap gap-2">';
                action += '<button type="button" class="btn btn-sm btn-outline-info resend-invite-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Resend Invite</button>';
                action += '<button type="button" class="btn btn-sm btn-outline-danger remove-user-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Remove</button>';
                action += '</div>';
                return '<tr><td>' + (u.email || '') + '</td><td>' + (u.name || '') + '</td><td>' + (u.username || '') + '</td><td>' + roleBadge + '</td><td>' + action + '</td></tr>';
              }

              var action = '<div class="d-flex flex-wrap gap-2">';
              if (isTrusted) {
                action += '<button type="button" class="btn btn-sm btn-outline-warning downgrade-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Downgrade to Registered</button>';
                if (currentRole === 'superadmin') {
                  action += '<button type="button" class="btn btn-sm btn-outline-primary promote-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Promote to Admin</button>';
                }
              } else {
                action += '<button type="button" class="btn btn-sm btn-outline-info upgrade-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Upgrade to Trusted</button>';
                if (currentRole === 'superadmin') {
                  action += '<button type="button" class="btn btn-sm btn-outline-primary promote-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Promote to Admin</button>';
                }
              }
              action += '<button type="button" class="btn btn-sm btn-outline-danger remove-user-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Remove</button>';
              action += '</div>';

              return '<tr><td>' + (u.email || '') + '</td><td>' + (u.name || '') + '</td><td>' + (u.username || '') + '</td><td>' + roleBadge + '</td><td>' + action + '</td></tr>';
            }).join('');
            tbody.querySelectorAll('.promote-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                if (!email || !confirm('Promote "' + email + '" to admin? They will be able to access the admin portal.')) return;
                this.disabled = true;
                fetch(API + '/api/admin/users/promote', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'include',
                  body: JSON.stringify({ email: email })
                })
                  .then(function(r) { return r.json(); })
                  .then(function(data) {
                    if (data.success) {
                      showAlert(data.message || 'User promoted to admin.', true);
                      loadUsers();
                    } else {
                      showAlert(data.message || 'Failed to promote.', false);
                      btn.disabled = false;
                    }
                  })
                  .catch(function() {
                    showAlert('Network error. Could not promote user.', false);
                    btn.disabled = false;
                  });
              });
            });
            tbody.querySelectorAll('.upgrade-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                if (!email || !confirm('Upgrade "' + email + '" to Trusted user? They will be able to upload via SFTP.')) return;
                this.disabled = true;
                fetch(API + '/api/admin/users/upgrade-trusted', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'include',
                  body: JSON.stringify({ email: email })
                })
                  .then(function(r) { return r.json(); })
                  .then(function(data) {
                    if (data.success) {
                      showAlert(data.message || 'User upgraded to Trusted.', true);
                      loadUsers();
                    } else {
                      showAlert(data.message || 'Failed to upgrade.', false);
                      btn.disabled = false;
                    }
                  })
                  .catch(function() {
                    showAlert('Network error. Could not upgrade user.', false);
                    btn.disabled = false;
                  });
              });
            });

            tbody.querySelectorAll('.downgrade-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                if (!email || !confirm('Downgrade "' + email + '" back to Registered?')) return;
                this.disabled = true;
                fetch(API + '/api/admin/users/downgrade-registered', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'include',
                  body: JSON.stringify({ email: email })
                })
                  .then(function(r) { return r.json(); })
                  .then(function(data) {
                    if (data.success) {
                      showAlert(data.message || 'User downgraded to registered.', true);
                      loadUsers();
                    } else {
                      showAlert(data.message || 'Failed to downgrade.', false);
                      btn.disabled = false;
                    }
                  })
                  .catch(function() {
                    showAlert('Network error. Could not downgrade user.', false);
                    btn.disabled = false;
                  });
              });
            });

            tbody.querySelectorAll('.resend-invite-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                if (!email || !confirm('Resend invitation email to "' + email + '"? This will refresh their signup link.')) return;
                this.disabled = true;
                
                var oldHtml = this.innerHTML;
                this.textContent = 'Sending…';

                fetch(API + '/api/admin/users/resend-invitation', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  credentials: 'include',
                  body: JSON.stringify({ email: email })
                })
                  .then(function(r) { return r.json(); })
                  .then(function(data) {
                    if (data.success) {
                      showAlert(data.message || 'Invitation email resent.', true);
                      loadUsers();
                    } else {
                      showAlert(data.message || 'Failed to resend invitation.', false);
                      btn.disabled = false;
                      btn.innerHTML = oldHtml;
                    }
                  })
                  .catch(function() {
                    showAlert('Network error. Could not resend invitation.', false);
                    btn.disabled = false;
                    btn.innerHTML = oldHtml;
                  });
              });
            });

            tbody.querySelectorAll('.remove-user-btn').forEach(function(btn) {
              btn.addEventListener('click', function() {
                var email = this.getAttribute('data-email');
                if (!email) return;
                pendingRemoveEmail = email;
                var reasonInput = document.getElementById('removeUserReasonInput');
                if (reasonInput) reasonInput.value = '';
                if (removeUserModal) removeUserModal.show();
              });
            });
      }
      loadUsers();

      // Init remove modal + confirm handler
      var removeModalEl = document.getElementById('removeUserModal');
      if (removeModalEl && typeof bootstrap !== 'undefined') {
        removeUserModal = new bootstrap.Modal(removeModalEl);
      }

      var confirmRemoveBtn = document.getElementById('removeUserConfirmBtn');
      if (confirmRemoveBtn) {
        confirmRemoveBtn.addEventListener('click', function() {
          if (!pendingRemoveEmail) return;
          var email = pendingRemoveEmail;
          var reasonInput = document.getElementById('removeUserReasonInput');
          var reason = (reasonInput && reasonInput.value ? reasonInput.value : '').trim();
          if (!reason) {
            alert('Please enter a reason for removing this user.');
            return;
          }

          confirmRemoveBtn.disabled = true;
          confirmRemoveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Removing…';

          fetch(API + '/api/admin/users/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ email: email, reason: reason })
          })
            .then(function(r) { return r.json(); })
            .then(function(data) {
              if (data.success) {
                if (removeUserModal) removeUserModal.hide();
                pendingRemoveEmail = null;
                showAlert(data.message || 'User removed from data portal.', true);
                loadUsers();
              } else {
                alert(data.message || 'Remove failed.');
              }
            })
            .catch(function() {
              alert('Remove failed due to network error.');
            })
            .finally(function() {
              confirmRemoveBtn.disabled = false;
              confirmRemoveBtn.innerHTML = '<i class="bx bx-trash me-1"></i>Remove';
            });
        });
      }
    })();
  </script>
  <script src="{{ asset('assets') }}/js/admin-responsive.js"></script>
  <script src="{{ asset('assets') }}/js/theme-switcher.js"></script>
  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="logoutConfirmMessage">Are you sure you want to log out? You will need to sign in again to use the Admin Data Portal.</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="logoutConfirmBtn">Log out</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var logoutBtn = document.getElementById('adminLogoutBtn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          var modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
          modal.show();
          document.getElementById('logoutConfirmBtn').onclick = function() {
            document.getElementById('adminLogoutForm').submit();
          };
        });
      }
    });
  </script>
</body>
</html>
