ALTER TABLE  `pages` ADD  `keywords` VARCHAR( 255 ) NOT NULL AFTER  `body` ,
ADD  `description` VARCHAR( 255 ) NOT NULL AFTER  `keywords`;