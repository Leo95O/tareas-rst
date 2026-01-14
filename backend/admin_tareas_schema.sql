-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: admin_tareas
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.8-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `intentos_acceso`
--

DROP TABLE IF EXISTS `intentos_acceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intentos_acceso` (
  `usuario_hash` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `nivel_bloqueo` int(11) DEFAULT 0,
  `ultimo_intento` datetime DEFAULT NULL,
  `bloqueado_hasta` datetime DEFAULT NULL,
  PRIMARY KEY (`usuario_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `proyecto_estados`
--

DROP TABLE IF EXISTS `proyecto_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyecto_estados` (
  `estado_id` int(11) NOT NULL AUTO_INCREMENT,
  `estado_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_orden` int(11) DEFAULT 0,
  PRIMARY KEY (`estado_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proyectos` (
  `proyecto_id` int(11) NOT NULL AUTO_INCREMENT,
  `proyecto_nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proyecto_descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  `estado_id` int(11) NOT NULL,
  `usuario_creador` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_eliminacion` datetime DEFAULT NULL,
  PRIMARY KEY (`proyecto_id`),
  KEY `sucursal_id` (`sucursal_id`),
  KEY `estado_id` (`estado_id`),
  KEY `usuario_creador` (`usuario_creador`),
  CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`sucursal_id`),
  CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `proyecto_estados` (`estado_id`),
  CONSTRAINT `proyectos_ibfk_3` FOREIGN KEY (`usuario_creador`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `rol_id` int(11) NOT NULL AUTO_INCREMENT,
  `rol_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sucursales`
--

DROP TABLE IF EXISTS `sucursales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursales` (
  `sucursal_id` int(11) NOT NULL AUTO_INCREMENT,
  `sucursal_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sucursal_direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`sucursal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tarea_categorias`
--

DROP TABLE IF EXISTS `tarea_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarea_categorias` (
  `categoria_id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`categoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tarea_estados`
--

DROP TABLE IF EXISTS `tarea_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarea_estados` (
  `estado_id` int(11) NOT NULL AUTO_INCREMENT,
  `estado_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_orden` int(11) DEFAULT 0,
  PRIMARY KEY (`estado_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tarea_prioridades`
--

DROP TABLE IF EXISTS `tarea_prioridades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarea_prioridades` (
  `prioridad_id` int(11) NOT NULL AUTO_INCREMENT,
  `prioridad_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prioridad_valor` int(11) NOT NULL,
  PRIMARY KEY (`prioridad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tareas`
--

DROP TABLE IF EXISTS `tareas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tareas` (
  `tarea_id` int(11) NOT NULL AUTO_INCREMENT,
  `tarea_titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tarea_descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_limite` datetime DEFAULT NULL,
  `prioridad_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `usuario_asignado` int(11) DEFAULT NULL,
  `usuario_creador` int(11) NOT NULL,
  `fecha_eliminacion` datetime DEFAULT NULL,
  PRIMARY KEY (`tarea_id`),
  KEY `prioridad_id` (`prioridad_id`),
  KEY `estado_id` (`estado_id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `usuario_asignado` (`usuario_asignado`),
  KEY `usuario_creador` (`usuario_creador`),
  CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`prioridad_id`) REFERENCES `tarea_prioridades` (`prioridad_id`),
  CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `tarea_estados` (`estado_id`),
  CONSTRAINT `tareas_ibfk_3` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`proyecto_id`),
  CONSTRAINT `tareas_ibfk_4` FOREIGN KEY (`categoria_id`) REFERENCES `tarea_categorias` (`categoria_id`),
  CONSTRAINT `tareas_ibfk_5` FOREIGN KEY (`usuario_asignado`) REFERENCES `usuarios` (`usuario_id`),
  CONSTRAINT `tareas_ibfk_6` FOREIGN KEY (`usuario_creador`) REFERENCES `usuarios` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `usuario_id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_token` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol_id` int(11) NOT NULL,
  `usuario_estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `usuario_correo` (`usuario_correo`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'admin_tareas'
--

--
-- Dumping routines for database 'admin_tareas'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-14 11:19:37
