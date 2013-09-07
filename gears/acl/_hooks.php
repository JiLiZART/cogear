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
 * ACL hooks
 *
 * @package		CoGear
 * @subpackage	Access Control Level
 * @category		Gears hooks
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */


	/**
	*  Act at every controllers header. Set basic access restrictions.
	*
	* @param	object $CI
	* @return	void
	*/
	function acl_header($CI){
		if(count($CI->uri->segments) < 1) return;
		if($CI->uri->segments[1] == 'admin' && !acl('admin access')){
			_403();
		}
		elseif(count($CI->uri->segments) > 1 && $CI->uri->segments[1] == 'admin' && $CI->user->get('user_group') != 1){
			_403();
		}
/*
		elseif(!acl($CI->name.($CI->uri->segments[1] == 'admin' ? (isset($CI->uri->segments[2]) ? ' admin' : ' access') : ' access'))){
			_403();
		}
*/
	}

	// ------------------------------------------------------------------------
// ------------------------------------------------------------------------