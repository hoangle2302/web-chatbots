-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 03, 2025 lúc 08:31 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `thuvien_ai`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ai_query_history`
--

CREATE TABLE `ai_query_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` text NOT NULL,
  `response` longtext NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `tokens_used` int(11) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `prompt_tokens` int(11) DEFAULT 0,
  `completion_tokens` int(11) DEFAULT 0,
  `latency_ms` int(11) DEFAULT 0,
  `status` varchar(30) DEFAULT 'ok'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `checksum` varchar(128) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `detail` varchar(500) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `detail`, `timestamp`, `ip_address`, `user_agent`) VALUES
(1, 1, 'admin_login_success', 'Admin logged in: admin', '2025-10-09 12:38:29', NULL, NULL),
(2, 1, 'update', 'User profile updated', '2025-10-09 13:03:51', NULL, NULL),
(3, 2, 'user_registered', 'User registered: test', '2025-10-19 10:00:42', NULL, NULL),
(4, 2, 'login_success', 'User logged in: test', '2025-10-19 10:03:18', NULL, NULL),
(5, 1, 'admin_login_success', 'Admin logged in: admin', '2025-10-19 10:07:19', NULL, NULL),
(6, 1, 'admin_login_success', 'Admin logged in: admin', '2025-10-19 10:07:45', NULL, NULL),
(7, 1, 'admin_login_success', 'Admin logged in: admin', '2025-10-19 10:10:10', NULL, NULL),
(8, 3, 'user_register', 'User đăng ký: testuser_1760870847', '2025-10-19 10:47:28', '127.0.0.1', 'Test-Script/1.0'),
(9, 1, 'user_login', 'User đăng nhập: admin', '2025-10-19 10:47:28', '127.0.0.1', 'Test-Script/1.0'),
(10, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 10:52:22', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(11, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 10:52:30', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(12, 1, 'user_login', 'User đăng nhập: admin', '2025-10-19 10:52:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(13, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-19 10:54:00', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(14, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 10:54:39', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(15, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 10:55:40', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(16, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 10:55:54', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(17, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 11:48:28', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(18, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-19 11:55:39', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(19, 2, 'user_login', 'User đăng nhập: test', '2025-10-19 12:12:13', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(20, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-19 12:13:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(21, 2, 'admin_update_credits', 'Admin cập nhật credits: set 5000 (từ 5000 thành 5000)', '2025-10-19 12:15:23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(22, 2, 'admin_update_credits', 'Admin cập nhật credits: set 60000 (từ 5000 thành 60000)', '2025-10-19 12:15:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(23, 2, 'admin_update_credits', 'Admin cập nhật credits: set 70000 (từ 60000 thành 70000)', '2025-10-19 12:17:16', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(24, 2, 'admin_update_credits', 'Admin cập nhật credits: set 4000 (từ 70000 thành 4000)', '2025-10-19 12:19:23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(25, 2, 'admin_update_credits', 'Admin cập nhật credits: set 5000 (từ 4000 thành 5000)', '2025-10-19 12:21:41', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(26, 2, 'admin_update_credits', 'Admin cập nhật credits: add 500000 (từ 5000 thành 505000)', '2025-10-19 12:23:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(27, 2, 'admin_update_credits', 'Admin cập nhật credits: add 70000000 (từ 505000 thành 70505000)', '2025-10-19 12:25:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(28, 2, 'admin_update_credits', 'Admin cập nhật credits: add 1 (từ 70505000 thành 70505001)', '2025-10-19 12:27:02', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(29, 2, 'admin_update_credits', 'Admin cập nhật credits: add 1 (từ 70505001 thành 70505002)', '2025-10-19 12:28:47', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(30, 2, 'admin_update_credits', 'Admin cập nhật credits: add 1 (từ 70505002 thành 70505003)', '2025-10-19 12:35:10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(31, 4, 'user_register', 'User đăng ký: test12345', '2025-10-24 07:54:34', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(32, 8, 'user_login', 'User đăng nhập: testuser1761294097', '2025-10-24 08:21:39', '127.0.0.1', ''),
(33, 8, 'user_login', 'User đăng nhập: testuser1761294097', '2025-10-24 08:22:03', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(34, 8, 'user_login', 'User đăng nhập: testuser1761294097', '2025-10-24 08:24:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(35, 9, 'user_register', 'User đăng ký: testkid', '2025-10-24 08:27:41', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(36, 9, 'user_login', 'User đăng nhập: testkid', '2025-10-24 08:27:56', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(37, 10, 'user_register', 'User đăng ký: testuser550263564', '2025-10-24 08:55:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584'),
(38, 11, 'user_register', 'User đăng ký: test3', '2025-10-24 09:01:49', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(39, 11, 'user_login', 'User đăng nhập: test3', '2025-10-24 09:02:05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(40, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-24 09:05:09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(41, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-24 15:56:08', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(42, 1, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-24 16:10:48', '127.0.0.1', ''),
(43, 12, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-24 16:34:59', '127.0.0.1', ''),
(44, 12, 'admin_login_success', 'Admin đăng nhập: admin', '2025-10-24 16:37:24', '127.0.0.1', ''),
(45, 1, 'admin_modify_credits', 'Admin modify credits: add 100 (từ 0 thành 100)', '2025-10-24 16:37:24', '127.0.0.1', ''),
(46, 9, 'admin_modify_credits', 'Admin modify credits: set 100020 (từ 10 thành 100020)', '2025-10-24 16:38:06', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(47, 11, 'admin_modify_credits', 'Admin modify credits: add 100 (từ 10 thành 110)', '2025-10-24 16:49:31', '127.0.0.1', ''),
(48, 9, 'admin_modify_credits', 'Admin modify credits: set 12000 (từ 100020 thành 12000)', '2025-10-24 16:50:23', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(49, 9, 'admin_modify_credits', 'Admin modify credits: set 10000 (từ 12000 thành 10000)', '2025-10-24 17:17:36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(50, 9, 'user_login', 'User đăng nhập: testkid', '2025-10-25 03:06:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(51, 9, 'admin_modify_credits', 'Admin modify credits: set 50000 (từ 10000 thành 50000)', '2025-10-25 03:35:58', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36'),
(52, 9, 'user_login', 'User đăng nhập: testkid', '2025-11-01 05:56:25', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(53, 12, 'admin_login_success', 'Admin đăng nhập: admin', '2025-11-01 06:27:13', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(54, 9, 'admin_modify_credits', 'Admin modify credits: set 100000 (từ 50000 thành 100000)', '2025-11-01 06:27:31', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(55, 9, 'user_login', 'User đăng nhập: testkid', '2025-11-01 12:35:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(56, 9, 'user_login', 'User đăng nhập: testkid', '2025-11-01 12:46:12', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(57, 13, 'user_register', 'User đăng ký: trnbang04', '2025-11-01 13:39:07', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(58, 14, 'user_register', 'User đăng ký: trnbng04', '2025-11-01 13:47:59', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(59, 14, 'user_login', 'User đăng nhập: trnbng04', '2025-11-01 13:48:19', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(60, 15, 'user_register', 'User đăng ký: hngle', '2025-11-01 13:55:05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(61, 15, 'user_login', 'User đăng nhập: hngle', '2025-11-01 13:55:21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(62, 12, 'admin_login_success', 'Admin đăng nhập: admin', '2025-11-01 13:56:10', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(63, 16, 'user_register', 'User đăng ký: bang', '2025-11-02 06:39:39', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(64, 16, 'user_login', 'User đăng nhập: bang', '2025-11-02 06:39:58', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(65, 16, 'admin_modify_credits', 'Admin modify credits: set 50000 (từ 10 thành 50000)', '2025-11-02 06:41:55', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(66, 9, 'user_login', 'User đăng nhập: testkid', '2025-11-02 16:05:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(67, 17, 'user_register', 'User đăng ký: okkkk', '2025-11-03 07:01:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(68, 17, 'user_login', 'User tự động đăng nhập sau đăng ký: okkkk', '2025-11-03 07:01:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(69, 18, 'user_register', 'User đăng ký: hngle11', '2025-11-03 07:26:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(70, 18, 'user_login', 'User tự động đăng nhập sau đăng ký: hngle11', '2025-11-03 07:26:53', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `failed_login_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `credits` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `is_active`, `failed_login_count`, `created_at`, `updated_at`, `email`, `display_name`, `last_login_at`, `credits`) VALUES
(1, '', '$2y$12$mfpJsNIqJDAOd7KZ65PfRuhU1UBSgc1jRtJQkzeeYzGkocZ0UfouO', 'admin', 1, 0, '2025-10-09 12:38:29', '2025-10-24 16:37:24', NULL, NULL, '2025-10-24 16:10:48', 100),
(2, 'test', '$2y$12$zVhWCVIqlmNeng.bvm1nm.Lrtup4q/luwE/i2a/6l80oDBgIj.R26', 'user', 1, 0, '2025-10-19 10:00:42', '2025-10-19 12:35:10', 'kvkjbvks@gmail.com', 'test', '2025-10-19 12:12:13', 70505003),
(3, 'testuser_1760870847', '$2y$12$wTrEQNdBJ./vBNgYNDDlCOK7vNGDLH6JPSumYl0br1JkOMAp1nJ0q', 'user', 1, 0, '2025-10-19 10:47:28', '2025-10-19 10:47:28', 'test@example.com', 'Test User', NULL, 10),
(4, 'test12345', '$2y$12$.rEqShoiQynMzz2CYvbkR..cnNvg//HJ1AWRQnf8r7N.o9GnpXdai', 'user', 1, 0, '2025-10-24 07:54:34', '2025-10-24 07:54:34', 'kgjfdhkgjdfshkgj@gmail.com', 'test12345', NULL, 10),
(8, 'testuser1761294097', '$2y$12$ydhvOEjA4b34ubBfKvpmLe3t3A5lwcTFOGk8scbkd/Oy0wIR1I32C', 'user', 1, 0, '2025-10-24 08:21:38', '2025-10-24 08:24:53', 'test1761294097@example.com', 'Test User', '2025-10-24 08:24:53', 100),
(9, 'testkid', '$2y$12$xSMNLjOe/BJtqQv0tXXwI.xfDGEHqYr8einvAGMKTLToCJgwrZBeG', 'user', 1, 0, '2025-10-24 08:27:41', '2025-11-02 16:11:36', 'jhlkuh@gmail.com', 'testkid', '2025-11-02 16:05:45', 99997),
(10, 'testuser550263564', '$2y$12$9gL1YNdBVxtp7BATc/F6Be/kxYQ90gIn6ER.yVhwo.rd75D6L85Gm', 'user', 1, 0, '2025-10-24 08:55:53', '2025-10-24 08:55:53', 'test910869938@example.com', NULL, NULL, 10),
(11, 'test3', '$2y$12$VDV7I6kPkDK72mMjGSJ5L.PPcRtDyLDEypVspfl6D1/mwBxXj3pxm', 'user', 1, 0, '2025-10-24 09:01:49', '2025-10-24 16:49:31', 'jhgjhlg@gmai.com', 'test3', '2025-10-24 09:02:05', 110),
(12, 'admin', '$2y$12$eRXcY2pmNbJhzOszhElTG.nR4siNhn3Y0Xw3OacHDoEkt7r1VloeS', 'admin', 1, 0, '2025-10-24 16:33:58', '2025-11-01 13:56:10', 'admin@example.com', 'Administrator', '2025-11-01 13:56:10', 1000000),
(13, 'trnbang04', '$2y$12$BR4Hug6LL3dU1AV9yU3BuO/G/hxNXvPCJLyCPMqK65JrI32TumYnq', 'user', 1, 0, '2025-11-01 13:39:07', '2025-11-01 13:39:07', 'flkdgkdfjgkdfsj@gmail.com', 'trnbang04', NULL, 10),
(14, 'trnbng04', '$2y$12$kGACd5oI3RbMJgbCC3PsKuMwo0.vZS5HG8AWjtu1rXqJ7EpkRHvsm', 'user', 1, 0, '2025-11-01 13:47:59', '2025-11-01 13:48:19', 'gkfgkjfds@gmail.com', 'trnbng04', '2025-11-01 13:48:19', 10),
(15, 'hngle', '$2y$12$JtUvGEgoKap4faGqOtBJG.ih5aZsv3tL8MkGfkJFoKWtI5965TlpW', 'user', 1, 0, '2025-11-01 13:55:05', '2025-11-01 13:55:21', 'gsjdf@gmail.com', 'hngle', '2025-11-01 13:55:21', 10),
(16, 'bang', '$2y$12$4SiTg8U2T1dfuYQMag6./OT/zWutxiCeIWjlaPMLky2FSkrN9G3PO', 'user', 1, 0, '2025-11-02 06:39:39', '2025-11-02 06:41:55', 'dlaksjdias@gmail.com', 'bang', '2025-11-02 06:39:58', 50000),
(17, 'okkkk', '$2y$12$i2x325vNDBJYF7RsKU/..uHAeST3IscPmdktYcXRufXqPytcaJCxu', 'user', 1, 0, '2025-11-03 07:01:44', '2025-11-03 07:01:44', 'gzsdfgfsd@gmail.com', 'okkkk', NULL, 10),
(18, 'hngle11', '$2y$12$8pKlTpKu8ISc6ZoHk3vqSOWykcyeownz2/J5DpqlapOQ5sjdEtl7q', 'user', 1, 0, '2025-11-03 07:26:53', '2025-11-03 07:26:53', 'fsdefsd@gmail.com', 'hngle11', NULL, 10);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ai_query_history`
--
ALTER TABLE `ai_query_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_timestamp` (`user_id`,`timestamp`),
  ADD KEY `idx_model` (`model`);

--
-- Chỉ mục cho bảng `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Chỉ mục cho bảng `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_user_time` (`user_id`,`timestamp`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_active_created` (`is_active`,`created_at`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ai_query_history`
--
ALTER TABLE `ai_query_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `ai_query_history`
--
ALTER TABLE `ai_query_history`
  ADD CONSTRAINT `ai_query_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
