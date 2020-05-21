-- Patched
CREATE TABLE `cron` (
  `id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `period` int(11) NOT NULL,
  `callback` varchar(255) NOT NULL,
  `params` varchar(255) NOT NULL,
  `last_call` int(11) NOT NULL,
  `position` smallint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `period` (`period`,`callback`,`last_call`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `cron`
--

INSERT INTO `cron` VALUES(1, 'Обновление потока новостей', 60, 'feed refresh', '', 1266260873, 1);