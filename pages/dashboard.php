<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$user = get_user();
$my_courses = get_user_courses($user['id']);
$all_courses = get_all_courses();

$stats = [
  'total' => count($my_courses),
  'pending' => count(array_filter($my_courses, fn($c) => $c['status'] === 'pending')),
  'confirmed' => count(array_filter($my_courses, fn($c) => $c['status'] === 'confirmed')),
  'completed' => count(array_filter($my_courses, fn($c) => $c['status'] === 'completed')),
];

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout dashboard-page';
$pageTitle = 'Dashboard';
$skipLayout = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Unisbank Career Centre</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<div class="user-dashboard-layout">
  <!-- Sidebar -->
  <aside class="user-sidebar">
    <div class="sidebar-header">
      <a class="logo" href="/">
        <img src="/uploads/logo.png" alt="UCC" style="width:40px;">
        <span>UCC</span>
      </a>
    </div>
    <nav class="user-nav">
      <a href="/dashboard" class="active">Dashboard</a>
      <a href="/course">Course</a>
      <a href="/jobs">Lowongan</a>
      <a href="/">Beranda</a>
    </nav>
    <div class="sidebar-footer">
      <a href="/logout" class="logout-link">Logout</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="user-main">
    <div class="dashboard-header">
      <h1>👋 Halo, <?= e($user['name']) ?>!</h1>
      <p>Selamat datang di Unisbank Career Centre</p>
    </div>

    <div class="user-content">
      <!-- Stats -->
      <div class="dashboard-stats">
        <div class="stat-card">
          <h3>📚 Total Course</h3>
          <div class="value"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card">
          <h3>⏳ Pending</h3>
          <div class="value"><?= $stats['pending'] ?></div>
        </div>
        <div class="stat-card">
          <h3>✅ Dikonfirmasi</h3>
          <div class="value"><?= $stats['confirmed'] ?></div>
        </div>
        <div class="stat-card">
          <h3>🎓 Selesai</h3>
          <div class="value"><?= $stats['completed'] ?></div>
        </div>
      </div>

      <div class="dashboard-content">
        <!-- My Courses -->
        <div class="content-card">
          <h2>📋 Course Saya</h2>
          <?php if (empty($my_courses)): ?>
            <div class="empty-state">
              <div class="icon">📚</div>
              <h3>Belum ada course</h3>
              <p>Silakan pilih course yang tersedia</p>
              <a href="/course" class="btn btn-primary" style="margin-top: 16px;">Lihat Course</a>
            </div>
          <?php else: ?>
            <div class="course-list">
              <?php foreach ($my_courses as $course): ?>
                <div class="course-item">
                  <div class="course-thumb" style="background: linear-gradient(135deg, #7a0f18, #c62828); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;">
                    <?= strtoupper(substr($course['title'], 0, 2)) ?>
                  </div>
                  <div class="course-info">
                    <h4><?= e($course['title']) ?></h4>
                    <p><?= e($course['category']) ?></p>
                    <div class="course-meta">
                      <span>📅 <?= $course['jadwal'] ? date('d M Y', strtotime($course['jadwal'])) : 'TBA' ?></span>
                      <span class="badge badge-<?= $course['status'] ?>"><?= ucfirst($course['status']) ?></span>
                    </div>
                  </div>
                  <a href="/course/<?= $course['id'] ?>" class="btn btn-sm btn-primary">Detail</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="sidebar">
          <div class="sidebar-card">
            <h3>👤 Profil</h3>
            <p><strong><?= e($user['name']) ?></strong></p>
            <p style="color: #666; font-size: 0.875rem;"><?= e($user['email']) ?></p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
</html>
