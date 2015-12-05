ALTER TABLE `material`
ADD FOREIGN KEY (`parent_id`) REFERENCES `material` (`materialid`) ON DELETE SET NULL ON UPDATE CASCADE;