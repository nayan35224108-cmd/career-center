<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');

    if ($user_id <= 0) {
      $error = 'User tidak valid';
    } elseif ($action === 'toggle_active') {
      $current = (int) ($_POST['current_active'] ?? 1);
      $new_value = $current === 1 ? 0 : 1;
      $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
      if ($stmt->execute([$new_value, $user_id])) {
        $success = 'Status user berhasil diperbarui';
      } else {
        $error = 'Gagal memperbarui status user';
      }
    }
  }
}

$q = sanitize($_GET['q'] ?? '');
$sql = 'SELECT * FROM users';
$params = [];
if ($q !== '') {
  $sql .= ' WHERE name LIKE ? OR email LIKE ?';
  $params[] = '%' . $q . '%';
  $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$totals = [
  'all' => 0,
  'active' => 0,
  'inactive' => 0,
  'must_change_password' => 0,
];
foreach ($users as $u) {
  $totals['all']++;
  if ((int) $u['is_active'] === 1) {
    $totals['active']++;
  } else {
    $totals['inactive']++;
  }
  if ((int) $u['must_change_password'] === 1) {
    $totals['must_change_password']++;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; flex-shrink: 0; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; overflow-x: auto; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .card { background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .card .label { font-size: 0.82rem; color: #6b7280; }
    .card .value { font-size: 1.5rem; font-weight: 700; color: #111827; }
    .toolbar { background: #fff; border-radius: 10px; padding: 12px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
    th, td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    th { background: #f9fafb; font-size: 0.85rem; color: #374151; }
    .badge { display: inline-block; border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; }
    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
    .btn { border: 0; border-radius: 8px; padding: 8px 10px; cursor: pointer; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .btn-muted { background: #e5e7eb; color: #111827; }
    .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .alert-success { background: #d1fae5; color: #065f46; }
    input[type="text"] { padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; min-width: 260px; }
    .btn-detail { background: #3b82f6; color: #fff; margin-right: 5px; }
    .btn-detail:hover { background: #2563eb; }
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
    .modal-overlay.active { display: flex; }
    .modal { background: #fff; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
    .modal-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 1.2rem; }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; }
    .modal-close:hover { color: #111; }
    .modal-body { padding: 20px; }
    .profile-header { text-align: center; margin-bottom: 20px; }
    .profile-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb; margin-bottom: 10px; }
    .profile-photo-placeholder { width: 100px; height: 100px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #6b7280; margin: 0 auto 10px; }
    .profile-name { font-size: 1.3rem; font-weight: 600; margin-bottom: 5px; }
    .profile-type { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
    .profile-type-mahasiswa { background: #dbeafe; color: #1d4ed8; }
    .profile-type-dosen { background: #fef3c7; color: #b45309; }
    .profile-type-user { background: #e5e7eb; color: #374151; }
    .profile-info { background: #f9fafb; border-radius: 8px; padding: 15px; }
    .profile-info-row { display: flex; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
    .profile-info-row:last-child { border-bottom: none; }
    .profile-info-label { width: 120px; color: #6b7280; font-size: 0.9rem; flex-shrink: 0; }
    .profile-info-value { flex: 1; font-weight: 500; }
    .profile-info-value.empty { color: #9ca3af; font-style: italic; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <h2>UCC Admin</h2>
      <a href="/admin">Dashboard</a>
      <a href="/admin/courses">Kelola Courses</a>
      <a href="/admin/jobs">Lowongan Kerja</a>
      <a href="/admin/news">Berita</a>
      <a href="/admin/master-categories">Master Kategori</a>
      <a href="/admin/master-pengajar">Master Pengajar</a>
      <a href="/admin/registrations">Pendaftaran</a>
      <a href="/admin/users" class="active">Users</a>
      <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>

    <main class="admin-main">
      <h1>Manajemen Users</h1>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div class="cards">
        <div class="card"><div class="label">Total Users</div><div class="value"><?= $totals['all'] ?></div></div>
        <div class="card"><div class="label">Active</div><div class="value"><?= $totals['active'] ?></div></div>
        <div class="card"><div class="label">Inactive</div><div class="value"><?= $totals['inactive'] ?></div></div>
        <div class="card"><div class="label">Must Change Password</div><div class="value"><?= $totals['must_change_password'] ?></div></div>
      </div>

      <div class="toolbar">
        <form method="GET" action="/admin/users" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <label for="q" style="font-weight:600;">Cari user:</label>
          <input id="q" name="q" type="text" value="<?= e($q) ?>" placeholder="Nama atau email">
          <button class="btn btn-primary" type="submit">Cari</button>
          <?php if ($q !== ''): ?><a href="/admin/users" class="btn btn-muted">Reset</a><?php endif; ?>
        </form>
      </div>

      <table>
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Whatsapp</th>
            <th>Status</th>
            <th>Join Date</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <strong><?= e($u['name']) ?></strong><br>
                <small><?= (int) $u['must_change_password'] === 1 ? 'Wajib ganti password' : 'Password normal' ?></small>
              </td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['phone_whatsapp'] ?: '-') ?></td>
              <td>
                <?php if ((int) $u['is_active'] === 1): ?>
                  <span class="badge badge-active">Aktif</span>
                <?php else: ?>
                  <span class="badge badge-inactive">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td><?= !empty($u['created_at']) ? date('d M Y H:i', strtotime($u['created_at'])) : '-' ?></td>
              <td>
                <button class="btn btn-detail" type="button" onclick="showDetail(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">Detail</button>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="toggle_active">
                  <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                  <input type="hidden" name="current_active" value="<?= (int) $u['is_active'] ?>">
                  <button class="btn btn-primary" type="submit">
                    <?= (int) $u['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </main>
  </div>

  <!-- Modal Detail User -->
  <div class="modal-overlay" id="userModal" onclick="closeModal(event)">
    <div class="modal" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3>Detail User</h3>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="profile-header">
          <div id="profilePhoto"></div>
          <div class="profile-name" id="profileName"></div>
          <div id="profileType"></div>
        </div>
        <div class="profile-info">
          <div class="profile-info-row">
            <div class="profile-info-label">NIM/NIY</div>
            <div class="profile-info-value" id="profileNim">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Email</div>
            <div class="profile-info-value" id="profileEmail">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">WhatsApp</div>
            <div class="profile-info-value" id="profilePhone">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Fakultas</div>
            <div class="profile-info-value" id="profileFakultas">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Prodi</div>
            <div class="profile-info-value" id="profileProdi">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Status</div>
            <div class="profile-info-value" id="profileStatus">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Bergabung</div>
            <div class="profile-info-value" id="profileJoined">-</div>
          </div>
          <div class="profile-info-row">
            <div class="profile-info-label">Login Terakhir</div>
            <div class="profile-info-value" id="profileLastLogin">-</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showDetail(user) {
      // Photo
      const photoContainer = document.getElementById('profilePhoto');
      if (user.foto) {
        photoContainer.innerHTML = '<img src="' + user.foto + '" class="profile-photo" alt="Foto" onerror="this.parentElement.innerHTML=\'<div class=\\\'profile-photo-placeholder\\\'>👤</div>\'">';
      } else {
        photoContainer.innerHTML = '<div class="profile-photo-placeholder">👤</div>';
      }

      // Name
      document.getElementById('profileName').textContent = user.name || '-';

      // Type badge
      const typeContainer = document.getElementById('profileType');
      const userType = user.user_type || 'user';
      typeContainer.className = 'profile-type profile-type-' + userType;
      typeContainer.textContent = userType.charAt(0).toUpperCase() + userType.slice(1);

      // Info rows
      document.getElementById('profileNim').textContent = user.nim || user.username || '-';
      document.getElementById('profileNim').className = 'profile-info-value' + (user.nim || user.username ? '' : ' empty');

      document.getElementById('profileEmail').textContent = user.email || '-';
      document.getElementById('profileEmail').className = 'profile-info-value' + (user.email ? '' : ' empty');

      document.getElementById('profilePhone').textContent = user.phone_whatsapp || '-';
      document.getElementById('profilePhone').className = 'profile-info-value' + (user.phone_whatsapp ? '' : ' empty');

      document.getElementById('profileFakultas').textContent = user.fakultas || '-';
      document.getElementById('profileFakultas').className = 'profile-info-value' + (user.fakultas ? '' : ' empty');

      document.getElementById('profileProdi').textContent = user.prodi || '-';
      document.getElementById('profileProdi').className = 'profile-info-value' + (user.prodi ? '' : ' empty');

      // Status
      const statusEl = document.getElementById('profileStatus');
      statusEl.innerHTML = parseInt(user.is_active) === 1
        ? '<span class="badge badge-active">Aktif</span>'
        : '<span class="badge badge-inactive">Nonaktif</span>';

      // Dates
      document.getElementById('profileJoined').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
      document.getElementById('profileLastLogin').textContent = user.last_login ? new Date(user.last_login).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Belum pernah login';

      // Show modal
      document.getElementById('userModal').classList.add('active');
    }

    function closeModal(event) {
      if (!event || event.target.classList.contains('modal-overlay')) {
        document.getElementById('userModal').classList.remove('active');
      }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
  </script>
</body>
</html>
