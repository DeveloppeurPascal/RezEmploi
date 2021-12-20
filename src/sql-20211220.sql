-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 20, 2021 at 08:47 PM
-- Server version: 5.7.23
-- PHP Version: 7.2.8

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET
time_zone = "+00:00";

--
-- Database: `rezemploi`
--

-- --------------------------------------------------------

--
-- Table structure for table `langues`
--

CREATE TABLE `langues`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `niveaux_etudes`
--

CREATE TABLE `niveaux_etudes`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pays`
--

CREATE TABLE `pays`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `reseaux_sociaux`
--

CREATE TABLE `reseaux_sociaux`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `rubriquescv`
--

CREATE TABLE `rubriquescv`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `types_contrats`
--

CREATE TABLE `types_contrats`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `types_diplomes`
--

CREATE TABLE `types_diplomes`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `types_realisations`
--

CREATE TABLE `types_realisations`
(
    `code`     int(11) NOT NULL,
    `priv_key` char(10)     NOT NULL,
    `libelle`  varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs`
(
    `code`                 int(11) NOT NULL,
    `priv_key`             char(10)     NOT NULL,
    `pseudo`               varchar(255) NOT NULL,
    `motdepasse`           varchar(255) NOT NULL,
    `email`                varchar(255) NOT NULL,
    `actif`                bit(1)       NOT NULL DEFAULT b'0',
    `creation_IP`          varchar(255) NOT NULL DEFAULT '',
    `creation_timestamp`   int(10) UNSIGNED NOT NULL DEFAULT '0',
    `activation_IP`        varchar(255) NOT NULL DEFAULT '',
    `activation_timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
    `bloque`               bit(1)       NOT NULL DEFAULT b'0',
    `blocage_IP`           varchar(255) NOT NULL DEFAULT '',
    `blocage_timestamp`    int(10) UNSIGNED NOT NULL DEFAULT '0',
    `droit_superadmin`     bit(1)       NOT NULL DEFAULT b'0',
    `droit_admin`          bit(1)       NOT NULL DEFAULT b'0',
    `droit_moderation`     bit(1)       NOT NULL DEFAULT b'0',
    `droit_particulier`    bit(1)       NOT NULL DEFAULT b'1',
    `droit_entreprise`     bit(1)       NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `langues`
--
ALTER TABLE `langues`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `langues_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `niveaux_etudes`
--
ALTER TABLE `niveaux_etudes`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `niveaux_etudes_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `pays`
--
ALTER TABLE `pays`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `pays_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `reseaux_sociaux`
--
ALTER TABLE `reseaux_sociaux`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `reseaux_sociaux_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `rubriquescv`
--
ALTER TABLE `rubriquescv`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `rubriquescv_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `types_contrats`
--
ALTER TABLE `types_contrats`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `types_contrats_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `types_diplomes`
--
ALTER TABLE `types_diplomes`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `types_diplomes_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `types_realisations`
--
ALTER TABLE `types_realisations`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `types_realisations_par_libelle` (`libelle`,`code`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    ADD PRIMARY KEY (`code`),
  ADD UNIQUE KEY `utilisateurs_par_pseudo` (`pseudo`,`code`),
  ADD UNIQUE KEY `utilisateurs_par_email` (`email`,`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `langues`
--
ALTER TABLE `langues`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `niveaux_etudes`
--
ALTER TABLE `niveaux_etudes`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pays`
--
ALTER TABLE `pays`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reseaux_sociaux`
--
ALTER TABLE `reseaux_sociaux`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rubriquescv`
--
ALTER TABLE `rubriquescv`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types_contrats`
--
ALTER TABLE `types_contrats`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types_diplomes`
--
ALTER TABLE `types_diplomes`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types_realisations`
--
ALTER TABLE `types_realisations`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    MODIFY `code` int (11) NOT NULL AUTO_INCREMENT;
