-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 09, 2026 at 02:47 PM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u175708099_rakha_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','sakit','izin','cuti','tidak hadir') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `keterangan_keluar` text DEFAULT NULL,
  `lampiran_keluar` varchar(255) DEFAULT NULL,
  `latitude_keluar` varchar(255) DEFAULT NULL,
  `longitude_keluar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agendas`
--

CREATE TABLE `agendas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3788d8',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agenda_user`
--

CREATE TABLE `agenda_user` (
  `agenda_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `lampiran` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `nama_user` varchar(255) NOT NULL,
  `nama_perusahaan` varchar(255) NOT NULL,
  `tanggal_berdiri` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_telpon` varchar(50) DEFAULT NULL,
  `alamat_user` text DEFAULT NULL,
  `saldo_awal` decimal(15,2) DEFAULT 0.00,
  `bank` varchar(50) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nama_di_rekening` varchar(70) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat_perusahaan` text DEFAULT NULL,
  `jabatan` varchar(70) DEFAULT NULL,
  `hobby_client` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cutis`
--

CREATE TABLE `cutis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_cuti` enum('tahunan','sakit','cuti bersama') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `alasan` text NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `status` enum('diajukan','disetujui','ditolak','dibatalkan','proses_finalisasi') NOT NULL DEFAULT 'diajukan',
  `catatan_approval` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_3_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_4_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_approver_3` enum('menunggu','disetujui','ditolak','skipped') DEFAULT 'menunggu',
  `status_approver_4` varchar(50) DEFAULT 'skipped',
  `tanggal_approve_3` datetime DEFAULT NULL,
  `status_approver_1` enum('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'menunggu',
  `status_approver_2` enum('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'menunggu',
  `tanggal_approve_1` timestamp NULL DEFAULT NULL,
  `tanggal_approve_2` timestamp NULL DEFAULT NULL,
  `approver_cuti_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total_hari` int(11) DEFAULT NULL,
  `catatan_approver_1` text DEFAULT NULL,
  `catatan_approver_2` text DEFAULT NULL,
  `catatan_approver_3` text DEFAULT NULL,
  `catatan_approver_4` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuti_bersama_ledgers`
--

CREATE TABLE `cuti_bersama_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `holiday_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `is_cuti_bersama` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interactions`
--

CREATE TABLE `interactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `peserta` varchar(255) DEFAULT NULL,
  `jenis_transaksi` enum('IN','OUT','ENTERTAIN') NOT NULL,
  `nilai_kontribusi` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tanggal_interaksi` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nilai_sales` int(11) DEFAULT NULL,
  `komisi` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lemburs`
--

CREATE TABLE `lemburs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk_lembur` time NOT NULL,
  `jam_keluar_lembur` time DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `lampiran_masuk` varchar(255) DEFAULT NULL,
  `lampiran_keluar` varchar(255) DEFAULT NULL,
  `latitude_masuk` varchar(255) DEFAULT NULL,
  `longitude_masuk` varchar(255) DEFAULT NULL,
  `latitude_keluar` varchar(255) DEFAULT NULL,
  `longitude_keluar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_absen`
--

CREATE TABLE `lokasi_absen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius` int(11) NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_barang`
--

CREATE TABLE `pengajuan_barang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `judul_pengajuan` varchar(255) NOT NULL,
  `divisi` varchar(255) NOT NULL,
  `rincian_barang` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rincian_barang`)),
  `status` enum('diajukan','diproses','selesai','ditolak','dibatalkan','proses_finalisasi') NOT NULL DEFAULT 'diajukan',
  `status_appr_1` enum('menunggu','disetujui','ditolak','skipped','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `approver_barang_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan_approver_1` text DEFAULT NULL,
  `tanggal_approved_1` timestamp NULL DEFAULT NULL,
  `status_appr_2` enum('menunggu','disetujui','ditolak','skipped','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `approver_barang_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_barang_3_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_appr_3` enum('menunggu','disetujui','ditolak','skipped') DEFAULT 'menunggu',
  `catatan_approver_3` text DEFAULT NULL,
  `tanggal_approved_3` timestamp NULL DEFAULT NULL,
  `approver_barang_4_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_appr_4` enum('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'skipped',
  `catatan_approver_4` text DEFAULT NULL,
  `tanggal_approved_4` timestamp NULL DEFAULT NULL,
  `catatan_approver_2` text DEFAULT NULL,
  `tanggal_approved_2` timestamp NULL DEFAULT NULL,
  `status_direktur` enum('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'skipped',
  `catatan_direktur` text DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `lampiran` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lampiran`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_dana`
--

CREATE TABLE `pengajuan_dana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `finance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_processed_at` timestamp NULL DEFAULT NULL,
  `judul_pengajuan` varchar(255) NOT NULL,
  `divisi` varchar(255) NOT NULL,
  `nama_bank` varchar(255) NOT NULL,
  `no_rekening` varchar(255) NOT NULL,
  `nama_rek` varchar(100) DEFAULT NULL,
  `total_dana` decimal(15,2) NOT NULL,
  `lampiran` text DEFAULT NULL,
  `approver_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_1_status` enum('menunggu','disetujui','ditolak','skipped') DEFAULT NULL,
  `approver_1_catatan` text DEFAULT NULL,
  `approver_1_approved_at` timestamp NULL DEFAULT NULL,
  `approver_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_2_status` enum('menunggu','disetujui','ditolak','skipped') DEFAULT NULL,
  `approver_2_catatan` text DEFAULT NULL,
  `approver_2_approved_at` timestamp NULL DEFAULT NULL,
  `rincian_dana` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rincian_dana`)),
  `status` enum('diajukan','diproses_appr_2','disetujui','proses_pembayaran','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'diajukan',
  `status_atasan` enum('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'menunggu',
  `catatan_atasan` text DEFAULT NULL,
  `payment_status` enum('menunggu','diproses','selesai','skipped') DEFAULT NULL,
  `catatan_finance` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_dokumens`
--

CREATE TABLE `pengajuan_dokumens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_dokumen` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_pendukung` varchar(255) DEFAULT NULL,
  `status` enum('diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan',
  `catatan_admin` text DEFAULT NULL,
  `file_hasil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_pekerjaan`
--

CREATE TABLE `riwayat_pekerjaan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(255) DEFAULT NULL,
  `posisi` varchar(255) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `deskripsi_pekerjaan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_pendidikan`
--

CREATE TABLE `riwayat_pendidikan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `jenjang` varchar(100) DEFAULT NULL,
  `nama_institusi` varchar(255) DEFAULT NULL,
  `jurusan` varchar(255) DEFAULT NULL,
  `tahun_lulus` varchar(4) DEFAULT NULL,
  `file_ijazah` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nip` varchar(100) DEFAULT NULL,
  `status_karyawan` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `fcm_token` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `divisi` varchar(255) DEFAULT NULL,
  `is_kepala_divisi` tinyint(1) NOT NULL DEFAULT 0,
  `atasan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lokasi_kerja` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(255) DEFAULT NULL,
  `alamat_ktp` text DEFAULT NULL COMMENT 'Diubah dari alamat',
  `alamat_domisili` text DEFAULT NULL,
  `tempat_lahir` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(255) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `golongan_darah` varchar(10) DEFAULT NULL,
  `status_pernikahan` varchar(50) DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `kontak_darurat_nama` varchar(255) DEFAULT NULL,
  `kontak_darurat_nomor` varchar(255) DEFAULT NULL,
  `kontak_darurat_hubungan` varchar(100) DEFAULT NULL,
  `tanggal_bergabung` date DEFAULT NULL,
  `tanggal_mulai_kontrak` date DEFAULT NULL,
  `tanggal_akhir_kontrak` date DEFAULT NULL,
  `tanggal_berhenti` date DEFAULT NULL,
  `npwp` varchar(100) DEFAULT NULL,
  `file_npwp` varchar(255) DEFAULT NULL,
  `ptkp` varchar(20) DEFAULT NULL,
  `bpjs_kesehatan` varchar(100) DEFAULT NULL,
  `file_bpjs_kesehatan` varchar(255) DEFAULT NULL,
  `bpjs_ketenagakerjaan` varchar(100) DEFAULT NULL,
  `file_bpjs_ketenagakerjaan` varchar(255) DEFAULT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `nomor_rekening` varchar(100) DEFAULT NULL,
  `pemilik_rekening` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `jatah_cuti` int(11) NOT NULL DEFAULT 12,
  `sisa_cuti` int(11) DEFAULT 0,
  `approver_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_keuangan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_3_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_cuti_4_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_barang_1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_barang_2_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_barang_3_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approver_barang_4_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nip`, `status_karyawan`, `name`, `email`, `email_verified_at`, `password`, `fcm_token`, `profile_picture`, `jabatan`, `divisi`, `is_kepala_divisi`, `atasan_id`, `lokasi_kerja`, `nomor_telepon`, `alamat_ktp`, `alamat_domisili`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `golongan_darah`, `status_pernikahan`, `nik`, `file_ktp`, `kontak_darurat_nama`, `kontak_darurat_nomor`, `kontak_darurat_hubungan`, `tanggal_bergabung`, `tanggal_mulai_kontrak`, `tanggal_akhir_kontrak`, `tanggal_berhenti`, `npwp`, `file_npwp`, `ptkp`, `bpjs_kesehatan`, `file_bpjs_kesehatan`, `bpjs_ketenagakerjaan`, `file_bpjs_ketenagakerjaan`, `nama_bank`, `nomor_rekening`, `pemilik_rekening`, `role`, `remember_token`, `created_at`, `updated_at`, `jatah_cuti`, `sisa_cuti`, `approver_1_id`, `approver_2_id`, `manager_keuangan_id`, `approver_cuti_1_id`, `approver_cuti_2_id`, `approver_cuti_3_id`, `approver_cuti_4_id`, `approver_barang_1_id`, `approver_barang_2_id`, `approver_barang_3_id`, `approver_barang_4_id`) VALUES
(1, NULL, NULL, 'Admin Rakha', 'admin@rakha.com', NULL, '$2y$12$NRDUYoMptm2d/srLnEgCm.0aCgQUy2GVvGC9rtcUF/r8Lx106D4BC', 'f8L6I4aVu2wXh3-q9B0w41:APA91bEPcSFnljGyEtzxTTUZya06ywCQfEbM0Te41IkAdFRKlgHjasHc5siCFInhisqozweFmSizaV9LMqsDeD8qpeHEEveapo5CKlZg_fMgazUIL70hL30', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', NULL, '2025-09-13 10:57:39', '2026-07-08 16:43:48', 12, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '2025090013', NULL, 'Tuah Maujana Sinaga', 'tmaujana@gmail.com', NULL, '$2y$12$V710jKd/4ZyEwuNkZeaXzuni1kNWRCKHLir4nqoG1ReN1T5gyR8ZC', 'emxVdpoqpjl8eVUIMfsKg6:APA91bFm8t7E3ctCFr1moRPuw61utykATpJfb4dE4CNqWAkGDgQ91K_jY5Hn-CC6lQOoOroxYJE-IBWYiu4Ii3K4ULXyREg3EKFN8mMw0qt1MSL8shPaIGc', 'profile_pictures/lpG7mTtA6bNzoM0NcErr7EI4HDf4MZYDToPaTBR3.png', 'Kepala Operasional', 'Marketing dan Operasional', 1, NULL, 'Bogor', '081804953761', 'Jln Pajajaran Indah 1 No 79 Kel. Baranangsiang, Kec. Bogor Timur, Kota Bogor', NULL, 'Simalungun', '1976-06-25', 'Laki-laki', 'Islam', 'A', 'Menikah', '3201012506760015', 'dokumen_karyawan/ILbTJ9LjAHRrYyPr7lQqrOBW4eZvApZByaw4zKbc.pdf', 'Maryeni', '085280660686', 'Istri', '2025-09-01', NULL, NULL, NULL, '446675589403000', 'dokumen_karyawan/yDCbjiLcD67TMaf8wT1jAqcCLdPdqGMXJSdfqnak.pdf', 'K/3', '0001264512262', 'dokumen_karyawan/Y0Umxu62p2KjHsALSqEgcBcjTZ3d0yXQBU4xs5B1.pdf', '26003779720', 'dokumen_karyawan/7uFgUgKSUitBYZR7jr5EolYBmFyYBFW5Pu9sW5V3.jpg', 'BCA', '5395168417', 'Tuah Maujana Sinaga', 'user', NULL, '2025-09-14 08:40:46', '2026-07-09 20:38:32', 12, 3, 24, NULL, 34, 24, NULL, 34, 40, 24, NULL, 34, 40),
(9, '202405006', 'Tetap', 'Tiara Alifa Amalia', 'tiaraalifaa56@gmail.com', NULL, '$2y$12$KD1Rw.Vh9txQ1muwrWLkHusw1QYLQi0cdDXJRbbbX1Ehakwh35ib6', 'dRpjS03KNAJNUXU8m61ZWE:APA91bGdlP3S6Ji6OX73CMqg9TmBSuzE29EIvpQOwuiePX31ZoA0CHJhekvmcupuXjwAK6dFj-67FWeU3-0PXYEPRBqt5aKYc2hEQFlqK-lzx9GLpce5Nw0', 'profile_pictures/k85H6wYBPtwzMABv9Q630MNkFgPSOlWhN9se7dpB.jpg', 'Admin Support', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 815-7249-6312', 'Perumahan Taman Kenari, Blok C2/ 12B', 'Perumahan Taman Kenari, Blok C2/ 15', 'Bandung', '2004-01-19', 'Perempuan', 'Islam', 'Tidak Tahu', NULL, '3204155901040002', 'dokumen_karyawan/lX2ofKlFykCCyo42D0q3ZFALa3WhdaKNfkmHWPKB.jpg', 'Asep Ariswandi', '081353027354', 'Orang Tua', '2025-09-15', NULL, NULL, NULL, NULL, NULL, NULL, '0001716805449', NULL, '24142015189', NULL, 'BCA', '7475349989', 'Tiara Alifa Amalia', 'user', NULL, '2025-09-15 13:38:20', '2026-07-09 17:52:44', 12, 1, 4, NULL, 34, 4, NULL, 34, 40, 4, NULL, 34, 40),
(10, '202405004', 'Tetap', 'Eko Sigit Nugroho', 'ekosnug67@gmail.com', NULL, '$2y$12$t766TorbNwJEvzKb1nfifel0SMlvi65sm.v1YOXJpFOWwGCPi8FDW', NULL, 'profile_pictures/GDK5FpCHk8GrTfP6YqUmZFvgsHH4QFKA1YG6RzHB.png', 'Marketing', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 851-5527-2050', 'Perumahan Bintang Metropole , jl Mars 1 Blok C1 no7 RT 03/RW 13 , Perwira Kec. Bekasi Utara, Kota Bekasi', 'Perumahan Bintang Metropole , jl Mars 1 Blok C1 no7 RT 03/RW 13 , Perwira Kec. Bekasi Utara, Kota Bekasi .', 'Surabaya', '1967-10-04', 'Laki-laki', 'Islam', 'B', 'Menikah', '3175070410670005', 'dokumen_karyawan/XmHOMR49E61uCfckU7ZkNbG3DADPkB4N7pbXrhqv.jpg', 'Septarika Purteny', '081918537285', 'Istri', '2025-09-15', NULL, NULL, NULL, '90.585.425.3-407.000', 'dokumen_karyawan/ertcbyKBPCUbXa5EftDMOTQfjij8s847v46iOFFZ.jpg', 'TK/2', NULL, NULL, NULL, NULL, 'BCA', '094.051.6862', 'Eko Nugroho Sigit', 'user', NULL, '2025-09-15 13:42:45', '2026-07-07 08:06:05', 12, 2, 4, 24, 34, 4, 24, 34, 40, 4, 24, 34, 40),
(11, '202405005', 'Tetap', 'Surachman', 'rahmansurachman.rs@gmail.com', NULL, '$2y$12$oFXqIjBCFHg0tf.H0Pd7P.FU/cOiw5qoIVlDpbi/qsb263azPefNq', 'eWopwV0Y5tHsHUL-9zNkXa:APA91bHURfHufq_14EldEkODC_Y5xw-UQvHy6PeQEqlpjFnokECNGc3qFtceevsYfS_HAFQSAw9niz_CW6k8gsL-PvqiPtIPOCUUzCNBUqag_o1M4awFr0w', 'profile_pictures/9zZq8mb51infq0K26eWKrU24IvBDxDbC9nnZaEUz.png', 'Marketing', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 821-2251-2293', 'Villa Mutiara Jaya Blok NB6/17, RT. 03/012\r\nDs. Wanajaya Kec. Cibitung\r\nBekasi', 'Villa Mutiara Jaya Blok NB6/17, RT. 03/012\r\nDs. Wanajaya Kec, Cibitung\r\nBekasi', 'Lebak', '1978-03-21', 'Laki-laki', 'Islam', 'AB', 'Menikah', '3216072103730010', 'dokumen_karyawan/4sgdtJcZ6S1mHIn18GVxNhvixnDhKX8QCLjsM8AG.jpg', 'Nova Yuliana', '081214920514', 'Istri', '2025-09-15', NULL, NULL, NULL, '350168076435000', NULL, 'K/1', '0002515883681', 'dokumen_karyawan/AwXGrzoJVmwBDyPcFP0kTMYtrIvwkViW3IANswEj.jpg', '3216072103730010', 'dokumen_karyawan/PnPadNxGcdcG3lwexPFiMPLOUe2sxgBMCH1fYnCH.jpg', 'BCA', '4860273409', 'Surachman', 'user', NULL, '2025-09-15 13:43:30', '2026-07-07 08:06:05', 12, 2, 4, 24, 34, 4, 24, 34, 40, 4, 24, 34, 40),
(12, '202405003', 'Tetap', 'Arief Natanael Haryanto', 'Paladin_arief@yahoo.com', NULL, '$2y$12$QSKR4uNZqG3u8bxlIDn91.J.PgtZr98DYnNzvvXT4lQy4w8ZiIxC.', NULL, 'profile_pictures/FqM1wJJuk005pjBnlDFVfGdTrw8zLUEXBORP90MZ.png', 'Marketing', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 813-1036-2542', NULL, NULL, 'Bogor', '1988-12-22', 'Laki-laki', 'Kristen Protestan', 'O', 'Belum Menikah', '3271042212880002', 'dokumen_karyawan/wOJ8AO0mt9sLpXkSdCNqcJ5xxk5c2DdcT9BfYQEq.jpg', NULL, NULL, NULL, '2024-05-01', NULL, NULL, NULL, '58612515404000', 'dokumen_karyawan/5vgG9rPZSPBQ6GIQR5zz42a6ujb1iMnz4aAX5ZnG.jpg', NULL, '0001774123683', 'dokumen_karyawan/EjUo8zELw0GjCDcvaGDASLhuzuVr2b4yJYiBquXO.png', '14004331527', 'dokumen_karyawan/DD9aERQ2i5izd6OUWEOHaVwaXI3exo8frIPHMyBe.png', 'BCA', '8720209102', 'Arief natanael hariyanto', 'user', NULL, '2025-09-15 13:44:03', '2026-07-07 08:06:05', 12, 3, 4, 24, 34, 4, 24, 34, 40, 4, 24, 34, 40),
(13, '202409007', 'Tetap', 'Fitria Nur Azizah', 'fitrianurazizah018@gmail.com', NULL, '$2y$12$JX.XqoboO7xBNZ/TiKQKD.0gxWaNO.f5NRVBScWqk8xLa2ldLO07.', 'cRy48AtkUJxp03Qh_cvamc:APA91bEUXduRdrgTVvBVl0o6BK3hJvXhh5SkMUj3fs8mJkkSFzUJ0R8sByujKofM6ZBtYoYTzYL6o2os9lT6uHqRGhqMvgecaVdgBfekERgOkofwkcO65aQ', 'profile_pictures/SbW6nJNCW1cZgA5mj6Z7PQh05aHnYx9aB0pdHdax.png', 'Admin Finance', 'Finance dan Gudang', 0, NULL, 'Bogor', '0895358688517', 'Jl. Perintis Kemerdekaan Gg. Raharjo 2, RT. 001/RW. 12 Kec. Tegal Timur, Kel. Panggung, Kota Tegal', 'Perumahan Taman Kenari Blok C2/15, RT. 03/RW. 01, Cimahpar, Kec. Bogor Utara, Kota Bogor', 'Kota tegal', '2001-12-18', 'Perempuan', 'Islam', 'Tidak Tahu', 'Lajang', '3376025812010002', 'dokumen_karyawan/TswD2re2d8e7HORPGFr9qShHIgVWuBTrXTkU2a2V.jpg', 'Pramesti Subiatun Ambarwati', '+628976523996', 'Ibu', '2024-01-09', NULL, NULL, NULL, NULL, NULL, NULL, '0001809740992', 'dokumen_karyawan/CWbsMgvMeOK7DxJhWpGH7BMRoiGWqKC2yuJo6c0m.jpg', '3376025812010002', 'dokumen_karyawan/mcvVjgYPneSGxjqPotDycaq9R4scIiIFZNQkXwWq.jpg', 'BCA', '7475349997', 'Fitria Nur Azizah', 'user', NULL, '2025-09-15 13:49:48', '2026-07-07 08:06:05', 12, 3, 16, NULL, 34, 16, NULL, 34, 40, 16, NULL, 34, 40),
(14, '202409009', 'Tetap', 'Herul Mustakim', 'herul.mustakim05@gmail.com', NULL, '$2y$12$tw8k97aeIiIM/UISzIV7HusuEd4uCmc3LYVm1UPRLI8v2.fX3UpAi', NULL, 'profile_pictures/oev2nu4MO4nDExFJWuxN3LqSqMahCwUU5OprGwFW.png', 'Admin Gudang', 'Finance dan Gudang', 0, NULL, 'Bogor', '089512639405', 'JL. Tegal panjang Rt 002 Re 004 desa Tugu selatan Kec. Cisarua Kab. Bogor', 'JL. Kampung Pendeuy Rt 004 Rw 006 desa Pandansari kec. Ciawi', 'Bogor', '1994-05-31', 'Laki-laki', 'Islam', 'AB', 'Menikah', '3201253105940002', NULL, 'Nadhia Eka Putri', '0895333000207', 'Istri', '2025-09-15', NULL, NULL, NULL, NULL, NULL, NULL, '0001096729918', 'dokumen_karyawan/VB69AGRx06DRSgRTIaNDZA3MjthxUAlrfQBZCQq9.jpg', '24142015171', 'dokumen_karyawan/B5WJynpHkNfsyPYAKHEOa1Rmkab2Uh0BcSNRw22q.jpg', 'BCA', '7475352441', NULL, 'user', NULL, '2025-09-15 13:55:00', '2026-07-07 08:06:05', 12, 1, 16, NULL, 34, 16, NULL, 34, 40, 4, 16, 34, 40),
(15, '2024012010', 'Tetap', 'Taufik', '000taufik.12@gmail.com', NULL, '$2y$12$UYiV0HtAlk2INR8GzrS/Mu22QNWXPqaQFohUXDWb6W6PjzHaNQYSu', 'ezw29qtDHHAFjMIrQieUv8:APA91bE2z1OE-F8nPTGFwU6Z90UppneMHRb8yC4iRLAuublDHJNLEtp6nLyUW1CN_d8z4N1kNrhDJNwJvbq0Zz8zVRoyoxjMcdOxZqCrkbJcRdlTF06RbT0', 'profile_pictures/4ZFCLRB1fd7bay2Dmz4076uV0WoTqrY5SaWA1ZcD.png', 'Marketing Support', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 856-5941-5038', 'Kp Babakan baru kec waluran des waluran RT/RW 001/002', 'Jl. Perumahan Taman Kenari RT No.C2/12B, RT.bRT.03/RW.01, Cimahpar, Kec. Bogor Utara, Kota Bogor, Jawa Barat 16155', 'Kp Babakan baru kec waluran des waluran', '2004-07-27', 'Laki-laki', 'Islam', 'O', 'Lajang', '3202202407040001', NULL, 'Hidayat', '+62 838-7002-5864', 'Kaka', '2025-09-15', NULL, NULL, NULL, NULL, NULL, NULL, '0003640973905', NULL, '25114074922', NULL, 'BCA', '5737063891', 'Taufik', 'user', NULL, '2025-09-15 13:56:23', '2026-07-07 08:06:05', 7, 0, 9, 4, 34, 9, 4, 34, 40, 9, 4, 34, 40),
(16, '2025010010', 'Tetap', 'R Nena Ratnagiri', 'Nenabil88@gmail.com', NULL, '$2y$12$1ZzSCdDPlqVzv9WL9uxTjO81dwtVlqEyFlWV.PWPRBAyj47ocsJIy', 'ecFWjUM5HWeD9H7zRVkK_z:APA91bFzFIWjUxBdDzhxYVS9ArMZhhXMxzqHrcglNjZugQebWpA9VB2csnqKETduJ_eXuFHoeC_xCe6CTyMO9r2YjP2-PJm2Ek86fSR5OYyXzccUtWtBoio', 'profile_pictures/TGnkH7KSQmG7QZ500FoFHvSUN2YZBEldXdLgCGGM.png', 'Kepala Finance dan Accounting', 'Finance dan Gudang', 1, NULL, 'Bogor', '08129561044', 'Perum tatya asri blok id no 36', 'Perum Tatya Asri Blok ID-36 RT.001/ RW.012 Desa Cijujung Kec. Sukaraja Kab. Bogor', 'Bogor', '1977-11-25', 'Perempuan', 'Islam', 'O', 'Menikah', '3201046511770003', NULL, 'Slamet w', '085840041716', 'Suami', '2025-01-01', NULL, NULL, NULL, NULL, NULL, NULL, '0001282253332', NULL, NULL, NULL, 'BCA', '8410026687', 'R Nena ratnagiri', 'user', NULL, '2025-09-15 14:07:49', '2026-07-07 12:09:15', 9, 0, 24, NULL, 34, NULL, 24, 34, 40, 24, NULL, 34, 40),
(18, '2025090014', 'Kontrak', 'Asep Muhammad Azwar', 'Muhammadasep1111@gmail.com', NULL, '$2y$12$TJ9azPMlslGlZX3RGAxZeOGrkXNkSOV/23CMTuMke4oJrSlLrJvPG', 'e7biw17Na7NULWTvm48vV0:APA91bHREedFb392XZuShaFW11KbUFynMLtC9nkzZjVHSzeIGGx5h5sfdiBHi9wlINP2_mVTmJL29Ms8IpZohLJpusNq9UstfqXf6aZKdxBatUEsZdXPjOw', 'profile_pictures/VBbhHfIrHMWnIHslkvZ0Ip4vTT4fb4gY1JA5z3US.png', 'Gudang', 'Finance dan Gudang', 0, NULL, 'Bogor', '0895373675802', 'Kp. Belentuk rt.01 rw.01 kel.cimahpar kec.bogor utara KOTA BOGOR', 'Kp. Belentuk RT. 001/RW 001 Kel. Cimahpar Kec. Bogor Utara', 'KOTA BOGOR', '2002-06-10', 'Laki-laki', 'Islam', 'A', 'Belum Menikah', '327105006020006', NULL, 'Epul', '+62 896-5432-4937', 'Saudara kandung', '2025-06-02', NULL, NULL, NULL, NULL, NULL, NULL, '0000499936689', NULL, '25150524111', 'dokumen_karyawan/CU3Ns3XuRgjbDd8CjvC5SG7uhfsRivba5B9i2FQw.jpg', 'BCA', '7475409981', 'Asep Muhammad Azwar', 'user', NULL, '2025-09-16 13:27:33', '2026-07-07 08:06:05', 9, 0, 14, 16, 34, 14, 16, 34, 40, 14, 16, 34, 40),
(19, '000000000', NULL, 'Test', 'test@gmail.com', NULL, '$2y$12$NqTiYofLqHRCmXh9Ul2Z5eZ/7myf.15FJgEUA5MA0sTcBiIWJDJYC', 'c1Dk6sQAPVINMOJ3EXU7J_:APA91bHidP1r7NPfaOqn7YArwEFHIz3jyD95MH7wRXi3igYRQC21DrduiF78DNj-NhXYzH_97fJwmH1RKBkLeA05Qe4Wdbw5u27CNEiRto9-So_Vj3WfL04', 'profile_pictures/rey0lLHtLCT0kDz4FLb1bWPjK3o2WSSrEbjaUhtJ.png', 'Test', 'Marketing dan Operasional', 0, NULL, 'Kota Bogor', '081289922400', NULL, NULL, 'Bogor', '2004-02-28', 'Laki-laki', 'Islam', 'A', 'Belum Menikah', '161', NULL, NULL, NULL, NULL, '2025-09-29', NULL, NULL, NULL, NULL, 'dokumen_karyawan/kWwsFdWPYlmqrmAjB2efhdqeWaJ3H74z2iM4YXZ8.pdf', NULL, NULL, NULL, NULL, NULL, 'Mandiri', NULL, NULL, 'user', NULL, '2025-09-29 05:38:09', '2026-07-08 09:44:42', 12, 3, 4, 24, 34, 4, 24, 34, 1, 4, 24, 34, 1),
(24, '202405001', 'Tetap', 'Agung Kunto Himawan', 'Agungkuntohimawan81@gmail.com', NULL, '$2y$12$9j87TbQOupCf7DQrSzIsxejp8WshMGXa1yKCKcASBXBHzR2JUJDXC', 'doti1VsmHTRu5SYTfnZZB3:APA91bFVFTCadiSLdGvor1Lh_SBlfhARbM76iIX1LX2HThtH0th79w31r-vtEfKTYu1nzCiqB1T8AcBcR4cZywy0uB7NbMTYnaFWUG2LsokuR2YmeSZNcCc', 'profile_pictures/JxOpneZ07jufbZluKTCUQQHIimw8k4RegKtrfk2S.png', 'Direktur', 'Top Management', 0, NULL, 'Bogor', '+62 815-4807-1515', 'Perum taman kenari no 12 B block c2/12B kel cimahphar kec Bogor utara Kota Bogor', 'Perumahan Taman Kenari Blok C2/15', 'Tegal', '1981-08-27', 'Laki-laki', 'Islam', 'B', 'Menikah', '3271052708810017', NULL, 'Dewi', '085212169368', NULL, '2024-01-05', NULL, NULL, NULL, '680908332501000', NULL, 'K/0', '0001771267353', NULL, NULL, NULL, 'BCA', '3600106464', 'Agung Kunto Himawan', 'user', NULL, '2025-10-14 10:07:13', '2026-07-07 08:06:05', 12, 3, NULL, NULL, 34, NULL, NULL, NULL, 40, NULL, NULL, 34, 40),
(27, '2025050012', 'Tetap', 'R Hendra Dipraja', 'mahendradipradja@gmail.com', NULL, '$2y$12$Md944tumH1MI.XZAVzu2l..7PWAuWkw6yrhNKksuWooUg3u0m7P/O', 'dRpjS03KNAJNUXU8m61ZWE:APA91bGdlP3S6Ji6OX73CMqg9TmBSuzE29EIvpQOwuiePX31ZoA0CHJhekvmcupuXjwAK6dFj-67FWeU3-0PXYEPRBqt5aKYc2hEQFlqK-lzx9GLpce5Nw0', 'profile_pictures/19K3rxit8MVFqh6tHWlyzKwjhgXzSUowEBaMsA2T.jpg', 'Marketing', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+62 813-2191-9149', 'Komplek. Sanggar Mas Lestari Blok C19 Rt.009 Rw.012 Ds.Tarajusari Kec.Banjaran Kab.Bandung', NULL, 'Bandung', '1981-08-23', 'Laki-laki', 'Islam', 'O', 'Menikah', '3204172308810001', 'dokumen_karyawan/VbtaGmxOrHfDNP7vyHAxZb9gmSOY6ERYB1Yp9Fsa.jpg', 'Dian Herlina', '082316783623', 'Istri', '2025-10-21', NULL, NULL, NULL, '451930697422000', 'dokumen_karyawan/yNwWvJhxahFwxnUcvpqBhf4O2U4N8XwjWOYaxm5Q.jpg', NULL, '0001637647018', 'dokumen_karyawan/DL5g75Nvo3BpOZIDLFCZCtnNIBmuTVpeM5PmmKdk.jpg', '3273032308810004', 'dokumen_karyawan/g0tmetL4jUHPnPZPfZw5yeb61r8jgVCSFUqNXCx0.jpg', 'BCA', '4531227241', 'Rusiman Hendra Dipraja', 'user', NULL, '2025-10-21 16:09:04', '2026-07-07 19:29:06', 7, 0, 4, 24, 34, 4, 24, 34, 40, 4, 24, 34, 40),
(34, '202505002', 'Tetap', 'Euis Dewi Kurnia', 'dewi.affandi@gmail.com', NULL, '$2y$12$zLVh/GBhPFpqvR1uvJFcNen6nIx9HiN0VAlX6077bjmVRh55aFu5.', 'fCNyYJ1wrvGYiBEQIbPFRy:APA91bEO4R1w_3C_Ev3GAwwUzuyCwHi905ux9CAq4pGO0Cf5OxYzN9WP5A11vm8icGeA9h49Lm9UeYnjMJ9km1qRWA4N0ZVxU9Kc2HmDlEFqzPk9wV9JpPE', 'profile-pictures/hv1LwCutIYQE0zKVjL4WzneiPAl6KYriAnJjdn1S.jpg', 'Finance & HRD', 'Top Management', 1, NULL, 'Bogor', '085212169368', 'Perumahan Taman Kenari Blok C2/15 RT. 003 RW. 001 Kel. Cimahpar Kec. Bogor Utara Kota Bogor', 'Perumahan Taman Kenari Blok C2/15', 'Bandung', '1981-03-22', 'Perempuan', 'Islam', 'B', 'Menikah', '3271056203810013', NULL, 'Agung Kunto Himawan', '081548071515', 'Suami', '2024-01-05', NULL, NULL, NULL, '24.678.111.6-445.000', NULL, 'TK/0', '0001372346728', NULL, '24142015213', NULL, 'BCA', '0712235444', 'Euis Dewi Kurnia', 'user', NULL, '2026-01-07 14:46:19', '2026-07-07 08:06:05', 12, 3, NULL, NULL, 34, NULL, NULL, 24, 40, NULL, NULL, NULL, 40),
(35, '2025110015', 'Kontrak', 'Ade Suryadi', 'Suryadiade014@gmail.com', NULL, '$2y$12$hzUqqPoToLVEdQuwc4JqcejPIBuPS2aNGT.19U7RxunIwnMKqEEjW', 'dvVdqXg2olZKx7wxmswXd7:APA91bH9_1uCpklbieA60dyIccUrh_6J6X2CbEbd1cQkxJCsnniZJce7EM7vNydpZiXCxkSgoIiNnvFrRlnps07DX7Hfp84a0vMgVWZJzaS2GlAbyTmDvyQ', 'profile_pictures/4lvmOSbip0Lqb1EaV9UogNkcvWZaMVl0OKmoKLqh.png', 'OfficeBoy', 'Finance dan Gudang', 0, NULL, 'Bogor', '085714372570', 'Kp.sukabirus', 'Kp.coblong', 'Bogor', '1994-07-17', 'Laki-laki', 'Islam', 'O', 'Belum Menikah', '320126130393004', NULL, 'Nenk siti saptinah', '087796310937', 'Kaka kandung', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, '0003033914218', NULL, NULL, NULL, 'Bca', '7361789036', 'Ade.suryadi', 'user', NULL, '2026-01-26 14:29:30', '2026-07-07 08:06:05', 0, 0, 40, NULL, 34, NULL, NULL, 34, 40, NULL, NULL, 34, 40),
(40, '2026020016', 'Percobaan', 'Tyar Raihan Utami', 'tyarraihan24@gmail.com', NULL, '$2y$12$Xd.lzjWHwGzYIupeoiEzb.YgCc7J3HgONnst5lFFh84UalK6tQT.a', 'dKvrPpLgOzn63NEkxCo1ge:APA91bE7bWqAL3d1dzoodude2cTCdt0btLU0YRd8nRW_txquQzH4v526El-SCkerdzxErF1QUHKp6BTLoyghvSpDcdfbizoxcL43ImkmmNDOkDGrpunaI3k', 'profile_pictures/M3liLrNSTRZV1zwOXnBwe5TGwtc8riOvQUqpr5Rj.png', 'Legal', 'HRD', 0, NULL, 'Bogor', '089526863595', 'Jl. Pulo Rote Gang. 4 No. 9, RT/RW 019/009, Kel. Panggung, Kec. Tegal Timur, Kota Tegal', 'Perumahan Taman Kenari Blok C2/15, RT/RW 03/01, Kel. Cimahpar, Kec. Bogor Utara, Kota Bogor', 'Tegal', '2004-09-24', 'Perempuan', 'Islam', 'B', NULL, '3376026409040003', 'dokumen_karyawan/6iSrCBFWzUTT5NaKsN4FzIqRKF2v2ZGh5XLuRpRH.pdf', 'Maulana Aditya Kurniawan', '082242137182', 'Saudara kandung', '2026-02-02', NULL, NULL, NULL, NULL, NULL, NULL, '0001834175125', NULL, NULL, NULL, 'BCA', '7475468741', 'Tyar Raihan Utami', 'user', NULL, '2026-02-02 08:06:09', '2026-07-08 17:07:41', 0, 0, NULL, NULL, 34, NULL, NULL, 34, 40, NULL, NULL, 34, 40),
(41, '2026010017', 'Percobaan', 'Muhammad Robiul Awal', 'robiul2598@gmail.com', NULL, '$2y$12$lephp4g5JLQjF7iWEbI6CuDjY5Qmr9YqAixbXmNNDSPIjtCET2Mp.', 'dwssGphWqULtmBo6BNa99b:APA91bE1tb9qNYUgXRO0ng2ovVOEIcYmtzbpWHEmLez7FHPZJDjvGkSGrjUEXAAPiPJQPNRJUEy-sxP0sjVuMFtRwm48bj53WKXvJoiu9Zg4d2Dy03UG0-I', 'profile_pictures/5pjq79dRuaK0KlvXlI7ffbQKhoIixeQg1uIVPymD.png', 'Ekspedisi', 'Marketing dan Operasional', 0, NULL, 'Bogor', '081286805673', 'Kp Cijeruk rt03/02 desa palasari cek Cijeruk kab bogor', 'Kp geblug RT01/01 desa palasari kec Cijeruk kab bogor', 'Bogor', '1998-07-25', 'Laki-laki', 'Islam', NULL, NULL, '3201280107980039', 'dokumen_karyawan/JUkzQNQb1NFs6XdwgD7kJsZmGBc4OAoc20HmSGhi.jpg', 'Maryati', '+62 838-1366-0093', 'Orang tua', '2026-02-10', NULL, NULL, NULL, '137709509434000', NULL, NULL, NULL, NULL, '3201280107980039', 'dokumen_karyawan/lIPdUxP7qH3z4DblfCzWwoDnzL154LUmrEiHBLXH.jpg', 'BCA', '7475470052', 'Muhammad robiul awal', 'user', NULL, '2026-02-10 08:02:35', '2026-07-07 08:06:05', 0, 0, 9, 4, 34, 9, 4, 34, 40, 9, 4, 34, 40),
(42, '2026040018', 'Percobaan', 'Karsono Nu Haeman', 'karsononuhaeman975@gmail.com', NULL, '$2y$12$u3mLHyn.f4CLMN02KtitVOJydQbNfDvxesJB/hrWfjRZsUdoOoSPS', 'daZv-WkvFfVKXrBj8ItEcp:APA91bFvb9tFTnkKUsBYw3Fo9DrmgYFSLBcXtkjUZpEon8pg5p8AlryQ5XJ7lPhd2KERe_Vi5RKd2L_dK0NRZWYyFD6OrNt0RoaPouOrsxazj1cko0zR8EM', 'profile_pictures/LnXo71O0jN9lVZUUjB4wsj7hThZ2lE1dyJSbXEMB.png', 'Marketing', 'Marketing dan Operasional', 0, NULL, 'Bogor', '+6287789426696', 'Jl. H. Jian No. 25, RT/RW 006/003, Kel. Cipete Utara, Kec. Kebayoran Baru', 'Jl. H. Jian No. 25, RT/RW 006/003, Kel. Cipete Utara, Kec. Kebayoran Baru', 'Jakarta', '1975-04-23', 'Laki-laki', 'Islam', NULL, 'Menikah', '3174072304750004', NULL, 'Endariyani', '08567797579', 'Istri', '2026-05-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BCA', '2180792867', NULL, 'user', NULL, '2026-05-04 06:26:29', '2026-07-07 08:06:05', 0, 0, 4, 24, 34, 4, 24, 34, 40, 4, 24, 34, 40);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `absensi_user_id_tanggal_unique` (`user_id`,`tanggal`);

--
-- Indexes for table `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_agendas_to_users` (`user_id`);

--
-- Indexes for table `agenda_user`
--
ALTER TABLE `agenda_user`
  ADD PRIMARY KEY (`agenda_id`,`user_id`),
  ADD KEY `fk_agenda_user_to_users` (`user_id`);

--
-- Indexes for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aktivitas_user_id_foreign` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_user_id_foreign` (`user_id`);

--
-- Indexes for table `cutis`
--
ALTER TABLE `cutis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cutis_user_id_foreign` (`user_id`),
  ADD KEY `cutis_approver_id_foreign` (`approver_id`),
  ADD KEY `fk_cutis_approver_cuti_3_id` (`approver_cuti_3_id`),
  ADD KEY `fk_cutis_appr_1` (`approver_cuti_1_id`),
  ADD KEY `fk_cutis_appr_2` (`approver_cuti_2_id`);

--
-- Indexes for table `cuti_bersama_ledgers`
--
ALTER TABLE `cuti_bersama_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_ledger` (`user_id`),
  ADD KEY `fk_holiday_ledger` (`holiday_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `holidays_tanggal_unique` (`tanggal`);

--
-- Indexes for table `interactions`
--
ALTER TABLE `interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interactions_client_id_foreign` (`client_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lemburs`
--
ALTER TABLE `lemburs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lemburs_user_id_foreign` (`user_id`);

--
-- Indexes for table `lokasi_absen`
--
ALTER TABLE `lokasi_absen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_barang_user_id_foreign` (`user_id`),
  ADD KEY `fk_barang_appr_1` (`approver_barang_1_id`),
  ADD KEY `fk_barang_appr_2` (`approver_barang_2_id`),
  ADD KEY `fk_barang_appr_3` (`approver_barang_3_id`),
  ADD KEY `fk_pengajuan_barang_approver_4` (`approver_barang_4_id`);

--
-- Indexes for table `pengajuan_dana`
--
ALTER TABLE `pengajuan_dana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_dana_user_id_foreign` (`user_id`),
  ADD KEY `pengajuan_dana_approver_1_id_foreign` (`approver_1_id`),
  ADD KEY `pengajuan_dana_approver_2_id_foreign` (`approver_2_id`),
  ADD KEY `pengajuan_dana_finance_id_foreign_new` (`finance_id`);

--
-- Indexes for table `pengajuan_dokumens`
--
ALTER TABLE `pengajuan_dokumens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengajuan_dokumens_to_users` (`user_id`);

--
-- Indexes for table `riwayat_pekerjaan`
--
ALTER TABLE `riwayat_pekerjaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pekerjaan_user_id_foreign` (`user_id`);

--
-- Indexes for table `riwayat_pendidikan`
--
ALTER TABLE `riwayat_pendidikan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pendidikan_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `fk_atasan_id` (`atasan_id`),
  ADD KEY `users_approver_1_id_foreign` (`approver_1_id`),
  ADD KEY `users_approver_2_id_foreign` (`approver_2_id`),
  ADD KEY `users_manager_keuangan_id_foreign` (`manager_keuangan_id`),
  ADD KEY `fk_users_approver_cuti_1` (`approver_cuti_1_id`),
  ADD KEY `fk_users_approver_cuti_2` (`approver_cuti_2_id`),
  ADD KEY `fk_users_approver_barang_1` (`approver_barang_1_id`),
  ADD KEY `fk_users_approver_barang_2` (`approver_barang_2_id`),
  ADD KEY `fk_users_approver_cuti_3` (`approver_cuti_3_id`),
  ADD KEY `fk_users_approver_barang_3` (`approver_barang_3_id`),
  ADD KEY `users_approver_cuti_4_id_foreign` (`approver_cuti_4_id`),
  ADD KEY `fk_users_approver_barang_4` (`approver_barang_4_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cutis`
--
ALTER TABLE `cutis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cuti_bersama_ledgers`
--
ALTER TABLE `cuti_bersama_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interactions`
--
ALTER TABLE `interactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lemburs`
--
ALTER TABLE `lemburs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lokasi_absen`
--
ALTER TABLE `lokasi_absen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_dana`
--
ALTER TABLE `pengajuan_dana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_dokumens`
--
ALTER TABLE `pengajuan_dokumens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_pekerjaan`
--
ALTER TABLE `riwayat_pekerjaan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_pendidikan`
--
ALTER TABLE `riwayat_pendidikan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agendas`
--
ALTER TABLE `agendas`
  ADD CONSTRAINT `fk_agendas_to_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `agenda_user`
--
ALTER TABLE `agenda_user`
  ADD CONSTRAINT `fk_agenda_user_to_agendas` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agenda_user_to_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD CONSTRAINT `aktivitas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cutis`
--
ALTER TABLE `cutis`
  ADD CONSTRAINT `cutis_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cutis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cutis_appr_1` FOREIGN KEY (`approver_cuti_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cutis_appr_2` FOREIGN KEY (`approver_cuti_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cutis_approver_cuti_3_id` FOREIGN KEY (`approver_cuti_3_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cuti_bersama_ledgers`
--
ALTER TABLE `cuti_bersama_ledgers`
  ADD CONSTRAINT `fk_holiday_ledger` FOREIGN KEY (`holiday_id`) REFERENCES `holidays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_ledger` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interactions`
--
ALTER TABLE `interactions`
  ADD CONSTRAINT `interactions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lemburs`
--
ALTER TABLE `lemburs`
  ADD CONSTRAINT `lemburs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_barang`
--
ALTER TABLE `pengajuan_barang`
  ADD CONSTRAINT `fk_barang_appr_1` FOREIGN KEY (`approver_barang_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_barang_appr_2` FOREIGN KEY (`approver_barang_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_barang_appr_3` FOREIGN KEY (`approver_barang_3_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_barang_approver_3_id` FOREIGN KEY (`approver_barang_3_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pengajuan_barang_approver_4` FOREIGN KEY (`approver_barang_4_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pengajuan_barang_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_dana`
--
ALTER TABLE `pengajuan_dana`
  ADD CONSTRAINT `pengajuan_dana_approver_1_id_foreign` FOREIGN KEY (`approver_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pengajuan_dana_approver_2_id_foreign` FOREIGN KEY (`approver_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pengajuan_dana_finance_id_foreign_new` FOREIGN KEY (`finance_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pengajuan_dana_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengajuan_dokumens`
--
ALTER TABLE `pengajuan_dokumens`
  ADD CONSTRAINT `fk_pengajuan_dokumens_to_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_pekerjaan`
--
ALTER TABLE `riwayat_pekerjaan`
  ADD CONSTRAINT `riwayat_pekerjaan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_pendidikan`
--
ALTER TABLE `riwayat_pendidikan`
  ADD CONSTRAINT `riwayat_pendidikan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_atasan_id` FOREIGN KEY (`atasan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_barang_1` FOREIGN KEY (`approver_barang_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_barang_2` FOREIGN KEY (`approver_barang_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_barang_3` FOREIGN KEY (`approver_barang_3_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_barang_4` FOREIGN KEY (`approver_barang_4_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_approver_cuti_1` FOREIGN KEY (`approver_cuti_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_cuti_2` FOREIGN KEY (`approver_cuti_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_approver_cuti_3` FOREIGN KEY (`approver_cuti_3_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_approver_1_id_foreign` FOREIGN KEY (`approver_1_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_approver_2_id_foreign` FOREIGN KEY (`approver_2_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_approver_cuti_4_id_foreign` FOREIGN KEY (`approver_cuti_4_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_manager_keuangan_id_foreign` FOREIGN KEY (`manager_keuangan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
