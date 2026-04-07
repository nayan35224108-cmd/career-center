<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];

$stmt = $pdo->query("
  SELECT c.*, 
    COUNT(cr.id) as total_registrations,
    SUM(CASE WHEN cr.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_registrations
  FROM courses c 
  LEFT JOIN course_registrations cr ON c.id = cr.course_id 
  GROUP BY c.id
  ORDER BY c.created_at DESC
");
$courses = $stmt->fetchAll();

$pageTitle = 'Kelola Courses';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; flex-shrink: 0; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; overflow-x: auto; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .admin-header h1 { margin: 0; font-size: 1.5rem; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .btn-primary:hover { background: #5c0a11; }
    .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
    .btn-success { background: #10b981; color: #fff; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-warning { background: #f59e0b; color: #fff; }
    .table-container { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e5e7eb; }
    th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 0.85rem; text-transform: uppercase; }
    tr:hover { background: #f9fafb; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 500; }
    .badge-active { background: #d1fae5; color: #065f46; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
    .actions { display: flex; gap: 8px; }
    .empty-state { text-align: center; padding: 60px 20px; color: #6b7280; }
    .empty-state h3 { margin-bottom: 8px; color: #374151; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <h2>UCC Admin</h2>
      <a href="/admin">Dashboard</a>
      <a href="/admin/courses" class="active">Kelola Courses</a>
      <a href="/admin/jobs">Lowongan Kerja</a>
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
      <div class="admin-header">
        <h1>Kelola Courses</h1>
        <a href="/admin/courses/add" class="btn btn-primary">+ Tambah Course</a>
      </div>
      
      <?php if (empty($courses)): ?>
        <div class="empty-state">
          <h3>Belum ada course</h3>
          <p>Silakan tambah course baru untuk memulai</p>
          <a href="/admin/courses/add" class="btn btn-primary" style="margin-top: 16px;">+ Tambah Course</a>
        </div>
      <?php else: ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Thumbnail</th>
                <th>Kategori</th>
                <th>Jadwal</th>
                <th>Kapasitas</th>
                <th>Pendaftar</th>
                <th>Pengajar</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($courses as $i => $course): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= e($course['title']) ?></td>
                  <td>
                    <?php if (!empty($course['thumbnail'])): ?>
                      <img src="<?= e($course['thumbnail']) ?>" alt="thumb" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;">
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td><?= e($course['category'] ?? '-') ?></td>
                  <td><?= $course['jadwal'] ? date('d M Y H:i', strtotime($course['jadwal'])) : '-' ?></td>
                  <td><?= e($course['kapasitas']) ?></td>
                  <td>
                    <?php 
                      $total = (int) $course['total_registrations'];
                      $confirmed = (int) $course['confirmed_registrations'];
                    ?>
                    <span title="Total: <?= $total ?>, Confirmed: <?= $confirmed ?>">
                      <strong><?= $confirmed ?>/<?= $course['kapasitas'] ?></strong>
                      <?php if ($total > $confirmed): ?>
                        <small style="color:#f59e0b;">(+<?= $total - $confirmed ?> pending)</small>
                      <?php endif; ?>
                    </span>
                  </td>
                  <td><?= e($course['pengajar'] ?? '-') ?></td>
                  <td>
                    <span class="badge <?= $course['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                      <?= $course['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions">
                      <a href="/admin/courses/edit/<?= $course['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                      <a href="/admin/courses/delete/<?= $course['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus course ini?')">Hapus</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
