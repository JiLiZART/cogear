<?php
/**
* Template functions
*
* 
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Template
* @version		$Id$
*/
function escape($value){
	return htmlspecialchars($value);
}

function value($value,$default = ''){
	return !empty($value) ? $value : $default;
}

function attr($name,$value){
	return empty($value) ? ' ' : ' '.$name.'="'.htmlspecialchars($value).'"';
}