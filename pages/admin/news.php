<?php
require_once __DIR__ . '/../../config.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];

// Get news list
$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Kelola Berita';
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
    .btn-add { background: #10b981; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; }
    .btn-add:hover { background: #059669; }
    .news-table { width: 100%; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-collapse: collapse; }
    .news-table th, .news-table td { padding: 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
    .news-table th { background: #f9fafb; font-weight: 600; color: #374151; }
    .news-table tr:hover { background: #f9fafb; }
    .news-title { font-weight: 500; color: #1a1a2e; }
    .news-excerpt { color: #666; font-size: 0.9rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
    .status-published { background: #d1fae5; color: #065f46; }
    .status-draft { background: #fef3c7; color: #92400e; }
    .actions a { margin-right: 10px; text-decoration: none; font-size: 0.9rem; }
    .actions .edit { color: #3b82f6; }
    .actions .delete { color: #ef4444; }
    .actions a:hover { text-decoration: underline; }
    .empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 10px; }
    .empty-state p { color: #666; margin-bottom: 20px; }
    @media (max-width: 768px) {
      .admin-sidebar { display: none; }
      .admin-main { padding: 20px; }
      .news-table { font-size: 0.85rem; }
      .news-table th, .news-table td { padding: 10px; }
    }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <h2>UCC Admin</h2>
      <a href="/admin">Dashboard</a>
      <a href="/admin/courses">Kelola Courses</a>
      <a href="/admin/jobs">Lowongan Kerja</a>
      <a href="/admin/news" class="active">Berita</a>
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
        <h1><?= e($pageTitle) ?></h1>
        <a href="/admin/news/add" class="btn-add">+ Tambah Berita</a>
      </div>

      <?php if (empty($newsList)): ?>
        <div class="empty-state">
          <p>Belum ada berita. Klik tombol di atas untuk menambah berita pertama.</p>
          <a href="/admin/news/add" class="btn-add">+ Tambah Berita</a>
        </div>
      <?php else: ?>
        <table class="news-table">
          <thead>
            <tr>
              <th>Judul</th>
              <th>Ringkasan</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($newsList as $news): ?>
              <tr>
                <td>
                  <div class="news-title"><?= e($news['title']) ?></div>
                </td>
                <td>
                  <div class="news-excerpt"><?= e($news['excerpt'] ?: '-') ?></div>
                </td>
                <td>
                  <span class="status-badge status-<?= $news['status'] ?>">
                    <?= $news['status'] === 'published' ? 'Dipublikasi' : 'Draft' ?>
                  </span>
                </td>
                <td><?= date('d M Y', strtotime($news['created_at'])) ?></td>
                <td class="actions">
                  <a href="/news/<?= e($news['slug']) ?>" target="_blank" class="view">Lihat</a>
                  <a href="/admin/news/edit/<?= $news['id'] ?>" class="edit">Edit</a>
                  <a href="/admin/news/delete/<?= $news['id'] ?>" class="delete" onclick="return confirm('Hapus berita ini?')">Hapus</a>
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