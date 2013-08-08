# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: nickpettazzoni.com (MySQL 5.5.22-0ubuntu1)
# Database: jacked
# Generation Time: 2012-09-05 20:16:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table admin_users
# ------------------------------------------------------------

CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `User` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Application
# ------------------------------------------------------------

CREATE TABLE `Application` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `apiKey` varchar(64) NOT NULL DEFAULT '',
  `device` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Blag
# ------------------------------------------------------------

CREATE TABLE `Blag` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `author` varchar(64) NOT NULL DEFAULT '',
  `posted` int(11) NOT NULL,
  `category` varchar(64) NOT NULL DEFAULT '',
  `alive` tinyint(1) DEFAULT '1',
  `title` varchar(255) NOT NULL DEFAULT '',
  `headline` text,
  `content` text NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table BlagCategory
# ------------------------------------------------------------

CREATE TABLE `BlagCategory` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Karma
# ------------------------------------------------------------

CREATE TABLE `Karma` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `target` varchar(64) NOT NULL DEFAULT '',
  `Source` varchar(64) NOT NULL DEFAULT '',
  `weight` int(10) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table Logr
# ------------------------------------------------------------

CREATE TABLE `Logr` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `timestamp` varchar(100) DEFAULT NULL,
  `message` text,
  `file` varchar(100) DEFAULT NULL,
  `line` int(10) DEFAULT NULL,
  `stack_hash` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `id` varchar(64) NOT NULL DEFAULT '',
  `access` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table Source
# ------------------------------------------------------------

CREATE TABLE `Source` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `Application` varchar(64) NOT NULL DEFAULT '',
  `User` varchar(64) DEFAULT NULL,
  `unique` varchar(255) NOT NULL DEFAULT '',
  `bans` tinyint(14) DEFAULT '0',
  `data` text,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table User
# ------------------------------------------------------------

CREATE TABLE `User` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


# Dump of table Curator
# ------------------------------------------------------------

CREATE TABLE `Curator` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `usage` int(11) DEFAULT 0,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table CuratorRelation
# ------------------------------------------------------------

CREATE TABLE `CuratorRelation` (
  `Curator` varchar(64) NOT NULL DEFAULT '',
  `target` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
