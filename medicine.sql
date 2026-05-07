-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 09:45 AM
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
-- Database: `medicine`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowers_slips`
--

CREATE TABLE `borrowers_slips` (
  `id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `category` enum('Student','Personnel') NOT NULL,
  `availability` enum('Yes','No') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrower_slip_items`
--

CREATE TABLE `borrower_slip_items` (
  `id` int(11) NOT NULL,
  `slip_id` int(11) NOT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `date_released` datetime DEFAULT NULL,
  `date_returned` datetime DEFAULT NULL,
  `remarks_purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `prescriber_name` varchar(100) DEFAULT NULL,
  `staff_name` varchar(100) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `medicine_id`, `quantity`, `action`, `patient_name`, `prescriber_name`, `staff_name`, `date`) VALUES
(450, 448, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(451, 449, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(452, 450, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(453, 451, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(454, 452, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(455, 453, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(456, 454, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(457, 455, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(458, 456, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(459, 457, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(460, 458, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(461, 459, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(462, 460, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(463, 461, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(464, 462, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(465, 463, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(466, 464, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(467, 465, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(468, 466, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(469, 467, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(470, 468, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:41:29'),
(471, 469, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(472, 470, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(473, 471, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(474, 472, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(475, 473, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(476, 474, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(477, 475, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(478, 476, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(479, 477, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(480, 478, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(481, 479, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(482, 480, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(483, 481, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(484, 482, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(485, 483, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(486, 484, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(487, 485, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(488, 486, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(489, 487, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(490, 488, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(491, 489, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:07'),
(492, 490, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(493, 491, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(494, 492, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(495, 493, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(496, 494, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(497, 495, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(498, 496, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(499, 497, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(500, 498, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(501, 499, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(502, 500, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(503, 501, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(504, 502, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(505, 503, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(506, 504, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(507, 505, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(508, 506, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(509, 507, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(510, 508, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(511, 509, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(512, 510, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(513, 511, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(514, 512, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(515, 513, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(516, 514, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(517, 515, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(518, 516, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(519, 517, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(520, 518, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(521, 519, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(522, 520, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(523, 521, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(524, 522, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(525, 523, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(526, 524, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(527, 525, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(528, 526, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(529, 527, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(530, 528, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(531, 529, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(532, 530, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(533, 531, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(534, 532, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(535, 533, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(536, 534, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(537, 535, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(538, 536, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(539, 537, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(540, 538, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(541, 539, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(542, 540, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(543, 541, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(544, 542, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(545, 543, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(546, 544, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(547, 545, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(548, 546, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(549, 547, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(550, 548, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(551, 549, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(552, 550, 0, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:17'),
(553, 551, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(554, 552, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(555, 553, 70, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(556, 554, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(557, 555, 35, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(558, 556, 3, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(559, 557, 40, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(560, 558, 13, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(561, 559, 500, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(562, 560, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(563, 561, 3, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(564, 562, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(565, 563, 35, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(566, 564, 4, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(567, 565, 200, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(568, 566, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(569, 567, 320, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(570, 568, 5, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(571, 569, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(572, 570, 680, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(573, 571, 360, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(574, 572, 70, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(575, 573, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(576, 574, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(577, 575, 5, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(578, 576, 3, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(579, 577, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(580, 578, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(581, 579, 24, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(582, 580, 24, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(583, 581, 36, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(584, 582, 24, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(585, 583, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(586, 584, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(587, 585, 150, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(588, 586, 600, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(589, 587, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(590, 588, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(591, 589, 150, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(592, 590, 300, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(593, 591, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(594, 592, 1000, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(595, 593, 1000, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(596, 594, 1000, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(597, 595, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(598, 596, 1000, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(599, 597, 50, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(600, 598, 150, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(601, 599, 150, 'Imported via CSV', NULL, NULL, NULL, '2026-05-04 05:42:23'),
(602, 600, 671, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(603, 601, 500, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(604, 602, 12, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(605, 603, 36, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(606, 604, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(607, 605, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(608, 606, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(609, 607, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(610, 608, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(611, 609, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(612, 610, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(613, 611, 3, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(614, 612, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(615, 613, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(616, 614, 500, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(617, 615, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(618, 616, 400, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(619, 617, 40, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(620, 618, 8, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(621, 619, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(622, 620, 200, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(623, 621, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(624, 622, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(625, 623, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(626, 624, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(627, 625, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(628, 626, 3, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(629, 627, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(630, 628, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(631, 629, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(632, 630, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(633, 631, 7, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(634, 632, 359, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(635, 633, 100, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(636, 634, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(637, 635, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(638, 636, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(639, 637, 200, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(640, 638, 200, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(641, 639, 250, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(642, 640, 250, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(643, 641, 500, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(644, 642, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(645, 643, 12, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(646, 644, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(647, 645, 4, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(648, 646, 8, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(649, 647, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(650, 648, 250, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(651, 649, 400, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(652, 650, 300, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(653, 651, 300, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(654, 652, 7, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(655, 653, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(656, 654, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(657, 655, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(658, 656, 10, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(659, 657, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(660, 658, 300, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(661, 659, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(662, 660, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(663, 661, 2, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(664, 662, 1, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(665, 663, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(666, 664, 20, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(667, 665, 30, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(668, 666, 6, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(669, 667, 15, 'Imported via CSV', NULL, NULL, NULL, '2026-05-05 01:05:09'),
(670, 600, 671, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:30:43'),
(671, 604, 20, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:31:41'),
(672, 605, 10, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:32:25'),
(673, 607, 30, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:33:12'),
(674, 612, 30, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:33:49'),
(675, 614, 500, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:34:27'),
(676, 616, 400, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:35:05'),
(677, 617, 40, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:35:40'),
(678, 620, 200, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:36:14'),
(679, 622, 15, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:37:10'),
(680, 623, 100, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:37:51'),
(681, 624, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:39:00'),
(682, 624, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:39:19'),
(683, 625, 100, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:40:00'),
(684, 628, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:40:36'),
(685, 629, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:41:19'),
(686, 630, 15, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:42:32'),
(687, 631, 7, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:43:10'),
(688, 638, 200, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:43:51'),
(689, 639, 250, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:44:31'),
(690, 640, 250, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:45:11'),
(691, 642, 20, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:46:02'),
(692, 645, 4, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:46:57'),
(693, 646, 8, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:47:25'),
(694, 648, 250, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:47:56'),
(695, 651, 300, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:48:36'),
(696, 658, 300, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:54:59'),
(697, 659, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:55:41'),
(698, 661, 2, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:56:17'),
(699, 662, 1, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:56:48'),
(700, 663, 30, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:57:18'),
(701, 664, 20, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:57:51'),
(702, 665, 30, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:58:40'),
(703, 666, 6, 'Item Updated', NULL, NULL, NULL, '2026-05-05 01:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `label` varchar(50) DEFAULT NULL,
  `batch_number` int(11) DEFAULT 1,
  `type` varchar(20) DEFAULT 'medicine',
  `category` varchar(50) DEFAULT 'General',
  `unit` varchar(20) DEFAULT 'pcs',
  `quantity` int(11) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `brand_serial` varchar(255) DEFAULT NULL,
  `ris_id` varchar(255) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `qty_serviceable` int(11) DEFAULT 0,
  `qty_unserviceable` int(11) DEFAULT 0,
  `qty_repair` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `label`, `batch_number`, `type`, `category`, `unit`, `quantity`, `is_archived`, `brand_serial`, `ris_id`, `color`, `date_acquired`, `qty_serviceable`, `qty_unserviceable`, `qty_repair`, `remarks`, `expiration_date`, `created_at`) VALUES
(469, 'Manual dental chair', '', 1, 'dental', '', '1 set', 1, 0, '', 'N/A', 'brown', NULL, 1, 0, 0, 'Borrowed from Soler?s Dental Clinic.', NULL, '2026-05-04 05:42:07'),
(470, 'Stainless Sink ( 60X60X 80cm)', '', 1, 'dental', '', '1 set', 0, 0, '', 'ICS 2023-02-0008', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(471, 'Single Burner Electric Stove', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS 2023-02-0008', 'gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(472, 'Dental Breech Loading Metallic Cartridge ( Aspirating syringe)', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS 2024-01-0006', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(473, 'Complete Set Dental Forceps, Upper', '', 1, 'dental', '', '1 set', 0, 0, '', 'ICS SPHV-2024-08-0244', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(474, 'Dental Extracting Forceps 150s Upper molar incisors surgical stainless steel', '', 1, 'dental', '', '1 set', 0, 0, '', 'ICS 2024-01-0010', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(475, 'Dental stool, saddle backrest, leather lifting swivel', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS # 2024-03-0277', 'white', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(476, 'Refine Ultrasonic scaler model', '', 1, 'dental', '', '1 unit', 0, 0, 'P5LC81464', 'ICS 2024-01-0040', 'white', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(477, 'Dental Mirror, Stainless Steel', '', 1, 'dental', '', '10 pcs', 0, 0, '', 'ICS 2024-01-0014', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(478, 'Dental LED Light cure', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS 2024-01-0013', 'white', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(479, 'Dental Extraction Elevator, Mix straight and curve (1 set)', '', 1, 'dental', '', '12 pcs', 0, 0, '', 'ICS 2024-01-0011', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(480, 'Dental Explorer Stainless double hook', '', 1, 'dental', '', '4 unit', 0, 0, '', 'ICS 2024-01-0009', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(481, 'Dental Cross  Bar, Stainless, Lower', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS 2024-01- 0007', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(482, 'Dental Forceps, Lower, Stainless Steel', '', 1, 'dental', '', '1 unit', 0, 0, '', 'ICS 2024-01-0012', 'Silver gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(483, 'Water Flosser', '', 1, 'dental', '', '1 unit', 0, 0, 'Waterpulse V300', 'ICS 2024-01-0041', 'White/ blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(484, 'Gum separator', '', 1, 'dental', '', '2 units', 0, 0, '', 'RIS # 2024-01-0003', 'Silver gray', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(485, 'Cotton plier with lock', '', 1, 'dental', '', '2 units', 0, 0, '', 'RIS # 2024-01-0003', 'Silver gray', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(486, 'Drill Handpiece kit with LED, standard 2-holes', '', 1, 'dental', '', '1 set', 0, 0, 'NSK- Pana-Max/ C3030320', 'ICS SPLV-2024-05-0178', 'gray', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(487, 'Tooth Model Oversized Model (premolars and molars featuring many common dental problems)', '', 1, 'dental', '', '1 set', 0, 0, '', 'ICS SPLV-2024-09-0460', 'white', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(488, 'LED Headlamp', '', 1, 'dental', '', '3 unit', 0, 0, 'Firefly (FEL561)', 'ICS SPLV-2024-12-0546', 'black', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(489, 'Dental Chair with compressor', '', 1, 'dental', '', '1 set', 0, 0, '', '', '', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:07'),
(490, 'Wheel chair', '', 1, 'medical', '', '1 unit', 0, 0, '', 'RIS No. 21-6-136', 'Blue black', NULL, 0, 0, 0, 'Donated by LGU year 2021', NULL, '2026-05-04 05:42:17'),
(491, 'Spine board', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS SPLV-2024-05-0158 (LGU)', 'ORANGE', NULL, 0, 0, 0, 'Donated by LGU on 1-1-2021', NULL, '2026-05-04 05:42:17'),
(492, 'BP Apparatus/ Aneroid sphygmomanometer', '', 1, 'medical', '', '3 sets', 0, 0, '', 'ICS  MDLS- 50203080-149', 'BLUE', NULL, 0, 0, 0, '#51- additional 2 units in stock', NULL, '2026-05-04 05:42:17'),
(493, 'Stethoscope', '', 1, 'medical', '', '3 units', 0, 0, '', 'ICS  MDLS- 50203080-150', 'BLUE', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(494, 'Pulse Oximeter', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS  MDLS- 50203080-130', 'BLACK', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(495, 'Glucometer', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS  MDLS- 50203080-152', 'white', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(496, 'Infrared Thermometer-thermal gun type', '', 1, 'medical', '', '2 units', 0, 0, '', 'N/A', 'White and green', NULL, 0, 0, 0, 'Donated by LGU year 2021', NULL, '2026-05-04 05:42:17'),
(497, 'UV light for Disinfection', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS  MDLS- 50203080-128', 'BLACK', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(498, 'Ice pack Bag', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS  MDLS- 50203080-138', 'Green and blue', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(499, 'Medical Oxygen Tank, 10 lbs.', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS  MDLS- 50203080-143', 'green', NULL, 0, 0, 0, 'For refill', NULL, '2026-05-04 05:42:17'),
(500, 'Oxygen regulator', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS  MDLS- 50203080-144', 'green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(501, 'Portable folding bed, 40kg load cap.', '', 1, 'medical', '', '4 pcs', 0, 0, '', 'ICS  MDLS- 50203080-143', 'Blue and orange', NULL, 0, 0, 0, 'Unserviceable were placed in the HSO stockroom.', NULL, '2026-05-04 05:42:17'),
(502, 'Portable height measuring scale', '', 1, 'medical', '', '1 pcs', 0, 0, '', 'ICS  MDLS- 50203080-148', 'white', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(503, 'Nebulizer Machine', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS  MDLS- 50203080-155', 'White blue', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(504, 'Bathroom weighing scale', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS  MDLS- 50203080-147', 'black', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(505, 'Wound dressing minor set', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS  MDLS- 50203080-157', 'Light green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(506, 'Triangular Bandage', '', 1, 'medical', '', '4 units', 0, 0, '', 'ICS  MDLS- 50203080-137', 'white', NULL, 0, 0, 0, '4- unused', NULL, '2026-05-04 05:42:17'),
(507, 'Mayo Scissor', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-158', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(508, 'Tissue forceps', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-162', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(509, 'Surgical scissor', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-159', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(510, 'Sponge holding forceps', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-161', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(511, 'Bandage scissors', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-160', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(512, 'Straight Kelly forceps', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-163', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(513, 'Curve Kelly forceps', '', 1, 'medical', '', '1 pc', 0, 0, '', 'ICS  MDLS- 50203080-164', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(514, 'Pillow with pillowcase', '', 1, 'medical', '', '3 pcs', 0, 0, '', 'ICS  MDLS- 50203080-132', 'blue', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(515, 'Dressing tray with cover', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS  MDLS- 50203080-166', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(516, 'Kidney basin (plastic)', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS  MDLS- 50203080-165', 'green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(517, '1st Aid Box', '', 1, 'medical', '', '23 units', 0, 0, '', '2022-DME-50203070-NON CSE', 'Red', '0000-00-00', 0, 0, 0, 'Borrowed by 20 offices', NULL, '2026-05-04 05:42:17'),
(518, 'Hand-held vacuum cleaner  (3-in-1)', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS 2023-10-0293', 'gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(519, 'Ultra low volume sprayer (misting) 1200W', '', 1, 'medical', '', '2 set', 0, 0, '', 'ICS 2024-01-0029', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(520, 'Medical IV stand w/ 4 hooks, rubber wheels', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS 2024-01-0028', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(521, 'Gooseneck lamp with wheels, LED light', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS 2024-01-0027', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(522, 'Bathroom weighing scale', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS 2024-01-0025', 'RED', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(523, 'UV Light Disinfection', '', 1, 'medical', '', '2 units', 0, 0, '', 'ICS 2024-01-0024', 'black', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(524, 'Foam Poly standard', '', 1, 'medical', '', '1 unit', 0, 0, 'MANDAUE foam brand', 'ICS SPLV -2024-10-0516', 'green', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(525, 'Bathroom weighing scale', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS SPLV-2024-09-0458', 'RED', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(526, 'Weighing Scale', '', 1, 'medical', '', '1 unit', 0, 0, 'DETECTO', '', 'White black', NULL, 0, 0, 0, 'Donated by LGU', NULL, '2026-05-04 05:42:17'),
(527, 'Diagnostic Penlight', '', 1, 'medical', '', '2 sets', 0, 0, '', 'ICS: SPLV- 2024-09-0459', 'WHITE', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(528, 'Two Crank hospital Bed', '', 1, 'medical', '', '2 sets', 0, 0, '', 'ICS SPHV- 2024-08-0257', 'BLUE WHITE', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(529, 'IV Stand 4 hooks', '', 1, 'medical', '', '2 sets', 0, 0, '', 'ICS- SPLV-2024-08-0412', 'silver', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(530, 'Walking cane Stick, 4 legs', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPLV-2024-08-0413', 'silver', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(531, 'Otoscope, Ophthalmoscope diagnostic set with soft case', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPHV-2024-08-0257', 'Green Black', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(532, 'Sturdy Steam Sterilizer SA-230', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPHV-2024-09-0300', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(533, 'Bedpan Stainless steel', '', 1, 'medical', '', '1 unit', 0, 0, 'DR. CARE', 'ICS SPLV-2024-10-0517', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(534, 'Instrument tray with cover (18?x 12?)', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS SPLV-2024-10-0518', 'silver', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(535, 'Fetal doppler', '', 1, 'medical', '', '1 unit', 0, 0, '', 'RIS # 2023-07-0267', 'White/pink', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(536, 'Gooseneck lamp with wheels, LED light', '', 1, 'medical', '', '1 set', 0, 0, '', 'RIS # 2023-07-0267', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(537, 'Medical IV stand w/ 4 hooks, rubber wheels', '', 1, 'medical', '', '2 sets', 0, 0, '', 'RIS # 2023-07-0267', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(538, 'Crutches', '', 1, 'medical', '', '1 set', 0, 0, '', 'RIS # 2023-07-0267', 'gray', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(539, 'Thermo gun', '', 1, 'medical', '', '2 sets', 0, 0, '', 'RIS # 2023-07-0267', 'blue', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(540, 'BP Apparatus/ Aneroid sphygmomanometer', '', 1, 'medical', '', '2 sets', 0, 0, 'ALRK2', 'ICS SPLV-2024-12-0544', 'black', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(541, 'Stethoscope', '', 1, 'medical', '', '3 set', 0, 0, 'ALRK2', '', 'BLACK', '0000-00-00', 0, 0, 0, '2- unused', NULL, '2026-05-04 05:42:17'),
(542, 'Baby Nursing Table, wall mounted', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPHV-2024-12-0373', 'WHITEGRAY', '0000-00-00', 0, 0, 0, 'uninstalled', NULL, '2026-05-04 05:42:17'),
(543, 'Mayo instrument stainless table', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPHV-2024-12-0374', 'silver', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(544, 'Examination table', '', 1, 'medical', '', '1 unit', 0, 0, '', '', 'Black/white', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(545, 'CPR Mannequin', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS-SPLV-2025-03-0031', 'Flesh', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(546, 'Ambu Bag w/ mask', '', 1, 'medical', '', '1 set', 0, 0, '', 'ICS SPLV-2025-03-0032', 'White', '0000-00-00', 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(547, 'Pulse Oximeter', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS-SPLV-2026-03-0042', 'White', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(548, 'Medical Oxygen Tank w/ Regulator', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS-SPLV-2026-03-0070', 'Green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(549, 'BP Aneroid w/ Wheels', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS-SPLV-026-03-0041', 'Dark green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(550, 'Medical Oxygen Tank trolley', '', 1, 'medical', '', '1 unit', 0, 0, '', 'ICS-SPLV-2026-03-0038', 'Green', NULL, 0, 0, 0, '', NULL, '2026-05-04 05:42:17'),
(551, 'Alcohol 70% Solution, 500ml', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(552, 'Alcohol 70% Soln (Spray), 1L', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(553, 'Alcohol 70% soln, 60 ml', '', 1, 'consumable', '', '', 70, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(554, 'Cotton rolls, 400mg', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(555, 'Cotton rolls, 10 mg', '', 1, 'consumable', '', '', 35, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(556, 'Coton rolls, 90 mg', '', 1, 'consumable', '', '', 3, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(557, 'Surgical Gloves, size 7 box', '', 1, 'consumable', '', '', 40, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(558, 'Clean Gloves', '', 1, 'consumable', '', '', 13, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(559, 'Medical Mask (N95)', '', 1, 'consumable', '', '', 500, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(560, 'Disposable mask (Medical)', '', 1, 'consumable', '', '', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(561, 'Non- woven plaster, hypoallergenic 12 rolls /box', '', 1, 'consumable', '', '', 3, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(562, 'Plaster (leukoplast) 12 rolls/ box', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(563, 'Betadine soln, 60 ml', '', 1, 'consumable', '', '', 35, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(564, 'Betadine soln, gal.', '', 1, 'consumable', '', '', 4, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(565, 'Pregnancy Kit', '', 1, 'consumable', '', '', 200, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(566, 'Sanitary pad, 9?s', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(567, 'Sterile Gauze pad, box', '', 1, 'consumable', '', '', 320, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(568, 'Non-sterile gauze pad', '', 1, 'consumable', '', '', 5, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(569, 'Hazmat (white/blue) set', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(570, 'Head cap (blue)', '', 1, 'consumable', '', '', 680, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(571, 'Sterile cotton applicator', '', 1, 'consumable', '', '', 360, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(572, 'Single-use surgical drape', '', 1, 'consumable', '', '', 70, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(573, 'Elastic Bandage, 4inch x 5 yards', '', 1, 'consumable', '', '', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(574, 'Arm sling (small)', '', 1, 'consumable', '', '', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(575, 'Arm sling (medium)', '', 1, 'consumable', '', '', 5, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(576, 'Arm sling (large)', '', 1, 'consumable', '', '', 3, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(577, 'Nasal Cannula', '', 1, 'consumable', '', '', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(578, 'Nebulizer kit', '', 1, 'consumable', '', '', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(579, 'Suture silk  4/0 (braded) non- absorbable , 12?s', '', 1, 'consumable', '', '', 24, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(580, 'Suture silk 3/0 (braded) non- absorbable , 12?s', '', 1, 'consumable', '', '', 24, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(581, 'Suture chromic 3/0 (catgut) absorbable , 12?s', '', 1, 'consumable', '', '', 36, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(582, 'Suture chromic 2/0 (catgut) absorbable, 12?s', '', 1, 'consumable', '', '', 24, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(583, 'Sterile tongue depressor, 100pcs/ box', '', 1, 'consumable', '', '', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(584, 'Abdominal binder', '', 1, 'consumable', '', '', 1, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(585, 'Band-aid, 25/pack', '', 1, 'consumable', '', '', 150, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(586, 'Paper bag ( brown #3), 100/ pack', '', 1, 'consumable', '', '', 600, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(587, 'Disinfectant, 1 gal', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(588, 'Alcohol 70 % soln., 1 gal', '', 1, 'consumable', '', '', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(589, 'Insulin syringe (29Gx1/2?), 100 pcs/ box', '', 1, 'consumable', '', '', 150, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(590, 'Disposable syringe (23Gx1?) , 100 pcs/ box', '', 1, 'consumable', '', '', 300, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(591, 'Disposable syringe (25Gx1?)', '', 1, 'consumable', '', '', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(592, 'Dental bib, 100 pcs/ pack', '', 1, 'consumable', '', '', 1000, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(593, 'Saliva ejector, 100 pcs/ pack', '', 1, 'consumable', '', '', 1000, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(594, 'Non- woven sponge (2x2x4 ply), 200 pcs/ pack', '', 1, 'consumable', '', '', 1000, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(595, 'Autoclave tape (1/2x 50m)', '', 1, 'consumable', '', '', 1, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(596, 'Dental Needle (30Gx 21mm), 100/box', '', 1, 'consumable', '', '', 1000, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(597, 'Cavity varnish Premium, 0.4ml per kit', '', 1, 'consumable', '', '', 50, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(598, 'Lancet for glucometer', '', 1, 'consumable', '', '', 150, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(599, 'Test strips for glucometer', '', 1, 'consumable', '', '', 150, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-04 05:42:23'),
(600, 'Aluminum Hydroxide Magnesium Hydroxide Simeticone (178mg/233mg, Branded)', '', 1, 'medicine', 'General', 'piece', 671, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-05-31', '2026-05-05 01:05:09'),
(601, 'Ambroxol HCl 75mg/tab', '', 1, 'medicine', '', 'piece', 500, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(602, 'Amoxicillin 500mg, 100\'s', '', 1, 'medicine', '', 'box', 12, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(603, 'Ascorbic Acid 500mg tablet, 100\'s', '', 1, 'medicine', '', 'box', 36, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(604, 'Azithromycin 500mg, box of 3', '', 1, 'medicine', 'General', 'box', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-08-31', '2026-05-05 01:05:09'),
(605, 'Benzydamine Hydrochloride 3mg lozenges, mint', '', 1, 'medicine', 'General', 'piece', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-04-30', '2026-05-05 01:05:09'),
(606, 'Benzydamine Hydrochloride 3mg/ml throat spray, non- steroidal, anti- inflammatory drug, sugar free', '', 1, 'medicine', '', 'piece', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(607, 'Betahistine Hydrocloride 24mg', '', 1, 'medicine', 'General', 'piece', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-07-31', '2026-05-05 01:05:09'),
(608, 'Blumea Balsamifera Sambong tab', '', 1, 'medicine', '', 'piece', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(609, 'Budesonide neb, 500mcg 2ml', '', 1, 'medicine', '', 'piece', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(610, 'Calcium Lactate 325 mg, 100\'s', '', 1, 'medicine', '', 'box', 1, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(611, 'Calcium+ Vit. D3+ Minerals tab, box of 60', '', 1, 'medicine', '', 'box', 3, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(612, 'Captopril 25mg', '', 1, 'medicine', 'General', 'piece', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-10-31', '2026-05-05 01:05:09'),
(613, 'Captopril 50mg', '', 1, 'medicine', '', 'piece', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(614, 'Carbocisteine 500mg (branded)', '', 1, 'medicine', 'General', 'piece', 500, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-05-31', '2026-05-05 01:05:09'),
(615, 'Cefalexin monohydrate 500mg', '', 1, 'medicine', '', 'box', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(616, 'Cefuroxime Axetil (500mg)', '', 1, 'medicine', 'General', 'piece', 400, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-12-31', '2026-05-05 01:05:09'),
(617, 'Celecoxib (200mg)', '', 1, 'medicine', 'General', 'piece', 40, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-01-31', '2026-05-05 01:05:09'),
(618, 'Cetirizine Hydrochloride 10 mg. tab, box of 100 tabs', '', 1, 'medicine', '', 'box', 8, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(619, 'Clonidine 150 mcg , 100\'s', '', 1, 'medicine', '', 'box', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(620, 'Co-Amoxiclav 625mg', '', 1, 'medicine', 'General', 'piece', 200, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-02-29', '2026-05-05 01:05:09'),
(621, 'Cold Spray 50ml (Immediate cooling,relieves pain from minor injuries;\n Contents: menthol, camphor, a', '', 1, 'medicine', '', 'piece', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(622, 'Dichlorobenzyl Alcohol Amylmetacresol lozenges pack of 8\'s', '', 1, 'medicine', 'General', 'pack', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-03-14', '2026-05-05 01:05:09'),
(623, 'Dicycloverine HCl 10mg', '', 1, 'medicine', 'General', 'piece', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-07-31', '2026-05-05 01:05:09'),
(624, 'Diphenhydramine Hydrochloride 50mg, 100\'s', '', 1, 'medicine', 'General', 'box', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-03-31', '2026-05-05 01:05:09'),
(625, 'Domperidone 10 mg', '', 1, 'medicine', 'General', 'pcs', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-09-30', '2026-05-05 01:05:09'),
(626, 'Epinephrine ampule, 1mg/ml', '', 1, 'medicine', '', 'piece', 3, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(627, 'Erythromycin Stearate (500mg)', '', 1, 'medicine', '', 'piece', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(628, 'Eye drops (Branded, itchy eye formula, 7.5 ml - 15 ml)', '', 1, 'medicine', 'General', 'piece', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-07-31', '2026-05-05 01:05:09'),
(629, 'Ferrous Sulfate + Folic Acid (300mg/250 mcg) tab, box of 100', '', 1, 'medicine', 'General', 'box', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-05-31', '2026-05-05 01:05:09'),
(630, 'Furosemide 40mg', '', 1, 'medicine', 'General', 'piece', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-07-31', '2026-05-05 01:05:09'),
(631, 'Gabapentin 300 mg cap/tab', '', 1, 'medicine', 'General', 'piece', 7, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-06-30', '2026-05-05 01:05:09'),
(632, 'Hyoscine N-Butylbromide (10mg)', '', 1, 'medicine', '', 'piece', 359, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(633, 'Hyoscine N-Butylbromide with Paracetamol (10mg/500mg, branded)', '', 1, 'medicine', '', 'piece', 100, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(634, 'Ibuprofen (200mg)', '', 1, 'medicine', '', 'piece', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(635, 'Influenza Vaccine quadrivalent single shot (FDA Approved)', '', 1, 'medicine', '', 'vial/ pre-filled syr', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(636, 'Lagundi 600mg tab., box of 100', '', 1, 'medicine', '', 'box', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(637, 'Loperamide HCl 2mg', '', 1, 'medicine', '', 'piece', 200, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(638, 'Loratadine (10mg, chewable)', '', 1, 'medicine', 'General', 'piece', 200, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-08-31', '2026-05-05 01:05:09'),
(639, 'Losartan (50mg)', '', 1, 'medicine', 'General', 'piece', 250, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-06-30', '2026-05-05 01:05:09'),
(640, 'Meclizine HCl (25mg Chewable Tablet, branded)', '', 1, 'medicine', 'General', 'piece', 250, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-02-29', '2026-05-05 01:05:09'),
(641, 'Mefenamic Acid 500mg cap', '', 1, 'medicine', '', 'piece', 500, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(642, 'Metoclopramide hydrochloride 10mg', '', 1, 'medicine', 'General', 'piece', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-06-30', '2026-05-05 01:05:09'),
(643, 'Montelukast Na + Levocetirizine diHCl 10 mg/5 mg - branded, box of 30\'s', '', 1, 'medicine', '', 'box', 12, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(644, 'Multivitamins + Iron - capsule/tablet , 100\'s', '', 1, 'medicine', '', 'box', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(645, 'Mupirocin 2% Antibacterial ointment, 5mg', '', 1, 'medicine', 'General', 'piece', 4, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-02-29', '2026-05-05 01:05:09'),
(646, 'N-Acetylcysteine 600mg effervescent tab, box of 20\'s', '', 1, 'medicine', 'General', 'box', 8, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-12-31', '2026-05-05 01:05:09'),
(647, 'Nifedipine 30 mg, tab', '', 1, 'medicine', '', 'tab', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(648, 'ORS - 75 Replacement Oral Rehydration Salts', '', 1, 'medicine', 'General', 'sachet', 250, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2029-03-31', '2026-05-05 01:05:09'),
(649, 'Paracetamol 500 mg tab', '', 1, 'medicine', '', 'piece', 400, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(650, 'Paracetamol Phenylpropanolamine HCl Chlrophenamine maleate 500mg/25mg/2mg - branded', '', 1, 'medicine', '', 'piece', 300, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(651, 'Phenylephrine HCl + Chlorphenamine Maleate + Paracetamol tab (10mg/2mg/500mg) - branded', '', 1, 'medicine', 'General', 'piece', 300, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-07-31', '2026-05-05 01:05:09'),
(652, 'Phenylpropanolamine HCl 15mg + Brompheniramine Maleate 12mg Tablet, 100\'s- branded', '', 1, 'medicine', '', 'box', 7, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(653, 'Pneumococcal vaccine single shot (FDA Approved)', '', 1, 'medicine', '', 'vial/ pre-filled syr', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(654, 'Retinol Palmitate (100,000 IU)/Vitamin A soft gel, 100\'s', '', 1, 'medicine', '', 'bot', 1, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(655, 'Salbutamol (2mg)', '', 1, 'medicine', '', 'piece', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(656, 'Salbutamol +Ipratropium bromide neb, 2.5ml/500mcg', '', 1, 'medicine', '', 'piece', 10, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(657, 'Salbutamol nebule, 1mg/ml 2ml', '', 1, 'medicine', '', 'piece', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(658, 'Sambucus Nigra etc., 36 mg tablet - branded', '', 1, 'medicine', 'General', 'piece', 300, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-10-31', '2026-05-05 01:05:09'),
(659, 'Silver sulfadiazine', '', 1, 'medicine', 'General', 'piece', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-09-30', '2026-05-05 01:05:09'),
(660, 'Tetanus Toxoid (Adsorbed Vaccine) 0.5ml per vial branded and FDA approved', '', 1, 'medicine', '', 'piece', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09'),
(661, 'Tetrahydrozoline HCI eye drops (Branded, Red Eyes Formula, 7.5 ml - 15 ml)', '', 1, 'medicine', 'General', 'piece', 2, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-10-31', '2026-05-05 01:05:09'),
(662, 'Tobramycin + Dexamethasone 3mg/1mg Eye Drops x5ml', '', 1, 'medicine', 'General', 'piece', 1, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-01-31', '2026-05-05 01:05:09'),
(663, 'Tramadol 50 mg', '', 1, 'medicine', 'General', 'pc', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-07-31', '2026-05-05 01:05:09'),
(664, 'Tramadol HCl + Paracetamol (37.5mg / 325mg)', '', 1, 'medicine', 'General', 'piece', 20, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2028-11-30', '2026-05-05 01:05:09'),
(665, 'Tranexamic (500mg)', '', 1, 'medicine', 'General', 'pc', 30, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-06-30', '2026-05-05 01:05:09'),
(666, 'Vitamin B Complex ( b1=100mg, B6= 10mg, B12= 50mcg) 100s', '', 1, 'medicine', 'General', 'box', 6, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2027-02-28', '2026-05-05 01:05:09'),
(667, 'Zinc Oxide + Camomile cream, 3.5g', '', 1, 'medicine', '', 'piece', 15, 0, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, '2026-05-05 01:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', '$2y$10$b.PjaH9ZSfaebibAvWXu5esiVpQUnhfOdBMHGJBJMUb9CuT2Ub1AK', 'System Administrator', '2026-05-04 08:42:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowers_slips`
--
ALTER TABLE `borrowers_slips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrower_slip_items`
--
ALTER TABLE `borrower_slip_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_slip` (`slip_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowers_slips`
--
ALTER TABLE `borrowers_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `borrower_slip_items`
--
ALTER TABLE `borrower_slip_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=704;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=668;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrower_slip_items`
--
ALTER TABLE `borrower_slip_items`
  ADD CONSTRAINT `fk_slip` FOREIGN KEY (`slip_id`) REFERENCES `borrowers_slips` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
