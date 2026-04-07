<?php
require_once __DIR__ . '/../../config.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$userCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM courses WHERE is_active = 1");
$courseCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM course_registrations");
$regCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM course_registrations WHERE status = 'pending'");
$pendingCount = $stmt->fetch()['total'];

$pageTitle = 'Admin Dashboard';
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout admin-page';
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
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-decoration: none; display: block; transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
    .stat-card h3 { color: #666; font-size: 0.9rem; margin: 0 0 10px 0; }
    .stat-card .value { font-size: 2rem; font-weight: bold; color: #333; }
    .stat-card.pending .value { color: #f59e0b; }
    .stat-card.success .value { color: #10b981; }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <h2>UCC Admin</h2>
      <a href="/admin" class="active">Dashboard</a>
      <a href="/admin/courses">Kelola Courses</a>
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
        <h1>Dashboard</h1>
        <div>
          <span> Halo, <strong><?= e($admin['name']) ?></strong></span>
        </div>
      </div>
      
      <div class="stats-grid">
        <a href="/admin/users" class="stat-card">
          <h3>Total Users</h3>
          <div class="value"><?= $userCount ?></div>
        </a>
        <a href="/admin/courses" class="stat-card">
          <h3>Total Courses</h3>
          <div class="value"><?= $courseCount ?></div>
        </a>
        <a href="/admin/registrations" class="stat-card">
          <h3>Total Pendaftaran</h3>
          <div class="value"><?= $regCount ?></div>
        </a>
        <a href="/admin/registrations" class="stat-card pending">
          <h3>Pending Approval</h3>
          <div class="value"><?= $pendingCount ?></div>
        </a>
      </div>
    </main>
  </div>
</body>
</html>
