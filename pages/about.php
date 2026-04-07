<?php
require_once __DIR__ . '/../includes/settings.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'about';
$pageTitle = 'Tentang Kami';

// Get about settings
$about_hero_title = get_setting($pdo, 'about_hero_title', 'Unisbank Career Centre');
$about_hero_subtitle = get_setting($pdo, 'about_hero_subtitle', 'Portal pelatihan karir & soft skill untuk mahasiswa Unisbank');
$about_profile = get_setting($pdo, 'about_profile', 'Unisbank Career Centre (UCC) adalah pusat pengembangan karir untuk mahasiswa Unisbank. Kami menyediakan pelatihan, konsultasi, dan sumber daya untuk membantu mahasiswa mempersiapkan diri menghadapi dunia kerja.');

// Services with enabled status
$about_service1_enabled = get_setting($pdo, 'about_service1_enabled', '1');
$about_service1_icon = get_setting($pdo, 'about_service1_icon', '🎓');
$about_service1_title = get_setting($pdo, 'about_service1_title', 'Pelatihan');
$about_service1_desc = get_setting($pdo, 'about_service1_desc', 'Course dan workshop untuk mengembangkan skill');

$about_service2_enabled = get_setting($pdo, 'about_service2_enabled', '1');
$about_service2_icon = get_setting($pdo, 'about_service2_icon', '💡');
$about_service2_title = get_setting($pdo, 'about_service2_title', 'Konsultasi');
$about_service2_desc = get_setting($pdo, 'about_service2_desc', 'Bimbingan karir personal dengan tim ahli');

$about_service3_enabled = get_setting($pdo, 'about_service3_enabled', '1');
$about_service3_icon = get_setting($pdo, 'about_service3_icon', '📋');
$about_service3_title = get_setting($pdo, 'about_service3_title', 'Job Placement');
$about_service3_desc = get_setting($pdo, 'about_service3_desc', 'Informasi lowongan dan rekrutmen');

$about_service4_enabled = get_setting($pdo, 'about_service4_enabled', '1');
$about_service4_icon = get_setting($pdo, 'about_service4_icon', '📊');
$about_service4_title = get_setting($pdo, 'about_service4_title', 'Assessment');
$about_service4_desc = get_setting($pdo, 'about_service4_desc', 'Tes potensi dan kepribadian');

// Count enabled services
$enabled_services = 0;
if ($about_service1_enabled === '1') $enabled_services++;
if ($about_service2_enabled === '1') $enabled_services++;
if ($about_service3_enabled === '1') $enabled_services++;
if ($about_service4_enabled === '1') $enabled_services++;

$about_step1_title = get_setting($pdo, 'about_step1_title', 'Daftar Akun');
$about_step1_desc = get_setting($pdo, 'about_step1_desc', 'Buat akun untuk mengakses semua layanan');
$about_step2_title = get_setting($pdo, 'about_step2_title', 'Pilih Program');
$about_step2_desc = get_setting($pdo, 'about_step2_desc', 'Jelajahi course dan pelatihan yang tersedia');
$about_step3_title = get_setting($pdo, 'about_step3_title', 'Ikuti Pelatihan');
$about_step3_desc = get_setting($pdo, 'about_step3_desc', 'Belajar dan praktik bersama instruktur');
$about_step4_title = get_setting($pdo, 'about_step4_title', 'Dapatkan Sertifikat');
$about_step4_desc = get_setting($pdo, 'about_step4_desc', 'Raih pengakuan kompetensi');

$about_cta_text = get_setting($pdo, 'about_cta_text', 'Mulai Perjalanan Karirmu');
$about_cta_link = get_setting($pdo, 'about_cta_link', '/course');
?>
<div class="inner-hero" style="min-height:40dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Tentang Kami</span>
      <h1><?= e($about_hero_title) ?></h1>
      <p class="sub"><?= e($about_hero_subtitle) ?></p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2>Tentang Kami</h2>
    <p style="font-size:1.1rem;line-height:1.8"><?= e($about_profile) ?></p>
  </div>
</div>

<?php if ($enabled_services > 0): ?>
<div class="inner-sec sec-soft-cyan">
  <div class="section-wrap">
    <h2>Layanan Kami</h2>
    <div class="service-grid">
      <?php if ($about_service1_enabled === '1'): ?>
      <div class="service-card">
        <h3><?= e($about_service1_icon) ?></h3>
        <h4><?= e($about_service1_title) ?></h4>
        <p><?= e($about_service1_desc) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($about_service2_enabled === '1'): ?>
      <div class="service-card">
        <h3><?= e($about_service2_icon) ?></h3>
        <h4><?= e($about_service2_title) ?></h4>
        <p><?= e($about_service2_desc) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($about_service3_enabled === '1'): ?>
      <div class="service-card">
        <h3><?= e($about_service3_icon) ?></h3>
        <h4><?= e($about_service3_title) ?></h4>
        <p><?= e($about_service3_desc) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($about_service4_enabled === '1'): ?>
      <div class="service-card">
        <h3><?= e($about_service4_icon) ?></h3>
        <h4><?= e($about_service4_title) ?></h4>
        <p><?= e($about_service4_desc) ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2>Cara Menggunakan</h2>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">1</div>
        <h4><?= e($about_step1_title) ?></h4>
        <p><?= e($about_step1_desc) ?></p>
      </div>
      <div class="step-card">
        <div class="step-number">2</div>
        <h4><?= e($about_step2_title) ?></h4>
        <p><?= e($about_step2_desc) ?></p>
      </div>
      <div class="step-card">
        <div class="step-number">3</div>
        <h4><?= e($about_step3_title) ?></h4>
        <p><?= e($about_step3_desc) ?></p>
      </div>
      <div class="step-card">
        <div class="step-number">4</div>
        <h4><?= e($about_step4_title) ?></h4>
        <p><?= e($about_step4_desc) ?></p>
      </div>
    </div>
  </div>
</div>

<div class="inner-sec sec-soft-cream" style="text-align:center">
  <div class="section-wrap">
    <h2>Siap Memulai?</h2>
    <p style="margin-bottom:24px">Bergabunglah dengan ribuan mahasiswa yang telah mengembangkan skill mereka di UCC</p>
    <a href="<?= e($about_cta_link) ?>" class="cta-btn"><?= e($about_cta_text) ?> →</a>
  </div>
</div>

<style>
.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
  margin-top: 24px;
}
.step-card {
  text-align: center;
  padding: 24px;
  background: #f8f9fa;
  border-radius: 12px;
  transition: transform 0.2s;
}
.step-card:hover {
  transform: translateY(-4px);
}
.step-card .step-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #7a0f18, #c62828);
  color: #fff;
  border-radius: 50%;
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 16px;
}
.step-card h4 {
  margin: 0 0 8px 0;
  font-size: 1.1rem;
  color: #1a1a2e;
}
.step-card p {
  margin: 0;
  color: #666;
  font-size: 0.95rem;
}
</style>