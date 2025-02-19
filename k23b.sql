-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Фев 19 2025 г., 00:20
-- Версия сервера: 8.0.41-0ubuntu0.24.04.1
-- Версия PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `k23b`
--

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `login` text,
  `password` text NOT NULL,
  `name` text NOT NULL,
  `email` text NOT NULL,
  `date_of_registration` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `height` int DEFAULT NULL,
  `shoulder_width` int DEFAULT NULL,
  `waist_size` int DEFAULT NULL,
  `cart` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `auth_token` varchar(255) DEFAULT NULL,
  `pass_reset_url` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `clients` (`id`, `login`, `password`, `name`, `email`, `date_of_registration`, `height`, `shoulder_width`, `waist_size`, `cart`, `auth_token`, `pass_reset_url`) VALUES
(1, 'client1', '$2y$10$DbyWzvdSh.xep8PIGganYOiyodiG3ypNGjTrZ74A0JazCxd4IQL4K', 'Rafis', 'Rafis@yandex.ru', '2023-10-01 12:00:00', 180, 70, 50, '{\"product_ids\": [1, 2]}', '18290b8878b5a7239990da828cdf05d011091f2021d1d20ee7a8504976bd1ee3', ''),
(2, 'client2', '$2y$10$DbyWzvdSh.xep8PIGganYOiyodiG3ypNGjTrZ74A0JazCxd4IQL4K', 'Jane Smith', 'test2@test2', '2023-10-02 15:30:00', 165, 40, 65, '{\"product_ids\": [3]}', '5465f4cf1101821aeb45f3663c64321605d7cd6dec618dd9210bb50e0842bbf5', ''),
(3, 'danil', '$2y$10$j44dFRzfxEmUgx4Lex.QS.KasGYv/EW8F.oPpc64KX2Z14tvFA.uS', 'Daniil2', 'daniil@kek.ru', '2024-10-16 20:51:24', 180, 80, 60, '[{\"id\":\"11\",\"options\":{\"cloth\":{\"id\":\"9\",\"free\":true},\"color\":{\"id\":\"10\",\"free\":true},\"lining\":{\"id\":\"7\",\"free\":false},\"button\":{\"id\":\"8\",\"free\":false}},\"body_sizes\":{\"height\":180,\"shoulder_width\":80,\"waist_size\":60},\"size\":\"41\",\"price\":16000}]', '8ce04a1ab9776afb092e37f57458f61e69db3c423df247b259b6a933256bf9dc', ''),
(4, 'kirill', '$2y$10$EpzYu4wd8Twk.vn9pW87hujWvjIvUrqX7Z3qP72oxO2cS4Qd4N1aa', 'Kirill', 'k237011@kccollege.ac.jp', '2024-10-17 08:55:03', 180, 80, 50, '[{\"id\":\"11\",\"options\":{\"cloth\":{\"id\":\"1\",\"free\":false},\"color\":{\"id\":\"\",\"free\":false},\"lining\":{\"id\":\"\",\"free\":false},\"button\":{\"id\":\"\",\"free\":false}},\"body_sizes\":{\"height\":180,\"shoulder_width\":80,\"waist_size\":50},\"size\":\"41\",\"price\":16000}]', 'acd6538a6bf530331641c3a777628b16d41d8a9f2fa4c065ab69042d00988e5d', ''),
(6, NULL, '$2y$10$ttQD1OTUwumH//9s89INVeRlfytSPA4.R/xmS9Cq48hQ0hrnSZENm', '123456', 'k237032@kccollege.ac.jp', '2024-11-27 12:35:12', NULL, NULL, NULL, '[{\"id\":\"14\",\"options\":{\"cloth\":{\"id\":\"\",\"free\":false},\"color\":{\"id\":\"\",\"free\":false},\"lining\":{\"id\":\"\",\"free\":false},\"button\":{\"id\":\"\",\"free\":false}},\"body_sizes\":{\"height\":0,\"shoulder_width\":0,\"waist_size\":0},\"size\":\"50\",\"price\":13000}]', '8baf488d4da7fb2f90555a6767b773509d23cd88035104fa17fc91fc2897db3e', ''),
(7, NULL, '$2y$10$ZrkYgH13SRmUN14A1WFpe.fUjs0oQgNxY1RXf/PO8eB5eDwC2fbb2', 'rafis', 'rafis@asdas.asd', '2024-12-09 16:31:34', NULL, NULL, NULL, '[{\"id\":\"12\",\"options\":{\"cloth\":{\"id\":\"\",\"free\":false},\"color\":{\"id\":\"\",\"free\":false},\"lining\":{\"id\":\"\",\"free\":false},\"button\":{\"id\":\"\",\"free\":false}},\"body_sizes\":{\"height\":0,\"shoulder_width\":0,\"waist_size\":0},\"size\":\"42\",\"price\":13000}]', '877c6abfedbd283a4076a5542b5f9ae94017c07c521ed640ac4b741ea7b517c0', ''),
(8, NULL, '$2y$10$n.yOx70DneDLjXhxYXXYw.TV3W0ZJw5RklhtgynVPgw41NO24nWSS', 'Daniel', 'k237034@kccollege.ac.jp', '2024-12-11 12:39:45', 180, 80, 60, '[{\"id\":\"11\",\"options\":{\"cloth\":{\"id\":\"\",\"free\":false},\"color\":{\"id\":\"\",\"free\":false},\"lining\":{\"id\":\"\",\"free\":false},\"button\":{\"id\":\"\",\"free\":false}},\"body_sizes\":{\"height\":180,\"shoulder_width\":80,\"waist_size\":60},\"size\":\"41\",\"price\":16000}]', 'c5e8216855a6ec595ed220d0dffd399cefca4df798fa8959fcc5a4e7c551a728', 'f10ee510262e4c56f0806e8b331870015ea99832e8a56223d42235b4a820d596'),
(9, NULL, '$2y$10$lAn2kPyHpOU6YTlRjaXRZ.EDoBZnpzj3COdXzKK7PGrMsUjZFZcMi', 'kyokosakaguchi', 'sakaguchi@kccollege.ac.jp', '2024-12-16 12:17:17', NULL, NULL, NULL, '[{\"id\":\"12\",\"options\":{\"cloth\":{\"id\":\"\",\"free\":false},\"color\":{\"id\":\"\",\"free\":false},\"lining\":{\"id\":\"\",\"free\":false},\"button\":{\"id\":\"\",\"free\":false}},\"body_sizes\":{\"height\":\"7\",\"shoulder_width\":\"6\",\"waist_size\":\"5\"},\"size\":\"42\",\"price\":13000}]', '1f37082ad5f19554ae94f977e00f99c5707049af51c64b927fafb6f928643dae', ''),
(10, '', '$2y$10$Oid4oWIM5QG8Z/t7sOb0DeVEfFH3ZW8TdMJGOyxybfv5B71CBTHT.', '山田', 'dancho1282@gmail.com', '2025-01-22 09:18:16', 234, 234, 234, '[{\"id\":\"12\",\"options\":{\"cloth\":{\"id\":\"5\",\"free\":false},\"color\":{\"id\":\"6\",\"free\":false},\"lining\":{\"id\":\"3\",\"free\":false},\"button\":{\"id\":\"8\",\"free\":false}},\"body_sizes\":{\"height\":\"150\",\"shoulder_width\":\"56\",\"waist_size\":\"56\"},\"size\":\"45\",\"price\":16000}]', 'ea89ae020deeeca3dbe56928584c9dd83b21601b26446ab468999657e1448429', ''),
(11, NULL, '$2y$10$88gXjdaNhnL76hxy5kK0/OwIuESKL4ubYCXrB5iXZuZ8B.Nd2E0Bu', 'Giliana', 'k237030@kccollege.ac.jp', '2025-02-10 09:38:46', NULL, NULL, NULL, '[]', 'a7a5b82088eebf74bb55d62b2b05192a5f20bfcf0b854e642951f84bd6c70d97', '');

-- --------------------------------------------------------

--
-- Структура таблицы `client_addresses`
--

CREATE TABLE `client_addresses` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `client_id` int NOT NULL,
  `address` text NOT NULL,
  `phone` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `client_addresses`
--

INSERT INTO `client_addresses` (`id`, `name`, `client_id`, `address`, `phone`, `active`) VALUES
(1, 'John Doe Home', 1, '123 Tokyo Street, Tokyo', '080-1234-5678', 1),
(2, 'Jane Smith Office', 2, '456 Kyoto Avenue, Kyoto', '080-9876-5432', 1),
(8, '寮', 3, '〒227-0054Kanagawa,Yokohama,AobaWard,Shiratoridai,５３−1C315インターナショナルコミュニティ青葉寮', '080-7474-75', 1),
(9, 'Kirill home', 4, 'Russia', '080-0000-0000', 0),
(10, 'Danilhome', 3, 'Russia', '+7920800800', 1),
(12, 'Okazawa', 4, 'Okazawa', '88000008784', 1),
(13, 'gddsgdg', 7, 'dfgdfg', 'dfgdfg', 1),
(14, 'Daniel Home', 8, '20 W 34th St., New York, NY 10001, USA', '+134578793576', 1),
(15, 'hhghg', 9, 'jhj', '64545', 1),
(16, '2', 4, '1', '3', 0),
(17, '1', 4, '2', '4', 0),
(18, '1', 4, '2', '333', 0),
(19, 'sfdsdf', 10, 'sdfsdf', '23423423', 0),
(20, '12', 4, '1', '36', 0),
(21, '1', 4, '23', '3', 0),
(22, '1', 4, '2', '3', 0),
(23, 'KEK', 3, 'sdfsdf', ';TRUNCATE TABLE radom;', 0),
(24, 'Giliana', 11, '横浜市保土ケ谷区鎌谷町', '09090000000', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `client_orders`
--

CREATE TABLE `client_orders` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `address_id` int NOT NULL,
  `payment_method_id` int NOT NULL,
  `status` text NOT NULL,
  `date_of_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_of_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `client_orders`
--

INSERT INTO `client_orders` (`id`, `client_id`, `address_id`, `payment_method_id`, `status`, `date_of_creation`, `date_of_change`) VALUES
(1, 1, 1, 0, 'processing', '2024-11-11 10:24:56', '2024-11-11 10:24:56'),
(2, 2, 2, 0, 'shipped', '2024-11-11 10:24:56', '2024-11-11 10:24:56'),
(3, 3, 10, 3, 'shipped', '2024-11-11 10:24:56', '2024-11-11 10:24:56'),
(4, 3, 8, 4, 'shipped', '2024-11-11 10:24:56', '2024-11-11 10:24:56'),
(5, 3, 10, 3, 'confirmed', '2024-12-08 19:29:13', '2024-12-08 19:29:13'),
(6, 4, 12, 0, 'confirmed', '2024-12-09 11:38:06', '2024-12-09 11:38:06'),
(7, 4, 12, 5, 'confirmed', '2024-12-09 11:43:54', '2024-12-09 11:43:54'),
(8, 4, 12, 5, 'confirmed', '2024-12-09 11:45:44', '2024-12-09 11:45:44'),
(10, 3, 10, 4, 'in_transit', '2024-12-16 12:09:26', '2024-12-16 03:11:21'),
(12, 3, 8, 3, 'confirmed', '2024-12-16 12:19:05', '2024-12-16 12:19:05'),
(15, 4, 12, 5, 'confirmed', '2025-01-20 10:04:16', '2025-01-20 10:04:16'),
(16, 4, 12, 21, 'confirmed', '2025-01-22 10:16:19', '2025-01-22 10:16:19'),
(17, 4, 12, 21, 'confirmed', '2025-01-22 10:16:46', '2025-01-22 10:16:46'),
(18, 4, 12, 22, 'confirmed', '2025-01-22 10:17:45', '2025-01-22 10:17:45'),
(19, 4, 12, 24, 'confirmed', '2025-01-22 10:51:03', '2025-01-22 10:51:03'),
(20, 4, 12, 23, 'confirmed', '2025-01-22 10:51:58', '2025-01-22 10:51:58'),
(21, 4, 12, 21, 'confirmed', '2025-01-27 09:25:50', '2025-01-27 09:25:50'),
(22, 4, 12, 21, 'confirmed', '2025-01-27 10:56:28', '2025-01-27 10:56:28'),
(23, 4, 12, 21, 'confirmed', '2025-01-27 10:58:05', '2025-01-27 10:58:05'),
(24, 4, 12, 21, 'confirmed', '2025-01-27 10:58:48', '2025-01-27 10:58:48'),
(25, 4, 12, 21, 'confirmed', '2025-01-27 11:00:11', '2025-01-27 11:00:11'),
(26, 4, 12, 21, 'confirmed', '2025-01-27 11:06:57', '2025-01-27 11:06:57'),
(27, 4, 12, 21, 'confirmed', '2025-01-27 11:18:59', '2025-01-27 11:18:59'),
(28, 3, 8, 27, 'confirmed', '2025-01-27 11:49:07', '2025-01-27 11:49:07'),
(29, 3, 8, 3, 'confirmed', '2025-01-27 12:13:25', '2025-01-27 12:13:25'),
(30, 4, 12, 21, 'confirmed', '2025-01-29 10:20:43', '2025-01-29 10:20:43'),
(32, 11, 24, 29, 'confirmed', '2025-02-10 09:41:35', '2025-02-10 09:41:35'),
(33, 8, 14, 6, 'in_transit', '2025-02-10 11:17:47', '2025-02-10 02:24:10');

-- --------------------------------------------------------

--
-- Структура таблицы `client_order_indexes`
--

CREATE TABLE `client_order_indexes` (
  `id` int NOT NULL,
  `client_order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `price` int NOT NULL,
  `size_id` int NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `client_order_indexes`
--

INSERT INTO `client_order_indexes` (`id`, `client_order_id`, `product_id`, `price`, `size_id`, `options`) VALUES
(2, 1, 3, 5000, 7, NULL),
(3, 2, 3, 5000, 5, NULL),
(4, 5, 12, 23000, 45, '[5,10,3,4]'),
(5, 6, 25, 1200, 68, '[]'),
(6, 7, 25, 1200, 68, '[]'),
(7, 8, 3, 7500, 9, '[]'),
(8, 9, 3, 7500, 9, '[]'),
(9, 10, 12, 22200, 45, '[5,6,3,12]'),
(10, 12, 12, 16000, 45, '[]'),
(11, 13, 6, 91000, 21, '[5,10,3,12]'),
(12, 14, 6, 93200, 21, '[5,6,7,4]'),
(13, 14, 3, 7500, 8, '[]'),
(14, 15, 11, 16000, 41, '[]'),
(15, 16, 11, 18800, 41, '[9,10,15,8]'),
(16, 17, 3, 7500, 8, '[]'),
(17, 18, 13, 16000, 49, '[]'),
(18, 19, 3, 7500, 8, '[]'),
(19, 19, 7, 99000, 25, '[13]'),
(20, 20, 3, 7500, 8, '[]'),
(21, 21, 7, 102800, 25, '[5,2,3,16]'),
(22, 21, 11, 20000, 41, '[1,2,7,12]'),
(23, 21, 12, 20000, 45, '[1,6,11,16]'),
(24, 21, 3, 7500, 8, '[]'),
(25, 21, 5, 15000, 14, '[]'),
(26, 21, 6, 92200, 21, '[13,6,11,4]'),
(27, 21, 8, 61700, 29, '[1,6,15,12]'),
(28, 21, 13, 20800, 49, '[5,10,15,8]'),
(29, 21, 14, 21000, 53, '[1,10,7,4]'),
(30, 21, 15, 57800, 57, '[5,2,3,8]'),
(31, 21, 16, 4000, 58, '[]'),
(32, 21, 17, 4500, 61, '[]'),
(33, 21, 25, 1200, 68, '[]'),
(34, 21, 26, 3500, 69, '[]'),
(35, 25, 13, 16000, 49, '[]'),
(36, 25, 17, 4500, 61, '[]'),
(37, 26, 12, 16000, 45, '[]'),
(38, 26, 3, 7500, 8, '[]'),
(39, 27, 13, 19000, 49, '[5]'),
(40, 27, 11, 19000, 41, '[13]'),
(41, 28, 11, 16000, 41, '[]'),
(42, 29, 6, 89800, 21, '[1,10,7,8]'),
(43, 30, 11, 16000, 41, '[]'),
(44, 31, 11, 16000, 41, '[]'),
(45, 32, 11, 22000, 40, '[1,2,3,4]'),
(46, 33, 11, 21000, 41, '[1,6,7,8]');

-- --------------------------------------------------------

--
-- Структура таблицы `client_payment_methods`
--

CREATE TABLE `client_payment_methods` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `card_number` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `client_payment_methods`
--

INSERT INTO `client_payment_methods` (`id`, `client_id`, `card_number`, `active`) VALUES
(1, 1, '1111111111111111', 1),
(2, 2, '1111111111111111', 1),
(3, 3, '1111111111111155', 1),
(4, 3, '1111111111111111', 1),
(5, 4, '4345 3453 4534 5345', 0),
(6, 8, '3423423423423423', 1),
(7, 9, '5454545445454545', 1),
(8, 4, '0', 0),
(9, 4, '1', 0),
(10, 4, '45', 0),
(11, 4, '7', 0),
(12, 3, '5872 3665 8463 8562', 0),
(13, 3, '4253 4532 4523 5434', 1),
(14, 4, '444', 0),
(15, 4, '5555', 0),
(16, 4, '789', 0),
(17, 4, '4578', 0),
(18, 10, '3453 4213 4234 1241', 0),
(19, 4, '33', 0),
(20, 4, '44', 0),
(21, 4, 'CASH', 1),
(22, 4, 'KONBINI', 1),
(23, 4, '4545', 0),
(24, 4, '49', 0),
(25, 4, 'ё', 0),
(26, 4, '0', 0),
(27, 3, 'KONBINI', 1),
(28, 8, 'CASH', 1),
(29, 11, 'CASH', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `options`
--

CREATE TABLE `options` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `type` text NOT NULL,
  `price` int NOT NULL,
  `stock` int NOT NULL,
  `date_of_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_of_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `options`
--

INSERT INTO `options` (`id`, `name`, `type`, `price`, `stock`, `date_of_creation`, `date_of_change`) VALUES
(1, 'コットン (綿)', 'cloth', 2000, 10, '2024-10-09 11:30:18', '2024-10-09 11:30:18'),
(2, '赤 (あか)', 'color', 1000, 10, '2024-10-09 11:32:05', '2024-10-09 11:32:05'),
(3, 'サテン裏地', 'lining', 2000, 10, '2024-10-09 11:32:05', '2024-10-09 11:32:05'),
(4, 'プラスチックボタン', 'button', 2000, 10, '2024-10-09 11:32:05', '2024-10-09 11:32:05'),
(5, 'カシミア', 'cloth', 3000, 10, '2024-10-21 12:00:00', '2024-10-21 12:00:00'),
(6, 'ネイビー', 'color', 1200, 10, '2024-10-21 12:00:00', '2024-10-21 12:00:00'),
(7, 'ポリエステル', 'lining', 1000, 10, '2024-10-21 12:00:00', '2024-10-21 12:00:00'),
(8, '金属ボタン', 'button', 800, 10, '2024-10-21 12:00:00', '2024-10-21 12:00:00'),
(9, 'ウール', 'cloth', 2000, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(10, 'グレー', 'color', 1000, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(11, 'ポリエステル裏地', 'lining', 1500, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(12, 'プラスチックボタン', 'button', 500, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(13, 'カシミア', 'cloth', 3000, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(14, 'ネイビー', 'color', 1200, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(15, 'サテン裏地', 'lining', 2000, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00'),
(16, '金属ボタン', 'button', 800, 10, '2024-10-21 12:30:00', '2024-10-21 12:30:00');

-- --------------------------------------------------------

--
-- Структура таблицы `options_indexes`
--

CREATE TABLE `options_indexes` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `option_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `options_indexes`
--

INSERT INTO `options_indexes` (`id`, `product_id`, `option_id`) VALUES
(9, 6, 9),
(10, 6, 10),
(11, 6, 11),
(12, 6, 12),
(13, 7, 9),
(14, 7, 10),
(15, 7, 11),
(16, 7, 12),
(17, 8, 13),
(18, 8, 14),
(19, 8, 15),
(20, 8, 16),
(21, 9, 9),
(22, 9, 10),
(23, 9, 11),
(24, 9, 12),
(25, 10, 9),
(26, 10, 10),
(27, 10, 11),
(28, 10, 12),
(29, 11, 9),
(30, 11, 10),
(31, 11, 11),
(32, 11, 12),
(33, 12, 9),
(34, 12, 10),
(35, 12, 11),
(36, 12, 12),
(37, 13, 13),
(38, 13, 14),
(39, 13, 15),
(40, 13, 16),
(41, 14, 9),
(43, 14, 11),
(44, 14, 12),
(45, 15, 9),
(46, 15, 10),
(47, 15, 11),
(48, 15, 12),
(49, 14, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `sub_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '""',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `name_eng` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_of_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_of_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `popular` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `type`, `sub_type`, `name`, `name_eng`, `description`, `date_of_creation`, `date_of_change`, `active`, `popular`) VALUES
(3, 'not_suit', 'shooes', 'クラシックダービー - ベーシックシューズ', 'Classic Derby - Basic Shoes', '<p><strong>商品名</strong>: クラシックダービー - ベーシックシューズ</p><p><strong>カラー</strong>: 落ち着いたブラウン</p><p><strong>素材</strong>: ソフトレザー（耐久性と柔らかさを兼ね備えた素材）</p><p><strong>特徴</strong>:</p><ul><li>ダービースタイルのシンプルで快適なデザイン</li><li>控えめなステッチがカジュアルにもビジネスにも適応</li><li>軽量ソールで長時間の着用でも快適</li><li>日常使いに最適な手頃な価格</li></ul>', '2024-10-09 11:04:10', '2024-12-04 02:42:11', 1, 0),
(5, 'not_suit', 'shooes', 'クラシックオックスフォード - プレミアムシューズ', 'Classic Oxford - Premium Shoes', '<p><strong>商品名</strong>: クラシックオックスフォード - プレミアムシューズ</p><p><strong>カラー</strong>: 漆黒のブラック</p><p><strong>素材</strong>: 高級本革（ポリッシュ仕上げで輝きを持続）</p><p><strong>特徴</strong>:</p><ul><li>オックスフォードスタイルの伝統的なデザイン</li><li>滑らかでミニマルなディテールがプロフェッショナルな印象を強調</li><li>快適なインソールと滑り止めのアウトソール</li><li>ビジネスからフォーマルシーンまで幅広く活用可能</li></ul><p><br></p>', '2024-10-21 12:00:00', '2024-12-04 02:40:50', 1, 0),
(6, 'suit', '', 'エターナルネイビー - 優雅の極みスーツ', 'Eternal Navy - The Pinnacle of Elegance Suit', '<p><strong>商品名</strong>: エターナルネイビー - 優雅の極みスーツ</p><p><strong>カラー</strong>: 深みのあるネイビーブルー</p><p><strong>素材</strong>: 高級ウール（Super 130）、軽量で通気性が高く、長時間の着用でも快適</p><p><strong>シルエット</strong>: スリムフィット（洗練されたシルエットを実現し、スタイリッシュな印象を与えます）</p><p><strong>特徴</strong>:</p><ul><li>クラシックなノッチドラペルデザインで上品さを強調</li><li>シングルブレストの2ボタン構造、フォーマルとモダンの調和</li><li>胸ポケットにはシルクのポケットチーフが付属</li><li>両サイドベントで動きやすさと快適さを追求</li><li>スラックスはテーパードカット、足元をスマートに見せます</li><li>裏地には高級感あるストライプ柄を使用し、細部にまでこだわったデザイン</li></ul><p><br></p>', '2024-10-21 12:30:00', '2024-12-04 02:21:27', 1, 0),
(7, 'suit', '', 'クラシックチャコール - 洗練のシンボルスーツ', 'Classic Charcoal - The Symbol of Sophistication Suit', '<p><strong>商品名</strong>: クラシックチャコール - 洗練のシンボルスーツ</p><p><strong>カラー</strong>: 落ち着いたチャコールグレー</p><p><strong>素材</strong>: 高品質ウール（Super 140）、滑らかな手触りと耐久性を兼ね備えた素材</p><p><strong>シルエット</strong>: モダンスリムフィット（クラシックと現代的な要素を融合）</p><p><strong>特徴</strong>:</p><ul><li>深みのあるチャコールグレーが落ち着いた印象を与える</li><li>クラシックなノッチドラペルとシングルブレスト2ボタンデザイン</li><li>胸ポケットに付属するダークグレーのポケットチーフがアクセント</li><li>両サイドベントで快適な動きとスタイリッシュなシルエット</li><li>スラックスはストレートカットで、上品でタイムレスなデザイン</li></ul>', '2024-10-21 12:30:00', '2024-12-04 02:24:31', 1, 0),
(8, 'suit', '', 'クラシックグレー - スマートエレガンススーツ', 'Classic Gray - Smart Elegance Suit', '<p><strong>商品名</strong>: クラシックグレー - スマートエレガンススーツ</p><p><strong>カラー</strong>: ミディアムグレー（落ち着きとモダンな印象を与えるカラー）</p><p><strong>素材</strong>: ウールポリエステル混紡（軽量で耐久性があり、シワになりにくい）</p><p><strong>シルエット</strong>: モダンスリムフィット（スマートで洗練されたラインを演出）</p><p><strong>特徴</strong>:</p><ul><li>クラシックなノッチドラペルとシングルブレスト2ボタンのデザイン</li><li>胸ポケットには控えめなホワイトポケットチーフが付属</li><li>両サイドベントで動きやすさを向上し、エレガントな印象をキープ</li><li>スラックスはストレートカットで、ビジネスシーンやフォーマルな場面に最適</li><li>着用しやすい軽量素材で、長時間の着用でも快適</li></ul><p><br></p>', '2024-10-21 12:30:00', '2024-12-04 02:27:59', 1, 0),
(9, 'suit', '', 'ルビーボルドースリーピーススーツ', 'Ruby Bordeaux Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>深みのあるボルドーカラーが印象的な、エレガントなメンズスリーピーススーツ。洗練されたデザインと上質な生地が、特別な場面での存在感を引き立てます。クラシックなピークドラペルとすっきりとしたシルエットが、都会的でスタイリッシュな印象を演出。フォーマルなイベントやパーティー、ビジネスシーンにも最適な一着です。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 深みのあるルビーボルドー（ワインレッド）</li><li><strong>デザイン</strong>: ピークドラペルジャケット、シングルボタン仕様、フラップポケット付き</li><li><strong>素材</strong>: 高品質ウールブレンド、快適な着心地と耐久性</li><li><strong>スタイル</strong>: ビジネス、フォーマル、パーティー、特別なイベントに最適</li><li><strong>セット内容</strong>: ベスト、パンツ、ホワイトドレスシャツ、ダーク柄ネクタイ、ポケットチーフ</li></ul><p>上品で華やかな印象を与える、大人のためのクラシックかつモダンなスーツです。</p>', '2024-10-21 12:30:00', '2025-02-05 02:29:57', 1, 0),
(10, 'suit', '', 'フォレストグリーンスリーピーススーツ', 'Forest Green Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>深みのあるフォレストグリーンが印象的な、洗練されたメンズスリーピーススーツ。クラシックなデザインと上質な生地が融合し、フォーマルな場面にふさわしいエレガントな印象を与えます。ビジネスシーンはもちろん、特別なイベントやパーティーでも存在感を放つ一着です。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 落ち着いたフォレストグリーン（深緑）</li><li><strong>デザイン</strong>: ノッチラペルジャケット、シングルボタン仕様、フラップポケット付き</li><li><strong>素材</strong>: 高品質ウールブレンド、快適な着心地と耐久性を兼ね備えた仕立て</li><li><strong>スタイル</strong>: ビジネス、フォーマル、結婚式、パーティー、特別なイベントに最適</li><li><strong>セット内容</strong>: ベスト、パンツ、ホワイトドレスシャツ、柄ネクタイ、ポケットチーフ</li></ul><p>洗練された大人のスタイルを演出する、クラシックでエレガントなスーツです。</p>', '2024-10-21 12:30:00', '2025-02-05 02:28:23', 1, 0),
(11, 'suit', '', 'エレガントブルーサイドビュースリーピーススーツ', 'Elegant Blue Side View Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>横からのシルエットが美しく際立つ、洗練されたブルーのスリーピーススーツ。高品質な生地とテーラード仕立てにより、フォーマルな場にふさわしい上品な印象を演出します。ジャケット、ベスト、パンツの3点セットで、ビジネスや特別なイベントに最適です。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 深みのあるエレガントなブルー</li><li><strong>デザイン</strong>: サイドビューで映えるシャープなテーラードジャケット、ノッチラペル、フラップポケット付き</li><li><strong>素材</strong>: 高品質ウールブレンド、通気性と耐久性に優れた快適な仕上がり</li><li><strong>スタイル</strong>: ビジネス、フォーマル、パーティー、結婚式など幅広いシーンに対応</li><li><strong>セット内容</strong>: ベスト、パンツ、ホワイトドレスシャツ、柄ネクタイ、ポケットチーフ</li></ul><p>品格とスタイルを兼ね備えたスーツで、洗練された大人の魅力を引き立てます。</p>', '2024-10-21 13:00:00', '2025-02-05 02:22:27', 1, 1),
(12, 'suit', '', 'エレガンスブルースリーピーススーツ', 'Elegance Blue Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>洗練されたデザインと上品なブルーカラーが特徴のメンズスリーピーススーツ。ジャケット、ベスト、パンツの3点セットで、フォーマルな場にふさわしい一着です。高品質な生地と丁寧な仕立てにより、快適な着心地とスタイリッシュなシルエットを実現。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 深みのあるエレガントなブルー</li><li><strong>デザイン</strong>: ノッチラペルジャケット、クラシックなシングルボタン仕様</li><li><strong>素材</strong>: 高品質ウールブレンドで、通気性と耐久性を兼ね備えた仕上がり</li><li><strong>スタイル</strong>: ビジネス、フォーマル、パーティーなど幅広いシーンに対応</li><li><strong>付属品</strong>: ベスト、パンツ、ホワイトドレスシャツ、柄ネクタイ、ポケットチーフ</li></ul><p>ビジネスシーンから特別なイベントまで、洗練されたスタイルを求める方に最適なスーツです。</p>', '2024-10-21 13:00:00', '2025-02-05 02:20:17', 1, 1),
(13, 'suit', '', 'エレガントベージュサイドビュースリーピーススーツ', 'Elegant Beige Side View Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>柔らかく洗練された印象を与えるベージュのスリーピーススーツ。横から見たスタイリッシュなシルエットが、上品で落ち着いた雰囲気を演出します。高品質な生地と仕立てにより、フォーマルな場面や特別なイベントに最適な一着です。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 優雅なベージュ（エレガントベージュ）</li><li><strong>デザイン</strong>: ノッチラペルジャケット、シングルボタン仕様、フラップポケット付き</li><li><strong>素材</strong>: 高品質ウールブレンド、快適な着心地と耐久性</li><li><strong>スタイル</strong>: ビジネス、フォーマル、パーティー、結婚式など幅広いシーンに対応</li><li><strong>セット内容</strong>: ベスト、パンツ、ホワイトドレスシャツ、柄ネクタイ、ポケットチーフ</li></ul><p>エレガントで落ち着いた印象を与える、洗練されたクラシックスタイルのスーツです。</p>', '2024-10-21 13:00:00', '2025-02-05 02:25:34', 1, 0),
(14, 'suit', '', 'エレガントレッドサイドビュースリーピーススーツ', 'Elegant Red Side View Three-Piece Suit', '<p><strong>商品説明</strong>:</p><p>力強さとエレガンスを兼ね備えた深紅のスリーピーススーツ。横からの洗練されたシルエットが、スタイルの美しさを際立たせます。高品質な生地と丁寧な仕立てにより、フォーマルなシーンや特別な場面に最適な一着です。</p><p><strong>特徴</strong>:</p><p><br></p><ul><li><strong>カラー</strong>: 気品あふれる深紅（エレガントレッド）</li><li><strong>デザイン</strong>: ノッチラペルジャケット、シングルボタン仕様、フラップポケット付き</li><li><strong>素材</strong>: 高品質ウールブレンド、快適な着心地と耐久性</li><li><strong>スタイル</strong>: ビジネス、フォーマル、パーティー、結婚式など幅広いシーンに対応</li><li><strong>セット内容</strong>: ベスト、パンツ、ホワイトドレスシャツ、柄ネクタイ、ポケットチーフ</li></ul><p>特別な場面で洗練された印象を与える、スタイリッシュでクラシックなスーツです。</p>', '2024-10-21 13:00:00', '2025-02-05 02:23:55', 1, 1),
(15, 'suit', '', '漆黒の風格 - シャドウエレガンススーツ', 'Shadow Elegance Suit - The Essence of Black', '<p><strong>商品名</strong>: シャドウエレガンススーツ</p><p><strong>カラー</strong>: 漆黒の深みのあるブラック</p><p><strong>素材</strong>: 高級ウールブレンド（ポリエステル混紡で耐久性と快適性を実現）</p><p><strong>シルエット</strong>: スリムフィット（身体のラインに寄り添い、エレガントな印象を与えます）</p><p><strong>特徴</strong>:</p><ul><li>優雅なピークラペルが全体の洗練された印象を強調</li><li>シングルブレストの2ボタンデザインでクラシックさを保ちながら現代的</li><li>胸ポケットにはシルク素材のポケットチーフが付属</li><li>裏地には上品なストライプ柄を採用し、内側にも高級感をプラス</li><li>両サイドベントで動きやすさと快適さを提供</li><li>スラックスは裾に向かって細くなるテーパードカット</li></ul>', '2024-10-21 13:00:00', '2024-12-04 02:21:32', 1, 0),
(16, 'not_suit', 'shirt', 'リラックスホワイト - フォーマルコンフォートシャツ', 'Relaxed White - Formal Comfort Shirt', '<p><strong>商品名</strong>: リラックスホワイト - フォーマルコンフォートシャツ</p><p><strong>カラー</strong>: クラシックホワイト（洗練された印象を与える純白）</p><p><strong>素材</strong>: 高品質コットンポリエステル混紡（通気性が良く、手入れが簡単な素材）</p><p><strong>シルエット</strong>: リラックスフィット（胸部とウエストに余裕があり、快適な着心地を提供）</p><p><strong>特徴</strong>:</p><ul><li>柔らかなストラクチャードカラーで、首周りに優しいデザイン</li><li>ボタンカフスがフォーマルな場面に対応</li><li>動きやすいストレートカットで、長時間の着用でも快適</li><li>フォーマルからビジネスシーンまで幅広く活用可能</li></ul><p><br></p>', '2024-10-21 13:30:00', '2024-12-04 02:36:13', 1, 0),
(17, 'not_suit', 'shirt', '上質リネン', 'Premium Linen', '<p>上質リネンシャツは、夏の暖かい日々にぴったりのアイテムです。軽やかで通気性の良いリネン素材を使用し、肌触りが抜群。シンプルでありながら洗練されたデザインは、ビジネスシーンからカジュアルな日常使いまで幅広く対応可能です。高品質なリネン素材が持つ自然な風合いを生かし、優れた着心地とともに、上品でスタイリッシュな印象を与えます。</p><p><br></p><ul><li><strong>素材:</strong> リネン100%</li><li><strong>カラー:</strong> ホワイト</li><li><strong>サイズ:</strong> M、L、XL</li><li><strong>特徴:</strong></li><li class=\"ql-indent-1\">軽量で通気性抜群</li><li class=\"ql-indent-1\">爽やかな着心地で夏でも快適</li><li class=\"ql-indent-1\">ビジネスにもカジュアルにも適応</li><li class=\"ql-indent-1\">しわ感を楽しむナチュラルな風合い</li><li class=\"ql-indent-1\">高級感のある仕上げと丁寧な縫製</li></ul><p><strong>お手入れ方法:</strong></p><ol><li>洗濯機使用可能（ネット使用、弱水流）</li><li>30℃以下で洗濯</li><li>漂白剤は使用しないでください</li><li>アイロンがけは低温で、リネン素材特有の風合いを保ちながら仕上げてください</li><li>洗濯後はすぐに干して、しわを伸ばしてください</li></ol><p><br></p>', '2024-10-21 13:30:00', '2024-11-27 03:01:02', 1, 0),
(25, 'not_suit', 'socks', 'シンプルブラック - ベーシックソックス', 'Simple Black - Basic Socks', '<p><strong>商品名</strong>: シンプルブラック - ベーシックソックス</p><p><strong>カラー</strong>: ジェットブラック（どんなスタイルにも合う万能な黒）</p><p><strong>素材</strong>: コットンブレンド（柔らかく快適で通気性の高い素材）</p><p><strong>特徴</strong>:</p><ul><li>シンプルで滑らかなテクスチャのデザイン</li><li>ビジネスやフォーマルなシーンに最適</li><li>丈夫で長持ちする耐久性のある作り</li><li>一日中快適に履ける軽量構造</li></ul><p><br></p>', '2024-11-27 11:01:34', '2024-12-18 01:08:33', 1, 0),
(26, 'not_suit', '\"\"', ' シンプルホワイト - デイリーフィットシャツ', 'Simple White - Daily Fit Shirt', '<p><strong>商品名</strong>: シンプルホワイト - デイリーフィットシャツ</p><p><strong>カラー</strong>: ソフトホワイト（落ち着いた自然なトーン）</p><p><strong>素材</strong>: コットンポリエステル混紡（耐久性が高く、手入れが簡単な実用的な素材）</p><p><strong>シルエット</strong>: リラックスフィット（胸部とウエストに余裕を持たせ、快適な着心地を実現）</p><p><strong>特徴</strong>:</p><ul><li>スタンダードカラーでカジュアルにもビジネスにも適応</li><li>シンプルなボタンカフスが機能性を重視</li><li>ストレートカットで、日常使いから軽いフォーマルシーンまで対応可能</li><li>手頃な価格で高い実用性を実現</li></ul><p><br></p>', '2024-12-04 11:38:06', '2025-02-05 00:42:24', 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `date_of_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_of_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `date_of_creation`, `date_of_change`) VALUES
(38, 11, '/images/6721af308896e.webp', '2024-10-30 12:59:44', '2024-10-30 12:59:44'),
(40, 17, '/images/67227283bd15b.webp', '2024-10-31 02:53:07', '2024-10-31 02:53:07'),
(42, 3, '/images/6722729b9d1cf.webp', '2024-10-31 02:53:31', '2024-10-31 02:53:31'),
(43, 12, '/images/672272c719d2d.webp', '2024-10-31 02:54:15', '2024-10-31 02:54:15'),
(44, 13, '/images/672272cff0d28.webp', '2024-10-31 02:54:23', '2024-10-31 02:54:23'),
(45, 14, '/images/672272e2d22f0.webp', '2024-10-31 02:54:42', '2024-10-31 02:54:42'),
(51, 11, '/images/67348f1ee4e9f.webp', '2024-11-13 20:35:58', '2024-11-13 20:35:58'),
(53, 15, '/images/674fb98214932.webp', '2024-12-04 11:08:02', '2024-12-04 11:08:02'),
(54, 6, '/images/674fbc375a5ea.webp', '2024-12-04 11:19:35', '2024-12-04 11:19:35'),
(55, 7, '/images/674fbd5300b2d.webp', '2024-12-04 11:24:19', '2024-12-04 11:24:19'),
(56, 8, '/images/674fbe2ae8909.webp', '2024-12-04 11:27:54', '2024-12-04 11:27:54'),
(57, 16, '/images/674fc008bd1e3.webp', '2024-12-04 11:35:52', '2024-12-04 11:35:52'),
(58, 26, '/images/674fc08e2d21b.webp', '2024-12-04 11:38:06', '2024-12-04 11:38:06'),
(59, 5, '/images/674fc116584cf.webp', '2024-12-04 11:40:22', '2024-12-04 11:40:22'),
(60, 25, '/images/674fc2b828f9f.webp', '2024-12-04 11:47:20', '2024-12-04 11:47:20'),
(61, 11, '/images/67a2cb63a1c18.webp', '2025-02-05 11:22:27', '2025-02-05 11:22:27'),
(62, 14, '/images/67a2cbbbd23d9.webp', '2025-02-05 11:23:55', '2025-02-05 11:23:55'),
(63, 13, '/images/67a2cc1eddabc.webp', '2025-02-05 11:25:34', '2025-02-05 11:25:34'),
(64, 10, '/images/67a2ccc7385be.webp', '2025-02-05 11:28:23', '2025-02-05 11:28:23'),
(65, 9, '/images/67a2cd2f55a13.webp', '2025-02-05 11:30:07', '2025-02-05 11:30:07');

-- --------------------------------------------------------

--
-- Структура таблицы `sizes`
--

CREATE TABLE `sizes` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `height_min` int DEFAULT NULL,
  `height_max` int DEFAULT NULL,
  `shoulder_width_min` int DEFAULT NULL,
  `shoulder_width_max` int DEFAULT NULL,
  `waist_size_min` int DEFAULT NULL,
  `waist_size_max` int DEFAULT NULL,
  `date_of_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_of_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stock` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `sizes`
--

INSERT INTO `sizes` (`id`, `product_id`, `name`, `price`, `height_min`, `height_max`, `shoulder_width_min`, `shoulder_width_max`, `waist_size_min`, `waist_size_max`, `date_of_creation`, `date_of_change`, `stock`) VALUES
(8, 3, '25cm', 7500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 11:24:38', '2024-12-04 02:43:24', 5),
(9, 3, '26cm', 7500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 11:24:38', '2024-12-04 02:43:24', 5),
(14, 5, '25cm', 15000, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 12:00:00', '2024-12-04 02:40:50', 5),
(15, 5, '26cm', 15000, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 12:00:00', '2024-12-04 02:40:50', 5),
(16, 5, '27cm', 15000, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 12:00:00', '2024-12-04 02:40:50', 5),
(18, 6, 'S', 80000, 160, 165, 42, 44, 72, 76, '2024-10-21 12:30:00', '2024-12-04 02:19:35', 5),
(19, 6, 'M', 82000, 165, 170, 44, 46, 76, 80, '2024-10-21 12:30:00', '2024-12-04 02:19:35', 4),
(20, 6, 'L', 84000, 170, 175, 46, 48, 80, 84, '2024-10-21 12:30:00', '2024-12-04 02:19:35', 3),
(21, 6, 'XL', 86000, 175, 180, 48, 50, 84, 88, '2024-10-21 12:30:00', '2024-12-04 02:19:35', 2),
(22, 7, 'S', 90000, 160, 165, 42, 44, 72, 76, '2024-10-21 12:30:00', '2024-12-04 02:24:19', 5),
(23, 7, 'M', 92000, 165, 170, 44, 46, 76, 80, '2024-10-21 12:30:00', '2024-12-04 02:24:19', 4),
(24, 7, 'L', 94000, 170, 175, 46, 48, 80, 84, '2024-10-21 12:30:00', '2024-12-04 02:24:19', 3),
(25, 7, 'XL', 96000, 175, 180, 48, 50, 84, 88, '2024-10-21 12:30:00', '2024-12-04 02:24:19', 2),
(26, 8, 'S', 55000, 160, 165, 42, 44, 72, 76, '2024-10-21 12:30:00', '2024-12-04 02:27:55', 5),
(27, 8, 'M', 56000, 165, 170, 44, 46, 76, 80, '2024-10-21 12:30:00', '2024-12-04 02:27:55', 4),
(28, 8, 'L', 57000, 170, 175, 46, 48, 80, 84, '2024-10-21 12:30:00', '2024-12-04 02:27:55', 3),
(29, 8, 'XL', 58000, 175, 180, 48, 50, 84, 88, '2024-10-21 12:30:00', '2024-12-04 02:27:55', 2),
(30, 9, 'S', 13000, 160, 165, 42, 44, 72, 76, '2024-10-21 12:30:00', '2024-10-21 12:30:00', 5),
(31, 9, 'M', 14000, 165, 170, 44, 46, 76, 80, '2024-10-21 12:30:00', '2024-10-21 12:30:00', 4),
(32, 9, 'L', 15000, 170, 175, 46, 48, 80, 84, '2024-10-21 12:30:00', '2024-10-21 12:30:00', 3),
(33, 9, 'XL', 16000, 175, 180, 48, 50, 84, 88, '2024-10-21 12:30:00', '2024-10-21 12:30:00', 2),
(34, 10, 'S', 50000, 160, 165, 42, 44, 72, 76, '2024-10-21 12:30:00', '2025-02-05 02:28:23', 5),
(35, 10, 'M', 51000, 165, 170, 44, 46, 76, 80, '2024-10-21 12:30:00', '2025-02-05 02:28:23', 4),
(36, 10, 'L', 52000, 170, 175, 46, 48, 80, 84, '2024-10-21 12:30:00', '2025-02-05 02:28:23', 3),
(37, 10, 'XL', 55000, 175, 180, 48, 50, 84, 88, '2024-10-21 12:30:00', '2025-02-05 02:28:23', 2),
(38, 11, 'S', 13000, 160, 165, 42, 44, 72, 76, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 5),
(39, 11, 'M', 14000, 165, 170, 44, 46, 76, 80, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 4),
(40, 11, 'L', 15000, 170, 175, 46, 48, 80, 84, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 3),
(41, 11, 'XL', 16000, 175, 180, 48, 50, 84, 88, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 2),
(42, 12, 'S', 13000, 160, 165, 42, 44, 72, 76, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 5),
(43, 12, 'M', 14000, 165, 170, 44, 46, 76, 80, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 4),
(44, 12, 'L', 15000, 170, 175, 46, 48, 80, 84, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 3),
(45, 12, 'XL', 16000, 175, 180, 48, 50, 84, 88, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 2),
(46, 13, 'S', 13000, 160, 165, 42, 44, 72, 76, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 5),
(47, 13, 'M', 14000, 165, 170, 44, 46, 76, 80, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 4),
(48, 13, 'L', 15000, 170, 175, 46, 48, 80, 84, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 3),
(49, 13, 'XL', 16000, 175, 180, 48, 50, 84, 88, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 2),
(50, 14, 'S', 13000, 160, 165, 42, 44, 72, 76, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 5),
(51, 14, 'M', 14000, 165, 170, 44, 46, 76, 80, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 4),
(52, 14, 'L', 15000, 170, 175, 46, 48, 80, 84, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 3),
(53, 14, 'XL', 16000, 175, 180, 48, 50, 84, 88, '2024-10-21 13:00:00', '2024-10-21 13:00:00', 2),
(54, 15, 'S', 45000, 160, 165, 42, 44, 72, 76, '2024-10-21 13:00:00', '2024-12-04 02:09:33', 5),
(55, 15, 'M', 47000, 165, 170, 44, 46, 76, 80, '2024-10-21 13:00:00', '2024-12-04 02:09:33', 4),
(56, 15, 'L', 49000, 170, 175, 46, 48, 80, 84, '2024-10-21 13:00:00', '2024-12-04 02:09:33', 3),
(57, 15, 'XL', 51000, 175, 180, 48, 50, 84, 88, '2024-10-21 13:00:00', '2024-12-04 02:09:33', 2),
(58, 16, 'S', 4000, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-12-04 02:36:13', 5),
(59, 16, 'M', 4000, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-12-04 02:36:13', 5),
(60, 16, 'L', 7500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-12-04 02:36:13', 5),
(61, 17, 'M', 4500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-10-21 13:30:00', 5),
(62, 17, 'L', 4500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-10-21 13:30:00', 5),
(63, 17, 'XL', 4500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-21 13:30:00', '2024-10-21 13:30:00', 5),
(68, 25, '25cm～28.0cm', 1200, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-27 11:01:34', '2024-12-04 02:47:20', 5),
(69, 26, 'S', 3500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 11:38:06', '2025-02-05 00:42:24', 3),
(70, 26, 'M', 3500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 11:38:06', '2024-12-04 11:38:06', 5),
(71, 26, 'L', 3500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 11:38:06', '2024-12-04 11:38:06', 7),
(72, 26, 'XL', 3500, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 11:38:06', '2024-12-04 11:38:06', 5),
(74, 17, 'XLL', 4500, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-20 11:29:45', '2025-01-20 11:29:45', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `name` text NOT NULL,
  `date_of_registration` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` text NOT NULL,
  `email` text NOT NULL,
  `auth_token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `name`, `date_of_registration`, `role`, `email`, `auth_token`) VALUES
(1, 'admin', '$2y$10$j44dFRzfxEmUgx4Lex.QS.KasGYv/EW8F.oPpc64KX2Z14tvFA.uS', 'Admin', '2023-09-01 08:00:00', 'admin', 'admin@arts-suit.com', 'acd6538a6bf530331641c3a777628b16d41d8a9f2fa4c065ab69042d00988e5d'),
(2, 'staff1', 'staffpass', 'Staff User', '2023-09-15 10:00:00', 'staff', 'staff1@arts-suit.com', '');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `client_addresses`
--
ALTER TABLE `client_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_client` (`client_id`);

--
-- Индексы таблицы `client_orders`
--
ALTER TABLE `client_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_clients` (`client_id`),
  ADD KEY `fk_clien_add` (`address_id`);

--
-- Индексы таблицы `client_order_indexes`
--
ALTER TABLE `client_order_indexes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prodcucts` (`product_id`),
  ADD KEY `fk_orders` (`client_order_id`);

--
-- Индексы таблицы `client_payment_methods`
--
ALTER TABLE `client_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_client_pm` (`client_id`);

--
-- Индексы таблицы `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `options_indexes`
--
ALTER TABLE `options_indexes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_images` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_images` (`product_id`);

--
-- Индексы таблицы `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product` (`product_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `client_addresses`
--
ALTER TABLE `client_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `client_orders`
--
ALTER TABLE `client_orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `client_order_indexes`
--
ALTER TABLE `client_order_indexes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT для таблицы `client_payment_methods`
--
ALTER TABLE `client_payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT для таблицы `options`
--
ALTER TABLE `options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `options_indexes`
--
ALTER TABLE `options_indexes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT для таблицы `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `client_addresses`
--
ALTER TABLE `client_addresses`
  ADD CONSTRAINT `fk_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Ограничения внешнего ключа таблицы `client_orders`
--
ALTER TABLE `client_orders`
  ADD CONSTRAINT `fk_clien_add` FOREIGN KEY (`address_id`) REFERENCES `client_addresses` (`id`),
  ADD CONSTRAINT `fk_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Ограничения внешнего ключа таблицы `client_order_indexes`
--
ALTER TABLE `client_order_indexes`
  ADD CONSTRAINT `fk_orders` FOREIGN KEY (`client_order_id`) REFERENCES `client_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prodcucts` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `client_payment_methods`
--
ALTER TABLE `client_payment_methods`
  ADD CONSTRAINT `fk_client_pm` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `sizes`
--
ALTER TABLE `sizes`
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
