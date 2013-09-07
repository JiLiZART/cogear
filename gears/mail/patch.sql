-- Patched
ALTER TABLE `pm` ADD `last_update` TIMESTAMP NOT NULL AFTER `created_date`;
SET `pm`.`last_update` = `pm`.`created_date`;
UPDATE pm SET last_update = (SELECT created_date FROM comments INNER JOIN comments_pm ON comments_pm.cid = comments.id WHERE comments_pm.pid = pm.id ORDER BY comments.id DESC LIMIT 1);
UPDATE pm SET last_update = created_date WHERE (SELECT COUNT(*) FROM comments_pm WHERE comments_pm.pid = pm.id) = 0;
ALTER TABLE  `pm` ADD  `system` ENUM(  'true' ) NULL AFTER  `comments`;

CREATE TABLE `comments_pm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `comments_pm_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `count` mediumint(9) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`cid`,`count`,`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE  `pm` ADD  `has_read` VARCHAR( 255 ) NOT NULL AFTER  `is_read`;
ALTER TABLE  `pm` CHANGE  `to`  `to` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `pm` ADD  `comments` INT( 11 ) NOT NULL AFTER  `body`;
ALTER TABLE  `pm` ADD INDEX (  `to` );
ALTER TABLE  `pm` ADD INDEX (  `has_read` ) ;
ALTER TABLE  `pm` ADD INDEX (  `comments` );
UPDATE  `pm` SET has_read =  CONCAT(pm.from,',',pm.to) WHERE is_read IS NOT NULL
UPDATE  `pm` SET has_read =  '' WHERE is_read IS NULL