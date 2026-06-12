-- Create the database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS sigereja;
-- USE sigereja;

-- 1. Manajemen Akses & Autentikasi
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('Super Admin', 'Admin Sistem', 'Sekretaris', 'Ketua Sektor', 'Pendeta', 'Jemaat') NOT NULL,
  `status_verifikasi` ENUM('Pending', 'Verified Sektor', 'Approved Majelis') DEFAULT 'Pending',
  `reset_token` VARCHAR(255) NULL,
  `reset_token_expiry` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Data Master
CREATE TABLE `tblSektor` (
  `id_sektor` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sektor` VARCHAR(100) NOT NULL
);

CREATE TABLE `tblSeksi` (
  `id_seksi` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_seksi` VARCHAR(100) NOT NULL
);

CREATE TABLE `tblKeahlian` (
  `id_keahlian` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_keahlian` VARCHAR(100) NOT NULL
);

CREATE TABLE `tblJabatanMajelis` (
  `id_jabatan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_jabatan` VARCHAR(100) NOT NULL
);

CREATE TABLE `tblJabatanSeksi` (
  `id_jabatan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_jabatan` VARCHAR(100) NOT NULL
);

CREATE TABLE `tblPeranIbadah` (
  `id_peran_ibadah` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_peran` VARCHAR(100) NOT NULL
);

-- 3. Data Inti Jemaat & Keluarga
CREATE TABLE `tblKeluarga` (
  `id_keluarga` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_kk` VARCHAR(50) UNIQUE,
  `id_kepala_keluarga` INT NULL -- Will be populated later or handled via logic
);

CREATE TABLE `tblJemaat` (
  `id_jemaat` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `id_keluarga` INT NULL,
  `status_dalam_keluarga` VARCHAR(50), -- Suami, Istri, Anak, dll
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `status_baptis` ENUM('Belum', 'Sudah') DEFAULT 'Belum',  -- H1 fix
  `status_sidi` ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
  `status_pernikahan` ENUM('Belum Menikah', 'Sudah Menikah', 'Cerai Hidup', 'Cerai Mati') NOT NULL,
  `pekerjaan` VARCHAR(100) NULL,                            -- H2 fix
  `id_sektor` INT NULL,
  `alamat` TEXT,
  `no_hp` VARCHAR(20),
  `alamat_email_aktif` VARCHAR(100),
  `asal_gereja` VARCHAR(150) NULL,
  `alasan_pindah` TEXT NULL,
  `status_keanggotaan` ENUM('Aktif', 'Meninggal Dunia', 'Pindah Gereja (Mutasi)', 'Tidak Ada Keterangan') DEFAULT 'Aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga`(`id_keluarga`) ON DELETE SET NULL,
  FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor`(`id_sektor`) ON DELETE SET NULL
);

-- ADD FK id_kepala_keluarga AFTER tblJemaat is created
ALTER TABLE `tblKeluarga`
  ADD CONSTRAINT `fk_kepala_keluarga`
  FOREIGN KEY (`id_kepala_keluarga`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE SET NULL;

-- 4. Relasi Kepengurusan & Keahlian
CREATE TABLE `tblPeran` (
  `id_peran` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_peran` VARCHAR(100) NOT NULL
);
-- Default Roles: Jemaat Biasa, Pelayan/Petugas, Pengurus Sektor, Pengurus Seksi, Majelis

CREATE TABLE `tblAkses_Jemaat` (
  `id_relasi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL,
  `id_peran` INT NOT NULL,
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_peran`) REFERENCES `tblPeran`(`id_peran`) ON DELETE CASCADE
);

CREATE TABLE `tblKeahlian_Pelayan` (
  `id_relasi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL,
  `id_keahlian` INT NOT NULL,
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_keahlian`) REFERENCES `tblKeahlian`(`id_keahlian`) ON DELETE CASCADE
);

CREATE TABLE `tblPengurusSektor` (
  `id_pengurus` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL,
  `id_sektor` INT NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL, -- e.g. Ketua, Sekretaris, dll
  `periode_tahun` VARCHAR(20),
  `status_approval` ENUM('Pending', 'Approved') DEFAULT 'Pending',
  `diverifikasi_oleh` INT NULL, -- FK to users (Pengantar/Sekretaris)
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor`(`id_sektor`) ON DELETE CASCADE,
  FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `tblPengurusSeksi` (
  `id_pengurus` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL,
  `id_seksi` INT NOT NULL,
  `id_jabatan` INT NOT NULL,
  `periode_tahun` VARCHAR(20),
  `status_approval` ENUM('Pending', 'Approved') DEFAULT 'Pending',
  `diverifikasi_oleh` INT NULL, -- FK to users (Pengantar/Sekretaris)
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_seksi`) REFERENCES `tblSeksi`(`id_seksi`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jabatan`) REFERENCES `tblJabatanSeksi`(`id_jabatan`) ON DELETE CASCADE,
  FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `tblDataMajelis` (
  `id_data_majelis` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL,
  `id_jabatan_majelis` INT NOT NULL,
  `periode_tahun` VARCHAR(20),
  `status_verifikasi_pendeta` ENUM('Pending', 'Approved') DEFAULT 'Pending',
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jabatan_majelis`) REFERENCES `tblJabatanMajelis`(`id_jabatan`) ON DELETE CASCADE
);

-- 5. Jadwal & Liturgi
CREATE TABLE `tblJadwalIbadah` (
  `id_ibadah` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_ibadah` VARCHAR(150) NOT NULL,
  `tanggal_waktu` DATETIME NOT NULL,
  `id_sektor` INT NULL, -- if specific to a Sektor
  FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor`(`id_sektor`) ON DELETE SET NULL
);

CREATE TABLE `tblLiturgi` (
  `id_liturgi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_ibadah` INT NOT NULL,
  `model_liturgi` VARCHAR(100),
  `ayat_bacaan` VARCHAR(100),
  `ayat_khotbah` VARCHAR(100),
  `keterangan_liturgi` TEXT,
  FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah`(`id_ibadah`) ON DELETE CASCADE
);

CREATE TABLE `tblAcaraKhusus` (
  `id_acara` INT AUTO_INCREMENT PRIMARY KEY,
  `id_ibadah` INT NOT NULL,
  `nama_grup` VARCHAR(100),
  `jenis_persembahan` VARCHAR(100),
  `urutan` INT,
  FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah`(`id_ibadah`) ON DELETE CASCADE
);

CREATE TABLE `tblNyanyian` (
  `id_nyanyian` INT AUTO_INCREMENT PRIMARY KEY,
  `id_ibadah` INT NOT NULL,
  `sumber_nyanyian` VARCHAR(100), -- e.g., Kidung Jemaat, Buku Nyanyian
  `nomor_lagu` VARCHAR(50),
  `ayat_lagu` VARCHAR(100),
  `urutan` INT,
  FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah`(`id_ibadah`) ON DELETE CASCADE
);

CREATE TABLE `tblPetugasIbadah` (
  `id_relasi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_ibadah` INT NOT NULL,
  `id_jemaat` INT NOT NULL,
  `id_peran_ibadah` INT NOT NULL,
  FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah`(`id_ibadah`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_peran_ibadah`) REFERENCES `tblPeranIbadah`(`id_peran_ibadah`) ON DELETE CASCADE
);

-- 6. Publikasi & Warta
-- NOTE: tblWartaJemaat is the canonical table used by all PHP code.
-- (The old tblWarta definition has been removed to avoid confusion.)
CREATE TABLE `tblWartaJemaat` (
  `id_warta` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `isi_warta` TEXT NOT NULL,
  `kategori` VARCHAR(50) DEFAULT 'Umum',
  `tanggal_terbit` DATE NOT NULL,
  `status_publish` ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft',
  `id_pengusul` INT NULL,    -- FK to users (author)
  `id_verifikator` INT NULL, -- FK to users (approver)
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_pengusul`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_verifikator`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `tblKegiatan` (
  `id_kegiatan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kegiatan` VARCHAR(150) NOT NULL,
  `tanggal_pelaksanaan` DATETIME NOT NULL,
  `tempat` VARCHAR(150),
  `status` ENUM('Akan Dilaksanakan', 'Selesai', 'Dibatalkan') DEFAULT 'Akan Dilaksanakan'
);

-- 7. Pendaftaran Pelayanan Khusus (Sakramen)
CREATE TABLE `tblPendaftaranBaptis` (
  `id_baptis` INT AUTO_INCREMENT PRIMARY KEY,
  `id_keluarga` INT NOT NULL,
  `nama_anak` VARCHAR(150) NOT NULL,
  `tempat_lahir` VARCHAR(100) NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
  `tanggal_pelaksanaan` DATE NULL,
  `status_approval` ENUM('Pending', 'Approved', 'Selesai', 'Ditolak') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga`(`id_keluarga`) ON DELETE CASCADE
);

CREATE TABLE `tblPendaftaranSidi` (
  `id_sidi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat` INT NOT NULL, -- The child
  `id_keluarga` INT NOT NULL, -- Parents applying
  `tanggal_pelaksanaan` DATE NULL,
  `status_approval` ENUM('Pending', 'Approved', 'Selesai', 'Ditolak') DEFAULT 'Pending',
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga`(`id_keluarga`) ON DELETE CASCADE
);

CREATE TABLE `tblPendaftaranNikah` (
  `id_nikah` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat_pria` INT NOT NULL,
  `id_jemaat_wanita` INT NOT NULL,
  `tanggal_pelaksanaan` DATETIME NULL,
  `tempat_pelaksanaan` VARCHAR(150) NULL,
  `status_approval` ENUM('Pending', 'Approved', 'Selesai', 'Ditolak') DEFAULT 'Pending',
  FOREIGN KEY (`id_jemaat_pria`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jemaat_wanita`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE
);

-- 8. Sistem Mutasi & Riwayat Keanggotaan
CREATE TABLE `tblMutasi` (
  `id_mutasi` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jemaat_pengaju` INT NOT NULL, -- Head of Family or Individual
  `tanggal_pengajuan` DATE NOT NULL,
  `gereja_tujuan` VARCHAR(150) NOT NULL,
  `alasan_mutasi` TEXT NOT NULL,
  `status_approval` ENUM('Pending', 'Approved', 'Ditolak') DEFAULT 'Pending',
  FOREIGN KEY (`id_jemaat_pengaju`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE
);

CREATE TABLE `tblMutasi_Detail` (
  `id_detail` INT AUTO_INCREMENT PRIMARY KEY,
  `id_mutasi` INT NOT NULL,
  `id_jemaat` INT NOT NULL, -- Which family members are moving
  FOREIGN KEY (`id_mutasi`) REFERENCES `tblMutasi`(`id_mutasi`) ON DELETE CASCADE,
  FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat`(`id_jemaat`) ON DELETE CASCADE
);

-- 9. Sistem Notifikasi
CREATE TABLE `tblNotifikasi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `url` VARCHAR(255) NULL,
  `is_read` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- =============================================================
-- SEED: Initial Super Admin Account
-- IMPORTANT: Do NOT use the default password in production!
-- After importing this schema, run the following PHP snippet
-- (or use the XAMPP shell) to generate a secure bcrypt hash:
--
--   echo password_hash('YOUR_SECURE_PASSWORD', PASSWORD_BCRYPT);
--
-- Then update the password column manually:
--   UPDATE users SET password = '<your_hash>' WHERE username = 'admin';
-- =============================================================
INSERT INTO `users` (`username`, `password`, `email`, `role`, `status_verifikasi`)
VALUES (
  'admin',
  '$2y$12$CHANGEME_RUN_password_hash_AND_REPLACE_THIS_HASH_NOW',
  'admin@sigereja.local',
  'Super Admin',
  'Approved Majelis'
);
-- ^^^ This intentionally uses an invalid hash so login is impossible
-- until the admin sets a real password. See instructions above.
