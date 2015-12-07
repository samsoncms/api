INSERT INTO `@prefixgroup` (`GroupID`, `Name`, `Active`)
VALUES ("2", "Admin", 1) ON DUPLICATE KEY UPDATE Active = 1;;