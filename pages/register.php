<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout auth-page';
$pageTitle = 'Daftar Akun';

// Handle form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
      $error = 'Nama, email, dan password harus diisi';
    } elseif ($password !== $confirm) {
      $error = 'Password dan konfirmasi password tidak sama';
    } elseif (strlen($password) < 6) {
      $error = 'Password minimal 6 karakter';
    } else {
      // Check if email exists
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $error = 'Email sudah terdaftar';
      } else {
        // Create user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone_whatsapp, password_hash) VALUES (?, ?, ?, ?)");
        try {
          $stmt->execute([$name, $email, $phone, $hash]);
          
          // Send welcome email
          $subject = 'Selamat Datang di Unisbank Career Centre';
          $content = '
            <h3>Halo, ' . e($name) . '!</h3>
            <p>Selamat datang di <strong>Unisbank Career Centre (UCC)</strong>.</p>
            <p>Akun Anda telah berhasil dibuat dengan email: <strong>' . e($email) . '</strong></p>
            <p>Anda sekarang dapat:</p>
            <ul>
              <li>Mendaftar course dan pelatihan karir</li>
              <li>Melihat lowongan pekerjaan</li>
              <li>Mengakses dashboard pribadi</li>
            </ul>
            <p style="margin-top: 20px;">
              <a href="' . SITE_URL . '/login" style="background: #7a0f18; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Login Sekarang</a>
            </p>
            <p style="margin-top: 20px; color: #666;">Jika Anda memiliki pertanyaan, silakan hubungi kami.</p>
          ';
          send_email_template($email, $subject, $content);
          
          $success = 'Pendaftaran berhasil! Silakan <a href="/login">login</a>';
        } catch (Exception $e) {
          $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Unisbank Career Centre</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <img src="/uploads/logo.png" alt="UCC Logo" class="auth-logo" onerror="this.style.display='none'">
        <h1>Daftar Akun</h1>
        <p>Bergabung dengan Unisbank Career Centre</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php else: ?>
      
      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label for="name">Nama Lengkap</label>
          <input type="text" id="name" name="name" required placeholder="Nama lengkap">
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required placeholder="email@unisbank.ac.id">
        </div>
        
        <div class="form-group">
          <label for="phone">No. WhatsApp (opsional)</label>
          <input type="tel" id="phone" name="phone" placeholder="0812xxxxxxx">
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Min. 6 karakter">
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Konfirmasi Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Daftar</button>
      </form>
      
      <?php endif; ?>
      
      <div class="auth-footer">
        <p>Sudah punya akun? <a href="/login">Login di sini</a></p>
        <p><a href="/">← Kembali ke Home</a></p>
      </div>
    </div>
  </div>
</body>
</html>
