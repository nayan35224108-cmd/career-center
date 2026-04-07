-- SQL untuk membuat tabel footer_settings
-- Jalankan ini di phpMyAdmin atau mysql CLI

CREATE TABLE IF NOT EXISTS footer_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(50) NOT NULL,
  key_name VARCHAR(100) NOT NULL,
  value TEXT,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_section_key (section, key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data default untuk Kolom 1 (Info & Kontak)
INSERT INTO footer_settings (section, key_name, value, sort_order) VALUES
('column1', 'logo_text', 'Unisbank Career Centre', 1),
('column1', 'description', 'Portal pelatihan karir & soft skill untuk mahasiswa Unisbank Semarang.', 2),
('column1', 'location_title', '📍 Lokasi', 3),
('column1', 'location_content', 'Kampus Kendeng<br>Jl. Kendeng V, Bendan Ngisor<br>Semarang, Jawa Tengah', 4),
('column1', 'contact_title', '📞 Kontak', 5),
('column1', 'contact_content', '<a href="tel:0248414970">(024) 8414970</a><br><a href="tel:0248311668">(024) 8311668</a>', 6),
('column1', 'email_title', '✉️ Email', 7),
('column1', 'email_content', '<a href="mailto:humas@mail.unisbank.ac.id">humas@mail.unisbank.ac.id</a>', 8);

-- Data default untuk Kolom 2 (Navigasi)
INSERT INTO footer_settings (section, key_name, value, sort_order) VALUES
('column2', 'nav_title', '🔗 Navigasi', 1),
('column2', 'nav_links', '[{"text":"🏠 Home","href":"/"},{"text":"ℹ️ About","href":"/about"},{"text":"📚 Course","href":"/course"},{"text":"📩 Contact","href":"/contact"}]', 2),
('column2', 'links_title', '🔗 Link Terkait', 3),
('column2', 'external_links', '[{"text":"🏛️ Unisbank Official","href":"https://www.unisbank.ac.id"},{"text":"🎓 PMB Unisbank","href":"https://pmb.unisbank.ac.id"},{"text":"📋 SIAKAD","href":"https://siakad.unisbank.ac.id"}]', 4);

-- Data default untuk Kolom 3 (Sosial Media)
INSERT INTO footer_settings (section, key_name, value, sort_order) VALUES
('column3', 'social_title', '📱 Social Media', 1),
('column3', 'social_description', 'Ikuti/update terbaru kami:', 2),
('column3', 'social_links', '[{"platform":"instagram","url":"https://instagram.com/kuliahdiunisbank","title":"Instagram"},{"platform":"tiktok","url":"https://www.tiktok.com/@unisbanksemarang","title":"TikTok"},{"platform":"youtube","url":"https://www.youtube.com/@universitasstikubank","title":"YouTube"},{"platform":"x","url":"https://x.com/UnisbankSmg","title":"X/Twitter"},{"platform":"facebook","url":"https://facebook.com/UnisbankSemarang","title":"Facebook"}]', 3),
('column3', 'whatsapp_title', 'Chat WhatsApp', 4),
('column3', 'whatsapp_number', '6282133332256', 5),
('column3', 'whatsapp_text', 'Chat WhatsApp', 6);

-- Data default untuk Copyright
INSERT INTO footer_settings (section, key_name, value, sort_order) VALUES
('copyright', 'copyright_text', 'Unisbank Career Centre', 1),
('copyright', 'credit_text', 'Developed with ❤️ for Unisbank Students', 2);
