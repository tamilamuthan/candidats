ALTER IGNORE TABLE `email_history` ADD COLUMN `for_module` varchar(255) default NULL;
ALTER IGNORE TABLE `email_history` ADD COLUMN `for_id` int(11) default NULL;
ALTER IGNORE TABLE `email_template` ADD COLUMN `basemodule` varchar(255) default NULL;
