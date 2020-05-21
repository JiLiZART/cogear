CREATE TABLE `menu` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `url_name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `template` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `show_pattern` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `resize` varchar(7) CHARACTER SET cp1251 NOT NULL,
  `crop` enum('true') CHARACTER SET cp1251 DEFAULT NULL,
  `access` enum('user','guest','all') CHARACTER SET cp1251 NOT NULL,
  `output` tinyint(3) unsigned NOT NULL,
  `position` tinyint(2) NOT NULL,
  `is_active` enum('true') CHARACTER SET cp1251 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `menu` VALUES(1, 'Главное меню', 'main', 'simple.tpl', '!admin/:any', '', NULL, 'all', 0, 0, '');

CREATE TABLE `menu_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mid` tinyint(2) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `url_name` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `link` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `pattern` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `image` varchar(255) CHARACTER SET cp1251 NOT NULL,
  `access` enum('user','guest','all') CHARACTER SET cp1251 NOT NULL,
  `position` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT INTO `menu_items` VALUES(1, 1, 'Главная', 'main', '/', '/', '', 'all', 1);
INSERT INTO `menu_items` VALUES(2, 1, 'Блоги', '0', 'blogs/', 'blogs/\r\nblogs/:any', '', 'all', 2);
INSERT INTO `menu_items` VALUES(3, 1, 'Сообщества', '', '/community/', 'community/\r\ncommunity/:any', '', 'all', 3);
INSERT INTO `menu_items` VALUES(4, 1, 'Вход', 'login', 'user/login', 'user/login/', '', 'guest', 4);
INSERT INTO `menu_items` VALUES(5, 1, 'Выход', 'logout', 'user/logout/', 'user/logout/', '', 'user', 5);