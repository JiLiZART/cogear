<?php
/**
 *  Cron hooks
 *
 * @author			Dmitriy Belyaev <admin@cogear.ru>
 * @copyright		Copyright (c) 2010, Dmitriy Belyeav
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @package		Cron
 * @version			$Id$
 */
 
function cron_after($CI){
	if($CI->gears->cron->poormanscron){
		$CI->_template(array('<img src="/cron/" width="0" height="0" alt="">'),999);
	}
}