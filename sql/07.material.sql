CREATE TABLE IF NOT EXISTS `@prefixmaterial` (
  `MaterialID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `Name` varchar(555) NOT NULL,
  `Url` varchar(255) NOT NULL,
  `Created` datetime DEFAULT CURRENT_TIMESTAMP,
  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `UserID` int(11) DEFAULT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `Draft` int(11) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '0',
  `Published` int(1) unsigned DEFAULT NULL,
  `Active` int(1) unsigned DEFAULT NULL,
  `system` int(1) NOT NULL DEFAULT '0',
  `remains` float NOT NULL DEFAULT '0',
  FOREIGN KEY (`UserID`) REFERENCES `@prefixuser`(`user_id`) ON DELETE CASCADE,
  KEY `Url` (`Url`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;