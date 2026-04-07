<?php
require_once __DIR__ . '/../includes/settings.php';

$siteTitle = get_setting($pdo, 'site_title', 'Unisbank Career Centre');
$siteLogo = '/uploads/logo.png';
$siteFavicon = '';

// SEO Meta Tags
$pageTitle = isset($pageTitle) && $pageTitle ? ($pageTitle . ' | ' . $siteTitle) : $siteTitle;
$metaDescription = isset($metaDescription) && $metaDescription ? $metaDescription : 'Unisbank Career Centre - Portal pelatihan karir & soft skill untuk mahasiswa';
$canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : SITE_URL;

$ogType = isset($ogType) ? $ogType : 'website';
$articlePublishedTime = isset($articlePublishedTime) ? $articlePublishedTime : null;
$articleModifiedTime = isset($articleModifiedTime) ? $articleModifiedTime : null;

$layoutMode = isset($layoutMode) ? $layoutMode : 'one-col';
$bodyClass = isset($bodyClass) && $bodyClass ? $bodyClass : 'inner-page';
$currentPage = isset($currentPage) ? $currentPage : 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <?php if ($siteFavicon): ?>
    <link rel="icon" href="<?= e($siteFavicon) ?>" type="image/png">
  <?php else: ?>
    <link rel="icon" href="<?= e($siteLogo) ?>" type="image/png">
  <?php endif; ?>

  <!-- Open Graph -->
  <meta property="og:type" content="<?= e($ogType) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <?php if ($siteLogo): ?>
    <meta property="og:image" content="<?= e(SITE_URL . $siteLogo) ?>">
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($metaDescription) ?>">

  <!-- Schema.org -->
  <script type="application/ld+json">
  <?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $siteTitle,
    'url' => SITE_URL,
    'logo' => $siteLogo ? SITE_URL . $siteLogo : null
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  </script>

  <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(__DIR__ . '/../assets/css/theme.css') ? filemtime(__DIR__ . '/../assets/css/theme.css') : time() ?>">
  <?php if (!empty($extraStyles)) echo $extraStyles; ?>
</head>
<body class="<?= e($bodyClass) ?>">
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="logo" href="/">
        <?php if ($siteLogo): ?>
          <img src="<?= e($siteLogo) ?>" alt="logo" style="width:48px;height:48px;object-fit:contain;vertical-align:middle;margin-right:10px">
        <?php endif; ?>
        <span><?= e($siteTitle) ?></span>
      </a>
      <button id="menuToggle" class="menu-toggle">☰</button>
<?php
$user = is_user_logged_in() ? get_user() : null;
?>
      <nav id="nav" class="nav">
        <a href="/" class="<?= $currentPage == 'home' ? 'active' : '' ?>">Home</a>
        <a href="/about" class="<?= $currentPage == 'about' ? 'active' : '' ?>">About</a>
        <a href="/course" class="<?= $currentPage == 'course' ? 'active' : '' ?>">Course</a>
        <a href="/jobs" class="<?= $currentPage == 'jobs' ? 'active' : '' ?>">Jobs</a>
        <a href="/news" class="<?= $currentPage == 'news' ? 'active' : '' ?>">Berita</a>
        <a href="/contact" class="<?= $currentPage == 'contact' ? 'active' : '' ?>">Contact</a>
        <?php if ($user): ?>
          <a href="/dashboard" class="<?= $currentPage == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <?php else: ?>
          <a href="/login" class="login-btn">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container <?= $layoutMode === 'one-col' ? 'one-col-layout' : 'two-col-layout' ?>">
    <section class="main-col">
      <?php if ($message = get_message()): ?>
        <div class="alert alert-<?= e($message['type']) ?>">
          <?= e($message['text']) ?>
        </div>
      <?php endif; ?>
