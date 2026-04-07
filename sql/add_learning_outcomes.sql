-- Tambah kolom learning_outcomes ke tabel courses
-- Berisi list skill/kompetensi yang akan dikuasai mahasiswa setelah mengikuti course
-- Format: JSON array of strings

ALTER TABLE courses ADD COLUMN learning_outcomes text NULL AFTER description;
