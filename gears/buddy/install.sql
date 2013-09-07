CREATE TABLE IF NOT EXISTS `buddies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `from` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL,
  `pm` int(10) unsigned NOT NULL,
  `approved` enum('true') default NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `from` (`from`,`to`,`pm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;