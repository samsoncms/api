INSERT INTO `@prefixuser` (`f_name`, `group_id`, `active`, `system`, `confirmed`)
VALUES ("SamsonCMS", 1, 1, 1, 1) ON DUPLICATE KEY UPDATE Active = 1;