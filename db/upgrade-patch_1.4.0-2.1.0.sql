INSERT INTO `auieo_uitype` (`id`, `name`, `fieldinfo`, `length`, `caption`) VALUES
(1, 'name', 2910002, 100, 'Name'),
(2, 'phone', 235840002, 20, 'Phone'),
(3, 'email', 167070002, 100, 'EMail'),
(4, 'number', 491520002, 11, 'Number'),
(5, 'owner', 163840723, 11, 'Owner'),
(6, 'date', 266560641, 30, 'Date'),
(7, 'customdropdown', 999999999, 0, 'Custom Drop Down');

ALTER TABLE `joborder` ADD `import_id` INT NOT NULL DEFAULT '0' ;