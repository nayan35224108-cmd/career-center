<?php
require_once __DIR__ . '/../../config.php';


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
    $section = sanitize($_POST['section'] ?? '');
    $errors = [];

    foreach ($_POST as $key => $value) {
      if ($key === 'csrf' || $key === 'section') continue;
      if (!save_footer_setting($pdo, $section, $key, $value)) {
        $errors[] = $key;
      }
    }

    if (empty($errors)) {
      $message = ['text' => 'Pengaturan footer berhasil disimpan.', 'type' => 'success'];
    } else {
      $message = ['text' => 'Gagal menyimpan: ' . implode(', ', $errors) . '. Pastikan tabel footer_settings sudah dibuat.', 'type' => 'error'];
    }
  }
}

$column1 = get_footer_settings($pdo, 'column1');
$column2 = get_footer_settings($pdo, 'column2');
$column3 = get_footer_settings($pdo, 'column3');
$copyright = get_footer_settings($pdo, 'copyright');

$pageTitle = 'Pengaturan Footer';
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
    .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .settings-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
    .settings-tab { padding: 12px 20px; background: none; border: none; cursor: pointer; font-size: 0.95rem; color: #666; border-bottom: 2px solid transparent; margin-bottom: -2px; }
    .settings-tab.active { color: #1a1a2e; border-bottom-color: #1a1a2e; font-weight: 600; }
    .settings-tab:hover { color: #1a1a2e; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    .form-group input[type="text"],
    .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; font-family: inherit; }
    .form-group textarea { min-height: 100px; resize: vertical; }
    .form-group .help-text { font-size: 0.85rem; color: #666; margin-top: 5px; }
    .form-group .help-text code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-size: 0.8rem; }
    .btn-save { background: #1a1a2e; color: #fff; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; }
    .btn-save:hover { background: #16213e; }
    .settings-section { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .settings-section h3 { margin-top: 0; margin-bottom: 20px; color: #1a1a2e; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; color: #666; font-size: 0.9rem; }
    .breadcrumb a { color: #7a0f18; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb-separator { color: #999; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #666; text-decoration: none; font-size: 0.9rem; margin-bottom: 16px; }
    .back-link:hover { color: #7a0f18; }
    @media (max-width: 768px) {
      .admin-sidebar { width: 100%; position: fixed; bottom: 0; left: 0; z-index: 100; padding: 10px; display: flex; overflow-x: auto; }
      .admin-sidebar h2 { display: none; }
      .admin-sidebar a { white-space: nowrap; margin-bottom: 0; margin-right: 5px; padding: 8px 12px; font-size: 0.85rem; }
      .admin-main { padding: 20px 20px 80px 20px; }
      .form-row { grid-template-columns: 1fr; }
      .settings-tabs { overflow-x: auto; }
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
        <span>Pengaturan Footer</span>
      </div>

      <a href="/admin/settings" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Pengaturan
      </a>

      <?php if ($message): ?>
        <div class="alert alert-<?= e($message['type']) ?>">
          <?= e($message['text']) ?>
        </div>
      <?php endif; ?>

      <div class="settings-tabs">
        <button class="settings-tab active" onclick="showTab('column1')">Kolom 1 - Info & Kontak</button>
        <button class="settings-tab" onclick="showTab('column2')">Kolom 2 - Navigasi</button>
        <button class="settings-tab" onclick="showTab('column3')">Kolom 3 - Sosial Media</button>
        <button class="settings-tab" onclick="showTab('copyright')">Copyright</button>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div id="tab-column1" class="tab-content active">
          <input type="hidden" name="section" value="column1">
          <div class="settings-section">
            <h3>Logo & Deskripsi</h3>
            <div class="form-group">
              <label for="logo_text">Teks Logo</label>
              <input type="text" id="logo_text" name="logo_text" value="<?= e($column1['logo_text'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label for="description">Deskripsi</label>
              <textarea id="description" name="description" rows="3"><?= e($column1['description'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="settings-section">
            <h3>Informasi Kontak</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="location_title">Judul Lokasi</label>
                <input type="text" id="location_title" name="location_title" value="<?= e($column1['location_title'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="contact_title">Judul Kontak</label>
                <input type="text" id="contact_title" name="contact_title" value="<?= e($column1['contact_title'] ?? '') ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="location_content">Konten Lokasi (HTML diperbolehkan)</label>
                <textarea id="location_content" name="location_content" rows="4"><?= e($column1['location_content'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label for="contact_content">Konten Kontak (HTML diperbolehkan)</label>
                <textarea id="contact_content" name="contact_content" rows="4"><?= e($column1['contact_content'] ?? '') ?></textarea>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="email_title">Judul Email</label>
                <input type="text" id="email_title" name="email_title" value="<?= e($column1['email_title'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="email_content">Konten Email (HTML diperbolehkan)</label>
                <textarea id="email_content" name="email_content" rows="3"><?= e($column1['email_content'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
          <button type="submit" class="btn-save">Simpan Perubahan</button>
        </div>
      </form>

      <form method="POST" action="">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div id="tab-column2" class="tab-content">
          <input type="hidden" name="section" value="column2">
          <div class="settings-section">
            <h3>Navigasi Internal</h3>
            <div class="form-group">
              <label for="nav_title">Judul Navigasi</label>
              <input type="text" id="nav_title" name="nav_title" value="<?= e($column2['nav_title'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="nav_links">Link Navigasi (Format JSON)</label>
              <textarea id="nav_links" name="nav_links" rows="6"><?= e($column2['nav_links'] ?? '') ?></textarea>
              <p class="help-text">Format: <code>[{"text":"Nama Link","href":"/url"},{"text":"Nama Link 2","href":"/url2"}]</code></p>
            </div>
          </div>

          <div class="settings-section">
            <h3>Link Eksternal</h3>
            <div class="form-group">
              <label for="links_title">Judul Link Terkait</label>
              <input type="text" id="links_title" name="links_title" value="<?= e($column2['links_title'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="external_links">Link Eksternal (Format JSON)</label>
              <textarea id="external_links" name="external_links" rows="6"><?= e($column2['external_links'] ?? '') ?></textarea>
              <p class="help-text">Format: <code>[{"text":"Nama Link","href":"https://example.com"},{"text":"Nama Link 2","href":"https://example2.com"}]</code></p>
            </div>
          </div>
          <button type="submit" class="btn-save">Simpan Perubahan</button>
        </div>
      </form>

      <form method="POST" action="">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div id="tab-column3" class="tab-content">
          <input type="hidden" name="section" value="column3">
          <div class="settings-section">
            <h3>Social Media</h3>
            <div class="form-group">
              <label for="social_title">Judul Sosial Media</label>
              <input type="text" id="social_title" name="social_title" value="<?= e($column3['social_title'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="social_description">Deskripsi</label>
              <input type="text" id="social_description" name="social_description" value="<?= e($column3['social_description'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label for="social_links">Link Sosial Media (Format JSON)</label>
              <textarea id="social_links" name="social_links" rows="10"><?= e($column3['social_links'] ?? '') ?></textarea>
              <p class="help-text">Format: <code>[{"platform":"instagram","url":"https://instagram.com/user","title":"Instagram"},{"platform":"facebook","url":"https://facebook.com/page","title":"Facebook"}]</code></p>
              <p class="help-text">Platform yang tersedia: <code>instagram</code>, <code>facebook</code>, <code>twitter</code>/<code>x</code>, <code>youtube</code>, <code>tiktok</code>, <code>linkedin</code>, <code>whatsapp</code></p>
            </div>
          </div>

          <div class="settings-section">
            <h3>WhatsApp</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="whatsapp_title">Judul WhatsApp</label>
                <input type="text" id="whatsapp_title" name="whatsapp_title" value="<?= e($column3['whatsapp_title'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="whatsapp_text">Teks Tombol</label>
                <input type="text" id="whatsapp_text" name="whatsapp_text" value="<?= e($column3['whatsapp_text'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label for="whatsapp_number">Nomor WhatsApp (format: 628xxxxxxxxxx)</label>
              <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= e($column3['whatsapp_number'] ?? '') ?>">
              <p class="help-text">Contoh: <code>6282133332256</code> (tanpa tanda + atau spasi)</p>
            </div>
          </div>
          <button type="submit" class="btn-save">Simpan Perubahan</button>
        </div>
      </form>

      <form method="POST" action="">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div id="tab-copyright" class="tab-content">
          <input type="hidden" name="section" value="copyright">
          <div class="settings-section">
            <h3>Copyright</h3>
            <div class="form-group">
              <label for="copyright_text">Teks Copyright</label>
              <input type="text" id="copyright_text" name="copyright_text" value="<?= e($copyright['copyright_text'] ?? '') ?>">
              <p class="help-text">Teks yang akan muncul setelah simbol copyright © dan tahun.</p>
            </div>
            <div class="form-group">
              <label for="credit_text">Teks Kredit</label>
              <input type="text" id="credit_text" name="credit_text" value="<?= e($copyright['credit_text'] ?? '') ?>">
              <p class="help-text">Teks kredit di bagian bawah footer.</p>
            </div>
          </div>
          <button type="submit" class="btn-save">Simpan Perubahan</button>
        </div>
      </form>
    </main>
  </div>

  <script>
    function showTab(tabName) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active'));
      document.getElementById('tab-' + tabName).classList.add('active');
      event.target.classList.add('active');
    }
  </script>
</body>
</html>
