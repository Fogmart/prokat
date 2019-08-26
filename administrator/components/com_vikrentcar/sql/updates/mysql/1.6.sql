ALTER TABLE `#__vikrentcar_categories` ADD COLUMN `descr` text DEFAULT NULL;
ALTER TABLE `#__vikrentcar_gpayments` ADD COLUMN `val_pcent` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__vikrentcar_gpayments` ADD COLUMN `ch_disc` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `opentime` varchar(16) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `pickupincl` tinyint(1) NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS `#__vikrentcar_hourscharges` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `idcar` int(10) NOT NULL, `ehours` int(10) NOT NULL, `idprice` int(10) NOT NULL, `cost` decimal(12,2) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('ehourschbasp','1');
ALTER TABLE `#__vikrentcar_optionals` ADD COLUMN `forcesel` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_optionals` ADD COLUMN `forceval` varchar(32) DEFAULT NULL;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('enablecoupons','1');
CREATE TABLE IF NOT EXISTS `#__vikrentcar_coupons` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`code` varchar(64) NOT NULL,`type` tinyint(1) NOT NULL DEFAULT 1,`percentot` tinyint(1) NOT NULL DEFAULT 1,`value` decimal(12,2) DEFAULT NULL,`datevalid` varchar(64) DEFAULT NULL,`allvehicles` tinyint(1) NOT NULL DEFAULT 1,`idcars` varchar(512) DEFAULT NULL,`mintotord` decimal(12,2) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `coupon` varchar(128) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_oldorders` ADD COLUMN `coupon` varchar(128) DEFAULT NULL;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('theme','default');