<?php
require_once __DIR__ . '/../../config.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];
$error = '';
$success = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token CSRF tidak valid';
  } else {
    $filename = basename($_POST['filename'] ?? '');
    $file_path = __DIR__ . '/../../uploads/courses/' . $filename;

    if ($filename === '' || !file_exists($file_path)) {
      $error = 'File tidak ditemukan';
    } else {
      // Check if image is still used by a course
      $public_path = '/uploads/courses/' . $filename;
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE thumbnail = ?");
      $stmt->execute([$public_path]);
      $in_use = (int) $stmt->fetchColumn();

      if ($in_use > 0) {
        $error = 'Gambar tidak dapat dihapus karena masih digunakan oleh ' . $in_use . ' course';
      } else {
        if (unlink($file_path)) {
          $success = 'Gambar berhasil dihapus';
        } else {
          $error = 'Gagal menghapus file';
        }
      }
    }
  }
}

// Scan uploads/courses directory for AI-generated images
$upload_dir = __DIR__ . '/../../uploads/courses';
$images = [];

if (is_dir($upload_dir)) {
  $files = scandir($upload_dir, SCANDIR_SORT_DESCENDING);
  foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) continue;

    $filepath = $upload_dir . '/' . $file;
    $public_path = '/uploads/courses/' . $file;

    // Check if used by a course
    $stmt = $pdo->prepare("SELECT title FROM courses WHERE thumbnail = ? LIMIT 1");
    $stmt->execute([$public_path]);
    $course = $stmt->fetch();

    $images[] = [
      'filename' => $file,
      'public_path' => $public_path,
      'size' => filesize($filepath),
      'modified' => filemtime($filepath),
      'course_title' => $course ? $course['title'] : null,
    ];
  }
}

$pageTitle = 'Media AI';
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
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .admin-header h1 { margin: 0; font-size: 1.5rem; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .btn-primary:hover { background: #5c0a11; }
    .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-danger:hover { background: #dc2626; }
    .btn-secondary { background: #6b7280; color: #fff; }
    .btn-secondary:hover { background: #4b5563; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .media-stats { display: flex; gap: 16px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 10px; padding: 16px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
    .stat-card .num { font-size: 1.8rem; font-weight: 700; color: #7a0f18; }
    .stat-card .label { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
    .media-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
    .media-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
    .media-card img { width: 100%; height: 180px; object-fit: cover; display: block; cursor: pointer; }
    .media-card-body { padding: 12px; }
    .media-card-name { font-size: 0.75rem; color: #374151; font-weight: 500; word-break: break-all; margin-bottom: 6px; }
    .media-card-meta { font-size: 0.72rem; color: #9ca3af; margin-bottom: 8px; }
    .media-card-used { font-size: 0.72rem; color: #059669; background: #d1fae5; padding: 2px 8px; border-radius: 20px; display: inline-block; margin-bottom: 8px; }
    .media-card-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .empty-state { text-align: center; padding: 80px 20px; color: #6b7280; }
    .empty-state h3 { margin-bottom: 8px; color: #374151; font-size: 1.2rem; }

    /* Lightbox */
    .lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; }
    .lightbox.open { display: flex; }
    .lightbox img { max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; }
    .lightbox-close { position: absolute; top: 20px; right: 24px; color: #fff; font-size: 2rem; cursor: pointer; line-height: 1; background: none; border: none; }
    .lightbox-caption { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 0.85rem; background: rgba(0,0,0,0.5); padding: 6px 14px; border-radius: 20px; }
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
      <a href="/admin/media" class="active">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <h1>Media AI</h1>
        <a href="/admin/courses/add" class="btn btn-primary">+ Generate Gambar Baru</a>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <?php
        $total_size = array_sum(array_column($images, 'size'));
        $used_count = count(array_filter($images, fn($img) => $img['course_title'] !== null));
      ?>
      <div class="media-stats">
        <div class="stat-card">
          <div class="num"><?= count($images) ?></div>
          <div class="label">Total Gambar</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= $used_count ?></div>
          <div class="label">Digunakan</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= count($images) - $used_count ?></div>
          <div class="label">Tidak Digunakan</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= $total_size > 1048576 ? round($total_size / 1048576, 1) . ' MB' : round($total_size / 1024) . ' KB' ?></div>
          <div class="label">Total Ukuran</div>
        </div>
      </div>

      <?php if (empty($images)): ?>
        <div class="empty-state">
          <h3>Belum ada gambar AI</h3>
          <p>Gambar yang dihasilkan AI saat membuat kursus akan tampil di sini.</p>
          <a href="/admin/courses/add" class="btn btn-primary" style="margin-top: 16px;">+ Buat Course & Generate Gambar</a>
        </div>
      <?php else: ?>
        <div class="media-grid">
          <?php foreach ($images as $img): ?>
            <div class="media-card">
              <img src="<?= e($img['public_path']) ?>" alt="<?= e($img['filename']) ?>"
                   onclick="openLightbox('<?= e($img['public_path']) ?>', '<?= e($img['filename']) ?>')"
                   title="Klik untuk memperbesar">
              <div class="media-card-body">
                <div class="media-card-name"><?= e($img['filename']) ?></div>
                <div class="media-card-meta">
                  <?= date('d M Y H:i', $img['modified']) ?> &bull;
                  <?= $img['size'] > 1048576 ? round($img['size'] / 1048576, 1) . ' MB' : round($img['size'] / 1024) . ' KB' ?>
                </div>
                <?php if ($img['course_title']): ?>
                  <div class="media-card-used" title="Digunakan oleh course ini">
                    &#10003; <?= e($img['course_title']) ?>
                  </div>
                <?php endif; ?>
                <div class="media-card-actions">
                  <button class="btn btn-sm btn-secondary"
                          onclick="copyUrl('<?= e($img['public_path']) ?>')"
                          title="Salin URL">Salin URL</button>
                  <?php if (!$img['course_title']): ?>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Hapus gambar ini? Tindakan tidak dapat dibatalkan.')">
                      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="filename" value="<?= e($img['filename']) ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <img id="lightbox-img" src="" alt="">
    <div class="lightbox-caption" id="lightbox-caption"></div>
  </div>

  <script>
    function openLightbox(src, caption) {
      document.getElementById('lightbox-img').src = src;
      document.getElementById('lightbox-caption').textContent = caption;
      document.getElementById('lightbox').classList.add('open');
    }
    function closeLightbox(e) {
      if (!e || e.target === document.getElementById('lightbox') || e.target.classList.contains('lightbox-close')) {
        document.getElementById('lightbox').classList.remove('open');
      }
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    function copyUrl(url) {
      const full = window.location.origin + url;
      navigator.clipboard.writeText(full).then(() => {
        alert('URL disalin: ' + full);
      });
    }
  </script>
</body>
</html>
