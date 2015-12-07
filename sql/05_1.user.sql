INSERT INTO `@prefixuser` (`user_id`, `f_name`, `group_id`, `active`, `system`, `confirmed`)
VALUES ("1", "SamsonCMS", 1, 1, 1, 1) ON DUPLICATE KEY UPDATE Active = 1;