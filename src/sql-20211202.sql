-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 02, 2021 at 06:53 PM
-- Server version: 5.7.23
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `rezemploi`
--

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `code` int(11) NOT NULL,
  `id` bigint(20) UNSIGNED NOT NULL,
  `pseudo` varchar(255) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `actif` bit(1) NOT NULL DEFAULT b'0',
  `creation_IP` varchar(255) NOT NULL,
  `creation_timestamp` int(10) UNSIGNED NOT NULL,
  `activation_IP` varchar(255) NOT NULL,
  `activation_timestamp` int(10) UNSIGNED NOT NULL,
  `bloque` bit(1) NOT NULL DEFAULT b'0',
  `blocage_IP` varchar(255) NOT NULL,
  `blocage_timestamp` int(10) UNSIGNED NOT NULL,
  `droit_superadmin` bit(1) NOT NULL DEFAULT b'0',
  `droit_admin` bit(1) NOT NULL DEFAULT b'0',
  `droit_moderation` bit(1) NOT NULL DEFAULT b'0',
  `droit_particulier` bit(1) NOT NULL DEFAULT b'1',
  `droit_entreprise` bit(1) NOT NULL DEFAULT b'0'
) DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `code` int(11) NOT NULL AUTO_INCREMENT;
