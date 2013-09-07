CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(75) NOT NULL,
  `url_name` varchar(75) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `nodes_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nid` int(10) unsigned NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`nid`,`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `nodes` ADD  `tags` VARCHAR( 255 ) NOT NULL AFTER  `data` ;