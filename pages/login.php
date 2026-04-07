<?php
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout auth-page';
$pageTitle = 'Login';

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
      $error = 'Username dan password harus diisi';
    } else {
      $login_success = false;

      // Try Unisbank API login first
      if (defined('UNISBANK_API_TOKEN') && !empty(UNISBANK_API_TOKEN)) {
        $api_result = unisbank_authenticate($username, $password);

        if ($api_result['success']) {
          $api_user = $api_result['user'];

          // Determine user identifier (NIM for students, NIY for lecturers)
          $user_identifier = $api_user['nim'] ?: $api_user['niy'] ?: $username;
          $email = $api_user['email'] ?: $user_identifier . '@unisbank.ac.id';

          // Check if user exists in local database
          $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
          $stmt->execute([$email, $user_identifier]);
          $local_user = $stmt->fetch();

          if ($local_user) {
            // Update existing user with latest data from API
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, nim = ?, phone_whatsapp = ?, fakultas = ?, prodi = ?, foto = ?, user_type = ?, last_login = NOW() WHERE id = ?");
            $stmt->execute([
              $api_user['nama_lengkap'],
              $email,
              $api_user['nim'] ?: $api_user['niy'],
              $api_user['handphone'],
              $api_user['fakultas'],
              $api_user['homebase'] ?: $api_user['prodi'],
              $api_user['foto'] ?? null,
              strtolower($api_user['role']),
              $local_user['id']
            ]);
            $local_user['name'] = $api_user['nama_lengkap'];
            $local_user['email'] = $email;
          } else {
            // Create new user from API (no password - login via API only)
            $random_password = bin2hex(random_bytes(32)); // Random password, user won't use this
            $password_hash = password_hash($random_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, username, nim, phone_whatsapp, fakultas, prodi, foto, user_type, password_hash, role, is_active, must_change_password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 1, 0, NOW())");
            $stmt->execute([
              $api_user['nama_lengkap'],
              $email,
              $user_identifier,
              $api_user['nim'] ?: $api_user['niy'],
              $api_user['handphone'],
              $api_user['fakultas'],
              $api_user['homebase'] ?: $api_user['prodi'],
              $api_user['foto'] ?? null,
              strtolower($api_user['role']),
              $password_hash
            ]);
            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $local_user = $stmt->fetch();
          }

          if ($local_user) {
            login_user($local_user);
            $login_success = true;
            redirect('/dashboard');
          }
        }
      }

      // Fall back to local database authentication
      if (!$login_success) {
        // Try login with email or username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
          login_user($user);
          redirect('/dashboard');
        } else {
          $error = 'Username atau password salah';
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
        <h1>Login</h1>
        <p>Masuk ke Unisbank Career Centre</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      
      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label for="username">Username / NIM</label>
          <input type="text" id="username" name="username" required placeholder="NIM atau Username">
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
      </form>
      
      <div class="auth-footer">
        <p>Belum punya akun? <a href="/register">Daftar di sini</a></p>
        <p><a href="/">← Kembali ke Home</a></p>
      </div>
    </div>
  </div>
</body>
</html>
