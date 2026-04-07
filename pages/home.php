<?php
require_once __DIR__ . '/../includes/settings.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'home';
$pageTitle = 'Home';

// Get hero settings
$hero_title = get_setting($pdo, 'hero_title', 'Portal Pelatihan Karir & Soft Skill untuk Mahasiswa');
$hero_subtitle = get_setting($pdo, 'hero_subtitle', 'Siap menghadapi dunia kerja? Kami membantu mahasiswa Unisbank mengembangkan potensi dan skill yang dibutuhkan perusahaan.');
$hero_cta_primary_text = get_setting($pdo, 'hero_cta_primary_text', 'Lihat Course');
$hero_cta_primary_link = get_setting($pdo, 'hero_cta_primary_link', '/course');
$hero_cta_secondary_text = get_setting($pdo, 'hero_cta_secondary_text', 'Tentang Kami →');
$hero_cta_secondary_link = get_setting($pdo, 'hero_cta_secondary_link', '/about');
?>
<div class="inner-hero" style="background:linear-gradient(135deg,rgba(122,15,24,.35),rgba(198,40,40,.45)),url('/uploads/hero-bg.jpg') center/cover no-repeat">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Unisbank Career Centre</span>
      <h1><?= e($hero_title) ?></h1>
      <p class="sub"><?= e($hero_subtitle) ?></p>
      <div class="inner-cta-row">
        <a href="<?= e($hero_cta_primary_link) ?>" class="cta-btn"><?= e($hero_cta_primary_text) ?></a>
        <a href="<?= e($hero_cta_secondary_link) ?>" class="inner-pill"><?= e($hero_cta_secondary_text) ?></a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-bubble">
        <img src="/uploads/hero.jpg" alt="UCC" style="width:100%;height:100%;object-fit:cover;border-radius:999px;">
      </div>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2>Selamat Datang di UCC</h2>
    <p>Unisbank Career Centre adalah platform pelatihan karir yang dirancang untuk membantu mahasiswa Unisbank mempersiapkan diri menghadapi dunia kerja.</p>
    
    <div class="service-grid">
      <div class="service-card">
        <h3>💼 Job Interview</h3>
        <p>Teknik wawancara profesional untuk menghadapi seleksi kerja.</p>
      </div>
      <div class="service-card">
        <h3>📊 Profiling</h3>
        <p>Pemahaman diri dan Identifikasi strengths & weaknesses.</p>
      </div>
      <div class="service-card">
        <h3>🤝 Soft Skill</h3>
        <p>Komunikasi, kepemimpinan, dan teamwork.</p>
      </div>
    </div>
  </div>
</div>

<div class="inner-sec sec-soft-cyan">
  <div class="section-wrap">
    <h2>Program Pelatihan Kami</h2>
    <div class="problem-grid">
      <div class="problem-card">
        <h3>🎯 Job Interview</h3>
        <p>Teknik wawancara profesional</p>
      </div>
      <div class="problem-card">
        <h3>📋 Profiling</h3>
        <p>Pemahaman diri</p>
      </div>
      <div class="problem-card">
        <h3>💪 Soft Skill</h3>
        <p>Komunikasi & Kepemimpinan</p>
      </div>
      <div class="problem-card">
        <h3>📝 CV & Letter</h3>
        <p>Resumé & Motivation Letter</p>
      </div>
      <div class="problem-card">
        <h3>🎯 Target Setting</h3>
        <p>Strategi pencapaian karir</p>
      </div>
      <div class="problem-card">
        <h3>🧩 Problem Solving</h3>
        <p>EMBA menghadapi masalah</p>
      </div>
    </div>
    <div style="margin-top:20px;text-align:center">
      <a href="/course" class="cta-btn">Course Selengkapnya →</a>
    </div>
  </div>
</div>

<div class="inner-sec sec-soft-cream">
  <div class="section-wrap">
    <h2>Mengapa Memilih UCC?</h2>
    <div class="why-grid">
      <div class="why-card">
        <h3>📚</h3>
        <p>Kurikulum Terstruktur</p>
      </div>
      <div class="why-card">
        <h3>🎯</h3>
        <p>Target Setting</p>
      </div>
      <div class="why-card">
        <h3>🤝</h3>
        <p>Praktis & Terapan</p>
      </div>
      <div class="why-card">
        <h3>🎓</h3>
        <p>Program Akreditasi</p>
      </div>
    </div>
  </div>
</div>
