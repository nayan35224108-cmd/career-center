<?php
require_once __DIR__ . '/../includes/settings.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'news';
$pageTitle = 'Berita';

// Get published news
$stmt = $pdo->query("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC");
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="inner-hero" style="min-height:40dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Berita</span>
      <h1>Berita Terbaru</h1>
      <p class="sub">Informasi dan berita seputar Unisbank Career Centre</p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <?php if (empty($newsList)): ?>
      <div class="empty-state">
        <p>Belum ada berita yang dipublikasi.</p>
      </div>
    <?php else: ?>
      <div class="news-grid">
        <?php foreach ($newsList as $news): ?>
          <a href="/news/<?= e($news['slug']) ?>" class="news-card">
            <?php if ($news['image']): ?>
              <div class="news-image">
                <img src="/uploads/news/<?= e($news['image']) ?>" alt="<?= e($news['title']) ?>">
              </div>
            <?php else: ?>
              <div class="news-image news-image-placeholder">
                <span>📰</span>
              </div>
            <?php endif; ?>
            <div class="news-content">
              <div class="news-date"><?= date('d M Y', strtotime($news['created_at'])) ?></div>
              <h3><?= e($news['title']) ?></h3>
              <?php if ($news['excerpt']): ?>
                <p><?= e($news['excerpt']) ?></p>
              <?php endif; ?>
              <span class="news-read-more">Baca selengkapnya →</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.news-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s, box-shadow 0.2s;
  display: block;
}

.news-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.news-image {
  height: 180px;
  overflow: hidden;
}

.news-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.news-card:hover .news-image img {
  transform: scale(1.05);
}

.news-image-placeholder {
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  display: flex;
  align-items: center;
  justify-content: center;
}

.news-image-placeholder span {
  font-size: 4rem;
}

.news-content {
  padding: 20px;
}

.news-date {
  font-size: 0.85rem;
  color: #7a0f18;
  font-weight: 500;
  margin-bottom: 8px;
}

.news-content h3 {
  font-size: 1.15rem;
  margin: 0 0 10px 0;
  color: #1a1a2e;
  line-height: 1.4;
}

.news-content p {
  color: #666;
  font-size: 0.95rem;
  margin: 0 0 15px 0;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.news-read-more {
  color: #7a0f18;
  font-weight: 500;
  font-size: 0.9rem;
}

.news-card:hover .news-read-more {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .news-grid {
    grid-template-columns: 1fr;
  }
}
</style>