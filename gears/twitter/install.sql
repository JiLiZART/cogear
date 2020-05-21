ALTER TABLE  `users` ADD  `twitter` VARCHAR( 255 ) NOT NULL AFTER  `avatar`;
ALTER TABLE  `users` ADD INDEX (  `twitter` );