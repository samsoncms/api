CREATE TABLE IF NOT EXISTS `@prefixuser` (
  `user_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `f_name` varchar(255) NOT NULL,
  `s_name` varchar(255) NOT NULL,
  `t_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `md5_email` varchar(255) NOT NULL,
  `md5_password` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `group_id` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `system` int(1) NOT NULL DEFAULT '0',
  `access_token` varchar(256) NOT NULL DEFAULT '0',
  `Password` varchar(64) DEFAULT NULL,
  `accessToken` varchar(256) DEFAULT NULL,
  `confirmed` varchar(32) DEFAULT NULL,
  FOREIGN KEY (`group_id`) REFERENCES `@prefixgroup`(`GroupID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;