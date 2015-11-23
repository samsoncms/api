CREATE TABLE IF NOT EXISTS `@prefixgroupright` (
  `GroupRightID` int(11) NOT NULL PRIMARY KEY,
  `GroupID` int(10) NOT NULL,
  `RightID` int(20) NOT NULL,
  `Entity` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '_',
  `Key` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Ban` int(10) NOT NULL,
  `TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Active` int(11) NOT NULL,
  FOREIGN KEY (GroupID) REFERENCES @prefixgroup(GroupID),
  FOREIGN KEY (RightID) REFERENCES @prefixright(RightID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
