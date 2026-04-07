<?php
// Auth functions

function get_user() {
  if (!empty($_SESSION['user_id'])) {
    return [
      'id' => $_SESSION['user_id'],
      'name' => $_SESSION['user_name'],
      'email' => $_SESSION['user_email'],
      'role' => $_SESSION['user_role'] ?? 'user'
    ];
  }
  return null;
}

function is_user_logged_in() {
  return !empty($_SESSION['user_id']);
}

function require_login() {
  if (!is_user_logged_in()) {
    redirect('/login');
  }
}

function require_admin() {
  require_login();
  if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    redirect('/dashboard');
  }
}

function login_user($user) {
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['user_name'] = $user['name'];
  $_SESSION['user_email'] = $user['email'];
  $_SESSION['user_role'] = $user['role'] ?? 'user';
  session_regenerate_id(true);
}

function logout_user() {
  $_SESSION = [];
  session_destroy();
  session_start();
  session_regenerate_id(true);
}

function get_user_courses($user_id) {
  global $pdo;
  $stmt = $pdo->prepare("
    SELECT c.*, cr.status, cr.registered_at 
    FROM courses c 
    JOIN course_registrations cr ON c.id = cr.course_id 
    WHERE cr.user_id = ? 
    ORDER BY cr.registered_at DESC
  ");
  $stmt->execute([$user_id]);
  return $stmt->fetchAll();
}

function get_course_registrations($course_id) {
  global $pdo;
  $stmt = $pdo->prepare("
    SELECT cr.*, u.name, u.email, u.phone_whatsapp
    FROM course_registrations cr
    JOIN users u ON cr.user_id = u.id
    WHERE cr.course_id = ?
    ORDER BY cr.registered_at DESC
  ");
  $stmt->execute([$course_id]);
  return $stmt->fetchAll();
}

function get_all_courses($active_only = true) {
  global $pdo;
  $sql = "SELECT * FROM courses";
  if ($active_only) {
    $sql .= " WHERE is_active = 1";
  }
  $sql .= " ORDER BY jadwal DESC";
  $stmt = $pdo->query($sql);
  return $stmt->fetchAll();
}

function get_course_by_id($id) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

function register_course($user_id, $course_id) {
  global $pdo;
  try {
    $stmt = $pdo->prepare("INSERT INTO course_registrations (user_id, course_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $course_id]);
    return ['success' => true];
  } catch (Exception $e) {
    return ['success' => false, 'error' => 'Anda sudah terdaftar di course ini'];
  }
}

function cancel_registration($user_id, $course_id) {
  global $pdo;
  $stmt = $pdo->prepare("DELETE FROM course_registrations WHERE user_id = ? AND course_id = ?");
  return $stmt->execute([$user_id, $course_id]);
}

function get_published_jobs() {
  global $pdo;
  auto_unpublish_expired_jobs();
  $stmt = $pdo->query("SELECT * FROM jobs WHERE is_published = 1 AND (deadline_at IS NULL OR deadline_at >= NOW()) ORDER BY COALESCE(deadline_at, '9999-12-31 23:59:59') ASC, created_at DESC");
  return $stmt->fetchAll();
}

function auto_unpublish_expired_jobs() {
  global $pdo;
  $stmt = $pdo->prepare("UPDATE jobs SET is_published = 0 WHERE is_published = 1 AND deadline_at IS NOT NULL AND deadline_at < NOW()");
  $stmt->execute();
}

function get_job_by_slug($slug) {
  global $pdo;
  $stmt = $pdo->prepare("SELECT * FROM jobs WHERE slug = ? AND is_published = 1 AND (deadline_at IS NULL OR deadline_at >= NOW()) LIMIT 1");
  $stmt->execute([$slug]);
  return $stmt->fetch();
}
