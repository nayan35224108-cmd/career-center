<?php
require_once __DIR__ . '/../../config.php';


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
      $error = 'Email dan password harus diisi';
    } else {
      $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
      $stmt->execute([$email]);
      $user = $stmt->fetch();
      
      if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_user'] = [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email'],
          'role' => $user['role']
        ];
        $_SESSION['admin_login_time'] = time();
        redirect('/admin');
      } else {
        $error = 'Email atau password salah';
      }
    }
  }
}

$pageTitle = 'Admin Login';
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout auth-page';
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
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 20px; margin: 0;">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <img src="/uploads/logo.png" alt="UCC Logo" class="auth-logo" onerror="this.style.display='none'">
        <h1>Admin Login</h1>
        <p>Masuk ke Panel Admin UCC</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      
      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label for="email">Email Admin</label>
          <input type="email" id="email" name="email" required placeholder="admin@ucc.local">
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
      </form>
      
      <div class="auth-footer">
        <p><a href="/">← Kembali ke Home</a></p>
      </div>
    </div>
  </div>
</body>
</html>
