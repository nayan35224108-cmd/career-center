<?php
require_once __DIR__ . '/../../config.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$error = '';
$success = '';
$allowed_status = ['pending', 'confirmed', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $registration_id = (int) ($_POST['registration_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? '');

    if ($registration_id <= 0 || !in_array($new_status, $allowed_status, true)) {
      $error = 'Data update status tidak valid';
    } else {
      // Get current registration info before updating
      $stmt = $pdo->prepare('SELECT cr.*, c.title as course_title, c.jadwal, c.lokasi, c.pengajar, c.category, u.name as user_name, u.email as user_email FROM course_registrations cr JOIN courses c ON c.id = cr.course_id JOIN users u ON u.id = cr.user_id WHERE cr.id = ?');
      $stmt->execute([$registration_id]);
      $reg_info = $stmt->fetch();
      
      $stmt = $pdo->prepare('UPDATE course_registrations SET status = ? WHERE id = ?');
      $ok = $stmt->execute([$new_status, $registration_id]);
      
      if ($ok) {
        $success = 'Status pendaftaran berhasil diperbarui';
        
        // Send email notification to user
        if ($reg_info) {
          $status_text = ucfirst($new_status);
          $status_color = match($new_status) {
            'confirmed' => '#10b981',
            'completed' => '#3b82f6',
            'cancelled' => '#ef4444',
            default => '#f59e0b'
          };
          
          $email_subject = 'Status Pendaftaran Course Diperbarui - ' . e($reg_info['course_title']);
          $email_content = '
            <h3>Pemberitahuan Perubahan Status</h3>
            <p>Halo, ' . e($reg_info['user_name']) . '!</p>
            <p>Status pendaftaran Anda telah diperbarui:</p>
            <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;">
              <h4 style="margin: 0 0 10px 0;">' . e($reg_info['course_title']) . '</h4>
              <p style="margin: 5px 0;"><strong>Kategori:</strong> ' . e($reg_info['category']) . '</p>
              <p style="margin: 5px 0;"><strong>Jadwal:</strong> ' . ($reg_info['jadwal'] ? date('d M Y, H:i', strtotime($reg_info['jadwal'])) : 'Akan diinformasikan') . '</p>
              <p style="margin: 5px 0;"><strong>Lokasi:</strong> ' . e($reg_info['lokasi'] ?: 'Online') . '</p>
            </div>
            <p><strong>Status Pendaftaran:</strong> <span style="color: ' . $status_color . '; font-weight: bold;">' . $status_text . '</span></p>
            <p>' . ($new_status === 'confirmed' ? 'Selamat! Pendaftaran Anda telah dikonfirmasi. Silakan hadir sesuai jadwal yang telah ditentukan.' : ($new_status === 'completed' ? 'Terima kasih telah mengikuti course ini. Sertifikat Anda akan dikirimkan segera.' : ($new_status === 'cancelled' ? 'Mohon maaf, pendaftaran Anda telah dibatalkan. Silakan hubungi admin untuk informasi lebih lanjut.' : 'Pendaftaran Anda masih dalam proses verifikasi.'))) . '</p>
            <p style="margin-top: 20px;">
              <a href="' . SITE_URL . '/dashboard" style="background: #7a0f18; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Lihat Dashboard</a>
            </p>
            <p style="margin-top: 20px; color: #666;">Terima kasih telah menggunakan Unisbank Career Centre!</p>
          ';
          send_email_template($reg_info['user_email'], $email_subject, $email_content);
        }
      } else {
        $error = 'Gagal memperbarui status pendaftaran';
      }
    }
  }
}

$filter_status = sanitize($_GET['status'] ?? '');
$params = [];
$sql = 'SELECT cr.id, cr.status, cr.registered_at, c.title AS course_title, c.jadwal, u.name AS user_name, u.email AS user_email
  FROM course_registrations cr
  JOIN courses c ON c.id = cr.course_id
  JOIN users u ON u.id = cr.user_id';

if (in_array($filter_status, $allowed_status, true)) {
  $sql .= ' WHERE cr.status = ?';
  $params[] = $filter_status;
}

$sql .= ' ORDER BY cr.registered_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

$count_stmt = $pdo->query("SELECT status, COUNT(*) AS total FROM course_registrations GROUP BY status");
$status_counts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
foreach ($count_stmt->fetchAll() as $row) {
  $status_counts[$row['status']] = (int) $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; flex-shrink: 0; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; overflow-x: auto; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .card { background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .card .label { font-size: 0.82rem; color: #6b7280; }
    .card .value { font-size: 1.5rem; font-weight: 700; color: #111827; }
    .toolbar { background: #fff; border-radius: 10px; padding: 12px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
    th, td { padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    th { background: #f9fafb; font-size: 0.85rem; color: #374151; }
    .badge { display: inline-block; border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-confirmed { background: #d1fae5; color: #065f46; }
    .badge-completed { background: #dbeafe; color: #1e40af; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .btn { border: 0; border-radius: 8px; padding: 8px 10px; cursor: pointer; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .alert { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
    .alert-error { background: #fee2e2; color: #991b1b; }
    .alert-success { background: #d1fae5; color: #065f46; }
    select { padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; }
    .empty { text-align: center; color: #6b7280; padding: 24px; background: #fff; border-radius: 10px; }
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
      <a href="/admin/registrations" class="active">Pendaftaran</a>
      <a href="/admin/users">Users</a>
      <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1>Manajemen Pendaftaran</h1>
      </div>

      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div class="cards">
        <div class="card"><div class="label">Pending</div><div class="value"><?= $status_counts['pending'] ?></div></div>
        <div class="card"><div class="label">Confirmed</div><div class="value"><?= $status_counts['confirmed'] ?></div></div>
        <div class="card"><div class="label">Completed</div><div class="value"><?= $status_counts['completed'] ?></div></div>
        <div class="card"><div class="label">Cancelled</div><div class="value"><?= $status_counts['cancelled'] ?></div></div>
      </div>

      <div class="toolbar">
        <form method="GET" action="/admin/registrations" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <label for="status" style="font-weight:600;">Filter status:</label>
          <select id="status" name="status">
            <option value="">Semua status</option>
            <?php foreach ($allowed_status as $status_item): ?>
              <option value="<?= e($status_item) ?>" <?= $filter_status === $status_item ? 'selected' : '' ?>><?= ucfirst($status_item) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary" type="submit">Terapkan</button>
          <?php if ($filter_status !== ''): ?><a href="/admin/registrations">Reset</a><?php endif; ?>
        </form>
      </div>

      <?php if (empty($registrations)): ?>
        <div class="empty">Belum ada data pendaftaran.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Peserta</th>
              <th>Course</th>
              <th>Jadwal</th>
              <th>Terdaftar</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($registrations as $item): ?>
              <tr>
                <td>
                  <strong><?= e($item['user_name']) ?></strong><br>
                  <small><?= e($item['user_email']) ?></small>
                </td>
                <td><?= e($item['course_title']) ?></td>
                <td><?= !empty($item['jadwal']) ? date('d M Y H:i', strtotime($item['jadwal'])) : '-' ?></td>
                <td><?= !empty($item['registered_at']) ? date('d M Y H:i', strtotime($item['registered_at'])) : '-' ?></td>
                <td><span class="badge badge-<?= e($item['status']) ?>"><?= ucfirst(e($item['status'])) ?></span></td>
                <td>
                  <form method="POST" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="registration_id" value="<?= (int) $item['id'] ?>">
                    <select name="status">
                      <?php foreach ($allowed_status as $status_item): ?>
                        <option value="<?= e($status_item) ?>" <?= $item['status'] === $status_item ? 'selected' : '' ?>><?= ucfirst($status_item) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
