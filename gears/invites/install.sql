CREATE TABLE IF NOT EXISTS `invites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `from` int(10) unsigned NOT NULL default '1',
  `to` int(10) unsigned default NULL,
  `invite` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;