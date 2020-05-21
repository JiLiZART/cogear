CREATE TABLE  IF NOT EXISTS `points` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`type` ENUM(  'node',  'comment',  'user', 'community' ) NOT NULL ,
`tid` INT UNSIGNED NOT NULL ,
`uid` INT UNSIGNED NOT NULL ,
`points` FLOAT( 2 ) NOT NULL ,
`created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
INDEX (  `type` ,  `tid` ,  `points` ,  `created_date` )
) ENGINE = MYISAM ;

ALTER TABLE `points` ADD UNIQUE (
`type` ,
`tid` ,
`uid`
);

ALTER TABLE  `nodes` ADD  `points` FLOAT( 3 ) NOT NULL AFTER  `comments`,
ADD  `points_counter` INT( 4 ) NOT NULL AFTER  `points` ,
ADD INDEX (  `points` ,  `points_counter` );

ALTER TABLE  `comments` ADD  `points` FLOAT( 3 ) NOT NULL AFTER  `body` ,
ADD  `points_counter` INT( 4 ) NOT NULL AFTER  `points` ,
ADD INDEX (  `points` ,  `points_counter` );


ALTER TABLE  `community` ADD  `points` FLOAT( 3 ) NOT NULL AFTER  `created_date` ,
ADD  `points_counter` INT( 4 ) NOT NULL AFTER  `points` ,
ADD INDEX (  `points` ,  `points_counter` );

ALTER TABLE  `users` ADD  `points` FLOAT( 3 ) NOT NULL AFTER  `avatar` ,
ADD  `points_counter` INT( 4 ) NOT NULL AFTER  `points` ,
ADD  `charge` INT( 3 ) NOT NULL DEFAULT  '3'  AFTER  `points_counter` ,
ADD  `last_charge_bonus` DATETIME NULL DEFAULT NULL AFTER  `charge`,
ADD INDEX (  `points` ,  `points_counter` , `charge` );