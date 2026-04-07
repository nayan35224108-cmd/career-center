<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$stmt = $pdo->query("SELECT * FROM jobs ORDER BY created_at DESC");
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jobs | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <style>
    .admin-layout { display:flex; min-height:100vh; }
    .admin-sidebar { width:250px; background:#1a1a2e; color:#fff; padding:20px; }
    .admin-sidebar a { display:block; color:#aaa; padding:12px 15px; text-decoration:none; border-radius:6px; margin-bottom:5px; }
    .admin-sidebar a.active,.admin-sidebar a:hover { background:#16213e; color:#fff; }
    .admin-main { flex:1; padding:30px; background:#f5f5f5; }
    .btn { display:inline-block; padding:8px 12px; border-radius:8px; text-decoration:none; }
    .btn-primary { background:#7a0f18; color:#fff; }
    .btn-warning { background:#f59e0b; color:#fff; }
    .btn-danger { background:#ef4444; color:#fff; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; }
    th,td { padding:12px; border-bottom:1px solid #e5e7eb; text-align:left; vertical-align:top; }
    th { background:#f9fafb; }
    .badge { padding:3px 10px; border-radius:20px; font-size:12px; }
    .badge-published { background:#d1fae5; color:#065f46; }
    .badge-draft { background:#fee2e2; color:#991b1b; }
  </style>
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <h2>UCC Admin</h2>
    <a href="/admin">Dashboard</a>
    <a href="/admin/courses">Kelola Courses</a>
    <a href="/admin/jobs" class="active">Lowongan Kerja</a>
    <a href="/admin/news">Berita</a>
    <a href="/admin/master-categories">Master Kategori</a>
    <a href="/admin/master-pengajar">Master Pengajar</a>
    <a href="/admin/registrations">Pendaftaran</a>
    <a href="/admin/users">Users</a>
      <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
    <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
  </aside>
  <main class="admin-main">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
      <h1>Lowongan Kerja</h1>
      <a href="/admin/jobs/add" class="btn btn-primary">+ Tambah Lowongan</a>
    </div>
    <table>
      <thead>
        <tr>
          <th>Poster</th><th>Judul</th><th>Perusahaan</th><th>Deadline</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($jobs as $job): ?>
        <tr>
          <td><?php if (!empty($job['poster_image'])): ?><img src="<?= e($job['poster_image']) ?>" alt="poster" style="width:56px;height:56px;object-fit:cover;border-radius:8px;"><?php else: ?>-<?php endif; ?></td>
          <td><?= e($job['title']) ?></td>
          <td><?= e($job['company_name']) ?></td>
          <td><?= $job['deadline_at'] ? date('d M Y', strtotime($job['deadline_at'])) : '-' ?></td>
          <td><span class="badge <?= (int)$job['is_published'] === 1 ? 'badge-published' : 'badge-draft' ?>"><?= (int)$job['is_published'] === 1 ? 'Published' : 'Draft' ?></span></td>
          <td>
            <a class="btn btn-warning" href="/admin/jobs/edit/<?= (int)$job['id'] ?>">Edit</a>
            <a class="btn btn-danger" href="/admin/jobs/delete/<?= (int)$job['id'] ?>" onclick="return confirm('Hapus lowongan ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
