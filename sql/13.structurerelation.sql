CREATE TABLE IF NOT EXISTS `@prefixstructure_relation` (
  `structure_relation_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `@prefixstructure`(`StructureID`) ON DELETE CASCADE,
  FOREIGN KEY (`child_id`) REFERENCES `@prefixstructure`(`StructureID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;