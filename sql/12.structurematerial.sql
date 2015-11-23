CREATE TABLE IF NOT EXISTS `@prefixstructurematerial` (
  `StructureMaterialID` int(11) NOT NULL PRIMARY KEY,
  `StructureID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Active` int(11) NOT NULL DEFAULT '1',
  FOREIGN KEY (`StructureID`) REFERENCES `@prefixstructure`(`StructureID`) ON DELETE CASCADE,
  FOREIGN KEY (`MaterialID`) REFERENCES `@prefixmaterial`(`MaterialID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;