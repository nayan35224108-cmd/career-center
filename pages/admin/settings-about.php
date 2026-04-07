<?php
require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../../includes/settings.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $message = ['text' => 'Token keamanan tidak valid.', 'type' => 'error'];
  } else {
    $fields = [
      'about_hero_title',
      'about_hero_subtitle',
      'about_profile',
      'about_service1_enabled',
      'about_service1_icon',
      'about_service1_title',
      'about_service1_desc',
      'about_service2_enabled',
      'about_service2_icon',
      'about_service2_title',
      'about_service2_desc',
      'about_service3_enabled',
      'about_service3_icon',
      'about_service3_title',
      'about_service3_desc',
      'about_service4_enabled',
      'about_service4_icon',
      'about_service4_title',
      'about_service4_desc',
      'about_step1_title',
      'about_step1_desc',
      'about_step2_title',
      'about_step2_desc',
      'about_step3_title',
      'about_step3_desc',
      'about_step4_title',
      'about_step4_desc',
      'about_cta_text',
      'about_cta_link'
    ];

    $errors = [];
    foreach ($fields as $field) {
      if (strpos($field, '_enabled') !== false) {
        $value = isset($_POST[$field]) ? '1' : '0';
      } else {
        $value = sanitize($_POST[$field] ?? '');
      }
      if (!set_setting($pdo, $field, $value)) {
        $errors[] = $field;
      }
    }

    if (empty($errors)) {
      $message = ['text' => 'Pengaturan berhasil disimpan.', 'type' => 'success'];
    } else {
      $message = ['text' => 'Gagal menyimpan beberapa pengaturan.', 'type' => 'error'];
    }
  }
}

// Load settings with defaults
$about_hero_title = get_setting($pdo, 'about_hero_title', 'Unisbank Career Centre');
$about_hero_subtitle = get_setting($pdo, 'about_hero_subtitle', 'Portal pelatihan karir & soft skill untuk mahasiswa Unisbank');
$about_profile = get_setting($pdo, 'about_profile', 'Unisbank Career Centre (UCC) adalah pusat pengembangan karir untuk mahasiswa Unisbank. Kami menyediakan pelatihan, konsultasi, dan sumber daya untuk membantu mahasiswa mempersiapkan diri menghadapi dunia kerja.');

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

$pageTitle = 'Pengaturan Halaman About';
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
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; color: #666; font-size: 0.9rem; }
    .breadcrumb a { color: #7a0f18; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb-separator { color: #999; }
    .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .settings-form { max-width: 900px; }
    .settings-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 24px; }
    .settings-section h3 { margin: 0 0 20px 0; color: #1a1a2e; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    .form-group input[type="text"],
    .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
    .form-group textarea { min-height: 80px; resize: vertical; }
    .form-group .help-text { font-size: 0.85rem; color: #666; margin-top: 6px; }
    .form-row { display: grid; grid-template-columns: 80px 1fr 1fr; gap: 15px; }
    .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-row input[type="text"] { padding: 10px; }
    .icon-input { text-align: center; font-size: 1.5rem; }
    .btn-save { background: #1a1a2e; color: #fff; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; }
    .btn-save:hover { background: #16213e; }
    .btn-secondary { background: #fff; color: #333; padding: 12px 24px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; text-decoration: none; display: inline-block; margin-right: 10px; }
    .btn-secondary:hover { background: #f9fafb; }
    .step-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #1a1a2e; color: #fff; border-radius: 50%; font-size: 0.85rem; font-weight: 600; margin-bottom: 10px; }
    .service-row { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
    .service-row input[type="text"] { flex: 1; }
    .toggle-switch { position: relative; width: 50px; height: 26px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 26px; }
    .toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
    .toggle-switch input:checked + .toggle-slider { background-color: #10b981; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }
    .service-label { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .service-label label { margin: 0; font-weight: 500; color: #333; }
    @media (max-width: 768px) {
      .admin-sidebar { width: 100%; position: fixed; bottom: 0; left: 0; z-index: 100; padding: 10px; display: flex; overflow-x: auto; }
      .admin-sidebar h2 { display: none; }
      .admin-sidebar a { white-space: nowrap; margin-bottom: 0; margin-right: 5px; padding: 8px 12px; font-size: 0.85rem; }
      .admin-main { padding: 20px 20px 80px 20px; }
      .form-row { grid-template-columns: 60px 1fr; }
      .form-row > div:last-child { grid-column: span 2; }
      .form-row-2 { grid-template-columns: 1fr; }
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
      <a href="/admin/news">Berita</a>
      <a href="/admin/master-categories">Master Kategori</a>
      <a href="/admin/master-pengajar">Master Pengajar</a>
      <a href="/admin/registrations">Pendaftaran</a>
      <a href="/admin/users">Users</a>
      <a href="/admin/settings" class="active">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1><?= e($pageTitle) ?></h1>
        <div>
          <span>Halo, <strong><?= e($admin['name']) ?></strong></span>
        </div>
      </div>

      <div class="breadcrumb">
        <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
        <span class="breadcrumb-separator">/</span>
        <span>Pengaturan Halaman About</span>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-<?= e($message['type']) ?>">
          <?= e($message['text']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="settings-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <!-- Hero Section -->
        <div class="settings-section">
          <h3>Hero Section</h3>
          <div class="form-group">
            <label for="about_hero_title">Judul Hero</label>
            <input type="text" id="about_hero_title" name="about_hero_title" value="<?= e($about_hero_title) ?>" required>
          </div>
          <div class="form-group">
            <label for="about_hero_subtitle">Subjudul Hero</label>
            <input type="text" id="about_hero_subtitle" name="about_hero_subtitle" value="<?= e($about_hero_subtitle) ?>">
          </div>
        </div>

        <!-- Tentang Kami -->
        <div class="settings-section">
          <h3>Tentang Kami</h3>
          <div class="form-group">
            <label for="about_profile">Profil Singkat</label>
            <textarea id="about_profile" name="about_profile" rows="4"><?= e($about_profile) ?></textarea>
            <p class="help-text">Paragraf singkat tentang siapa UCC dan apa yang kami lakukan.</p>
          </div>
        </div>

        <!-- Layanan -->
        <div class="settings-section">
          <h3>Layanan Kami (4 Kartu)</h3>

          <div class="form-group">
            <div class="service-label">
              <label>Layanan 1</label>
              <label class="toggle-switch">
                <input type="checkbox" name="about_service1_enabled" value="1" <?= $about_service1_enabled === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="form-row">
              <input type="text" name="about_service1_icon" value="<?= e($about_service1_icon) ?>" class="icon-input" placeholder="🎓">
              <input type="text" name="about_service1_title" value="<?= e($about_service1_title) ?>" placeholder="Judul">
              <input type="text" name="about_service1_desc" value="<?= e($about_service1_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <div class="service-label">
              <label>Layanan 2</label>
              <label class="toggle-switch">
                <input type="checkbox" name="about_service2_enabled" value="1" <?= $about_service2_enabled === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="form-row">
              <input type="text" name="about_service2_icon" value="<?= e($about_service2_icon) ?>" class="icon-input" placeholder="💡">
              <input type="text" name="about_service2_title" value="<?= e($about_service2_title) ?>" placeholder="Judul">
              <input type="text" name="about_service2_desc" value="<?= e($about_service2_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <div class="service-label">
              <label>Layanan 3</label>
              <label class="toggle-switch">
                <input type="checkbox" name="about_service3_enabled" value="1" <?= $about_service3_enabled === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="form-row">
              <input type="text" name="about_service3_icon" value="<?= e($about_service3_icon) ?>" class="icon-input" placeholder="📋">
              <input type="text" name="about_service3_title" value="<?= e($about_service3_title) ?>" placeholder="Judul">
              <input type="text" name="about_service3_desc" value="<?= e($about_service3_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <div class="service-label">
              <label>Layanan 4</label>
              <label class="toggle-switch">
                <input type="checkbox" name="about_service4_enabled" value="1" <?= $about_service4_enabled === '1' ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="form-row">
              <input type="text" name="about_service4_icon" value="<?= e($about_service4_icon) ?>" class="icon-input" placeholder="📊">
              <input type="text" name="about_service4_title" value="<?= e($about_service4_title) ?>" placeholder="Judul">
              <input type="text" name="about_service4_desc" value="<?= e($about_service4_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>
        </div>

        <!-- Cara Menggunakan -->
        <div class="settings-section">
          <h3>Cara Menggunakan (4 Langkah)</h3>

          <div class="form-group">
            <label><span class="step-number">1</span> Langkah Pertama</label>
            <div class="form-row-2">
              <input type="text" name="about_step1_title" value="<?= e($about_step1_title) ?>" placeholder="Judul Langkah">
              <input type="text" name="about_step1_desc" value="<?= e($about_step1_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <label><span class="step-number">2</span> Langkah Kedua</label>
            <div class="form-row-2">
              <input type="text" name="about_step2_title" value="<?= e($about_step2_title) ?>" placeholder="Judul Langkah">
              <input type="text" name="about_step2_desc" value="<?= e($about_step2_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <label><span class="step-number">3</span> Langkah Ketiga</label>
            <div class="form-row-2">
              <input type="text" name="about_step3_title" value="<?= e($about_step3_title) ?>" placeholder="Judul Langkah">
              <input type="text" name="about_step3_desc" value="<?= e($about_step3_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>

          <div class="form-group">
            <label><span class="step-number">4</span> Langkah Keempat</label>
            <div class="form-row-2">
              <input type="text" name="about_step4_title" value="<?= e($about_step4_title) ?>" placeholder="Judul Langkah">
              <input type="text" name="about_step4_desc" value="<?= e($about_step4_desc) ?>" placeholder="Deskripsi singkat">
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div class="settings-section">
          <h3>Call-to-Action</h3>
          <div class="form-row-2">
            <div class="form-group">
              <label for="about_cta_text">Teks Tombol</label>
              <input type="text" id="about_cta_text" name="about_cta_text" value="<?= e($about_cta_text) ?>">
            </div>
            <div class="form-group">
              <label for="about_cta_link">Link Tombol</label>
              <input type="text" id="about_cta_link" name="about_cta_link" value="<?= e($about_cta_link) ?>">
              <p class="help-text">Contoh: /course atau /register</p>
            </div>
          </div>
        </div>

        <div>
          <a href="/admin/settings" class="btn-secondary">Kembali</a>
          <button type="submit" class="btn-save">Simpan Perubahan</button>
        </div>
      </form>
    </main>
  </div>
</body>
</html>