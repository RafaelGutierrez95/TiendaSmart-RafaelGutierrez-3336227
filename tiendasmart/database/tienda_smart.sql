-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-11-2026 a las 20:13:03
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
-- Base de datos: `tienda_smart`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) UNSIGNED NOT NULL,
  `id_cliente` int(11) UNSIGNED NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_cliente`, `fecha_creacion`) VALUES
(1, 3, '2026-09-20 01:20:00'),
(2, 4, '2026-09-20 01:20:00'),
(3, 1, '2026-11-11 06:20:01'),
(24, 5, '2026-11-11 00:39:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) UNSIGNED NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`) VALUES
(1, 'Víveres', 'Granos, aceites y productos de despensa básicos'),
(2, 'Lácteos', 'Leche, quesos y derivados'),
(3, 'Abarrotes', 'Huevos, panela y productos generales'),
(4, 'Aseo', 'Productos de limpieza para el hogar'),
(5, 'Snacks', 'Papas, galletas y dulces');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_carrito`
--

CREATE TABLE `detalle_carrito` (
  `id_detalle_c` int(11) UNSIGNED NOT NULL,
  `id_carrito` int(11) UNSIGNED NOT NULL,
  `id_producto` int(11) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_carrito`
--

INSERT INTO `detalle_carrito` (`id_detalle_c`, `id_carrito`, `id_producto`, `cantidad`) VALUES
(1, 1, 1, 2),
(3, 2, 5, 1),
(4, 3, 4, 1),
(5, 24, 2, 2),
(6, 24, 1, 2),
(7, 24, 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) UNSIGNED NOT NULL,
  `id_pedido` int(11) UNSIGNED NOT NULL,
  `id_producto` int(11) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 2, 3500.00),
(2, 1, 2, 1, 4200.00),
(3, 2, 3, 1, 14000.00),
(4, 3, 4, 1, 8500.00),
(5, 3, 5, 1, 12000.00),
(6, 3, 3, 1, 14000.00),
(7, 4, 4, 1, 8500.00),
(8, 5, 5, 2, 12000.00),
(9, 6, 4, 1, 8500.00),
(10, 7, 4, 1, 8500.00),
(11, 7, 3, 1, 14000.00),
(12, 7, 2, 1, 4200.00),
(13, 7, 1, 1, 3500.00),
(14, 7, 5, 1, 12000.00),
(15, 8, 2, 2, 4200.00),
(16, 8, 1, 2, 3500.00),
(17, 8, 3, 1, 14000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` int(11) UNSIGNED NOT NULL,
  `id_pedido` int(11) UNSIGNED NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(12,2) NOT NULL,
  `impuestos` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_factura` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_pedido`, `numero_factura`, `fecha_emision`, `subtotal`, `impuestos`, `total_factura`) VALUES
(1, 1, 'FAC-1001', '2026-09-19 15:30:00', 11200.00, 0.00, 11200.00),
(2, 5, 'FAC-1002', '2026-09-19 22:50:00', 24000.00, 0.00, 24000.00),
(3, 6, 'TS-0006', '2026-11-11 06:24:12', 8500.00, 0.00, 0.00),
(4, 7, 'TS-0007', '2026-11-11 06:33:03', 42200.00, 0.00, 0.00),
(5, 8, 'TS-0008', '2026-11-11 00:41:42', 29400.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) UNSIGNED NOT NULL,
  `id_cliente` int(11) UNSIGNED NOT NULL,
  `id_domiciliario` int(11) UNSIGNED DEFAULT NULL,
  `fecha_pedido` datetime NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `estado` varchar(30) NOT NULL,
  `direccion_entrega` varchar(200) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `id_domiciliario`, `fecha_pedido`, `total`, `estado`, `direccion_entrega`, `metodo_pago`) VALUES
(1, 3, 2, '2026-09-19 10:00:00', 11200.00, 'Entregado', 'Calle 15 # 7-10, Barrio San Carlos', 'Pago Contraentrega'),
(2, 4, 2, '2026-09-19 11:30:00', 14000.00, 'En camino', 'Cra 8 # 13-50, Barrio Pontevedra', 'Pago Contraentrega'),
(3, 5, 2, '2026-09-19 14:00:00', 34500.00, 'En camino', 'Calle 16 # 6-30, Barrio Los Cortijos', 'Pago Contraentrega'),
(4, 3, NULL, '2026-09-19 15:45:00', 8500.00, 'Pendiente', 'Calle 15 # 7-10, Barrio San Carlos', 'Pago Contraentrega'),
(5, 4, 2, '2026-09-19 17:20:00', 24000.00, 'Entregado', 'Cra 8 # 13-50, Barrio Pontevedra', 'Pago Contraentrega'),
(6, 1, NULL, '2026-11-11 01:24:12', 9000.00, 'Confirmado', 'Confirmado', 'Pago Contraentrega'),
(7, 1, NULL, '2026-11-11 01:33:03', 42700.00, 'Confirmado', 'Confirmado', 'Pago Contraentrega'),
(8, 5, NULL, '2026-11-10 19:41:42', 29900.00, 'Confirmado', 'Confirmado', 'Pago Contraentrega');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) UNSIGNED NOT NULL,
  `id_categoria` int(11) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `stock_minimo` int(11) NOT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_categoria`, `codigo`, `nombre`, `precio`, `stock_actual`, `stock_minimo`, `proveedor`, `estado`) VALUES
(1, 1, 'PROD-001', 'Arroz Diana x 1kg', 3500.00, 47, 10, 'Distribuidora Diana', 'Disponible'),
(2, 2, 'PROD-002', 'Leche Colanta x 1L', 4200.00, 27, 5, 'Colanta', 'Disponible'),
(3, 3, 'PROD-003', 'Huevos AA x 30', 14000.00, 18, 5, 'Granja Avícola', 'Disponible'),
(4, 1, 'PROD-004', 'Aceite Gourmet x 1L', 8500.00, 13, 3, 'Gourmet S.A.', 'Disponible'),
(5, 4, 'PROD-005', 'Detergente Ariel x 1kg', 12000.00, 24, 5, 'Procter & Gamble', 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) UNSIGNED NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Tendero', 'Administrador del sistema y gestión de inventario'),
(2, 'Domiciliario', 'Encargado de la entrega de pedidos'),
(3, 'Cliente', 'Usuario final que realiza compras');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) UNSIGNED NOT NULL,
  `id_rol` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `token_recuperacion` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `correo`, `password`, `telefono`, `direccion`, `estado`, `token_recuperacion`) VALUES
(1, 1, 'Rafa Gutierrez', 'admin@tiendasmart.com', '$2y$10$Xlvw2UIZvDhq9uzsqLxdmOtNB6mKoJAZiqBID9DvmQJWL5u1F0DPK', '3000000001', 'Cra 6 # 14-20, Barrio Novalito', 'Activo', '3SCBPKJSW3LID1T7'),
(2, 2, 'Carlos Domicilios', 'carlos@tiendasmart.com', 'hash123', '3000000002', 'N/A', 'Activo', NULL),
(3, 3, 'Maria Lopez', 'maria@gmail.com', 'hash123', '3000000003', 'Calle 15 # 7-10, Barrio San Carlos', 'Activo', NULL),
(4, 3, 'Juan Perez', 'juan@gmail.com', 'hash123', '3000000004', 'Cra 8 # 13-50, Barrio Pontevedra', 'Activo', NULL),
(5, 3, 'Ana Gomez', 'ana@gmail.com', '$2y$10$P4SjPgJykhhBlURH5BZdse9rfkERZz41JEur6HLqsFokcn9lBwdI6', '3000000005', 'Calle 16 # 6-30, Barrio Los Cortijos', 'Activo', 'IYX0BVJR4GAK838J'),
(6, 3, 'Luis Perez', 'luis@gmail.com', 'hash123', '3201234567', 'Calle 44 # 23-11, Barrio San Martin', 'Activo', NULL),
(7, 3, 'Carmen Diaz', 'carmen@gmail.com', 'hash123', '3000000007', 'Cra 12 # 8-22, Barrio La Nevada', 'Activo', NULL),
(8, 3, 'Jorge Oñate', 'jorge@gmail.com', 'hash123', '3000000008', 'Calle 22 # 15-10, Barrio Primero de Mayo', 'Activo', NULL),
(9, 2, 'Pedro Domicilios', 'pedro@tiendasmart.com', 'hash123', '3000000009', 'N/A', 'Activo', NULL),
(10, 3, 'Sofia Lopez', 'sofia@gmail.com', 'hash123', '3000000010', 'Cra 9 # 11-40, Barrio Guatapuri', 'Activo', NULL),
(11, 3, 'José Lopéz', 'prueba@tiendasmart.com', '$2y$10$4CYOPmnM.qet7R9l9F7dce3w1uy90tpFSU/IytYe7ifUZCsFElCO6', '3002002002', 'Valledupar', 'Activo', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  ADD PRIMARY KEY (`id_detalle_c`),
  ADD KEY `id_carrito` (`id_carrito`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_domiciliario` (`id_domiciliario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  MODIFY `id_detalle_c` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_factura` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_carrito`
--
ALTER TABLE `detalle_carrito`
  ADD CONSTRAINT `detalle_carrito_ibfk_1` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_carrito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_domiciliario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
