<?php
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'course';
$pageTitle = 'Course & Pelatihan';

// Get courses from database
$courses = get_all_courses();

$categories = [];
foreach ($courses as $course_item) {
  $cat = trim((string) ($course_item['category'] ?? ''));
  if ($cat !== '') {
    $categories[$cat] = true;
  }
}
$category_options = array_keys($categories);
sort($category_options, SORT_NATURAL | SORT_FLAG_CASE);

$selected_category = sanitize($_GET['category'] ?? '');
if ($selected_category !== '') {
  $courses = array_values(array_filter($courses, function ($course_item) use ($selected_category) {
    return (string) ($course_item['category'] ?? '') === $selected_category;
  }));
}

usort($courses, function ($a, $b) {
  $a_ts = !empty($a['jadwal']) ? strtotime((string) $a['jadwal']) : PHP_INT_MAX;
  $b_ts = !empty($b['jadwal']) ? strtotime((string) $b['jadwal']) : PHP_INT_MAX;
  return $a_ts <=> $b_ts;
});
?>
<div class="inner-hero" style="min-height:40dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Course & Pelatihan</span>
      <h1>Program Pelatihan Kami</h1>
      <p class="sub">Pelatihan karir dan soft skill yang dirancang untuk membantu mahasiswa Unisbank menghadapi dunia kerja.</p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2><?= count($courses) ?> Program Pelatihan</h2>
    <p>Pilih program pelatihan yang sesuai dengan kebutuhanmu:</p>
    <form method="GET" action="/course" style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <label for="category" style="font-weight:600;">Filter kategori:</label>
      <select id="category" name="category" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;min-width:220px;">
        <option value="">Semua kategori</option>
        <?php foreach ($category_options as $category_option): ?>
          <option value="<?= e($category_option) ?>" <?= $selected_category === $category_option ? 'selected' : '' ?>>
            <?= e($category_option) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm" style="background:#7a0f18;color:#fff;">Terapkan</button>
      <?php if ($selected_category !== ''): ?>
        <a href="/course" class="btn btn-sm" style="background:#e5e7eb;color:#111827;">Reset</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="inner-sec sec-soft-cyan">
  <div class="section-wrap">
    <div class="problem-grid">
      <?php foreach ($courses as $course): ?>
        <div class="problem-card">
          <?php if (!empty($course['thumbnail'])): ?>
            <img src="<?= e($course['thumbnail']) ?>" alt="<?= e($course['title']) ?>" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;margin-bottom:10px;">
          <?php endif; ?>
          <h3><?= e($course['title']) ?></h3>
          <p><?= e(mb_strimwidth((string) ($course['description'] ?? ''), 0, 180, '...')) ?></p>
          <div style="margin-top: 12px; font-size: 0.85rem; color: #666;">
            <span>📅 <?= $course['jadwal'] ? date('d M Y', strtotime($course['jadwal'])) : 'Akan diinformasikan' ?></span><br>
            <span>👥 <?= e($course['kapasitas']) ?> orang</span><br>
            <span>📍 <?= e($course['lokasi'] ?: 'Online') ?></span>
          </div>
          <a href="/course/<?= $course['id'] ?>" class="btn btn-sm" style="margin-top: 12px; background: #7a0f18; color: #fff; display: inline-block;">Detail →</a>
        </div>
      <?php endforeach; ?>
      
      <?php if (empty($courses)): ?>
        <div class="problem-card" style="grid-column: 1/-1; text-align: center;">
          <p>Belum ada course yang tersedia. Stay tuned!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;align-items:start;">
    <div>
      <h2>Mengapa Pelatihan Ini Penting?</h2>
      <p>Pelatihan karir dan soft skill tidak hanya membantu mahasiswa dalam mendapatkan pekerjaan, tetapi juga membantu mereka:</p>
      <ul>
        <li>Berkembang secara profesional dan personal</li>
        <li>Menghadapi tantangan dunia kerja dengan lebih siap</li>
        <li>Membangun kompetensi yang dibutuhkan industri</li>
        <li>Mencapai kesuksesan karir jangka panjang</li>
      </ul>
    </div>
    <div>
      <h2>Cara Bergabung</h2>
      <p>Untuk bergabung dengan program pelatihan kami:</p>
      <ol>
        <li>Pilih program pelatihan yang tersedia</li>
        <li>Klik "Detail" untuk melihat informasi lengkap</li>
        <li>Login atau daftar akun</li>
        <li>Klik "Daftar Sekarang" untuk mendaftar</li>
      </ol>
      <div style="margin-top: 20px">
        <a href="/login" class="cta-btn">Login / Daftar →</a>
      </div>
    </div>
  </div>
</div>
