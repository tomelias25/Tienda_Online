-- =====================================================================
-- tienda_online.sql  (versión 2 - rediseño PPA)
-- Compatible con MariaDB 10.4 / phpMyAdmin (XAMPP)
-- Mejoras frente al volcado original:
--   * usuarios: email UNIQUE, rol, password con hash bcrypt NOT NULL,
--     bloqueo por intentos fallidos
--   * productos: stock y activo
--   * pedidos: estado, total, direccion_envio, metodo_pago, id_user NOT NULL
--   * detalle_pedidos: precio_unitario histórico, llaves foráneas NOT NULL
-- Contraseña de PRUEBA de todos los usuarios: Tienda2026*
-- (cámbiela en producción)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS `tienda_online` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tienda_online`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `detalle_pedidos`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuarios`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(50) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `uq_nombre_categoria` (`nombre_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `rol` enum('cliente','administrador') NOT NULL DEFAULT 'cliente',
  `intentos_fallidos` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_producto` varchar(80) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_categoria` int(11) NOT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  CONSTRAINT `chk_precio` CHECK (`precio` >= 0),
  CONSTRAINT `chk_stock` CHECK (`stock` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_pedido` date NOT NULL,
  `id_user` int(11) NOT NULL,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `direccion_envio` varchar(150) DEFAULT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta','contraentrega') NOT NULL DEFAULT 'contraentrega',
  PRIMARY KEY (`id_pedido`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `usuarios` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  CONSTRAINT `chk_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`) VALUES
(1,'Tecnología'),(2,'Hogar'),(3,'Ropa'),(4,'Deportes'),(5,'Juguetes'),
(6,'Belleza'),(7,'Alimentos'),(8,'Papelería'),(9,'Mascotas'),(10,'Muebles');

INSERT INTO `usuarios` (`id_user`,`nombre`,`email`,`password`,`ciudad`,`rol`) VALUES
(1,'Laura Gómez','laura.gomez@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Cúcuta','administrador'),
(2,'Juan Pérez','juan.perez@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Bogotá','cliente'),
(3,'María Torres','maria.torres@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Medellín','cliente'),
(4,'Carlos Ruiz','carlos.ruiz@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Cali','cliente'),
(5,'Ana Rodríguez','ana.rodriguez@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Cúcuta','cliente'),
(6,'Diego Martínez','diego.martinez@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Bucaramanga','cliente'),
(7,'Sofía Castro','sofia.castro@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Cúcuta','cliente'),
(8,'Andrés López','andres.lopez@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Barranquilla','cliente'),
(9,'Valentina Díaz','valentina.diaz@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Pasto','cliente'),
(10,'Felipe Herrera','felipe.herrera@mail.com','$2y$10$nnnX4p0EtHcliUe1YnxUiuTBtkn9KKEn0AYdNKtNiP8PWCM/64EiC','Cúcuta','cliente');

INSERT INTO `productos` (`id_producto`,`nombre_producto`,`precio`,`stock`,`id_categoria`) VALUES
(1,'Audífonos Bluetooth',89000.00,40,1),
(2,'Licuadora',120000.00,25,2),
(3,'Camiseta deportiva',45000.00,80,3),
(4,'Balón de fútbol',60000.00,35,4),
(5,'Muñeca articulada',35000.00,50,5),
(6,'Crema facial',28000.00,60,6),
(7,'Paquete de arroz',8000.00,200,7),
(8,'Cuaderno universitario',6000.00,150,8),
(9,'Correa para perro',22000.00,45,9),
(10,'Silla de oficina',250000.00,12,10);

INSERT INTO `pedidos` (`id_pedido`,`fecha_pedido`,`id_user`,`estado`,`total`,`direccion_envio`,`metodo_pago`) VALUES
(1,'2026-09-01',1,'entregado',178000.00,'Calle 10 # 5-20, Cúcuta','tarjeta'),
(2,'2026-09-02',2,'entregado',120000.00,'Carrera 7 # 45-10, Bogotá','transferencia'),
(3,'2026-09-03',3,'entregado',135000.00,'Calle 50 # 30-15, Medellín','contraentrega'),
(4,'2026-09-04',4,'enviado',60000.00,'Avenida 6N # 20-30, Cali','tarjeta'),
(5,'2026-09-05',5,'entregado',70000.00,'Avenida 5 # 12-40, Cúcuta','efectivo'),
(6,'2026-09-06',6,'pagado',28000.00,'Carrera 27 # 36-12, Bucaramanga','transferencia'),
(7,'2026-09-07',7,'entregado',40000.00,'Calle 12 # 3-55, Cúcuta','contraentrega'),
(8,'2026-09-08',8,'pendiente',24000.00,'Calle 72 # 50-18, Barranquilla','efectivo'),
(9,'2026-09-09',9,'entregado',22000.00,'Carrera 25 # 17-60, Pasto','tarjeta'),
(10,'2026-09-10',10,'enviado',250000.00,'Avenida 0 # 10-25, Cúcuta','transferencia');

INSERT INTO `detalle_pedidos` (`id_detalle`,`id_pedido`,`id_producto`,`cantidad`,`precio_unitario`) VALUES
(1,1,1,2,89000.00),(2,2,2,1,120000.00),(3,3,3,3,45000.00),(4,4,4,1,60000.00),
(5,5,5,2,35000.00),(6,6,6,1,28000.00),(7,7,7,5,8000.00),(8,8,8,4,6000.00),
(9,9,9,1,22000.00),(10,10,10,1,250000.00);
