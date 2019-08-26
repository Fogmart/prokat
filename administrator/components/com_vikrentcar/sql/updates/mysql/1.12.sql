CREATE TABLE IF NOT EXISTS `#__vikrentcar_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `cfields` text DEFAULT NULL,
  `pin` int(5) NOT NULL DEFAULT 0,
  `ujid` int(5) NOT NULL DEFAULT 0,
  `address` varchar(256) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `zip` varchar(16) DEFAULT NULL,
  `doctype` varchar(64) DEFAULT NULL,
  `docnum` varchar(128) DEFAULT NULL,
  `docimg` varchar(128) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `vat` varchar(64) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `bdate` varchar(16) DEFAULT NULL,
  `pbirth` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_customers_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcustomer` int(10) NOT NULL,
  `idorder` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_restrictions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'restriction',
  `month` tinyint(2) NOT NULL DEFAULT 7,
  `wday` tinyint(1) DEFAULT NULL,
  `minlos` tinyint(2) NOT NULL DEFAULT 1,
  `multiplyminlos` tinyint(1) NOT NULL DEFAULT 0,
  `maxlos` tinyint(2) NOT NULL DEFAULT 0,
  `dfrom` int(10) DEFAULT NULL,
  `dto` int(10) DEFAULT NULL,
  `wdaytwo` tinyint(1) DEFAULT NULL,
  `wdaycombo` varchar(28) DEFAULT NULL,
  `allcars` tinyint(1) NOT NULL DEFAULT 1,
  `idcars` varchar(512) DEFAULT NULL,
  `ctad` varchar(28) DEFAULT NULL,
  `ctdd` varchar(28) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__vikrentcar_custfields` ADD COLUMN `flag` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `address` varchar(128) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `wopening` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_prices` ADD COLUMN `closingd` text DEFAULT NULL;

INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('enablepin','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('emailsendwhen','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('icalendtype','pick');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('typedeposit','pcent');