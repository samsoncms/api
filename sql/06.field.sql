CREATE TABLE IF NOT EXISTS `@prefixfield` (
  `FieldID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `Name` varchar(255) NOT NULL,
  `Type` int(11) NOT NULL,
  `local` int(10) NOT NULL,
  `filtered` int(10) NOT NULL,
  `Value` text NOT NULL,
  `Description` text NOT NULL,
  `Created` datetime NOT NULL,
  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Active` int(11) NOT NULL,
  `system` int(1) NOT NULL DEFAULT '0',
  FOREIGN KEY (`UserID`) REFERENCES `@prefixuser`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
