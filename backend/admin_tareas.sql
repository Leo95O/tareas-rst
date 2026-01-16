CREATE DATABASE  IF NOT EXISTS `admin_tareas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `admin_tareas`;
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
-- Dumping data for table `intentos_acceso`
--

LOCK TABLES `intentos_acceso` WRITE;
/*!40000 ALTER TABLE `intentos_acceso` DISABLE KEYS */;
/*!40000 ALTER TABLE `intentos_acceso` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `proyecto_estados`
--

LOCK TABLES `proyecto_estados` WRITE;
/*!40000 ALTER TABLE `proyecto_estados` DISABLE KEYS */;
INSERT INTO `proyecto_estados` VALUES (1,'Activo',1),(2,'Pausado',2),(3,'Finalizado',3),(4,'Cancelado',4);
/*!40000 ALTER TABLE `proyecto_estados` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `proyectos`
--

LOCK TABLES `proyectos` WRITE;
/*!40000 ALTER TABLE `proyectos` DISABLE KEYS */;
INSERT INTO `proyectos` VALUES (1,'Sistema ERP','Desarrollo del sistema integral',1,1,1,'2026-01-05',NULL,'2026-01-05 22:08:20',NULL),(2,'Microservicio','Agregar microservices',1,1,1,'2025-12-17','2025-11-11','2026-01-05 22:50:11',NULL),(3,'Optimización de BD','Mejoras para el rendimiento de la bd',1,1,1,'2026-01-12','2026-01-17','2026-01-13 10:26:52',NULL);
/*!40000 ALTER TABLE `proyectos` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMIN'),(2,'PROJECT_MANAGER'),(3,'USER');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sucursal_estados`
--

DROP TABLE IF EXISTS `sucursal_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursal_estados` (
  `estado_id` int(11) NOT NULL,
  `estado_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sucursal_estados`
--

LOCK TABLES `sucursal_estados` WRITE;
/*!40000 ALTER TABLE `sucursal_estados` DISABLE KEYS */;
INSERT INTO `sucursal_estados` VALUES (1,'ACTIVO'),(2,'INACTIVO');
/*!40000 ALTER TABLE `sucursal_estados` ENABLE KEYS */;
UNLOCK TABLES;

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
  `sucursal_estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`sucursal_id`),
  KEY `idx_sucursal_estado` (`sucursal_estado`),
  CONSTRAINT `fk_sucursales_estado` FOREIGN KEY (`sucursal_estado`) REFERENCES `sucursal_estados` (`estado_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sucursales`
--

LOCK TABLES `sucursales` WRITE;
/*!40000 ALTER TABLE `sucursales` DISABLE KEYS */;
INSERT INTO `sucursales` VALUES (1,'Sucursal Central','Av. Principal #123',1),(2,'Sucursal Norte','Calle 45 Norte',1),(3,'Sucursal Cerrada','Local 5 - En remodelación',2);
/*!40000 ALTER TABLE `sucursales` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tarea_categorias`
--

LOCK TABLES `tarea_categorias` WRITE;
/*!40000 ALTER TABLE `tarea_categorias` DISABLE KEYS */;
INSERT INTO `tarea_categorias` VALUES (1,'Desarrollo'),(2,'Diseño'),(3,'Infraestructura'),(4,'Marketing'),(5,'Ventas');
/*!40000 ALTER TABLE `tarea_categorias` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tarea_estados`
--

LOCK TABLES `tarea_estados` WRITE;
/*!40000 ALTER TABLE `tarea_estados` DISABLE KEYS */;
INSERT INTO `tarea_estados` VALUES (1,'Pendiente',1),(2,'En Progreso',2),(3,'En Revisión',3),(4,'Completada',4);
/*!40000 ALTER TABLE `tarea_estados` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `tarea_prioridades`
--

LOCK TABLES `tarea_prioridades` WRITE;
/*!40000 ALTER TABLE `tarea_prioridades` DISABLE KEYS */;
INSERT INTO `tarea_prioridades` VALUES (1,'Baja',1),(2,'Media',2),(3,'Alta',3),(4,'Crítica',4);
/*!40000 ALTER TABLE `tarea_prioridades` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tareas`
--

LOCK TABLES `tareas` WRITE;
/*!40000 ALTER TABLE `tareas` DISABLE KEYS */;
INSERT INTO `tareas` VALUES (1,'Configurar Servidor','Instalar Linux y Docker','2024-12-31 23:59:59',4,1,1,3,3,2,'2026-01-05 22:49:01'),(2,'Hola','hpña ajaj','2026-01-05 23:17:38',3,4,1,NULL,2,1,NULL),(3,'hola','Implementa log en la conexión de impresoras antes del la inicialización ','2026-01-17 10:25:43',4,1,2,NULL,3,1,NULL),(4,'sssssss','sssssssssssssss','2026-01-23 09:52:42',4,2,3,NULL,NULL,1,NULL),(5,'hola','lknknknpkn','2026-01-23 11:52:53',4,1,2,NULL,4,1,NULL);
/*!40000 ALTER TABLE `tareas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_estados`
--

DROP TABLE IF EXISTS `usuario_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_estados` (
  `estado_id` int(11) NOT NULL,
  `estado_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_estados`
--

LOCK TABLES `usuario_estados` WRITE;
/*!40000 ALTER TABLE `usuario_estados` DISABLE KEYS */;
INSERT INTO `usuario_estados` VALUES (1,'ACTIVO','Usuario con acceso total al sistema'),(2,'INACTIVO','Usuario deshabilitado o eliminado lógicamente (Soft Delete)');
/*!40000 ALTER TABLE `usuario_estados` ENABLE KEYS */;
UNLOCK TABLES;

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
  `usuario_estado` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `usuario_correo` (`usuario_correo`),
  KEY `rol_id` (`rol_id`),
  KEY `fk_usuarios_estado` (`usuario_estado`),
  CONSTRAINT `fk_usuarios_estado` FOREIGN KEY (`usuario_estado`) REFERENCES `usuario_estados` (`estado_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Super Admin','admin@test.com','$2y$12$.Y5cUcilrbXvIkdKGsphyeicTX047Pe2qLqUUZCztjX0PMGSbE7Le','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3Njg1Njg3MzEsImV4cCI6MTc2ODY1NTEzMSwic3ViIjoxLCJkYXRhIjp7Im5vbWJyZSI6IlN1cGVyIEFkbWluIiwiY29ycmVvIjoiYWRtaW5AdGVzdC5jb20iLCJyb2wiOjF9fQ.Jf_88UF1h6BEjf-6-1qY7fi9nNMABIvaIMja8NX6lLw',1,1,'2026-01-05 22:08:20'),(2,'Gerente Proyecto','manager@test.com','$2y$12$.Y5cUcilrbXvIkdKGsphyeicTX047Pe2qLqUUZCztjX0PMGSbE7Le','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjcmVhY2lvbiI6MTc2NzY3MTg4MCwiZXhwaXJhY2lvbiI6MTc2Nzc1ODI4MCwiZGF0YSI6eyJpZCI6Miwibm9tYnJlIjoiR2VyZW50ZSBQcm95ZWN0byIsImNvcnJlbyI6Im1hbmFnZXJAdGVzdC5jb20iLCJyb2wiOjJ9fQ.a6gBpyfyw01HH-ZXZYxHchFQHDvOBBZC8xXZJiKf4pk',2,1,'2026-01-05 22:08:20'),(3,'Empleado Dev','dev@test.com','$2y$12$.Y5cUcilrbXvIkdKGsphyeicTX047Pe2qLqUUZCztjX0PMGSbE7Le','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjcmVhY2lvbiI6MTc2ODQ4NjI3OSwiZXhwaXJhY2lvbiI6MTc2ODU3MjY3OSwiZGF0YSI6eyJpZCI6Mywibm9tYnJlIjoiRW1wbGVhZG8gRGV2IiwiY29ycmVvIjoiZGV2QHRlc3QuY29tIiwicm9sIjozfX0.Dge9RCK-ZRkxtfosrUHoIpsFIm_EpqfYkvP_juPS_BQ',3,1,'2026-01-05 22:08:20'),(4,'HmuLs8j3U3D0ufQnDhVmR2gqeLF2eMhbThnJJ4VvXKk=','Joel@test.com','$2y$10$Uxu1JQRCv16OavAdf/Slz.5ZxcseMALY.T0/wFweJpk3iBG8oTtDq',NULL,1,1,'2026-01-15 11:43:36');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

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

-- Dump completed on 2026-01-16  9:18:36
