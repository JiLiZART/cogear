CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `nid` (`nid`,`uid`,`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
