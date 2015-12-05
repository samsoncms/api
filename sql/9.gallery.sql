CREATE TABLE IF NOT EXISTS `@prefixgallery` (
  `PhotoID` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `MaterialID` int(11) NOT NULL,
  `materialFieldId` int(11) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `Path` varchar(255) NOT NULL,
  `Src` varchar(255) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `Loaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Description` text NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Active` int(11) NOT NULL,
  FOREIGN KEY (`MaterialID`) REFERENCES `@prefixmaterial`(`MaterialID`) ON DELETE CASCADE,
  FOREIGN KEY (`materialFieldId`) REFERENCES `@prefixmaterialfield`(`materialFieldId`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
