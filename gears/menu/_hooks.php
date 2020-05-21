<?php
/**
 * Menu hooks
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Menu
 * @version			$Id$
 */
 
 function menu_after($CI){
	 $CI->menu->show();
 }