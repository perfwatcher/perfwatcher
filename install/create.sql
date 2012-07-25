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

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `collectd`
--

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

DROP TABLE IF EXISTS `data`;
CREATE TABLE IF NOT EXISTS `data` (
  `date` datetime NOT NULL,
  `host_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `type_instance` varchar(255) DEFAULT NULL,
  `dataset_id` int(11) NOT NULL,
  `value` double NOT NULL,
  UNIQUE KEY `host_id_3` (`host_id`,`plugin_id`,`plugin_instance`,`type_id`,`type_instance`,`dataset_id`),
  KEY `host_id` (`host_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `plugin_instance` (`plugin_instance`),
  KEY `type_id` (`type_id`),
  KEY `type_instance` (`type_instance`),
  KEY `dataset_id` (`dataset_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `dataset`
--

DROP TABLE IF EXISTS `dataset`;
CREATE TABLE IF NOT EXISTS `dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('COUNTER','GAUGE','DERIVE','ABSOLUTE') NOT NULL,
  `min` double NOT NULL,
  `max` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`type_id`),
  UNIQUE KEY `type_id` (`type_id`,`name`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=285 ;

-- --------------------------------------------------------

--
-- Table structure for table `host`
--

DROP TABLE IF EXISTS `host`;
CREATE TABLE IF NOT EXISTS `host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9254 ;

-- --------------------------------------------------------

--
-- Table structure for table `plugin`
--

DROP TABLE IF EXISTS `plugin`;
CREATE TABLE IF NOT EXISTS `plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=81 ;

-- --------------------------------------------------------

--
-- Table structure for table `snap_data`
--

DROP TABLE IF EXISTS `snap_data`;
CREATE TABLE IF NOT EXISTS `snap_data` (
  `date` datetime NOT NULL,
  `host_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `type_instance` varchar(255) DEFAULT NULL,
  `dataset_id` int(11) NOT NULL,
  `value` double NOT NULL,
  UNIQUE KEY `host_id_3` (`host_id`,`plugin_id`,`plugin_instance`,`type_id`,`type_instance`,`dataset_id`),
  KEY `host_id` (`host_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `type_id` (`type_id`),
  KEY `dataset_id` (`dataset_id`),
  KEY `dataset_id_2` (`dataset_id`),
  KEY `dataset_id_3` (`dataset_id`),
  KEY `dataset_id_4` (`dataset_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
CREATE TABLE IF NOT EXISTS `type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`name`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=172 ;

-- --------------------------------------------------------

--
-- Structure for view `data_view`
--
DROP TABLE IF EXISTS `data_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `data_view` AS select `data`.`date` AS `date`,`host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`data`.`type_instance` AS `type_instance`,`dataset`.`name` AS `dataset_name`,`data`.`value` AS `value` from ((((`data` join `host`) join `plugin`) join `type`) join `dataset`) where ((`host`.`id` = `data`.`host_id`) and (`plugin`.`id` = `data`.`plugin_id`) and (`type`.`id` = `data`.`type_id`) and (`data`.`dataset_id` = `dataset`.`id`));

-- --------------------------------------------------------

--
-- Structure for view `eqdg3pzsqldisco-mysql`
--
DROP TABLE IF EXISTS `eqdg3pzsqldisco-mysql`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `eqdg3pzsqldisco-mysql` AS select `data_view`.`date` AS `date`,`data_view`.`host` AS `host`,`data_view`.`plugin` AS `plugin`,`data_view`.`plugin_instance` AS `plugin_instance`,`data_view`.`type` AS `type`,`data_view`.`type_instance` AS `type_instance`,`data_view`.`dataset_name` AS `dataset_name`,`data_view`.`value` AS `value` from `data_view` where ((`data_view`.`host` like 'eqdg3pzsqldisco-mysql%') and (`data_view`.`type` like 'queue_length'));

-- --------------------------------------------------------

--
-- Structure for view `plugin_view`
--
DROP TABLE IF EXISTS `plugin_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `plugin_view` AS select `host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`data`.`type_instance` AS `type_instance` from (((`data` join `host`) join `plugin`) join `type`) where ((`host`.`id` = `data`.`host_id`) and (`plugin`.`id` = `data`.`plugin_id`) and (`type`.`id` = `data`.`type_id`));

-- --------------------------------------------------------

--
-- Structure for view `snap_data_view`
--
DROP TABLE IF EXISTS `snap_data_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `snap_data_view` AS select `snap_data`.`date` AS `date`,`host`.`name` AS `host`,`plugin`.`name` AS `plugin`,`snap_data`.`plugin_instance` AS `plugin_instance`,`type`.`name` AS `type`,`snap_data`.`type_instance` AS `type_instance`,`dataset`.`name` AS `dataset_name`,`snap_data`.`value` AS `value` from ((((`snap_data` join `host`) join `plugin`) join `type`) join `dataset`) where ((`host`.`id` = `snap_data`.`host_id`) and (`plugin`.`id` = `snap_data`.`plugin_id`) and (`type`.`id` = `snap_data`.`type_id`) and (`snap_data`.`dataset_id` = `dataset`.`id`));

-- --------------------------------------------------------

--
-- Structure for view `types_db`
--
DROP TABLE IF EXISTS `types_db`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `types_db` AS select `type`.`name` AS `type`,`dataset`.`name` AS `ds` from (`type` join `dataset`) where (`dataset`.`type_id` = `type`.`id`) order by `type`.`name`,`dataset`.`id`;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `jsTree` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `jsTree`;

--
-- Table structure for table `tree`
--

DROP TABLE IF EXISTS `tree`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
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
