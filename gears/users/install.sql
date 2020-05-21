-- phpMyAdmin SQL Dump
-- version 2.11.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 24, 2009 at 12:15 AM
-- Server version: 5.0.41
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `cogear`
--

-- --------------------------------------------------------

--
-- Table structure for table `online`
--

CREATE TABLE IF NOT EXISTS `online` (
  `uid` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(32) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `uid` (`uid`,`session_id`,`user_agent`,`time`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
