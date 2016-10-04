CREATE TABLE IF NOT EXISTS `@prefixstructure` (
  `StructureID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ParentID` int(11) DEFAULT NULL,
  `Name` varchar(255) NOT NULL,
  `Created` datetime DEFAULT CURRENT_TIMESTAMP,
  `UserID` int(11) DEFAULT NULL,
  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Url` varchar(255) NOT NULL,
  `MaterialID` int(11),
  `PriorityNumber` int(11) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '0',
  `Active` int(11) NOT NULL DEFAULT '1',
  `system` int(1) NOT NULL DEFAULT '0',
  FOREIGN KEY (`UserID`) REFERENCES `@prefixuser`(`user_id`) ON DELETE CASCADE,
  KEY `Url` (`Url`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;