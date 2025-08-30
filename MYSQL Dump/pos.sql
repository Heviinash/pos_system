-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 09:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `businessregistration`
--

CREATE TABLE `businessregistration` (
  `business_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email_address` varchar(100) NOT NULL,
  `tax_identification_number` varchar(50) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `companylogo` varchar(255) DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businessregistration`
--

INSERT INTO `businessregistration` (`business_name`, `registration_number`, `address`, `contact_number`, `email_address`, `tax_identification_number`, `business_type`, `created_at`, `updated_at`, `companylogo`, `bank`, `account_number`) VALUES
('Win Win Enterprise', '1234567890', '23, Jalan Air Itam, 11500 Ayer Itam, Pulau Pinang', '0169432209', 'winwiwn@gmail.com', NULL, 'POS ', '2024-10-16 06:58:37', '2024-12-01 13:48:31', 'uploads/blackbackground.jpg', 'CitiBank', '111-222-333');

-- --------------------------------------------------------

--
-- Table structure for table `companymemberships`
--

CREATE TABLE `companymemberships` (
  `companymembership_id` varchar(50) NOT NULL,
  `companyregistration` varchar(100) DEFAULT NULL,
  `membership_expirydate` date DEFAULT NULL,
  `membership_points` int(11) DEFAULT 0,
  `membership_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companymemberships`
--

INSERT INTO `companymemberships` (`companymembership_id`, `companyregistration`, `membership_expirydate`, `membership_points`, `membership_type`) VALUES
('COMEMBERYtB0', '030922070863', '2024-09-07', 20, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `companyregistration`
--

CREATE TABLE `companyregistration` (
  `companyregistration` varchar(100) NOT NULL,
  `companyname` varchar(255) NOT NULL,
  `companyssmform` longblob DEFAULT NULL,
  `companyid` varchar(50) NOT NULL,
  `industrytype` varchar(100) DEFAULT NULL,
  `companyemail` varchar(255) DEFAULT NULL,
  `companyphone` varchar(20) DEFAULT NULL,
  `contactpersonname` varchar(255) DEFAULT NULL,
  `contactpersonmobile` varchar(20) DEFAULT NULL,
  `contactpersonemail` varchar(255) DEFAULT NULL,
  `contactpersonname2` varchar(255) DEFAULT NULL,
  `contactpersonmobile2` varchar(20) DEFAULT NULL,
  `contactpersonemail2` varchar(255) DEFAULT NULL,
  `membership` enum('yes','no') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `banktype` varchar(50) DEFAULT NULL,
  `accountnumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companyregistration`
--

INSERT INTO `companyregistration` (`companyregistration`, `companyname`, `companyssmform`, `companyid`, `industrytype`, `companyemail`, `companyphone`, `contactpersonname`, `contactpersonmobile`, `contactpersonemail`, `contactpersonname2`, `contactpersonmobile2`, `contactpersonemail2`, `membership`, `created_at`, `banktype`, `accountnumber`) VALUES
('030922070863', 'Nash Enterprise', 0x53432d393030204e6f746573202d204d6f64756c6520312e706466, 'COMBERIFZRV', 'technology', 'nash@gmail.com', '0169432209', 'Nash', '0164567789', 'nash@gmail.com', 'Nash22', '0143094960', 'nash22@gmail.com', 'yes', '2024-09-07 13:26:38', 'cimb', '070899773643');

-- --------------------------------------------------------

--
-- Table structure for table `customerid_settings`
--

CREATE TABLE `customerid_settings` (
  `id` int(11) NOT NULL,
  `id_type` varchar(20) NOT NULL,
  `prefix` varchar(2) NOT NULL,
  `custom_code` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customerid_settings`
--

INSERT INTO `customerid_settings` (`id`, `id_type`, `prefix`, `custom_code`) VALUES
(16, 'companymembership', 'CO', 'MEMBER'),
(17, 'customer', 'CU', 'IDPOSH'),
(18, 'company', 'CO', '241012'),
(19, 'membership', 'CM', '240904');

-- --------------------------------------------------------

--
-- Table structure for table `customermemberships`
--

CREATE TABLE `customermemberships` (
  `icnumber` varchar(255) NOT NULL,
  `membership_id` varchar(255) DEFAULT NULL,
  `membership_expirydate` date DEFAULT NULL,
  `membership_points` int(11) DEFAULT NULL,
  `membership_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customermemberships`
--

INSERT INTO `customermemberships` (`icnumber`, `membership_id`, `membership_expirydate`, `membership_points`, `membership_type`) VALUES
('030922070863', 'MU240901MqtP', '2025-09-22', 20, 'None'),
('22092003', 'CM240904AD7R', '2025-09-12', 20, 'Basic');

-- --------------------------------------------------------

--
-- Table structure for table `customerregistration`
--

CREATE TABLE `customerregistration` (
  `name` varchar(255) NOT NULL,
  `icnumber` varchar(255) NOT NULL,
  `customerid` varchar(255) NOT NULL,
  `dateofbirth` date NOT NULL,
  `gender` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `membership` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customerregistration`
--

INSERT INTO `customerregistration` (`name`, `icnumber`, `customerid`, `dateofbirth`, `gender`, `email`, `address`, `phone`, `membership`) VALUES
('Nash', '030922070863', 'CU240901c0ZM', '2003-09-22', 'Male', 'nash@gmail.com', NULL, '0169432209', 'yes'),
('Heviinash Parugavelu', '22092003', 'CUSREGID632X', '2003-09-22', 'Male', 'heviinash@gmail.com', '2E-01-13, Medan Angsana 1', '0169432209', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `employeedetails`
--

CREATE TABLE `employeedetails` (
  `fullname` varchar(255) NOT NULL,
  `icnumber` varchar(20) NOT NULL,
  `dateofbirth` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `employmentdate` date NOT NULL,
  `employeeid` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employeedetails`
--

INSERT INTO `employeedetails` (`fullname`, `icnumber`, `dateofbirth`, `gender`, `contactnumber`, `address`, `employmentdate`, `employeeid`, `username`, `password`, `role_name`, `created_at`) VALUES
('Harishiini Parugavelu', '000923072309', '2000-09-23', 'Female', '0161232309', '2E-01-13, Medan Angsana 1', '2024-10-03', '000923', 'harishiini', 'Harishiini@23092000', 'Guest', '2024-10-03 15:04:16'),
('Heviinash Parugavelu', '030922070863', '2003-09-22', 'Male', '0169432209', '2E-01-13, Medan Angsana 1', '2024-09-05', '030922', 'Heviinash', 'Heviinash@22', 'Admin', '2024-09-05 11:53:07'),
('Asteria', '123456', '1997-10-22', 'Male', '0161232309', '2E-01-13, Medan Angsana 1', '2024-10-21', '6543', 'Asteria65', '1234567', 'Guest', '2024-10-21 17:18:09'),
('Hevii', '12345678', '2003-09-22', 'Male', '1234566789', 'malaysia', '2024-11-08', '0309', '0309', 'Heviinash@22', 'OfficeClerk', '2024-11-08 06:09:49'),
('Nash Parugavelu', '22092003', '2003-09-22', 'Male', '0169432209', '2E-01-13, Medan Angsana 1', '2024-09-21', '22092003030922', 'Nash', 'Nash@22092003', 'Technician', '2024-09-21 06:43:12');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `userid` varchar(255) NOT NULL,
  `expense_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `status` enum('Pending','Approved','Acknowledge') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` varchar(255) NOT NULL,
  `decision_notes` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `payment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `username`, `userid`, `expense_type`, `amount`, `date`, `vendor`, `notes`, `attachments`, `status`, `created_at`, `updated_at`, `approved_by`, `decision_notes`, `approved_at`, `payment`) VALUES
(1, 'Heviinash', '030922', 'Utilities', 250.00, '2024-11-21', 'Nash', 'Jadual testing', 'uploads/JADUAL PEMANTAUAN LATIHAN INDUSTRI SESI 1 2024_2025 FASA 2 (1).xlsx', 'Approved', '2024-11-20 17:12:56', '2024-11-21 14:27:35', '30922', NULL, '2024-11-21 14:27:35', 'Cash'),
(2, 'Heviinash', '030922', 'Advertising', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Approved', '2024-11-20 17:19:34', '2024-11-20 17:20:20', '30922', NULL, '2024-11-20 17:20:20', 'Cash'),
(3, 'Heviinash', '030922', 'Advertising', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Acknowledge', '2024-11-20 17:19:37', '2024-11-20 17:20:21', '30922', NULL, '2024-11-20 17:20:21', 'Cash'),
(4, 'Heviinash', '030922', 'Advertising', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Acknowledge', '2024-11-20 17:19:39', '2024-11-20 17:20:23', '30922', NULL, '2024-11-20 17:20:23', 'Cash'),
(5, 'Heviinash', '030922', 'Restock Expenses', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Approved', '2024-11-20 17:19:47', '2024-11-20 17:20:22', '30922', NULL, '2024-11-20 17:20:22', 'Cash'),
(6, 'Heviinash', '030922', 'Taxes', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Approved', '2024-11-20 17:19:56', '2024-11-20 17:20:28', '30922', NULL, '2024-11-20 17:20:28', 'Cash'),
(7, 'Heviinash', '030922', 'Supplies', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Acknowledge', '2024-11-20 17:20:02', '2024-11-20 17:20:24', '30922', NULL, '2024-11-20 17:20:24', 'Cash'),
(8, 'Heviinash', '030922', 'Rent', 0.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Approved', '2024-11-20 17:20:12', '2024-11-20 17:20:26', '30922', NULL, '2024-11-20 17:20:26', 'Cash'),
(9, 'Heviinash', '030922', 'Account Payable - Supplies', 250.00, '2024-11-21', 'Heviinash Parugavelu', 'Ledger', 'uploads/ledger.xlsx', 'Approved', '2024-11-20 17:41:09', '2024-11-21 14:27:36', '30922', NULL, '2024-11-21 14:27:36', 'Cash'),
(10, 'Heviinash', '030922', 'Advertising', 20.00, '2024-11-21', 'Vogue', 'Vogue Magazine', 'uploads/quotation_Heviinash_Parugavelu_2024-11-21_08-21-24.pdf', 'Approved', '2024-11-21 12:47:15', '2024-11-21 12:47:44', '30922', NULL, '2024-11-21 12:47:44', 'Cash'),
(11, 'Heviinash', '030922', 'Restock Expenses', 250.00, '2024-11-21', 'Nash', 'Sales Report', 'uploads/sales_report.xlsx', 'Approved', '2024-11-21 14:19:08', '2024-11-21 14:20:52', '30922', NULL, '2024-11-21 14:20:52', 'Cash'),
(12, 'Raven', '22092003', 'Rent', 450.00, '2024-11-18', 'Heviinash Parugavelu', 'Taxes evidence', 'uploads/sales_tax_data.xlsx', 'Approved', '2024-11-21 14:20:44', '2024-11-21 14:20:55', '30922', NULL, '2024-11-21 14:20:55', 'Cash'),
(13, 'Luke', '98765', 'Taxes', 200.00, '2024-11-21', 'Tax Officer', 'Tax ', 'uploads/sales_tax_data (2).xlsx', 'Approved', '2024-11-21 14:24:47', '2024-11-21 14:25:15', '30922', NULL, '2024-11-21 14:25:15', 'Cash'),
(14, 'Grey', '654321', 'Advertising', 200.00, '2024-10-31', 'Bazaar', 'Invoice', 'uploads/Latest Invoice.pdf', 'Approved', '2024-11-21 15:07:34', '2024-11-21 15:08:20', '30922', NULL, '2024-11-21 15:08:20', 'Cash'),
(15, 'Heviinash', '030922', 'Utilities', 200.00, '2024-11-27', 'Nash', 'Payment will be done before 30/11/2024', 'uploads/Roadmap to the full stack.pdf', 'Acknowledge', '2024-11-27 15:08:45', '2024-11-27 15:09:20', '30922', NULL, '2024-11-27 15:09:20', 'Account Payable');

-- --------------------------------------------------------

--
-- Table structure for table `invoicepdf_settings`
--

CREATE TABLE `invoicepdf_settings` (
  `setting_id` int(11) NOT NULL,
  `watermark_text` varchar(255) DEFAULT NULL,
  `font_size` int(11) DEFAULT NULL,
  `font_family` varchar(100) DEFAULT NULL,
  `company_info_position` varchar(50) DEFAULT NULL,
  `sender_info_position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoicepdf_settings`
--

INSERT INTO `invoicepdf_settings` (`setting_id`, `watermark_text`, `font_size`, `font_family`, `company_info_position`, `sender_info_position`) VALUES
(7, 'Heviinash', 12, 'helvetica', NULL, 'topright');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `employeeid` varchar(50) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`employeeid`, `login_time`, `logout_time`) VALUES
('030922', '2024-11-07 07:34:59', '2024-11-07 07:35:13'),
('030922', '2024-11-07 14:40:32', '2024-11-07 14:41:02'),
('030922', '2024-11-07 14:44:36', '2024-11-07 14:48:21'),
('030922', '2024-11-07 14:57:08', NULL),
('030922', '2024-11-07 15:08:54', '2024-11-07 15:15:01'),
('030922', '2024-11-07 15:15:35', '2024-11-07 15:15:42'),
('030922', '2024-11-07 22:38:53', NULL),
('030922', '2024-11-08 12:57:27', '2024-11-08 14:17:17'),
('030922', '2024-11-09 21:34:35', NULL),
('030922', '2024-11-10 13:08:46', '2024-11-10 13:26:51'),
('030922', '2024-11-10 13:50:53', '2024-11-10 14:56:55'),
('030922', '2024-11-10 21:34:21', NULL),
('030922', '2024-11-10 21:39:34', NULL),
('030922', '2024-11-11 12:27:48', NULL),
('030922', '2024-11-11 21:23:35', NULL),
('030922', '2024-11-11 22:55:20', NULL),
('030922', '2024-11-12 15:04:40', NULL),
('030922', '2024-11-12 21:04:53', NULL),
('030922', '2024-11-13 01:38:33', NULL),
('030922', '2024-11-13 13:29:00', '2024-11-13 15:29:37'),
('030922', '2024-11-13 15:29:49', NULL),
('030922', '2024-11-13 15:31:59', '2024-11-13 15:34:07'),
('030922', '2024-11-13 15:34:18', NULL),
('030922', '2024-11-13 19:45:34', NULL),
('030922', '2024-11-14 11:49:01', NULL),
('030922', '2024-11-14 16:04:58', NULL),
('030922', '2024-11-14 20:19:03', '2024-11-14 23:54:45'),
('030922', '2024-11-14 23:55:57', NULL),
('030922', '2024-11-14 23:59:52', '2024-11-15 00:02:14'),
('030922', '2024-11-15 00:02:45', '2024-11-15 00:48:49'),
('030922', '2024-11-15 00:55:03', '2024-11-15 00:57:49'),
('030922', '2024-11-15 00:58:04', NULL),
('030922', '2024-11-15 00:59:56', NULL),
('030922', '2024-11-15 12:33:14', NULL),
('030922', '2024-11-15 14:47:53', NULL),
('030922', '2024-11-16 00:31:44', NULL),
('030922', '2024-11-16 00:55:48', NULL),
('030922', '2024-11-16 13:13:18', NULL),
('030922', '2024-11-16 18:37:07', '2024-11-16 18:37:13'),
('030922', '2024-11-16 18:37:29', '2024-11-16 18:45:13'),
('030922', '2024-11-16 18:45:23', NULL),
('030922', '2024-11-16 21:44:06', NULL),
('030922', '2024-11-17 13:54:29', NULL),
('030922', '2024-11-17 21:24:07', NULL),
('030922', '2024-11-18 16:12:23', NULL),
('030922', '2024-11-18 21:22:42', NULL),
('030922', '2024-11-20 11:24:53', NULL),
('030922', '2024-11-20 19:34:26', NULL),
('030922', '2024-11-20 21:41:41', NULL),
('030922', '2024-11-21 13:55:01', NULL),
('030922', '2024-11-21 14:29:09', '2024-11-21 15:26:10'),
('030922', '2024-11-21 20:00:38', '2024-11-22 01:09:30'),
('030922', '2024-11-22 22:43:53', NULL),
('030922', '2024-11-23 11:21:19', NULL),
('030922', '2024-11-24 19:20:21', NULL),
('030922', '2024-11-24 21:41:08', NULL),
('030922', '2024-11-25 12:32:14', NULL),
('030922', '2024-11-25 17:05:40', NULL),
('030922', '2024-11-25 17:09:45', NULL),
('030922', '2024-11-25 21:43:57', '2024-11-25 23:20:59'),
('030922', '2024-11-25 23:21:42', NULL),
('030922', '2024-11-25 23:26:37', '2024-11-25 23:27:54'),
('030922', '2024-11-25 23:28:28', NULL),
('030922', '2024-11-25 23:31:11', '2024-11-25 23:44:28'),
('030922', '2024-11-25 23:44:47', NULL),
('030922', '2024-11-26 00:43:00', NULL),
('030922', '2024-11-26 00:46:27', '2024-11-26 00:47:23'),
('030922', '2024-11-26 11:37:45', NULL),
('030922', '2024-11-26 12:46:53', NULL),
('030922', '2024-11-26 12:59:39', NULL),
('030922', '2024-11-26 13:17:27', '2024-11-26 14:19:27'),
('030922', '2024-11-26 14:19:45', NULL),
('030922', '2024-11-26 20:55:08', NULL),
('030922', '2024-11-27 11:39:44', NULL),
('030922', '2024-11-27 19:55:51', NULL),
('030922', '2024-11-28 11:33:13', '2024-11-28 13:21:46'),
('030922', '2024-11-28 13:22:06', NULL),
('030922', '2024-11-28 13:23:24', NULL),
('030922', '2024-11-28 13:24:11', NULL),
('030922', '2024-11-28 16:39:15', '2024-11-28 18:09:16'),
('030922', '2024-11-28 18:13:19', NULL),
('030922', '2024-11-28 18:18:03', '2024-11-28 18:18:36'),
('030922', '2024-11-28 21:55:11', NULL),
('030922', '2024-11-29 10:41:41', '2024-11-29 11:12:01'),
('030922', '2024-12-01 11:47:40', '2024-12-01 11:52:17'),
('030922', '2024-12-01 21:47:07', '2024-12-01 21:50:27'),
('030922', '2024-12-02 23:20:06', '2024-12-02 23:20:10'),
('0309', '2024-12-02 23:21:14', NULL),
('030922', '2024-12-03 14:02:47', '2024-12-03 14:16:29'),
('030922', '2024-12-04 11:23:09', NULL),
('030922', '2024-12-04 11:24:36', '2024-12-04 11:34:25'),
('030922', '2024-12-04 11:35:04', '2024-12-04 12:34:47'),
('030922', '2024-12-04 13:07:55', NULL),
('030922', '2024-12-27 23:32:32', '2024-12-27 23:56:48'),
('030922', '2024-12-31 19:55:24', NULL),
('030922', '2025-01-01 14:40:28', NULL),
('030922', '2025-01-03 22:45:44', '2025-01-03 23:18:01'),
('030922', '2025-01-08 00:01:02', '2025-01-08 00:32:36'),
('030922', '2025-01-12 22:34:14', '2025-01-12 22:36:34'),
('030922', '2025-01-24 00:11:01', '2025-01-24 00:14:29'),
('030922', '2025-02-14 20:45:23', '2025-02-14 20:48:33'),
('030922', '2025-03-10 16:48:34', '2025-03-10 16:49:13'),
('030922', '2025-03-10 16:51:24', '2025-03-10 16:56:09'),
('030922', '2025-03-10 17:01:25', '2025-03-10 17:09:11'),
('030922', '2025-05-20 21:34:43', '2025-05-20 21:38:56'),
('030922', '2025-05-20 21:40:56', '2025-05-20 21:43:15'),
('0309', '2025-05-20 21:43:31', '2025-05-20 21:44:14'),
('030922', '2025-08-05 23:21:11', NULL),
('030922', '2025-08-05 23:42:10', NULL),
('030922', '2025-08-11 13:56:14', '2025-08-11 14:10:27'),
('030922', '2025-08-21 20:28:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pdf_customization_settings`
--

CREATE TABLE `pdf_customization_settings` (
  `setting_id` int(11) NOT NULL,
  `watermark_text` varchar(255) DEFAULT NULL,
  `header_text` varchar(255) DEFAULT NULL,
  `footer_text` varchar(255) DEFAULT NULL,
  `font_size` int(11) DEFAULT 12,
  `font_family` varchar(50) DEFAULT 'helvetica',
  `company_info_position` varchar(50) DEFAULT 'top-left',
  `sender_info_position` varchar(50) DEFAULT 'bottom-right'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pdf_customization_settings`
--

INSERT INTO `pdf_customization_settings` (`setting_id`, `watermark_text`, `header_text`, `footer_text`, `font_size`, `font_family`, `company_info_position`, `sender_info_position`) VALUES
(1, 'Quotation', 'Heviinash Enterprise', 'Nash Enterprise', 11, 'times', 'left', 'left');

-- --------------------------------------------------------

--
-- Table structure for table `pdf_settings`
--

CREATE TABLE `pdf_settings` (
  `id` int(11) NOT NULL,
  `add_watermark` tinyint(1) DEFAULT 0,
  `watermark_text` varchar(255) DEFAULT NULL,
  `font_size` int(11) DEFAULT 10,
  `position` enum('top-left','top-right','center','bottom-left') DEFAULT 'top-left',
  `layout` enum('side-by-side','top-bottom') DEFAULT 'side-by-side',
  `include_header_footer` tinyint(1) DEFAULT 1,
  `header_text` varchar(255) DEFAULT 'Quotation',
  `footer_text` varchar(255) DEFAULT 'Thank you for your business!',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_name`, `description`) VALUES
('accdashboard.php', 'AccountantDashboard'),
('addingproduct.php', 'Adding Product'),
('add_inventory.php', 'Add New Service Product/Items'),
('add_service.php', 'Create Service & Assign Product'),
('barcodetestpayment.php', 'BarcodeTestPayment'),
('comissions.php', 'Employee Commision Table'),
('companyregistration.php', 'Company Registration'),
('customerregistration.php', 'Customer Registration'),
('customer_followup.php', 'Customer Followup Date'),
('customer_log.php', 'Log Customer Visit'),
('customer_purchase_history.php', 'Customer Purchase History'),
('financialdashboard.php', 'Financial Dashboard'),
('inventorytrack.php', 'Track Inventory'),
('invoicepdfsettings.php', 'InvoicePDFSettings'),
('loginhistory.php', 'View User Log History'),
('mainpage.php', 'Main Page'),
('manageuserrolemanagement.php', 'Manage User Roles'),
('manage_permission.php', 'Manage Permission'),
('paymenttesting.php', 'Pay Test'),
('promo_setup.php', 'Promotion Setup'),
('properpaymenttesting.php', 'PaymentTestProper'),
('quickinvoice.php', 'QuickInvoice'),
('quotationpdfsettings.php', 'QuotationPDFSettings'),
('request_restock.php', 'Request Restock'),
('restock.php', 'Manual Restock'),
('rolepermissionview.php', 'View Role Permission'),
('save_id_settings.php', 'Custom ID Generator'),
('systemuserregistration.php', 'System User Registration'),
('transaction_details.php', 'View Receipt'),
('trialpaymenttest.php', 'Perform Sales'),
('userrolemanagement.php', 'User Role Management'),
('viewcustomerlogs.php', 'View Customer Logs'),
('viewinventory.php', 'Inventory');

-- --------------------------------------------------------

--
-- Table structure for table `productinventorytable`
--

CREATE TABLE `productinventorytable` (
  `productpic_url` varchar(255) DEFAULT NULL,
  `brand` varchar(100) NOT NULL,
  `producttype` varchar(100) NOT NULL,
  `variant` varchar(150) DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `wholesaleprice` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `status` enum('Available','Out of Stock','Discontinue') NOT NULL DEFAULT 'Available',
  `last_restocked_by` varchar(255) DEFAULT NULL,
  `product_added_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productinventorytable`
--

INSERT INTO `productinventorytable` (`productpic_url`, `brand`, `producttype`, `variant`, `sku`, `barcode`, `price`, `wholesaleprice`, `stock`, `status`, `last_restocked_by`, `product_added_by`, `created_at`, `updated_at`) VALUES
('uploads/Screenshot 2024-09-17 183743.png', 'Derma', 'Facial Moisturizer', 'Ceramide 25g', '', '85463782', 76.00, 65.00, 17, 'Available', 'Heviinash', 'Heviinash', '2024-10-04 02:15:03', '2025-01-23 16:12:30'),
('uploads/Screenshot 2024-09-17 184929.png', 'Vaseline Niveas', 'Lip Balm', 'Rosy Lips', '0025', '243567898', 120.00, 100.00, 36, 'Available', '030922', NULL, '2024-09-24 07:46:05', '2024-12-04 05:09:43'),
('uploads/Screenshot 2024-09-17 184907.png', 'Hada Labu', 'Body Lotion', 'Sensitive Skin 100ml', '023', '235467890', 78.00, 68.00, 42, 'Available', '030922', 'Heviinash', '2024-09-24 12:37:17', '2024-12-04 05:09:43'),
('uploads/Screenshot 2024-09-17 183553.png', 'Cerave', 'Facial Cleanser', 'Gel 500ml', '12', '67235467', 28.00, 20.00, 23, 'Available', NULL, 'Heviinash', '2024-10-04 02:20:04', '2024-12-04 03:31:05'),
('uploads/Screenshot 2024-09-17 183728.png', 'Cerave', 'Facial Serum', 'Oily Skin', '1234', '7654321', 70.00, 50.00, 18, 'Available', '030922', NULL, '2024-09-24 00:48:01', '2025-02-14 12:47:31'),
('uploads/RAM.png', 'Kingston', 'RAM', '16GB', '16', '76542523', 450.00, 350.00, 30, 'Available', NULL, 'Heviinash', '2024-10-10 15:20:05', '2024-11-17 09:41:51'),
('uploads/castrol oil.png', 'Castrol', 'Engine Oil', 'GTX 20W', '234', '78263747', 58.00, 50.00, 12, 'Available', NULL, 'Heviinash', '2024-10-10 03:15:56', '2024-12-04 03:31:05'),
('uploads/Screenshot 2024-09-17 184432.png', 'Wardah', 'Facial Moisturizer', 'Panthenol Ceramide 50g', '56', '128746374', 25.00, 20.00, 8, 'Available', NULL, 'Heviinash', '2024-10-04 02:17:34', '2024-12-04 05:09:43'),
('uploads/Screenshot 2024-09-17 193219.png', 'Aiken', 'Facial Moisturizer', 'Vitamin C Cream 50g', '65', '84256371', 58.00, 45.00, 12, 'Available', NULL, 'Heviinash', '2024-10-04 02:22:09', '2024-12-04 05:09:43'),
('uploads/Screenshot 2024-09-17 184346.png', 'Aiken', 'Serum', 'Sensitive Skin 20ml', '8765', '254678934', 85.00, 70.00, 5, 'Available', NULL, 'Heviinash', '2024-09-28 09:33:03', '2024-12-04 03:31:05'),
('uploads/Screenshot 2024-09-17 184946.png', 'Nivea', 'Lip Balm', 'Soft Rose 5g', '98', '8425372', 15.00, 10.00, 21, 'Available', NULL, 'Heviinash', '2024-10-04 02:25:50', '2024-12-04 03:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `productsinventory`
--

CREATE TABLE `productsinventory` (
  `product_id` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_description` text DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productsinventory`
--

INSERT INTO `productsinventory` (`product_id`, `product_name`, `product_description`, `category_name`, `image_url`, `created_at`) VALUES
('12345678', 'Vaseline', 'Lip Balm', 'Lip Balm', 'uploads/Screenshot 2024-09-17 184929.png', '2024-09-19 06:21:43'),
('2', 'Cerave', 'Cream', 'Face Cleanser', 'uploads/Screenshot 2024-09-17 183728.png', '2024-09-19 13:46:50'),
('3', 'Derma Care', 'Face Cleanser', 'Face Cleanser', 'uploads/Screenshot 2024-09-17 183743.png', '2024-09-19 13:47:56'),
('7', 'Aiken', 'Facial Cream', 'Facial Cream', 'uploads/Screenshot 2024-09-17 184346.png', '2024-09-19 14:08:52'),
('765425', 'Wardah Face Wash', 'Face Wash', 'Face Moisturizer', 'uploads/Screenshot 2024-09-17 184432.png', '2024-09-19 16:12:27'),
('87', 'Skintific', 'Face Serum', 'Facial Cream', 'uploads/Screenshot 2024-09-17 183642.png', '2024-09-19 14:29:10');

-- --------------------------------------------------------

--
-- Table structure for table `profit`
--

CREATE TABLE `profit` (
  `transaction_id` varchar(255) NOT NULL,
  `profit` decimal(10,2) NOT NULL,
  `sale_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profit`
--

INSERT INTO `profit` (`transaction_id`, `profit`, `sale_datetime`) VALUES
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', 86.00, '2024-11-17 17:57:52'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', 86.00, '2024-11-17 17:58:26'),
('TIDWi82XRLgsfzmbx6MyV71T5DUPlQYr', 33.00, '2024-11-19 01:34:49'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', 62.00, '2024-11-21 20:22:14'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', 51.00, '2024-11-21 20:32:40'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', 71.00, '2024-11-21 20:50:39'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', 110.00, '2024-11-21 22:14:08'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', 61.00, '2024-11-21 22:55:09'),
('TIDLzEbFwB94kjUhZgDRC7Xd0SivYmAa', 38.00, '2024-11-22 00:37:57'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', 50.00, '2024-11-22 00:42:28'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', 50.00, '2024-11-22 00:47:46'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', 50.00, '2024-11-22 00:51:58'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', 50.00, '2024-11-22 00:52:18'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', 50.00, '2024-11-22 00:53:22'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', 63.00, '2024-11-22 01:03:09'),
('TID0rKOw468aVWGgEuIk3Mhe9JNYcDbp', 25.00, '2024-11-22 01:04:04'),
('TIDCtGpHfewFz8jRV6TDlPxmLgIbdqJ3', 25.00, '2024-11-22 01:08:38'),
('TIDcY3bjhmtUXGy5PoCi9WxuSgZ42Mzv', 45.00, '2024-11-23 11:28:47'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', 36.00, '2024-11-25 22:51:57'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', 56.00, '2024-11-26 22:44:59'),
('TIDB6Ehia8qvWzIfp7J2TKG0ZAgYc9L5', 40.00, '2024-11-26 22:47:56'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', 179.00, '2024-11-26 22:58:58'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', 48.00, '2024-11-28 22:31:17'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', 59.00, '2024-11-28 22:34:39'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', 71.00, '2024-11-28 22:37:09'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', 58.00, '2024-11-29 00:18:36'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', 77.00, '2024-11-29 00:20:16'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', 69.00, '2024-12-03 14:14:57'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', 115.00, '2024-12-04 11:31:05'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', 59.00, '2024-12-04 13:09:43'),
('TIDx7yMTlCfzE6N4b5JWnOoueiQHjXDZ', 33.00, '2025-01-24 00:12:30'),
('TID1XoOSavMhwmdUDWk84FK9TZtcl7Nb', 20.00, '2025-02-14 20:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_type` varchar(50) NOT NULL,
  `promotion_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`promotion_type`, `promotion_name`, `description`, `promo_code`, `discount_value`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
('Sale', 'Black Friday Sale', 'Friday 50% Sale', '', 30.00, '2024-10-15', '2024-10-21', '2024-10-14 13:24:51', '2024-10-14 13:24:51'),
('seasonal', 'Winter Sale', 'Winter', '', 80.00, '2024-10-14', '2024-10-21', '2024-10-14 13:25:41', '2024-10-14 13:25:41'),
('Business Promotion', 'Business', 'business to business', '', 40.00, '2024-10-17', '2024-10-30', '2024-10-16 06:30:25', '2024-10-16 06:30:25'),
('Business to Business', 'Business Promo', 'business', '', 40.00, '2024-10-16', '2025-02-16', '2024-10-16 06:32:44', '2024-10-16 06:32:44'),
('Black Friday', 'Black Friday Sale', 'Friday 40', '', 40.00, '2024-10-25', '2024-10-27', '2024-10-21 17:15:45', '2024-10-21 17:15:45'),
('seasonal', 'Black Friday Sale', 'Black Friday Sale', '', 70.00, '2024-11-26', '2024-12-07', '2024-11-26 06:30:26', '2024-11-26 06:30:26');

-- --------------------------------------------------------

--
-- Table structure for table `quotation`
--

CREATE TABLE `quotation` (
  `id` int(11) NOT NULL,
  `log_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_id` varchar(50) NOT NULL,
  `contact_info` varchar(100) DEFAULT NULL,
  `problem_desc` text DEFAULT NULL,
  `followup_date` date DEFAULT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`services`)),
  `total_product_price` decimal(10,2) DEFAULT 0.00,
  `total_service_price` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation`
--

INSERT INTO `quotation` (`id`, `log_id`, `customer_name`, `customer_id`, `contact_info`, `problem_desc`, `followup_date`, `products`, `services`, `total_product_price`, `total_service_price`, `grand_total`, `created_at`, `updated_at`) VALUES
(1, 1, 'Heviinash', '22092003', '0169432209', 'Laptop Issue', '0000-00-00', '[{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"}]', '[{\"service_needed\":\"Black Oil Service\",\"price\":\"75.00\"}]', 120.00, 75.00, 195.00, '2024-11-10 05:26:05', '2024-11-10 05:26:05'),
(2, 3, 'Heviinash Parugavelu', '22092003', '0169432209', 'Food Issue', '0000-00-00', '[{\"product_needed\":\"Castrol - Engine Oil - GTX 20W\",\"quantity\":1,\"price\":\"58.00\"}]', '[{\"service_needed\":\"Vehicle Black Oil\",\"price\":\"180.00\"}]', 58.00, 180.00, 238.00, '2024-11-11 05:51:40', '2024-11-11 05:51:40'),
(7, 7, 'Heviinash Parugavelu', '22092003', '0169432209', 'Issue Testing', '2024-11-13', '[{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"}]', '[{\"service_needed\":\"Vehicle Black Oil\",\"price\":\"180.00\"}]', 120.00, 180.00, 300.00, '2024-11-13 12:42:48', '2024-11-13 12:42:48'),
(8, 11, 'Heviinash Parugavelu', '22092003', '0169432209', 'Test Issue', '2024-11-21', '[{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"},{\"product_needed\":\"Derma - Facial Moisturizer - Ceramide 25g\",\"quantity\":1,\"price\":\"76.00\"}]', '[]', 196.00, 0.00, 196.00, '2024-11-21 06:22:38', '2024-11-21 06:22:38'),
(9, 11, 'Heviinash Parugavelu', '22092003', '0169432209', 'Test Issue', '2024-11-21', '[{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"},{\"product_needed\":\"Derma - Facial Moisturizer - Ceramide 25g\",\"quantity\":1,\"price\":\"76.00\"}]', '[]', 196.00, 0.00, 196.00, '2024-11-21 07:22:02', '2024-11-21 07:22:02'),
(10, 12, 'Nash', '22092003', '0169432209', 'Service and Product Test', '2024-11-23', '[{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"},{\"product_needed\":\"Aiken - Serum - Sensitive Skin 20ml\",\"quantity\":1,\"price\":\"85.00\"}]', '[{\"service_needed\":\"Vehicle Black Oil\",\"price\":\"180.00\"}]', 205.00, 180.00, 385.00, '2024-11-23 03:32:46', '2024-11-23 03:32:46'),
(11, 14, 'Heviinash Parugavelu', '030922070863', '0169432209', 'Laptop Reset and Vehicle Black Oil change ', '0000-00-00', '[{\"product_needed\":\"Derma - Facial Moisturizer - Ceramide 25g\",\"quantity\":1,\"price\":\"76.00\"},{\"product_needed\":\"Vaseline Niveas - Lip Balm - Rosy Lips\",\"quantity\":1,\"price\":\"120.00\"}]', '[{\"service_needed\":\"Vehicle Black Oil\",\"price\":\"180.00\"}]', 196.00, 180.00, 376.00, '2024-11-28 15:24:41', '2024-11-28 15:24:41'),
(12, 15, 'Heviinash Parugavelu', '22092003', '0169432209', 'Acer Aspire 5 Laptop', '0000-00-00', '[{\"product_needed\":\"Kingston - RAM - 16GB\",\"quantity\":1,\"price\":\"450.00\"}]', '[{\"service_needed\":\"Vehicle Black Oil\",\"price\":\"180.00\"}]', 450.00, 180.00, 630.00, '2024-12-01 13:50:22', '2024-12-01 13:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `quotationpdfsettings`
--

CREATE TABLE `quotationpdfsettings` (
  `id` int(11) NOT NULL,
  `add_watermark` tinyint(1) DEFAULT 0,
  `watermark_text` varchar(255) DEFAULT NULL,
  `font_size` int(11) DEFAULT 10,
  `position` enum('top-left','top-right','center','bottom-left') DEFAULT 'top-left',
  `layout` enum('side-by-side','top-bottom') DEFAULT 'side-by-side',
  `include_header_footer` tinyint(1) DEFAULT 1,
  `header_text` varchar(255) DEFAULT 'Quotation',
  `footer_text` varchar(255) DEFAULT 'Thank you for your business!',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotationpdf_settings`
--

CREATE TABLE `quotationpdf_settings` (
  `setting_id` int(11) NOT NULL,
  `watermark_text` varchar(255) DEFAULT NULL,
  `font_size` int(11) DEFAULT NULL,
  `font_family` varchar(100) DEFAULT NULL,
  `sender_info_position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotationpdf_settings`
--

INSERT INTO `quotationpdf_settings` (`setting_id`, `watermark_text`, `font_size`, `font_family`, `sender_info_position`) VALUES
(1, 'QUOTATION', 12, 'helvetica', 'topright');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_name`, `description`) VALUES
('-', 'Nothing'),
('Accountant', 'accounting'),
('Admin', 'Control All'),
('Backed Dev', 'Web Dev'),
('Guest', 'Only can see but cannot perform anything '),
('OfficeClerk', 'Clerk'),
('System God', 'God of System'),
('Technician', 'Oversee all the web settings'),
('Web', 'web'),
('Web Tech', 'Only Technician');

-- --------------------------------------------------------

--
-- Table structure for table `roles_permission`
--

CREATE TABLE `roles_permission` (
  `role_name` varchar(50) NOT NULL,
  `permission_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles_permission`
--

INSERT INTO `roles_permission` (`role_name`, `permission_name`) VALUES
('Accountant', 'accdashboard.php'),
('Accountant', 'comissions.php'),
('Accountant', 'financialdashboard.php'),
('Accountant', 'promo_setup.php'),
('Accountant', 'properpaymenttesting.php'),
('Admin', 'accdashboard.php'),
('Admin', 'addingproduct.php'),
('Admin', 'add_inventory.php'),
('Admin', 'add_service.php'),
('Admin', 'barcodetestpayment.php'),
('Admin', 'comissions.php'),
('Admin', 'companyregistration.php'),
('Admin', 'customerregistration.php'),
('Admin', 'customer_followup.php'),
('Admin', 'customer_log.php'),
('Admin', 'customer_purchase_history.php'),
('Admin', 'financialdashboard.php'),
('Admin', 'inventorytrack.php'),
('Admin', 'invoicepdfsettings.php'),
('Admin', 'loginhistory.php'),
('Admin', 'mainpage.php'),
('Admin', 'manageuserrolemanagement.php'),
('Admin', 'manage_permission.php'),
('Admin', 'paymenttesting.php'),
('Admin', 'promo_setup.php'),
('Admin', 'properpaymenttesting.php'),
('Admin', 'quickinvoice.php'),
('Admin', 'quotationpdfsettings.php'),
('Admin', 'request_restock.php'),
('Admin', 'restock.php'),
('Admin', 'rolepermissionview.php'),
('Admin', 'save_id_settings.php'),
('Admin', 'systemuserregistration.php'),
('Admin', 'transaction_details.php'),
('Admin', 'trialpaymenttest.php'),
('Admin', 'userrolemanagement.php'),
('Admin', 'viewcustomerlogs.php'),
('Admin', 'viewinventory.php'),
('Backed Dev', 'addingproduct.php'),
('Backed Dev', 'add_inventory.php'),
('Backed Dev', 'add_service.php'),
('Backed Dev', 'customer_log.php'),
('Backed Dev', 'mainpage.php'),
('Backed Dev', 'manageuserrolemanagement.php'),
('Backed Dev', 'manage_permission.php'),
('Backed Dev', 'rolepermissionview.php'),
('Backed Dev', 'systemuserregistration.php'),
('Backed Dev', 'userrolemanagement.php'),
('Backed Dev', 'viewcustomerlogs.php'),
('Backed Dev', 'viewinventory.php'),
('Technician', 'mainpage.php'),
('Technician', 'properpaymenttesting.php'),
('Technician', 'trialpaymenttest.php'),
('Technician', 'viewinventory.php');

-- --------------------------------------------------------

--
-- Table structure for table `running_balance`
--

CREATE TABLE `running_balance` (
  `id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `running_balance`
--

INSERT INTO `running_balance` (`id`, `balance`, `transaction_date`) VALUES
(1, 10061.51, '2024-11-17 09:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `sales_payments`
--

CREATE TABLE `sales_payments` (
  `transaction_id` varchar(50) DEFAULT NULL,
  `employeeid` varchar(20) NOT NULL,
  `icnumber` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_number` varchar(15) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `membership_info` varchar(255) DEFAULT NULL,
  `voucher` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','mobile') NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `additional_payment_method` enum('cash','card','mobile') DEFAULT NULL,
  `additional_payment_amount` decimal(10,2) DEFAULT NULL,
  `change_provided` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_payments`
--

INSERT INTO `sales_payments` (`transaction_id`, `employeeid`, `icnumber`, `customer_name`, `customer_number`, `customer_email`, `membership_info`, `voucher`, `subtotal`, `discount`, `grand_total`, `payment_method`, `payment_amount`, `additional_payment_method`, `additional_payment_amount`, `change_provided`, `created_at`) VALUES
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 517.00, 0.00, 548.02, 'cash', 550.00, '', 0.00, 2.00, '2024-11-17 09:57:52'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 517.00, 0.00, 548.02, 'cash', 550.00, '', 0.00, 2.00, '2024-11-17 09:58:26'),
('TIDWi82XRLgsfzmbx6MyV71T5DUPlQYr', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 153.00, 0.00, 162.18, 'cash', 165.00, '', 0.00, 3.00, '2024-11-18 17:34:49'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', 'Nash', '0169432209', 'nash@gmail.com', '', '', 317.00, 0.00, 336.02, 'cash', 340.00, '', 0.00, 4.00, '2024-11-21 12:22:15'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', '030922', '', 'Raven', '0169432209', 'nash@gmail.com', '', '', 264.00, 0.00, 279.84, 'cash', 280.00, '', 0.00, 1.00, '2024-11-21 12:32:40'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', 'Raven', '', '', '', '', 364.00, 0.00, 385.84, 'cash', 390.00, '', 0.00, 5.00, '2024-11-21 12:50:39'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 598.00, 0.00, 633.88, 'cash', 650.00, '', 0.00, 20.00, '2024-11-21 14:14:09'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 306.00, 0.00, 324.36, 'cash', 330.00, '', 0.00, 6.00, '2024-11-21 14:55:10'),
('TIDLzEbFwB94kjUhZgDRC7Xd0SivYmAa', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 193.00, 0.00, 204.58, 'cash', 205.00, '', 0.00, 1.00, '2024-11-21 16:37:57'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', '030922', '', 'Raven', '', '', '', '', 308.00, 0.00, 326.48, 'cash', 330.00, '', 0.00, 4.00, '2024-11-21 16:42:28'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', '030922', '', 'Raven', '', '', '', '', 308.00, 0.00, 326.48, 'cash', 330.00, '', 0.00, 4.00, '2024-11-21 16:47:46'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', '030922', '', 'Raven', '', '', '', '', 308.00, 0.00, 326.48, 'cash', 330.00, '', 0.00, 4.00, '2024-11-21 16:51:58'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', '030922', '', 'Raven', '', '', '', '', 308.00, 0.00, 326.48, 'cash', 330.00, '', 0.00, 4.00, '2024-11-21 16:52:18'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', '030922', '', 'Raven', '', '', '', '', 308.00, 0.00, 326.48, 'cash', 330.00, '', 0.00, 4.00, '2024-11-21 16:53:22'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 366.00, 0.00, 387.96, 'cash', 390.00, '', 0.00, 10.00, '2024-11-21 17:03:09'),
('TID0rKOw468aVWGgEuIk3Mhe9JNYcDbp', '030922', '', 'Raven', '', '', '', '', 163.00, 0.00, 172.78, 'cash', 175.00, '', 0.00, 3.00, '2024-11-21 17:04:05'),
('TIDCtGpHfewFz8jRV6TDlPxmLgIbdqJ3', '030922', '', 'Raven', '', '', '', '', 163.00, 0.00, 172.78, 'cash', 175.00, '', 0.00, 3.00, '2024-11-21 17:08:38'),
('TIDcY3bjhmtUXGy5PoCi9WxuSgZ42Mzv', '030922', '', 'Raven', '', '', '', '', 283.00, 0.00, 299.98, 'cash', 300.00, '', 0.00, 1.00, '2024-11-23 03:28:47'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', '030922', '', 'Dr House', '', '', '', '', 186.00, 0.00, 197.16, 'cash', 200.00, '', 0.00, 3.00, '2024-11-25 14:51:57'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 321.00, 0.00, 340.26, 'cash', 350.00, '', 0.00, 10.00, '2024-11-26 14:44:59'),
('TIDB6Ehia8qvWzIfp7J2TKG0ZAgYc9L5', '030922', '', 'AllisonChase', '', '', '', '', 230.00, 0.00, 243.80, 'cash', 245.00, '', 0.00, 2.00, '2024-11-26 14:47:56'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', 'House', '', '', '', '', 982.00, 0.00, 1040.92, 'cash', 1050.00, '', 0.00, 10.00, '2024-11-26 14:58:58'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 288.00, 0.00, 305.28, 'cash', 306.00, '', 0.00, 1.00, '2024-11-28 14:31:17'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', 'Eric Foreman', '', '', '', '', 357.00, 0.00, 378.42, 'cash', 379.00, '', 0.00, 1.00, '2024-11-28 14:34:39'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 424.00, 0.00, 449.44, 'cash', 450.00, '', 0.00, 1.00, '2024-11-28 14:37:09'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 341.00, 0.00, 361.46, 'cash', 362.00, '', 0.00, 1.00, '2024-11-28 16:18:37'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', 'Allison Cameron', '', '', '', '', 407.00, 0.00, 431.42, 'cash', 440.00, '', 0.00, 9.00, '2024-11-28 16:20:16'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 392.00, 0.00, 415.52, 'cash', 420.00, '', 0.00, 5.00, '2024-12-03 06:14:58'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 613.00, 0.00, 649.78, 'cash', 650.00, '', 0.00, 1.00, '2024-12-04 03:31:07'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 357.00, 0.00, 378.42, 'cash', 380.00, '', 0.00, 20.00, '2024-12-04 05:09:43'),
('TIDx7yMTlCfzE6N4b5JWnOoueiQHjXDZ', '030922', '22092003', '22092003', '0169432209', 'heviinash@gmail.com', '', '', 228.00, 0.00, 145.01, 'cash', 200.00, '', 0.00, 0.00, '2025-01-23 16:12:31'),
('TID1XoOSavMhwmdUDWk84FK9TZtcl7Nb', '030922', '', 'Nash', '123', 'heviinash@gmail.com', '', '', 70.00, 0.00, 74.20, 'cash', 75.00, '', 0.00, 0.00, '2025-02-14 12:47:32');

-- --------------------------------------------------------

--
-- Table structure for table `sales_transaction`
--

CREATE TABLE `sales_transaction` (
  `transaction_id` varchar(50) DEFAULT NULL,
  `employeeid` varchar(50) DEFAULT NULL,
  `icnumber` varchar(20) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_transaction`
--

INSERT INTO `sales_transaction` (`transaction_id`, `employeeid`, `icnumber`, `barcode`, `quantity`, `total_price`, `sale_date`) VALUES
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '254678934', 1, 85.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '235467890', 1, 78.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '8425372', 1, 15.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '84256371', 1, 58.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '128746374', 1, 25.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '78263747', 1, 58.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '243567898', 1, 120.00, '2024-11-17 17:57:52'),
('TIDEoaDU6ceBJl0vT17ftCzqKrNHbISn', '030922', '22092003', '235467890', 1, 78.00, '2024-11-17 17:57:52'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '254678934', 1, 85.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '235467890', 1, 78.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '8425372', 1, 15.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '84256371', 1, 58.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '128746374', 1, 25.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '78263747', 1, 58.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '243567898', 1, 120.00, '2024-11-17 17:58:26'),
('TIDvRpZs9iHrYAaXMQtf5Vw4LIugo0CJ', '030922', '22092003', '235467890', 1, 78.00, '2024-11-17 17:58:26'),
('TIDWi82XRLgsfzmbx6MyV71T5DUPlQYr', '030922', '22092003', '7654321', 1, 70.00, '2024-11-19 01:34:49'),
('TIDWi82XRLgsfzmbx6MyV71T5DUPlQYr', '030922', '22092003', '78263747', 1, 58.00, '2024-11-19 01:34:49'),
('TIDWi82XRLgsfzmbx6MyV71T5DUPlQYr', '030922', '22092003', '128746374', 1, 25.00, '2024-11-19 01:34:49'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', '7654321', 1, 70.00, '2024-11-21 20:22:14'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', '78263747', 1, 58.00, '2024-11-21 20:22:14'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', '85463782', 1, 76.00, '2024-11-21 20:22:14'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', '254678934', 1, 85.00, '2024-11-21 20:22:14'),
('TIDvJRbeZmVEwA3GTMo8uD7Li2ak0pWN', '030922', '', '67235467', 1, 28.00, '2024-11-21 20:22:14'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', '030922', '', '7654321', 1, 70.00, '2024-11-21 20:32:40'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', '030922', '', '78263747', 1, 58.00, '2024-11-21 20:32:40'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', '030922', '', '84256371', 1, 58.00, '2024-11-21 20:32:40'),
('TIDTOhUnWAcIrQC1JkHEMF6x2DjyNYeg', '030922', '', '235467890', 1, 78.00, '2024-11-21 20:32:40'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '7654321', 1, 70.00, '2024-11-21 20:50:39'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '78263747', 1, 58.00, '2024-11-21 20:50:39'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '84256371', 1, 58.00, '2024-11-21 20:50:39'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '235467890', 1, 78.00, '2024-11-21 20:50:39'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '254678934', 1, 85.00, '2024-11-21 20:50:39'),
('TIDFuUO5QItCWL16wrR4j2izkNbPlyfA', '030922', '', '8425372', 1, 15.00, '2024-11-21 20:50:39'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '7654321', 1, 70.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '78263747', 1, 58.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '128746374', 1, 25.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '85463782', 1, 76.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '243567898', 1, 120.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '84256371', 1, 58.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '254678934', 1, 85.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '235467890', 1, 78.00, '2024-11-21 22:14:08'),
('TIDINkUMu1obC6OXTfREah0nJxwVHLle', '030922', '22092003', '67235467', 1, 28.00, '2024-11-21 22:14:08'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', '030922', '22092003', '7654321', 1, 70.00, '2024-11-21 22:55:09'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', '030922', '22092003', '78263747', 1, 58.00, '2024-11-21 22:55:09'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', '030922', '22092003', '84256371', 1, 58.00, '2024-11-21 22:55:09'),
('TIDsk2pmgRyYuzN0Gnx7bcVjOqM6PZ51', '030922', '22092003', '243567898', 1, 120.00, '2024-11-21 22:55:09'),
('TIDLzEbFwB94kjUhZgDRC7Xd0SivYmAa', '030922', '22092003', '8425372', 1, 15.00, '2024-11-22 00:37:57'),
('TIDLzEbFwB94kjUhZgDRC7Xd0SivYmAa', '030922', '22092003', '84256371', 1, 58.00, '2024-11-22 00:37:57'),
('TIDLzEbFwB94kjUhZgDRC7Xd0SivYmAa', '030922', '22092003', '243567898', 1, 120.00, '2024-11-22 00:37:57'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', '030922', '', '128746374', 1, 25.00, '2024-11-22 00:42:28'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', '030922', '', '243567898', 1, 120.00, '2024-11-22 00:42:28'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', '030922', '', '254678934', 1, 85.00, '2024-11-22 00:42:28'),
('TIDxyBAkRTuG01Xg46rWC72mYic9vZHL', '030922', '', '235467890', 1, 78.00, '2024-11-22 00:42:28'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', '030922', '', '128746374', 1, 25.00, '2024-11-22 00:47:46'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', '030922', '', '243567898', 1, 120.00, '2024-11-22 00:47:46'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', '030922', '', '254678934', 1, 85.00, '2024-11-22 00:47:46'),
('TID0LNypHESl41qOV723ZvdfjbieBAxK', '030922', '', '235467890', 1, 78.00, '2024-11-22 00:47:46'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', '030922', '', '128746374', 1, 25.00, '2024-11-22 00:51:58'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', '030922', '', '243567898', 1, 120.00, '2024-11-22 00:51:58'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', '030922', '', '254678934', 1, 85.00, '2024-11-22 00:51:58'),
('TIDIYn1QwT6jEBCVAUNpqcJolWkfOuMt', '030922', '', '235467890', 1, 78.00, '2024-11-22 00:51:58'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', '030922', '', '128746374', 1, 25.00, '2024-11-22 00:52:18'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', '030922', '', '243567898', 1, 120.00, '2024-11-22 00:52:18'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', '030922', '', '254678934', 1, 85.00, '2024-11-22 00:52:18'),
('TIDBsY1aLdMI46tEr70ifNqScUkW9xJK', '030922', '', '235467890', 1, 78.00, '2024-11-22 00:52:18'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', '030922', '', '128746374', 1, 25.00, '2024-11-22 00:53:22'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', '030922', '', '243567898', 1, 120.00, '2024-11-22 00:53:22'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', '030922', '', '254678934', 1, 85.00, '2024-11-22 00:53:22'),
('TIDAMGepc6ZXlSi7sxW0QLn1HTrfYB9g', '030922', '', '235467890', 1, 78.00, '2024-11-22 00:53:22'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '254678934', 1, 85.00, '2024-11-22 01:03:08'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '84256371', 1, 58.00, '2024-11-22 01:03:08'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '243567898', 1, 120.00, '2024-11-22 01:03:08'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '235467890', 1, 78.00, '2024-11-22 01:03:09'),
('TIDMIvXwOPgL13UKoaSQuzjVy5hWrYTC', '030922', '22092003', '128746374', 1, 25.00, '2024-11-22 01:03:09'),
('TID0rKOw468aVWGgEuIk3Mhe9JNYcDbp', '030922', '', '254678934', 1, 85.00, '2024-11-22 01:04:04'),
('TID0rKOw468aVWGgEuIk3Mhe9JNYcDbp', '030922', '', '235467890', 1, 78.00, '2024-11-22 01:04:04'),
('TIDCtGpHfewFz8jRV6TDlPxmLgIbdqJ3', '030922', '', '254678934', 1, 85.00, '2024-11-22 01:08:38'),
('TIDCtGpHfewFz8jRV6TDlPxmLgIbdqJ3', '030922', '', '235467890', 1, 78.00, '2024-11-22 01:08:38'),
('TIDcY3bjhmtUXGy5PoCi9WxuSgZ42Mzv', '030922', '', '254678934', 1, 85.00, '2024-11-23 11:28:47'),
('TIDcY3bjhmtUXGy5PoCi9WxuSgZ42Mzv', '030922', '', '235467890', 1, 78.00, '2024-11-23 11:28:47'),
('TIDcY3bjhmtUXGy5PoCi9WxuSgZ42Mzv', '030922', '', '243567898', 1, 120.00, '2024-11-23 11:28:47'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', '030922', '', '254678934', 1, 85.00, '2024-11-25 22:51:57'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', '030922', '', '67235467', 1, 28.00, '2024-11-25 22:51:57'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', '030922', '', '8425372', 1, 15.00, '2024-11-25 22:51:57'),
('TIDh6rmLDOj15pUPJXNG0VlWHQw2Y8Bi', '030922', '', '78263747', 1, 58.00, '2024-11-25 22:51:57'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '128746374', 1, 25.00, '2024-11-26 22:44:59'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '85463782', 1, 76.00, '2024-11-26 22:44:59'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '243567898', 1, 120.00, '2024-11-26 22:44:59'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '254678934', 1, 85.00, '2024-11-26 22:44:59'),
('TIDPRTc6ze5DFEK8lrSo73g4Aq0mdisN', '030922', '22092003', '8425372', 1, 15.00, '2024-11-26 22:44:59'),
('TIDB6Ehia8qvWzIfp7J2TKG0ZAgYc9L5', '030922', '', '254678934', 1, 85.00, '2024-11-26 22:47:56'),
('TIDB6Ehia8qvWzIfp7J2TKG0ZAgYc9L5', '030922', '', '128746374', 1, 25.00, '2024-11-26 22:47:56'),
('TIDB6Ehia8qvWzIfp7J2TKG0ZAgYc9L5', '030922', '', '243567898', 1, 120.00, '2024-11-26 22:47:56'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '254678934', 1, 85.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '84256371', 2, 116.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '128746374', 1, 25.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '85463782', 1, 76.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '243567898', 3, 360.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '235467890', 1, 78.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '67235467', 2, 56.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '78263747', 2, 116.00, '2024-11-26 22:58:58'),
('TIDiXTKM7yRPVkQd6A3wl1nDrWf9v04h', '030922', '', '7654321', 1, 70.00, '2024-11-26 22:58:58'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', '030922', '22092003', '128746374', 1, 25.00, '2024-11-28 22:31:17'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', '030922', '22092003', '78263747', 1, 58.00, '2024-11-28 22:31:17'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', '030922', '22092003', '243567898', 1, 120.00, '2024-11-28 22:31:17'),
('TIDKarpzQls2kM3OTE5WfvFnoNdAgLVq', '030922', '22092003', '254678934', 1, 85.00, '2024-11-28 22:31:17'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', '235467890', 1, 78.00, '2024-11-28 22:34:39'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', '243567898', 1, 120.00, '2024-11-28 22:34:39'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', '84256371', 1, 58.00, '2024-11-28 22:34:39'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', '128746374', 1, 25.00, '2024-11-28 22:34:39'),
('TIDVBNurCOx16QYi07WKFIzjAeslagpR', '030922', '', '85463782', 1, 76.00, '2024-11-28 22:34:39'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '254678934', 1, 85.00, '2024-11-28 22:37:09'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '84256371', 1, 58.00, '2024-11-28 22:37:09'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '243567898', 1, 120.00, '2024-11-28 22:37:09'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '128746374', 1, 25.00, '2024-11-28 22:37:09'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '78263747', 1, 58.00, '2024-11-28 22:37:09'),
('TIDQkGZ40duvjYwtWfPJ7b6rphFNE8sS', '030922', '22092003', '235467890', 1, 78.00, '2024-11-28 22:37:09'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', '030922', '22092003', '84256371', 1, 58.00, '2024-11-29 00:18:36'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', '030922', '22092003', '243567898', 1, 120.00, '2024-11-29 00:18:36'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', '030922', '22092003', '254678934', 1, 85.00, '2024-11-29 00:18:36'),
('TIDGSUnuZY5hM10kzJoyEcIxFTNmqrf6', '030922', '22092003', '235467890', 1, 78.00, '2024-11-29 00:18:36'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '84256371', 1, 58.00, '2024-11-29 00:20:16'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '243567898', 1, 120.00, '2024-11-29 00:20:16'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '128746374', 1, 25.00, '2024-11-29 00:20:16'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '85463782', 1, 76.00, '2024-11-29 00:20:16'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '78263747', 1, 58.00, '2024-11-29 00:20:16'),
('TIDmKr9IE7h4q5xDpwjL0W13Vev8tuBY', '030922', '', '7654321', 1, 70.00, '2024-11-29 00:20:16'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '128746374', 1, 25.00, '2024-12-03 14:14:57'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '85463782', 1, 76.00, '2024-12-03 14:14:57'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '78263747', 1, 58.00, '2024-12-03 14:14:57'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '7654321', 1, 70.00, '2024-12-03 14:14:57'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '235467890', 1, 78.00, '2024-12-03 14:14:57'),
('TIDmMgkZKetUVyQYC6so5hivpclL839B', '030922', '22092003', '254678934', 1, 85.00, '2024-12-03 14:14:57'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '84256371', 1, 58.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '128746374', 1, 25.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '85463782', 1, 76.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '7654321', 1, 70.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '78263747', 1, 58.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '243567898', 1, 120.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '235467890', 1, 78.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '254678934', 1, 85.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '67235467', 1, 28.00, '2024-12-04 11:31:05'),
('TIDXcgkDoqPMLGxSQjfA4EB72ib0w9nF', '030922', '22092003', '8425372', 1, 15.00, '2024-12-04 11:31:05'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '84256371', 1, 58.00, '2024-12-04 13:09:43'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '128746374', 1, 25.00, '2024-12-04 13:09:43'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '85463782', 1, 76.00, '2024-12-04 13:09:43'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '243567898', 1, 120.00, '2024-12-04 13:09:43'),
('TIDNa7Vl1Te9unrZcxpIqzFEwSdOk46L', '030922', '22092003', '235467890', 1, 78.00, '2024-12-04 13:09:43'),
('TIDx7yMTlCfzE6N4b5JWnOoueiQHjXDZ', '030922', '22092003', '85463782', 3, 228.00, '2025-01-24 00:12:30'),
('TID1XoOSavMhwmdUDWk84FK9TZtcl7Nb', '030922', '', '7654321', 1, 70.00, '2025-02-14 20:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `serviceinventory`
--

CREATE TABLE `serviceinventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('raw items','product') DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `status` enum('available','out_of_stock') DEFAULT 'available',
  `reorder_level` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `serviceinventory`
--

INSERT INTO `serviceinventory` (`item_id`, `item_name`, `item_type`, `quantity`, `unit`, `status`, `reorder_level`) VALUES
(19, 'Coconut Oil', 'product', 20.00, 'quantity', 'available', 10),
(20, 'Castrol Black Oil', 'product', 20.00, 'quantity', 'available', 10),
(21, 'Massage Oil', 'product', 20.00, 'quantity', 'available', 10),
(22, 'Mutton', 'product', 10.00, 'kg', 'available', 5);

-- --------------------------------------------------------

--
-- Table structure for table `servicejournalentries`
--

CREATE TABLE `servicejournalentries` (
  `entry_id` int(11) NOT NULL,
  `icnumber` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `issue_reported` text NOT NULL,
  `solution_provided` text DEFAULT NULL,
  `parts_used` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_paths1` text DEFAULT NULL,
  `image_paths2` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicejournalentries`
--

INSERT INTO `servicejournalentries` (`entry_id`, `icnumber`, `service_date`, `service_type`, `issue_reported`, `solution_provided`, `parts_used`, `follow_up_date`, `created_by`, `created_at`, `image_paths1`, `image_paths2`) VALUES
(1, 22092003, '2024-10-01', 'Black Oil Service', '1/10/2024', 'New Engine Oil', 'Castrol', '2024-10-02', '22092003', '2024-10-01 14:20:55', 'C:\\xampp\\htdocs\\DevelopmentProject\\uploadsScreenshot 2024-09-17 184907.png,C:\\xampp\\htdocs\\DevelopmentProject\\uploadsScreenshot 2024-09-17 184929.png', 'C:\\xampp\\htdocs\\DevelopmentProject\\uploadsScreenshot 2024-09-17 183642.png,C:\\xampp\\htdocs\\DevelopmentProject\\uploadsScreenshot 2024-09-17 183728.png'),
(2, 2147483647, '2024-10-03', 'Facial Service', 'Acne Treatement', 'Acne Treatment Package', '1x Moisturizer\r\nSponge', '2024-10-05', 'Heviinash', '2024-10-02 13:32:25', 'C:\\xampp\\htdocs\\DevelopmentProject\\uploads\\Screenshot 2024-09-17 183728.png', 'C:\\xampp\\htdocs\\DevelopmentProject\\uploads\\Screenshot 2024-09-17 184907.png');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `duration`) VALUES
(28, 'Vehicle Black Oil', 'Black Oil', 180.00, 10);

-- --------------------------------------------------------

--
-- Table structure for table `service_items`
--

CREATE TABLE `service_items` (
  `service_item_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_needed` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_items`
--

INSERT INTO `service_items` (`service_item_id`, `service_id`, `item_id`, `quantity_needed`) VALUES
(23, 28, 20, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `phone`, `address`) VALUES
('030922070863', 'Heviinash Parugavelu', 'heviinashparugavelu@gmail.com', '+60169432209', 'Penang'),
('123', 'Nash', 'heviinash22@gmail.com', '0169432209', '2E-01-13, Medan Angsana 1, Bandar Baru Air Itam 11500');

-- --------------------------------------------------------

--
-- Table structure for table `testcustomer_logs`
--

CREATE TABLE `testcustomer_logs` (
  `log_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `icnumber` varchar(50) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `problem_desc` text DEFAULT NULL,
  `followupdate` date DEFAULT NULL,
  `image_path` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testcustomer_logs`
--

INSERT INTO `testcustomer_logs` (`log_id`, `name`, `icnumber`, `contact_info`, `problem_desc`, `followupdate`, `image_path`, `created_at`) VALUES
(1, 'Heviinash', '22092003', '0169432209', 'Laptop Issue', '0000-00-00', 'uploads/RAM (1).png', '2024-11-10 13:26:01'),
(2, 'Heviinash', '22092003', '0169432209', 'System Test Issue', '0000-00-00', 'uploads/RAM (1).png', '2024-11-10 13:51:46'),
(3, 'Heviinash Parugavelu', '22092003', '0169432209', 'Food Issue', '0000-00-00', 'uploads/IMG_8393.jpeg', '2024-11-11 13:50:01'),
(4, 'Heviinash', '22092003', '0169432209', 'System Test Issue', '2003-09-22', 'uploads/RAM (1).png', '2024-11-11 22:56:08'),
(5, 'Heviinash', '22092003', '0169432209', 'System Test Issue', '2009-05-29', 'uploads/IMG_8393.jpeg', '2024-11-12 21:05:45'),
(6, 'Heviinash Parugavelu', '22092003', '0169432209', 'System Test Issue', '2024-11-13', 'uploads/42844194_1965686786807513_5454169249679409152_n (1).jpg', '2024-11-13 15:58:28'),
(7, 'Heviinash Parugavelu', '22092003', '0169432209', 'Issue Testing', '2024-11-13', 'uploads/RAM (1).png', '2024-11-13 19:50:21'),
(8, 'Heviinash Parugavelu', '22092003', '0169432209', 'System Test Issue ', '0000-00-00', 'uploads/Screenshot 2024-09-17 183728.png', '2024-11-14 00:40:15'),
(9, 'Heviinash Parugavelu', '22092003', '0169432209', 'System Test Issue', '2024-11-14', 'uploads/Screenshot 2024-09-17 183728.png', '2024-11-14 11:51:17'),
(10, 'Heviinash Parugavelu', '22092003', '0169432209', 'Test Issue', '2024-11-21', '', '2024-11-21 14:05:18'),
(11, 'Heviinash Parugavelu', '22092003', '0169432209', 'Test Issue', '2024-11-21', '', '2024-11-21 14:22:34'),
(12, 'Nash', '22092003', '0169432209', 'Service and Product Test', '2024-11-23', '', '2024-11-23 11:32:39'),
(13, 'Allison Cameron', '876543245', '0165542384', 'Macbook Air RAM Upgrade', '0000-00-00', 'uploads/Screenshot 2024-11-26 122011.png', '2024-11-26 21:08:05'),
(14, 'Heviinash Parugavelu', '030922070863', '0169432209', 'Laptop Reset and Vehicle Black Oil change ', '0000-00-00', '', '2024-11-28 23:24:39'),
(15, 'Heviinash Parugavelu', '22092003', '0169432209', 'Acer Aspire 5 Laptop', '0000-00-00', '', '2024-12-01 21:49:44'),
(16, 'Heviinash', '22092003', '0169432209', 'Test', '0000-00-00', 'uploads/TUO34JwWPD22dNfopWIF5bXpYGObum7rgP9fvA29.png', '2025-02-14 20:48:18'),
(17, 'Heviinash Parugavelu', '1234567890', '0169432209', 'Car Model: Toyota Rush Engine Oil Change', '0000-00-00', '', '2025-03-10 17:07:58');

-- --------------------------------------------------------

--
-- Table structure for table `testcustomer_products`
--

CREATE TABLE `testcustomer_products` (
  `log_id` int(11) DEFAULT NULL,
  `product_needed` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testcustomer_products`
--

INSERT INTO `testcustomer_products` (`log_id`, `product_needed`, `price`, `quantity`) VALUES
(1, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(2, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(3, 'Castrol - Engine Oil - GTX 20W', 58.00, 1),
(4, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(5, 'Kingston - RAM - 16GB', 450.00, 1),
(6, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(7, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(8, 'Hada Labu - Body Lotion - Sensitive Skin 100ml', 78.00, 1),
(8, 'Derma - Facial Moisturizer - Ceramide 25g', 76.00, 1),
(8, 'Kingston - RAM - 16GB', 450.00, 1),
(9, 'Derma - Facial Moisturizer - Ceramide 25g', 76.00, 1),
(9, 'Hada Labu - Body Lotion - Sensitive Skin 100ml', 78.00, 1),
(11, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(11, 'Derma - Facial Moisturizer - Ceramide 25g', 76.00, 1),
(12, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(12, 'Aiken - Serum - Sensitive Skin 20ml', 85.00, 1),
(13, 'Castrol - Engine Oil - GTX 20W', 58.00, 1),
(14, 'Derma - Facial Moisturizer - Ceramide 25g', 76.00, 1),
(14, 'Vaseline Niveas - Lip Balm - Rosy Lips', 120.00, 1),
(15, 'Kingston - RAM - 16GB', 450.00, 1),
(16, 'Kingston - RAM - 16GB', 450.00, 1),
(17, 'Castrol - Engine Oil - GTX 20W', 58.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `testcustomer_services`
--

CREATE TABLE `testcustomer_services` (
  `log_id` int(11) DEFAULT NULL,
  `service_needed` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testcustomer_services`
--

INSERT INTO `testcustomer_services` (`log_id`, `service_needed`, `price`) VALUES
(1, 'Black Oil Service', 75.00),
(2, 'Chicken Curry', 10.00),
(2, 'Chicken Curry', 10.00),
(3, 'Vehicle Black Oil', 180.00),
(4, 'Vehicle Black Oil', 180.00),
(5, 'Vehicle Black Oil', 180.00),
(6, 'Vehicle Black Oil', 180.00),
(7, 'Vehicle Black Oil', 180.00),
(8, 'Vehicle Black Oil', 180.00),
(8, 'Vehicle Black Oil', 180.00),
(9, 'Vehicle Black Oil', 180.00),
(9, 'Vehicle Black Oil', 180.00),
(10, 'Vehicle Black Oil', 180.00),
(12, 'Vehicle Black Oil', 180.00),
(13, 'Vehicle Black Oil', 180.00),
(14, 'Vehicle Black Oil', 180.00),
(15, 'Vehicle Black Oil', 180.00),
(16, 'Vehicle Black Oil', 180.00),
(17, 'Vehicle Black Oil', 180.00);

-- --------------------------------------------------------

--
-- Table structure for table `usersystem`
--

CREATE TABLE `usersystem` (
  `employeeid` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `confirmpassword` varchar(255) NOT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usersystem`
--

INSERT INTO `usersystem` (`employeeid`, `username`, `password`, `confirmpassword`, `role_name`, `created_at`) VALUES
('000923', 'harishiini', 'Harishiini@23092000', 'Harishiini@23092000', 'Guest', '2024-10-03 15:04:16'),
('0309', '0309', 'Heviinash@22', 'Heviinash@22', 'OfficeClerk', '2024-11-08 06:09:49'),
('030922', 'Heviinash', 'heviinash22', 'heviinash22', 'Admin', '2024-09-05 11:53:07'),
('22092003030922', 'Nash', 'Nash@22092003', 'Nash@22092003', 'Technician', '2024-09-21 06:43:12'),
('6543', 'Asteria65', '1234567', '1234567', 'Guest', '2024-10-21 17:18:09');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_code`, `discount_percentage`) VALUES
('nash22092003', 70.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `businessregistration`
--
ALTER TABLE `businessregistration`
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `companymemberships`
--
ALTER TABLE `companymemberships`
  ADD PRIMARY KEY (`companymembership_id`),
  ADD KEY `companyregistration` (`companyregistration`);

--
-- Indexes for table `companyregistration`
--
ALTER TABLE `companyregistration`
  ADD PRIMARY KEY (`companyregistration`);

--
-- Indexes for table `customerid_settings`
--
ALTER TABLE `customerid_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_type` (`id_type`);

--
-- Indexes for table `customermemberships`
--
ALTER TABLE `customermemberships`
  ADD PRIMARY KEY (`icnumber`);

--
-- Indexes for table `customerregistration`
--
ALTER TABLE `customerregistration`
  ADD PRIMARY KEY (`icnumber`);

--
-- Indexes for table `employeedetails`
--
ALTER TABLE `employeedetails`
  ADD PRIMARY KEY (`icnumber`),
  ADD UNIQUE KEY `employeeid` (`employeeid`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoicepdf_settings`
--
ALTER TABLE `invoicepdf_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `pdf_customization_settings`
--
ALTER TABLE `pdf_customization_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `pdf_settings`
--
ALTER TABLE `pdf_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_name`);

--
-- Indexes for table `productinventorytable`
--
ALTER TABLE `productinventorytable`
  ADD PRIMARY KEY (`sku`),
  ADD UNIQUE KEY `SKU` (`sku`),
  ADD UNIQUE KEY `sku_2` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `productsinventory`
--
ALTER TABLE `productsinventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `quotation`
--
ALTER TABLE `quotation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotationpdfsettings`
--
ALTER TABLE `quotationpdfsettings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotationpdf_settings`
--
ALTER TABLE `quotationpdf_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_name`);

--
-- Indexes for table `roles_permission`
--
ALTER TABLE `roles_permission`
  ADD PRIMARY KEY (`role_name`,`permission_name`),
  ADD KEY `permission_name` (`permission_name`);

--
-- Indexes for table `running_balance`
--
ALTER TABLE `running_balance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `serviceinventory`
--
ALTER TABLE `serviceinventory`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `servicejournalentries`
--
ALTER TABLE `servicejournalentries`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `service_items`
--
ALTER TABLE `service_items`
  ADD PRIMARY KEY (`service_item_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `testcustomer_logs`
--
ALTER TABLE `testcustomer_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `usersystem`
--
ALTER TABLE `usersystem`
  ADD UNIQUE KEY `employeeid` (`employeeid`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customerid_settings`
--
ALTER TABLE `customerid_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `invoicepdf_settings`
--
ALTER TABLE `invoicepdf_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pdf_customization_settings`
--
ALTER TABLE `pdf_customization_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pdf_settings`
--
ALTER TABLE `pdf_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation`
--
ALTER TABLE `quotation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `quotationpdfsettings`
--
ALTER TABLE `quotationpdfsettings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `running_balance`
--
ALTER TABLE `running_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `serviceinventory`
--
ALTER TABLE `serviceinventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `servicejournalentries`
--
ALTER TABLE `servicejournalentries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `service_items`
--
ALTER TABLE `service_items`
  MODIFY `service_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `testcustomer_logs`
--
ALTER TABLE `testcustomer_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `companymemberships`
--
ALTER TABLE `companymemberships`
  ADD CONSTRAINT `companymemberships_ibfk_1` FOREIGN KEY (`companyregistration`) REFERENCES `companyregistration` (`companyregistration`);

--
-- Constraints for table `customermemberships`
--
ALTER TABLE `customermemberships`
  ADD CONSTRAINT `customermemberships_ibfk_1` FOREIGN KEY (`icnumber`) REFERENCES `customerregistration` (`icnumber`);

--
-- Constraints for table `roles_permission`
--
ALTER TABLE `roles_permission`
  ADD CONSTRAINT `roles_permission_ibfk_1` FOREIGN KEY (`role_name`) REFERENCES `roles` (`role_name`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_permission_ibfk_2` FOREIGN KEY (`permission_name`) REFERENCES `permissions` (`permission_name`) ON DELETE CASCADE;

--
-- Constraints for table `service_items`
--
ALTER TABLE `service_items`
  ADD CONSTRAINT `service_items_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`),
  ADD CONSTRAINT `service_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `serviceinventory` (`item_id`);

--
-- Constraints for table `usersystem`
--
ALTER TABLE `usersystem`
  ADD CONSTRAINT `usersystem_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employeedetails` (`employeeid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
