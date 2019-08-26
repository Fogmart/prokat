ALTER TABLE `#__vikrentcar_cars` ADD COLUMN `idretplace` varchar(128) DEFAULT NULL;
UPDATE `#__vikrentcar_cars` SET `idretplace`=`idplace`;
ALTER TABLE `#__vikrentcar_cars` ADD COLUMN `moreimgs` varchar(256) DEFAULT NULL;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('requirelogin','0');
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `ujid` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `spname` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `wdays` varchar(16) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_seasons` CHANGE `from` `from` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `#__vikrentcar_seasons` CHANGE `to` `to` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `lat` varchar(16) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `lng` varchar(16) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `descr` varchar(256) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `#__vikrentcar_dispcosthours` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `idcar` int(10) NOT NULL, `hours` int(10) NOT NULL, `idprice` int(10) NOT NULL, `cost` decimal(12,2) DEFAULT NULL, `attrdata` varchar(256) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `hourly` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_oldorders` ADD COLUMN `hourly` tinyint(1) NOT NULL DEFAULT 0;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('loadjquery','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('calendar','jqueryui');