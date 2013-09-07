<?php
/**
* Form shortcuts
*
* @author			Dmitriy Belyaev <admin@cogear.ru>
* @copyright		Copyright (c) 2010, Dmitriy Belyeav
* @license			http://cogear.ru/license.html
* @link				http://cogear.ru
* @package			Form
* @subpackage		Libraries
* @version			$Id$
*/

/**
 * Simple button create outside form
 *
 * @param	string	$i18n_string	Translation string like 'edit create'
 * @param	string	$link				Link button provide
 * @param	boolean	$return	
 */
function button($i18n_string = '',$link = '',$return = FALSE){
	$CI =& get_instance();
    return $CI->builder->a($CI->builder->span(t($i18n_string)),$link,'button',!$return);
}