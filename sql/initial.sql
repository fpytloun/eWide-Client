-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Sobota 30. července 2011, 18:55
-- Verze MySQL: 5.1.54
-- Verze PHP: 5.3.5-1ubuntu7.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `porac_ewclient`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ic` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `dic` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `psc` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `www` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `group` int(11) NOT NULL DEFAULT '1',
  `tags` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `send_emails` tinyint(1) NOT NULL DEFAULT '1',
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `clients`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `clients_groups`
--

CREATE TABLE IF NOT EXISTS `clients_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `clients_groups`
--

INSERT INTO `clients_groups` (`id`, `name`, `color`, `description`, `weight`, `added`) VALUES
(1, 'Hlavní', 'FFF9D7', '', 0, '2010-10-26 15:51:07');

-- --------------------------------------------------------

--
-- Struktura tabulky `clients_orders`
--

CREATE TABLE IF NOT EXISTS `clients_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `clients_orders`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `clients_persons`
--

CREATE TABLE IF NOT EXISTS `clients_persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `position` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `clients_persons`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `clients_tags`
--

CREATE TABLE IF NOT EXISTS `clients_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `clients_tags`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `clients_visits`
--

CREATE TABLE IF NOT EXISTS `clients_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `clients_visits`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `clients_visits_types`
--

CREATE TABLE IF NOT EXISTS `clients_visits_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Vypisuji data pro tabulku `clients_visits_types`
--

INSERT INTO `clients_visits_types` (`id`, `type`) VALUES
(1, 'Osobně'),
(2, 'Telefonicky'),
(3, 'Emailem'),
(4, 'Ostatní');

-- --------------------------------------------------------

--
-- Struktura tabulky `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `property` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `property` (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `settings`
--

INSERT INTO `settings` (`property`, `value`) VALUES
('all_columns', 'id=>Id,name=>Firma,address=>Sídlo,contact_person=>Kontaktní osoba,phone=>Telefon,email=>Email,www=>Web,last_visit=>Poslední návštěva,last_visit_notes=>Poznámky z návštěvy,last_visit_undone=>Připomenutí,last_visit_undone_notes=>Poznámky připomenutí,last_order=>Poslední objednávka,last_order_price=>Cena objednávky,notes=>Poznámky,added=>Přidán,group_name=>Skupina'),
('clients_per_page', '50'),
('colored_id', '1'),
('currency', 'Kč'),
('date_format', 'j.n.Y'),
('default_client_city', ''),
('default_client_name', ''),
('default_client_psc', ''),
('default_group', '1'),
('goto_added_client', '1'),
('language', 'cs'),
('map_url', 'http://mapy.google.cz/?q='),
('show_bot_menu', '8'),
('show_columns', 'id=>Id,name=>Firma,address=>Sídlo,phone=>Telefon,last_visit_undone=>Připomenutí,last_visit_undone_notes=>Poznámky připomenutí,last_visit=>Poslední návštěva,last_visit_notes=>Poznámky z návštěvy,last_order=>Poslední objednávka,last_order_price=>Cena objednávky,notes=>Poznámky'),
('theme', 'smooth');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `email` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `role`, `email`, `phone`, `created`, `active`) VALUES
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'admin', '', '', '2010-09-22 10:38:50', 1),
(2, 'user', '12dea96fec20593566ab75692c9949596833adc9', 'user', '', '', '2010-09-29 10:56:44', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `users_settings`
--

CREATE TABLE IF NOT EXISTS `users_settings` (
  `user` int(11) NOT NULL,
  `property` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `property` (`property`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `users_settings`
--

