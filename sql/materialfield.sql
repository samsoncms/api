CREATE TABLE IF NOT EXISTS `@prefixmaterialfield` (
  `MaterialFieldID` int(11) NOT NULL PRIMARY KEY,
  `FieldID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `key_value` bigint(20) NOT NULL DEFAULT '0',
  `Value` text NOT NULL,
  `numeric_value` double NOT NULL DEFAULT '0',
  `locale` varchar(10) NOT NULL,
  `Active` int(11) NOT NULL,
  FOREIGN KEY (FieldID) REFERENCES @prefixfield(FieldID),
  FOREIGN KEY (MaterialID) REFERENCES @prefixmaterial(MaterialID),
  ADD KEY `key_value` (`key_value`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;