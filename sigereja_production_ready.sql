-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sigereja
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tblAcaraKhusus`
--

DROP TABLE IF EXISTS `tblAcaraKhusus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblAcaraKhusus` (
  `id_acara` int(11) NOT NULL AUTO_INCREMENT,
  `id_ibadah` int(11) NOT NULL,
  `nama_grup` varchar(100) DEFAULT NULL,
  `jenis_persembahan` varchar(100) DEFAULT NULL,
  `urutan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_acara`),
  KEY `id_ibadah` (`id_ibadah`),
  CONSTRAINT `tblacarakhusus_ibfk_1` FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah` (`id_ibadah`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblAcaraKhusus`
--

LOCK TABLES `tblAcaraKhusus` WRITE;
/*!40000 ALTER TABLE `tblAcaraKhusus` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblAcaraKhusus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblAkses_Jemaat`
--

DROP TABLE IF EXISTS `tblAkses_Jemaat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblAkses_Jemaat` (
  `id_relasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_peran` int(11) NOT NULL,
  PRIMARY KEY (`id_relasi`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_peran` (`id_peran`),
  CONSTRAINT `tblakses_jemaat_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblakses_jemaat_ibfk_2` FOREIGN KEY (`id_peran`) REFERENCES `tblPeran` (`id_peran`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblAkses_Jemaat`
--

LOCK TABLES `tblAkses_Jemaat` WRITE;
/*!40000 ALTER TABLE `tblAkses_Jemaat` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblAkses_Jemaat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblDataMajelis`
--

DROP TABLE IF EXISTS `tblDataMajelis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblDataMajelis` (
  `id_data_majelis` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_jabatan_majelis` int(11) NOT NULL,
  `periode_tahun` varchar(20) DEFAULT NULL,
  `status_verifikasi_pendeta` enum('Pending','Approved') DEFAULT 'Pending',
  PRIMARY KEY (`id_data_majelis`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_jabatan_majelis` (`id_jabatan_majelis`),
  CONSTRAINT `tbldatamajelis_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tbldatamajelis_ibfk_2` FOREIGN KEY (`id_jabatan_majelis`) REFERENCES `tblJabatanMajelis` (`id_jabatan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblDataMajelis`
--

LOCK TABLES `tblDataMajelis` WRITE;
/*!40000 ALTER TABLE `tblDataMajelis` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblDataMajelis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblJabatanMajelis`
--

DROP TABLE IF EXISTS `tblJabatanMajelis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblJabatanMajelis` (
  `id_jabatan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblJabatanMajelis`
--

LOCK TABLES `tblJabatanMajelis` WRITE;
/*!40000 ALTER TABLE `tblJabatanMajelis` DISABLE KEYS */;
INSERT INTO `tblJabatanMajelis` VALUES (2,'Ketua'),(3,'Sekretaris'),(7,'Wakil Sekretaris'),(10,'Wakil Ketua'),(11,'Bendahara'),(13,'Wakil bendahara'),(14,'Penasehat 1'),(15,'Penasehat 2'),(16,'Penasehat 3');
/*!40000 ALTER TABLE `tblJabatanMajelis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblJabatanSeksi`
--

DROP TABLE IF EXISTS `tblJabatanSeksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblJabatanSeksi` (
  `id_jabatan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  PRIMARY KEY (`id_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblJabatanSeksi`
--

LOCK TABLES `tblJabatanSeksi` WRITE;
/*!40000 ALTER TABLE `tblJabatanSeksi` DISABLE KEYS */;
INSERT INTO `tblJabatanSeksi` VALUES (1,'Ketua'),(2,'Sekretaris'),(3,'Bendahara');
/*!40000 ALTER TABLE `tblJabatanSeksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblJadwalIbadah`
--

DROP TABLE IF EXISTS `tblJadwalIbadah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblJadwalIbadah` (
  `id_ibadah` int(11) NOT NULL AUTO_INCREMENT,
  `nama_ibadah` varchar(150) NOT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `id_sektor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_ibadah`),
  KEY `id_sektor` (`id_sektor`),
  CONSTRAINT `tbljadwalibadah_ibfk_1` FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor` (`id_sektor`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblJadwalIbadah`
--

LOCK TABLES `tblJadwalIbadah` WRITE;
/*!40000 ALTER TABLE `tblJadwalIbadah` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblJadwalIbadah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblJemaat`
--

DROP TABLE IF EXISTS `tblJemaat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblJemaat` (
  `id_jemaat` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `id_keluarga` int(11) DEFAULT NULL,
  `status_dalam_keluarga` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `status_baptis` enum('Belum','Sudah') DEFAULT 'Belum',
  `status_sidi` enum('Ya','Tidak') DEFAULT 'Tidak',
  `status_pernikahan` enum('Belum Menikah','Sudah Menikah','Cerai Hidup','Cerai Mati') NOT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `id_sektor` int(11) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat_email_aktif` varchar(100) DEFAULT NULL,
  `asal_gereja` varchar(150) DEFAULT NULL,
  `alasan_pindah` text DEFAULT NULL,
  `status_keanggotaan` enum('Aktif','Meninggal Dunia','Pindah Gereja (Mutasi)','Tidak Ada Keterangan') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jemaat`),
  KEY `user_id` (`user_id`),
  KEY `id_keluarga` (`id_keluarga`),
  KEY `id_sektor` (`id_sektor`),
  CONSTRAINT `tbljemaat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tbljemaat_ibfk_2` FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga` (`id_keluarga`) ON DELETE SET NULL,
  CONSTRAINT `tbljemaat_ibfk_3` FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor` (`id_sektor`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblJemaat`
--

LOCK TABLES `tblJemaat` WRITE;
/*!40000 ALTER TABLE `tblJemaat` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblJemaat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblKeahlian`
--

DROP TABLE IF EXISTS `tblKeahlian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKeahlian` (
  `id_keahlian` int(11) NOT NULL AUTO_INCREMENT,
  `nama_keahlian` varchar(100) NOT NULL,
  PRIMARY KEY (`id_keahlian`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblKeahlian`
--

LOCK TABLES `tblKeahlian` WRITE;
/*!40000 ALTER TABLE `tblKeahlian` DISABLE KEYS */;
INSERT INTO `tblKeahlian` VALUES (1,'Keyboard'),(2,'Guitar'),(3,'Singer'),(4,'Multi Media'),(5,'Kelistrikan'),(6,'Teknik Bangunan'),(7,'Komputer (Office)'),(8,'Komputer (Hardware)'),(9,'Hospitality'),(10,'Master Ceremony'),(11,'Ahli Adat dan Budaya'),(12,'Bidang Hukum'),(13,'Pengembangan Sistem Informasi Gereja'),(14,'Keamanan Gereja'),(15,'Penyusun Laporan Keuangan Gereja'),(16,'Pajak'),(17,'Komunikasi Publik'),(18,'Hubungan Masyarakat'),(19,'Videography'),(20,'Photography');
/*!40000 ALTER TABLE `tblKeahlian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblKeahlian_Pelayan`
--

DROP TABLE IF EXISTS `tblKeahlian_Pelayan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKeahlian_Pelayan` (
  `id_relasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_keahlian` int(11) NOT NULL,
  PRIMARY KEY (`id_relasi`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_keahlian` (`id_keahlian`),
  CONSTRAINT `tblkeahlian_pelayan_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblkeahlian_pelayan_ibfk_2` FOREIGN KEY (`id_keahlian`) REFERENCES `tblKeahlian` (`id_keahlian`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblKeahlian_Pelayan`
--

LOCK TABLES `tblKeahlian_Pelayan` WRITE;
/*!40000 ALTER TABLE `tblKeahlian_Pelayan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblKeahlian_Pelayan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblKegiatan`
--

DROP TABLE IF EXISTS `tblKegiatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKegiatan` (
  `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kegiatan` varchar(150) NOT NULL,
  `tanggal_pelaksanaan` datetime NOT NULL,
  `tempat` varchar(150) DEFAULT NULL,
  `status` enum('Akan Dilaksanakan','Selesai','Dibatalkan') DEFAULT 'Akan Dilaksanakan',
  PRIMARY KEY (`id_kegiatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblKegiatan`
--

LOCK TABLES `tblKegiatan` WRITE;
/*!40000 ALTER TABLE `tblKegiatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblKegiatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblKeluarga`
--

DROP TABLE IF EXISTS `tblKeluarga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKeluarga` (
  `id_keluarga` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_kk` varchar(50) DEFAULT NULL,
  `id_kepala_keluarga` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_keluarga`),
  UNIQUE KEY `nomor_kk` (`nomor_kk`),
  KEY `fk_kepala_keluarga` (`id_kepala_keluarga`),
  CONSTRAINT `fk_kepala_keluarga` FOREIGN KEY (`id_kepala_keluarga`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblKeluarga`
--

LOCK TABLES `tblKeluarga` WRITE;
/*!40000 ALTER TABLE `tblKeluarga` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblKeluarga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblLiturgi`
--

DROP TABLE IF EXISTS `tblLiturgi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblLiturgi` (
  `id_liturgi` int(11) NOT NULL AUTO_INCREMENT,
  `id_ibadah` int(11) NOT NULL,
  `model_liturgi` varchar(100) DEFAULT NULL,
  `ayat_bacaan` varchar(100) DEFAULT NULL,
  `ayat_khotbah` varchar(100) DEFAULT NULL,
  `keterangan_liturgi` text DEFAULT NULL,
  PRIMARY KEY (`id_liturgi`),
  KEY `id_ibadah` (`id_ibadah`),
  CONSTRAINT `tblliturgi_ibfk_1` FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah` (`id_ibadah`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblLiturgi`
--

LOCK TABLES `tblLiturgi` WRITE;
/*!40000 ALTER TABLE `tblLiturgi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblLiturgi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblMutasi`
--

DROP TABLE IF EXISTS `tblMutasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblMutasi` (
  `id_mutasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat_pengaju` int(11) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `gereja_tujuan` varchar(150) NOT NULL,
  `alasan_mutasi` text NOT NULL,
  `status_approval` enum('Pending','Approved','Ditolak') DEFAULT 'Pending',
  PRIMARY KEY (`id_mutasi`),
  KEY `id_jemaat_pengaju` (`id_jemaat_pengaju`),
  CONSTRAINT `tblmutasi_ibfk_1` FOREIGN KEY (`id_jemaat_pengaju`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblMutasi`
--

LOCK TABLES `tblMutasi` WRITE;
/*!40000 ALTER TABLE `tblMutasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblMutasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblMutasi_Detail`
--

DROP TABLE IF EXISTS `tblMutasi_Detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblMutasi_Detail` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_mutasi` int(11) NOT NULL,
  `id_jemaat` int(11) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_mutasi` (`id_mutasi`),
  KEY `id_jemaat` (`id_jemaat`),
  CONSTRAINT `tblmutasi_detail_ibfk_1` FOREIGN KEY (`id_mutasi`) REFERENCES `tblMutasi` (`id_mutasi`) ON DELETE CASCADE,
  CONSTRAINT `tblmutasi_detail_ibfk_2` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblMutasi_Detail`
--

LOCK TABLES `tblMutasi_Detail` WRITE;
/*!40000 ALTER TABLE `tblMutasi_Detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblMutasi_Detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblNotifikasi`
--

DROP TABLE IF EXISTS `tblNotifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblNotifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tblnotifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblNotifikasi`
--

LOCK TABLES `tblNotifikasi` WRITE;
/*!40000 ALTER TABLE `tblNotifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblNotifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblNyanyian`
--

DROP TABLE IF EXISTS `tblNyanyian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblNyanyian` (
  `id_nyanyian` int(11) NOT NULL AUTO_INCREMENT,
  `id_ibadah` int(11) NOT NULL,
  `sumber_nyanyian` varchar(100) DEFAULT NULL,
  `nomor_lagu` varchar(50) DEFAULT NULL,
  `ayat_lagu` varchar(100) DEFAULT NULL,
  `urutan` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_nyanyian`),
  KEY `id_ibadah` (`id_ibadah`),
  CONSTRAINT `tblnyanyian_ibfk_1` FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah` (`id_ibadah`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblNyanyian`
--

LOCK TABLES `tblNyanyian` WRITE;
/*!40000 ALTER TABLE `tblNyanyian` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblNyanyian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPendaftaranBaptis`
--

DROP TABLE IF EXISTS `tblPendaftaranBaptis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPendaftaranBaptis` (
  `id_baptis` int(11) NOT NULL AUTO_INCREMENT,
  `id_keluarga` int(11) NOT NULL,
  `nama_anak` varchar(150) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `status_approval` enum('Pending','Approved','Selesai','Ditolak') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_baptis`),
  KEY `id_keluarga` (`id_keluarga`),
  CONSTRAINT `tblpendaftaranbaptis_ibfk_1` FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga` (`id_keluarga`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPendaftaranBaptis`
--

LOCK TABLES `tblPendaftaranBaptis` WRITE;
/*!40000 ALTER TABLE `tblPendaftaranBaptis` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPendaftaranBaptis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPendaftaranNikah`
--

DROP TABLE IF EXISTS `tblPendaftaranNikah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPendaftaranNikah` (
  `id_nikah` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat_pria` int(11) NOT NULL,
  `id_jemaat_wanita` int(11) NOT NULL,
  `tanggal_pelaksanaan` datetime DEFAULT NULL,
  `tempat_pelaksanaan` varchar(150) DEFAULT NULL,
  `status_approval` enum('Pending','Approved','Selesai','Ditolak') DEFAULT 'Pending',
  PRIMARY KEY (`id_nikah`),
  KEY `id_jemaat_pria` (`id_jemaat_pria`),
  KEY `id_jemaat_wanita` (`id_jemaat_wanita`),
  CONSTRAINT `tblpendaftarannikah_ibfk_1` FOREIGN KEY (`id_jemaat_pria`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblpendaftarannikah_ibfk_2` FOREIGN KEY (`id_jemaat_wanita`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPendaftaranNikah`
--

LOCK TABLES `tblPendaftaranNikah` WRITE;
/*!40000 ALTER TABLE `tblPendaftaranNikah` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPendaftaranNikah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPendaftaranSidi`
--

DROP TABLE IF EXISTS `tblPendaftaranSidi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPendaftaranSidi` (
  `id_sidi` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_keluarga` int(11) NOT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `status_approval` enum('Pending','Approved','Selesai','Ditolak') DEFAULT 'Pending',
  PRIMARY KEY (`id_sidi`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_keluarga` (`id_keluarga`),
  CONSTRAINT `tblpendaftaransidi_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblpendaftaransidi_ibfk_2` FOREIGN KEY (`id_keluarga`) REFERENCES `tblKeluarga` (`id_keluarga`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPendaftaranSidi`
--

LOCK TABLES `tblPendaftaranSidi` WRITE;
/*!40000 ALTER TABLE `tblPendaftaranSidi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPendaftaranSidi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPengurusSeksi`
--

DROP TABLE IF EXISTS `tblPengurusSeksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPengurusSeksi` (
  `id_pengurus` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_seksi` int(11) NOT NULL,
  `id_jabatan` int(11) NOT NULL,
  `periode_tahun` varchar(20) DEFAULT NULL,
  `status_approval` enum('Pending','Approved') DEFAULT 'Pending',
  `diverifikasi_oleh` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pengurus`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_seksi` (`id_seksi`),
  KEY `id_jabatan` (`id_jabatan`),
  KEY `diverifikasi_oleh` (`diverifikasi_oleh`),
  CONSTRAINT `tblpengurusseksi_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblpengurusseksi_ibfk_2` FOREIGN KEY (`id_seksi`) REFERENCES `tblSeksi` (`id_seksi`) ON DELETE CASCADE,
  CONSTRAINT `tblpengurusseksi_ibfk_3` FOREIGN KEY (`id_jabatan`) REFERENCES `tblJabatanSeksi` (`id_jabatan`) ON DELETE CASCADE,
  CONSTRAINT `tblpengurusseksi_ibfk_4` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPengurusSeksi`
--

LOCK TABLES `tblPengurusSeksi` WRITE;
/*!40000 ALTER TABLE `tblPengurusSeksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPengurusSeksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPengurusSektor`
--

DROP TABLE IF EXISTS `tblPengurusSektor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPengurusSektor` (
  `id_pengurus` int(11) NOT NULL AUTO_INCREMENT,
  `id_jemaat` int(11) NOT NULL,
  `id_sektor` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode_tahun` varchar(20) DEFAULT NULL,
  `status_approval` enum('Pending','Approved') DEFAULT 'Pending',
  `diverifikasi_oleh` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pengurus`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_sektor` (`id_sektor`),
  KEY `diverifikasi_oleh` (`diverifikasi_oleh`),
  CONSTRAINT `tblpengurussektor_ibfk_1` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblpengurussektor_ibfk_2` FOREIGN KEY (`id_sektor`) REFERENCES `tblSektor` (`id_sektor`) ON DELETE CASCADE,
  CONSTRAINT `tblpengurussektor_ibfk_3` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPengurusSektor`
--

LOCK TABLES `tblPengurusSektor` WRITE;
/*!40000 ALTER TABLE `tblPengurusSektor` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPengurusSektor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPeran`
--

DROP TABLE IF EXISTS `tblPeran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPeran` (
  `id_peran` int(11) NOT NULL AUTO_INCREMENT,
  `nama_peran` varchar(100) NOT NULL,
  PRIMARY KEY (`id_peran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPeran`
--

LOCK TABLES `tblPeran` WRITE;
/*!40000 ALTER TABLE `tblPeran` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPeran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPeranIbadah`
--

DROP TABLE IF EXISTS `tblPeranIbadah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPeranIbadah` (
  `id_peran_ibadah` int(11) NOT NULL AUTO_INCREMENT,
  `nama_peran` varchar(100) NOT NULL,
  PRIMARY KEY (`id_peran_ibadah`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPeranIbadah`
--

LOCK TABLES `tblPeranIbadah` WRITE;
/*!40000 ALTER TABLE `tblPeranIbadah` DISABLE KEYS */;
INSERT INTO `tblPeranIbadah` VALUES (1,'Khotbah'),(2,'Singer'),(3,'Penyambut Jemaat'),(4,'Petugas Kolekte'),(5,'Doa Syafaat'),(6,'Pembaca Bacaan Ayat Alkitab'),(7,'Pemimpin Ibadah (votum)'),(8,'Pembaca Warta Jemaat'),(9,'Pembaca Warta Pembangunan');
/*!40000 ALTER TABLE `tblPeranIbadah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblPetugasIbadah`
--

DROP TABLE IF EXISTS `tblPetugasIbadah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblPetugasIbadah` (
  `id_relasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_ibadah` int(11) NOT NULL,
  `id_jemaat` int(11) NOT NULL,
  `id_peran_ibadah` int(11) NOT NULL,
  PRIMARY KEY (`id_relasi`),
  KEY `id_ibadah` (`id_ibadah`),
  KEY `id_jemaat` (`id_jemaat`),
  KEY `id_peran_ibadah` (`id_peran_ibadah`),
  CONSTRAINT `tblpetugasibadah_ibfk_1` FOREIGN KEY (`id_ibadah`) REFERENCES `tblJadwalIbadah` (`id_ibadah`) ON DELETE CASCADE,
  CONSTRAINT `tblpetugasibadah_ibfk_2` FOREIGN KEY (`id_jemaat`) REFERENCES `tblJemaat` (`id_jemaat`) ON DELETE CASCADE,
  CONSTRAINT `tblpetugasibadah_ibfk_3` FOREIGN KEY (`id_peran_ibadah`) REFERENCES `tblPeranIbadah` (`id_peran_ibadah`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblPetugasIbadah`
--

LOCK TABLES `tblPetugasIbadah` WRITE;
/*!40000 ALTER TABLE `tblPetugasIbadah` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblPetugasIbadah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblSeksi`
--

DROP TABLE IF EXISTS `tblSeksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblSeksi` (
  `id_seksi` int(11) NOT NULL AUTO_INCREMENT,
  `nama_seksi` varchar(100) NOT NULL,
  PRIMARY KEY (`id_seksi`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblSeksi`
--

LOCK TABLES `tblSeksi` WRITE;
/*!40000 ALTER TABLE `tblSeksi` DISABLE KEYS */;
INSERT INTO `tblSeksi` VALUES (1,'Pria (Bapak)'),(2,'Wanita (Ibu)'),(3,'Pemuda'),(4,'Sekolah Minggu & Remaja'),(5,'Pembangunan Gereja'),(6,'Sinode Gereja');
/*!40000 ALTER TABLE `tblSeksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblSektor`
--

DROP TABLE IF EXISTS `tblSektor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblSektor` (
  `id_sektor` int(11) NOT NULL AUTO_INCREMENT,
  `nama_sektor` varchar(100) NOT NULL,
  PRIMARY KEY (`id_sektor`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblSektor`
--

LOCK TABLES `tblSektor` WRITE;
/*!40000 ALTER TABLE `tblSektor` DISABLE KEYS */;
INSERT INTO `tblSektor` VALUES (1,'Betlehem'),(2,'Betsheda'),(3,'Getsemane'),(4,'Sinai'),(5,'Nazareth'),(6,'Galilea'),(7,'Betania'),(8,'Sei Jordan');
/*!40000 ALTER TABLE `tblSektor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblWarta`
--

DROP TABLE IF EXISTS `tblWarta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblWarta` (
  `id_warta` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(150) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `konten` text NOT NULL,
  `status_verifikasi` enum('Draft/Usulan','Approved') DEFAULT 'Draft/Usulan',
  `dibuat_oleh` int(11) DEFAULT NULL,
  `diverifikasi_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_warta`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  KEY `diverifikasi_oleh` (`diverifikasi_oleh`),
  CONSTRAINT `tblwarta_ibfk_1` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tblwarta_ibfk_2` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblWarta`
--

LOCK TABLES `tblWarta` WRITE;
/*!40000 ALTER TABLE `tblWarta` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblWarta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tblWartaJemaat`
--

DROP TABLE IF EXISTS `tblWartaJemaat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblWartaJemaat` (
  `id_warta` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(150) NOT NULL,
  `isi_warta` text NOT NULL,
  `kategori` varchar(50) DEFAULT 'Umum',
  `tanggal_terbit` date NOT NULL,
  `status_publish` enum('Draft','Published','Archived') DEFAULT 'Draft',
  `id_pengusul` int(11) DEFAULT NULL,
  `id_verifikator` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_warta`),
  KEY `id_pengusul` (`id_pengusul`),
  KEY `id_verifikator` (`id_verifikator`),
  CONSTRAINT `tblwartajemaat_ibfk_1` FOREIGN KEY (`id_pengusul`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tblwartajemaat_ibfk_2` FOREIGN KEY (`id_verifikator`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tblWartaJemaat`
--

LOCK TABLES `tblWartaJemaat` WRITE;
/*!40000 ALTER TABLE `tblWartaJemaat` DISABLE KEYS */;
/*!40000 ALTER TABLE `tblWartaJemaat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Super Admin','Admin Sistem','Sekretaris','Ketua Sektor','Pendeta','Jemaat') NOT NULL,
  `status_verifikasi` enum('Pending','Verified Sektor','Approved Majelis') DEFAULT 'Pending',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','admin@sigereja.local','Super Admin','',NULL,NULL,'2026-05-17 03:28:32'),(5,'ketua sektor 1','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','ketuasektor1@gmail.com','Ketua Sektor','Approved Majelis',NULL,NULL,'2026-05-29 10:53:10'),(6,'sekretaris gereja','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','sekretaris@sigereja.local','Sekretaris','Approved Majelis',NULL,NULL,'2026-05-29 10:53:51'),(7,'Pendeta gereja','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','pendeta@gereja.com','Pendeta','Approved Majelis',NULL,NULL,'2026-05-29 10:54:27'),(8,'Admin Gereja','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','admingereja@sigereja.local','Admin Sistem','Approved Majelis',NULL,NULL,'2026-05-29 10:55:21'),(9,'dinisetia','$2y$10$cvR4cNXYF670oXiuF1p92u94LIakT7cXSrJJvEU2V4oGifOyNje7m','dinisetia@gmail.com','Jemaat','Pending','338d677f0698a1ea5c37b620b5c479333b5da2a497dcc2a50f027b3622860dae','2026-06-02 11:09:49','2026-06-02 08:06:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12 15:49:50
