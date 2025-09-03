-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Sep 2025 pada 20.39
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `babi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `keterangan` varchar(100) DEFAULT 'Hadir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@anastasyavocalarts.com', '2025-08-27 07:51:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `whatsapp_number` varchar(20) DEFAULT '6281234567890',
  `admin_email` varchar(255) DEFAULT 'admin@anastasyavocalarts.com',
  `enable_email_notifications` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `whatsapp_number`, `admin_email`, `enable_email_notifications`, `created_at`, `updated_at`) VALUES
(1, '6281233009283', 'ava@anastasyavocalarts.co', 0, '2025-08-12 13:20:16', '2025-08-12 13:23:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ebooks`
--

CREATE TABLE `ebooks` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `gambar_cover` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL,
  `smtp_host` varchar(255) DEFAULT 'localhost',
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(255) DEFAULT '',
  `smtp_password` varchar(255) DEFAULT '',
  `from_email` varchar(255) NOT NULL DEFAULT 'ava@anastasya.co',
  `from_name` varchar(255) NOT NULL DEFAULT 'Anastasya Vocal Arts',
  `is_active` tinyint(1) DEFAULT 1,
  `send_invoice` tinyint(1) DEFAULT 1,
  `send_notification` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `email_settings`
--

INSERT INTO `email_settings` (`id`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`, `from_email`, `from_name`, `is_active`, `send_invoice`, `send_notification`, `created_at`, `updated_at`) VALUES
(1, 'localhost', 587, '', '', 'ava@anastasya.co', 'Anastasya Vocal Arts', 1, 1, 1, '2025-08-12 13:20:16', '2025-08-12 13:26:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id`, `username`, `password`, `nama_lengkap`, `email`, `telepon`, `created_at`) VALUES
(1, 'guru', '$2y$10$nrVrW6yPE/pweA2pdTdn1.zKec4lN02E4wtYq8TbAA8AJXdVDZh16', 'Guru AVA', 'guru@anastasyavocalarts.com', '08123456789', '2025-08-27 07:51:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ijin`
--

CREATE TABLE `ijin` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal_ijin` date NOT NULL,
  `alasan` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `is_booked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id`, `hari`, `jam_mulai`, `jam_selesai`, `is_booked`, `created_at`) VALUES
(1, 'Senin', '09:00:00', '10:00:00', 0, '2025-08-27 07:51:40'),
(2, 'Selasa', '10:00:00', '11:00:00', 0, '2025-08-27 07:51:40'),
(3, 'Rabu', '14:00:00', '15:00:00', 0, '2025-08-27 07:51:40'),
(4, 'Kamis', '16:00:00', '17:00:00', 0, '2025-08-27 07:51:40'),
(5, 'Senin', '10:00:00', '10:45:00', 0, '2025-08-28 04:16:44'),
(6, 'Senin', '13:00:00', '13:45:00', 0, '2025-08-28 04:17:20'),
(7, 'Senin', '14:00:00', '14:45:00', 0, '2025-08-28 04:18:34'),
(8, 'Senin', '11:00:00', '11:45:00', 0, '2025-08-28 04:18:48'),
(9, 'Senin', '15:00:00', '15:45:00', 0, '2025-08-28 04:19:04'),
(10, 'Senin', '16:00:00', '16:45:00', 0, '2025-08-28 04:19:11'),
(11, 'Senin', '17:00:00', '17:45:00', 0, '2025-08-28 04:19:20'),
(13, 'Selasa', '11:00:00', '11:45:00', 0, '2025-08-28 04:19:44'),
(14, 'Selasa', '13:00:00', '13:45:00', 0, '2025-08-28 04:19:50'),
(15, 'Selasa', '14:00:00', '14:45:00', 0, '2025-08-28 04:19:58'),
(16, 'Selasa', '15:00:00', '15:45:00', 0, '2025-08-28 04:20:07'),
(17, 'Selasa', '16:00:00', '16:45:00', 0, '2025-08-28 04:20:14'),
(18, 'Selasa', '17:00:00', '17:45:00', 0, '2025-08-28 04:20:29'),
(19, 'Selasa', '09:00:00', '09:45:00', 0, '2025-08-28 04:20:42'),
(22, 'Rabu', '11:00:00', '11:45:00', 0, '2025-08-28 04:21:09'),
(24, 'Rabu', '13:00:00', '13:45:00', 0, '2025-08-28 04:21:22'),
(25, 'Rabu', '15:00:00', '15:45:00', 0, '2025-08-28 04:21:28'),
(27, 'Rabu', '17:00:00', '17:45:00', 0, '2025-08-28 04:21:41'),
(28, 'Kamis', '11:00:00', '11:45:00', 0, '2025-08-28 04:22:36'),
(29, 'Kamis', '13:00:00', '13:45:00', 0, '2025-08-28 04:22:54'),
(30, 'Kamis', '14:00:00', '14:45:00', 0, '2025-08-28 04:23:02'),
(31, 'Kamis', '15:00:00', '15:45:00', 0, '2025-08-28 04:23:08'),
(32, 'Kamis', '17:00:00', '17:45:00', 0, '2025-08-28 04:23:15'),
(33, 'Jumat', '10:00:00', '10:45:00', 0, '2025-08-28 04:23:32'),
(36, 'Jumat', '14:00:00', '14:45:00', 0, '2025-08-28 04:23:55'),
(37, 'Jumat', '15:00:00', '15:45:00', 0, '2025-08-28 04:24:02'),
(38, 'Jumat', '16:00:00', '16:45:00', 0, '2025-08-28 04:24:08'),
(39, 'Jumat', '17:00:00', '17:45:00', 0, '2025-08-28 04:24:15'),
(40, 'Sabtu', '09:00:00', '09:45:00', 0, '2025-08-28 04:24:26'),
(41, 'Sabtu', '10:00:00', '10:45:00', 0, '2025-08-28 04:24:34'),
(42, 'Sabtu', '11:00:00', '11:45:00', 0, '2025-08-28 04:24:40'),
(43, 'Sabtu', '13:00:00', '13:45:00', 0, '2025-08-28 04:24:52'),
(44, 'Sabtu', '14:00:00', '14:45:00', 0, '2025-08-28 04:24:59'),
(45, 'Sabtu', '15:00:00', '15:45:00', 0, '2025-08-28 04:25:07'),
(46, 'Sabtu', '16:00:00', '16:45:00', 0, '2025-08-28 04:25:13'),
(47, 'Sabtu', '17:00:00', '17:45:00', 0, '2025-08-28 04:25:19'),
(48, 'Jumat', '11:30:00', '12:15:00', 0, '2025-08-31 04:14:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `manual_payment_verification`
--

CREATE TABLE `manual_payment_verification` (
  `id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `payment_proof_path` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `manual_payment_verification`
--

INSERT INTO `manual_payment_verification` (`id`, `order_id`, `payment_proof_path`, `notes`, `status`, `admin_notes`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 'REG-34-1756615892', 'uploads/payment_proofs/REG-34-1756615892_1756616113.png', '', 'approved', '', 1, '2025-09-01 02:26:19', '2025-08-31 04:55:13', '2025-09-01 02:26:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_siswa`
--

CREATE TABLE `notifikasi_siswa` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_as_push` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi_siswa`
--

INSERT INTO `notifikasi_siswa` (`id`, `siswa_id`, `judul`, `pesan`, `is_read`, `sent_as_push`, `created_at`) VALUES
(1, 37, 'Tes Notif', 'Hallo, ini adalah tes push notifikesyen ', 1, 1, '2025-09-02 22:38:34'),
(2, 37, 'tes lg', 'tes ', 1, 1, '2025-09-02 22:39:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `harga_kursus` decimal(10,2) NOT NULL,
  `biaya_pendaftaran` decimal(10,2) NOT NULL,
  `midtrans_fee` decimal(10,2) DEFAULT 0.00,
  `pajak_total` decimal(15,2) DEFAULT 0.00,
  `kode_promo` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT 'pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `snap_redirect_url` text DEFAULT NULL,
  `transaction_time` timestamp NULL DEFAULT NULL,
  `settlement_time` timestamp NULL DEFAULT NULL,
  `expiry_time` timestamp NULL DEFAULT NULL,
  `fraud_status` varchar(50) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `payment_proof_path` varchar(255) DEFAULT NULL,
  `payment_proof_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bukti_manual` varchar(255) DEFAULT NULL COMMENT 'Path to manual payment proof file',
  `status_manual_upload` enum('pending','approved','rejected') DEFAULT NULL COMMENT 'Status of manual payment verification',
  `harga_kursus_base` decimal(10,2) DEFAULT 0.00,
  `biaya_pendaftaran_base` decimal(10,2) DEFAULT 0.00,
  `pajak_kursus` decimal(10,2) DEFAULT 0.00,
  `pajak_pendaftaran` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `order_id`, `gross_amount`, `harga_kursus`, `biaya_pendaftaran`, `midtrans_fee`, `pajak_total`, `kode_promo`, `payment_type`, `transaction_status`, `snap_token`, `snap_redirect_url`, `transaction_time`, `settlement_time`, `expiry_time`, `fraud_status`, `pdf_path`, `payment_proof_path`, `payment_proof_notes`, `created_at`, `updated_at`, `bukti_manual`, `status_manual_upload`, `harga_kursus_base`, `biaya_pendaftaran_base`, `pajak_kursus`, `pajak_pendaftaran`) VALUES
(32, 34, 'REG-34-1756615892', 705000.00, 500000.00, 200000.00, 5000.00, 0.00, 'PRIVATESALEAVA00', 'manual_transfer', 'paid', '945d7db7-29a0-41c7-9ba9-eef2e54a1e07', NULL, NULL, '2025-09-01 02:26:19', NULL, NULL, NULL, 'uploads/payment_proofs/REG-34-1756615892_1756616113.png', '', '2025-08-31 04:51:32', '2025-09-01 02:26:19', NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(33, 35, 'REG-35-1756617195', 705000.00, 500000.00, 200000.00, 5000.00, 0.00, 'PRIVATESALEAVA00', NULL, 'paid', '023d47c1-42c1-411e-a1a1-86be8e6cc9ee', NULL, NULL, NULL, NULL, 'accept', NULL, NULL, NULL, '2025-08-31 05:13:15', '2025-08-31 05:19:01', NULL, NULL, 0.00, 0.00, 0.00, 0.00),
(34, 36, 'REG-36-1756617272', 705000.00, 500000.00, 200000.00, 5000.00, 0.00, 'PRIVATESALEAVA00', NULL, 'paid', 'fa826819-094c-4e10-95e0-afc57da52a1a', NULL, NULL, NULL, NULL, 'accept', NULL, NULL, NULL, '2025-08-31 05:14:32', '2025-08-31 05:16:54', NULL, NULL, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_pengaturan` varchar(100) NOT NULL,
  `nilai_pengaturan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `harga_kursus` decimal(10,2) NOT NULL,
  `biaya_pendaftaran` decimal(10,2) NOT NULL DEFAULT 200000.00,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `max_usage` int(11) DEFAULT NULL COMMENT 'Maximum number of times this promo can be used',
  `current_usage` int(11) NOT NULL DEFAULT 0 COMMENT 'Current number of times this promo has been used'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `kode_promo`, `nama_promo`, `harga_kursus`, `biaya_pendaftaran`, `deskripsi`, `status`, `created_at`, `updated_at`, `max_usage`, `current_usage`) VALUES
(1, 'PRIVATESALEAVA00', 'Private Sale Early Bird', 500000.00, 200000.00, 'Harga spesial private sale untuk pendaftar awal', 'aktif', '2025-08-12 13:20:16', '2025-08-31 05:14:32', 5, 3),
(2, 'PRESALEAVA01', 'Pre Sale Promo', 650000.00, 200000.00, 'Harga pre sale untuk pendaftar early bird', 'aktif', '2025-08-12 13:20:16', '2025-08-29 08:48:47', 5, 0),
(3, 'REGULER', 'Harga Reguler', 800000.00, 200000.00, 'Harga reguler tanpa promo', 'aktif', '2025-08-12 13:20:16', '2025-08-29 08:46:46', 15, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','guru','admin') NOT NULL,
  `endpoint` text NOT NULL,
  `p256dh_key` varchar(255) NOT NULL,
  `auth_key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'midtrans_server_key', 'Mid-server-Y2EkzEiKMinHkIWJtbOv8Q6t', '2025-08-27 13:53:37', '2025-08-27 15:40:37'),
(2, 'midtrans_client_key', 'Mid-client-ADmmAFI61tw89u63', '2025-08-27 13:53:37', '2025-08-27 15:40:37'),
(3, 'harga_kursus_standar', '800000', '2025-08-28 19:27:40', '2025-08-28 19:27:40'),
(4, 'biaya_pendaftaran_standar', '200000', '2025-08-28 19:27:40', '2025-08-28 19:27:40'),
(5, 'pwa_app_name', 'Anastasya Vocal Arts', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(6, 'pwa_app_short_name', 'AVA', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(7, 'pwa_app_description', 'Aplikasi Anastasya Vocal Arts', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(8, 'pwa_theme_color', '#ee3a6a', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(9, 'pwa_background_color', '#fffee0', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(10, 'pwa_icon_72', '/uploads/pwa_icons/icon-72x72.png', '2025-09-02 22:25:44', '2025-09-02 22:25:44'),
(16, 'pwa_icon_96', '/uploads/pwa_icons/icon-96x96.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(17, 'pwa_icon_128', '/uploads/pwa_icons/icon-128x128.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(18, 'pwa_icon_144', '/uploads/pwa_icons/icon-144x144.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(19, 'pwa_icon_152', '/uploads/pwa_icons/icon-152x152.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(20, 'pwa_icon_192', '/uploads/pwa_icons/icon-192x192.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(21, 'pwa_icon_384', '/uploads/pwa_icons/icon-384x384.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05'),
(22, 'pwa_icon_512', '/uploads/pwa_icons/icon-512x512.png', '2025-09-02 22:27:05', '2025-09-02 22:27:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nama_panggilan` varchar(100) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_orang_tua` varchar(255) DEFAULT NULL,
  `pekerjaan_orang_tua` varchar(255) DEFAULT NULL,
  `telepon_orang_tua` varchar(20) DEFAULT NULL,
  `email_orang_tua` varchar(255) DEFAULT NULL,
  `pendidikan_terakhir` varchar(255) DEFAULT NULL,
  `kelas_semester` varchar(100) DEFAULT NULL,
  `hobi_minat` text DEFAULT NULL,
  `pengalaman_musik` text DEFAULT NULL,
  `genre_favorit` text DEFAULT NULL,
  `pernah_lomba` varchar(10) DEFAULT NULL,
  `detail_lomba` text DEFAULT NULL,
  `motivasi_harapan` text DEFAULT NULL,
  `referensi_lagu` text DEFAULT NULL,
  `riwayat_kesehatan` text DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `kode_promo` varchar(50) DEFAULT NULL,
  `jadwal_id` int(11) DEFAULT NULL,
  `durasi_bulan` int(11) DEFAULT 1,
  `biaya_per_bulan` decimal(10,2) DEFAULT 500000.00,
  `tanggal_mulai` date DEFAULT NULL,
  `status_pembayaran` enum('pending','paid','failed','expired','Belum Lunas','Lunas','Cicil') DEFAULT 'pending',
  `qr_code_identifier` varchar(100) DEFAULT NULL,
  `active_stream_id` varchar(100) DEFAULT NULL,
  `tanggal_pendaftaran` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nama_lengkap`, `nama_panggilan`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat_lengkap`, `telepon`, `email`, `password`, `nama_orang_tua`, `pekerjaan_orang_tua`, `telepon_orang_tua`, `email_orang_tua`, `pendidikan_terakhir`, `kelas_semester`, `hobi_minat`, `pengalaman_musik`, `genre_favorit`, `pernah_lomba`, `detail_lomba`, `motivasi_harapan`, `referensi_lagu`, `riwayat_kesehatan`, `foto_profil`, `kode_promo`, `jadwal_id`, `durasi_bulan`, `biaya_per_bulan`, `tanggal_mulai`, `status_pembayaran`, `qr_code_identifier`, `active_stream_id`, `tanggal_pendaftaran`, `created_at`, `updated_at`, `remember_token`, `remember_token_expires`) VALUES
(34, 'Syahla Queena', 'Syahla', 'Bandung', '2000-02-18', 'Perempuan', 'Jl. Guntursari III No. 33', '0818225933', 'srecarvinatalegawa@gmail.com', '$2y$10$I8GDgZXoLGNl3eZe0J./mOeBl49snXwcoFVXKMxv9KNnV/lGw.k8K', '', '', '', '', 'Sarjana', '', 'Hobi menyanyi & ingin berkarya', 'Mengikuti paduan suara saat SD, mengikuti vocal group saat SMP, lumayan aktif nyanyi waktu SMP dan SMA.', 'Pop', 'Ya', 'Paduan Suara waktu SD, FLS2N Vocal Group waktu SMP, dan nyanyi solo di angkatan sekolah aja sih waktu SMP & SMA. Hehehe', 'Kalau ada jalan mau berkarya & supaya lebih pede lagi nyanyinya krn udah lama bgt ga nyanyi. Terutama belajar high note, krn sebelumnya kurang pede jadi seringkali bawain lagu yang flat. ', 'Maudy Ayunda? Dsb...... banyaknya seneng lagu jadul 🙂', 'Rhinitis Alergi cukup parah. Ini salah satu challenge hrs jaga imun banget krn jadinya sering radang tenggorokan.', '', 'PRIVATESALEAVA00', 4, 1, 500000.00, '2025-09-01', 'paid', 'AVA-68b3d4d42ab97', NULL, '2025-08-31 04:51:32', '2025-08-31 04:51:32', '2025-09-01 02:25:48', NULL, NULL),
(35, 'Weni Gustiani', 'Weni', 'Bandung', '1991-08-31', 'Perempuan', 'Arjuna Land City Blok D2 Bojongsoang', '081223531991', 'inyugustiani@gmail.com', '$2y$10$CysY0K1FwNCavrtT63y5F.Gxv10uBq7Um40gzYLzJ.K3RXe6fRBAK', '', '', '', '', 'Sarjana', 'Guru', 'Hobi menggambar dan bernyanyi', 'Kalau musik belum ada pengalaman hanya pengalaman dalam bernyanyi', 'Pop', 'Ya', 'Lomba nyanyi', 'Motivasi ikut kursus disini karna coach nya kak putri sdh profesional dan harapannya aku bisa lebih meningkatkan skill bernyanyi', 'Pinkan mamboo kekasih yg tak dianggap, agnez MO, sheilla on 7, Dewa 19, Rossa, BCl, Melly Goeslaw, ', 'Kadang naik asam lambung kalau telat makan', 'uploads/profil/68b3d9eb435fe.jpeg', 'PRIVATESALEAVA00', 38, 1, 500000.00, NULL, 'paid', 'AVA-68b3d9eb50053', NULL, '2025-08-31 05:13:15', '2025-08-31 05:13:15', '2025-08-31 05:19:01', NULL, NULL),
(36, 'Arsyila Hamandinasha asis', 'Arsyila', 'Bandung', '2018-10-09', 'Perempuan', 'Tatar tarubhawana \r\nJalan tarusanti 18 \r\nKota baru parahyangan', '08112320876', 'Achriawalasis197@gmail.com', '$2y$10$dQsqaLM7o2RoNJzLjFaMFOGR2aZw6pQhy9SMtVPIlPS4TeeMiqFQa', 'Anita rahayu', 'Ibu rumah tangga', '081222017969', 'rahayunita212@gmail.com', 'Primary ', 'Kelas 1', 'Menyanyi', 'Pernah ikut alunias di sekolah ', 'Pop', 'Tidak', '', 'Bismillah mudah2an dengan arsyila ikut les vocal arsyila bisa mengembangkan hobinya dan bisa bernyanyi dengan benar bagus aamiin ', 'Selalu ada di nadimu,,', 'Alergi dan asma ringan ', NULL, 'PRIVATESALEAVA00', 41, 1, 500000.00, NULL, 'paid', 'AVA-68b3da3808be0', NULL, '2025-08-31 05:14:32', '2025-08-31 05:14:32', '2025-08-31 05:16:54', NULL, NULL),
(37, 'Muhaammad Yuda Ramadhan', 'Yuda', 'Bandung', '1997-01-23', 'Laki-laki', 'ads', '081233009283', 'myudar2301@gmail.com', '$2y$10$nrVrW6yPE/pweA2pdTdn1.zKec4lN02E4wtYq8TbAA8AJXdVDZh16', 'hoho', 'hihi', '081233009283', 'kahveeproduction@gmail.com', 's3', 'lulus', 'hobi', 'musik', 'Dangdut', 'Tidak', NULL, 'a', 'b', 'c', NULL, NULL, 3, 1, 800000.00, '2025-08-26', 'paid', 'AVA-68ae0a24b8056', NULL, '2025-08-26 12:25:24', '2025-08-26 12:25:24', '2025-09-03 18:30:40', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `streaming_sessions`
--

CREATE TABLE `streaming_sessions` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `stream_title` varchar(255) NOT NULL,
  `stream_key` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `student_progress`
--

CREATE TABLE `student_progress` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `checkin_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `checkout_time` timestamp NULL DEFAULT NULL,
  `nilai_perkembangan` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tagihan`
--

CREATE TABLE `tagihan` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `invoice_kode` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `bulan_ke` int(11) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `status` enum('Belum Lunas','Lunas','Terlambat') DEFAULT 'Belum Lunas',
  `snap_token` varchar(255) DEFAULT NULL,
  `midtrans_order_id` varchar(100) DEFAULT NULL,
  `tanggal_bayar` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `vapid_keys`
--

CREATE TABLE `vapid_keys` (
  `id` int(11) NOT NULL,
  `public_key` text NOT NULL,
  `private_key` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_tanggal` (`siswa_id`,`tanggal`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ebooks`
--
ALTER TABLE `ebooks`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `ijin`
--
ALTER TABLE `ijin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`hari`,`jam_mulai`,`jam_selesai`);

--
-- Indeks untuk tabel `manual_payment_verification`
--
ALTER TABLE `manual_payment_verification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `status` (`status`);

--
-- Indeks untuk tabel `notifikasi_siswa`
--
ALTER TABLE `notifikasi_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`);

--
-- Indeks untuk tabel `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indeks untuk tabel `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_subscription` (`user_id`,`user_type`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `jadwal_id` (`jadwal_id`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- Indeks untuk tabel `streaming_sessions`
--
ALTER TABLE `streaming_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indeks untuk tabel `student_progress`
--
ALTER TABLE `student_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_siswa_date` (`siswa_id`,`session_date`),
  ADD KEY `idx_session_date` (`session_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_kode` (`invoice_kode`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `vapid_keys`
--
ALTER TABLE `vapid_keys`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ebooks`
--
ALTER TABLE `ebooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ijin`
--
ALTER TABLE `ijin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `manual_payment_verification`
--
ALTER TABLE `manual_payment_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_siswa`
--
ALTER TABLE `notifikasi_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `streaming_sessions`
--
ALTER TABLE `streaming_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `student_progress`
--
ALTER TABLE `student_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `vapid_keys`
--
ALTER TABLE `vapid_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ijin`
--
ALTER TABLE `ijin`
  ADD CONSTRAINT `ijin_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi_siswa`
--
ALTER TABLE `notifikasi_siswa`
  ADD CONSTRAINT `notifikasi_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`);

--
-- Ketidakleluasaan untuk tabel `streaming_sessions`
--
ALTER TABLE `streaming_sessions`
  ADD CONSTRAINT `streaming_sessions_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `student_progress`
--
ALTER TABLE `student_progress`
  ADD CONSTRAINT `student_progress_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  ADD CONSTRAINT `tagihan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
