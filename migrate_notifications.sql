-- =============================================================
-- LEGACY MIGRATION: For databases created from the OLD schema.sql
-- If you did a FRESH install from the updated schema.sql (Jun 2026+),
-- you do NOT need to run this file — these tables are already included.
-- =============================================================
USE sigereja;

-- Add notifications table (safe to re-run)
CREATE TABLE IF NOT EXISTS `tblNotifikasi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `url` VARCHAR(255) NULL,
  `is_read` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Add warta table (safe to re-run) — replaces old tblWarta
CREATE TABLE IF NOT EXISTS `tblWartaJemaat` (
  `id_warta` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `isi_warta` TEXT NOT NULL,
  `kategori` VARCHAR(50) DEFAULT 'Umum',
  `tanggal_terbit` DATE NOT NULL,
  `status_publish` ENUM('Draft','Published','Archived') DEFAULT 'Draft',
  `id_pengusul` INT NULL,
  `id_verifikator` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_pengusul`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_verifikator`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- If you had the old tblWarta, migrate its data:
-- INSERT INTO tblWartaJemaat (judul, isi_warta, tanggal_terbit, id_pengusul, id_verifikator, status_publish)
-- SELECT judul, konten, tanggal_terbit, dibuat_oleh, diverifikasi_oleh,
--        IF(status_verifikasi='Approved','Published','Draft') FROM tblWarta;
-- DROP TABLE IF EXISTS tblWarta;
