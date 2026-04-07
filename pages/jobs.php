<?php
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'jobs';
$pageTitle = 'Lowongan Pekerjaan';

global $pdo;
auto_unpublish_expired_jobs();

$q = sanitize($_GET['q'] ?? '');
$location_filter = sanitize($_GET['location'] ?? '');
$type_filter = sanitize($_GET['type'] ?? '');

$sql = 'SELECT * FROM jobs WHERE is_published = 1 AND (deadline_at IS NULL OR deadline_at >= NOW())';
$params = [];

if ($q !== '') {
  $sql .= ' AND (title LIKE ? OR company_name LIKE ?)';
  $params[] = '%' . $q . '%';
  $params[] = '%' . $q . '%';
}
if ($location_filter !== '') {
  $sql .= ' AND location = ?';
  $params[] = $location_filter;
}
if ($type_filter !== '') {
  $sql .= ' AND employment_type = ?';
  $params[] = $type_filter;
}
$sql .= ' ORDER BY COALESCE(deadline_at, \'9999-12-31 23:59:59\') ASC, created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$stmt = $pdo->query("SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
$locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="inner-hero" style="min-height:32dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Jobs Board</span>
      <h1>Lowongan Pekerjaan</h1>
      <p class="sub">Temukan peluang kerja dan magang terbaru dari mitra dan perusahaan.</p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2><?= count($jobs) ?> Lowongan Ditemukan</h2>
    
    <form method="GET" action="/jobs" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
      <div style="flex:1;min-width:200px;">
        <label for="q" style="display:block;margin-bottom:4px;font-weight:600;font-size:0.9rem;">Cari</label>
        <input id="q" name="q" type="text" value="<?= e($q) ?>" placeholder="Nama lowongan atau perusahaan" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
      </div>
      <div style="min-width:160px;">
        <label for="location" style="display:block;margin-bottom:4px;font-weight:600;font-size:0.9rem;">Lokasi</label>
        <select id="location" name="location" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
          <option value="">Semua lokasi</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?= e($loc) ?>" <?= $location_filter === $loc ? 'selected' : '' ?>><?= e($loc) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="min-width:140px;">
        <label for="type" style="display:block;margin-bottom:4px;font-weight:600;font-size:0.9rem;">Tipe</label>
        <select id="type" name="type" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
          <option value="">Semua tipe</option>
          <option value="full_time" <?= $type_filter === 'full_time' ? 'selected' : '' ?>>Full Time</option>
          <option value="part_time" <?= $type_filter === 'part_time' ? 'selected' : '' ?>>Part Time</option>
          <option value="internship" <?= $type_filter === 'internship' ? 'selected' : '' ?>>Internship</option>
          <option value="contract" <?= $type_filter === 'contract' ? 'selected' : '' ?>>Contract</option>
        </select>
      </div>
      <div style="padding-bottom:2px;">
        <button type="submit" class="btn" style="background:#7a0f18;color:#fff;padding:10px 16px;border-radius:8px;border:0;cursor:pointer;">Cari</button>
        <?php if ($q !== '' || $location_filter !== '' || $type_filter !== ''): ?>
          <a href="/jobs" class="btn" style="background:#e5e7eb;color:#111827;padding:10px 16px;border-radius:8px;text-decoration:none;margin-left:4px;">Reset</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="inner-sec sec-soft-cyan">
  <div class="section-wrap">
    <div class="problem-grid">
      <?php foreach ($jobs as $job): ?>
        <div class="problem-card">
          <?php if (!empty($job['poster_image'])): ?>
            <img src="<?= e($job['poster_image']) ?>" alt="<?= e($job['title']) ?>" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;margin-bottom:10px;">
          <?php endif; ?>
          <h3><?= e($job['title']) ?></h3>
          <p style="margin:6px 0;"><strong><?= e($job['company_name']) ?></strong></p>
          <p><?= e(mb_strimwidth(strip_tags((string) $job['description']), 0, 180, '...')) ?></p>
          <div style="margin-top:12px;font-size:0.85rem;color:#666;">
            <span>📍 <?= e($job['location'] ?: 'Tidak disebutkan') ?></span><br>
            <span>💼 <?= e(str_replace('_', ' ', $job['employment_type'])) ?></span><br>
            <span>⏳ <?= $job['deadline_at'] ? date('d M Y', strtotime($job['deadline_at'])) : 'Tanpa batas waktu' ?></span>
          </div>
          <a href="/jobs/<?= e($job['slug']) ?>" class="btn btn-sm" style="margin-top: 12px; background: #7a0f18; color: #fff; display: inline-block;">Detail →</a>
        </div>
      <?php endforeach; ?>

      <?php if (empty($jobs)): ?>
        <div class="problem-card" style="grid-column: 1/-1; text-align: center;">
          <p>Belum ada lowongan aktif saat ini.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
