# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
# ************************************************************


# Dump of table admin_users
# ------------------------------------------------------------

CREATE TABLE `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `User` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Application
# ------------------------------------------------------------

CREATE TABLE `Application` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `apiKey` varchar(64) NOT NULL DEFAULT '',
  `device` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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
  PRIMARY KEY (`guid`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table BlagCategory
# ------------------------------------------------------------

CREATE TABLE `BlagCategory` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table Curator
# ------------------------------------------------------------

CREATE TABLE `Curator` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `canonicalName` varchar(255) NOT NULL DEFAULT '',
  `usage` int(11) DEFAULT 0,
  PRIMARY KEY (`guid`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table CuratorRelation
# ------------------------------------------------------------

CREATE TABLE `CuratorRelation` (
  `Curator` varchar(64) NOT NULL DEFAULT '',
  `target` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Karma
# ------------------------------------------------------------

CREATE TABLE `Karma` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `target` varchar(64) NOT NULL DEFAULT '',
  `Source` varchar(64) NOT NULL DEFAULT '',
  `weight` int(10) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table Product
# ------------------------------------------------------------

CREATE TABLE `Product` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NULL DEFAULT '',
  `cost` int(8) NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `tangible` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`guid`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table Promotion
# ------------------------------------------------------------

CREATE TABLE `Promotion` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NULL DEFAULT '',
  `value` int(8) NULL DEFAULT '0',
  `active` tinyint(1) NULL DEFAULT '1',
  `single_use` tinyint(1) NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Sale
# ------------------------------------------------------------

CREATE TABLE `Sale` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `User` varchar(64) NOT NULL DEFAULT '',
  `ShippingAddress` varchar(64) DEFAULT '',
  `Product` varchar(64) NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL,
  `total` int(8) NOT NULL DEFAULT '0',
  `converted_total` int(15) NULL DEFAULT '0',
  `quantity` int(5) NOT NULL DEFAULT '1',
  `payment` ENUM('PAYPAL', 'STRIPE', 'DOGE') NOT NULL,
  `shipped` tinyint(1) NULL DEFAULT '0',
  `confirmed` tinyint(1) NULL DEFAULT '0',
  `IPN_timestamp` int(10) NULL DEFAULT '0',
  `tracking` varchar(255) NULL DEFAULT '',
  `external_transaction_id` varchar(255) NULL DEFAULT '',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Ticket
# ------------------------------------------------------------

CREATE TABLE `Ticket` (
  `guid` varchar(64) NOT NULL DEFAULT '',
  `User` varchar(64) NOT NULL DEFAULT '',
  `Promotion` varchar(64) NOT NULL DEFAULT '',
  `valid` tinyint(1) NULL DEFAULT '1',
  `redeemed` varchar(64) NULL DEFAULT '',
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table TicketRedemption
# ------------------------------------------------------------

CREATE TABLE `TicketRedemption` (
  `Ticket` varchar(64) NOT NULL DEFAULT '',
  `Sale` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`Ticket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `id` varchar(64) NOT NULL DEFAULT '',
  `access` int(10) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table ShippingAddress
# ------------------------------------------------------------

CREATE TABLE `ShippingAddress` (
  `guid` varchar(64) NOT NULL,
  `User` varchar(64) NOT NULL,
  `recipient_name` varchar(50) NOT NULL,
  `line1` varchar(100) NOT NULL,
  `line2` varchar(100) DEFAULT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `state` varchar(2) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
