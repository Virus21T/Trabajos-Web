-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 24-05-2025 a las 13:50:47
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nequi`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

DROP TABLE IF EXISTS `transacciones`;
CREATE TABLE IF NOT EXISTS `transacciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `origen_id` int DEFAULT NULL,
  `destino_id` int NOT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `origen_id` (`origen_id`),
  KEY `destino_id` (`destino_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `transacciones`
--

INSERT INTO `transacciones` (`id`, `origen_id`, `destino_id`, `monto`, `fecha`) VALUES
(1, 2, 2, 12345.00, '2025-05-19 11:03:00'),
(2, 3, 2, 2000.00, '2025-05-19 11:04:47'),
(3, 4, 2, 1000000.00, '2025-05-19 15:14:48'),
(4, 2, 2, 2000.00, '2025-05-24 02:32:51'),
(5, 5, 2, 2000.00, '2025-05-24 03:34:38'),
(6, 5, 0, 2000.00, '2025-05-24 03:38:18'),
(7, 2, 0, 2000.00, '2025-05-24 03:41:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `saldo` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `telefono` (`telefono`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `telefono`, `password`, `saldo`) VALUES
(1, 'diego', '3209091694', '$2y$10$t2rEDqwZja6CP0ypXhLqS.Qra6UXQ9tGwgfYaU1eyQZmsts8Oh.e2', 0.00),
(2, 'diego', '3208090912', '$2y$10$G.BmQ1TbORELWjVAt.1MrOZCYf5KWvU5rWOoBx9Y6OK4qt.PLwbry', 1046345.00),
(3, 'diego', '32145', '$2y$10$UVZOBIyvUc6gcBGq6IdGXupqdzQT8aWZ0P8bp8lrglQW4bW5Na7MW', 18000.00),
(4, 'batman', '3009826288', '$2y$10$wXTVN4LIMyA/nBsb0GOAROKgwxAwTaVlUvzRLcrkOhvsK0BwkbFUe', 98999999.99),
(5, 'carlos', '3208090911', '$2y$10$uXD3natP4iIUn4vA4typC.XHNjYqPr4rTaaXlykEZussbO/TyUcMi', 4000.00);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
