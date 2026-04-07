<?php
require_once __DIR__ . '/../../config.php';

if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

// Get news ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Get news data
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
  header('Location: /admin/news');
  exit;
}

// Delete image file
if ($news['image']) {
  $imagePath = __DIR__ . '/../../uploads/news/' . $news['image'];
  if (file_exists($imagePath)) {
    unlink($imagePath);
  }
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
$stmt->execute([$id]);

header('Location: /admin/news');
exit;