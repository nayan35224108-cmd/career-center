<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
  try {
    $stmt = $pdo->prepare("DELETE FROM course_registrations WHERE course_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = 'Course berhasil dihapus';
    $_SESSION['message_type'] = 'success';
  } catch (Exception $e) {
    $_SESSION['message'] = 'Gagal menghapus course';
    $_SESSION['message_type'] = 'error';
  }
}

header('Location: /admin/courses');
exit;
