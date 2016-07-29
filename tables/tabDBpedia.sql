-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Lug 29, 2016 alle 22:25
-- Versione del server: 10.1.13-MariaDB
-- Versione PHP: 5.5.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `DoGi`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `tabdbpedia`
--

CREATE TABLE `tabDbpedia` (
  `IDResponsabilita` mediumint(8) NOT NULL,
  `DBpediaURL` text NOT NULL,
  `Filtered` enum('YES','NO','TOBECHECKED') NOT NULL DEFAULT 'TOBECHECKED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
