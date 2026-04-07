# Unisbank Career Centre (UCC)

Platform pusat karir Unisbank yang memungkinkan siswa menjelajahi kursus pelatihan, mendaftar untuk program, dan melihat penawaran pekerjaan.

## Fitur Utama

- **Kelola Kursus**: Admin dapat menambah, mengedit, dan menghapus kursus pelatihan
- **Daftar Pekerjaan**: Kelola dan tampilkan lowongan kerja
- **Registrasi Siswa**: Siswa dapat mendaftar untuk kursus pelatihan
- **Manajemen Pengguna**: Kelola akun siswa dan admin
- **Pengaturan Situs**: Konfigurasi umum, tentang, kontak, dan footer
- **Generasi Konten AI**: Buat deskripsi kursus dan gambar menggunakan API Gemini
- **Media Manager**: Kelola upload file dan media

## Tech Stack

- **Backend**: Plain PHP (tanpa framework)
- **Database**: MySQL dengan PDO
- **Authentication**: Session-based
- **Email**: PHPMailer
- **Frontend**: Vanilla JavaScript, CSS
- **AI Integration**: Google Gemini API

## Instalasi

### Persyaratan
- PHP 7.4+
- MySQL 5.7+
- Composer
- Web Server (Apache/Nginx)

### Setup

1. Clone repository
```bash
git clone https://github.com/nayan35224108-cmd/career-center.git
cd career-center
```

2. Install dependencies
```bash
composer install
```

3. Buat database MySQL
```sql
CREATE DATABASE ucc_db;
```

4. Konfigurasi environment
- Copy `config.php` dan sesuaikan dengan database Anda:
  - `DB_HOST`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASS`
  - SMTP settings untuk email

5. Jalankan aplikasi
```bash
php -S localhost:8000
```

Akses di `http://localhost:8000`

## Struktur Direktori

```
/
├── config.php              # Konfigurasi database dan konstanta
├── index.php               # Router utama
├── includes/               # Helper functions
│   ├── auth.php           # Fungsi autentikasi
│   ├── functions.php      # Fungsi utility
│   ├── settings.php       # Manajemen setting situs
│   └── unisbank-api.php   # Integrasi API Unisbank
├── pages/                  # Halaman frontend
│   ├── course.php
│   ├── jobs.php
│   ├── login.php
│   └── admin/             # Halaman admin
├── templates/              # Template reusable
│   ├── header.php
│   └── footer.php
├── assets/                # CSS dan JavaScript
│   ├── css/
│   └── js/
├── uploads/              # File upload
├── sql/                  # Database scripts
└── vendor/              # Dependencies Composer
```

## Fitur Admin

Akses dashboard admin di `/admin` dengan credential admin yang sudah terdaftar di database.

### Menu Admin
- **Dashboard**: Ringkasan statistik
- **Kursus**: CRUD kursus pelatihan
- **Pekerjaan**: CRUD lowongan kerja
- **Berita**: CRUD artikel berita
- **Registrasi**: Kelola registrasi siswa
- **Pengguna**: Kelola akun pengguna
- **Pengaturan**: Konfigurasi situs, footer, konten
- **Media**: Kelola file upload

## Konvensi Kode

### PHP
- 2-space indentation
- snake_case untuk functions dan variables
- Prepared statements untuk DB queries
- Escape output dengan `e()` function
- CSRF protection pada semua form

### Database
- Selalu gunakan prepared statements
- Jangan concatenate user input ke SQL
- Gunakan PDO dengan `FETCH_ASSOC`

### Keamanan
- Password hashing dengan `password_hash()`
- Input sanitization dengan `sanitize()`
- Output escaping dengan `e()`
- CSRF tokens pada forms

## Email Configuration

PHPMailer sudah dikonfigurasi di `config.php`. Atur SMTP settings:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'no-reply@unisbank.ac.id');
```

## AI Integration

### Gemini API
Untuk menggunakan fitur generasi konten AI, set environment variables:

```bash
export GEMINI_API_KEY=your_api_key
export GEMINI_MODEL=gemini-1.5-flash
export GEMINI_IMAGE_MODEL=gemini-2.0-flash-exp
```

Fitur yang tersedia:
- Generate deskripsi kursus
- Generate gambar kursus
- Improve judul kursus
- Generate skill dan learning outcomes

## Testing

### Syntax Check
```bash
php -l config.php
php -l pages/*.php
# Check semua PHP files
for f in $(find . -name "*.php"); do php -l "$f"; done
```

## Deployment

### Production Checklist
- [ ] Update `config.php` dengan production database credentials
- [ ] Set error reporting: `error_reporting(0);`
- [ ] Enable HTTPS
- [ ] Set proper file permissions (private app files)
- [ ] Configure SMTP untuk production
- [ ] Set `SESSION_SECURE` untuk HTTPS
- [ ] Update `BASE_URL` di config
- [ ] Backup database sebelum deploy

## Troubleshooting

### Database Connection Error
- Pastikan MySQL server running
- Cek credentials di `config.php`
- Cek database sudah dibuat

### Email Not Sending
- Verifikasi SMTP credentials
- Cek PHP ini settings untuk `allow_url_fopen`
- Check firewall/ISP port blocking

### Upload Issues
- Pastikan folder `uploads/` writable
- Check PHP `upload_max_filesize` setting
- Verify file permissions

## Support

Untuk bantuan dan issue, hubungi tim development atau buat issue di GitHub.

## License

Proprietary - Unisbank

## Changelog

### v1.0.0 - Initial Release
- Dashboard siswa
- CRUD kursus
- CRUD pekerjaan
- CRUD berita
- Registrasi siswa
- Admin panel
- AI content generation
