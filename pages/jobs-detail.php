<?php
$extraStyles = '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/auth.css">
<style>
.site-header .nav a { color: #fff; }
.site-header .nav .login-btn {
  background: #7a0f18;
  color: #fff !important;
  border: 1px solid rgba(255, 255, 255, 0.28);
  padding: 8px 20px;
  border-radius: 20px;
  display: inline-block;
  text-decoration: none;
}
.site-header .nav .login-btn:hover {
  background: #fff;
  color: #7a0f18 !important;
  border-color: #7a0f18;
}
</style>';

$slug = sanitize($_GET['slug'] ?? '');
$job = get_job_by_slug($slug);

if (!$job) {
  include __DIR__ . '/../templates/404.php';
  exit;
}

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'jobs';
$pageTitle = $job['title'];
?>
<div class="course-detail-header">
  <div class="container">
    <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff; margin-bottom: 12px; display: inline-block;">
      <?= e(str_replace('_', ' ', $job['employment_type'])) ?>
    </span>
    <h1><?= e($job['title']) ?></h1>
    <p class="meta">🏢 <?= e($job['company_name']) ?> | 📍 <?= e($job['location'] ?: 'Tidak disebutkan') ?></p>
  </div>
</div>

<div class="course-detail-body">
  <div class="container">
    <div class="course-detail-grid">
      <div>
        <div class="course-detail-card">
          <?php if (!empty($job['poster_image'])): ?>
            <img src="<?= e($job['poster_image']) ?>" alt="<?= e($job['title']) ?>" style="width:100%;max-width:380px;aspect-ratio:1/1;object-fit:cover;border-radius:12px;margin-bottom:16px;">
          <?php endif; ?>
          <h2>Deskripsi Pekerjaan</h2>
          <div class="job-content"><?= $job['description'] ?></div>

          <?php if (!empty($job['requirements'])): ?>
            <h2 style="margin-top:20px;">Kualifikasi</h2>
            <div class="job-content"><?= $job['requirements'] ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div class="enroll-section">
          <div class="course-info-list">
            <div class="course-info-item"><div class="icon">🏢</div><div><div class="label">Perusahaan</div><div class="value"><?= e($job['company_name']) ?></div></div></div>
            <div class="course-info-item"><div class="icon">📍</div><div><div class="label">Lokasi</div><div class="value"><?= e($job['location'] ?: '-') ?></div></div></div>
            <div class="course-info-item"><div class="icon">💼</div><div><div class="label">Tipe</div><div class="value"><?= e(str_replace('_', ' ', $job['employment_type'])) ?></div></div></div>
            <div class="course-info-item"><div class="icon">⏳</div><div><div class="label">Deadline</div><div class="value"><?= $job['deadline_at'] ? date('d M Y H:i', strtotime($job['deadline_at'])) : 'Tanpa batas waktu' ?></div></div></div>
          </div>
          <?php if (!empty($job['application_url'])): ?>
            <a href="<?= e($job['application_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-block">Apply Sekarang</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
