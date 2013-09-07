<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CoGear
 *
 * Content management system based on CodeIgniter
 *
 * @package		CoGear
 * @author			CodeMotion, Dmitriy Belyaev
 * @copyright		Copyright (c) 2009, CodeMotion
 * @license			http://cogear.ru/license.html
 * @link				http://cogear.ru
 * @since			Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Sidebar hooks
 *
 * @package		CoGear
 * @subpackage	Sidebar
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Compile sidebar in footer
	*
	* @param	object
	* @return	void
	*/
	function sidebar_footer($CI){
		if(isset($CI->no_sidebar) && $CI->no_sidebar OR $CI->gear->no_sidebar) {
			return;
		}
		$CI->content['sidebar'] = $CI->sidebar->compile(TRUE);
	}
	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------