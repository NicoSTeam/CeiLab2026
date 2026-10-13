-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-10-2026 a las 06:16:56
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
-- Base de datos: `cerp`
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
  `Disponibilidad` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo`
--

INSERT INTO `equipo` (`IDEquipo`, `Estado`, `Observaciones`, `Tipo`, `IDSala_ceilab`, `Disponibilidad`) VALUES
(1, 'Inserbible', 'Pantalla destruida', 'Ceibalita', 1, 1),
(2, 'Aceptable', '  ', 'Ceibalita', 1, 2),
(3, 'Fuera de servicio', 'Problema desconocido  ', 'Impresora 3D', 1, 2),
(4, 'Aceptable', '    ', 'DRON', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `operaciones`
--

CREATE TABLE `operaciones` (
  `IDOperaciones` int(10) NOT NULL,
  `Accion` int(10) NOT NULL,
  `Fecha` date NOT NULL,
  `Observaciones` varchar(50) NOT NULL,
  `IDUsuario` int(11) DEFAULT NULL,
  `IDEquipo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `IDReservas` int(10) NOT NULL,
  `CantHoras` int(10) NOT NULL,
  `IDSalon` int(10) NOT NULL,
  `IDUsuario` int(10) NOT NULL,
  `Fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(0, 1),
(1, 0),
(2, 3);

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
(1, 'Salon inexistente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_material`
--

CREATE TABLE `solicitud_material` (
  `IDSolicitud` int(10) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Cantidad` int(10) NOT NULL,
  `IDUsuario` int(10) NOT NULL,
  `Fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telefonos`
--

CREATE TABLE `telefonos` (
  `IDUsuario` int(11) NOT NULL,
  `Telefono` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `telefonos`
--

INSERT INTO `telefonos` (`IDUsuario`, `Telefono`) VALUES
(0, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiene_equipos`
--

CREATE TABLE `tiene_equipos` (
  `IDEquipo` int(50) NOT NULL,
  `IDReservas` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IDUsuario` int(10) NOT NULL,
  `Nombre` varchar(30) DEFAULT NULL,
  `Contraseña` varchar(50) DEFAULT NULL,
  `Email` varchar(30) DEFAULT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Faltas` int(10) DEFAULT NULL,
  `Observaciones` varchar(50) NOT NULL,
  `Fecha_creacion` date DEFAULT NULL,
  `Crador_IDUsuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IDUsuario`, `Nombre`, `Contraseña`, `Email`, `Direccion`, `Faltas`, `Observaciones`, `Fecha_creacion`, `Crador_IDUsuario`) VALUES
(0, 'Jaime Reyes', '0123', 'Azul@gmail', 'Puerto Rico', 0, 'El escarabajo Azul', '0000-00-00', 0),
(1, 'Hall Jhordan', '123', 'Linterna@gmail', 'OA', 0, 'Linterna Verde', '0000-00-00', 0),
(2, 'Petter Parker', '123', 'spider@gmail', 'Brooklyn', 1, 'Spider-man', '0000-00-00', 0);

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
  ADD PRIMARY KEY (`IDEquipo`,`IDReservas`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IDUsuario`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD CONSTRAINT `IDSala_ceilab` FOREIGN KEY (`IDSala_ceilab`) REFERENCES `sala_ceilab` (`IDSalon`);

--
-- Filtros para la tabla `operaciones`
--
ALTER TABLE `operaciones`
  ADD CONSTRAINT `FK_Operaciones_USUARIO` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`),
  ADD CONSTRAINT `FK_Operaciones_equipo` FOREIGN KEY (`IDEquipo`) REFERENCES `equipo` (`IDEquipo`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `FK_reservas_usuario` FOREIGN KEY (`IDUsuario`) REFERENCES `usuario` (`IDUsuario`),
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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
