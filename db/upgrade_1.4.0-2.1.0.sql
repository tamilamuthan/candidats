CREATE TABLE IF NOT EXISTS `auieo_uitype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NOT NULL,
  `fieldinfo` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `data_item_type` ADD `tablename` VARCHAR(50) NOT NULL ;
ALTER TABLE `data_item_type` ADD `modulename` VARCHAR(50) NOT NULL ;
ALTER TABLE `data_item_type` ADD `primarykey` VARCHAR(50) NOT NULL ;
ALTER TABLE `data_item_type` ADD `site_id` INT NOT NULL ;

UPDATE `data_item_type` SET `tablename` = 'candidate', `modulename` = 'candidates', `primarykey` = 'candidate_id', `site_id` = '1' WHERE `data_item_type`.`data_item_type_id` = 100; 
UPDATE `data_item_type` SET `tablename` = 'company', `modulename` = 'companies', `primarykey` = 'company_id', `site_id` = '1' WHERE `data_item_type`.`data_item_type_id` = 200; 
UPDATE `data_item_type` SET `tablename` = 'contact', `modulename` = 'contacts', `primarykey` = 'contact_id', `site_id` = '1' WHERE `data_item_type`.`data_item_type_id` = 300; 
UPDATE `data_item_type` SET `tablename` = 'joborder', `modulename` = 'joborders', primarykey = 'joborder_id', `site_id` = '1' WHERE `data_item_type`.`data_item_type_id` = 400;

CREATE TABLE IF NOT EXISTS `auieo_sharingaccess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_item_type` int(11) NOT NULL,
  `sharingaccess` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auieo_dropdown` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `data_item_type` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auieo_dropdowndata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dropdown_id` int(11) NOT NULL,
  `data` varchar(100) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;