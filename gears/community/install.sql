CREATE TABLE IF NOT EXISTS `community` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `url_name` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `points` float NOT NULL,
  `points_counter` int(10) unsigned NOT NULL,
  `icon` varchar(255) NOT NULL,
  `private` enum('true') default NULL,
  `invites_only` enum('true') default NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`,`url_name`,`description`),
  KEY `points` (`points`,`points_counter`),
  KEY `private` (`private`,`invites_only`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `community_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL,
  `cid` smallint(5) unsigned NOT NULL,
  `role` enum('admin','moder','member') NOT NULL default 'member',
  `approved` enum('true') default 'true',
  `pm` int(10) unsigned default NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique` (`uid`,`cid`),
  KEY `uid` (`uid`,`cid`,`role`,`approved`,`pm`,`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `nodes` ADD  `cid` SMALLINT( 3 ) NOT NULL DEFAULT  '0' AFTER  `aid` ;