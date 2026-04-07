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
    $title = sanitize($_POST['title'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = sanitize($_POST['status'] ?? 'draft');

    if (empty($title)) {
      $message = ['text' => 'Judul berita wajib diisi.', 'type' => 'error'];
    } else {
      // Generate slug
      $slug = slugify($title);
      $originalSlug = $slug;
      $counter = 1;
      while (true) {
        $stmt = $pdo->prepare("SELECT id FROM news WHERE slug = ?");
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) break;
        $slug = $originalSlug . '-' . $counter++;
      }

      // Handle image upload
      $image = '';
      if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/news/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
          $image = 'news-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
          move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }
      }

      $stmt = $pdo->prepare("INSERT INTO news (title, slug, excerpt, content, image, status) VALUES (?, ?, ?, ?, ?, ?)");
      if ($stmt->execute([$title, $slug, $excerpt, $content, $image, $status])) {
        header('Location: /admin/news');
        exit;
      } else {
        $message = ['text' => 'Gagal menyimpan berita.', 'type' => 'error'];
      }
    }
  }
}

$pageTitle = 'Tambah Berita';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
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
    .news-form { max-width: 900px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    .form-group input[type="text"],
    .form-group textarea,
    .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; font-family: inherit; box-sizing: border-box; }
    .form-group textarea { min-height: 200px; resize: vertical; }
    .form-group .help-text { font-size: 0.85rem; color: #666; margin-top: 6px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .btn-save { background: #1a1a2e; color: #fff; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; }
    .btn-save:hover { background: #16213e; }
    .btn-secondary { background: #fff; color: #333; padding: 12px 24px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: 500; text-decoration: none; display: inline-block; margin-right: 10px; }
    .btn-secondary:hover { background: #f9fafb; }
    .image-preview { margin-top: 10px; }
    .image-preview img { max-width: 200px; border-radius: 6px; }
    #editor-container { min-height: 300px; border: 1px solid #ddd; border-radius: 6px; }
    .ql-container { font-family: inherit; font-size: 0.95rem; }
    .ql-editor { min-height: 250px; }
    @media (max-width: 768px) {
      .admin-sidebar { display: none; }
      .admin-main { padding: 20px; }
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
      <a href="/admin/news" class="active">Berita</a>
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
        <h1><?= e($pageTitle) ?></h1>
      </div>

      <div class="breadcrumb">
        <a href="/admin/news">Berita</a>
        <span class="breadcrumb-separator">/</span>
        <span>Tambah Berita</span>
      </div>

      <?php if ($message): ?>
        <div class="alert alert-<?= e($message['type']) ?>">
          <?= e($message['text']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="news-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="form-group">
          <label for="title">Judul Berita *</label>
          <input type="text" id="title" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="excerpt">Ringkasan</label>
          <textarea id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan singkat berita..."><?= e($_POST['excerpt'] ?? '') ?></textarea>
          <p class="help-text">Akan ditampilkan di daftar berita.</p>
        </div>

        <div class="form-group">
          <label for="editor-container">Konten</label>
          <input type="hidden" name="content" id="content-input">
          <div id="editor-container"><?= $_POST['content'] ?? '' ?></div>
          <p class="help-text">Gunakan toolbar untuk format teks (tebal, miring, dll).</p>
        </div>

        <div class="form-group">
          <label for="image">Gambar</label>
          <input type="file" id="image" name="image" accept="image/*">
          <p class="help-text">Format: JPG, PNG, GIF, WebP. Maksimal 2MB.</p>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
              <option value="draft">Draft</option>
              <option value="published">Dipublikasi</option>
            </select>
          </div>
        </div>

        <div style="margin-top: 30px;">
          <a href="/admin/news" class="btn-secondary">Batal</a>
          <button type="submit" class="btn-save">Simpan</button>
        </div>
      </form>
    </main>
  </div>
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <script>
    var quill = new Quill('#editor-container', {
      theme: 'snow',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline', 'strike'],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          ['link'],
          ['clean']
        ]
      }
    });
    document.querySelector('form').addEventListener('submit', function() {
      document.getElementById('content-input').value = quill.root.innerHTML;
    });
  </script>
</body>
</html>