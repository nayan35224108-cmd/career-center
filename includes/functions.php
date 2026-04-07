<?php
function e($v) {
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function sanitize($v) {
  return trim((string) $v);
}

/**
 * Send email using PHPMailer with SMTP
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text version (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($to, $subject, $body, $altBody = '') {
  $mail = new PHPMailer\PHPMailer\PHPMailer(true);
  
  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    // Recipients
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($to);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    if ($altBody) {
      $mail->AltBody = $altBody;
    }
    
    $mail->send();
    
    return ['success' => true, 'message' => 'Email sent successfully'];
  } catch (Exception $e) {
    return ['success' => false, 'message' => "Email failed: {$mail->ErrorInfo}"];
  }
}

/**
 * Send email with template wrapper
 * 
 * @param string $to Email recipient
 * @param string $subject Email subject
 * @param string $content Main content (HTML)
 * @return array ['success' => bool, 'message' => string]
 */
function send_email_template($to, $subject, $content) {
  $html = '
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="UTF-8">
    <style>
      body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
      .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
      .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
      .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
  </head>
  <body>
    <div class="header">
      <h2 style="margin: 0;">' . e(SITE_TITLE) . '</h2>
    </div>
    <div class="content">
      ' . $content . '
    </div>
    <div class="footer">
      <p>© ' . date('Y') . ' ' . e(SITE_TITLE) . '. All rights reserved.</p>
    </div>
  </body>
  </html>';
  
  return send_email($to, $subject, $html, strip_tags($content));
}

function slugify($text) {
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
  $text = preg_replace('~[^-\w]+~', '', $text);
  $text = trim($text, '-');
  $text = preg_replace('~-+~', '-', $text);
  $text = strtolower($text);
  return $text ?: 'halaman';
}

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function csrf_check($token) {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $token);
}

function is_logged_in() {
  return !empty($_SESSION['admin_user']);
}

function seo_description(string $html, int $max = 160): string {
  $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
  if (mb_strlen($text) <= $max) {
    return $text;
  }
  return rtrim(mb_substr($text, 0, $max - 1)) . '…';
}

function optimize_content_html(string $html): string {
  // Add lazy/async attrs to images if missing
  $html = preg_replace('/<img\b(?![^>]*\bloading=)([^>]*)>/i', '<img loading="lazy" decoding="async" $1>', $html);
  return $html;
}

function redirect($url) {
  header('Location: ' . $url);
  exit;
}

function set_message($message, $type = 'success') {
  $_SESSION['message'] = $message;
  $_SESSION['message_type'] = $type;
}

function get_message() {
  if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $type = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message'], $_SESSION['message_type']);
    return ['text' => $message, 'type' => $type];
  }
  return null;
}

function get_footer_settings($pdo, $section = null) {
  $defaults = [
    'column1' => [
      'logo_text' => 'Unisbank Career Centre',
      'description' => 'Portal pelatihan karir & soft skill untuk mahasiswa Unisbank Semarang.',
      'location_title' => '📍 Lokasi',
      'location_content' => "Kampus Kendeng<br>Jl. Kendeng V, Bendan Ngisor<br>Semarang, Jawa Tengah",
      'contact_title' => '📞 Kontak',
      'contact_content' => "<a href='tel:0248414970'>(024) 8414970</a><br><a href='tel:0248311668'>(024) 8311668</a>",
      'email_title' => '✉️ Email',
      'email_content' => "<a href='mailto:humas@mail.unisbank.ac.id'>humas@mail.unisbank.ac.id</a>"
    ],
    'column2' => [
      'nav_title' => '🔗 Navigasi',
      'nav_links' => '[{"text":"🏠 Home","href":"/"},{"text":"ℹ️ About","href":"/about"},{"text":"📚 Course","href":"/course"},{"text":"📩 Contact","href":"/contact"}]',
      'links_title' => '🔗 Link Terkait',
      'external_links' => '[{"text":"🏛️ Unisbank Official","href":"https://www.unisbank.ac.id"},{"text":"🎓 PMB Unisbank","href":"https://pmb.unisbank.ac.id"},{"text":"📋 SIAKAD","href":"https://siakad.unisbank.ac.id"}]'
    ],
    'column3' => [
      'social_title' => '📱 Social Media',
      'social_description' => 'Ikuti/update terbaru kami:',
      'social_links' => '[{"platform":"instagram","url":"https://instagram.com/kuliahdiunisbank","title":"Instagram"},{"platform":"tiktok","url":"https://www.tiktok.com/@unisbanksemarang","title":"TikTok"},{"platform":"youtube","url":"https://www.youtube.com/@universitasstikubank","title":"YouTube"},{"platform":"x","url":"https://x.com/UnisbankSmg","title":"X/Twitter"},{"platform":"facebook","url":"https://facebook.com/UnisbankSemarang","title":"Facebook"}]',
      'whatsapp_title' => 'Chat WhatsApp',
      'whatsapp_number' => '6282133332256',
      'whatsapp_text' => 'Chat WhatsApp'
    ],
    'copyright' => [
      'copyright_text' => 'Unisbank Career Centre',
      'credit_text' => 'Developed with ❤️ for Unisbank Students'
    ]
  ];

  try {
    if ($section) {
      $stmt = $pdo->prepare("SELECT key_name, value FROM footer_settings WHERE section = ? AND is_active = 1 ORDER BY sort_order");
      $stmt->execute([$section]);
      $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
      return array_merge($defaults[$section] ?? [], $results);
    } else {
      $stmt = $pdo->query("SELECT section, key_name, value FROM footer_settings WHERE is_active = 1 ORDER BY section, sort_order");
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $settings = $defaults;
      foreach ($results as $row) {
        $settings[$row['section']][$row['key_name']] = $row['value'];
      }
      return $settings;
    }
  } catch (Throwable $e) {
    return $section ? ($defaults[$section] ?? []) : $defaults;
  }
}

function save_footer_setting($pdo, $section, $key, $value) {
  try {
    // Cek apakah record sudah ada
    $stmt = $pdo->prepare("SELECT id FROM footer_settings WHERE section = ? AND key_name = ?");
    $stmt->execute([$section, $key]);
    $exists = $stmt->fetch();
    
    if ($exists) {
      // Update existing
      $stmt = $pdo->prepare("UPDATE footer_settings SET value = ?, updated_at = NOW() WHERE section = ? AND key_name = ?");
      $stmt->execute([$value, $section, $key]);
    } else {
      // Insert new
      $stmt = $pdo->prepare("INSERT INTO footer_settings (section, key_name, value, is_active, sort_order) VALUES (?, ?, ?, 1, 0)");
      $stmt->execute([$section, $key, $value]);
    }
    return true;
  } catch (Throwable $e) {
    error_log('Footer save error: ' . $e->getMessage());
    return false;
  }
}

function get_social_icon($platform) {
  $icons = [
    'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
    'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>',
    'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>',
    'x' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/></svg>',
    'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/></svg>',
    'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
    'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
    'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>'
  ];
  return $icons[$platform] ?? '';
}
