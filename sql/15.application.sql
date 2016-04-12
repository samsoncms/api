ALTER TABLE `structure` ADD `hidden` INT(1) NOT NULL DEFAULT '0' FIRST;
ALTER TABLE `structure` ADD `applicationGenerate` INT(1) NOT NULL DEFAULT '0' AFTER `hidden`;
ALTER TABLE `structure` ADD `applicationOutput` INT(1) NOT NULL DEFAULT '0' AFTER `applicationGenerate`;
ALTER TABLE `structure` ADD `applicationIcon` varchar(100) NOT NULL DEFAULT 'users' AFTER `applicationOutput`;
ALTER TABLE `structure` ADD `applicationRenderMain` INT(1) NOT NULL DEFAULT '1' AFTER `applicationIcon`;
ALTER TABLE `field` ADD `showInList` INT(1) NOT NULL DEFAULT '1' AFTER `system`;
ALTER TABLE `field` ADD `showInForm` INT(1) NOT NULL DEFAULT '1' AFTER `showInList`;
ALTER TABLE `field` ADD `customTypeName` VARCHAR(100) DEFAULT NULL AFTER `showInForm`;