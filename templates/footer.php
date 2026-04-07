<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$column1 = get_footer_settings($pdo, 'column1');
$column2 = get_footer_settings($pdo, 'column2');
$column3 = get_footer_settings($pdo, 'column3');
$copyright = get_footer_settings($pdo, 'copyright');

$nav_links = json_decode($column2['nav_links'] ?? '[]', true) ?: [];
$external_links = json_decode($column2['external_links'] ?? '[]', true) ?: [];
$social_links = json_decode($column3['social_links'] ?? '[]', true) ?: [];
?>
<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Kolom 1: Info & Kontak -->
      <div class="footer-col">
        <div class="footer-logo">
          <img src="/uploads/logo.png" alt="UCC Logo" onerror="this.style.display='none'">
          <span><?= e($column1['logo_text'] ?? 'Unisbank Career Centre') ?></span>
        </div>
        <p class="footer-desc"><?= e($column1['description'] ?? '') ?></p>

        <div class="footer-contact">
          <h4><?= e($column1['location_title'] ?? '📍 Lokasi') ?></h4>
          <p><?= $column1['location_content'] ?? 'Kampus Kendeng<br>Jl. Kendeng V, Bendan Ngisor<br>Semarang, Jawa Tengah' ?></p>

          <h4><?= e($column1['contact_title'] ?? '📞 Kontak') ?></h4>
          <p><?= $column1['contact_content'] ?? '<a href="tel:0248414970">(024) 8414970</a><br><a href="tel:0248311668">(024) 8311668</a>' ?></p>

          <h4><?= e($column1['email_title'] ?? '✉️ Email') ?></h4>
          <p><?= $column1['email_content'] ?? '<a href="mailto:humas@mail.unisbank.ac.id">humas@mail.unisbank.ac.id</a>' ?></p>
        </div>
      </div>

      <!-- Kolom 2: Navigasi -->
      <div class="footer-col">
        <h4><?= e($column2['nav_title'] ?? '🔗 Navigasi') ?></h4>
        <ul class="footer-links">
          <?php foreach ($nav_links as $link): ?>
            <li><a href="<?= e($link['href'] ?? '/') ?>"><?= e($link['text'] ?? '') ?></a></li>
          <?php endforeach; ?>
        </ul>

        <h4><?= e($column2['links_title'] ?? '🔗 Link Terkait') ?></h4>
        <ul class="footer-links">
          <?php foreach ($external_links as $link): ?>
            <li><a href="<?= e($link['href'] ?? '#') ?>" target="_blank" rel="noopener"><?= e($link['text'] ?? '') ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Kolom 3: Social Media -->
      <div class="footer-col">
        <h4><?= e($column3['social_title'] ?? '📱 Social Media') ?></h4>
        <p><?= e($column3['social_description'] ?? 'Ikuti/update terbaru kami:') ?></p>

        <div class="footer-social">
          <?php foreach ($social_links as $social): ?>
            <a href="<?= e($social['url'] ?? '#') ?>" target="_blank" rel="noopener" class="social-link" title="<?= e($social['title'] ?? '') ?>">
              <?= get_social_icon($social['platform'] ?? '') ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- WhatsApp CTA -->
        <div class="footer-whatsapp">
          <a href="https://wa.me/<?= e($column3['whatsapp_number'] ?? '6282133332256') ?>" target="_blank" rel="noopener" class="whatsapp-btn">
            <?= get_social_icon('whatsapp') ?>
            <?= e($column3['whatsapp_text'] ?? 'Chat WhatsApp') ?>
          </a>
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <strong><?= e($copyright['copyright_text'] ?? 'Unisbank Career Centre') ?></strong>. All rights reserved.</p>
      <p class="footer-credit"><?= e($copyright['credit_text'] ?? 'Developed with ❤️ for Unisbank Students') ?></p>
    </div>
  </div>
</footer>

<style>
.site-footer {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  color: #fff;
  padding: 60px 0 20px;
  margin-top: 60px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.footer-logo img {
  width: 48px;
  height: 48px;
  object-fit: contain;
  background: #fff;
  border-radius: 8px;
  padding: 4px;
}

.footer-logo span {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
}

.footer-desc {
  color: #a0aec0;
  margin-bottom: 24px;
  line-height: 1.6;
}

.footer-contact h4,
.footer-col > h4 {
  color: #f6ad55;
  font-size: 0.95rem;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.footer-contact p {
  color: #cbd5e0;
  line-height: 1.7;
  margin-bottom: 16px;
}

.footer-contact a {
  color: #63b3ed;
  text-decoration: none;
  transition: color 0.2s;
}

.footer-contact a:hover {
  color: #f6ad55;
  text-decoration: underline;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
}

.footer-links li {
  margin-bottom: 10px;
}

.footer-links a {
  color: #cbd5e0;
  text-decoration: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.footer-links a:hover {
  color: #f6ad55;
  transform: translateX(4px);
}

.footer-social {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  background: rgba(255,255,255,0.1);
  border-radius: 50%;
  color: #fff;
  transition: all 0.3s;
}

.social-link:hover {
  background: #f6ad55;
  color: #1a1a2e;
  transform: translateY(-3px);
}

.whatsapp-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: #25D366;
  color: #fff;
  padding: 12px 24px;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
}

.whatsapp-btn:hover {
  background: #20BD5A;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
}

.footer-bottom {
  text-align: center;
  padding-top: 30px;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.footer-bottom p {
  color: #a0aec0;
  margin: 0;
}

.footer-bottom strong {
  color: #f6ad55;
}

.footer-credit {
  margin-top: 8px !important;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }

  .footer-col {
    text-align: center;
  }

  .footer-logo {
    justify-content: center;
  }

  .footer-social {
    justify-content: center;
  }

  .whatsapp-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>

<script src="/assets/js/main.js?v=<?= file_exists(__DIR__ . '/../assets/js/main.js') ? filemtime(__DIR__ . '/../assets/js/main.js') : time() ?>"></script>
</body>
</html>
