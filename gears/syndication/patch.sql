-- Patched
CREATE TABLE `syndication_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` mediumint(3) NOT NULL,
  `link` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `body` text CHARACTER SET utf8 NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`created_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

CREATE TABLE `syndication_sources` (
  `id` mediumint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `link` varchar(255) CHARACTER SET utf8 NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `last_update` datetime NOT NULL,
  `refresh_rate` smallint(5) NOT NULL,
  `position` mediumint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`last_update`,`refresh_rate`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;
