-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Сен 11 2009 г., 18:51
-- Версия сервера: 5.1.37
-- Версия PHP: 5.2.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- База данных: `cogear`
--

-- --------------------------------------------------------

--
-- Структура таблицы `acl`
--

DROP TABLE IF EXISTS `acl`;
CREATE TABLE `acl` (
  `rule` varchar(50) NOT NULL,
  `gear` varchar(30) NOT NULL,
  `gid` smallint(6) unsigned NOT NULL,
  KEY `rule` (`rule`,`gear`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `acl`
--

INSERT INTO `acl` VALUES('add', 'comments', 100);
INSERT INTO `acl` VALUES('create', 'nodes', 100);
INSERT INTO `acl` VALUES('manage', 'favorites', 100);

-- --------------------------------------------------------

--
-- Структура таблицы `acl_rules`
--

DROP TABLE IF EXISTS `acl_rules`;
CREATE TABLE `acl_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gear` varchar(20) NOT NULL,
  `rule` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gear` (`gear`,`rule`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Дамп данных таблицы `acl_rules`
--

INSERT INTO `acl_rules` VALUES(1, 'nodes', 'create');
INSERT INTO `acl_rules` VALUES(2, 'community', 'view_private');
INSERT INTO `acl_rules` VALUES(3, 'favorites', 'manage');
INSERT INTO `acl_rules` VALUES(4, 'meta', 'edit');
INSERT INTO `acl_rules` VALUES(5, 'community', 'change_node_all_communities');
INSERT INTO `acl_rules` VALUES(6, 'jevix', 'disable');
INSERT INTO `acl_rules` VALUES(7, 'image', 'upload');
INSERT INTO `acl_rules` VALUES(8, 'comments', 'add');
INSERT INTO `acl_rules` VALUES(9, 'comments', 'edit');
INSERT INTO `acl_rules` VALUES(10, 'comments', 'edit_all');
INSERT INTO `acl_rules` VALUES(11, 'comments', 'delete_all');
INSERT INTO `acl_rules` VALUES(12, 'comments', 'destroy');
INSERT INTO `acl_rules` VALUES(13, 'comments', 'view_ip');
INSERT INTO `acl_rules` VALUES(14, 'comments', 'view_ip_all');
INSERT INTO `acl_rules` VALUES(15, 'comments', 'delete_node_author');
INSERT INTO `acl_rules` VALUES(16, 'comments', 'edit_node_author');
INSERT INTO `acl_rules` VALUES(17, 'nodes', 'edit_all');
INSERT INTO `acl_rules` VALUES(18, 'user', 'change_email');
INSERT INTO `acl_rules` VALUES(19, 'comments', 'delete');
INSERT INTO `acl_rules` VALUES(20, 'community', 'create');
INSERT INTO `acl_rules` VALUES(21, 'community', 'change_node_community');
INSERT INTO `acl_rules` VALUES(22, 'community', 'delete');
INSERT INTO `acl_rules` VALUES(23, 'nodes', 'delete');
INSERT INTO `acl_rules` VALUES(24, 'blogs', 'view_unpublished');
INSERT INTO `acl_rules` VALUES(27, 'upload', 'image');
INSERT INTO `acl_rules` VALUES(26, 'nodes', 'url_name');
INSERT INTO `acl_rules` VALUES(28, 'upload', 'images');
INSERT INTO `acl_rules` VALUES(29, 'index', 'promote');
INSERT INTO `acl_rules` VALUES(30, 'index', 'always_on_index');

-- --------------------------------------------------------

--
-- Структура таблицы `buddies`
--

DROP TABLE IF EXISTS `buddies`;
CREATE TABLE `buddies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL,
  `pm` int(10) unsigned NOT NULL,
  `approved` enum('true') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `from` (`from`,`to`,`pm`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `buddies`
--


-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `level` smallint(6) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL,
  `body` text NOT NULL,
  `points` float NOT NULL,
  `points_counter` int(10) unsigned NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` enum('true') DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `path` (`path`,`level`,`aid`,`created_date`,`deleted`,`ip`),
  KEY `points` (`points`,`points_counter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `comments`
--


-- --------------------------------------------------------

--
-- Структура таблицы `comments_nodes`
--

DROP TABLE IF EXISTS `comments_nodes`;
CREATE TABLE `comments_nodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `comments_nodes`
--


-- --------------------------------------------------------

--
-- Структура таблицы `comments_nodes_views`
--

DROP TABLE IF EXISTS `comments_nodes_views`;
CREATE TABLE `comments_nodes_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `count` mediumint(9) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`,`cid`,`count`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `comments_nodes_views`
--


-- --------------------------------------------------------

--
-- Структура таблицы `community`
--

DROP TABLE IF EXISTS `community`;
CREATE TABLE `community` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `url_name` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `points` float NOT NULL,
  `points_counter` int(10) unsigned NOT NULL,
  `icon` varchar(255) NOT NULL,
  `private` enum('true') DEFAULT NULL,
  `invites_only` enum('true') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`url_name`,`description`),
  KEY `points` (`points`,`points_counter`),
  KEY `private` (`private`,`invites_only`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `community`
--


-- --------------------------------------------------------

--
-- Структура таблицы `community_users`
--

DROP TABLE IF EXISTS `community_users`;
CREATE TABLE `community_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `cid` smallint(5) unsigned NOT NULL,
  `role` enum('admin','moder','member') NOT NULL DEFAULT 'member',
  `approved` enum('true') DEFAULT 'true',
  `pm` int(10) unsigned DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`uid`,`cid`),
  KEY `uid` (`uid`,`cid`,`role`,`approved`,`pm`,`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `community_users`
--


-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `nid` (`nid`,`uid`,`created_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `favorites`
--


-- --------------------------------------------------------

--
-- Структура таблицы `nodes`
--
CREATE TABLE `nodes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `aid` mediumint(8) unsigned NOT NULL,
  `cid` int(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(80) NOT NULL,
  `url_name` varchar(80) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `data` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `comments` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `points` float NOT NULL,
  `points_counter` int(4) NOT NULL,
  `published` enum('true') DEFAULT NULL,
  `promoted` enum('true') DEFAULT NULL,
  `promoted_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url_name` (`url_name`),
  KEY `id` (`id`,`aid`,`cid`,`url_name`,`published`,`created_date`),
  KEY `index` (`promoted`,`promoted_date`),
  KEY `points` (`points`,`points_counter`),
  FULLTEXT KEY `name` (`name`,`body`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `nodes`
--

INSERT INTO `nodes` VALUES(1, 1, 0, 'Добро пожаловать!', 'welcome', 'Привет, друг, здесь, должны, быть, ключевые, слова', 'Наконец-то ты добрался до редактирования топика! Поздравляю!', '<p align="center"><img src="templates/default/images/logo.png" class="no-border" alt="cogear"></p><br>\r\nПриветствую тебя, <strong>%username%</strong>!<br>\r\nТы читаешь эти строки, потому что установка системы управления сайтами <strong>cogear</strong> прошла успешно. <br>\r\nПрежде чем ты начнешь пользоваться движком, обязательно прочитай <a href="http://cogear.ru/license.html" rel="nofollow">лицензионное соглашение</a>.<br>\r\nТакже настоятельно рекомендую ознакомиться с <a href="http://cogear.ru/user_guide/" rel="nofollow">«Руководством»</a>, которое посвятит тебя в устройство движка и поможет решить возникшие вопросы.<br>\r\nПомни, что некоторые шестерни отключены — ты можешь включить их сам через <a href="/admin/install/" rel="nofollow">«Панель управления сайтом»</a>.<br>\r\nДля меня важно мнение каждого пользователя, но помни, что данный движок — труд одного человека, поэтому в нем не стоит искать совершенства. Мы с тобой к нему только стремимся.<br>\r\nЧем лучше ты поймешь документацию, тем проще тебе будет создавать свои шестеренки и исправлять возникшие ошибки.<br>\r\nНа разработку этого движка было отдано около года моей жизни.<br>\r\nСпасибо тебе за то, что ты проявил интерес к <strong>cogear</strong>. <br>\r\nУдачи!<br>\r\n<p align="right"><a href="http://cuamckuykot.habrahabr.ru" rel="nofollow">Дима</a></p>', '', '', 0, 0, 0, 'true', 'true', '2009-06-15 17:30:00', '2009-06-15 17:30:00', '2009-06-16 15:15:26', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `online`
--

DROP TABLE IF EXISTS `online`;
CREATE TABLE `online` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `session_id` varchar(32) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `uid` (`uid`,`session_id`,`user_agent`,`time`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `online`
--

INSERT INTO `online` VALUES(1, '555b1f69608dc8047bbf84643c519cee', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; ru', '2009-09-11 18:45:45');
INSERT INTO `online` VALUES(1, '555b1f69608dc8047bbf84643c519cee', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; ru', '2009-09-11 18:44:40');
INSERT INTO `online` VALUES(1, '555b1f69608dc8047bbf84643c519cee', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; ru', '2009-09-11 18:47:04');
INSERT INTO `online` VALUES(0, '48ba2541f6040a20b6b472aa28ae90ad', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_1; ', '2009-09-11 18:41:48');

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `aid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `url_name` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL default '0000-00-00 00:00:00',
  `position` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `aid` (`aid`),
  KEY `position` (`position`),
  KEY `url_name` (`url_name`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `pages`
--

INSERT INTO `pages` VALUES(1, 1, 'Форматирование текста', 'html', '<h1>Форматирование текста</h1>&lt;b&gt;&lt;/b&gt; — жирный<br/>\\r\\n&lt;i&gt;&lt;/i&gt; — курсив<br/>\\r\\n&lt;u&gt;&lt;/u&gt; — подчеркнутый<br/>\\r\\n&lt;s&gt;&lt;/s&gt; — перечеркнутый<br/>\\r\\n&lt;h1&gt;&lt;/h1&gt; — заголовок первого уровня<br/>\\r\\n&lt;h2&gt;&lt;/h2&gt; — заголовок второго уровня<br/>\\r\\n&lt;h3&gt;&lt;/h3&gt; — заголовок третьего уровня<br/>\\r\\n&lt;a href=«адрес»&gt;текст&lt;/a&gt; — ссылка<br/>\\r\\n&lt;code class=«синтаксис»&gt;&lt;/code&gt; — код<br/>\\r\\n&lt;blockquote&gt;&lt;/blockquote&gt; — цитата<h1>Ненумерованный список</h1>&lt;ul&gt;<br/>\\r\\n&lt;li&gt;&lt;/li&gt;<br/>\\r\\n…<br/>\\r\\n&lt;/ul&gt;<br/>\\r\\n<h1>Нумерованный список</h1>&lt;ol&gt;<br/>\\r\\n&lt;li&gt;&lt;/li&gt;<br/>\\r\\n…<br/>\\r\\n&lt;/ol&gt;<br/>\\r\\n<h1>BB-коды</h1><blockquote>Открывающие ([) и закрывающие скобки (]) представлены символом #.</blockquote><p>Разделение публикации на краткую и полную версии<br/>\\r\\n#cut#<br/>\\r\\n#cut=Читать далее#<br/>\\r\\n</p><p>Публикация видео с YouTube, RuTube, Vimeo<br/>\\r\\n#video#http://www.youtube.com/watch?v=pHGkbgkyEZc#/video#</p><p>Ссылка на профиль пользователя<br/>\\r\\n#user=имя_пользователя#</p>', '', '', '2010-11-17 15:25:18', '0000-00-00 00:00:00', 0);



-- --------------------------------------------------------

--
-- Структура таблицы `comments_pm`
--
DROP TABLE IF EXISTS `comments_pm`;
CREATE TABLE `comments_pm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `comments_pm_views`
--
DROP TABLE IF EXISTS `comments_pm_views`;
CREATE TABLE `comments_pm_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `count` mediumint(9) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`cid`,`count`,`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `pm`
--
DROP TABLE IF EXISTS `pm`;
CREATE TABLE `pm` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) unsigned NOT NULL,
  `to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `comments` int(11) NOT NULL,
  `system` enum('true') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_read` enum('true') DEFAULT NULL,
  `has_read` varchar(255) NOT NULL,
  `owner` enum('from','to') NOT NULL DEFAULT 'to',
  PRIMARY KEY (`id`),
  KEY `from` (`from`,`to`),
  KEY `created_date` (`created_date`),
  KEY `to` (`to`,`is_read`,`owner`),
  KEY `from_2` (`from`,`is_read`,`owner`),
  KEY `has_read` (`has_read`),
  KEY `to_2` (`to`),
  KEY `comments` (`comments`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `pm`
--


-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `secemail` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url_name` varchar(255) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_group` int(11) NOT NULL DEFAULT '100',
  `avatar` varchar(255) DEFAULT NULL,
  `allow_mail` enum('true') DEFAULT 'true',
  `is_validated` enum('true') DEFAULT NULL,
  `validate_code` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_name` (`url_name`),
  UNIQUE KEY `name` (`name`),
  KEY `reg_date` (`reg_date`),
  KEY `last_visit` (`last_visit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` VALUES(1, 'user@cogear.ru', NULL, '696d29e0940a4957748fe3fc9efd22a3', 'admin', 'admin', '2007-03-14 23:10:48', '2009-09-11 18:13:17', 1, '/uploads/avatars/1/iavatar.jpg', 'true', 'true', '', '127.0.0.1');

--
-- Структура таблицы `users_openid`
--

CREATE TABLE IF NOT EXISTS `users_openid` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `openid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `openid` (`openid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Структура таблицы `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `id` smallint(2) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_groups`
--

INSERT INTO `user_groups` VALUES(1, 'admin', '');
INSERT INTO `user_groups` VALUES(100, 'user', '');
INSERT INTO `user_groups` VALUES(0, 'guest', '');

-- --------------------------------------------------------

--
-- Структура таблицы `widgets`
--

DROP TABLE IF EXISTS `widgets`;
CREATE TABLE `widgets` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=144 ;

--
-- Дамп данных таблицы `widgets`
--

INSERT INTO `widgets` VALUES(143, 'online', 4);
INSERT INTO `widgets` VALUES(142, 'comments', 3);
INSERT INTO `widgets` VALUES(141, 'community', 2);
INSERT INTO `widgets` VALUES(140, 'search', 1);

CREATE TABLE `syndication_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` mediumint(3) NOT NULL,
  `link` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `body` text CHARACTER SET utf8 NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`created_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `syndication_sources` (
  `id` mediumint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `link` varchar(255) CHARACTER SET utf8 NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `last_update` datetime NOT NULL,
  `refresh_rate` smallint(5) NOT NULL,
  `position` mediumint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`last_update`,`refresh_rate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


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
