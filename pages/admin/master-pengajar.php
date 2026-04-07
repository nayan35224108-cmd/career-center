<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } elseif (($_POST['action'] ?? '') === 'add') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    if ($name === '') {
      $error = 'Nama pengajar wajib diisi';
    } else {
      try {
        $stmt = $pdo->prepare('INSERT INTO master_pengajar (name, email) VALUES (?, ?)');
        $stmt->execute([$name, $email ?: null]);
      } catch (Exception $e) {
        $error = 'Pengajar sudah ada atau tidak valid';
      }
    }
  } elseif (($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
      $stmt = $pdo->prepare('DELETE FROM master_pengajar WHERE id = ?');
      $stmt->execute([$id]);
    }
  }
}

$stmt = $pdo->query('SELECT * FROM master_pengajar ORDER BY name ASC');
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Master Pengajar | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; flex-shrink: 0; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; }
    .card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
    .btn { border: 0; border-radius: 8px; padding: 8px 12px; cursor: pointer; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .btn-danger { background: #ef4444; color: #fff; }
    input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
    .row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; }
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
      <a href="/admin/master-pengajar" class="active">Master Pengajar</a>
      <a href="/admin/registrations">Pendaftaran</a>
      <a href="/admin/users">Users</a>
      <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>
    <main class="admin-main">
      <h1>Master Pengajar</h1>
      <?php if ($error): ?><div class="card" style="color:#991b1b;"><?= e($error) ?></div><?php endif; ?>
      <div class="card">
        <form method="POST">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="add">
          <div class="row">
            <input name="name" type="text" placeholder="Nama pengajar" required>
            <input name="email" type="email" placeholder="Email (opsional)">
            <button class="btn btn-primary" type="submit">Tambah</button>
          </div>
        </form>
      </div>
      <div class="card">
        <table>
          <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $i => $row): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['email'] ?? '-') ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Hapus pengajar ini?');">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <button class="btn btn-danger" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
