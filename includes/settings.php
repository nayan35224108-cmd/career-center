<?php
function get_setting($pdo, $key, $default = '') {
  try {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['value'] : $default;
  } catch (Throwable $e) {
    return $default;
  }
}

function set_setting($pdo, $key, $value) {
  try {
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()");
    $stmt->execute([$key, $value, $value]);
    return true;
  } catch (Throwable $e) {
    return false;
  }
}
