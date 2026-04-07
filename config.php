<?php
session_start();

// Load Composer autoloader (for PHPMailer)
require_once __DIR__ . '/vendor/autoload.php';

// Load helper functions
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/unisbank-api.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'ucc');
define('DB_USER', 'dbs13');
define('DB_PASS', 'qGLnu48XZEKjlxL9OH8Q');
define('BASE_URL', 'https://ucc.unisbank.ac.id');

// Setting constants
define('SITE_TITLE', 'Unisbank Career Centre');
define('SITE_URL', 'https://ucc.unisbank.ac.id');
define('SITE_LOGO', '');

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash-lite-preview-09-2025');
define('GEMINI_IMAGE_MODEL', getenv('GEMINI_IMAGE_MODEL') ?: 'gemini-3.1-flash-image-preview');

// Unisbank API Configuration
define('UNISBANK_API_URL', 'https://api.unisbank.ac.id/auth/');
define('UNISBANK_API_TOKEN', 'ZGV2ZWxvcGVyQGVkdS51bmlzYmFuay5hYy5pZDpmODA1MTZjMjFjNTQyYjMzYTY0ZmQxNjU3ODI1ZDBhYg==');

// SMTP Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'dbs@edu.unisbank.ac.id');
define('SMTP_PASS', 'flcuzjozfbfncbyy');
define('SMTP_FROM', 'dbs@edu.unisbank.ac.id');
define('SMTP_FROM_NAME', 'Unisbank Career Centre');
define('SMTP_SECURE', 'tls');

try {
  $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  exit('Database connection failed.');
}
