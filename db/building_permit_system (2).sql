-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 06:37 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `building_permit_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `permit_type` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_comment` text DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `applicant_name` varchar(255) NOT NULL,
  `applicant_email` varchar(255) NOT NULL,
  `applicant_mobile` varchar(20) NOT NULL,
  `applicant_address` text NOT NULL,
  `applicant_nationality` varchar(100) NOT NULL,
  `applicant_gender` enum('Male','Female','Other') NOT NULL,
  `agent_name` varchar(255) DEFAULT NULL,
  `agent_gender` enum('Male','Female','Other') DEFAULT NULL,
  `agent_address` text DEFAULT NULL,
  `architectural_drawings` varchar(255) DEFAULT NULL,
  `structure_reports` varchar(255) DEFAULT NULL,
  `fire_permit` varchar(255) DEFAULT NULL,
  `epa_report` varchar(255) DEFAULT NULL,
  `geo_technical_report` varchar(255) DEFAULT NULL,
  `traffic_impact_assessment` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `foundations_materials` varchar(255) DEFAULT NULL,
  `foundations_proportions` varchar(255) DEFAULT NULL,
  `walls_materials` varchar(255) DEFAULT NULL,
  `walls_proportions` varchar(255) DEFAULT NULL,
  `floors_materials` varchar(255) DEFAULT NULL,
  `floors_proportions` varchar(255) DEFAULT NULL,
  `floors_joint_dimension` varchar(255) DEFAULT NULL,
  `floors_covering_thickness` varchar(255) DEFAULT NULL,
  `windows_types` varchar(255) DEFAULT NULL,
  `windows_dimension` varchar(255) DEFAULT NULL,
  `doors_types` varchar(255) DEFAULT NULL,
  `doors_dimension` varchar(255) DEFAULT NULL,
  `roof_types` varchar(255) DEFAULT NULL,
  `roof_covering` varchar(255) DEFAULT NULL,
  `roof_spacing_trusses` varchar(255) DEFAULT NULL,
  `steps_materials` varchar(255) DEFAULT NULL,
  `verandah_materials` varchar(255) DEFAULT NULL,
  `fencing_materials` varchar(255) DEFAULT NULL,
  `yards_details` varchar(255) DEFAULT NULL,
  `outbuilding_details` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `permit_type`, `status`, `admin_comment`, `submission_date`, `created_at`, `applicant_name`, `applicant_email`, `applicant_mobile`, `applicant_address`, `applicant_nationality`, `applicant_gender`, `agent_name`, `agent_gender`, `agent_address`, `architectural_drawings`, `structure_reports`, `fire_permit`, `epa_report`, `geo_technical_report`, `traffic_impact_assessment`, `document_path`, `foundations_materials`, `foundations_proportions`, `walls_materials`, `walls_proportions`, `floors_materials`, `floors_proportions`, `floors_joint_dimension`, `floors_covering_thickness`, `windows_types`, `windows_dimension`, `doors_types`, `doors_dimension`, `roof_types`, `roof_covering`, `roof_spacing_trusses`, `steps_materials`, `verandah_materials`, `fencing_materials`, `yards_details`, `outbuilding_details`) VALUES
(6, 'RESIDENTIAL', 'pending', NULL, '2025-03-17 10:19:26', '2025-03-17 10:19:26', 'Afful Bismark', 'kwadwomegas@gmail.com', '0545041428', 'Battor', 'Ghanaian', 'Male', 'Aseye Abledu', 'Female', 'Tema', 'uploads/1742206766_RSPP EN-US SG M05 SYSADMINISTRATION.pdf', 'uploads/1742206766_RSPP EN-US SG M05 DEBUGGINGTESTING.pdf', 'uploads/1742206766_RSPP EN-US SG M05 FUNCTIONS.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Residential', 'approved', NULL, '2025-03-17 16:49:15', '2025-03-17 16:49:15', 'Zayne Ewusi Amponsah', 'zayne@gmail.com', '056348973', 'Accra', 'Ghanaian', 'Male', 'Beatrice', 'Female', 'Accra', 'uploads/1742230155_RSPP EN-US SG M05 SYSADMINISTRATION.pdf', 'uploads/1742230155_RSPP EN-US SG M05 DEBUGGINGTESTING.pdf', NULL, 'uploads/1742230155_RSPP EN-US SG M05 FUNCTIONS.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `application_details`
--

CREATE TABLE `application_details` (
  `id` int(11) NOT NULL,
  `application_id` varchar(36) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `owner_address` text DEFAULT NULL,
  `land_location` text DEFAULT NULL,
  `construction_location` text DEFAULT NULL,
  `purpose` varchar(50) DEFAULT NULL,
  `building_details` text DEFAULT NULL,
  `date_of_application` date DEFAULT NULL,
  `applicant_signature` varchar(255) DEFAULT NULL,
  `witness_signature` varchar(255) DEFAULT NULL,
  `attached_documents` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `application_details`
--

INSERT INTO `application_details` (`id`, `application_id`, `owner_name`, `owner_address`, `land_location`, `construction_location`, `purpose`, `building_details`, `date_of_application`, `applicant_signature`, `witness_signature`, `attached_documents`) VALUES
(26, 'X/BAT/25/0001', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Regularization', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(27, 'X/BAT/25/0002', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Alteration', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(28, 'X/BAT/25/0003', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Install property', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(29, 'X/BAT/25/0004', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Regularization', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(30, 'X/BAT/25/0005', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Install property', '', '2025-03-25', 'K.F', 'A.B', 'structural_drawings,land_title'),
(31, 'X/BAT/25/0006', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Alteration', '', '2025-03-25', 'K.F', 'A.B', 'land_title'),
(32, 'X/BAT/25/0007', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Extension', '', '2025-03-25', 'K.F', 'A.B', 'structural_drawings,land_title'),
(33, 'X/BAT/25/0008', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Install property', '', '2025-03-25', 'K.F', 'A.B', 'structural_drawings,land_title'),
(34, 'X/BAT/25/0009', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Alteration', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(35, 'X/BAT/25/0010', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Regularization', '', '2025-03-25', 'K.F', 'A.B', 'architectural_drawings,structural_drawings'),
(36, 'X/BAT/25/0011', 'Kusi Francis', 'Kumasi Kwadaso', 'Kwadaso', 'Kwadaso', 'Demolition', '', '2025-03-18', 'K.F', 'A.B', 'land_title');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `material_descriptions`
--

CREATE TABLE `material_descriptions` (
  `id` int(11) NOT NULL,
  `application_id` varchar(36) DEFAULT NULL,
  `foundations_materials` text DEFAULT NULL,
  `foundations_proportions` text DEFAULT NULL,
  `walls_materials` text DEFAULT NULL,
  `walls_proportions` text DEFAULT NULL,
  `floors_materials` text DEFAULT NULL,
  `floors_proportions` text DEFAULT NULL,
  `floors_joint_dimension` text DEFAULT NULL,
  `floors_covering_thickness` text DEFAULT NULL,
  `windows_types` text DEFAULT NULL,
  `windows_dimension` text DEFAULT NULL,
  `doors_types` text DEFAULT NULL,
  `doors_dimension` text DEFAULT NULL,
  `roof_types` text DEFAULT NULL,
  `roof_covering` text DEFAULT NULL,
  `roof_spacing_trusses` text DEFAULT NULL,
  `steps_materials` text DEFAULT NULL,
  `verandah_materials` text DEFAULT NULL,
  `fencing_materials` text DEFAULT NULL,
  `yards_details` text DEFAULT NULL,
  `outbuilding_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `material_descriptions`
--

INSERT INTO `material_descriptions` (`id`, `application_id`, `foundations_materials`, `foundations_proportions`, `walls_materials`, `walls_proportions`, `floors_materials`, `floors_proportions`, `floors_joint_dimension`, `floors_covering_thickness`, `windows_types`, `windows_dimension`, `doors_types`, `doors_dimension`, `roof_types`, `roof_covering`, `roof_spacing_trusses`, `steps_materials`, `verandah_materials`, `fencing_materials`, `yards_details`, `outbuilding_details`) VALUES
(27, 'X/BAT/25/0001', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(28, 'X/BAT/25/0002', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(29, 'X/BAT/25/0003', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(30, 'X/BAT/25/0004', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(31, 'X/BAT/25/0005', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(32, 'X/BAT/25/0006', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(33, 'X/BAT/25/0007', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(34, 'X/BAT/25/0008', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(35, 'X/BAT/25/0009', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(36, 'X/BAT/25/0010', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', ''),
(37, 'X/BAT/25/0011', 'Sand', '2', 'Cement', '5', 'Sand', '5', '12 by 12', '4', 'Wood', '4', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `permits`
--

CREATE TABLE `permits` (
  `permit_number` varchar(50) NOT NULL,
  `application_id` varchar(36) NOT NULL,
  `permit_holder` varchar(255) NOT NULL,
  `land_location` text NOT NULL,
  `construction_type` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `ppo_signature` varchar(255) NOT NULL,
  `we_signature` varchar(255) NOT NULL,
  `ppo_date` date NOT NULL,
  `we_date` date NOT NULL,
  `ppo_name` varchar(255) NOT NULL,
  `we_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permits`
--

INSERT INTO `permits` (`permit_number`, `application_id`, `permit_holder`, `land_location`, `construction_type`, `issue_date`, `expiry_date`, `ppo_signature`, `we_signature`, `ppo_date`, `we_date`, `ppo_name`, `we_name`, `created_at`) VALUES
('SPC/NTDA/DP-0001/25', 'X/BAT/25/0001', 'Kusi France', 'North Tongu District', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 05:40:50'),
('SPC/NTDA/DP-0002/25', 'X/BAT/25/0002', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 06:09:36'),
('SPC/NTDA/DP-0003/25', 'X/BAT/25/0003', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 06:23:01'),
('SPC/NTDA/DP-0004/25', 'X/BAT/25/0004', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 07:39:38'),
('SPC/NTDA/DP-0005/25', 'X/BAT/25/0005', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 08:06:41'),
('SPC/NTDA/DP-0006/25', 'X/BAT/25/0006', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 08:27:49'),
('SPC/NTDA/DP-0007/25', 'X/BAT/25/0007', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 08:41:03'),
('SPC/NTDA/DP-0008/25', 'X/BAT/25/0008', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 08:50:40'),
('SPC/NTDA/DP-0010/25', 'X/BAT/25/0010', 'Kusi France', 'Kwadaso', 'Residential', '2025-03-25', '2030-03-25', 'B.A', 'A.A', '2025-03-25', '2025-03-25', 'Afful Bismark', 'Aseye Abledu', '2025-03-25 16:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `permit_applications`
--

CREATE TABLE `permit_applications` (
  `application_id` varchar(36) NOT NULL,
  `applicant_name` varchar(255) DEFAULT NULL,
  `applicant_email` varchar(255) DEFAULT NULL,
  `applicant_mobile` varchar(50) DEFAULT NULL,
  `applicant_nationality` varchar(50) DEFAULT NULL,
  `applicant_gender` varchar(10) DEFAULT NULL,
  `permit_type` varchar(50) DEFAULT NULL,
  `applicant_address` text DEFAULT NULL,
  `agent_name` varchar(255) DEFAULT NULL,
  `agent_gender` varchar(10) DEFAULT NULL,
  `agent_address` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','deferred') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `permit_issued` tinyint(1) DEFAULT 0,
  `permit_number` varchar(20) DEFAULT NULL,
  `issue_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permit_applications`
--

INSERT INTO `permit_applications` (`application_id`, `applicant_name`, `applicant_email`, `applicant_mobile`, `applicant_nationality`, `applicant_gender`, `permit_type`, `applicant_address`, `agent_name`, `agent_gender`, `agent_address`, `status`, `created_at`, `permit_issued`, `permit_number`, `issue_date`) VALUES
('X/BAT/25/0001', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 05:39:37', 1, NULL, NULL),
('X/BAT/25/0002', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 05:51:36', 1, NULL, NULL),
('X/BAT/25/0003', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 06:22:26', 1, NULL, NULL),
('X/BAT/25/0004', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 07:38:50', 1, NULL, NULL),
('X/BAT/25/0005', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 08:05:44', 1, NULL, NULL),
('X/BAT/25/0006', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 08:27:24', 1, NULL, NULL),
('X/BAT/25/0007', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 08:40:30', 1, NULL, NULL),
('X/BAT/25/0008', 'Kusi France', 'kusi@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 08:50:10', 1, NULL, NULL),
('X/BAT/25/0009', 'Kusi France', 'Bismarkasante820@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 09:57:14', 0, NULL, NULL),
('X/BAT/25/0010', 'Kusi France', 'Bismarkasante820@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'approved', '2025-03-25 16:42:59', 1, NULL, NULL),
('X/BAT/25/0011', 'Kusi France', 'Bismarkasante820@gmail.com', '0543258791', 'Ghanaian', 'Male', 'Residential', 'Kumasi Kwadaso', 'Asante', 'Male', 'Kumasi Buokrom', 'deferred', '2025-03-25 16:53:28', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `application_id` varchar(36) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `uploaded_files`
--

INSERT INTO `uploaded_files` (`id`, `application_id`, `file_type`, `file_path`) VALUES
(35, 'X/BAT/25/0001', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0001_architectural_drawings_1742881177_RSPP_EN-US_SG_M05_FUNCTIONS_2.pdf'),
(36, 'X/BAT/25/0002', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0002_architectural_drawings_1742881896_RSPP_EN-US_SG_M05_FUNCTIONS_2.pdf'),
(37, 'X/BAT/25/0003', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0003_architectural_drawings_1742883746_RSDB_EN-US_SG_M06_TABLESANDDATATYPES.pdf'),
(38, 'X/BAT/25/0004', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0004_architectural_drawings_1742888330_RSPP_EN-US_SG_M05_MODSLIBRARIES.pdf'),
(39, 'X/BAT/25/0004', 'structure_reports', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0004_structure_reports_1742888330_RSPP_EN-US_SG_M05_DEBUGGINGTESTING.pdf'),
(40, 'X/BAT/25/0005', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0005_architectural_drawings_1742889944_RSPP_EN-US_SG_M05_DEBUGGINGTESTING.pdf'),
(41, 'X/BAT/25/0005', 'structure_reports', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0005_structure_reports_1742889944_RSPP_EN-US_SG_M05_SYSADMINISTRATION.pdf'),
(42, 'X/BAT/25/0006', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0006_architectural_drawings_1742891244_RSDB_EN-US_SG_M06_INSERTINGDATA.pdf'),
(43, 'X/BAT/25/0007', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0007_architectural_drawings_1742892030_RSPP_EN-US_SG_M05_FUNCTIONS_2.pdf'),
(44, 'X/BAT/25/0008', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0008_architectural_drawings_1742892610_RSDB_EN-US_SG_M06_TABLESANDDATATYPES.pdf'),
(45, 'X/BAT/25/0009', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0009_architectural_drawings_1742896634_RSDB_EN-US_SG_M06_INSERTINGDATA.pdf'),
(46, 'X/BAT/25/0009', 'structure_reports', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0009_structure_reports_1742896634_RSDB_EN-US_SG_M06_INSERTINGDATA.pdf'),
(47, 'X/BAT/25/0010', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0010_architectural_drawings_1742920979_RSDB_EN-US_SG_M06_DBINTERACTIONTRANSACTION.pdf'),
(48, 'X/BAT/25/0011', 'architectural_drawings', 'C:\\xampp\\htdocs\\building_permit_system\\uploads/X-BAT-25-0011_architectural_drawings_1742921608_Planning_Permit_Certificate_X_BAT_25_0001_2.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(25, 'Regular User', 'user@example.com', '$2y$10$jNSW6WPPXmBtPi./RzAvLezDHhIBMQwwQn63Mi0HEX9rfieqYYQzu', NULL, 'user', '2025-03-22 13:13:02'),
(26, 'Joojo Megas', 'kwadwomegas@gmail.com', '$2y$10$unJmNIprToc5T75L7V5O0uHIMS5YH/d8aQ1EyQg.9heVOZJgng5li', '0545041428', 'user', '2025-03-22 13:33:11'),
(27, 'Bismark Afful', 'kabslink@gmail.com', '$2y$10$Mi92I09.yzOsTKs.RpIFqun457..OCVA6O5RJEDNHU27uBvBcXPrW', '0543258791', 'admin', '2025-03-22 13:43:45'),
(28, 'Kusi France', 'kusi@gmail.com', '$2y$10$N/mIw8sKcykpfGPcR.LjAudUWPoGb/Q96OqHawMKaxwtcL02dgor6', '', 'admin', '2025-03-24 19:14:46'),
(29, 'Bismark', 'bismark@mail.com', '$2y$10$U2K5yFOnySBSP7I9yeLCPeGWCWSfiyKJshzQ654LK0kImU27F0mZC', '', 'user', '2025-03-24 19:47:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `application_details`
--
ALTER TABLE `application_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `material_descriptions`
--
ALTER TABLE `material_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `permits`
--
ALTER TABLE `permits`
  ADD PRIMARY KEY (`permit_number`),
  ADD KEY `fk_permits_application_id` (`application_id`);

--
-- Indexes for table `permit_applications`
--
ALTER TABLE `permit_applications`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `application_details`
--
ALTER TABLE `application_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `material_descriptions`
--
ALTER TABLE `material_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_details`
--
ALTER TABLE `application_details`
  ADD CONSTRAINT `application_details_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `permit_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_descriptions`
--
ALTER TABLE `material_descriptions`
  ADD CONSTRAINT `material_descriptions_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `permit_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `permits`
--
ALTER TABLE `permits`
  ADD CONSTRAINT `fk_permits_application_id` FOREIGN KEY (`application_id`) REFERENCES `permit_applications` (`application_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `permit_applications` (`application_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
