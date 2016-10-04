INSERT INTO `@prefixgroup` (`GroupID`, `Name`, `Active`)
VALUES ("1", "Admin", 1) ON DUPLICATE KEY UPDATE Active = 1;;