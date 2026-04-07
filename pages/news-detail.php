<?php
require_once __DIR__ . '/../includes/settings.php';

$layoutMode = 'one-col';
$bodyClass = 'one-col-layout inner-page';
$currentPage = 'news';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

// Get news
$stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
$stmt->execute([$slug]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
  include __DIR__ . '/../templates/404.php';
  exit;
}

$pageTitle = $news['title'];
$metaDescription = $news['excerpt'] ?: seo_description($news['content']);
?>
<div class="inner-hero" style="min-height:40dvh">
  <div class="hero-wrap">
    <div class="hero-copy">
      <span class="inner-badge">Berita</span>
      <h1><?= e($news['title']) ?></h1>
      <p class="sub"><?= date('d F Y', strtotime($news['created_at'])) ?></p>
    </div>
  </div>
</div>

<div class="inner-sec sec-white">
  <div class="section-wrap">
    <article class="news-article">
      <?php if ($news['image']): ?>
        <div class="news-featured-image">
          <img src="/uploads/news/<?= e($news['image']) ?>" alt="<?= e($news['title']) ?>">
        </div>
      <?php endif; ?>

      <?php if ($news['excerpt']): ?>
        <div class="news-excerpt">
          <?= e($news['excerpt']) ?>
        </div>
      <?php endif; ?>

      <div class="news-body">
        <?= optimize_content_html($news['content']) ?>
      </div>

      <div class="news-footer">
        <a href="/news" class="back-link">← Kembali ke Berita</a>
      </div>
    </article>
  </div>
</div>

<style>
.news-article {
  max-width: 800px;
  margin: 0 auto;
}

.news-featured-image {
  margin-bottom: 30px;
  border-radius: 12px;
  overflow: hidden;
}

.news-featured-image img {
  width: 100%;
  height: auto;
  display: block;
}

.news-excerpt {
  font-size: 1.2rem;
  color: #666;
  line-height: 1.8;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 30px;
}

.news-body {
  font-size: 1.05rem;
  line-height: 1.8;
  color: #333;
}

.news-body h2 {
  font-size: 1.5rem;
  margin-top: 30px;
  margin-bottom: 15px;
  color: #1a1a2e;
}

.news-body h3 {
  font-size: 1.25rem;
  margin-top: 25px;
  margin-bottom: 12px;
  color: #1a1a2e;
}

.news-body p {
  margin-bottom: 20px;
}

.news-body ul, .news-body ol {
  margin-bottom: 20px;
  padding-left: 30px;
}

.news-body li {
  margin-bottom: 8px;
}

.news-body img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 20px 0;
}

.news-body a {
  color: #7a0f18;
  text-decoration: underline;
}

.news-body blockquote {
  border-left: 4px solid #7a0f18;
  padding-left: 20px;
  margin: 20px 0;
  color: #666;
  font-style: italic;
}

.news-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.back-link {
  display: inline-flex;
  align-items: center;
  color: #7a0f18;
  text-decoration: none;
  font-weight: 500;
}

.back-link:hover {
  text-decoration: underline;
}
</style>