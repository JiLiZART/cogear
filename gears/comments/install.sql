-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 24, 2009 at 12:13 AM
-- Server version: 5.0.41
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `cogear`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `path` varchar(255) NOT NULL,
  `level` smallint(6) NOT NULL default '0',
  `aid` int(11) NOT NULL,
  `body` text NOT NULL,
  `points` float NOT NULL,
  `points_counter` int(10) unsigned NOT NULL,
  `created_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `deleted` enum('true') default NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `path` (`path`,`level`,`aid`,`created_date`,`deleted`,`ip`),
  KEY `points` (`points`,`points_counter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `comments_nodes`
--

CREATE TABLE IF NOT EXISTS `comments_nodes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `nid` (`nid`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `comments_nodes`
--


-- --------------------------------------------------------

--
-- Table structure for table `comments_nodes_views`
--

CREATE TABLE IF NOT EXISTS `comments_nodes_views` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nid` int(10) unsigned NOT NULL,
  `cid` int(10) unsigned NOT NULL,
  `count` mediumint(9) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `nid` (`nid`,`cid`,`count`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `comments_nodes_views`
--

ALTER TABLE  `nodes` ADD  `comments` MEDIUMINT( 4 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `data` ;