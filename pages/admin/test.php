<?php
require_once __DIR__ . '/config.php';

echo "=== DEBUG INFO ===\n";
echo "Segments: " . print_r($segments ?? [], true) . "\n";
echo "Admin Session: " . print_r($_SESSION['admin_user'] ?? [], true) . "\n";
echo "=================\n";

echo "<h1>TEST PAGE - /admin/courses</h1>";
echo "<p>Jika Anda melihat ini, routing berhasil!</p>";
echo "<a href='/admin'>Back to Admin</a>";
