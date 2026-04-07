<?php
$id = (int) ($segments[1] ?? 0);
$course = get_course_by_id($id);

if (!$course) {
  include __DIR__ . '/../templates/404.php';
  exit;
}

$is_logged_in = is_user_logged_in();
$user = $is_logged_in ? get_user() : null;
$registered = false;
$registration = null;

if ($user) {
  $stmt = $pdo->prepare("SELECT * FROM course_registrations WHERE user_id = ? AND course_id = ?");
  $stmt->execute([$user['id'], $id]);
  $registration = $stmt->fetch();
  $registered = (bool) $registration;
}

// Handle registration
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $message = 'Token tidak valid';
    $message_type = 'error';
  } elseif ($registered) {
    // Cancel registration
    cancel_registration($user['id'], $id);
    $message = 'Pendaftaran dibatalkan';
    $message_type = 'success';
    $registered = false;
    $registration = null;
  } else {
    // Register
    $result = register_course($user['id'], $id);
    if ($result['success']) {
      $message = 'Pendaftaran berhasil!';
      $message_type = 'success';
      $registered = true;
      
      // Send registration confirmation email
      $email_subject = 'Konfirmasi Pendaftaran Course - ' . e($course['title']);
      $email_content = '
        <h3>Pendaftaran Berhasil!</h3>
        <p>Halo, ' . e($user['name']) . '!</p>
        <p>Anda telah berhasil mendaftar di course:</p>
        <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;">
          <h4 style="margin: 0 0 10px 0;">' . e($course['title']) . '</h4>
          <p style="margin: 5px 0;"><strong>Kategori:</strong> ' . e($course['category']) . '</p>
          <p style="margin: 5px 0;"><strong>Jadwal:</strong> ' . ($course['jadwal'] ? date('d M Y, H:i', strtotime($course['jadwal'])) : 'Akan diinformasikan') . '</p>
          <p style="margin: 5px 0;"><strong>Lokasi:</strong> ' . e($course['lokasi'] ?: 'Online') . '</p>
          <p style="margin: 5px 0;"><strong>Pengajar:</strong> ' . e($course['pengajar']) . '</p>
        </div>
        <p><strong>Status Pendaftaran:</strong> <span style="color: #f59e0b;">Pending</span></p>
        <p>Tim admin akan segera memverifikasi pendaftaran Anda. Anda akan menerima email notifikasi saat pendaftaran telah dikonfirmasi.</p>
        <p style="margin-top: 20px;">
          <a href="' . SITE_URL . '/course/' . $id . '" style="background: #7a0f18; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Lihat Course</a>
        </p>
        <p style="margin-top: 20px; color: #666;">Terima kasih telah mendaftar di Unisbank Career Centre!</p>
      ';
      send_email_template($user['email'], $email_subject, $email_content);
    } else {
      $message = $result['error'];
      $message_type = 'error';
    }
  }
}

// Count registrations
$stmt = $pdo->prepare("SELECT COUNT(*) FROM course_registrations WHERE course_id = ? AND status = 'confirmed'");
$stmt->execute([$id]);
$enrolled = $stmt->fetchColumn();
$spots_left = $course['kapasitas'] - $enrolled;

$pageTitle = $course['title'];
$metaDescription = seo_description($course['description'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Unisbank Career Centre</title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .site-header .nav a {
      color: #fff;
    }

    .site-header .nav .login-btn {
      background: #7a0f18;
      color: #fff !important;
      border: 1px solid rgba(255, 255, 255, 0.28);
      padding: 8px 20px;
      border-radius: 20px;
      display: inline-block;
      text-decoration: none;
    }

    .site-header .nav .login-btn:visited,
    .site-header .nav .login-btn:active,
    .site-header .nav .login-btn:focus {
      color: #fff !important;
    }

    .site-header .nav .login-btn:hover,
    .site-header .nav .login-btn:visited:hover,
    .site-header .nav .login-btn:focus:hover,
    .site-header .nav .login-btn:active:hover {
      background: #fff;
      color: #7a0f18 !important;
      border-color: #7a0f18;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/templates/header.php'; ?>

  <!-- Course Header -->
  <div class="course-detail-header">
    <div class="container">
      <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff; margin-bottom: 12px; display: inline-block;">
        <?= e($course['category']) ?>
      </span>
      <h1><?= e($course['title']) ?></h1>
      <p class="meta">
        🎓 <?= e($course['pengajar']) ?> 
        <?php if ($course['lokasi']): ?>
           | 📍 <?= e($course['lokasi']) ?>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <div class="course-detail-body">
    <div class="container">
      <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= e($message) ?></div>
      <?php endif; ?>

      <div class="course-detail-grid">
        <!-- Main Content -->
        <div>
          <div class="course-detail-card">
            <?php if (!empty($course['thumbnail'])): ?>
              <img src="<?= e($course['thumbnail']) ?>" alt="<?= e($course['title']) ?>" style="width:100%;display:block;border-radius:12px;margin-bottom:20px;">
            <?php endif; ?>
            <h2>📝 Deskripsi</h2>
            <p style="text-align:justify;"><?= nl2br(e($course['description'] ?? 'Deskripsi akan segera hadir.')) ?></p>
          </div>
        </div>

        <!-- Sidebar -->
        <div>
          <div class="enroll-section">
            <div class="price">GRATIS</div>
            
            <div class="course-info-list">
              <div class="course-info-item">
                <div class="icon">📅</div>
                <div>
                  <div class="label">Jadwal</div>
                  <div class="value"><?= $course['jadwal'] ? date('d M Y, H:i', strtotime($course['jadwal'])) : 'Akan diinformasikan' ?></div>
                </div>
              </div>
              <div class="course-info-item">
                <div class="icon">👥</div>
                <div>
                  <div class="label">Kapasitas</div>
                  <div class="value"><?= $course['kapasitas'] ?> orang</div>
                </div>
              </div>
              <div class="course-info-item">
                <div class="icon">📍</div>
                <div>
                  <div class="label">Lokasi</div>
                  <div class="value"><?= e($course['lokasi'] ?: 'Online') ?></div>
                </div>
              </div>
              <div class="course-info-item">
                <div class="icon">👨‍🏫</div>
                <div>
                  <div class="label">Pengajar</div>
                  <div class="value"><?= e($course['pengajar']) ?></div>
                </div>
              </div>
            </div>

            <div class="spots">📌 <?= $spots_left ?> tempat tersisa</div>

            <?php if (!$is_logged_in): ?>
              <a href="/login?redirect=/course/<?= $id ?>" class="btn btn-primary btn-block">Login untuk Daftar</a>
              <p style="text-align: center; margin-top: 12px; font-size: 0.875rem; color: #666;">
                Belum punya akun? <a href="/register">Daftar di sini</a>
              </p>
            <?php elseif ($registered): ?>
              <div class="alert alert-success">
                ✓ Anda sudah terdaftar<br>
                <strong>Status:</strong> <?= ucfirst($registration['status']) ?>
              </div>
              <form method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin ingin membatalkan?')">Batalkan Pendaftaran</button>
              </form>
            <?php else: ?>
              <form method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <button type="submit" class="btn btn-success btn-block">📝 Daftar Sekarang</button>
              </form>
            <?php endif; ?>
          </div>

          <div style="margin-top: 20px;">
            <a href="/course" class="btn btn-sm" style="background: #f0f0f0; color: #333;">← Kembali ke Course</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/templates/footer.php'; ?>
</body>
</html>
