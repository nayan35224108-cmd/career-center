-- SQL untuk menambahkan kolom username ke tabel users
-- Jalankan ini di phpMyAdmin atau mysql CLI

-- Tambahkan kolom username jika belum ada
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(100) DEFAULT NULL AFTER email;

-- Tambahkan index untuk pencarian lebih cepat
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_username (username);

-- Tambahkan kolom last_login jika belum ada
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL AFTER phone_whatsapp;