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
-- Current Database: `collectd`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `collectd` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `collectd`;

--
-- Table structure for table `data`
--

DROP TABLE IF EXISTS `data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `host_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `type_instance` varchar(255) DEFAULT NULL,
  `dataset_id` int(11) NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_id_3` (`host_id`,`plugin_id`,`plugin_instance`,`type_id`,`type_instance`,`dataset_id`),
  KEY `host_id` (`host_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `type_id` (`type_id`),
  KEY `dataset_id` (`dataset_id`)
) ENGINE=MEMORY AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `data_view`
--

DROP TABLE IF EXISTS `data_view`;
/*!50001 DROP VIEW IF EXISTS `data_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `data_view` (
  `date` datetime,
  `host` varchar(255),
  `plugin` varchar(255),
  `plugin_instance` varchar(255),
  `type` varchar(255),
  `type_instance` varchar(255),
  `dataset_name` varchar(255),
  `value` double
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `dataset`
--

DROP TABLE IF EXISTS `dataset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('COUNTER','GAUGE','DERIVE','ABSOLUTE') NOT NULL,
  `min` double NOT NULL,
  `max` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`type_id`),
  KEY `name` (`name`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `dataset_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grouped_type`
--

DROP TABLE IF EXISTS `grouped_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grouped_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `host`
--

DROP TABLE IF EXISTS `host`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `nbcpu` int(11) NOT NULL DEFAULT '0',
  `load` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `host_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `type_instance` varchar(255) DEFAULT NULL,
  `severity` enum('FAILURE','WARNING','OKAY','UNKNOW') NOT NULL,
  `message` mediumtext,
  PRIMARY KEY (`id`,`date`),
  KEY `type_instance` (`type_instance`),
  KEY `plugin_instance` (`plugin_instance`),
  KEY `severity` (`severity`),
  KEY `host_id_2` (`host_id`,`plugin_id`,`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
/*!50100 PARTITION BY HASH (MONTH(`date`))
PARTITIONS 12 */;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `notification_view`
--

DROP TABLE IF EXISTS `notification_view`;
/*!50001 DROP VIEW IF EXISTS `notification_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `notification_view` (
  `date` datetime,
  `hostname` varchar(255),
  `plugin` varchar(255),
  `plugin_instance` varchar(255),
  `type` varchar(255),
  `type_instance` varchar(255),
  `severity` enum('FAILURE','WARNING','OKAY','UNKNOW'),
  `message` mediumtext
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `plugin`
--

DROP TABLE IF EXISTS `plugin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `plugin_view`
--

DROP TABLE IF EXISTS `plugin_view`;
/*!50001 DROP VIEW IF EXISTS `plugin_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `plugin_view` (
  `host` varchar(255),
  `plugin` varchar(255),
  `plugin_instance` varchar(255),
  `type` varchar(255),
  `type_instance` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `snap_data`
--

DROP TABLE IF EXISTS `snap_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snap_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `host_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `type_instance` varchar(255) DEFAULT NULL,
  `dataset_id` int(11) NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_id_3` (`host_id`,`plugin_id`,`plugin_instance`,`type_id`,`type_instance`,`dataset_id`),
  KEY `host_id` (`host_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `type_id` (`type_id`),
  KEY `dataset_id` (`dataset_id`)
) ENGINE=MEMORY AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `snap_data_view`
--

DROP TABLE IF EXISTS `snap_data_view`;
/*!50001 DROP VIEW IF EXISTS `snap_data_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `snap_data_view` (
  `date` datetime,
  `host` varchar(255),
  `plugin` varchar(255),
  `plugin_instance` varchar(255),
  `type` varchar(255),
  `type_instance` varchar(255),
  `dataset_name` varchar(255),
  `value` double
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `types_db`
--

DROP TABLE IF EXISTS `types_db`;
/*!50001 DROP VIEW IF EXISTS `types_db`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `types_db` (
  `type` varchar(255),
  `ds` varchar(255)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

USE `collectd`;

--
-- Final view structure for view `data_view`
--

/*!50001 DROP TABLE IF EXISTS `data_view`*/;
/*!50001 DROP VIEW IF EXISTS `data_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `data_view` AS select `data`.`date` AS `date`,`host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`data`.`type_instance` AS `type_instance`,`dataset`.`name` AS `dataset_name`,`data`.`value` AS `value` from ((((`data` join `host`) join `plugin`) join `type`) join `dataset`) where ((`host`.`id` = `data`.`host_id`) and (`plugin`.`id` = `data`.`plugin_id`) and (`type`.`id` = `data`.`type_id`) and (`data`.`dataset_id` = `dataset`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `notification_view`
--

/*!50001 DROP TABLE IF EXISTS `notification_view`*/;
/*!50001 DROP VIEW IF EXISTS `notification_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `notification_view` AS select `n`.`date` AS `date`,`h`.`name` AS `hostname`,`p`.`name` AS `plugin`,`n`.`plugin_instance` AS `plugin_instance`,`t`.`name` AS `type`,`n`.`type_instance` AS `type_instance`,`n`.`severity` AS `severity`,`n`.`message` AS `message` from (((`notification` `n` join `host` `h`) join `plugin` `p`) join `type` `t`) where ((`n`.`host_id` = `h`.`id`) and (`n`.`plugin_id` = `p`.`id`) and (`n`.`type_id` = `t`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `plugin_view`
--

/*!50001 DROP TABLE IF EXISTS `plugin_view`*/;
/*!50001 DROP VIEW IF EXISTS `plugin_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `plugin_view` AS select `host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`data`.`type_instance` AS `type_instance` from (((`data` join `host`) join `plugin`) join `type`) where ((`host`.`id` = `data`.`host_id`) and (`plugin`.`id` = `data`.`plugin_id`) and (`type`.`id` = `data`.`type_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `snap_data_view`
--

/*!50001 DROP TABLE IF EXISTS `snap_data_view`*/;
/*!50001 DROP VIEW IF EXISTS `snap_data_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `snap_data_view` AS select `snap_data`.`date` AS `date`,`host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`snap_data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`snap_data`.`type_instance` AS `type_instance`,`dataset`.`name` AS `dataset_name`,`snap_data`.`value` AS `value` from ((((`snap_data` join `host`) join `plugin`) join `type`) join `dataset`) where ((`host`.`id` = `snap_data`.`host_id`) and (`plugin`.`id` = `snap_data`.`plugin_id`) and (`type`.`id` = `snap_data`.`type_id`) and (`snap_data`.`dataset_id` = `dataset`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `types_db`
--

/*!50001 DROP TABLE IF EXISTS `types_db`*/;
/*!50001 DROP VIEW IF EXISTS `types_db`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `types_db` AS select `type`.`name` AS `type`,`dataset`.`name` AS `ds` from (`type` join `dataset`) where (`dataset`.`type_id` = `type`.`id`) order by `type`.`name`,`dataset`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;


--
-- Current Database: `jsTree`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `jsTree` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `jsTree`;

--
-- Table structure for table `tree`
--

DROP TABLE IF EXISTS `tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tree` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL,
  `position` bigint(20) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `datas` varchar(8192) NOT NULL DEFAULT 'a:0:{}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`parent_id`),
  KEY `title` (`title`),
  KEY `type` (`type`),
  KEY `id_2` (`id`,`title`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `tree` (`id`, `parent_id`, `position`, `title`, `type`, `datas`) VALUES
(1, 0, 0, NULL, 'drive', 'a:0:{}'),
(2, 1, 0, 'ROOT', 'drive', 'a:0:{}');
