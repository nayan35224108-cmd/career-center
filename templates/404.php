<?php
$pageTitle = 'Halaman Tidak Ditemukan';
$metaDescription = '404 - Halaman yang Anda cari tidak ditemukan';
$canonicalUrl = SITE_URL;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="logo" href="/">
        <span>UCC</span>
      </a>
      <button id="menuToggle" class="menu-toggle">☰</button>
      <nav id="nav" class="nav">
        <a href="/" class="active">Home</a>
        <a href="/about">About</a>
        <a href="/course">Course</a>
        <a href="/contact">Contact</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section class="main-col">
      <div class="error-page">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Halaman Tidak Ditemukan</h2>
        <p class="error-message">Maaf, halaman yang Anda cari tidak tersedia atau telah dihapus.</p>
        <a href="/" class="btn btn-primary">Kembali ke Home</a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-content">
      <div class="footer-section">
        <h3>Unisbank Career Centre</h3>
        <p><?= e($siteTitle) ?></p>
      </div>
    </div>
    <div class="footer-bottom">
      <small>© <?= date('Y') ?> Unisbank Career Centre.</small>
    </div>
  </footer>
</body>
</html>
