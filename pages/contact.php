<?php
require_once __DIR__ . '/../includes/settings.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'contact';
$pageTitle = 'Hubungi Kami';

// Get contact settings
$contact_hero_title = get_setting($pdo, 'contact_hero_title', 'Kami Siap Membantu');
$contact_hero_subtitle = get_setting($pdo, 'contact_hero_subtitle', 'Ada pertanyaan? Silakan hubungi kami melalui formulir di bawah ini.');
$contact_email = get_setting($pdo, 'contact_email', 'admin@ucc.pjdigital.top');
$contact_whatsapp = get_setting($pdo, 'contact_whatsapp', '+62 812 3456 7890');
$contact_location = get_setting($pdo, 'contact_location', 'Gedung Kampus Unisbank<br>Ruang UCC');
$contact_operational_hours = get_setting($pdo, 'contact_operational_hours', "Senin - Jumat: 08.00 - 17.00\nSabtu - Minggu: Tutup");

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
  $name = sanitize($_POST['name'] ?? '');
  $email = sanitize($_POST['email'] ?? '');
  $phone = sanitize($_POST['phone'] ?? '');
  $subject = sanitize($_POST['subject'] ?? '');
  $message_text = sanitize($_POST['message'] ?? '');

  if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
    $message = 'Mohon lengkapi semua field yang wajib.';
    $messageType = 'error';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = 'Format email tidak valid.';
    $messageType = 'error';
  } else {
    $message = 'Terima kasih telah menghubungi kami. Kami akan membalas segera.';
    $messageType = 'success';
  }
}
?>
<div class="inner-hero" style="min-height:40dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Hubungi Kami</span>
      <h1><?= e($contact_hero_title) ?></h1>
      <p class="sub"><?= e($contact_hero_subtitle) ?></p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2>Informasi Kontak</h2>
    <div class="service-grid">
      <div class="service-card">
        <h3>📧</h3>
        <h4>Email</h4>
        <p><?= e($contact_email) ?></p>
      </div>
      <div class="service-card">
        <h3>📱</h3>
        <h4>WhatsApp</h4>
        <p><?= e($contact_whatsapp) ?></p>
      </div>
      <div class="service-card">
        <h3>📍</h3>
        <h4>Lokasi</h4>
        <p><?= $contact_location ?></p>
      </div>
    </div>
  </div>
</div>

<div class="inner-sec sec-soft-cyan">
  <div class="section-wrap">
    <h2>Kirim Pesan</h2>
    
    <?php if ($message): ?>
      <div class="alert alert-<?= e($messageType) ?>" style="padding:15px;border-radius:8px;margin-bottom:20px;<?= $messageType === 'success' ? 'background:#d4edda;border:1px solid #28a745' : 'background:#f8d7da;border:1px solid #dc3545' ?>">
        <?= e($message) ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" style="max-width:600px">
      <div style="margin-bottom:15px">
        <label style="display:block;margin-bottom:5px;font-weight:600">Nama *</label>
        <input type="text" name="name" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px">
      </div>
      
      <div style="margin-bottom:15px">
        <label style="display:block;margin-bottom:5px;font-weight:600">Email *</label>
        <input type="email" name="email" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px">
      </div>
      
      <div style="margin-bottom:15px">
        <label style="display:block;margin-bottom:5px;font-weight:600">Nomor Telepon</label>
        <input type="tel" name="phone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px">
      </div>
      
      <div style="margin-bottom:15px">
        <label style="display:block;margin-bottom:5px;font-weight:600">Subjek *</label>
        <input type="text" name="subject" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px">
      </div>
      
      <div style="margin-bottom:15px">
        <label style="display:block;margin-bottom:5px;font-weight:600">Pesan *</label>
        <textarea name="message" rows="6" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px"></textarea>
      </div>
      
      <button type="submit" name="submit_contact" class="cta-btn">Kirim Pesan</button>
    </form>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <h2>Jam Operasional</h2>
    <ul>
      <?php foreach (explode("\n", $contact_operational_hours) as $line): ?>
        <?php if (trim($line)): ?>
          <li><?= e(trim($line)) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
