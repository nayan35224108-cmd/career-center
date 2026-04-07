<?php
require_once __DIR__ . '/../../config.php';


if (empty($_SESSION['admin_user'])) {
  header('Location: /admin/login');
  exit;
}

function upload_job_poster_edit(array $file): array {
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return ['success' => true, 'path' => null];
  }
  if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
    return ['success' => false, 'error' => 'Upload poster gagal'];
  }
  if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
    return ['success' => false, 'error' => 'Ukuran poster maksimal 3MB'];
  }
  $tmp = $file['tmp_name'];
  $mime = mime_content_type($tmp);
  $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/jpeg' ? 'jpg' : '');
  if ($ext === '') {
    return ['success' => false, 'error' => 'Format poster harus JPG/PNG'];
  }
  $dir = __DIR__ . '/../../uploads/jobs';
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
  }
  $name = 'job-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . '/' . $name;
  if (!move_uploaded_file($tmp, $dest)) {
    return ['success' => false, 'error' => 'Gagal menyimpan poster'];
  }
  return ['success' => true, 'path' => '/uploads/jobs/' . $name];
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
$stmt->execute([$id]);
$job = $stmt->fetch();
if (!$job) {
  header('Location: /admin/jobs');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Token tidak valid';
  } else {
    $title = sanitize($_POST['title'] ?? '');
    $company_name = sanitize($_POST['company_name'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $employment_type = sanitize($_POST['employment_type'] ?? 'full_time');
    $description = trim((string) ($_POST['description'] ?? ''));
    $requirements = trim((string) ($_POST['requirements'] ?? ''));
    $application_url = sanitize($_POST['application_url'] ?? '');
    $deadline_at = sanitize($_POST['deadline_at'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $slug = slugify($title);

    if ($title === '' || $company_name === '' || $description === '') {
      $error = 'Judul, perusahaan, dan deskripsi wajib diisi';
    } else {
      $upload = upload_job_poster_edit($_FILES['poster_image'] ?? []);
      if (!$upload['success']) {
        $error = $upload['error'];
      } else {
        $poster = $upload['path'] ?: ($job['poster_image'] ?? null);
        $published_at = $is_published ? ($job['published_at'] ?: date('Y-m-d H:i:s')) : null;
        $stmt = $pdo->prepare("UPDATE jobs SET title = ?, slug = ?, company_name = ?, location = ?, employment_type = ?, description = ?, requirements = ?, poster_image = ?, application_url = ?, deadline_at = ?, is_published = ?, published_at = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $company_name, $location ?: null, $employment_type, $description, $requirements ?: null, $poster, $application_url ?: null, $deadline_at ?: null, $is_published, $published_at, $id]);
        header('Location: /admin/jobs');
        exit;
      }
    }
  }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Edit Lowongan</title><link rel="stylesheet" href="/assets/css/theme.css"><link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet"><style>.admin-layout{display:flex;min-height:100vh}.admin-sidebar{width:250px;background:#1a1a2e;color:#fff;padding:20px}.admin-sidebar a{display:block;color:#aaa;padding:12px 15px;text-decoration:none;border-radius:6px;margin-bottom:5px}.admin-sidebar a.active,.admin-sidebar a:hover{background:#16213e;color:#fff}.admin-main{flex:1;padding:30px;background:#f5f5f5}.card{background:#fff;border-radius:10px;padding:20px;max-width:860px}.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}label{display:block;margin-bottom:6px;font-weight:600}input,select{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px}#editor-description,#editor-requirements{height:180px}.btn{display:inline-block;padding:10px 14px;border-radius:8px;border:0;cursor:pointer}.btn-primary{background:#7a0f18;color:#fff}.btn-muted{background:#e5e7eb;color:#111827;text-decoration:none}</style></head><body>
<div class="admin-layout"><aside class="admin-sidebar"><h2>UCC Admin</h2><a href="/admin">Dashboard</a><a href="/admin/courses">Kelola Courses</a><a href="/admin/jobs" class="active">Lowongan Kerja</a><a href="/admin/master-categories">Master Kategori</a><a href="/admin/master-pengajar">Master Pengajar</a><a href="/admin/registrations">Pendaftaran</a><a href="/admin/users">Users</a><a href="/admin/media">Media AI</a>
      <a href="/admin/settings">Pengaturan</a><a href="/admin/logout" style="margin-top:30px;color:#ef4444">Logout</a></aside>
<main class="admin-main"><h1>Edit Lowongan</h1><?php if ($error): ?><div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;margin-bottom:10px;"><?= e($error) ?></div><?php endif; ?><div class="card"><form method="POST" enctype="multipart/form-data" id="jobForm"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="row"><div><label>Judul *</label><input type="text" name="title" value="<?= e($job['title']) ?>" required></div><div><label>Perusahaan *</label><input type="text" name="company_name" value="<?= e($job['company_name']) ?>" required></div></div><div class="row" style="margin-top:10px;"><div><label>Lokasi</label><input type="text" name="location" value="<?= e($job['location']) ?>"></div><div><label>Tipe Kerja</label><select name="employment_type"><option value="full_time" <?= $job['employment_type'] === 'full_time' ? 'selected' : '' ?>>Full Time</option><option value="part_time" <?= $job['employment_type'] === 'part_time' ? 'selected' : '' ?>>Part Time</option><option value="internship" <?= $job['employment_type'] === 'internship' ? 'selected' : '' ?>>Internship</option><option value="contract" <?= $job['employment_type'] === 'contract' ? 'selected' : '' ?>>Contract</option></select></div></div><div style="margin-top:10px;"><label>Deskripsi *</label><div id="editor-description"><?= $job['description'] ?></div><input type="hidden" name="description" id="description"></div><div style="margin-top:10px;"><label>Kualifikasi</label><div id="editor-requirements"><?= $job['requirements'] ?? '' ?></div><input type="hidden" name="requirements" id="requirements"></div><div class="row" style="margin-top:10px;"><div><label>Link Apply</label><input type="url" name="application_url" value="<?= e($job['application_url']) ?>" placeholder="https://..."></div><div><label>Deadline</label><input type="datetime-local" name="deadline_at" value="<?= !empty($job['deadline_at']) ? date('Y-m-d\TH:i', strtotime($job['deadline_at'])) : '' ?>"></div></div><div style="margin-top:10px;"><label>Poster Lowongan (JPG/PNG max 3MB)</label><input type="file" name="poster_image" accept="image/png,image/jpeg"><?php if (!empty($job['poster_image'])): ?><div style="margin-top:8px;"><img src="<?= e($job['poster_image']) ?>" alt="poster" style="width:120px;height:120px;object-fit:cover;border-radius:8px;"></div><?php endif; ?></div><div style="margin-top:10px;"><label><input type="checkbox" name="is_published" value="1" <?= (int)$job['is_published'] === 1 ? 'checked' : '' ?>> Publish</label></div><div style="margin-top:16px;display:flex;gap:10px;"><button type="submit" class="btn btn-primary">Update</button><a class="btn btn-muted" href="/admin/jobs">Batal</a></div></form></div></main></div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>var quillDesc=new Quill('#editor-description',{theme:'snow'});var quillReq=new Quill('#editor-requirements',{theme:'snow'});document.querySelector('form').addEventListener('submit',function(){document.getElementById('description').value=quillDesc.root.innerHTML;document.getElementById('requirements').value=quillReq.root.innerHTML;});</script>
</body></html>
