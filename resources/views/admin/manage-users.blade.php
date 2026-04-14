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
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo py-4">
          <a href="{{ route('admin_dashboard') }}" class="app-brand-link d-flex align-items-center">
            <span class="app-brand-logo demo me-2"><img src="{{ asset('assets') }}/img/front-pages/landing-page/3DHub logo1.png" alt="3DHub" style="height: 56px; width: auto; max-height: 56px; object-fit: contain; display: block;" /></span>
            <span class="app-brand-text demo menu-text fw-bold" style="font-size: 1.4em;">3DHub Admin</span>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item">
            <a href="{{ route('admin_dashboard') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div>Dashboard</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('admin.add_3d_model') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-cube"></i>
              <div>Add 3D Model</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('admin.manage_map_pins') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-map-pin"></i>
              <div>Manage Map Pins</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('admin.manage_showcase') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-grid-alt"></i>
              <div>Manage Showcase</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('admin.client_uploads') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-cloud-upload"></i>
              <div>Client Uploads</div>
            </a>
          </li>
          <li class="menu-item active">
            <a href="{{ route('admin.manage_users') }}" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div>Manage Users</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('landing') }}" class="menu-link" target="_blank">
              <i class="menu-icon tf-icons bx bx-map"></i>
              <div>View Portal</div>
            </a>
          </li>
        </ul>
      </aside>
      <div class="layout-page">
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
          <div class="navbar-brand demo d-flex align-items-center py-0 me-3">
            <button class="admin-menu-toggle btn btn-icon d-xl-none me-2 border-0 bg-transparent p-0" type="button" aria-label="Toggle menu"><i class="bx bx-menu icon-lg"></i></button>
          </div>
          <div class="navbar-nav-right d-flex align-items-center ms-auto">
            <a href="{{ route('admin_dashboard') }}" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
          </div>
        </nav>
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold mb-4">Waitlist & Pending Requests</h4>
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
              var actions = '<div class="d-flex flex-wrap gap-2">' +
                '<button type="button" class="btn btn-sm btn-success approve-req-btn" data-id="' + r.id + '">Approve</button>' +
                '<button type="button" class="btn btn-sm btn-danger reject-req-btn" data-id="' + r.id + '">Reject</button>' +
                '</div>';
              return '<tr><td>' + (r.email || '') + '</td><td>' + (r.name || '') + '</td><td>' + (r.company_name || '—') + '</td><td>' + (r.reason_for_access || '—') + '</td><td>' + actions + '</td></tr>';
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
                : (isSuperAdmin ? '<span class="badge bg-label-dark">Super Admin</span>' : (isPending ? '<span class="badge bg-label-warning">Pending</span>' : (isAdmin ? '<span class="badge bg-label-success">Admin</span>' : (isTrusted ? '<span class="badge bg-label-primary">Trusted</span>' : '<span class="badge bg-label-secondary">Registered</span>'))));

              if (isAdmin || isSuperAdmin || isRemoved || isPending) {
                return '<tr><td>' + (u.email || '') + '</td><td>' + (u.name || '') + '</td><td>' + (u.username || '') + '</td><td>' + roleBadge + '</td><td><span class="text-muted small">—</span></td></tr>';
              }

              var action = '<div class="d-flex flex-wrap gap-2">';
              if (isTrusted) {
                action += '<button type="button" class="btn btn-sm btn-outline-warning downgrade-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Downgrade to registered</button>';
                if (currentRole === 'superadmin') {
                  action += '<button type="button" class="btn btn-sm btn-outline-primary promote-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Promote to admin</button>';
                }
              } else {
                action += '<button type="button" class="btn btn-sm btn-outline-info upgrade-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Upgrade to Trusted</button>';
                if (currentRole === 'superadmin') {
                  action += '<button type="button" class="btn btn-sm btn-outline-primary promote-btn" data-email="' + (u.email || '').replace(/"/g, '&quot;') + '">Promote to admin</button>';
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
</body>
</html>
