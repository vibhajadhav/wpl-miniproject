-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2025 at 09:07 AM
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
-- Database: `pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `gstno` varchar(20) NOT NULL,
  `company` varchar(100) NOT NULL,
  `c_address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`gstno`, `company`, `c_address`) VALUES
('19ABCDE5678G1Z4', 'HealthSupplies Inc', 'New Delhi, Delhi, India'),
('19LMNOP1234T1Z7', 'HealthPharm Solutions', 'Surat, Gujarat, India'),
('19LMNOP45670IZ8', 'Healthell Pharma', 'Bhubaneswar, Odisha, India'),
('21ABCDE8765T1Z3', 'Lifescience Enterprises', 'Pune, Maharashtra, India'),
('21LMNOP1234R1Z2', 'CureLife Industries', 'Chandigarh, Punjab, India'),
('21ZYXWV9876R1Z4', 'MediPro Solutions', 'Nagpur, Maharashtra, India'),
('22ABCDE2345P1Z9', 'MedicAid Pharma', 'Noida, Uttar Pradesh, India'),
('22LMNOP1234Q1Z8', 'WellMed Pharma', 'Hyderabad, Telangana, India'),
('22LMNOP7896R1Z4', 'PharmaCare Ltd', 'Indore, Madhya Pradesh, India'),
('27LMNOP1234T1Z9', 'PharmXpert Pvt Ltd', 'Kochi, Kerala, India'),
('27XYZFG1234H1Z9', 'PharmaPlus Ltd', 'Bengaluru, Karnataka, India'),
('27XYZLM2345H1Z1', 'MedSupply Solutions', 'Jaipur, Rajasthan, India'),
('27ZYXWV8765T1Z6', 'Lifelus Pharma', 'Patna, Bihar, India'),
('27ZYXWV9876P1Z5', 'Pharma World', 'Ahmedabad, Gujarat, India'),
('29ABCDE1234F1Z5', 'MedCorp Pvt Ltd', 'Mumbai, Maharashtra, India'),
('29ABCDE2345T1Z3', 'Medica Health Pvt Ltd', 'Kanpur, Uttar Pradesh, India'),
('29ABCDE7896G1Z5', 'CureMedic Enterprises', 'Lucknow, Uttar Pradesh, India'),
('29GHIJKL6789F1Z4', 'PharmaTech Pvt Ltd', 'Bhopal, Madhya Pradesh, India'),
('29GHJKL1234R1Z2', 'MediCare Co.', 'Chennai, Tamil Nadu, India'),
('29HJKMN2345Q1Z6', 'MediSupply Pvt Ltd', 'Kolkata, West Bengal, India');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneno` bigint(20) NOT NULL,
  `address` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`username`, `email`, `phoneno`, `address`) VALUES
('anita', 'anita@xyz.com', 8948491042, 'delhi rohini sector'),
('badapple', 'badapple@xyz.com', 8948491031, 'pune karve road'),
('brajesh', 'brajesh@xyz.com', 8948491027, 'chennai mount road'),
('brijesh', 'brijesh@xyz.com', 8948491043, 'hyderabad madhapur area'),
('example', 'example@gmail.com', 1234567890, ''),
('hello', 'rayan.kokate@somaiya.edu', 987654320, ''),
('lankesh', 'lankesh@xyz.com', 8948491038, 'kolkata salt lake'),
('om', 'omiii@xyz.com', 8948491032, 'lucknow gomti nagar'),
('prajesh', 'prajesh@xyz.com', 8948491026, 'bangalore koramangala road'),
('pratibha', 'pratibha@xyz.com', 8948491039, 'mumbai andheri west'),
('rajesh', 'rajesh@xyz.com', 8948491025, 'delhi sector 5'),
('rakesh', 'rakesh@xyz.com', 8948491037, 'bangalore mg road'),
('Rayan', 'Rayan@xyz.com', 8948491036, 'Ghatkopar (west), Mumbai'),
('riya', 'riyaj@xyz.com', 8948491035, 'delhi connaught place'),
('Ryan', 'rayankokate18@gmail.com', 1234567890, ''),
('saniya', 'saniya@xyz.com', 8948491029, 'mumbai bandra east'),
('sarita', 'sarita@xyz.com', 8948491030, 'delhi lajpat nagar'),
('vibha', 'vibhaj@xyz.com', 8948490024, 'mumbai central'),
('vijay', 'vijay@xyz.com', 8948491028, 'kolkata park street'),
('vinay', 'vinay@xyz.com', 8948491041, 'chennai adyar area'),
('vineta', 'vinetaaa@xyz.com', 8948491040, 'pune kalyani nagar'),
('vyom', 'vyom@xyz.com', 8948491033, 'hyderabad banjara hills'),
('yugeveer', 'yugeveer@xyz.com', 8948491034, 'ahmedabad ellis bridge');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `d_name` varchar(50) NOT NULL,
  `d_id` int(11) NOT NULL,
  `d_phone` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`d_name`, `d_id`, `d_phone`) VALUES
('Amit Sharma', 1, 9876543210),
('Ravi Kumar', 2, 9887654321),
('Suresh Patel', 3, 9988776655),
('Rahul Yadav', 4, 9456123456),
('Vikram Singh', 5, 9365482379),
('Anil Gupta', 6, 9756123480),
('Pankaj Mehta', 7, 9665544332),
('Ajay Bhardwaj', 8, 9223344556),
('Manoj Joshi', 9, 9387445567),
('Sandeep Verma', 10, 9098765432),
('Karan Thakur', 11, 9187654321),
('Rakesh Kumar', 12, 9246123478),
('Mohit Agarwal', 13, 9325874012),
('Pradeep Nair', 14, 9192837465),
('Deepak Sharma', 15, 9574839201),
('Sunil Chaudhary', 16, 9347501823),
('Nikhil Reddy', 17, 9678490234),
('Arvind Verma', 18, 9726438590),
('Shivendra Singh', 19, 9988772233),
('Sushil Kumar', 20, 9198765433);

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `m_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `m_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`m_name`, `price`, `m_id`) VALUES
('Paracetamol', 31.00, 1),
('Aspirin', 41.00, 2),
('Ibuprofen', 26.00, 3),
('Paracetamol', 31.00, 4),
('Amoxicillin', 75.00, 5),
('Ibuprofen', 28.00, 6),
('Cough Syrup', 50.00, 7),
('Cough Syrup', 48.00, 8),
('Aspirin', 43.00, 9),
('Metformin', 150.00, 10),
('Paracetamol', 32.00, 11),
('Ibuprofen', 28.00, 12),
('Antihistamine', 60.00, 13),
('Amoxicillin', 72.00, 14),
('Metformin', 148.00, 15),
('Antihistamine', 58.00, 16),
('Paracetamol', 34.00, 17),
('Cough Syrup', 52.00, 18),
('Ibuprofen', 29.00, 19),
('Metformin', 155.00, 20);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `amount` decimal(10,2) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`amount`, `order_id`) VALUES
(193.00, 45723);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(2, 'Ryan', 'rayankokate18@gmail.com', '$2y$10$QpqibH95WIRTrIvhR2HIV.d7Ju8q5lEkvN4YDdqMJoD0eN8iTn8lW', '2025-04-13 17:37:49'),
(8, 'Vibha', 'v.j@gmail.com', '$2y$10$wPZY4PNMI5zCEKCMGcU/rONKWKkfZigZdl5h8hSH1W0xjQ2MBifIq', '2025-04-14 04:04:14'),
(9, 'hello', 'rayan.kokate@somaiya.edu', '$2y$10$VaczkMC5hbdQCV2HOjtTiO1LM1dHHpmz3FmDT/Nj/Mb3k.OD7MYn.', '2025-04-15 05:59:08'),
(10, 'example', 'example@gmail.com', '$2y$10$HxZ7tbpNk4HpDq2dG2mDLO.OVXhmRARkdvqfvcBCh/idgrq2B/Z26', '2025-04-15 06:42:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`gstno`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`m_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`order_id`);

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
