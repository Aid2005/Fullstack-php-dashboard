-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2026 at 06:06 PM
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
-- Database: `dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `KIme` varchar(16) NOT NULL,
  `LozinkaHasb` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institut`
--

CREATE TABLE `institut` (
  `SifraInstituta` int(11) NOT NULL,
  `NazivInstituta` varchar(255) NOT NULL,
  `Grad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `istrazivac`
--

CREATE TABLE `istrazivac` (
  `SifraIstrazivaca` int(11) NOT NULL,
  `ImeIstrazivaca` varchar(255) NOT NULL,
  `DatumRodjenja` date NOT NULL,
  `DatumZaposlenja` date NOT NULL,
  `Plata` decimal(10,2) NOT NULL,
  `SifraInstituta` int(11) NOT NULL,
  `SifraSeminara` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `IDK` int(11) NOT NULL,
  `SifraSeminara` int(11) NOT NULL,
  `Tekst` varchar(255) NOT NULL,
  `Pozitivno` tinyint(1) DEFAULT 0,
  `Negativno` tinyint(1) DEFAULT 0,
  `KreiranoAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pkomentar`
--

CREATE TABLE `pkomentar` (
  `IDK` int(11) NOT NULL,
  `SifraSeminara` int(11) NOT NULL,
  `Tekst` varchar(255) NOT NULL,
  `KreiranoAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seminar`
--

CREATE TABLE `seminar` (
  `SifraSeminara` int(11) NOT NULL,
  `NazivSeminara` varchar(255) NOT NULL,
  `Budzet` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vijest`
--

CREATE TABLE `vijest` (
  `ID` int(11) NOT NULL,
  `Naslov` varchar(255) NOT NULL,
  `Opis` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`KIme`);

--
-- Indexes for table `institut`
--
ALTER TABLE `institut`
  ADD PRIMARY KEY (`SifraInstituta`);

--
-- Indexes for table `istrazivac`
--
ALTER TABLE `istrazivac`
  ADD PRIMARY KEY (`SifraIstrazivaca`),
  ADD KEY `SifraInstituta` (`SifraInstituta`),
  ADD KEY `SifraSeminara` (`SifraSeminara`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`IDK`),
  ADD KEY `SifraSeminara` (`SifraSeminara`);

--
-- Indexes for table `pkomentar`
--
ALTER TABLE `pkomentar`
  ADD PRIMARY KEY (`IDK`),
  ADD KEY `SifraSeminara` (`SifraSeminara`);

--
-- Indexes for table `seminar`
--
ALTER TABLE `seminar`
  ADD PRIMARY KEY (`SifraSeminara`);

--
-- Indexes for table `vijest`
--
ALTER TABLE `vijest`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `institut`
--
ALTER TABLE `institut`
  MODIFY `SifraInstituta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `istrazivac`
--
ALTER TABLE `istrazivac`
  MODIFY `SifraIstrazivaca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `IDK` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pkomentar`
--
ALTER TABLE `pkomentar`
  MODIFY `IDK` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seminar`
--
ALTER TABLE `seminar`
  MODIFY `SifraSeminara` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vijest`
--
ALTER TABLE `vijest`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `istrazivac`
--
ALTER TABLE `istrazivac`
  ADD CONSTRAINT `istrazivac_ibfk_1` FOREIGN KEY (`SifraInstituta`) REFERENCES `institut` (`SifraInstituta`) ON DELETE CASCADE,
  ADD CONSTRAINT `istrazivac_ibfk_2` FOREIGN KEY (`SifraSeminara`) REFERENCES `seminar` (`SifraSeminara`) ON DELETE CASCADE;

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`SifraSeminara`) REFERENCES `seminar` (`SifraSeminara`) ON DELETE CASCADE;

--
-- Constraints for table `pkomentar`
--
ALTER TABLE `pkomentar`
  ADD CONSTRAINT `pkomentar_ibfk_1` FOREIGN KEY (`SifraSeminara`) REFERENCES `seminar` (`SifraSeminara`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
