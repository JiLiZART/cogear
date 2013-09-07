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
 * Common Functions
 *
 * Set shortcut for oftenly used method.
 *
 * @package		CoGear
 * @subpackage	Access Control Level
 * @category		Gears Libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	*  Quick access function for check method.
	*
	* @param	string $rule
	* @return	void
	*/
	function acl($rule){
		$CI =& get_instance();
		if(isset($CI->uri->segments[1]) && $CI->uri->segments[1] == 'install') return;
		return $CI->acl->check($rule);
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------