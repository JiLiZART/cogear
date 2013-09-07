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
 * Empty class
 *
 * Very usefull thing. Helps you not to catch error on $object->non_existent_param., but NULL.
 * It's used in array2object function, also in gears config. $this->gears->module->non_existent_param will be NULL
 *
 * @package		CoGear
 * @subpackage	Global
 * @category		Gears libraries
 * @author			CodeMotion, Dmitriy Belyaev
 * @link				http://cogear.ru/user_guide/
 */
class emptyClass{
	/*
	* Get param that doesn't exist
	*
	* @param	string
	* @return	NULL
	*/
	function __get($name){
		return NULL;
	}
	// ------------------------------------------------------------------------

	/*
	* Call method that doesn't exist
	*
	* @param	string
	* @param	array
	* @return	void
	*/
	function __call($func,$args){
		return FALSE;
	}
	// ------------------------------------------------------------------------
}
// ------------------------------------------------------------------------