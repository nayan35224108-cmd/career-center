<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json; charset=utf-8');

// ─── Helper functions ─────────────────────────────────────────────────────

function gd_rgb($img, $r, $g, $b)       { return imagecolorallocate($img, $r, $g, $b); }
function gd_rgba($img, $r, $g, $b, $a)  { return imagecolorallocatealpha($img, $r, $g, $b, $a); }

function poster_wrap_text(string $text, float $size, string $font, int $max_w): array {
  $words = preg_split('/\s+/', trim($text));
  $lines = [];
  $cur   = '';
  foreach ($words as $word) {
    $test = $cur === '' ? $word : "$cur $word";
    $bbox = imagettfbbox($size, 0, $font, $test);
    if (($bbox[2] - $bbox[0]) > $max_w && $cur !== '') {
      $lines[] = $cur;
      $cur     = $word;
    } else {
      $cur = $test;
    }
  }
  if ($cur !== '') $lines[] = $cur;
  return $lines;
}

function poster_text($img, $size, $font, $x, $y, $text, $color): void {
  imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
}

function poster_text_shadow($img, $size, $font, $x, $y, $text, $color): void {
  $shadow = gd_rgba($img, 0, 0, 0, 60);
  imagettftext($img, $size, 0, $x + 2, $y + 2, $shadow, $font, $text);
  imagecolordeallocate($img, $shadow);
  imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
}

function poster_text_centered($img, $size, $font, $x1, $x2, $y, $text, $color): void {
  $bbox = imagettfbbox($size, 0, $font, $text);
  $tw   = $bbox[2] - $bbox[0];
  $cx   = (int)($x1 + ($x2 - $x1 - $tw) / 2);
  imagettftext($img, $size, 0, $cx, $y, $color, $font, $text);
}

// Truncate text with "..." until it fits within max_w pixels
function poster_fit_text(string $text, float $size, string $font, int $max_w): string {
  $bbox = imagettfbbox($size, 0, $font, $text);
  if (($bbox[2] - $bbox[0]) <= $max_w) return $text;
  while (mb_strlen($text) > 1) {
    $text = mb_substr($text, 0, mb_strlen($text) - 1);
    $try  = $text . '...';
    $bbox = imagettfbbox($size, 0, $font, $try);
    if (($bbox[2] - $bbox[0]) <= $max_w) return $try;
  }
  return $text . '...';
}

function poster_rounded_rect($img, $x1, $y1, $x2, $y2, $r, $color): void {
  imagefilledrectangle($img, $x1 + $r, $y1,      $x2 - $r, $y2,      $color);
  imagefilledrectangle($img, $x1,      $y1 + $r,  $x2,      $y2 - $r, $color);
  imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
  imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
  imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
  imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
}

function format_jadwal_id(string $jadwal): string {
  $ts = strtotime($jadwal);
  if (!$ts) return $jadwal;
  $days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  $months = ['','Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'];
  return $days[(int)date('w', $ts)] . ', ' . (int)date('j', $ts) . ' '
    . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts) . '  ' . date('H:i', $ts) . ' WIB';
}

// ─── Auth & validation ────────────────────────────────────────────────────

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

$title       = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$skills_raw  = $_POST['skills'] ?? '[]';
$jadwal      = sanitize($_POST['jadwal'] ?? '');
$lokasi      = sanitize($_POST['lokasi'] ?? '');
$pengajar    = sanitize($_POST['pengajar'] ?? '');

if ($title === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Judul wajib diisi']);
  exit;
}
if (GEMINI_API_KEY === '') {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Konfigurasi GEMINI_API_KEY belum diatur']);
  exit;
}

$skills = [];
$decoded = json_decode($skills_raw, true);
if (is_array($decoded)) $skills = $decoded;

// ─── 1. Generate background illustration via Gemini ───────────────────────

$image_prompt =
  "Professional photography-style image of people actively doing the activity described by this course title: \"{$title}\". " .
  "Visualize the most relevant real-world activity for that topic — for example: if it's about job interviews, " .
  "show a job interview scene; if it's about public speaking, show someone confidently speaking to an audience; " .
  "if it's about coding, show developers collaborating at computers; adapt naturally to the course theme. " .
  "Show 2-4 professionals in a realistic modern setting appropriate to the activity. " .
  "Warm, motivating atmosphere, natural lighting, dynamic and engaging composition. " .
  "NO TEXT, NO LOGOS, NO WATERMARKS. Realistic photographic style, high quality, vibrant colors.";

$api_url = 'https://generativelanguage.googleapis.com/v1beta/models/'
  . GEMINI_IMAGE_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$payload = [
  'contents' => [['parts' => [['text' => $image_prompt]]]],
  'generationConfig' => [
    'responseModalities' => ['IMAGE'],
    'temperature' => 0.5,
    'candidateCount' => 1
  ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$response   = curl_exec($ch);
$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false || $curl_error) {
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => 'Gagal terhubung ke layanan AI gambar']);
  exit;
}

$ai_data     = json_decode($response, true);
$inline_data = $ai_data['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? '';

if ($http_code >= 400 || $inline_data === '') {
  $api_error = $ai_data['error']['message'] ?? 'AI tidak dapat menghasilkan gambar';
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => $api_error]);
  exit;
}

$bg_raw = base64_decode($inline_data);
if ($bg_raw === false) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Gagal decode gambar dari AI']);
  exit;
}

// ─── 2. Compose poster with PHP GD ────────────────────────────────────────

$FONT_BOLD    = '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf';
$FONT_REGULAR = '/usr/share/fonts/dejavu/DejaVuSans.ttf';
$W       = 800;
$H       = 1200;
$ILLUS_H = 420;   // illustration zone height
$PAD     = 50;    // horizontal padding

$canvas = imagecreatetruecolor($W, $H);
imagealphablending($canvas, true);
imagesavealpha($canvas, false);

// Palette
$c_navy  = gd_rgb($canvas, 26,  26,  46);
$c_red   = gd_rgb($canvas, 122, 15,  24);
$c_red2  = gd_rgb($canvas, 158, 28,  42);
$c_gold  = gd_rgb($canvas, 245, 158, 11);
$c_lgold = gd_rgb($canvas, 255, 195, 80);
$c_white = gd_rgb($canvas, 255, 255, 255);
$c_lgray = gd_rgb($canvas, 195, 205, 218);
$c_mgray = gd_rgb($canvas, 130, 140, 158);

// Fill canvas with dark navy
imagefilledrectangle($canvas, 0, 0, $W, $H, $c_navy);

// ── Place AI illustration (center-crop to W × ILLUS_H) ───────────────────
$bg_img = @imagecreatefromstring($bg_raw);
if ($bg_img !== false) {
  $src_w = imagesx($bg_img);
  $src_h = imagesy($bg_img);

  // Center-crop to target aspect ratio
  $t_ratio = $W / $ILLUS_H;
  $s_ratio = $src_w / $src_h;
  if ($s_ratio > $t_ratio) {
    $crop_h = $src_h;
    $crop_w = (int)($src_h * $t_ratio);
    $crop_x = (int)(($src_w - $crop_w) / 2);
    $crop_y = 0;
  } else {
    $crop_w = $src_w;
    $crop_h = (int)($src_w / $t_ratio);
    $crop_x = 0;
    $crop_y = 0;
  }
  imagecopyresampled($canvas, $bg_img, 0, 0, $crop_x, $crop_y,
    $W, $ILLUS_H, $crop_w, $crop_h);
  imagedestroy($bg_img);

  // Gradient fade: illustration → navy (from y=240 down to y=ILLUS_H)
  $fade_start = 240;
  for ($fy = $fade_start; $fy <= $ILLUS_H; $fy++) {
    $progress = ($fy - $fade_start) / ($ILLUS_H - $fade_start);
    $alpha    = (int)(127 * (1 - $progress)); // 127=transparent .. 0=opaque
    $fc = gd_rgba($canvas, 26, 26, 46, $alpha);
    imageline($canvas, 0, $fy, $W - 1, $fy, $fc);
    imagecolordeallocate($canvas, $fc);
  }
}

// ── Workshop badge ───────────────────────────────────────────────────────
$badge_y1 = $ILLUS_H - 46;
$badge_y2 = $ILLUS_H + 32;
imagefilledrectangle($canvas, 0, $badge_y1, $W, $badge_y2, $c_red);
imagefilledrectangle($canvas, 0, $badge_y1, 7, $badge_y2, $c_gold); // left gold stripe
poster_text($canvas, 13, $FONT_BOLD, 24, $badge_y1 + 30, 'WORKSHOP  &  PELATIHAN', $c_white);

// ── Title ────────────────────────────────────────────────────────────────
$y = $badge_y2 + 38;
$title_lines = poster_wrap_text($title, 34, $FONT_BOLD, $W - $PAD * 2);
$title_lines = array_slice($title_lines, 0, 3);
foreach ($title_lines as $line) {
  poster_text_shadow($canvas, 34, $FONT_BOLD, $PAD, $y, $line, $c_white);
  $y += 46;
}

// ── Gold divider ─────────────────────────────────────────────────────────
$y += 16;
imagesetthickness($canvas, 2);
imageline($canvas, $PAD, $y, $W - $PAD, $y, $c_gold);
imagesetthickness($canvas, 1);
$y += 26;

// ── Skills ───────────────────────────────────────────────────────────────
if (!empty($skills)) {
  poster_text($canvas, 10, $FONT_BOLD, $PAD, $y, 'SKILL YANG AKAN DIKUASAI', $c_gold);
  $y += 26;

  $bullet_w  = 22;                                    // bullet circle width
  $text_x    = $PAD + $bullet_w + 10;                 // text start x
  $max_skill = $W - $PAD - $text_x;                   // available width for skill text

  foreach (array_slice($skills, 0, 7) as $skill) {
    // bullet circle
    poster_rounded_rect($canvas, $PAD, $y - 15, $PAD + $bullet_w, $y + 7, 11, $c_red2);
    poster_text_centered($canvas, 9, $FONT_BOLD, $PAD, $PAD + $bullet_w, $y, 'OK', $c_lgold);
    // fit skill text within available width
    $display = poster_fit_text($skill, 15, $FONT_REGULAR, $max_skill);
    poster_text($canvas, 15, $FONT_REGULAR, $text_x, $y, $display, $c_lgray);
    $y += 30;
  }
  $y += 14;
} else {
  $y += 8;
}

// ── Info section ─────────────────────────────────────────────────────────
$info_rows = [];
if ($jadwal)   $info_rows[] = ['label' => 'TANGGAL',    'val' => format_jadwal_id($jadwal)];
if ($lokasi)   $info_rows[] = ['label' => 'TEMPAT',     'val' => $lokasi];
if ($pengajar) $info_rows[] = ['label' => 'NARASUMBER', 'val' => $pengajar];

if (!empty($info_rows)) {
  // thin divider
  imagesetthickness($canvas, 1);
  $lc = gd_rgba($canvas, 255, 255, 255, 100);
  imageline($canvas, $PAD, $y, $W - $PAD, $y, $lc);
  imagecolordeallocate($canvas, $lc);
  $y += 18;

  poster_text($canvas, 10, $FONT_BOLD, $PAD, $y, 'INFORMASI KEGIATAN', $c_gold);
  $y += 26;

  foreach ($info_rows as $row) {
    // Label
    $lw = (int)(imagettfbbox(9, 0, $FONT_BOLD, $row['label'])[2]
      - imagettfbbox(9, 0, $FONT_BOLD, $row['label'])[0]) + 20;
    poster_rounded_rect($canvas, $PAD, $y - 14, $PAD + $lw, $y + 6, 8, $c_red);
    poster_text($canvas, 9, $FONT_BOLD, $PAD + 10, $y, $row['label'], $c_lgold);
    // Value
    poster_text($canvas, 15, $FONT_REGULAR, $PAD + $lw + 14, $y, $row['val'], $c_white);
    $y += 36;
  }
}

// ── Footer bar ───────────────────────────────────────────────────────────
$footer_y = $H - 70;
imagefilledrectangle($canvas, 0, $footer_y, $W, $H, $c_red);
imagefilledrectangle($canvas, 0, $footer_y, 6, $H, $c_gold);

poster_text($canvas, 14, $FONT_BOLD,    24, $footer_y + 28, 'Unisbank Career Centre', $c_white);
poster_text($canvas, 11, $FONT_REGULAR, 24, $footer_y + 50, 'ucc.unisbank.ac.id', $c_lgray);

// Decorative dots right
for ($i = 0; $i < 6; $i++) {
  $dx  = $W - 28 - $i * 22;
  $dc  = ($i === 0) ? $c_gold : gd_rgba($canvas, 255, 255, 255, 80 + $i * 5);
  imagefilledellipse($canvas, $dx, $footer_y + 35, 12, 12, $dc);
  if ($i > 0) imagecolordeallocate($canvas, $dc);
}

// ─── Save poster ──────────────────────────────────────────────────────────

$upload_dir = __DIR__ . '/../../uploads/courses';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$filename  = 'poster-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.png';
$file_path = $upload_dir . '/' . $filename;

ob_start();
imagepng($canvas, null, 7);
$png_data = ob_get_clean();
imagedestroy($canvas);

if (!$png_data || file_put_contents($file_path, $png_data) === false) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Gagal menyimpan poster']);
  exit;
}

echo json_encode([
  'success'            => true,
  'thumbnail'          => '/uploads/courses/' . $filename,
  'image_prompt'       => $image_prompt,
  'image_generated_at' => date('Y-m-d H:i:s')
]);
