ALTER TABLE `materialfield` CHANGE `FieldID` `FieldID` INT(11) NULL DEFAULT NULL;
ALTER TABLE `materialfield` CHANGE `MaterialID` `MaterialID` INT(11) NULL DEFAULT NULL;
ALTER TABLE `materialfield` CHANGE `key_value` `key_value` INT(11) NULL DEFAULT NULL;
ALTER TABLE `materialfield` CHANGE `Value` `Value` INT(11) NULL DEFAULT NULL;
ALTER TABLE `materialfield` CHANGE `numeric_value` `numeric_value` INT(11) NULL DEFAULT NULL;
ALTER TABLE `materialfield` CHANGE `locale` `locale` INT(11) NULL DEFAULT NULL;
ALTER TABLE `structure` ADD `applicationOutputStructure` INT(1) NOT NULL DEFAULT '0' AFTER `applicationIcon`;
