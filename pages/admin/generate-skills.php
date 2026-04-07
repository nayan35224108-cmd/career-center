<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Sesi admin tidak valid']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Metode tidak diizinkan']);
  exit;
}

if (!csrf_check($_POST['csrf'] ?? '')) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Token CSRF tidak valid']);
  exit;
}

$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$model = sanitize($_POST['model'] ?? '');

if ($title === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Judul course wajib diisi']);
  exit;
}

if ($model === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Model wajib dipilih']);
  exit;
}

$context = $description !== '' ? "Deskripsi: {$description}" : '';

$prompt = "Kamu adalah perancang kurikulum profesional. " .
  "Berdasarkan course berikut, buat daftar skill atau kompetensi yang akan dikuasai peserta setelah mengikuti course ini. " .
  "Judul course: \"{$title}\". {$context} " .
  "Buat 5-7 poin skill dalam bahasa Indonesia. " .
  "ATURAN WAJIB: setiap poin MAKSIMAL 40 karakter (termasuk spasi), singkat, padat, dan spesifik. " .
  "Output HANYA berupa JSON array of strings, tanpa penjelasan, tanpa markdown, contoh: [\"Skill satu\", \"Skill dua\"]";

$payload = [
  'model' => $model,
  'messages' => [
    ['role' => 'user', 'content' => $prompt]
  ],
  'stream' => false,
  'options' => [
    'temperature' => 0.5
  ]
];

$ch = curl_init('http://localhost:11434/v1/chat/completions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $curl_error) {
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => 'Gagal terhubung ke Ollama: ' . $curl_error]);
  exit;
}

$data = json_decode($response, true);
$text = trim($data['choices'][0]['message']['content'] ?? '');

if ($http_code >= 400 || $text === '') {
  $api_error = $data['error']['message'] ?? 'Model tidak dapat menghasilkan daftar skill';
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => $api_error]);
  exit;
}

// Ekstrak JSON array dari respons (model kadang membungkus dengan markdown)
if (preg_match('/\[.*\]/s', $text, $matches)) {
  $text = $matches[0];
}

$skills = json_decode($text, true);
if (!is_array($skills)) {
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => 'Format respons tidak valid, coba lagi']);
  exit;
}

// Bersihkan dan batasi panjang tiap item (max 45 karakter)
$skills = array_values(array_filter(array_map(function($s) {
  $s = trim($s);
  return mb_strlen($s) > 45 ? mb_substr($s, 0, 43) . '...' : $s;
}, $skills)));

echo json_encode([
  'success' => true,
  'skills' => $skills,
  'raw' => json_encode($skills, JSON_UNESCAPED_UNICODE)
]);
