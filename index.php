<?php
require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'https';
$host = $_SERVER['HTTP_HOST'] ?? 'ucc.pjdigital.top';
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = $path === '' ? [] : explode('/', $path);

// Default values
$pageTitle = 'Home';
$metaDescription = 'Unisbank Career Centre - Portal pelatihan karir & soft skill untuk mahasiswa';
$canonicalUrl = $scheme . '://' . $host . '/';
$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'home';

$pageContent = '';

// Handle logout
if ($segments[0] === 'logout') {
  logout_user();
  redirect('/');
  exit;
}

// Handle admin logout
if ($segments[0] === 'admin' && ($segments[1] ?? '') === 'logout') {
  unset($_SESSION['admin_user']);
  redirect('/admin/login');
  exit;
}

// Handle routing based on segments
if (empty($segments) || $segments[0] === '' || $segments[0] === 'home') {
  $currentPage = 'home';
  $pageTitle = 'Home';
  $metaDescription = 'Unisbank Career Centre - Portal pelatihan karir & soft skill untuk mahasiswa';
  $canonicalUrl = $scheme . '://' . $host . '/';

  ob_start();
  include __DIR__ . '/pages/home.php';
  $pageContent = ob_get_clean();

} elseif ($segments[0] === 'about') {
  $currentPage = 'about';
  $pageTitle = 'Tentang Kami';
  $metaDescription = 'Tentang Unisbank Career Centre - Visi, Misi, dan Tujuan';
  $canonicalUrl = $scheme . '://' . $host . '/about';

  ob_start();
  include __DIR__ . '/pages/about.php';
  $pageContent = ob_get_clean();

} elseif ($segments[0] === 'course') {
  $currentPage = 'course';
  $pageTitle = 'Course & Pelatihan';
  $metaDescription = 'Course & Pelatihan - Job Interview, Profiling, Soft Skill, dll';
  $canonicalUrl = $scheme . '://' . $host . '/course';

  if (isset($segments[1]) && is_numeric($segments[1])) {
    // Course detail
    ob_start();
    include __DIR__ . '/pages/course-detail.php';
    $pageContent = ob_get_clean();
  } else {
    ob_start();
    include __DIR__ . '/pages/course.php';
    $pageContent = ob_get_clean();
  }

} elseif ($segments[0] === 'jobs') {
  $currentPage = 'jobs';
  $pageTitle = 'Lowongan Pekerjaan';
  $metaDescription = 'Lowongan kerja terbaru untuk mahasiswa dan alumni Unisbank';
  $canonicalUrl = $scheme . '://' . $host . '/jobs';

  if (!empty($segments[1])) {
    $_GET['slug'] = $segments[1];
    ob_start();
    include __DIR__ . '/pages/jobs-detail.php';
    $pageContent = ob_get_clean();
  } else {
    ob_start();
    include __DIR__ . '/pages/jobs.php';
    $pageContent = ob_get_clean();
  }

} elseif ($segments[0] === 'contact') {
  $currentPage = 'contact';
  $pageTitle = 'Hubungi Kami';
  $metaDescription = 'Hubungi Kami - Unisbank Career Centre';
  $canonicalUrl = $scheme . '://' . $host . '/contact';

  ob_start();
  include __DIR__ . '/pages/contact.php';
  $pageContent = ob_get_clean();

} elseif ($segments[0] === 'news') {
  $currentPage = 'news';
  $pageTitle = 'Berita';
  $metaDescription = 'Berita dan informasi terbaru dari Unisbank Career Centre';
  $canonicalUrl = $scheme . '://' . $host . '/news';

  if (!empty($segments[1])) {
    $_GET['slug'] = $segments[1];
    ob_start();
    include __DIR__ . '/pages/news-detail.php';
    $pageContent = ob_get_clean();
  } else {
    ob_start();
    include __DIR__ . '/pages/news.php';
    $pageContent = ob_get_clean();
  }

} elseif ($segments[0] === 'login') {
  $currentPage = 'login';
  $pageTitle = 'Login';
  
  ob_start();
  include __DIR__ . '/pages/login.php';
  $pageContent = ob_get_clean();

} elseif ($segments[0] === 'register') {
  $currentPage = 'register';
  $pageTitle = 'Daftar';
  
  ob_start();
  include __DIR__ . '/pages/register.php';
  $pageContent = ob_get_clean();

} elseif ($segments[0] === 'admin') {
  if (($segments[1] ?? '') === 'generate-description') {
    include __DIR__ . '/pages/admin/generate-description.php';
    exit;
  }

  if (($segments[1] ?? '') === 'improve-title') {
    include __DIR__ . '/pages/admin/improve-title.php';
    exit;
  }

  if (($segments[1] ?? '') === 'generate-skills') {
    include __DIR__ . '/pages/admin/generate-skills.php';
    exit;
  }

  if (($segments[1] ?? '') === 'generate-course-image') {
    include __DIR__ . '/pages/admin/generate-course-image.php';
    exit;
  }

  if (($segments[1] ?? '') === 'login') {
    if (!empty($_SESSION['admin_user'])) {
      header('Location: /admin');
      exit;
    }
    $currentPage = 'admin-login';
    $pageTitle = 'Admin Login';
    $skipLayout = true;
    
    ob_start();
    include __DIR__ . '/pages/admin/login.php';
    $pageContent = ob_get_clean();
  } elseif (empty($_SESSION['admin_user'])) {
    header('Location: /admin/login');
    exit;
  } elseif (empty($segments[1]) || $segments[1] === 'dashboard') {
    $currentPage = 'admin-dashboard';
    $pageTitle = 'Admin Dashboard';
    $skipLayout = true;
    
    ob_start();
    include __DIR__ . '/pages/admin/dashboard.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'test') {
    $skipLayout = true;
    $pageContent = '<h1>TEST - Routing OK</h1>';
  } elseif ($segments[1] === 'courses') {
    $currentPage = 'admin-courses';
    $pageTitle = 'Kelola Courses';
    $skipLayout = true;
    
    if (($segments[2] ?? '') === 'add') {
      ob_start();
      include __DIR__ . '/pages/admin/course-add.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'edit' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/course-edit.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'delete' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/course-delete.php';
      $pageContent = ob_get_clean();
    } else {
      ob_start();
      include __DIR__ . '/pages/admin/courses.php';
      $pageContent = ob_get_clean();
    }
  } elseif ($segments[1] === 'master-categories') {
    $currentPage = 'admin-master-categories';
    $pageTitle = 'Master Kategori';
    $skipLayout = true;

    ob_start();
    include __DIR__ . '/pages/admin/master-categories.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'master-pengajar') {
    $currentPage = 'admin-master-pengajar';
    $pageTitle = 'Master Pengajar';
    $skipLayout = true;

    ob_start();
    include __DIR__ . '/pages/admin/master-pengajar.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'registrations') {
    $currentPage = 'admin-registrations';
    $pageTitle = 'Pendaftaran';
    $skipLayout = true;

    ob_start();
    include __DIR__ . '/pages/admin/registrations.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'users') {
    $currentPage = 'admin-users';
    $pageTitle = 'Users';
    $skipLayout = true;

    ob_start();
    include __DIR__ . '/pages/admin/users.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'jobs') {
    $currentPage = 'admin-jobs';
    $pageTitle = 'Jobs';
    $skipLayout = true;

    if (($segments[2] ?? '') === 'add') {
      ob_start();
      include __DIR__ . '/pages/admin/job-add.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'edit' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/job-edit.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'delete' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/job-delete.php';
      $pageContent = ob_get_clean();
    } else {
      ob_start();
      include __DIR__ . '/pages/admin/jobs.php';
      $pageContent = ob_get_clean();
    }
  } elseif ($segments[1] === 'news') {
    $currentPage = 'admin-news';
    $pageTitle = 'Berita';
    $skipLayout = true;

    if (($segments[2] ?? '') === 'add') {
      ob_start();
      include __DIR__ . '/pages/admin/news-add.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'edit' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/news-edit.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'delete' && isset($segments[3])) {
      $_GET['id'] = $segments[3];
      ob_start();
      include __DIR__ . '/pages/admin/news-delete.php';
      $pageContent = ob_get_clean();
    } else {
      ob_start();
      include __DIR__ . '/pages/admin/news.php';
      $pageContent = ob_get_clean();
    }
  } elseif ($segments[1] === 'media') {
    $currentPage = 'admin-media';
    $pageTitle = 'Media AI';
    $skipLayout = true;

    ob_start();
    include __DIR__ . '/pages/admin/media.php';
    $pageContent = ob_get_clean();
  } elseif ($segments[1] === 'settings') {
    $skipLayout = true;

    if (($segments[2] ?? '') === 'general') {
      $currentPage = 'admin-settings-general';
      $pageTitle = 'Pengaturan Umum';

      ob_start();
      include __DIR__ . '/pages/admin/settings-general.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'footer') {
      $currentPage = 'admin-settings-footer';
      $pageTitle = 'Pengaturan Footer';

      ob_start();
      include __DIR__ . '/pages/admin/footer-settings.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'contact') {
      $currentPage = 'admin-settings-contact';
      $pageTitle = 'Pengaturan Kontak';

      ob_start();
      include __DIR__ . '/pages/admin/settings-contact.php';
      $pageContent = ob_get_clean();
    } elseif (($segments[2] ?? '') === 'about') {
      $currentPage = 'admin-settings-about';
      $pageTitle = 'Pengaturan About';

      ob_start();
      include __DIR__ . '/pages/admin/settings-about.php';
      $pageContent = ob_get_clean();
    } else {
      $currentPage = 'admin-settings';
      $pageTitle = 'Pengaturan';

      ob_start();
      include __DIR__ . '/pages/admin/settings.php';
      $pageContent = ob_get_clean();
    }
  } else {
    include __DIR__ . '/templates/404.php';
    exit;
  }

} elseif ($segments[0] === 'dashboard') {
  $currentPage = 'dashboard';
  $pageTitle = 'Dashboard';
  
  ob_start();
  include __DIR__ . '/pages/dashboard.php';
  $pageContent = ob_get_clean();

} else {
  include __DIR__ . '/templates/404.php';
  exit;
}

// Include layout
if (!empty($skipLayout)) {
  echo $pageContent;
} else {
  include __DIR__ . '/templates/header.php';
  echo $pageContent;
  include __DIR__ . '/templates/footer.php';
}
