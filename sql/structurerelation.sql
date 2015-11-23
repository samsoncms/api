CREATE TABLE IF NOT EXISTS `@prefixstructure_relation` (
  `structure_relation_id` int(11) NOT NULL PRIMARY KEY,
  `parent_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  FOREIGN KEY (parent_id) REFERENCES @prefixstructure(StructureID),
  FOREIGN KEY (child_id) REFERENCES @prefixstructure(StructureID)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;