ALTER IGNORE TABLE `activity` ADD COLUMN `name` varchar(255) default NULL;

ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `is_extra` int(1) default 0;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `presence` varchar(255) default 0;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `fieldlabel` varchar(255) default NULL;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `defaultvalue` TEXT default NULL;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `maximumlength` INT(11) DEFAULT '0' ;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `blockid` INT(11) DEFAULT '0' ;
ALTER IGNORE TABLE `extra_field_settings` ADD COLUMN `helpinfo` TEXT default NULL;

ALTER IGNORE TABLE `joborder` ADD COLUMN `candidate_mapping` text default NULL;

ALTER TABLE `candidate` ADD `ownertype` INT(1) NOT NULL DEFAULT '0'; 
ALTER TABLE `joborder` ADD `ownertype` INT(1) NOT NULL DEFAULT '0'; 
ALTER TABLE `company` ADD `ownertype` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `contact` ADD `ownertype` INT(1) NOT NULL DEFAULT '0'; 

CREATE TABLE IF NOT EXISTS `auieo_blocks` (
  `id` int(19) NOT NULL AUTO_INCREMENT,
  `data_item_type_id` int(19) NOT NULL,
  `blocklabel` varchar(100) NOT NULL,
  `sequence` int(10) DEFAULT NULL,
  `show_title` int(2) DEFAULT NULL,
  `visible` int(2) NOT NULL DEFAULT '0',
  `create_view` int(2) NOT NULL DEFAULT '0',
  `edit_view` int(2) NOT NULL DEFAULT '0',
  `detail_view` int(2) NOT NULL DEFAULT '0',
  `display_status` int(1) NOT NULL DEFAULT '1',
  `iscustom` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_1_auieo_blocks` (`data_item_type_id`)
) ENGINE=InnoDB  DEFAULT  CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `auieo_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `import_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime  NOT NULL,
  `data_item_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `uitype` int(11) NOT NULL DEFAULT '1',
  `field_options` text COLLATE utf8_unicode_ci,
  `position` int(4) NOT NULL DEFAULT '0',
  `is_extra` int(1) DEFAULT '0',
  `presence` int(1) DEFAULT '2',
  `readonly` int(1) NOT NULL DEFAULT '0',
  `fieldlabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `defaultvalue` text COLLATE utf8_unicode_ci NOT NULL,
  `maximumlength` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `blockid` int(11) NOT NULL DEFAULT '0',
  `displaytype` int(11) NOT NULL DEFAULT '0',
  `helpinfo` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldname` (`fieldname`,`data_item_type`,`site_id`)
) ENGINE=InnoDB  DEFAULT  CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `auieo_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime  NOT NULL,
  `parentid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rolename` (`rolename`,`site_id`)
) ENGINE=InnoDB  DEFAULT  CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `auieo_profiles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `profilename` varchar(50) NOT NULL,
  `description` text,
  `site_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
    UNIQUE KEY `profilename` (`profilename`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auieo_profiles2permissions` (
  `profileid` int(11) DEFAULT NULL,
  `data_item_type` int(11) DEFAULT 0,
  `operation` int(11) DEFAULT 0,
  `permissions` int(1) DEFAULT 0,
  `site_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `profilename` (`profileid`,`data_item_type`,`operation`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `auieo_profiles` (`id`, `profilename`, `description`, `site_id`) VALUES
(1, 'Administrator', 'Admin Profile',1),
(2, 'Sourcer', '',1),
(3, 'DTP', '',1);

ALTER TABLE `user` ADD `roleid` INT NOT NULL DEFAULT '0' ;

CREATE TABLE IF NOT EXISTS `auieo_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupname` (`groupname`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `auieo_roles2profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) NOT NULL,
  `profileid` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles2profiles` (`roleid`,`profileid`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auieo_groups2roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `roleid` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups2roles` (`groupid`,`roleid`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auieo_groups2users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
UNIQUE KEY `groups2roles` (`groupid`,`user_id`,`site_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;