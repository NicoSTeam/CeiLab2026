-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-10-2026 a las 13:26:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ceibal`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo`
--

CREATE TABLE `equipo` (
  `IDEquipo` int(50) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Observaciones` varchar(50) DEFAULT NULL,
  `Tipo` varchar(50) DEFAULT NULL,
  `IDSala_ceilab` int(11) NOT NULL,
  `Disponibilidad` tinyint(1) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo`
--

INSERT INTO `equipo` (`IDEquipo`, `Estado`, `Observaciones`, `Tipo`, `IDSala_ceilab`, `Disponibilidad`, `activo`) VALUES
(1, 'EnMantenimiento', 'Devolución en estado roto', 'Ceibalita', 1, 0, 1),
(6, 'NoDisponible', '', 'Lápiz 3D', 2, 0, 1),
(7, 'Disponible', 'Devolución en estado normal', 'Ceibalita', 1, 1, 1),
(8, 'EnMantenimiento', 'cambio de bateria', 'Dron', 1, 0, 1),
(9, 'NoDisponible', 'No devuelto el 2026-10-27. se olvido', 'Micro:bit', 2, 0, 1),
(10, 'Disponible', 'Devolución en estado normal', 'Sensores Fisicoquímicos', 1, 1, 1),
(11, 'Disponible', 'Devolución en estado normal', 'Impresora 3D', 2, 1, 1),
(12, 'NoDisponible', 'No devuelto el 2026-10-30. no devuelve', 'Placa Makey Makey', 1, 0, 1),
(13, 'Disponible', '', 'Ceibalita', 1, 1, 1),
(14, 'Disponible', '', 'Microscopio USB', 1, 1, 0),
(15, 'Disponible', 'Devolución en estado normal', 'Microscopio USB', 1, 1, 1),
(16, 'EnMantenimiento', '', 'Dron', 1, 0, 1),
(17, 'Disponible', 'Devolución en estado normal', 'Dron', 1, 1, 1),
(18, 'EnMantenimiento', '', 'Dron', 1, 0, 1),
(19, 'EnMantenimiento', '', 'Impresora 3D', 2, 0, 1),
(20, 'Disponible', '', 'Ceibalita', 1, 1, 1),
(21, 'Disponible', '', 'Cable HDMI', 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_dot`
--

CREATE TABLE `horarios_dot` (
  `idDot` int(11) NOT NULL,
  `IDusuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `descripcion` varchar(255) DEFAULT 'Presencial en Sala',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_dot`
--

INSERT INTO `horarios_dot` (`idDot`, `IDusuario`, `fecha`, `hora_inicio`, `hora_fin`, `descripcion`, `fecha_registro`) VALUES
(1, 49, '2026-10-10', '09:00:00', '12:00:00', 'Presencial en Sala', '2026-10-22 02:31:06'),
(3, 49, '2026-11-13', '21:00:00', '23:00:00', 'Presencial en Sala', '2026-10-22 02:40:23'),
(5, 49, '2026-10-10', '09:00:00', '12:00:00', 'Presencial en Sala2', '2026-10-27 03:28:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operaciones`
--

CREATE TABLE `operaciones` (
  `IDOperaciones` int(10) NOT NULL,
  `Accion` int(10) NOT NULL,
  `Fecha` date NOT NULL,
  `HoraInicio` time DEFAULT NULL,
  `HoraFin` time DEFAULT NULL,
  `Observaciones` varchar(70) NOT NULL,
  `IDUsuario` int(11) DEFAULT NULL,
  `IDEquipo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `operaciones`
--

INSERT INTO `operaciones` (`IDOperaciones`, `Accion`, `Fecha`, `HoraInicio`, `HoraFin`, `Observaciones`, `IDUsuario`, `IDEquipo`) VALUES
(5, 1, '2026-10-26', '07:00:00', '09:00:00', 'Préstamo generado desde Reserva #28', 49, 1),
(6, 1, '2026-10-26', '07:00:00', '09:00:00', 'Préstamo generado desde Reserva #28', 49, 7),
(7, 1, '2026-10-26', '07:00:00', '16:00:00', 'Préstamo directo sin reserva previa', 51, 11),
(8, 1, '2026-10-26', '07:00:00', '15:00:00', 'Préstamo generado desde Reserva #29', 49, 9),
(9, 1, '2026-10-26', '07:00:00', '15:00:00', 'Préstamo generado desde Reserva #29', 49, 10),
(10, 2, '2026-10-26', '18:11:56', '18:11:56', 'Devolución registrada. Condición: Roto. La impreso', 51, 11),
(11, 1, '2026-10-26', '07:00:00', '12:00:00', 'Préstamo directo sin reserva previa', 52, 12),
(12, 2, '2026-10-26', '18:16:06', '18:16:06', 'Devolución registrada. Condición: Roto. roto', 52, 12),
(13, 1, '2026-10-26', '13:00:00', '14:00:00', 'Préstamo directo sin reserva previa', 52, 12),
(14, 2, '2026-10-26', '18:24:17', '18:24:17', 'Devolución registrada. Condición: Roto. falta entr', 52, 12),
(15, 2, '2026-10-26', '18:26:57', '18:26:57', 'Devolución registrada. Condición: Normal. ', 49, 7),
(16, 2, '2026-10-26', '18:28:16', '18:28:16', 'Devolución registrada. Condición: Normal. ', 49, 1),
(17, 1, '2026-10-26', '19:00:00', '23:00:00', 'Préstamo generado desde Reserva #30', 49, 1),
(18, 1, '2026-10-26', '19:00:00', '23:00:00', 'Préstamo generado desde Reserva #30', 49, 7),
(19, 2, '2026-10-26', '18:28:35', '18:28:35', 'Devolución registrada. Condición: Normal. ', 49, 7),
(20, 2, '2026-10-26', '18:28:39', '18:28:39', 'Devolución registrada. Condición: Normal. ', 49, 1),
(21, 2, '2026-10-26', '18:28:45', '18:28:45', 'Devolución registrada. Condición: Normal. ', 49, 9),
(22, 1, '2026-10-26', '15:00:00', '17:00:00', 'Préstamo directo sin reserva previa', 52, 1),
(23, 2, '2026-10-26', '18:38:07', '18:38:07', 'Incidencia: Equipo no devuelto al cierre del día. No entrega equipo di', 52, 1),
(24, 2, '2026-10-26', '18:57:00', '18:57:00', 'Devolución registrada. Condición: Normal. ', 49, 10),
(25, 1, '2026-10-26', '16:00:00', '18:00:00', 'Préstamo generado desde Reserva #33', 52, 10),
(26, 2, '2026-10-26', '18:57:34', '18:57:34', 'Devolución registrada. Condición: Normal. ', 52, 10),
(27, 1, '2026-10-26', '15:00:00', '23:00:00', 'Préstamo generado desde Reserva #31', 49, 9),
(28, 1, '2026-10-26', '15:00:00', '23:00:00', 'Préstamo generado desde Reserva #31', 49, 12),
(29, 2, '2026-10-27', '03:05:49', '03:05:49', 'Devolución registrada. Condición: Normal. ', 49, 12),
(30, 2, '2026-10-27', '03:06:11', '03:06:11', 'Incidencia: Equipo no devuelto al cierre del día. se olvido', 49, 9),
(31, 1, '2026-10-27', '07:00:00', '10:00:00', 'Préstamo generado desde Reserva #35', 52, 15),
(32, 2, '2026-10-27', '03:39:36', '03:39:36', 'Devolución registrada. Condición: Normal. ', 52, 15),
(33, 1, '2026-10-27', '07:00:00', '10:00:00', 'Préstamo directo sin reserva previa', 12, 12),
(34, 2, '2026-10-27', '05:59:44', '05:59:44', 'Devolución registrada. Condición: Normal. ', 12, 12),
(35, 1, '2026-10-27', '07:00:00', '19:00:00', 'Préstamo generado desde Reserva #40', 49, 1),
(36, 1, '2026-10-27', '07:00:00', '19:00:00', 'Préstamo generado desde Reserva #40', 49, 7),
(37, 1, '2026-10-27', '09:00:00', '13:00:00', 'Préstamo directo sin reserva previa', 52, 10),
(38, 2, '2026-10-27', '06:29:10', '06:29:10', 'Devolución registrada. Condición: Normal. ', 52, 10),
(39, 1, '2026-10-27', '14:00:00', '15:00:00', 'Préstamo generado desde Reserva #41', 49, 11),
(40, 1, '2026-10-27', '14:00:00', '15:00:00', 'Préstamo generado desde Reserva #41', 49, 17),
(41, 2, '2026-10-27', '06:38:07', '06:38:07', 'Devolución registrada. Condición: Normal. ', 49, 17),
(42, 2, '2026-10-27', '06:38:10', '06:38:10', 'Devolución registrada. Condición: Normal. ', 49, 11),
(43, 2, '2026-10-27', '06:38:12', '06:38:12', 'Devolución registrada. Condición: Normal. ', 49, 7),
(44, 2, '2026-10-27', '06:38:14', '06:38:14', 'Devolución registrada. Condición: Normal. ', 49, 1),
(45, 1, '2026-10-27', '07:00:00', '08:00:00', 'Préstamo directo sin reserva previa', 49, 17),
(46, 2, '2026-10-27', '07:41:24', '07:41:24', 'Devolución registrada. Condición: Normal. ', 49, 17),
(47, 1, '2026-10-27', '07:00:00', '08:00:00', 'Préstamo directo sin reserva previa', 15, 12),
(48, 1, '2026-10-27', '07:00:00', '08:00:00', 'Préstamo directo sin reserva previa', 15, 10),
(49, 2, '2026-10-28', '02:38:08', '02:38:08', 'Incidencia: Equipo no devuelto al cierre del día. uwu', 15, 12),
(50, 2, '2026-10-29', '07:14:53', '07:14:53', 'Devolución registrada. Condición: Normal. ', 15, 10),
(51, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 1),
(52, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 7),
(53, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 17),
(54, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 11),
(55, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 12),
(56, 1, '2026-10-29', '18:00:00', '19:00:00', 'Préstamo directo sin reserva previa', 52, 10),
(57, 2, '2026-10-30', '08:17:53', '08:17:53', 'Incidencia: Equipo no devuelto al cierre del día. no devuelve', 52, 12),
(58, 2, '2026-10-30', '08:43:30', '08:43:30', 'Devolución registrada. Condición: Normal. ', 52, 11),
(59, 2, '2026-10-30', '08:43:57', '08:43:57', 'Devolución registrada. Condición: Normal. ', 52, 17),
(60, 2, '2026-10-30', '08:44:06', '08:44:06', 'Devolución registrada. Condición: Roto. ', 52, 1),
(61, 2, '2026-10-30', '08:45:13', '08:45:13', 'Devolución registrada. Condición: Normal. ', 52, 10),
(62, 1, '2026-10-31', '07:00:00', '09:00:00', 'Préstamo directo sin reserva previa', 49, 17),
(63, 1, '2026-10-31', '07:00:00', '09:00:00', 'Préstamo directo sin reserva previa', 49, 11),
(64, 1, '2026-10-31', '07:00:00', '09:00:00', 'Préstamo directo sin reserva previa', 57, 10),
(65, 2, '2026-10-31', '08:00:46', '08:00:46', 'Devolución registrada. Condición: Normal. ', 57, 10),
(66, 2, '2026-10-31', '08:00:48', '08:00:48', 'Devolución registrada. Condición: Normal. ', 49, 11),
(67, 2, '2026-10-31', '08:00:50', '08:00:50', 'Devolución registrada. Condición: Normal. ', 49, 17),
(68, 2, '2026-10-31', '08:00:52', '08:00:52', 'Devolución registrada. Condición: Normal. ', 52, 7),
(69, 1, '2026-10-31', '07:00:00', '09:00:00', 'Préstamo directo sin reserva previa', 57, 7),
(70, 1, '2026-10-31', '15:00:00', '16:00:00', 'Préstamo directo sin reserva previa', 51, 10),
(71, 2, '2026-10-31', '09:11:38', '09:11:38', 'Devolución registrada. Condición: Normal. ', 57, 7),
(72, 1, '2026-10-31', '12:00:00', '17:00:00', 'Préstamo generado desde Reserva #47', 49, 7),
(73, 1, '2026-10-31', '12:00:00', '17:00:00', 'Préstamo generado desde Reserva #47', 49, 17),
(74, 2, '2026-10-31', '09:16:13', '09:16:13', 'Devolución registrada. Condición: Normal. ', 49, 7),
(75, 2, '2026-10-31', '09:16:15', '09:16:15', 'Devolución registrada. Condición: Normal. ', 49, 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `IDReservas` int(10) NOT NULL,
  `CantHoras` int(10) NOT NULL,
  `IDSalon` int(10) NOT NULL,
  `IDUsuario` int(10) NOT NULL,
  `Fecha` date DEFAULT NULL,
  `HoraInicio` time NOT NULL,
  `HoraFin` time NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`IDReservas`, `CantHoras`, `IDSalon`, `IDUsuario`, `Fecha`, `HoraInicio`, `HoraFin`, `activo`) VALUES
(1, 8, 1, 49, '2026-10-28', '10:00:00', '18:00:00', 0),
(2, 5, 1, 49, '2026-10-29', '15:00:00', '20:00:00', 0),
(3, 1, 1, 49, '2026-10-31', '16:00:00', '17:00:00', 1),
(4, 2, 1, 49, '2026-10-31', '18:00:00', '20:00:00', 0),
(5, 1, 1, 49, '2026-10-26', '17:00:00', '18:00:00', 0),
(6, 2, 1, 15, '2026-10-31', '20:00:00', '22:00:00', 0),
(7, 4, 1, 15, '2026-10-26', '13:00:00', '17:00:00', 0),
(8, 4, 1, 15, '2026-10-26', '13:00:00', '17:00:00', 0),
(9, 2, 1, 49, '2026-10-25', '12:00:00', '14:00:00', 1),
(10, 15, 1, 49, '2026-10-01', '07:00:00', '22:00:00', 1),
(11, 1, 1, 49, '2026-10-10', '19:00:00', '20:00:00', 1),
(12, 2, 1, 15, '2026-10-31', '17:00:00', '19:00:00', 1),
(13, 3, 1, 49, '2026-10-25', '07:00:00', '10:00:00', 1),
(14, 3, 1, 49, '2026-10-25', '07:00:00', '10:00:00', 0),
(15, 3, 1, 49, '2026-10-25', '07:00:00', '10:00:00', 1),
(16, 2, 1, 49, '2026-10-25', '19:00:00', '22:00:00', 0),
(17, 3, 1, 49, '2026-10-28', '19:00:00', '22:00:00', 1),
(18, 2, 1, 15, '2026-10-28', '18:00:00', '20:00:00', 0),
(19, 4, 1, 49, '2026-10-30', '07:00:00', '11:00:00', 1),
(20, 3, 1, 49, '2026-11-20', '16:00:00', '19:00:00', 0),
(21, 3, 2, 49, '2026-11-20', '16:00:00', '19:00:00', 1),
(22, 1, 1, 49, '2026-11-21', '09:00:00', '11:00:00', 0),
(23, 4, 1, 49, '2026-11-29', '12:00:00', '16:00:00', 1),
(24, 8, 2, 49, '2026-10-26', '07:00:00', '15:00:00', 0),
(25, 5, 1, 49, '2026-12-15', '18:00:00', '23:00:00', 1),
(26, 4, 1, 49, '2026-10-26', '19:00:00', '23:00:00', 0),
(27, 7, 2, 49, '2026-10-26', '07:00:00', '14:00:00', 0),
(28, 2, 1, 49, '2026-10-26', '07:00:00', '09:00:00', 0),
(29, 8, 2, 49, '2026-10-26', '07:00:00', '15:00:00', 0),
(30, 4, 1, 49, '2026-10-26', '19:00:00', '23:00:00', 0),
(31, 8, 2, 49, '2026-10-26', '15:00:00', '23:00:00', 0),
(32, 1, 1, 52, '2026-10-26', '20:00:00', '21:00:00', 1),
(33, 2, 1, 52, '2026-10-26', '16:00:00', '18:00:00', 0),
(34, 7, 1, 52, '2026-10-27', '09:00:00', '16:00:00', 0),
(35, 3, 1, 52, '2026-10-27', '07:00:00', '10:00:00', 0),
(36, 1, 1, 49, '2026-10-28', '18:00:00', '19:00:00', 0),
(37, 1, 1, 49, '2026-10-28', '22:00:00', '23:00:00', 0),
(38, 3, 2, 49, '2026-10-28', '17:00:00', '20:00:00', 0),
(39, 12, 2, 49, '2026-10-27', '07:00:00', '19:00:00', 0),
(40, 12, 1, 49, '2026-10-27', '07:00:00', '19:00:00', 0),
(41, 1, 1, 49, '2026-10-27', '14:00:00', '15:00:00', 0),
(42, 1, 1, 49, '2026-10-27', '07:00:00', '08:00:00', 1),
(43, 1, 1, 49, '2026-10-27', '07:00:00', '08:00:00', 0),
(44, 2, 1, 49, '2026-11-19', '09:00:00', '11:00:00', 0),
(45, 10, 1, 49, '2026-10-29', '07:00:00', '17:00:00', 1),
(46, 1, 1, 52, '2026-11-25', '21:00:00', '22:00:00', 1),
(47, 5, 1, 49, '2026-10-31', '12:00:00', '17:00:00', 0),
(48, 2, 3, 49, '2026-10-31', '07:00:00', '09:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `IDUsuario` int(11) NOT NULL,
  `ROL` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`IDUsuario`, `ROL`) VALUES
(12, 1),
(15, 2),
(15, 3),
(49, 1),
(50, 2),
(50, 3),
(51, 4),
(52, 2),
(53, 4),
(54, 4),
(55, 4),
(56, 3),
(57, 4),
(58, 4),
(59, 4),
(60, 4),
(61, 4),
(62, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sala_ceilab`
--

CREATE TABLE `sala_ceilab` (
  `IDSalon` int(15) NOT NULL,
  `Observaciones` varchar(50) DEFAULT NULL,
  `Disponibilidad` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sala_ceilab`
--

INSERT INTO `sala_ceilab` (`IDSalon`, `Observaciones`, `Disponibilidad`) VALUES
(1, 'Salon inexistente', 1),
(2, '', 1),
(3, '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_material`
--

CREATE TABLE `solicitud_material` (
  `IDSolicitud` int(10) NOT NULL,
  `tipoEquipo` varchar(50) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Cantidad` int(10) NOT NULL,
  `IDUsuario` int(10) NOT NULL,
  `Fecha` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud_material`
--

INSERT INTO `solicitud_material` (`IDSolicitud`, `tipoEquipo`, `Estado`, `Descripcion`, `Cantidad`, `IDUsuario`, `Fecha`, `activo`) VALUES
(1, 'Ceibalita', 'pendiente', 'faltan más equipos para el centro', 20, 49, '2026-10-27', 1),
(2, 'Micro:bit', 'rechazada', 'Feria de la ciencia en camino', 60, 49, '2026-10-29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos`
--

CREATE TABLE `telefonos` (
  `IDUsuario` int(11) NOT NULL,
  `Telefono` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telefonos`
--

INSERT INTO `telefonos` (`IDUsuario`, `Telefono`) VALUES
(12, '094777888'),
(12, '42234498'),
(15, '42154572'),
(49, '42894578'),
(49, '9999999'),
(50, '0912323237'),
(50, '42222223'),
(51, '42222223'),
(52, '345'),
(52, '678'),
(53, '099999999'),
(54, '099999999'),
(55, '22222'),
(56, '12345678'),
(57, '1111'),
(58, '1'),
(59, '1'),
(60, '1'),
(61, '1'),
(62, '784568');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiene_equipos`
--

CREATE TABLE `tiene_equipos` (
  `IDEquipo` int(50) NOT NULL,
  `IDReservas` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiene_equipos`
--

INSERT INTO `tiene_equipos` (`IDEquipo`, `IDReservas`) VALUES
(1, 14),
(1, 15),
(1, 17),
(1, 19),
(1, 22),
(1, 23),
(1, 25),
(1, 26),
(1, 28),
(1, 30),
(1, 34),
(1, 40),
(1, 42),
(1, 44),
(1, 45),
(1, 46),
(7, 16),
(7, 17),
(7, 20),
(7, 23),
(7, 25),
(7, 26),
(7, 28),
(7, 30),
(7, 34),
(7, 40),
(7, 46),
(7, 47),
(9, 15),
(9, 16),
(9, 17),
(9, 21),
(9, 27),
(9, 29),
(9, 31),
(10, 14),
(10, 29),
(10, 32),
(10, 33),
(10, 44),
(10, 46),
(11, 19),
(11, 20),
(11, 21),
(11, 23),
(11, 24),
(11, 26),
(11, 41),
(11, 44),
(11, 46),
(12, 31),
(12, 46),
(15, 35),
(17, 41),
(17, 43),
(17, 46),
(17, 47);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IDUsuario` int(10) NOT NULL,
  `Nombre` varchar(30) DEFAULT NULL,
  `Contraseña` varchar(61) DEFAULT NULL,
  `Email` varchar(30) DEFAULT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Faltas` int(11) NOT NULL DEFAULT 0,
  `Observaciones` varchar(50) NOT NULL,
  `Fecha_creacion` date DEFAULT NULL,
  `Creador_IDUsuario` int(11) DEFAULT NULL,
  `cedula` int(8) NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IDUsuario`, `Nombre`, `Contraseña`, `Email`, `Direccion`, `Faltas`, `Observaciones`, `Fecha_creacion`, `Creador_IDUsuario`, `cedula`, `activo`) VALUES
(12, 'Nico', '$2y$10$OP7J4x2g3RsFf7Ivd2w7vOhRjNBNMU2qHvfYozqdPLalp2J4v3LIW', 'nico@gmail.com', 'lejos', 0, 'No duerme', NULL, NULL, 48850557, 1),
(15, 'Gonzalo', '$2y$10$toFPqFGO5v13IABeZgU8heXArL7AoGj502C./94ZNLGTrPm/Kpeqy', 'gonzalo@gmail.com', 'lejos', 1, 'No duerme', NULL, NULL, 55554555, 1),
(49, 'Nico', '$2y$10$4oOilxbMg.B5h2Pm17f2EebI6E1x/dwLiyhhNVNxmsBLq.q/Pf9XW', 'nico1@gmail.com', 'lejos', 1, 'No duerme', NULL, NULL, 48850556, 1),
(50, 'Eduardo', '$2y$10$DPp0zZ45PF.svnYYloNo7.5xsJcmp6PZe8Stt8lZ3hF9d0Hc37Fvm', 'eduardo@gmail.com', 'cerp', 0, 'Es Profe', NULL, NULL, 12345678, 0),
(51, 'Estudiante', '$2y$10$X2J4KZ9MOle9R566qUHDueJWJzD0v0z3PPlqVtLmywELPih1VpdQW', 'estudiante@gmail.com', 'su casa', 0, 'va a clase', NULL, NULL, 12345678, 1),
(52, 'prueba', '$2y$10$uUZUpFRobCf0B5Mhh9MH6eaHdNtvk8zG.9zoPHokb2VFFBv3HkmTy', 'prueba1@gmail.com', 'ff', 7, 'f', NULL, NULL, 2, 1),
(53, 'pepito', '$2y$10$sZn9CLr.V7Uvw0SZy9kAsuRVfPl4NrWcLlpOBWCbDF2offQ8MX7qq', 'cositopum@gmail.com', 'Mi_casa', 0, 'usuario de prueba', NULL, NULL, 3344667, 0),
(54, 'pepito', '$2y$10$GusBlYqybcbeFawnsCuN4OfuOvmK3lwmLI4Ga9Cy1.RmGNhYYnGNe', 'cositopumm@gmail.com', 'Mi_casa', 0, '', NULL, NULL, 3344667, 0),
(55, 'ben10', '$2y$10$ONiw5EYqXyHIPTAJxyWmqOdb4xQW1rfi9NlhtQu6EVCffa/ZCccS.', 'ben10@gmail.com', 'El espacio', 0, '', NULL, NULL, 22, 0),
(56, 'luffy', '$2y$10$MNQ0d1dhvKAIblBCa5mz4.xPUM/GREzDAiHYRn1VWEBFbQpSyMHG6', 'luffy@gmail.com', 'grandline', 0, '', NULL, NULL, 18948754, 1),
(57, 'zoro', '$2y$10$1Y1VetchUROcROTm33u.rOLS/j824TRaNDOJVW66dwbbigs3WjH9G', 'zoro@gmail.com', 'perdido', 0, '', NULL, NULL, 21549878, 1),
(58, 'd', '$2y$10$bKxjPARC6Yv7Hyj1xHgBL.RF3RtAu9mrLOjsoPN6XkQ4fe2kd5bvG', 'g@mail.com', 'perdido', 0, '', NULL, NULL, 7, 0),
(59, 'd', '$2y$10$TyLWyV06HGIuDtcsBFdIgOsYIsFJUTzs.XeYB7nsydQfkxlm1IEdm', 'gq@mail.com', 'perdido', 0, '', NULL, NULL, 3, 0),
(60, 'f', '$2y$10$WxigetcB8e6o9jFnjGXaAeXthwHK8iVDEVSQjVI5L0.2VmmWJ8Zem', 'h@gmail.com', 'u', 0, '', NULL, NULL, 5, 0),
(61, 'r', '$2y$10$OTlkfTBZwxgHxB4uA3QI5.Frf/sbu7XpPfgkXdWiVtrMFzW5gnjpe', 'o@gmail.com', 'o', 0, '', NULL, NULL, 6, 0),
(62, 'Hijo', '$2y$10$pahzYsdcfikktk6MdYB4JeB/Vl7fP6lFrRPmwsuIKHVdWVoR8UHfu', 'hijo@gmail.com', 'en frente al hospital', 0, '', '2026-10-31', 49, 34567891, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD PRIMARY KEY (`IDEquipo`),
  ADD KEY `IDSala_ceilab` (`IDSala_ceilab`);

--
-- Indices de la tabla `horarios_dot`
--
ALTER TABLE `horarios_dot`
  ADD PRIMARY KEY (`idDot`),
  ADD KEY `IDusuario` (`IDusuario`);

--
-- Indices de la tabla `operaciones`
--
ALTER TABLE `operaciones`
  ADD PRIMARY KEY (`IDOperaciones`),
  ADD KEY `FK_Operaciones_USUARIO` (`IDUsuario`),
  ADD KEY `FK_Operaciones_equipo` (`IDEquipo`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`IDReservas`),
  ADD KEY `IDSalon` (`IDSalon`),
  ADD KEY `FK_reservas_usuario` (`IDUsuario`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`IDUsuario`,`ROL`);

--
-- Indices de la tabla `sala_ceilab`
--
ALTER TABLE `sala_ceilab`
  ADD PRIMARY KEY (`IDSalon`);

--
-- Indices de la tabla `solicitud_material`
--
ALTER TABLE `solicitud_material`
  ADD PRIMARY KEY (`IDSolicitud`),
  ADD KEY `FK_Solicitud_IDUsusario` (`IDUsuario`);

--
-- Indices de la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD PRIMARY KEY (`IDUsuario`,`Telefono`);

--
-- Indices de la tabla `tiene_equipos`
--
ALTER TABLE `tiene_equipos`
  ADD PRIMARY KEY (`IDEquipo`,`IDReservas`),
  ADD KEY `reserva` (`IDReservas`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IDUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo`
--
ALTER TABLE `equipo`
  MODIFY `IDEquipo` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `horarios_dot`
--
ALTER TABLE `horarios_dot`
  MODIFY `idDot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `operaciones`
--
ALTER TABLE `operaciones`
  MODIFY `IDOperaciones` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `IDReservas` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `solicitud_material`
--
ALTER TABLE `solicitud_material`
  MODIFY `IDSolicitud` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IDUsuario` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD CONSTRAINT `IDSala_ceilab` FOREIGN KEY (`IDSala_ceilab`) REFERENCES `sala_ceilab` (`IDSalon`);

--
-- Filtros para la tabla `horarios_dot`
--
ALTER TABLE `horarios_dot`
  ADD CONSTRAINT `horarios_dot_ibfk_1` FOREIGN KEY (`IDusuario`) REFERENCES `usuario` (`IDUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `operaciones`
--
ALTER TABLE `operaciones`
  ADD CONSTRAINT `FK_Operaciones_equipo` FOREIGN KEY (`IDEquipo`) REFERENCES `equipo` (`IDEquipo`),
  ADD CONSTRAINT `FK_Usuario` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `IDSalon` FOREIGN KEY (`IDSalon`) REFERENCES `sala_ceilab` (`IDSalon`),
  ADD CONSTRAINT `IDUsuario` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`);

--
-- Filtros para la tabla `rol`
--
ALTER TABLE `rol`
  ADD CONSTRAINT `rol_ibfk_1` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`);

--
-- Filtros para la tabla `solicitud_material`
--
ALTER TABLE `solicitud_material`
  ADD CONSTRAINT `FK_Solicitud_IDUsusario` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`);

--
-- Filtros para la tabla `telefonos`
--
ALTER TABLE `telefonos`
  ADD CONSTRAINT `telefonos_ibfk_1` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`);

--
-- Filtros para la tabla `tiene_equipos`
--
ALTER TABLE `tiene_equipos`
  ADD CONSTRAINT `equipo` FOREIGN KEY (`IDEquipo`) REFERENCES `equipo` (`IDEquipo`),
  ADD CONSTRAINT `reserva` FOREIGN KEY (`IDReservas`) REFERENCES `reservas` (`IDReservas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
