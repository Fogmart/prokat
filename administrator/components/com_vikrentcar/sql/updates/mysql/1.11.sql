ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `adminnotes` text DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `cust_cost` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `cust_idiva` int(10) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `extracosts` varchar(2048) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `seen` tinyint(1) NOT NULL DEFAULT 0;