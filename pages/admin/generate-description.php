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

$prompt = "Buat deskripsi course dalam bahasa Indonesia berdasarkan judul berikut: '{$title}'. " .
  "Panjang 120-180 kata, formal, jelas, dan fokus pada manfaat untuk mahasiswa/pencari kerja. " .
  "Output HANYA isi deskripsi tanpa heading, tanpa bullet, tanpa tanda kutip.";

$payload = [
  'model' => $model,
  'messages' => [
    ['role' => 'user', 'content' => $prompt]
  ],
  'stream' => false,
  'options' => [
    'temperature' => 0.7
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
$text = $data['choices'][0]['message']['content'] ?? '';

if ($http_code >= 400 || $text === '') {
  $api_error = $data['error']['message'] ?? 'Model tidak dapat menghasilkan deskripsi';
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => $api_error]);
  exit;
}

echo json_encode([
  'success' => true,
  'description' => trim($text),
  'model' => $model
]);
