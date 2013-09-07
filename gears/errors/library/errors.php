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
 * Errors functions 
 *
 * @package		CoGear
 * @subpackage	Errors
 * @category		Gears libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
	/**
	* Shortcut for error method of error class
	*
	* @return	void
	*/
	function error(){
		$args = func_get_args();
		$CI =& get_instance();
		call_user_func_array(array($CI->errors,'show'),$args);
	}
	// ------------------------------------------------------------------------

	/**
	* Shortcut for _404 method of error class
	*
	* @return	void
	*/
	function show404(){
		$args = func_get_args();
		$CI =& get_instance();
		call_user_func_array(array($CI->errors,'_404'),$args);
	}
	// ------------------------------------------------------------------------

	/**
	* Shortcut for _404 method of error class
	*
	* @return	void
	*/
	function _404(){
		$args = func_get_args();
		$CI =& get_instance();
		call_user_func_array(array($CI->errors,'_404'),$args);
	}
	// ------------------------------------------------------------------------

	/**
	* Shortcut for _403 method of error class
	*
	* @return	void
	*/
	function _403(){
		$args = func_get_args();
		$CI =& get_instance();
		call_user_func_array(array($CI->errors,'_403'),$args);
	}
	// ------------------------------------------------------------------------

	/**
	* Shortcut for info method of error class
	*
	* @return	void
	*/
	function info(){
		$args = func_get_args();
		$CI =& get_instance();
		call_user_func_array(array($CI->errors,'info'),$args);
	}
	// ------------------------------------------------------------------------

// ------------------------------------------------------------------------