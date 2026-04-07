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
      'contact_hero_title',
      'contact_hero_subtitle',
      'contact_email',
      'contact_whatsapp',
      'contact_location',
      'contact_operational_hours'
    ];

    $errors = [];
    foreach ($fields as $field) {
      $value = sanitize($_POST[$field] ?? '');
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

$contact_hero_title = get_setting($pdo, 'contact_hero_title', 'Kami Siap Membantu');
$contact_hero_subtitle = get_setting($pdo, 'contact_hero_subtitle', 'Ada pertanyaan? Silakan hubungi kami melalui formulir di bawah ini.');
$contact_email = get_setting($pdo, 'contact_email', 'admin@ucc.pjdigital.top');
$contact_whatsapp = get_setting($pdo, 'contact_whatsapp', '+62 812 3456 7890');
$contact_location = get_setting($pdo, 'contact_location', 'Gedung Kampus Unisbank<br>Ruang UCC');
$contact_operational_hours = get_setting($pdo, 'contact_operational_hours', "Senin - Jumat: 08.00 - 17.00\nSabtu - Minggu: Tutup");

$pageTitle = 'Pengaturan Kontak';
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
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #666; text-decoration: none; font-size: 0.9rem; margin-bottom: 16px; }
    .back-link:hover { color: #7a0f18; }
    .settings-form { max-width: 800px; }
    .settings-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 24px; }
    .settings-section h3 { margin: 0 0 20px 0; color: #1a1a2e; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
    .form-group textarea { min-height: 100px; resize: vertical; }
    .form-group .help-text { font-size: 0.85rem; color: #666; margin-top: 6px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .btn-save { background: #1a1a2e; color: #fff; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; }
    .btn-save:hover { background: #16213e; }
    .btn-secondary { background: #fff; color: #333; padding: 12px 24px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; text-decoration: none; display: inline-block; margin-right: 10px; }
    .btn-secondary:hover { background: #f9fafb; }
    @media (max-width: 768px) {
      .admin-sidebar { width: 100%; position: fixed; bottom: 0; left: 0; z-index: 100; padding: 10px; display: flex; overflow-x: auto; }
      .admin-sidebar h2 { display: none; }
      .admin-sidebar a { white-space: nowrap; margin-bottom: 0; margin-right: 5px; padding: 8px 12px; font-size: 0.85rem; }
      .admin-main { padding: 20px 20px 80px 20px; }
      .form-row { grid-template-columns: 1fr; }
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
        <span>Pengaturan Kontak</span>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-<?= e($message['type']) ?>">
          <?= e($message['text']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="settings-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="settings-section">
          <h3>Konten Hero Section</h3>
          <div class="form-group">
            <label for="contact_hero_title">Judul Hero</label>
            <input type="text" id="contact_hero_title" name="contact_hero_title" value="<?= e($contact_hero_title) ?>" required>
            <p class="help-text">Judul utama yang ditampilkan di halaman kontak.</p>
          </div>
          <div class="form-group">
            <label for="contact_hero_subtitle">Subjudul Hero</label>
            <textarea id="contact_hero_subtitle" name="contact_hero_subtitle" rows="2"><?= e($contact_hero_subtitle) ?></textarea>
            <p class="help-text">Deskripsi pendukung di bawah judul hero.</p>
          </div>
        </div>

        <div class="settings-section">
          <h3>Informasi Kontak</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="contact_email">Email</label>
              <input type="email" id="contact_email" name="contact_email" value="<?= e($contact_email) ?>">
              <p class="help-text">Alamat email kontak.</p>
            </div>
            <div class="form-group">
              <label for="contact_whatsapp">WhatsApp</label>
              <input type="text" id="contact_whatsapp" name="contact_whatsapp" value="<?= e($contact_whatsapp) ?>">
              <p class="help-text">Nomor WhatsApp (contoh: +62 812 3456 7890).</p>
            </div>
          </div>
          <div class="form-group">
            <label for="contact_location">Lokasi</label>
            <textarea id="contact_location" name="contact_location" rows="2"><?= e($contact_location) ?></textarea>
            <p class="help-text">Alamat lokasi. Gunakan &lt;br&gt; untuk baris baru.</p>
          </div>
        </div>

        <div class="settings-section">
          <h3>Jam Operasional</h3>
          <div class="form-group">
            <label for="contact_operational_hours">Jam Operasional</label>
            <textarea id="contact_operational_hours" name="contact_operational_hours" rows="4"><?= e($contact_operational_hours) ?></textarea>
            <p class="help-text">Jam operasional. Gunakan baris baru untuk setiap baris.</p>
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