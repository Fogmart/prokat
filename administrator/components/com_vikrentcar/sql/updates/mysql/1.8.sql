ALTER TABLE `#__vikrentcar_optionals` ADD COLUMN `forceifdays` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_places` ADD COLUMN `idiva` int(10) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `locationvat` decimal(12,2) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `#__vikrentcar_usersdata` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`ujid` int(10) NOT NULL,`data` text DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `roundmode` varchar(32) DEFAULT NULL;
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('numberformat','2:.:,');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('setdropdplus','0');
ALTER TABLE `#__vikrentcar_gpayments` ADD COLUMN `params` varchar(512) DEFAULT NULL;