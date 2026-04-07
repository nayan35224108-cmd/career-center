<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
  $stmt = $pdo->prepare('SELECT poster_image FROM jobs WHERE id = ?');
  $stmt->execute([$id]);
  $job = $stmt->fetch();

  $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
  $stmt->execute([$id]);

  if (!empty($job['poster_image']) && strpos($job['poster_image'], '/uploads/jobs/') === 0) {
    $abs = __DIR__ . '/../../' . ltrim($job['poster_image'], '/');
    if (is_file($abs)) {
      @unlink($abs);
    }
  }
}

header('Location: /admin/jobs');
exit;
