-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: event_store
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `<?php echo $this->_name; ?>`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `<?php echo $this->_name; ?>` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `<?php echo $this->_name; ?>`;

--
-- Table structure for table `aggregates`
--

DROP TABLE IF EXISTS `aggregates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aggregates` (
`id` binary(16) NOT NULL COMMENT 'UUID',
`type` varchar(255) DEFAULT NULL COMMENT 'Nom complet de l''agrégat',
`version` int(10) unsigned DEFAULT NULL COMMENT 'Version du dernier événement associé à cet aggrégat',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Aggrégat concerné par des événements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commands` (
`id` binary(16) NOT NULL COMMENT 'UUID',
`type` varchar(255) DEFAULT NULL COMMENT 'Nom complet de la commande',
`generation_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date de génération de la commande',
`processing_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date d''éxécution de la commande',
`writing_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date d''écriture de la commande en base de données',
`data` blob COMMENT 'Données transportées par la commande (langage agnostique)',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
`id` binary(16) NOT NULL COMMENT 'UUID',
`type` varchar(255) DEFAULT NULL COMMENT 'Nom de l''événement',
`data` blob COMMENT 'Données de l''événement (langage agnostique)',
`version` int(10) unsigned DEFAULT NULL COMMENT 'Version de l''événement (défini au niveau de l''aggrégat) ',
`generation_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date de génération de l''événement',
`writing_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date d''insertion en base de données',
`aggregates_id` binary(16) DEFAULT NULL COMMENT 'Aggrégat relatif à l''événement courant',
`commands_id` binary(16) DEFAULT NULL COMMENT 'Commande ayant entraîné la génération de l''événement',
PRIMARY KEY (`id`),
KEY `fk_events_aggregates_idx` (`aggregates_id`),
KEY `fk_events_commands1_idx` (`commands_id`),
CONSTRAINT `fk_events_aggregates` FOREIGN KEY (`aggregates_id`) REFERENCES `aggregates` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
CONSTRAINT `fk_events_commands1` FOREIGN KEY (`commands_id`) REFERENCES `commands` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Evenements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `snapshots`
--

DROP TABLE IF EXISTS `snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snapshots` (
`id` binary(16) NOT NULL COMMENT 'UUID',
`aggregates_id` binary(16) DEFAULT NULL COMMENT 'UUID de l''aggrégat',
`data` blob COMMENT 'donnée sérialisée de l''état de l''aggrégat (langage agnostique)',
`version` int(10) unsigned DEFAULT NULL COMMENT 'Version du snapshot',
`generation_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date de génération du snapshot',
`writing_date` timestamp(6) NULL DEFAULT NULL COMMENT 'Date d''écriture du snapshot en base de données',
PRIMARY KEY (`id`),
KEY `fk_snapshots_aggregates1_idx` (`aggregates_id`),
CONSTRAINT `fk_snapshots_aggregates1` FOREIGN KEY (`aggregates_id`) REFERENCES `aggregates` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Représente un snapshot de de l''état d''un aggrégat à une version déterminée';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-06-07 17:24:24

CREATE USER IF NOT EXISTS '<?php echo $this->_user; ?>'@'localhost';
SET PASSWORD FOR '<?php echo $this->_user; ?>'@'localhost' = '<?php echo $this->_password; ?>';
GRANT SELECT, UPDATE, DELETE, INSERT, EXECUTE ON <?php echo $this->_name; ?>.* TO '<?php echo $this->_user; ?>'@'localhost';

CREATE USER IF NOT EXISTS '<?php echo $this->_rootUser; ?>'@'localhost';
SET PASSWORD FOR '<?php echo $this->_rootUser; ?>'@'localhost' = '<?php echo $this->_rootPassword; ?>';
GRANT ALL PRIVILEGES ON <?php echo $this->_name; ?>.* TO '<?php echo $this->_rootUser; ?>'@'localhost';