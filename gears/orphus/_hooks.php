<?php
/**
* Orphus hooks
*
* @author		Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2009, Dmitriy Belyeav
* @license		http://cogear.ru/license.html
* @link			http://cogear.ru
* @package		Orphus
* @version		$Id$
*/

function orphus_footer($CI){
	$CI->builder->div('<a href="http://orphus.ru" id="orphus" target="_blank"><img alt="Система Orphus" src="/gears/orphus/img/orphus.gif" border="0" ></a>'.js('/gears/orphus/js/inline/orphus',FALSE,FALSE),FALSE,'orphus-holder',TRUE);
	//$CI->builder->div('<a href="http://orphus.ru" id="orphus" target="_blank"><img alt="Система Orphus" title="Система Orphus" src="/gears/orphus/img/orphus.gif" border="0"/></a>',FALSE,'orphus',TRUE);
}