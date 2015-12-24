INSERT INTO `@prefixuser` (`user_id`, `f_name`, `email`, `group_id`, `active`, `system`, `confirmed`)
VALUES ("2", "Admin", 'admin@admin.com', 1, 1, 0, 1) ON DUPLICATE KEY UPDATE Active = 1;