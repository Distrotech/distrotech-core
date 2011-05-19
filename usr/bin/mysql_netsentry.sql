-- MySQL dump 10.11
--
-- Host: localhost    Database: 
-- ------------------------------------------------------
-- Server version	5.0.33

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
-- Current Database: `horde`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `horde` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `horde`;

--
-- Table structure for table `horde_categories`
--

DROP TABLE IF EXISTS `horde_categories`;
CREATE TABLE `horde_categories` (
  `category_id` int(11) NOT NULL default '0',
  `group_uid` varchar(255) NOT NULL default '',
  `user_uid` varchar(255) default NULL,
  `category_name` varchar(255) NOT NULL default '',
  `category_data` text,
  `category_serialized` smallint(6) NOT NULL default '0',
  `category_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`category_id`),
  KEY `category_category_name_idx` (`category_name`),
  KEY `category_group_idx` (`group_uid`),
  KEY `category_user_idx` (`user_uid`),
  KEY `category_serialized_idx` (`category_serialized`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Categories';

--
-- Dumping data for table `horde_categories`
--

LOCK TABLES `horde_categories` WRITE;
/*!40000 ALTER TABLE `horde_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `horde_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horde_categories_categories`
--

DROP TABLE IF EXISTS `horde_categories_categories`;
CREATE TABLE `horde_categories_categories` (
  `category_id_parent` int(11) NOT NULL default '0',
  `category_id_child` int(11) NOT NULL default '0',
  PRIMARY KEY  (`category_id_parent`,`category_id_child`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `horde_categories_categories`
--

LOCK TABLES `horde_categories_categories` WRITE;
/*!40000 ALTER TABLE `horde_categories_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `horde_categories_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horde_prefs`
--

DROP TABLE IF EXISTS `horde_prefs`;
CREATE TABLE `horde_prefs` (
  `pref_uid` varchar(255) NOT NULL default '',
  `pref_scope` varchar(16) NOT NULL default '',
  `pref_name` varchar(32) NOT NULL default '',
  `pref_value` text,
  PRIMARY KEY  (`pref_uid`,`pref_scope`,`pref_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Horde Preferances';

--
-- Dumping data for table `horde_prefs`
--

LOCK TABLES `horde_prefs` WRITE;
/*!40000 ALTER TABLE `horde_prefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `horde_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horde_users`
--

DROP TABLE IF EXISTS `horde_users`;
CREATE TABLE `horde_users` (
  `user_uid` varchar(255) NOT NULL default '',
  `user_pass` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`user_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Horde Users';

--
-- Dumping data for table `horde_users`
--

LOCK TABLES `horde_users` WRITE;
/*!40000 ALTER TABLE `horde_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `horde_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kronolith_events`
--

DROP TABLE IF EXISTS `kronolith_events`;
CREATE TABLE `kronolith_events` (
  `event_id` bigint(20) NOT NULL default '0',
  `calendar_id` varchar(255) NOT NULL default '',
  `event_description` text,
  `event_location` text,
  `event_keywords` text,
  `event_exceptions` text,
  `event_title` varchar(80) default NULL,
  `event_category` varchar(80) default NULL,
  `event_recurtype` varchar(11) default '0',
  `event_recurinterval` varchar(11) default NULL,
  `event_recurdays` varchar(11) default NULL,
  `event_recurenddate` datetime default NULL,
  `event_start` datetime default NULL,
  `event_end` datetime default NULL,
  `event_alarm` int(11) default '0',
  `event_modified` int(11) NOT NULL default '0',
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kronolith_events`
--

LOCK TABLES `kronolith_events` WRITE;
/*!40000 ALTER TABLE `kronolith_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `kronolith_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mnemo_memos`
--

DROP TABLE IF EXISTS `mnemo_memos`;
CREATE TABLE `mnemo_memos` (
  `memo_owner` varchar(255) NOT NULL default '',
  `memo_id` int(11) NOT NULL default '0',
  `memo_desc` varchar(64) NOT NULL default '',
  `memo_body` text,
  `memo_category` int(11) NOT NULL default '0',
  `memo_private` smallint(6) NOT NULL default '1',
  `memo_modified` int(11) NOT NULL default '0',
  PRIMARY KEY  (`memo_owner`,`memo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mnemo_memos`
--

LOCK TABLES `mnemo_memos` WRITE;
/*!40000 ALTER TABLE `mnemo_memos` DISABLE KEYS */;
/*!40000 ALTER TABLE `mnemo_memos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nag_tasks`
--

DROP TABLE IF EXISTS `nag_tasks`;
CREATE TABLE `nag_tasks` (
  `task_owner` varchar(255) NOT NULL default '',
  `task_id` int(11) NOT NULL default '0',
  `task_name` varchar(64) NOT NULL default '',
  `task_desc` text,
  `task_modified` int(11) NOT NULL default '0',
  `task_due` int(11) default NULL,
  `task_priority` int(11) NOT NULL default '0',
  `task_category` int(11) NOT NULL default '0',
  `task_completed` smallint(6) NOT NULL default '0',
  `task_private` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`task_owner`,`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nag_tasks`
--

LOCK TABLES `nag_tasks` WRITE;
/*!40000 ALTER TABLE `nag_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `nag_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turba_objects`
--

DROP TABLE IF EXISTS `turba_objects`;
CREATE TABLE `turba_objects` (
  `object_id` varchar(32) NOT NULL default '',
  `owner_id` varchar(255) NOT NULL default '',
  `object_name` varchar(255) default NULL,
  `object_alias` varchar(32) default NULL,
  `object_email` varchar(255) default NULL,
  `object_homeAddress` varchar(255) default NULL,
  `object_workAddress` varchar(255) default NULL,
  `object_homePhone` varchar(25) default NULL,
  `object_workPhone` varchar(25) default NULL,
  `object_cellPhone` varchar(25) default NULL,
  `object_fax` varchar(25) default NULL,
  `object_title` varchar(32) default NULL,
  `object_company` varchar(32) default NULL,
  `object_notes` text,
  PRIMARY KEY  (`object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `turba_objects`
--

LOCK TABLES `turba_objects` WRITE;
/*!40000 ALTER TABLE `turba_objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `turba_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `mysql`
--
-- INSERT INTO `columns_priv` VALUES ('localhost','mysql','control','tables_priv','Host','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','tables_priv','Db','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','tables_priv','User','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','tables_priv','Table_name','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','tables_priv','Table_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','tables_priv','Column_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Host','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','User','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Select_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Insert_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Update_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Delete_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Create_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Drop_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Reload_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Shutdown_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Process_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','File_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Grant_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','References_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Index_priv','2007-02-28 11:56:35','Select'),
-- ('localhost','mysql','control','user','Alter_priv','2007-02-28 11:56:35','Select');
-- INSERT INTO `db` VALUES 
-- ('%','networksentry_log','logview','Y','Y','Y','Y','N','N','N','N','N','N','N','N','N','N','N','N','Y'),
-- ('%','horde','horde','Y','Y','Y','Y','N','N','N','N','N','N','N','N','N','N','N','N','Y'),
-- ('localhost','phpBB2','phpBB2','Y','Y','Y','Y','Y','Y','N','Y','Y','Y','N','N','N','N','Y','Y','Y'),
-- ('localhost','phpmyadmin','control','Y','Y','Y','Y','N','N','N','N','N','N','N','N','N','N','N','N','Y');
-- INSERT INTO `tables_priv` VALUES 
-- ('localhost','mysql','control','db','admin@localhost','2007-02-28 11:56:35','Select',''),
-- ('localhost','mysql','control','tables_priv','admin@localhost','2007-02-28 11:56:35','','Select'),
-- ('localhost','mysql','control','user','admin@localhost','2007-02-28 11:56:35','','Select');
-- INSERT INTO `user` VALUES 
-- ('%','admin','43e9a4ab75570f5b','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','','','','',0,0,0,0),
-- ('localhost','horde','43e9a4ab75570f5b','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),
-- ('localhost','phpBB2','0f0dbe0300330c1b','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),
-- ('localhost','control','565216cf44376aec','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),('%','logview','','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),('%','radius','','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),('%','horde','','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),('%','asterisk','','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0),('%','SugarCRM','','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','Y','Y','N','N','N','N','N','N','N','N','','','','',0,0,0,0);

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `networksentry_log` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `networksentry_log`;

--
-- Table structure for table `mail_from`
--

DROP TABLE IF EXISTS `mail_from`;
CREATE TABLE `mail_from` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `message_id` varchar(32) NOT NULL default '',
  `addr` varchar(128) NOT NULL default '',
  `msg_size` bigint(20) unsigned default NULL,
  `rcpt_count` int(10) unsigned default '0',
  `message_tag` varchar(255) NOT NULL default '',
  `raddr` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mail_from`
--

LOCK TABLES `mail_from` WRITE;
/*!40000 ALTER TABLE `mail_from` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_from` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_to`
--

DROP TABLE IF EXISTS `mail_to`;
CREATE TABLE `mail_to` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `time` datetime default NULL,
  `message_id` varchar(32) NOT NULL default '',
  `addr` varchar(128) NOT NULL default '',
  `delay` time default NULL,
  `mailer` varchar(32) NOT NULL default '',
  `stat` varchar(255) NOT NULL default '',
  `xdelay` time default NULL,
  `caddr` varchar(128) NOT NULL default '',
  `raddr` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mail_to`
--

LOCK TABLES `mail_to` WRITE;
/*!40000 ALTER TABLE `mail_to` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_to` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packet_filter`
--

DROP TABLE IF EXISTS `packet_filter`;
CREATE TABLE `packet_filter` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `raw_mac` varchar(80) default NULL,
  `oob_time_sec` int(10) unsigned default NULL,
  `oob_time_usec` int(10) unsigned default NULL,
  `oob_prefix` varchar(32) default NULL,
  `oob_mark` int(10) unsigned default NULL,
  `oob_in` varchar(32) default NULL,
  `oob_out` varchar(32) default NULL,
  `ip_saddr` int(10) unsigned default NULL,
  `ip_daddr` int(10) unsigned default NULL,
  `ip_protocol` tinyint(3) unsigned default NULL,
  `ip_tos` tinyint(3) unsigned default NULL,
  `ip_ttl` tinyint(3) unsigned default NULL,
  `ip_totlen` smallint(5) unsigned default NULL,
  `ip_ihl` tinyint(3) unsigned default NULL,
  `ip_csum` smallint(5) unsigned default NULL,
  `ip_id` smallint(5) unsigned default NULL,
  `ip_fragoff` smallint(5) unsigned default NULL,
  `tcp_sport` smallint(5) unsigned default NULL,
  `tcp_dport` smallint(5) unsigned default NULL,
  `tcp_seq` int(10) unsigned default NULL,
  `tcp_ackseq` int(10) unsigned default NULL,
  `tcp_window` smallint(5) unsigned default NULL,
  `tcp_urg` tinyint(4) default NULL,
  `tcp_urgp` smallint(5) unsigned default NULL,
  `tcp_ack` tinyint(4) default NULL,
  `tcp_psh` tinyint(4) default NULL,
  `tcp_rst` tinyint(4) default NULL,
  `tcp_syn` tinyint(4) default NULL,
  `tcp_fin` tinyint(4) default NULL,
  `udp_sport` smallint(5) unsigned default NULL,
  `udp_dport` smallint(5) unsigned default NULL,
  `udp_len` smallint(5) unsigned default NULL,
  `icmp_type` tinyint(3) unsigned default NULL,
  `icmp_code` tinyint(3) unsigned default NULL,
  `icmp_echoid` smallint(5) unsigned default NULL,
  `icmp_echoseq` smallint(5) unsigned default NULL,
  `icmp_gateway` int(10) unsigned default NULL,
  `icmp_fragmtu` smallint(5) unsigned default NULL,
  `pwsniff_user` varchar(30) default NULL,
  `pwsniff_pass` varchar(30) default NULL,
  `ahesp_spi` int(10) unsigned default NULL,
  `local_time` int(32) unsigned default NULL,
  UNIQUE KEY `id` (`id`),
  KEY `index_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `packet_filter`
--

LOCK TABLES `packet_filter` WRITE;
/*!40000 ALTER TABLE `packet_filter` DISABLE KEYS */;
/*!40000 ALTER TABLE `packet_filter` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-02-28 12:39:11
