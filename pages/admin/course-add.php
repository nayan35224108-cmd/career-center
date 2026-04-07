<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

$admin = $_SESSION['admin_user'];
$error = '';
$success = '';

$stmt = $pdo->query("SELECT name FROM master_categories WHERE is_active = 1 ORDER BY name ASC");
$category_options = $stmt->fetchAll();

$stmt = $pdo->query("SELECT name FROM master_pengajar WHERE is_active = 1 ORDER BY name ASC");
$pengajar_options = $stmt->fetchAll();

// Fetch available Ollama models
function get_ollama_models(): array {
  $ch = curl_init('http://localhost:11434/api/tags');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  $response = curl_exec($ch);
  curl_close($ch);
  if (!$response) return [];
  $data = json_decode($response, true);
  return array_column($data['models'] ?? [], 'name');
}
$ollama_models = get_ollama_models();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $title = sanitize($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $category = sanitize($_POST['category'] ?? '');
    $jadwal = sanitize($_POST['jadwal'] ?? '');
    $kapasitas = (int) ($_POST['kapasitas'] ?? 20);
    $pengajar = sanitize($_POST['pengajar'] ?? '');
    $lokasi = sanitize($_POST['lokasi'] ?? '');
    $learning_outcomes = $_POST['learning_outcomes'] ?? '';
    $thumbnail = sanitize($_POST['thumbnail'] ?? '');
    $image_prompt = sanitize($_POST['image_prompt'] ?? '');
    $image_generated_at = sanitize($_POST['image_generated_at'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi learning_outcomes: harus JSON array yang valid jika diisi
    if ($learning_outcomes !== '' && json_decode($learning_outcomes) === null) {
      $learning_outcomes = '';
    }

    if (empty($title)) {
      $error = 'Judul course wajib diisi';
    } else {
      try {
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, learning_outcomes, category, jadwal, kapasitas, pengajar, lokasi, thumbnail, image_prompt, image_generated_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $learning_outcomes ?: null, $category, $jadwal ?: null, $kapasitas, $pengajar, $lokasi, $thumbnail ?: null, $image_prompt ?: null, $image_generated_at ?: null, $is_active]);
        $success = 'Course berhasil ditambahkan';
      } catch (Exception $e) {
        $error = 'Gagal menambahkan course: ' . $e->getMessage();
      }
    }
  }
}

$pageTitle = 'Tambah Course';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | UCC Admin</title>
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
  <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 250px; background: #1a1a2e; color: #fff; padding: 20px; flex-shrink: 0; }
    .admin-sidebar h2 { color: #fff; margin-bottom: 30px; font-size: 1.2rem; }
    .admin-sidebar a { display: block; color: #aaa; padding: 12px 15px; text-decoration: none; border-radius: 6px; margin-bottom: 5px; }
    .admin-sidebar a:hover, .admin-sidebar a.active { background: #16213e; color: #fff; }
    .admin-main { flex: 1; padding: 30px; background: #f5f5f5; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .admin-header h1 { margin: 0; font-size: 1.5rem; }
    .form-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; max-width: 700px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; font-family: inherit; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #7a0f18; box-shadow: 0 0 0 3px rgba(122,15,24,0.1); }
    .form-group textarea { min-height: 120px; resize: vertical; }
    .inline-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 10px; }
    .btn-ai { background: #0f766e; color: #fff; border: none; border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; cursor: pointer; }
    .btn-ai:hover { background: #0b5f59; }
    .form-group.checkbox { display: flex; align-items: center; gap: 10px; }
    .form-group.checkbox input { width: auto; }
    .form-group.checkbox label { margin: 0; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .btn-primary { background: #7a0f18; color: #fff; }
    .btn-primary:hover { background: #5c0a11; }
    .btn-secondary { background: #6b7280; color: #fff; }
    .btn-secondary:hover { background: #4b5563; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <h2>UCC Admin</h2>
      <a href="/admin">Dashboard</a>
      <a href="/admin/courses" class="active">Kelola Courses</a>
      <a href="/admin/jobs">Lowongan Kerja</a>
      <a href="/admin/news">Berita</a>
      <a href="/admin/master-categories">Master Kategori</a>
      <a href="/admin/master-pengajar">Master Pengajar</a>
      <a href="/admin/registrations">Pendaftaran</a>
      <a href="/admin/users">Users</a>
      <a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a>
      <a href="/admin/logout" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </aside>
    
    <main class="admin-main">
      <div class="admin-header">
        <h1>Tambah Course</h1>
        <a href="/admin/courses" class="btn btn-secondary">← Kembali</a>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>
      
      <div class="form-card">
        <form method="POST">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          
          <div class="form-group">
            <div class="inline-actions">
              <label for="title">Judul Course *</label>
              <?php if (!empty($ollama_models)): ?>
                <button type="button" class="btn-ai" id="improveTitleBtn">Perbaiki Judul</button>
              <?php endif; ?>
            </div>
            <input type="text" id="title" name="title" required placeholder="Contoh: Workshop Job Interview">
          </div>
          
          <div class="form-group">
            <div class="inline-actions">
              <label for="description">Deskripsi</label>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <?php if (!empty($ollama_models)): ?>
                  <select id="ollamaModel" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:0.85rem;background:#fff;max-width:220px;">
                    <?php foreach ($ollama_models as $m): ?>
                      <option value="<?= e($m) ?>"><?= e($m) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="button" class="btn-ai" id="generateDescriptionBtn">Generate Deskripsi</button>
                <?php else: ?>
                  <span style="font-size:0.8rem;color:#9ca3af;">Ollama tidak tersedia</span>
                <?php endif; ?>
              </div>
            </div>
            <textarea id="description" name="description" placeholder="Deskripsi lengkap course..."><?= e($_POST['description'] ?? '') ?></textarea>
          </div>
          
          <div class="form-group">
            <div class="inline-actions">
              <label>Skill yang Akan Dikuasai</label>
              <?php if (!empty($ollama_models)): ?>
                <button type="button" class="btn-ai" id="generateSkillsBtn">Generate Skill</button>
              <?php endif; ?>
            </div>
            <input type="hidden" id="learning_outcomes" name="learning_outcomes" value="">
            <div id="skillsPreview" style="display:none; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-top:4px;">
              <ul id="skillsList" style="margin:0; padding-left:20px; color:#374151; font-size:0.9rem; line-height:1.8;"></ul>
              <button type="button" id="clearSkillsBtn" style="margin-top:10px; font-size:0.78rem; color:#ef4444; background:none; border:none; cursor:pointer; padding:0;">&#10005; Hapus</button>
            </div>
            <p id="skillsEmpty" style="font-size:0.82rem; color:#9ca3af; margin:6px 0 0;">Belum ada skill. Isi judul lalu klik Generate Skill.</p>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="category">Kategori</label>
              <select id="category" name="category">
                <option value="">Pilih kategori</option>
                <?php foreach ($category_options as $opt): ?>
                  <option value="<?= e($opt['name']) ?>" <?= (($_POST['category'] ?? '') === $opt['name']) ? 'selected' : '' ?>>
                    <?= e($opt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="jadwal">Jadwal</label>
              <input type="datetime-local" id="jadwal" name="jadwal">
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="kapasitas">Kapasitas</label>
              <input type="number" id="kapasitas" name="kapasitas" value="20" min="1">
            </div>
            
            <div class="form-group">
              <label for="pengajar">Pengajar</label>
              <select id="pengajar" name="pengajar">
                <option value="">Pilih pengajar</option>
                <?php foreach ($pengajar_options as $opt): ?>
                  <option value="<?= e($opt['name']) ?>" <?= (($_POST['pengajar'] ?? '') === $opt['name']) ? 'selected' : '' ?>>
                    <?= e($opt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="lokasi">Lokasi</label>
            <input type="text" id="lokasi" name="lokasi" placeholder="Contoh: Room 301 atau Online">
          </div>

          <div class="form-group">
            <div class="inline-actions">
              <label for="thumbnailPreview">Thumbnail Course (1:1)</label>
              <button type="button" class="btn-ai" id="generateImageBtn">Generate Gambar AI</button>
            </div>
            <input type="hidden" id="thumbnail" name="thumbnail" value="<?= e($_POST['thumbnail'] ?? '') ?>">
            <input type="hidden" id="image_prompt" name="image_prompt" value="<?= e($_POST['image_prompt'] ?? '') ?>">
            <input type="hidden" id="image_generated_at" name="image_generated_at" value="<?= e($_POST['image_generated_at'] ?? '') ?>">
            <div id="thumbnailPreviewWrap" style="margin-top:10px; <?= empty($_POST['thumbnail']) ? 'display:none;' : '' ?>">
              <img id="thumbnailPreview" src="<?= e($_POST['thumbnail'] ?? '') ?>" alt="Preview thumbnail" style="width:220px;aspect-ratio:1/1;object-fit:cover;border-radius:12px;border:1px solid #d1d5db;">
            </div>
          </div>
          
          <div class="form-group checkbox">
            <input type="checkbox" id="is_active" name="is_active" value="1" checked>
            <label for="is_active">Course aktif</label>
          </div>
          
          <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary">Simpan Course</button>
            <a href="/admin/courses" class="btn btn-secondary">Batal</a>
          </div>
        </form>
      </div>
    </main>
  </div>
<script>
  const titleInput = document.getElementById('title');
  const descInput = document.getElementById('description');
  const generateBtn = document.getElementById('generateDescriptionBtn');
  const improveTitleBtn = document.getElementById('improveTitleBtn');
  const generateSkillsBtn = document.getElementById('generateSkillsBtn');
  const generateImageBtn = document.getElementById('generateImageBtn');
  const learningOutcomesInput = document.getElementById('learning_outcomes');
  const skillsPreview = document.getElementById('skillsPreview');
  const skillsList = document.getElementById('skillsList');
  const skillsEmpty = document.getElementById('skillsEmpty');
  const clearSkillsBtn = document.getElementById('clearSkillsBtn');
  const thumbnailInput = document.getElementById('thumbnail');
  const imagePromptInput = document.getElementById('image_prompt');
  const imageGeneratedAtInput = document.getElementById('image_generated_at');
  const thumbnailPreview = document.getElementById('thumbnailPreview');
  const thumbnailPreviewWrap = document.getElementById('thumbnailPreviewWrap');

  function renderSkills(skills) {
    if (!skills || skills.length === 0) {
      skillsPreview.style.display = 'none';
      skillsEmpty.style.display = 'block';
      learningOutcomesInput.value = '';
      return;
    }
    skillsList.innerHTML = skills.map(s => `<li>${s}</li>`).join('');
    learningOutcomesInput.value = JSON.stringify(skills);
    skillsPreview.style.display = 'block';
    skillsEmpty.style.display = 'none';
  }

  if (clearSkillsBtn) {
    clearSkillsBtn.addEventListener('click', () => renderSkills([]));
  }

  if (generateSkillsBtn) {
    generateSkillsBtn.addEventListener('click', async function () {
      const title = (titleInput.value || '').trim();
      if (!title) {
        alert('Isi Judul Course terlebih dahulu.');
        titleInput.focus();
        return;
      }

      const modelSelect = document.getElementById('ollamaModel');
      const model = modelSelect ? modelSelect.value : '';
      if (!model) { alert('Pilih model terlebih dahulu.'); return; }

      const csrf = document.querySelector('input[name="csrf"]').value;

      generateSkillsBtn.disabled = true;
      generateSkillsBtn.textContent = 'Generating...';

      try {
        const body = new URLSearchParams();
        body.append('title', title);
        body.append('description', descInput.value || '');
        body.append('model', model);
        body.append('csrf', csrf);

        const response = await fetch('/admin/generate-skills', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: body.toString()
        });

        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.error || 'Gagal generate skill');

        renderSkills(data.skills);
      } catch (err) {
        alert(err.message || 'Terjadi kesalahan saat generate skill.');
      } finally {
        generateSkillsBtn.disabled = false;
        generateSkillsBtn.textContent = 'Generate Skill';
      }
    });
  }

  if (improveTitleBtn) {
    improveTitleBtn.addEventListener('click', async function () {
      const title = (titleInput.value || '').trim();
      if (!title) {
        alert('Isi Judul Course terlebih dahulu.');
        titleInput.focus();
        return;
      }

      const modelSelect = document.getElementById('ollamaModel');
      const model = modelSelect ? modelSelect.value : '';
      if (!model) { alert('Pilih model terlebih dahulu.'); return; }

      const csrf = document.querySelector('input[name="csrf"]').value;

      improveTitleBtn.disabled = true;
      improveTitleBtn.textContent = 'Memperbaiki...';

      try {
        const body = new URLSearchParams();
        body.append('title', title);
        body.append('model', model);
        body.append('csrf', csrf);

        const response = await fetch('/admin/improve-title', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: body.toString()
        });

        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.error || 'Gagal memperbaiki judul');

        titleInput.value = data.title || title;
      } catch (err) {
        alert(err.message || 'Terjadi kesalahan saat memperbaiki judul.');
      } finally {
        improveTitleBtn.disabled = false;
        improveTitleBtn.textContent = 'Perbaiki Judul';
      }
    });
  }

  if (generateBtn) {
    generateBtn.addEventListener('click', async function () {
      const title = (titleInput.value || '').trim();
      if (!title) {
        alert('Isi Judul Course terlebih dahulu.');
        titleInput.focus();
        return;
      }

      const modelSelect = document.getElementById('ollamaModel');
      const model = modelSelect ? modelSelect.value : '';
      if (!model) {
        alert('Pilih model terlebih dahulu.');
        return;
      }

      const csrfInput = document.querySelector('input[name="csrf"]');
      const csrf = csrfInput ? csrfInput.value : '';

      generateBtn.disabled = true;
      generateBtn.textContent = 'Generating...';

      try {
        const body = new URLSearchParams();
        body.append('title', title);
        body.append('model', model);
        body.append('csrf', csrf);

        const response = await fetch('/admin/generate-description', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: body.toString()
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
          throw new Error(data.error || 'Gagal generate deskripsi');
        }

        descInput.value = data.description || '';
      } catch (err) {
        alert(err.message || 'Terjadi kesalahan saat generate deskripsi.');
      } finally {
        generateBtn.disabled = false;
        generateBtn.textContent = 'Generate Deskripsi';
      }
    });
  }

  generateImageBtn.addEventListener('click', async function () {
    const title = (titleInput.value || '').trim();
    if (!title) {
      alert('Isi judul terlebih dahulu untuk generate poster.');
      return;
    }

    const csrfInput = document.querySelector('input[name="csrf"]');
    const csrf = csrfInput ? csrfInput.value : '';

    generateImageBtn.disabled = true;
    generateImageBtn.textContent = 'Generating...';

    try {
      const body = new URLSearchParams();
      body.append('title', title);
      body.append('description', descInput.value || '');
      body.append('skills', learningOutcomesInput ? (learningOutcomesInput.value || '[]') : '[]');
      body.append('jadwal', document.getElementById('jadwal')?.value || '');
      body.append('lokasi', document.getElementById('lokasi')?.value || '');
      body.append('pengajar', document.getElementById('pengajar')?.value || '');
      body.append('csrf', csrf);

      const response = await fetch('/admin/generate-course-image', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: body.toString()
      });

      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.error || 'Gagal generate gambar');
      }

      thumbnailInput.value = data.thumbnail || '';
      imagePromptInput.value = data.image_prompt || '';
      imageGeneratedAtInput.value = data.image_generated_at || '';

      if (data.thumbnail) {
        thumbnailPreview.src = data.thumbnail;
        thumbnailPreviewWrap.style.display = 'block';
      }
    } catch (err) {
      alert(err.message || 'Terjadi kesalahan saat generate gambar.');
    } finally {
      generateImageBtn.disabled = false;
      generateImageBtn.textContent = 'Generate Gambar AI';
    }
  });
</script>
</body>
</html>
